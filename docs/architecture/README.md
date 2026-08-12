# Architecture Wiki (arch-wiki)

Living architecture map for **Crave Bakery**, generated with the [arch-wiki](https://github.com/ahmedemad3/arch-wiki) skill adapted for Laravel 13 + Inertia.

## Files

| File | Purpose |
|---|---|
| `architecture.json` | Machine-readable manifest (modules, routes, RBAC, services). Edit via the Laravel sync helper — do not invent endpoints. |
| `architecture.html` | Interactive dashboard. **Never edit by hand** — regenerate after JSON changes. |
| `build_html.py` | Upstream HTML generator (from arch-wiki). |
| `_build_manifest.py` | Laravel adapter: maps `php artisan route:list` into `architecture.json`. |
| `sync_laravel.py` | **Preferred sync entrypoint** for this project. |
| `_routes_raw.json` | Temporary route export (optional / regenerable). |

Cursor skill copy (instructions for the agent):

- [`.cursor/skills/arch-wiki/SKILL.md`](../../.cursor/skills/arch-wiki/SKILL.md)
- [`.cursor/skills/arch-wiki/templates/build_html.py`](../../.cursor/skills/arch-wiki/templates/build_html.py)

## Prerequisites

- Python 3.8+ (`python --version`)
- PHP **>= 8.4.1** for `php artisan route:list` (Laravel Herd `php84` recommended; avoid older XAMPP `php.exe` on PATH)
- On Windows, prefer UTF-8 output:

```powershell
$env:PYTHONIOENCODING = "utf-8"
$env:PYTHONUTF8 = "1"
```

`sync_laravel.py` auto-selects the newest usable PHP binary (Herd `php84` preferred).

## First-time / refresh (Laravel-safe)

From the project root:

```powershell
python docs/architecture/sync_laravel.py
```

This will:

1. Export routes with `php artisan route:list --json`
2. Rebuild `architecture.json` for Laravel/Inertia modules + permissions
3. Regenerate `architecture.html`

Then open `docs/architecture/architecture.html` in a browser.

## HTML-only regenerate

If you only tweaked `architecture.json` by hand:

```powershell
python docs/architecture/build_html.py
```

## Important: do not use upstream `--sync` here

```powershell
# UNSAFE for this repo — wipes Laravel data
python docs/architecture/build_html.py --sync
```

The upstream scanner does not understand Laravel route files (`web.php` / `admin.php` / `auth.php`). It mis-detects **FastAPI**, finds **0 endpoints**, and overwrites `architecture.json`.

Always use:

```powershell
python docs/architecture/sync_laravel.py
```

## Prompt the AI after code changes

In Cursor Agent mode:

> Run the arch-wiki skill using the Laravel-safe sync. Execute `python docs/architecture/sync_laravel.py`, then summarize any new modules/endpoints.

Or after adding a specific admin module:

> Update architecture docs for the new [module]. Prefer `docs/architecture/sync_laravel.py` over `build_html.py --sync`.

## What is documented

- Public catalogue, cart, checkout, payments, profile/account
- Auth (Breeze)
- Admin dashboard, products, categories, attributes, orders, reviews, customers, settings/payments, users
- Middleware (`admin`, `super-admin`, Inertia share)
- RBAC from `config/permissions.php`
- Core services under `app/Services`
- Empty Docker topology (no `docker-compose.yml`)
- No public OpenAPI/Swagger server (Inertia web app)

## Route parity notes

- Documented app modules cover named application routes from `web.php`, `auth.php`, and `admin.php`.
- Framework/dev routes (`/up`, Debugbar, Sanctum CSRF cookie, storage) stay under `systemEndpoints`.
- Duplicate unnamed `PATCH /addresses/{address}` may appear as a system route alongside the named `addresses.update` PUT route.
