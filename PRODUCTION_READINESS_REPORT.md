# Production Readiness Report

## A. Project audit summary

The local XAMPP database configuration and both form tables have now been set up and exercised. This verifies local integration only; the local `config.php` must not be uploaded to a public host. Hostinger/live database, email delivery, and production-host behavior remain unverified.

**Production readiness: NOT VERIFIED**

The required contact and service-request database INSERT/SELECT paths have not been verified against the live database. Hostinger behavior and email delivery are also unverified, so production cannot be certified from local tests.

## B. Issues discovered

- The application loaded its database settings from `.env`/environment variables and only built PostgreSQL connections.
- README described `database/schema.sql`, but no schema file existed.
- `gravityhub_backup.sql` exists but is empty (0 bytes), so it contains no usable schema or data for migration.
- The database INSERT code uses the `contact_messages` and `service_requests` tables.
- The current notification implementation calls PHP `mail()`; authenticated SMTP is not implemented.
- The available local PHP has `pdo_mysql` enabled but not `pdo_pgsql`.
- The existing Dockerfile was PostgreSQL-specific and used port 10000, not the Hostinger shared-PHP setup requested.

## C. Issues fixed

- Replaced `.env` parsing and PostgreSQL-only connection setup with a PHP-returned configuration array supporting PDO MySQL/MariaDB and PostgreSQL.
- Added a MySQL/MariaDB schema for the two form tables used by existing INSERT statements.
- Updated database setup instructions and added Hostinger upload, configuration, backup, and troubleshooting steps.
- Removed the obsolete `.env.example` and Docker deployment files. The existing `.env` was left untouched but is no longer read by the application.
- Added canonical/Open Graph metadata and Organization, WebSite, BlogPosting, and breadcrumb structured data, with a validated HTTPS site origin from either the private production config or an environment variable.
- Added robots and sitemap endpoints plus Apache rewrites; the sitemap intentionally requires a configured site origin.
- Removed placeholder social links, added `noopener` to external new-tab links, and restored intended component borders by removing an overriding CSS reset.
- Deferred decorative video loading/playback until near the viewport, respecting reduced-motion and Data Saver preferences.
- Re-encoded the site's image and decorative video assets; the media set is now about 2.08 MiB total.

## D. Files created, modified, and removed

**Created**

- `config.php` — blank local configuration values; excluded by `.gitignore`.
- `config.example.php` — non-secret template to copy and configure.
- `database/schema.sql` — MySQL/MariaDB definitions for form storage.
- `HOSTINGER_DEPLOYMENT.md` — setup and post-upload guide.
- `PRODUCTION_READINESS_REPORT.md` — this evidence-based status report.

**Modified**

- `includes/database.php` — reads `config.php`, validates settings, and creates PDO MySQL/PostgreSQL connections.
- `includes/form-handler.php` and `request-service.php` — server-side form validation, country-aware phone normalization, and validation feedback.
- `includes/head.php`, `includes/site-url.php`, and `config.example.php` — SEO metadata, structured data, and validated site-origin handling.
- `assets/css/style.css`, `assets/js/main.js`, `index.php`, `about.php`, `services.php`, and `contact.php` — targeted UI, asset-loading, link, and country-selector improvements.
- `.htaccess`, `README.md`, and this report — crawler/cache rules and setup/validation documentation.
- `.gitignore` — excludes the private `config.php`.

**Added**

- `robots.php` and `sitemap.php` — crawler endpoints, rewritten to `/robots.txt` and `/sitemap.xml`.
- `includes/phone-country-data.php` — 58 supported country calling codes and national number length rules.
- Optimized image and decorative video files under `assets/images/`.

**Removed**

- `.env.example` — superseded by `config.example.php`.
- `Dockerfile` and `.dockerignore` — not needed for the requested shared PHP hosting target; the Dockerfile used PostgreSQL and port 10000.

No Git commit, push, or deployment was performed by the assistant. The user previously reported deploying the site, but the live version was not inspected or verified in this work.

## E. Database engine and migration status

- Default config driver: MySQL/MariaDB.
- Connection code: PDO `mysql` and `pgsql` drivers.
- The MySQL PDO extension is present in local PHP; the PostgreSQL PDO extension is not.
- Hostinger database support and actual connection details must be confirmed in hPanel.
- No migration was performed. `gravityhub_backup.sql` is empty.
- `database/schema.sql` is for MySQL/MariaDB only; PostgreSQL schema setup is not provided.

## F. Required database tables

- `contact_messages`
- `service_requests`

Both tables are created with InnoDB, `utf8mb4`, primary keys, and relevant lookup indexes. The schema uses `CREATE TABLE IF NOT EXISTS` and does not drop existing tables.

## G. Contact form testing

