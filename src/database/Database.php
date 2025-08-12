<?php
require_once "config.php";

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            try {
                // ✅ CORREGIDO: Usar métodos en lugar de constantes
                $dsn =
                    "mysql:host=" .
                    Config::getDbHost() .
                    ";dbname=" .
                    Config::getDbName() .
                    ";charset=utf8mb4";
                self::$connection = new PDO(
                    $dsn,
                    Config::getDbUser(),
                    Config::getDbPass(),
                );
                self::$connection->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION,
                );
                self::$connection->setAttribute(
                    PDO::ATTR_DEFAULT_FETCH_MODE,
                    PDO::FETCH_ASSOC,
                );
            } catch (PDOException $e) {
                throw new Exception("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>
