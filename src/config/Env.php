<?php

namespace Config;

/**
 * Clase para cargar y gestionar variables de entorno desde .env
 */
class Env
{
    private static array $variables = [];
    
    /**
     * Cargar variables desde archivo .env
     */
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            // Si no existe .env, intentar cargar .env.example
            $examplePath = dirname($path) . '/.env.example';
            if (file_exists($examplePath)) {
                $path = $examplePath;
            } else {
                throw new \Exception("Archivo .env no encontrado en: $path");
            }
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parsear línea
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remover comillas si existen
                $value = trim($value, '"\'');
                
                self::$variables[$name] = $value;
                
                // También establecer en $_ENV
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
    
    /**
     * Obtener valor de variable de entorno
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * Establecer variable de entorno 
     */
    public static function set(string $key, mixed $value): void
    {
        self::$variables[$key] = $value;
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
    
    /**
     * Verificar si existe una variable
     */
    public static function has(string $key): bool
    {
        return isset(self::$variables[$key]) || isset($_ENV[$key]) || getenv($key) !== false;
    }
}
