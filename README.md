# EnergyHub Services — Premium Service-Based Website

A complete, premium, modern frontend for an electrical monitoring, energy management, and IoT device service company. Built with PHP, HTML5, CSS3, vanilla JavaScript, and Bootstrap 5.

## Project Goal

Professional, service-based technical support for electrical equipment, energy-monitoring devices, and related work:
- Electrical Troubleshooting & Fault Diagnosis
- Equipment Installation & Commissioning
- Preventive Maintenance & Servicing
- Repair, Replacement & Component Support (subject to assessment and availability)
- On-Site Technical Assistance (subject to location and scheduling)
- Custom Technical Tasks & Project Support (subject to scope and approval)

EnergyHub does not promise ready-stock products or instant parts delivery. Scope, material sourcing, site visits, costs, and timelines require review and confirmation.

## Features

- Premium dark technology design (deep navy, cyan accents, glass-style cards)
- Responsive: 1440px, 1024px, 768px, 375px, 320px
- Sticky header with mobile hamburger menu
- Smooth scrolling and scroll-reveal animations (respects `prefers-reduced-motion`)
- Working FAQ accordion (vanilla JS)
- Six shared service categories across the homepage, services page, request form, and footer
- Editorial blog page with three practical equipment-support guides
- Service detail sections with matching image, anchor, request link, required information, and availability notes
- Footer app download area with inactive Android and iOS placeholders
- Contact and service-request forms with server-side validation and PDO persistence
- Responsive WebP image delivery with PNG fallback, lazy-loaded below-the-fold media, and a compressed hero video
- Search/social metadata and Organization structured data on all pages
- Back-to-top button
- Reusable PHP includes (header, footer, head, service data)
- Dynamic copyright year in footer
- No admin dashboard, authentication, or payments

## Folder Structure

```
energyhubwebsite/
├── index.php              # Homepage (sections A–J)
├── services.php           # Services page
├── about.php              # About Us page
├── blog.php               # Practical technical guides and insights
├── contact.php            # Contact page (form UI)
├── request-service.php    # Service request page (form UI)
├── includes/
│   ├── head.php           # Shared HTML head
│   ├── header.php         # Sticky navigation + logo
│   ├── footer.php         # Footer with links + dynamic year
│   ├── service-data.php     # Central editable service data
│   ├── database.php         # PDO connection for MySQL/MariaDB or PostgreSQL
│   └── form-handler.php     # CSRF protection, validation, and form inserts
├── database/
│   └── schema.sql           # MySQL/MariaDB form tables and indexes
├── .htaccess                # Apache directory, include, and file-access rules
├── config.example.php       # Safe database configuration template
├── config.php               # Private database configuration; do not commit
├── HOSTINGER_DEPLOYMENT.md  # Hostinger setup guide
├── assets/
│   ├── css/style.css      # All styles (CSS variables, components, responsive)
│   ├── js/main.js         # All interactions (menu, scroll, FAQ, validation)
│   └── images/            # Site images and backgrounds
├── README.md
└── .gitignore
```

## Pages

| Page | File | Description |
|------|------|-------------|
| Home | `index.php` | Hero, trust highlights, six service cards, why us, how it works, featured diagnosis CTA, about preview, FAQ, final CTA |
| Services | `services.php` | Six detailed service sections, request links, how it works, FAQs, CTA |
| About Us | `about.php` | Intro, mission, values, service approach, why choose us, CTA |
| Blog | `blog.php` | Editorial blog listing with featured post, category/search filters, and individual article views; currently uses sample PHP-array content |
| Contact Us | `contact.php` | Contact details, message form, and service-location placeholder |
| Request Service | `request-service.php` | Server-validated service request form |

## Image Assets

The site includes optimized WebP images and a compressed hero video:

| File | Section |
|------|---------|
| `photo1.1.webp`, `photo1.2.webp`, `photo1.3.webp` | Homepage and page hero slideshow |
| `photo2.webp` | Fault diagnosis |
| `photo4.webp` | Equipment installation |
| `photo5.webp` | Preventive maintenance |
| `photo6-optimized.mp4` | Why Choose Us video |
| `photo7.webp` | About Us |
| `photo8.webp` | Custom technical tasks |
| `photo9.webp` | On-site technical assistance / final CTA |
| `photo10.webp` | Component support |
| `photo11.1.webp`, `photo11.webp` | Contact and final CTA backgrounds |
| `photo12.webp` | Request a Service hero background |
| `photo13.webp` | Blog journal masthead background |
| `gravity-hub-logo.webp`, `gravity-hub-icon.webp` | Site logo and icon |

