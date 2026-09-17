<?php

namespace Izumi\Backend\app\Database;

use Exception;
use PDO;

function postgresUriToDsn(string $uri): array
{
    $parsed = parse_url($uri);

    if ($parsed === false) {
        throw new Exception('Invalid DATABASE_URL format');
    }

    $host = $parsed['host'] ?? 'localhost';
    $port = $parsed['port'] ?? 5432;
    $database = ltrim($parsed['path'] ?? '', '/');
    $user = $parsed['user'] ?? '';
    $password = $parsed['pass'] ?? '';

    if ($database === '') {
        throw new Exception('Database name is missing');
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        $host,
        $port,
        $database
    );

    return [
        'dsn' => $dsn,
        'user' => urldecode($user),
        'password' => urldecode($password),
    ];
}

$uri = getenv('DATABASE_URL');

if (!$uri) {
    throw new Exception(
        'DATABASE_URL environment variable is not set'
    );
}

$config = postgresUriToDsn($uri);

return new PDO(
    $config['dsn'],
    $config['user'],
    $config['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
