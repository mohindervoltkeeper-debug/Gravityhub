<?php

return [
    // Leave blank locally; set the verified HTTPS origin on the production server.
    'site_url' => '',
    'database' => [
        // Use "mysql" for Hostinger MySQL/MariaDB, or "pgsql" if your provider offers PostgreSQL.
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'your_database_name',
        'username' => 'your_database_user',
        'password' => 'replace_with_your_database_password',
        // PostgreSQL only. Use "require" if the PostgreSQL provider requires TLS.
        'sslmode' => 'prefer',
    ],
];
