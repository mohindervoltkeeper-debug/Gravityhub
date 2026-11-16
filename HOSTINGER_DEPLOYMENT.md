# Hostinger Deployment

## Database support and current limits

This project uses PDO and supports MySQL/MariaDB (`mysql`) or PostgreSQL (`pgsql`) when the matching PDO extension is enabled. Hostinger shared plans typically provide MySQL/MariaDB, but confirm the database type, hostname, port, and PHP extensions in your Hostinger hPanel before configuring the site. This project has not been connected to your Hostinger account.

The supplied `database/schema.sql` is specifically for MySQL/MariaDB. The available `gravityhub_backup.sql` file is empty, so there is no SQL data to migrate from it. A PostgreSQL schema is not included.

## Deployment steps

1. In hPanel, confirm the PHP version and enable/check `pdo_mysql`. Select a PHP version supported by your account and the project's PHP syntax.
2. Create a MySQL database and user in hPanel. Grant that user access to the database and note the exact database name, username, host, and port.
3. Import `database/schema.sql` in phpMyAdmin. This creates `contact_messages` and `service_requests` if absent; it does not drop tables or delete data.
4. Copy `config.example.php` to `config.php`, then enter the database values supplied by hPanel. Set `site_url` to your verified HTTPS origin, for example `https://your-domain.com` (no path or trailing slash). This enables correct canonical URLs, absolute social metadata, and the sitemap; `ENERGYHUB_SITE_URL`, if provided by your host, takes precedence. Use `driver` = `mysql` and port `3306` only if those values match your hosting details.
5. Upload the site files to the domain's document root. Keep `config.php` on the server and do not commit or publish it. The root `.htaccess` denies direct requests for it; test this after upload. The `includes/` and `database/` folders also deny direct access.
6. Point the domain to the hosting account, enable HTTPS in hPanel, and force HTTPS using the hosting's supported setting after confirming the certificate is active.
7. Check file permissions: ordinary site files should be readable by the web server; do not make configuration or SQL files world-writable. Do not change permissions recursively to `777`.
8. Submit one contact form and one service request using test data. In phpMyAdmin, verify rows were saved:

   ```sql
   SELECT id, name, email, created_at FROM contact_messages ORDER BY id DESC LIMIT 5;
   SELECT id, customer_name, service_type, status, created_at FROM service_requests ORDER BY id DESC LIMIT 5;
   ```

9. Remove any test submissions through phpMyAdmin only after verifying them, if they should not remain. Do not run destructive queries against existing data.
10. Configure and verify outgoing email separately. The current code uses PHP `mail()`; it does not implement authenticated SMTP. Do not assume email delivery works until a real test confirms it.

## Backups and recovery

Use hPanel/phpMyAdmin to export the database before schema or application changes and store the export somewhere private. To restore, create or select the intended database, import the reviewed backup through phpMyAdmin, and verify both form tables. Do not import over production data without first taking a backup.

## Troubleshooting

- **Missing config error:** Ensure the uploaded root `config.php` exists and returns the configuration array from `config.example.php`.
- **PDO driver error:** Enable `pdo_mysql` for the PHP version assigned to the domain.
- **Connection refused / access denied:** Re-check the database host, name, username, password, and grants in hPanel; `localhost` is not guaranteed to be the correct host.
- **Table not found:** Import `database/schema.sql` into the same database configured in `config.php`.
- **Form reports save failure:** Check the private PHP error log; do not display database exception details publicly.
- **No email notification:** The current implementation depends on the PHP mail transport. Authenticated SMTP setup is not implemented and must be addressed before relying on notifications.

## Security notes

`config.php` contains database credentials. Keep it out of Git and backups that are publicly accessible. Confirm that HTTPS and the `.htaccess` access-denial rules work on the selected Hostinger web server before accepting real submissions. This guide is not evidence that the site has been deployed or verified on Hostinger.