| Test | Expected result | Actual result | Status |
|---|---|---|---|
| Contact form INSERT | Valid submission is persisted in `contact_messages` | Local XAMPP form handler inserted a test submission | PASS (local only) |
| Contact form SELECT | Submitted row can be retrieved from the database | Local SELECT found the exact test row; the test row was then removed | PASS (local only) |
| Country code and phone validation | Chosen country code is stored with a valid phone; malformed or overlong phones are rejected | 58 country choices render; 13-digit India number rejected; valid India number stored as `+91…`; invalid email, country, length, input type, and UTF-8 rejected | PASS (local handler/browser tests) |
| Required fields and CSRF | Missing required values and invalid CSRF are rejected | Missing name/message and invalid CSRF rejected; no invalid request reached the database | PASS (local handler tests) |

## H. Get Service form testing

| Test | Expected result | Actual result | Status |
|---|---|---|---|
| Service request INSERT | Valid submission is persisted in `service_requests` | Local XAMPP form handler inserted a test submission | PASS (local only) |
| Service request SELECT | Submitted row can be retrieved from the database | Local SELECT found the exact test row; the test row was then removed | PASS (local only) |
| Country code and phone validation | Chosen country code is stored with a valid phone; malformed or overlong phones are rejected | US number stored normalized as `+1…`; invalid phone, country, service/material/contact enums rejected | PASS (local handler/browser tests) |
| Required fields and CSRF | Missing required values and invalid CSRF are rejected | Missing required values and invalid CSRF rejected; no invalid request reached the database | PASS (local handler tests) |

## I. Email delivery testing

Authenticated SMTP delivery was **not tested**. Existing code uses PHP `mail()` and has no authenticated SMTP integration. Do not rely on email notifications until SMTP is implemented/configured and a real delivery test succeeds. Database persistence should be verified independently.

## J. Security audit results

- `config.php` is ignored by Git and direct HTTP requests to configuration files are denied by the root `.htaccess`.
- `includes/` and `database/` have access-denial rules.
- Existing form code uses PDO prepared statements and CSRF tokens, but a full security review and regression test were not performed.
- Apache rule behavior must be confirmed on the actual hosting account.
- Rate limiting, duplicate-submission protection, SMTP security, and other controls from the full project brief remain unverified.

## K. Performance and load-testing results

The images total about 1.67 MiB and the decorative video is about 0.41 MiB (640x360 H.264, no audio), approximately 2.08 MiB together. The ten re-encoded images were reduced from about 8.9 MB to 1.1 MB; the video was reduced from 9.77 MB to about 0.41 MB. Static assets returned `Cache-Control: public, max-age=604800`. The browser confirmed the video was not requested at initial page view and loaded/played after scrolling it near the viewport.

No Lighthouse/Core Web Vitals audit, load test, or response-time measurement was run. No concurrency, error-rate, CPU, memory, database throughput, or guaranteed performance-score claim is made.

## L. UI/UX improvements

Removed an accidental global CSS rule that forced every element border to zero, restoring component and form borders. Removed nonfunctional placeholder social links and added `rel="noopener"` to new-tab external links. This was a targeted fix, not a full visual redesign.

## M. Browser and device testing

Local smoke checks covered all nine public/article routes, responsive layout at 320, 375, 768, 1024, and 1440 px, the mobile menu, image alternatives, deferred video loading/playback, and the India map layout. Full accessibility and real-device testing remain outstanding.

## N. Hostinger compatibility results

The project now has a Hostinger-oriented PHP configuration example and MySQL/MariaDB schema. Compatibility on the purchased plan is **NOT TESTED**: confirm its PHP version, `pdo_mysql`, database host/port, Apache `.htaccess` support, HTTPS, and file permissions in hPanel.

## O. Remaining risks and limitations

- Local database and both form save/read flows passed, but the live/Hostinger database connection is not proven.
- The local `config.php` uses local XAMPP settings and must not be deployed as production configuration.
- SMTP is not implemented.
- Hostinger plan capabilities and domain/HTTPS setup are not verified.
- `sitemap.xml` returns HTTP 503 locally until the correct HTTPS origin is configured; do not set a placeholder origin in production.
- No PostgreSQL schema is included.
- No full application security, accessibility, real-device, Core Web Vitals, or load audit was completed.
- There is no admin dashboard; submitted records must be accessed through a secured database tool.

## P. Remaining production verification steps

1. In Hostinger hPanel, confirm MySQL/MariaDB availability, database host/port, PHP version, and `pdo_mysql`. Do not reuse the local XAMPP database settings.
2. Create the database/user and grant the user access.
3. Import `database/schema.sql` in phpMyAdmin.
4. Set the exact hPanel connection values and verified HTTPS `site_url` in the server's private `config.php`; do not upload the local XAMPP config or commit/publish the server config.
5. Upload the site and confirm `.htaccess` denies direct access to configuration and private folders.
6. Submit one contact form and one service request, then verify both records with the SELECT statements in `HOSTINGER_DEPLOYMENT.md`.
7. Implement/configure authenticated SMTP and verify delivery.
8. Complete the remaining security review, browser/device checks, backups, and controlled performance tests.

