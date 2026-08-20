# BigWein Owner Portal — Deployment Guide
Date: 2026-05-28

## What's New in This Package
- `/owner/register` — 3-step owner type registration (Seller / Builder)
- `/owner/login` — Owner login
- `/owner/dashboard` — Stats, listings, enquiries overview
- `/owner/my-properties` — Property grid with search/filter
- `/owner/post-property` — 7-step property listing form
- `/owner/property/{id}/edit` — Edit existing listing
- `/owner/subscription` — Plan upgrade (reads from packages table)
- `/owner/profile` — Profile + password management
- `/owner/enquiries` — Buyer enquiry table with Call/WhatsApp

## Files Changed / Added
```
CHANGED:
  app/Http/Kernel.php              → added 'owner.auth' middleware entry
  routes/frontend.php              → added all owner portal routes

ADDED:
  app/Http/Middleware/
    OwnerAuthMiddleware.php
  app/Http/Controllers/Frontend/
    OwnerAuthController.php
    OwnerDashboardController.php
    OwnerPropertyController.php
    OwnerSubscriptionController.php
  database/migrations/
    2026_05_28_000001_add_owner_fields_to_customers.php
    2026_05_28_000002_add_fields_to_propertys.php
  resources/views/frontend/owner/
    layouts/app.blade.php
    auth/login.blade.php
    register.blade.php
    dashboard.blade.php
    my-properties.blade.php
    post-property.blade.php
    subscription.blade.php
    profile.blade.php
    enquiries.blade.php
  public/frontend/css/owner.css
  public/frontend/js/owner.js
  bw_owner_migration.sql           ← SQL to run on DB
```

## Deployment Steps

### Step 1: Upload Files
Upload this entire folder to your server, overwriting existing files at:
`/home/u429100873/domains/codegensolutions.com/public_html/bigweinadmin/`

### Step 2: Run Database Migration
Go to phpMyAdmin → Select `u429100873_bigweinadmin` → SQL tab, paste and run:
`bw_owner_migration.sql`

OR via SSH:
```bash
cd /home/u429100873/domains/codegensolutions.com/public_html/bigweinadmin
mysql -u u429100873_bigweinadmin -p u429100873_bigweinadmin < bw_owner_migration.sql
```

### Step 3: Clear Cache
```bash
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Step 4: Test
1. Visit https://bigweinadmin.codegensolutions.com/owner/register
2. Select "Seller" or "Builder" → fill form → submit
3. Should land on /owner/dashboard
4. Try posting a property, viewing enquiries, etc.

## Notes
- `web.php` already has `require __DIR__.'/frontend.php';` — no change needed
- `bw.auth` middleware was already in Kernel.php — no change needed
- `owner.auth` middleware was added to Kernel.php in this release
- The SQL uses `ADD COLUMN IF NOT EXISTS` — safe to run even if columns partially exist
