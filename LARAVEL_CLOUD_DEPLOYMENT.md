# Laravel Cloud Deployment & Environment Switcher Guide

Paddle Field Sports Center supports an **on/off switch** between **DEV (Local SQLite)** and **PROD (MySQL / Laravel Cloud)**.

---

## 1. Quick On/Off Environment Switcher (Windows)

We have created fast, 1-click tools in your project root:

| Script / Command | Description |
| :--- | :--- |
| **`switch-env.bat`** | **Interactive Switcher**: Double-click to view active environment, flip between DEV and PROD, or run migrations. |
| **`set-dev.bat`** | **1-Click DEV**: Instantly sets `APP_ENV=local`, `APP_DEBUG=true`, and `DB_CONNECTION=sqlite`. |
| **`set-prod.bat`** | **1-Click PROD**: Instantly sets `APP_ENV=production`, `APP_DEBUG=false`, and `DB_CONNECTION=mysql`. |
| **`php artisan app:switch-env`** | **Artisan Command**: Run `php artisan app:switch-env dev` or `php artisan app:switch-env prod` or without arguments to toggle. |

---

## 2. Environment Presets

- **`.env.dev`**: Configured for local development:
  - `APP_ENV=local`
  - `APP_DEBUG=true`
  - `DB_CONNECTION=sqlite` (`database/database.sqlite`)
  - `LOG_LEVEL=debug`

- **`.env.prod`**: Configured for production / Laravel Cloud:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `DB_CONNECTION=mysql` (`paddle_field_sports`)
  - `LOG_LEVEL=error`

---

## 3. Deploying to Laravel Cloud

When deploying to [Laravel Cloud](https://cloud.laravel.com):

### Step A: Configure Environment Variables in Laravel Cloud
In your Laravel Cloud application dashboard (**Settings > Environment Variables**), set:

```env
APP_NAME="Paddle Field Sports Center"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.laravel.cloud
APP_KEY=base64:7bHXpMWtvlQSlItIGiTSIJlGMLZ1DK3nKYT5ytixI3g=

# Database Settings (Provided by Laravel Cloud Managed MySQL)
DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=741229
REVERB_APP_KEY=fb55jppym2ho4c4cpqwz
REVERB_APP_SECRET=wsl34vwiosjkzmyg4i59
REVERB_HOST="your-domain.laravel.cloud"
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# PayMongo Gateway Keys (Optional if entered via Owner Settings Dashboard)
PAYMONGO_SECRET_KEY=sk_live_...
PAYMONGO_PUBLIC_KEY=pk_live_...
PAYMONGO_WEBHOOK_TOKEN=whsk_...
```

### Step B: Build Hook
In Laravel Cloud deployment settings, configure the build script:
```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
```

### Step C: Deploy / Post-Deploy Hook
Configure the command to run after deployment finishes:
```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step D: Database Seeding (First-Time Deploy Only)
To seed the initial admin accounts, courts, and venue settings:
```bash
php artisan db:seed --force
```

---

## 4. Default Seeded Credentials
- **Court Owner**: `owner@paddlefield.com` / `password123`
- **Admin Assistant**: `assistant@paddlefield.com` / `password123`
- **System Admin**: `admin@paddlefield.com` / `password123`
- **Player Demo**: `marcus@example.com` / `password123`
