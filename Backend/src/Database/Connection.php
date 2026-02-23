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
            if ($driver === 'sqlite') {
                $dbPath = Env::get('DB_DATABASE', __DIR__ . '/../../database.sqlite');
                $dsn = 'sqlite:' . $dbPath;
                $pdo = new PDO($dsn);
            } else {
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

                $pdo = new PDO($dsn, $username, $password);
            }
        } catch (PDOException $e) {
            throw new RuntimeException('No se pudo establecer conexión a la base de datos', 0, $e);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
