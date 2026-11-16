<?php

function configured_site_url(): string
{
    static $siteUrl = null;
    if ($siteUrl !== null) {
        return $siteUrl;
    }

    $configuredUrl = getenv('ENERGYHUB_SITE_URL');
    if ($configuredUrl === false || trim($configuredUrl) === '') {
        $configurationPath = dirname(__DIR__) . '/config.php';
        $applicationConfiguration = is_file($configurationPath) && is_readable($configurationPath)
            ? require $configurationPath
            : [];
        $configuredUrl = is_array($applicationConfiguration)
            ? ($applicationConfiguration['site_url'] ?? '')
            : '';
    }

    if (!is_string($configuredUrl)) {
        error_log('EnergyHub SEO: site_url in config.php or ENERGYHUB_SITE_URL must be a string.');
        $siteUrl = '';
        return $siteUrl;
    }

    $configuredUrl = trim($configuredUrl);
    if ($configuredUrl === '') {
        $siteUrl = '';
        return $siteUrl;
    }

    $configuredUrl = rtrim($configuredUrl, '/');
    $components = parse_url($configuredUrl);
    if (!filter_var($configuredUrl, FILTER_VALIDATE_URL)
        || !is_array($components)
        || strtolower((string) ($components['scheme'] ?? '')) !== 'https'
        || empty($components['host'])
        || isset($components['user'])
        || isset($components['pass'])
        || isset($components['query'])
        || isset($components['fragment'])
        || (isset($components['path']) && $components['path'] !== '' && $components['path'] !== '/')) {
        error_log('EnergyHub SEO: site_url in config.php or ENERGYHUB_SITE_URL must be a valid HTTPS origin without credentials or a path.');
        $siteUrl = '';
        return $siteUrl;
    }

    $siteUrl = $configuredUrl;
    return $siteUrl;
}
