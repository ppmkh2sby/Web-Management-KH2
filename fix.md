# Task: Performance Optimization — Login Speed & Page Navigation

## Context
This is a Laravel-based web management system (`ppmkh2sby/Web-Management-KH2`).
Users are experiencing slow login redirects and slow navigation between feature pages.
After a full code audit, the following root causes were identified and must ALL be fixed.

---

## 1. Fix Session & Cache Driver (`.env` + `config/`)

### Problem
`config/session.php` and `config/cache.php` both default to `database` driver.
This causes extra DB queries on every single request just to read/write the session.

### Action
**File: `config/session.php`** — change default driver:
```php
// BEFORE:
'driver' => env('SESSION_DRIVER', 'database'),

// AFTER:
'driver' => env('SESSION_DRIVER', 'file'),
```

**File: `config/cache.php`** — change default store:
```php
// BEFORE:
'default' => env('CACHE_STORE', 'database'),

// AFTER:
'default' => env('CACHE_STORE', 'file'),
```

---

## 2. Remove Redundant Redirect Chain After Login

### Problem
After login, the flow for santri is:
`POST /login → redirect('santri.dashboard') → redirect('santri.home')`
That is 2 HTTP round-trips just to land on the home page.

### Action
**File: `routes/web.php`** — remove the intermediate alias redirect:
```php
// REMOVE this line entirely:
Route::get('/dashboard', fn () => redirect()->route('santri.home'))->name('dashboard');
```

**File: `app/Support/RedirectPath.php`** (or wherever `RedirectPath::forUser()` is defined) — make sure the `santri` role redirects DIRECTLY to `route('santri.home')`, not via `santri.dashboard`:
```php
// BEFORE (example):
'santri' => route('santri.dashboard'),

// AFTER:
'santri' => route('santri.home'),
```

Also in **`app/Http/Controllers/Auth/LoginController.php`**, update the match block:
```php
// BEFORE:
'santri' => redirect()->route('santri.dashboard'),

// AFTER:
'santri' => redirect()->route('santri.home'),
```

---

## 3. Optimize Dashboard Queries — Replace Multiple COUNT Queries with Single GROUP BY

### Problem
**File: `app/Http/Controllers/Santri/DashboardController.php`**

In `home()` method, both the santri section (lines ~144–196) and staff section (lines ~198–272)
run 5 separate `count()` queries for attendance statuses. This should be 1 query.

### Action
**For santri attendance stats** — replace the 5 separate `count()` calls:
```php
// REMOVE these 5 lines:
$attendanceStats['total'] = (clone $attendanceBase)->count();
$attendanceStats['hadir'] = (clone $attendanceBase)->where('status', 'hadir')->count();
$attendanceStats['izin']  = (clone $attendanceBase)->where('status', 'izin')->count();
$attendanceStats['sakit'] = (clone $attendanceBase)->where('status', 'sakit')->count();
$attendanceStats['alpha'] = (clone $attendanceBase)->where('status', 'alpha')->count();
$attendanceStats['persentase'] = ...

// REPLACE WITH:
$attendanceCounts = (clone $attendanceBase)
    ->selectRaw('status, count(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$attendanceStats['hadir']  = (int) ($attendanceCounts['hadir'] ?? 0);
$attendanceStats['izin']   = (int) ($attendanceCounts['izin'] ?? 0);
$attendanceStats['sakit']  = (int) ($attendanceCounts['sakit'] ?? 0);
$attendanceStats['alpha']  = (int) ($attendanceCounts['alpha'] ?? 0);
$attendanceStats['total']  = $attendanceStats['hadir'] + $attendanceStats['izin']
                           + $attendanceStats['sakit'] + $attendanceStats['alpha'];
$attendanceStats['persentase'] = $attendanceStats['total'] > 0
    ? (int) round(($attendanceStats['hadir'] / $attendanceStats['total']) * 100)
    : 0;
```

**For staff attendance stats** — apply the same GROUP BY refactor to the staff block (lines ~202–213), same pattern as above but using the global `Presensi::query()` without the `santri_id` filter.

**For `staffProgressRows`** — add a limit to prevent loading ALL rows:
```php
// BEFORE:
$staffProgressRows = ProgressKeilmuan::query()
    ->with('santri:id,nama_lengkap,tim,code')
    ->get();

// AFTER:
$staffProgressRows = ProgressKeilmuan::query()
    ->with('santri:id,nama_lengkap,tim,code')
    ->limit(500)
    ->get();
```

**For `staffLogRows`** — add a limit:
```php
// BEFORE:
$staffLogRows = LogKeluarMasuk::query()
    ->with('santri:id,nama_lengkap,gender,tim,code')
    ->latest('tanggal_pengajuan')
    ->latest('id')
    ->get();

// AFTER:
$staffLogRows = LogKeluarMasuk::query()
    ->with('santri:id,nama_lengkap,gender,tim,code')
    ->latest('tanggal_pengajuan')
    ->latest('id')
    ->limit(200)
    ->get();
```