## How to Run Locally

This is a PHP project. You need PHP installed to run it.

### Option 1: PHP Built-in Server (recommended for testing)

From the project root:

```powershell
D:\xampp\php\php.exe -S localhost:8000
```

Then open: http://localhost:8000/index.php

### Option 2: XAMPP / WAMP / MAMP

Place the project folder in your web server's document root (e.g., `htdocs`) and access via `http://localhost/energyhubwebsite/index.php`.

## Database and Form Setup

The Contact Us and Request a Service forms save submissions in `contact_messages` and `service_requests` using PDO prepared statements. The connection supports MySQL/MariaDB and PostgreSQL; Hostinger shared hosting commonly provides MySQL/MariaDB, but confirm the database type and connection details in your hosting panel. `database/schema.sql` targets MySQL/MariaDB. The current backup file is empty, so it does not contain data to migrate.

The forms validate input on the server, use CSRF tokens, and send email notifications through PHP's configured mail transport. A successful database save remains recorded even if notification delivery is unavailable.

Set the connection values in the root `config.php`. For a fresh setup, copy `config.example.php` to `config.php` and replace the example values with the database details shown by your provider. Keep `config.php` private; it is ignored by Git and direct HTTP access is denied by `.htaccess`. Do not put database credentials in frontend files or commit them.

1. Create a MySQL/MariaDB database and database user in your hosting control panel, and grant that user access to the database.
2. Import `database/schema.sql` using phpMyAdmin or the hosting database import tool. It creates the two form tables if missing; it does not drop tables or delete existing data.
3. Set `driver`, `host`, `port`, `database`, `username`, and `password` in `config.php`. Typical MySQL values use driver `mysql` and port `3306`, but use the exact values from your host.
4. Ensure the PHP `pdo_mysql` extension is enabled. For XAMPP, check `D:\xampp\php\php.ini` and restart Apache after enabling it. Check with `D:\xampp\php\php.exe -m | findstr /I pdo_mysql`.
5. Submit a contact message and service request, then verify both records in phpMyAdmin:

   ```sql
   SELECT id, name, email, created_at FROM contact_messages ORDER BY id DESC LIMIT 5;
   SELECT id, customer_name, service_type, status, created_at FROM service_requests ORDER BY id DESC LIMIT 5;
   ```

Database connection errors are written to the PHP error log; the form displays a generic save error without exposing credentials or server details. PostgreSQL connections are supported by the connection code, but this repository does not currently include a PostgreSQL schema.

## What Is Functional Now

- All navigation links work across pages (Home, About Us, Services, Blog, Contact Us)
- Blog page uses sample in-file post data for now; superadmin/database publishing is not yet connected
- Sticky header with scroll shadow
- Mobile hamburger menu (open/close)
- Smooth scroll for anchor links
- Six service cards, detail anchors, matching request-form preselection, and footer service links driven by shared PHP data
- FAQ accordion (open/close)
- Back-to-top button
- Scroll-reveal animations
- Client-side and server-side form validation on contact and request-service pages
- CSRF-protected contact and service-request submissions stored in MySQL/MariaDB or PostgreSQL
- Dynamic footer year
- Missing-image-safe containers

## Remaining Backend Work

- Configure database details in `config.php` and import `database/schema.sql` before accepting submissions.
- An authenticated admin dashboard for reviewing submissions is not included.

## Design System

- Background: `#060a12` (deep navy)
- Cards and panels: `#0a0f18` / `#0e1726`
- Primary accent: `#00d2ff` (cyan)
- Text: `#f3f4f6` headings and `#a1aebf` supporting text
- Hero: full-width slideshow using `photo1.1.png`, `photo1.2.png`, and `photo1.3.png`, with matching text and a dark overlay; the floating services hint links to the services page
- Icons: Bootstrap Icons (CDN)
- CSS framework: Bootstrap 5 (CDN)

## Publishing Notes

The contact email is `gravityhubautomation@gmail.com`; configure and test the hosting server's mail transport before publishing. Address and business hours are placeholders and must be confirmed before publishing. The displayed phone and WhatsApp number are retained from the existing project. Confirm service coverage, site availability, material sourcing, costs, and timing for each request before committing to work.
