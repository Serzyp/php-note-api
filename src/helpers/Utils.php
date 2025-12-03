<?php

namespace Helpers;

/**
 * Funciones auxiliares
 */
class Utils
{
    /**
     * Obtener el cuerpo de la petición HTTP como array
     */
    public static function getRequestBody(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        return $data ?? [];
    }
    
    /**
     * Validar email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Sanitizar string
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Obtener header de autorización
     */
    public static function getAuthorizationHeader(): ?string
    {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
    
    /**
     * Extraer token del header Authorization
     */
    public static function getBearerToken(): ?string
    {
        $headers = self::getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Validar longitud de string
     */
    public static function validateLength(string $value, int $min, int $max): bool
    {
        $length = strlen($value);
        return $length >= $min && $length <= $max;
    }
    
    /**
     * Generar hash de contraseña
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Verificar contraseña
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}