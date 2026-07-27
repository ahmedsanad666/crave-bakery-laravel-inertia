# Hostinger Deploy Guide — Vendify / Crave Bakery

How to push backend and frontend updates to the Hostinger subdomain.

| Item | Value |
|------|--------|
| Site URL | https://vendify.techsquare.dev |
| SSH | `ssh -p 65002 u589889954@2.57.89.6` |
| App path on server | `/home/u589889954/domains/techsquare.dev/public/vendify` |
| Document root | `.../vendify/public` (subdomain points here) |
| Git remote | `https://github.com/ahmedsanad666/crave-bakery-laravel-inertia.git` |
| Branch | `master` |

Hostinger shared hosting has **no Node/npm**. Build frontend assets on your local machine, then upload `public/build`.

---

## First-time setup (already done once)

These steps were required for the initial deploy. Keep them for reference if you recreate the site.

1. Clone the repo into the app path (or pull into an existing clone).
2. Copy env and configure DB / `APP_URL`:
   ```bash
   cp .env.example .env
   # Edit .env: APP_URL, DB_*, APP_ENV=production, APP_DEBUG=false
   php artisan key:generate --force
   ```
3. Install PHP deps (if not already installed):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
4. Run migrations:
   ```bash
   php artisan migrate --force
   ```
5. Create the storage link **manually** (Hostinger disables `exec()`, so `php artisan storage:link` fails):
   ```bash
   cd /home/u589889954/domains/techsquare.dev/public/vendify
   rm -rf public/storage
   ln -s "$(pwd)/storage/app/public" "$(pwd)/public/storage"
   chmod -R 775 storage bootstrap/cache
   ```
6. Upload a local Vite build (see Frontend section below).

---

## Backend updates (PHP, routes, migrations, config)

Run these on your **local** machine first, then on the **server**.

### 1. Local — commit and push

```bash
git add .
git commit -m "Your message"
git push origin master
```

### 2. Server — pull and apply

```bash
ssh -p 65002 u589889954@2.57.89.6
cd /home/u589889954/domains/techsquare.dev/public/vendify

git pull origin master

composer install --no-dev --optimize-autoloader

php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Optional: speed up production after changes
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Notes

- Always use `migrate --force` in production (avoids interactive prompts; Hostinger also lacks `proc_open()` used by some prompts).
- Do **not** commit `.env`. Keep production secrets only on the server.
- If `composer` or `php` version errors appear, set the hosting PHP version to **8.4+** in hPanel (matches this project).

---

## Frontend updates (Vue, CSS, JS, Inertia pages)

Hostinger cannot run `npm`. Build locally, then upload.

### 1. Local — build

From the project root (`E:\freelancePro\pastacı-website`):

```powershell
npm run build
```

Ignore `[INVALID_ANNOTATION]` / `[PLUGIN_TIMINGS]` messages from Vite/Rolldown — the build still succeeds when you see `✓ built`.

### 2. Local — upload `public/build`

Run this on your **local PC** (not inside the SSH session):

```powershell
scp -P 65002 -r public/build u589889954@2.57.89.6:/home/u589889954/domains/techsquare.dev/public/vendify/public/
```

### 3. Browser

Hard-refresh: **Ctrl+F5** on https://vendify.techsquare.dev/

### Verify on server (optional)

```bash
ls /home/u589889954/domains/techsquare.dev/public/vendify/public/build/manifest.json
```

---

## Typical deploy checklist

| Change type | What to do |
|-------------|------------|
| PHP / Laravel only | `git push` → server `git pull` → `migrate --force` → clear/cache config |
| Vue / CSS / JS only | Local `npm run build` → `scp` `public/build` |
| Both | Do backend steps, then frontend build + `scp` |
| New uploaded images | Already go to `storage/app/public` via the symlink; no build needed |
| Seeders | Server: `php artisan db:seed --force` (only when you intend to seed) |

---

## Common errors

### `500 | Server Error` — missing `APP_KEY`

Log: `No application encryption key has been specified.`

```bash
cd /home/u589889954/domains/techsquare.dev/public/vendify
php artisan key:generate --force
php artisan config:clear
```

### `500` — Vite manifest not found

Log: `Vite manifest not found at: .../public/build/manifest.json`

Build locally and `scp` `public/build` (see Frontend section).

### `npm: command not found` on the server

Expected. Do not run `npm` on Hostinger. Build on your PC and upload.

### `Call to undefined function ... exec()` / `storage:link` fails

Create the symlink manually (see First-time setup step 5).

### Duplicate migration / table already exists

Ensure only one create migration exists for each table. After fixing in git, on the server:

```bash
git pull origin master
# Repair only if a bad/partial table was left behind — example for payment_gateways:
php artisan tinker --execute="Illuminate\Support\Facades\Schema::dropIfExists('payment_gateways'); Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%145333_create_payment_gateways%')->delete();"
php artisan migrate --force
```

### Local Git Bash `php` is 8.2 but project needs 8.4+

Use Herd’s PHP, or run artisan from a terminal where `php -v` shows 8.4+:

```powershell
herd php artisan migrate
```

---

## Quick reference commands

**Local frontend deploy**

```powershell
npm run build
scp -P 65002 -r public/build u589889954@2.57.89.6:/home/u589889954/domains/techsquare.dev/public/vendify/public/
```

**Server backend deploy**

```bash
cd /home/u589889954/domains/techsquare.dev/public/vendify
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
```
