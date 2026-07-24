# Inertia SEO That Appears in View Page Source

A reusable guide for Laravel + Inertia (Vue) apps where SEO meta tags show in **Inspect Element** but **not** in **View Page Source**.

This pattern was applied in **Crave Bakery** and can be copied into other Inertia projects without enabling full SSR.

---

## 1. The Problem

### What you see

| Method | Result |
|--------|--------|
| DevTools → Inspect | `<meta name="description">` is present |
| Browser → View Page Source | Meta tags are missing |
| Social crawlers / some bots | May only read the first HTML response |

### Why it happens

Inertia without SSR returns a thin Blade shell (`app.blade.php`). The page body and most `<head>` updates are applied **after JavaScript boots**.

```
Browser request
    → Laravel renders app.blade.php  ←── View Page Source sees ONLY this
    → Vue / Inertia hydrates
    → <Head> injects / updates meta tags  ←── Inspect sees this
```

So:

- `<Head>` in Vue = great for **SPA navigations** and the live DOM
- `<Head>` alone = **not** in the initial HTML source
- Crawlers that do not execute JS (or that prefer the first HTML) miss those tags

**Important:** Google often runs JS, but relying on that alone is fragile. Social previews (Open Graph), many validators, and “View Source” checks need tags in the **first response**.

---

## 2. Solution Overview (No SSR Required)

Use a **hybrid** approach:

1. **Server-render SEO in Blade** → tags exist in View Page Source
2. **Mirror the same tags in Vue `<Head>`** → tags stay correct after Inertia client navigations
3. **Mark Blade tags with `inertia="..."`** → Inertia can replace them cleanly on the client
4. **Optional page overrides via `withViewData`** → Home / Catalogue / etc. get the right title in the first HTML

```
┌─────────────────────────────────────────────────────────┐
│  app.blade.php (first HTML)                             │
│  - <title inertia>…</title>                             │
│  - <meta inertia="description" …>                       │
│  - <meta inertia="keywords" …>                          │
│  - og:title / og:description                            │
└─────────────────────────────────────────────────────────┘
              ▲                         ▲
              │                         │
   View::composer (defaults)    Controller withViewData
              │                         │
              └────────────┬────────────┘
                           │
              SiteSettingService::documentSeo()
                           │
              ┌────────────┴────────────┐
              │  Vue useSiteSeo() + Head │
              │  (SPA navigation sync)   │
              └──────────────────────────┘
```

Full Inertia SSR also solves this, but it needs a Node SSR process, Vite SSR build, and ops complexity. This Blade hybrid is usually enough for global / page-level SEO.

---

## 3. Step-by-Step Implementation

### Step A — Central SEO resolver (PHP)

Create (or extend) a service that returns a consistent shape:

```php
/**
 * @param  array{site_name?: string, tagline?: string, category?: string, product?: string, page_title?: string}  $replacements
 * @return array{title: string, description: string, keywords: string}
 */
public function documentSeo(array $replacements = []): array
{
    // 1) Resolve title from template placeholders (%site_name%, %tagline%, …)
    // 2) If page_title is set and template has no %page_title%,
    //    use: "{page_title} - {site_name}"
    // 3) Return description + comma-joined keywords from settings/DB
}
```

Also implement `resolveTitleTemplate()` for placeholder replacement and cleanup of trailing `|` / `-`.

**Why:** One source of truth for Blade, controllers, and tests.

---

### Step B — Default SEO on every page (View composer)

In `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\View;

View::composer('app', function ($view) {
    if ($view->offsetExists('seo')) {
        return; // controller already overrode
    }

    $view->with('seo', app(SiteSettingService::class)->documentSeo());
});
```

**Why:** Every Inertia page gets description/keywords in View Source, even if the controller forgets `withViewData`.

---

### Step C — Render tags in the root Blade layout

In `resources/views/app.blade.php`:

```blade
@php
    $seo = $seo ?? app(\App\Services\SiteSettingService::class)->documentSeo();
    $seoTitle = $seo['title'] ?? config('app.name');
    $seoDescription = $seo['description'] ?? '';
    $seoKeywords = $seo['keywords'] ?? '';
@endphp

<title inertia>{{ $seoTitle }}</title>

@if ($seoDescription !== '')
    <meta inertia="description" name="description" content="{{ $seoDescription }}">
    <meta inertia="og:description" property="og:description" content="{{ $seoDescription }}">
@endif

@if ($seoKeywords !== '')
    <meta inertia="keywords" name="keywords" content="{{ $seoKeywords }}">
@endif

<meta inertia="og:title" property="og:title" content="{{ $seoTitle }}">
<meta inertia="og:type" property="og:type" content="website">
```

**Critical details:**

- Use the `inertia="..."` attribute (or matching `head-key` on the Vue side) so client Head updates replace these nodes instead of duplicating them.
- Keep `@inertiaHead` in `<head>` so Inertia can manage head elements.

After this, **View Page Source** already contains SEO.

---

### Step D — Page-specific titles via `withViewData`

Inertia can pass data to the root Blade view:

