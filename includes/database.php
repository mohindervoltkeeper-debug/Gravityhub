<?php

function database_configuration(): array
{
    static $configuration = null;
    if ($configuration !== null) {
        return $configuration;
    }

    $configurationPath = dirname(__DIR__) . '/config.php';
    if (!is_file($configurationPath) || !is_readable($configurationPath)) {
        throw new RuntimeException(
            'Database configuration is missing. Copy config.example.php to config.php and set your database details.'
        );
    }

    $fileConfiguration = require $configurationPath;
    if (!is_array($fileConfiguration) || !isset($fileConfiguration['database'])
        || !is_array($fileConfiguration['database'])) {
        throw new RuntimeException('config.php must return an array with a database section.');
    }

    $configuration = $fileConfiguration['database'];
    return $configuration;
}

function database_connection(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $configuration = database_configuration();
    $driver = $configuration['driver'] ?? '';
    $host = $configuration['host'] ?? '';
    $port = $configuration['port'] ?? '';
    $database = $configuration['database'] ?? '';
    $username = $configuration['username'] ?? '';
    $password = $configuration['password'] ?? '';
    $sslMode = $configuration['sslmode'] ?? 'prefer';

    if (!is_string($driver) || !in_array($driver, ['mysql', 'pgsql'], true)) {
        throw new RuntimeException('The database driver must be set to mysql or pgsql in config.php.');
    }
    if (!in_array($driver, PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('The PDO ' . $driver . ' driver is not enabled.');
    }
    if (!is_string($host) || $host === '' || !preg_match('/^[a-zA-Z0-9._:-]+$/D', $host)) {
        throw new RuntimeException('The database host in config.php is missing or invalid.');
    }
    if ((!is_int($port) && (!is_string($port) || !ctype_digit($port)))
        || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('The database port in config.php must be a valid TCP port.');
    }
    if (!is_string($database) || $database === '' || !preg_match('/^[a-zA-Z0-9_.-]+$/D', $database)) {
        throw new RuntimeException('The database name in config.php is missing or invalid.');
    }
    if (!is_string($username) || $username === '') {
        throw new RuntimeException('The database username in config.php is required.');
    }
    if (!is_string($password)) {
        throw new RuntimeException('The database password in config.php must be a string.');
    }

    if ($driver === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            (int) $port,
            $database
        );
    } else {
        if (!is_string($sslMode)
            || !in_array($sslMode, ['disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'], true)) {
            throw new RuntimeException('The PostgreSQL sslmode in config.php is invalid.');
        }
        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;options='--client_encoding=UTF8';sslmode=%s",
            $host,
            (int) $port,
            $database,
            $sslMode
        );
    }

    $connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
