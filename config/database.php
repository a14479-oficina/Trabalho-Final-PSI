<?php

require_once __DIR__ . '/load_env.php';

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $driver = getenv('DB_DRIVER') ?: 'pgsql';

            if ($driver === 'sqlite') {
                $path = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../data/devbank.sqlite';
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                self::$instance = new PDO("sqlite:{$path}", null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                self::$instance->exec("PRAGMA foreign_keys = ON");
            } else {
                $host = getenv('DB_HOST') ?: 'aws-0-eu-west-1.pooler.supabase.com';
                $port = getenv('DB_PORT') ?: '6543';
                $name = getenv('DB_NAME') ?: 'postgres';
                $user = getenv('DB_USER') ?: 'postgres';
                $pass = getenv('DB_PASSWORD') ?: '';

                $dsn = "pgsql:host={$host};port={$port};dbname={$name}";

                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            }
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
