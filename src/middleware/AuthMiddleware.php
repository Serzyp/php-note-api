<?php

namespace Middleware;

use Services\TokenService;
use Services\Response;
use Helpers\Utils;

/**
 * Middleware para validar autenticación mediante tokens
 */
class AuthMiddleware
{
    /**
     * Verificar que el usuario esté autenticado
     * Retorna el ID del usuario si está autenticado
     */
    public static function authenticate(): int
    {
        $token = Utils::getBearerToken();
        
        if (!$token) {
            Response::unauthorized('Token de autenticación no proporcionado');
        }
        
        $tokenService = new TokenService();
        $userId = $tokenService->validateToken($token);
        
        if (!$userId) {
            Response::unauthorized('Token inválido o expirado');
        }
        
        return $userId;
    }
    
    /**
     * Verificar método HTTP
     */
    public static function validateMethod(array $allowedMethods): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (!in_array($method, $allowedMethods)) {
            Response::error('Método HTTP no permitido', 405);
        }
    }
}