```php
// Home — use global title template
return Inertia::render('Home', [/* props */])
    ->withViewData([
        'seo' => app(SiteSettingService::class)->documentSeo(),
    ]);

// Catalogue — short page title → "Catalogue - Site Name"
return Inertia::render('Products/Index', [/* props */])
    ->withViewData([
        'seo' => app(SiteSettingService::class)->documentSeo([
            'page_title' => 'Catalogue',
        ]),
    ]);
```

**Why:** The first HTML for `/` vs `/products` can have different `<title>` values without SSR.

---

### Step E — Mirror tags in Vue for SPA navigations

When the user clicks from Cart → Home, Blade does **not** re-render. Vue must update the head.

Create a composable (e.g. `useSiteSeo.js`) that mirrors PHP rules, then in pages:

```vue
<script setup>
import { Head } from '@inertiajs/vue3'
import { useSiteSeo } from '@/Composables/useSiteSeo'

const { headTitle, title, description, keywords } = useSiteSeo()
// Catalogue: useSiteSeo({ pageTitle: 'Catalogue' })
</script>

<template>
  <Head>
    <title>{{ headTitle }}</title>
    <meta
      v-if="description"
      head-key="description"
      name="description"
      :content="description"
    />
    <meta
      v-if="keywords"
      head-key="keywords"
      name="keywords"
      :content="keywords"
    />
    <meta head-key="og:title" property="og:title" :content="title" />
    <meta
      v-if="description"
      head-key="og:description"
      property="og:description"
      :content="description"
    />
  </Head>
</template>
```

Share SEO settings through Inertia middleware (`HandleInertiaRequests`) so the composable can read `page.props.siteSettings.seo`.

---

### Step F — Avoid double site-name in the document title

Inertia’s `createInertiaApp({ title })` callback often does:

```js
title: (title) => `${title} - ${appName}`
```

If Home already sends a **full** SEO title (`Site Name | Tagline`), you get:

`Site Name | Tagline - Site Name`

Fix:

```js
title: (title) => {
  if (!title) return siteName
  if (title === siteName || title.includes(siteName)) return title
  return `${title} - ${siteName}`
}
```

Convention used here:

- **Home / full template titles** → pass the resolved full string (Head uses it as-is when it already contains the site name)
- **Catalogue / short labels** → pass `"Catalogue"` → becomes `Catalogue - Site Name`

---

## 4. Verification Checklist

1. Set SEO values in admin (title template, meta description, keywords).
2. Open the page in a **full reload** (not only SPA click).
3. **View Page Source** — confirm:
   - `<title>…</title>`
   - `<meta name="description" …>`
   - `<meta name="keywords" …>`
4. **Inspect** — confirm the same tags (possibly updated by Head).
5. Navigate away and back via Inertia links — title/description should still update.
6. Feature test the **HTML string** (not only Inertia props):

```php
$response = $this->get(route('home'));
$response->assertOk();
$response->assertSee(
    '<meta inertia="description" name="description" content="Best bakery in town.">',
    false,
);
```

Asserting on the raw HTML is what proves View Source correctness.

---

## 5. When to Choose Full SSR Instead

Use Inertia SSR when you need:

- Page-specific SEO that depends on heavy Vue-only content
- Fully rendered HTML body for every route (not just head tags)
- Maximum parity between source and hydrated DOM

For **global SEO + a few public landing pages**, the Blade + `withViewData` hybrid is usually the better cost/benefit.

---

## 6. Checklist for a New Project

- [ ] Store SEO fields (title template, description, keywords) somewhere queryable
- [ ] Share them on every Inertia request
- [ ] `documentSeo()` (or equivalent) returns `{ title, description, keywords }`
- [ ] `View::composer` sets default `$seo` for the root layout
- [ ] Root Blade outputs title + meta + OG with `inertia="…"` attributes
- [ ] Public controllers call `->withViewData(['seo' => …])` when the title must differ
- [ ] Vue pages use `<Head>` + `head-key` mirroring the same values
- [ ] Title callback does not double-append the site name
- [ ] Feature test asserts meta tags exist in the HTTP response HTML

---

## 7. Crave Bakery Reference Files

| Piece | Location |
|-------|----------|
| SEO resolver | `app/Services/SiteSettingService.php` (`resolveTitleTemplate`, `documentSeo`) |
| Default view data | `app/Providers/AppServiceProvider.php` (`View::composer('app', …)`) |
| Server-rendered tags | `resources/views/app.blade.php` |
| Home override | `app/Http/Controllers/HomeController.php` |
| Catalogue override | `app/Http/Controllers/ProductController.php` |
| Vue helper | `resources/js/Composables/useSiteSeo.js` |
| Page Head usage | `resources/js/Pages/Home.vue`, `resources/js/Pages/Products/Index.vue` |
| Title callback | `resources/js/app.js` |
| HTML assertion | `tests/Feature/Admin/SiteSettingsTest.php` |

---

## 8. Mental Model (Keep This)

> **View Page Source = first Laravel HTML.**  
> **Inspect = DOM after JavaScript.**  
>  
> If SEO must be visible in source, put it in Blade (or enable SSR).  
> If SEO must survive SPA navigations, also put it in Inertia `<Head>`.  
> Do both.

That dual write is the core fix — not a meta-tag syntax trick.
