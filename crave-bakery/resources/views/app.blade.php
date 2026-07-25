<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $themeService = app(\App\Services\SiteSettingService::class);
            $siteSettings = $themeService->publicPayload();
            $themeCss = $themeService->themeCssProperties();
            $themeFontsHref = $themeService->googleFontsHref();
            $faviconUrl = $siteSettings['favicon'] ?? null;
            $siteName = $siteSettings['site_name']
                ?? $siteSettings['name']
                ?? config('app.name', 'Crave Bakery');
            $seo = $seo ?? $themeService->documentSeo();
            $seoTitle = $seo['title'] ?? $siteName;
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

        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
            <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
        @endif

        <style>
            :root {
                @foreach ($themeCss as $property => $value)
                    {{ $property }}: {{ $value }};
                @endforeach
            }
        </style>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link id="app-theme-fonts" href="{{ $themeFontsHref }}" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
