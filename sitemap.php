<?php

require_once __DIR__ . '/includes/site-url.php';

$siteUrl = configured_site_url();
if ($siteUrl === '') {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Set site_url in config.php or ENERGYHUB_SITE_URL to the verified HTTPS site origin to generate the sitemap.';
    exit;
}

$paths = [
    '/',
    '/about.php',
    '/services.php',
    '/blog.php',
    '/contact.php',
    '/request-service.php',
    '/blog.php?slug=prepare-for-technical-support',
    '/blog.php?slug=reviewing-energy-monitor-readings',
    '/blog.php?slug=equipment-maintenance-records',
];

header('Content-Type: application/xml; charset=UTF-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($paths as $path) {
    $location = htmlspecialchars($siteUrl . $path, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    echo "  <url><loc>{$location}</loc></url>\n";
}
echo "</urlset>\n";
