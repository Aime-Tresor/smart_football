<?php

namespace App\Support;

use App\Config;
use PDO;
use PDOException;

/**
 * PDO factory. New service code takes a PDO instance via constructor
 * injection rather than reading a global - this is what lets the test
 * suite swap in a connection to fa_db_test without touching production
 * code paths (app/database.php stays untouched for legacy includes).
 */
class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = self::connect(Config::dbName());
        }
        return self::$connection;
    }

    public static function connect(string $dbName): PDO
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', Config::dbHost(), $dbName);
        try {
            return new PDO($dsn, Config::dbUser(), Config::dbPassword(), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode());
        }
    }
}