## Test evidence

| Test | Expected result | Actual result | Status |
|---|---|---|---|
| PHP syntax check | No syntax errors in PHP project files | `php -l` passed on all 17 PHP files | PASS |
| JavaScript syntax check | No syntax errors | `node --check assets/js/main.js` exited successfully | PASS |
| Whitespace/diff check | No patch whitespace errors | `git diff --check` exited successfully | PASS |
| Local PDO module inventory | Determine available local PDO drivers | `pdo_mysql` and `pdo_sqlite` present; `pdo_pgsql` unavailable | PASS (inventory only) |
| Incomplete config validation | Blank database details fail before connecting | `database_connection()` raised the expected configuration `RuntimeException` | PASS |
| Local MySQL connection/schema import | Connect and import the project form tables | Connected to local `gravityhub`; imported `database/schema.sql`; verified `contact_messages` (7 columns) and `service_requests` (14 columns) | PASS (local only) |
| Local PDO configuration | Application connects using `config.php` | `database_connection()` connected to the local `gravityhub` database | PASS (local only) |
| Form persistence and reads | Save and query both form types | Both form handlers inserted rows and SELECT verified them; isolated QA rows were removed and absence verified | PASS (local only) |
| Live/Hostinger database | Connect the deployed website to its hosting database | Exact live host/user details and successful live form submissions have not been verified | NOT TESTED |
| Password rotation | Replace any database password shared in chat | User was advised to rotate it in the provider panel; completion not confirmed | ACTION REQUIRED |
| Responsive route smoke test | Public pages render without horizontal overflow at common viewport widths | Nine page/article routes returned HTTP 200 with one H1 each; no overflow at 320, 375, 768, 1024, or 1440 px | PASS (local browser only) |
| Mobile navigation and image alternatives | Menu opens and images have alt attributes | Menu expanded; no missing `alt`, placeholder `#!`, or unsafe new-tab link found on homepage | PASS (local browser only) |
| Homepage image decoding | Homepage images resolve to valid decoded media | Images loaded successfully; the one initially deferred lazy image decoded to 1600x900 when requested | PASS (local browser only) |
| Contact service-location map | India reference map is embedded responsively without suggesting a verified business pin | OpenStreetMap embed endpoint is reachable; iframe layout tested at 320, 375, 768, and 1440 px with no horizontal overflow; address/coverage disclaimer is visible | PASS (local layout and endpoint check; live display depends on external map access) |
| SEO metadata/structured data | Valid canonical/social metadata and article schema when a verified HTTPS origin is configured | Test origin rendered canonical URL and Open Graph image; Organization, WebSite, and BlogPosting JSON-LD decoded; local output omitted fake canonical URLs | PASS (render test only) |
| Page titles and descriptions | Every indexable route has distinct, non-empty search metadata | All nine public/article URLs returned unique titles and descriptions locally | PASS (local render only; Google may rewrite snippets) |
| Production site-origin configuration | Production can set canonical/sitemap origin without relying on host environment-variable support | Isolated test confirmed `site_url` in `config.php` works and `ENERGYHUB_SITE_URL` overrides it | PASS (configuration test only) |
| SEO endpoints | Robots endpoint works locally and sitemap is generated only with a real origin | `robots.txt` returned 200; `sitemap.xml` returned the expected 503 without configuration; a test HTTPS origin generated nine absolute sitemap URLs | PASS (local/configuration test) |
| Favicon formats and delivery | Browser, search crawler, and Apple touch icon URLs return supported image types | Shared head advertises ICO, 48x48 PNG, and 180x180 Apple icon; all returned HTTP 200 with correct MIME types; root `favicon.ico` fallback also returned 200 | PASS (local browser/Apache only) |
| Cache headers | Static assets have a browser cache policy | CSS, JS, WebP image, and MP4 returned HTTP 200 with `public, max-age=604800` | PASS (local Apache only) |
| Deferred video | Decorative video is not fetched at initial view and begins when near the viewport | No video request initially; one request after scrolling; video reached readyState 4 and was playing | PASS (local browser only) |
| Optimized media | Re-encoded media is valid and materially smaller | Ten images: about 8.9 MB to 1.1 MB; MP4: 9.77 MB to about 0.41 MB; 640x360 H.264 verified | PASS (local files) |
| Card border styling | Cards retain their intended borders | Browser computed the service-card border as `1px` after removing the global border reset | PASS |
