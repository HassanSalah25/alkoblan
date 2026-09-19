# AL-KOBLAN Thermopipe Factory — Backend, CMS & Mini E-commerce

A full Laravel 11 backend that powers the AL-KOBLAN Thermopipe Factory website: a CMS (pages, homepage sections, menus, blog, events, FAQs, careers), a mini e-commerce catalog (categories, products, attributes/variants, cart, checkout, orders), a media library, an admin dashboard with role/permission-based access, and a public JSON API — all serving the original static frontend (now converted to server-rendered Blade views so the design is preserved pixel-for-pixel).

## 1. Stack

- PHP 8.2+, Laravel 11
- MySQL 8+ (developed against MariaDB 10.4 via XAMPP — fully compatible)
- Spatie `laravel-permission` for roles/permissions
- Laravel Sanctum (available for token-based API auth; the public API itself is unauthenticated/read-mostly plus a few throttled write endpoints)
- Blade for both the public storefront and the admin panel (no separate JS framework/build step required)

## 2. Requirements

- PHP >= 8.2 with the `pdo_mysql`, `mbstring`, `fileinfo`, `gd` extensions
- Composer 2.x
- MySQL 8+ or MariaDB 10.4+
- Node is **not** required to run the site (no frontend build step — the original `css/`, `js/`, `images/` assets are served directly from `public/`)

## 3. Installation

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

## 4. Environment setup

Edit `.env`:

```env
APP_URL=http://localhost:8000
APP_LOCALE=ar          # default site language (ar or en)
SUPPORTED_LOCALES=ar,en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alkoblan_db
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

## 5. Database setup

Create the database (MySQL/MariaDB):

```sql
CREATE DATABASE alkoblan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Migration commands

```bash
php artisan migrate
```

This creates ~40 tables covering: users/roles/permissions, settings, media library, menus, pages, homepage CMS (hero slides, content blocks, testimonials, famous clients), product catalog (categories, products, images, files, attributes, attribute values, variants), commerce (carts, cart items, orders, order items, payment transactions), branches, contact messages, FAQs, blog (categories, tags, posts), events, and careers (job openings, applications).

### Seeder commands

```bash
php artisan db:seed
```

Seeds, in order: roles & permissions, the super-admin user, site settings, the main/footer menus, branches (Riyadh/Jeddah/Dammam), the base media library entries (the original 6 stock photos), product categories & attributes (Diameter, Pressure Rating, Color, Material) & ~26 sample products with variants, homepage CMS content (hero slides, about/quality/CTA blocks, testimonials, famous clients), FAQs, blog posts & events, career listings, and CMS pages (About, Company Profile, Privacy Policy, Terms & Conditions).

To reset and reseed from scratch during development:

```bash
php artisan migrate:fresh --seed
```

**Never run `migrate:fresh` against a production database** — it drops every table.

## 6. Admin credentials

After seeding, log in to the admin panel at **`/admin/login`**:

- Email: `admin@alkoblan.com.sa`
- Password: `Password123!`

**Change this password immediately after your first deployment.** The seeded account has the `Super Admin` role (every permission). Other seeded roles — `Admin`, `Content Manager`, `Product Manager`, `Editor`, `Customer` — can be assigned to new users from **Admin → Settings → Users & Roles**.

Customer-facing accounts (storefront registration at `/register`) are a completely separate authentication flow from admin accounts — a `type=customer` user can never log into `/admin`, even with valid credentials, and a `type=admin` user is not meant to shop.

## 7. Storage setup

Product images, media library uploads, and job-application CVs are stored on the `public` disk. Link it once per environment:

```bash
php artisan storage:link
```

This creates `public/storage` → `storage/app/public`. Uploaded files are then served at `/storage/...`.

The original static frontend's `css/`, `js/`, and `images/` folders were copied verbatim into `public/` so every relative asset path in the converted Blade views (`asset('css/styles.css')`, etc.) resolves without any build step.

## 8. Running the app locally

```bash
php artisan serve
```

Visit `http://localhost:8000` for the storefront and `http://localhost:8000/admin` for the admin panel.

## 9. Frontend configuration

- The public storefront lives in `resources/views/pages/*.blade.php`, extending `resources/views/layouts/app.blade.php`, which includes the shared `partials/header`, `partials/mobile-menu`, and `partials/footer` — all fed dynamically from the `menu_items` and `settings` tables via a view composer (`App\View\Composers\SiteLayoutComposer`).
- Bilingual content uses **dual columns** per model (e.g. `name` / `name_ar`) rather than separate translation tables — the `trans_field($model, 'field')` helper (in `app/helpers.php`) picks the right one for the active locale. UI chrome strings use Laravel's `__()` helper with a flat JSON dictionary at `resources/lang/ar.json` (English is the fallback/base language for keys; Arabic is the default active locale, matching the original site).
- Switch language via `/lang/{ar|en}` (stored in session, applied by `App\Http\Middleware\SetLocale`).
- The cart is server-side (`carts`/`cart_items` tables, resolved by session id via `App\Services\WebCart`), and `public/js/cart.js` calls `POST /cart/add` via `fetch()` for "add to cart" buttons (`data-add-to-cart` + `data-product-id` on every product card) while the cart/checkout pages themselves are fully server-rendered.
- A separate, stateless **JSON API** (see below) exists under `/api/*` for any external/mobile client, backed by its own services (`app/Services/CartService.php`, `OrderService.php`, etc.) that mirror the same business rules independently of the web session-based cart.

