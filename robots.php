<?php

require_once __DIR__ . '/includes/site-url.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "User-agent: *\n";
echo "Allow: /\n";

$siteUrl = configured_site_url();
if ($siteUrl !== '') {
    echo 'Sitemap: ' . $siteUrl . "/sitemap.xml\n";
}
