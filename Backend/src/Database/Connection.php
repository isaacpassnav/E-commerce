<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Env;
use PDO;
use PDOException;
use RuntimeException;

final class Connection
{
    public static function make(): PDO
    {
        $driver = Env::get('DB_DRIVER', 'mysql');

        try {
            [$dsn, $username, $password] = self::buildConfig($driver);
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            throw new RuntimeException('No se pudo establecer conexion a la base de datos', 0, $e);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    }

    /**
     * @return array{0:string,1:?string,2:?string}
     */
    private static function buildConfig(string $driver): array
    {
        if ($driver === 'sqlite') {
            $dbPath = Env::get('DB_DATABASE', __DIR__ . '/../../database.sqlite');
            return ['sqlite:' . $dbPath, null, null];
        }

        if ($driver === 'pgsql') {
            return self::buildPostgresConfig();
        }

        return self::buildMysqlConfig();
    }

    /**
     * @return array{0:string,1:?string,2:?string}
     */
    private static function buildMysqlConfig(): array
    {
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('Falta la extension pdo_mysql en PHP');
        }

        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '3306');
        $database = Env::get('DB_DATABASE', 'ecommerce_db_okea');
        $username = Env::get('DB_USERNAME', 'root');
        $password = Env::get('DB_PASSWORD', '');
        $charset = Env::get('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        return [$dsn, $username, $password];
    }

    /**
     * @return array{0:string,1:?string,2:?string}
     */
    private static function buildPostgresConfig(): array
    {
        if (!extension_loaded('pdo_pgsql')) {
            throw new RuntimeException('Falta la extension pdo_pgsql en PHP');
        }

        $url = Env::get('DB_URL');

        if ($url !== null && $url !== '') {
            $parsed = self::parseDatabaseUrl($url);
            $sslmode = $parsed['sslmode'] ?? Env::get('DB_SSLMODE', 'require');

            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
                $parsed['host'],
                $parsed['port'],
                $parsed['database'],
                $sslmode
            );

            return [$dsn, $parsed['username'], $parsed['password']];
        }

        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '5432');
        $database = Env::get('DB_DATABASE', 'postgres');
        $username = Env::get('DB_USERNAME', 'postgres');
        $password = Env::get('DB_PASSWORD', '');
        $sslmode = Env::get('DB_SSLMODE', 'prefer');

        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
            $host,
            $port,
            $database,
            $sslmode
        );

        return [$dsn, $username, $password];
    }

    /**
     * @return array{
     *   host:string,
     *   port:string,
     *   database:string,
     *   username:string,
     *   password:string,
     *   sslmode:?string
     * }
     */
    private static function parseDatabaseUrl(string $url): array
    {
        $parts = parse_url($url);
        if ($parts === false) {
            throw new RuntimeException('DB_URL no es valido');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $allowedSchemes = ['postgres', 'postgresql', 'pgsql'];
        if (!in_array($scheme, $allowedSchemes, true)) {
            throw new RuntimeException('DB_URL debe usar esquema postgres:// o postgresql://');
        }

        $host = (string) ($parts['host'] ?? '');
        $database = ltrim((string) ($parts['path'] ?? ''), '/');
        $username = urldecode((string) ($parts['user'] ?? ''));
        $password = urldecode((string) ($parts['pass'] ?? ''));
        $port = (string) ($parts['port'] ?? 5432);

        if ($host === '' || $database === '' || $username === '') {
            throw new RuntimeException('DB_URL incompleto: host, database y username son obligatorios');
        }

        $sslmode = null;
        if (isset($parts['query']) && $parts['query'] !== '') {
            parse_str($parts['query'], $query);
            if (isset($query['sslmode']) && is_string($query['sslmode'])) {
                $sslmode = $query['sslmode'];
            }
        }

        return [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'sslmode' => $sslmode,
        ];
    }
}
