<?php
require_once __DIR__ . '/site-url.php';

$site_url = configured_site_url();
$canonical_url = '';
$social_image_url = '';
$page_route = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

if ($site_url !== '') {
    $canonical_path = $page_route === 'index.php' ? '/' : '/' . $page_route;
    if ($page_route === 'blog.php' && isset($active_post['slug'])) {
        $canonical_path .= '?slug=' . rawurlencode($active_post['slug']);
    }
    $canonical_url = $site_url . $canonical_path;

    if (!empty($page_social_image)) {
        $social_image_url = $site_url . '/' . ltrim(webp_image_path($page_social_image), '/');
    }
}

$escaped_page_title = htmlspecialchars($page_title ?? 'EnergyHub Services', ENT_QUOTES, 'UTF-8');
$escaped_page_description = htmlspecialchars(
    $page_description ?? 'Professional technical services for electrical equipment and energy-monitoring devices.',
    ENT_QUOTES,
    'UTF-8'
);
$organization_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'EnergyHub Services',
    'email' => 'gravityhubautomation@gmail.com',
    'telephone' => '+91 99834 98888',
];

if ($canonical_url !== '') {
    $organization_schema['url'] = $site_url;
    $organization_schema['logo'] = $site_url . '/assets/images/gravity-hub-logo.webp';
    $website_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'EnergyHub Services',
        'url' => $site_url,
        'inLanguage' => 'en-IN',
    ];
} else {
    $website_schema = null;
}

$article_schema = null;
$breadcrumb_schema = null;
if (isset($active_post) && $active_post !== null) {
    $article_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $active_post['title'],
        'description' => $page_description,
        'datePublished' => $active_post['published'],
        'author' => [
            '@type' => 'Organization',
            'name' => $active_post['author'],
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'EnergyHub Services',
        ],
        'inLanguage' => 'en-IN',
    ];

    if ($canonical_url !== '') {
        $article_schema['mainEntityOfPage'] = [
            '@type' => 'WebPage',
            '@id' => $canonical_url,
        ];
    }
    if ($social_image_url !== '') {
        $article_schema['image'] = $social_image_url;
    }
    if ($canonical_url !== '') {
        $breadcrumb_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Blog',
                    'item' => $site_url . '/blog.php',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $active_post['title'],
                    'item' => $canonical_url,
                ],
            ],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="<?php echo $escaped_page_description; ?>">
    <meta name="author" content="EnergyHub Services">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#060a12">

    <title><?php echo $escaped_page_title; ?></title>
    <meta property="og:type" content="<?php echo isset($active_post) && $active_post !== null ? 'article' : 'website'; ?>">
    <meta property="og:site_name" content="EnergyHub Services">
    <meta property="og:title" content="<?php echo $escaped_page_title; ?>">
    <meta property="og:description" content="<?php echo $escaped_page_description; ?>">
    <meta property="og:locale" content="en_IN">
    <meta name="twitter:card" content="<?php echo $social_image_url !== '' ? 'summary_large_image' : 'summary'; ?>">
    <meta name="twitter:title" content="<?php echo $escaped_page_title; ?>">
    <meta name="twitter:description" content="<?php echo $escaped_page_description; ?>">
    <?php if ($canonical_url !== ''): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <?php if ($social_image_url !== ''): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($social_image_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image:alt" content="<?php echo $escaped_page_description; ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($social_image_url, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <?php if (isset($active_post) && $active_post !== null): ?>
    <meta property="article:published_time" content="<?php echo htmlspecialchars($active_post['published'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="article:author" content="<?php echo htmlspecialchars($active_post['author'], ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <link rel="icon" href="favicon.ico?v=<?php echo filemtime(__DIR__ . '/../favicon.ico'); ?>" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="favicon-48.png?v=<?php echo filemtime(__DIR__ . '/../favicon-48.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png?v=<?php echo filemtime(__DIR__ . '/../apple-touch-icon.png'); ?>">

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Local Stylesheet -->
    <link href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>" rel="stylesheet">

    <!-- Bootstrap Icons (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script type="application/ld+json"><?php echo json_encode($organization_schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <?php if ($website_schema !== null): ?>
    <script type="application/ld+json"><?php echo json_encode($website_schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <?php endif; ?>
    <?php if ($article_schema !== null): ?>
    <script type="application/ld+json"><?php echo json_encode($article_schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <?php endif; ?>
    <?php if ($breadcrumb_schema !== null): ?>
    <script type="application/ld+json"><?php echo json_encode($breadcrumb_schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <?php endif; ?>
</head>
<body <?php echo isset($body_class) ? 'class="' . htmlspecialchars($body_class) . '"' : ''; ?>>
    <a class="skip-link" href="#main-content">Skip to main content</a>