---

## 4. Fix Double Alpine.js Load

### Problem
Alpine.js is already bundled inside `resources/js/app.js` via npm.
But the layout also loads Alpine from CDN via a `<script>` tag, causing it to load TWICE.

### Action
**File: `resources/views/layouts/app.blade.php`** — REMOVE this line:
```html
<!-- REMOVE THIS LINE: -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```
Keep only the `@vite(...)` directive. The Alpine that's already in `app.js` is sufficient.

---

## 5. Fix Lucide Icons Loaded from External CDN

### Problem
**File: `resources/views/layouts/santri-modern.blade.php`** loads Lucide from `unpkg.com` CDN
on every page navigation. This creates an external network request per page load.

### Action

**Step 1** — Install lucide via npm:
```bash
npm install lucide
```

**Step 2** — **File: `resources/js/app.js`** — import and initialize lucide:
```js
import './bootstrap';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
Alpine.start();

// Initialize lucide icons on every page load
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});
```

**Step 3** — **File: `resources/views/layouts/santri-modern.blade.php`** — REMOVE the CDN script tag:
```html
<!-- REMOVE THIS LINE: -->
<script defer src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<!-- ALSO REMOVE the inline init script below it: -->
<script>
    document.addEventListener('DOMContentLoaded', () => { window.lucide?.createIcons?.(); });
</script>
```

---

## 6. Move `loadMissing` from Layout View to Middleware or Base Controller

### Problem
**File: `resources/views/layouts/santri-modern.blade.php`** runs `loadMissing('santri')` on
the authenticated user on every single page render. This triggers a DB query every page navigation.

```php
// This runs on EVERY page using santri-modern layout:
$currentUser?->loadMissing('santri');
```

### Action

**Option A (recommended)** — Create a middleware that eagerly loads the `santri` relation once per request:

**New file: `app/Http/Middleware/LoadUserRelations.php`**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoadUserRelations
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            auth()->user()->loadMissing('santri');
        }

        return $next($request);
    }
}
```

**File: `bootstrap/app.php`** — register the middleware in the `web` group:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\LoadUserRelations::class,
    ]);
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

**File: `resources/views/layouts/santri-modern.blade.php`** — REMOVE the manual `loadMissing` call:
```php
// REMOVE:
$currentUser?->loadMissing('santri');
```
The user with `santri` relation will already be loaded by the middleware.

---

## 7. Add Laravel Route, Config & View Caching (Production Optimization)

### Problem
Without caching, Laravel re-parses all route files, config files, and Blade views on every request.

### Action
Add the following commands to the deployment script or run them manually on the server after each deploy:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

If there is a `Makefile` or `deploy.sh`, add these commands there.
If not, create a new file:

**New file: `deploy.sh`**
```bash
#!/bin/bash
echo "Running deployment optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
npm run build
echo "Done."
```

---

## 8. Rebuild Frontend Assets

### Action
After all JS/CSS changes above, run:
```bash
npm run build
```
This ensures Alpine and Lucide are bundled properly in production mode, not served in slow dev mode.

---

## Summary Checklist

- [ ] `config/session.php` → default driver changed to `file`
- [ ] `config/cache.php` → default store changed to `file`
- [ ] `routes/web.php` → removed intermediate `santri.dashboard` alias redirect
- [ ] `app/Http/Controllers/Auth/LoginController.php` → santri redirects to `santri.home` directly
- [ ] `app/Support/RedirectPath.php` → santri redirects to `santri.home` directly
- [ ] `DashboardController.php` → attendance stats use 1 GROUP BY query instead of 5 count queries
- [ ] `DashboardController.php` → `staffProgressRows` has `->limit(500)`
- [ ] `DashboardController.php` → `staffLogRows` has `->limit(200)`
- [ ] `layouts/app.blade.php` → removed duplicate Alpine CDN script tag
- [ ] `layouts/santri-modern.blade.php` → removed Lucide CDN script tag and inline init
- [ ] `resources/js/app.js` → Lucide imported from npm and initialized
- [ ] New `LoadUserRelations` middleware created and registered
- [ ] `layouts/santri-modern.blade.php` → removed `loadMissing('santri')` call
- [ ] `deploy.sh` created with artisan cache commands
- [ ] `npm run build` executed after JS changes

## Important Notes for the Agent
- Do NOT change business logic, only change the parts explicitly described above
- Do NOT remove or rename any routes, only change their targets
- After making changes to `routes/web.php`, verify that `santri.home` route still exists and is not broken
- All `limit()` values (500, 200) are safe defaults — do not reduce them further without testing
- The `loadMissing` middleware should only call `loadMissing`, not `load` (to avoid re-querying if already loaded)