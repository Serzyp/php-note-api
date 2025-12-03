<?php

namespace Config;

use PDO;
use PDOException;

/**
 * Clase para manejar la conexión a la base de datos MySQL
 */
class Database
{
    private static ?PDO $connection = null;
    
    /**
     * Obtener conexión a la base de datos (Singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $host = Env::get('DB_HOST', 'localhost');
                $port = Env::get('DB_PORT', '3306');
                $dbname = Env::get('DB_NAME', 'notes_api');
                $user = Env::get('DB_USER', 'root');
                $pass = Env::get('DB_PASS', '');
                
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                self::$connection = new PDO($dsn, $user, $pass, $options);
                
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'message' => 'Error de conexión a la base de datos',
                    'error' => $e->getMessage(),
                    'data' => null
                ]));
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Cerrar conexión
     */
    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}