## 10. Admin panel

Sidebar structure: Dashboard · Content (Pages, Homepage, Menus, FAQs, Blogs, Events, Careers) · Products (Categories, Products, Attributes, Product Files) · Commerce (Orders, Customers) · Locations (Branches) · Media (Media Library) · Communication (Contact Messages, Career Applications) · Settings (General, SEO, Social Media, Users & Roles).

Every module has full CRUD with search/filter/pagination and permission-gated routes (`permission:{module}.{view|create|update|delete}` middleware, matching the seeded Spatie permissions). Sidebar links are hidden for roles that lack the corresponding permission.

## 11. API documentation

Base path: `/api`. Every response follows:

```json
// success
{ "success": true, "message": "...", "data": {}, "meta": {} }
// error
{ "success": false, "message": "...", "errors": {} }
```

| Method | Endpoint | Notes |
|---|---|---|
| GET | `/api/settings` | Public site settings (contact, social, SEO defaults, etc.) |
| GET | `/api/menus?location=main` | `main`, `mobile`, `footer_quick`, `footer_products`, `footer_bottom` |
| GET | `/api/homepage` | Hero slides, content blocks, testimonials, clients, featured categories/products in one call |
| GET | `/api/pages/{slug}` | Published CMS page |
| GET | `/api/categories`, `/api/categories/{slug}` | Category tree with recursive product counts |
| GET | `/api/products` | Filters: `category`, `q`, `attribute[]`, `min_price`/`max_price`, `featured`, `sort`, `per_page` |
| GET | `/api/products/{slug}` | Full detail: images, files, attributes, variants, related products |
| GET | `/api/blogs`, `/api/blogs/{slug}` | Filters: `category`, `tag`, `q` |
| GET | `/api/events`, `/api/events/{slug}` | Filters: `featured`, `upcoming` |
| GET | `/api/faqs` | Filter: `category` |
| GET | `/api/branches` | Active branches |
| POST | `/api/contact` | Throttled 10/min |
| GET/POST/PATCH/DELETE | `/api/careers`, `/api/careers/{slug}`, `/api/careers/{slug}/apply` | Apply is throttled and accepts a CV upload |
| GET/POST/PATCH/DELETE | `/api/cart` | Guest cart keyed by a `cart_token` (returned in every response; send it back via `X-Cart-Token` header or query param) |
| POST | `/api/orders` | Checkout from a `cart_token` or a direct `items[]` array; throttled |
| GET | `/api/orders/{order_number}` | Order lookup by order number |

## 12. Testing

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`) so they never touch the real MySQL data. Coverage includes: storefront pages, shop category filtering, product detail pages, the full guest cart → checkout → order-creation flow (with correct 15% VAT math), contact form validation, and customer registration/login/authorization.

## 13. Production deployment

1. `composer install --no-dev --optimize-autoloader`
2. Set `APP_ENV=production`, `APP_DEBUG=false`, a strong unique `APP_KEY`, and real mail/DB credentials in `.env`.
3. `php artisan migrate --force`
4. `php artisan db:seed --force` (first deploy only — seeds the initial admin user, roles, settings, menus, and starter catalog/content)
5. `php artisan storage:link`
6. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Point your web server's document root at `public/`.
8. Serve over HTTPS and set `SESSION_SECURE_COOKIE=true` / `SESSION_ENCRYPT=true` in `.env` for production traffic.
9. Change the seeded admin password immediately.

## 14. Queue setup

`QUEUE_CONNECTION=database` is configured out of the box (the `jobs` table is migrated). No jobs are queued by default in this build, but the infrastructure is ready for future async work (e.g. order confirmation emails) — run a worker when you add any:

```bash
php artisan queue:work --tries=3
```

## 15. Cron setup

Laravel's scheduler is wired via `routes/console.php`. Point your server's cron at it once per minute (no scheduled tasks are defined yet, but this is required for any future ones, e.g. scheduled blog publishing):

```
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```

## 16. Security notes

- CSRF protection is on for every web form; the JSON API is stateless and uses per-endpoint request validation instead.
- Passwords are hashed via Laravel's `hashed` cast (bcrypt).
- File uploads (media library, job application CVs) validate MIME type and size.
- `POST /contact`, `POST /careers/{slug}/apply`, and `POST /api/orders` are rate-limited.
- Never commit a real `.env` file. `APP_KEY`, DB credentials, and mail credentials are read from environment variables only.
