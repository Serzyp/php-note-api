<?php

/**
 * Rutas de autenticación
 * Prefijo: /auth
 */

use Controllers\AuthController;
use Middleware\AuthMiddleware;

$controller = new AuthController();

// Obtener el segundo segmento de la URI
$action = $uriParts[2] ?? '';

switch ($action) {
    case 'register':
        // POST /auth/register
        AuthMiddleware::validateMethod(['POST']);
        $controller->register();
        break;
        
    case 'login':
        // POST /auth/login
        AuthMiddleware::validateMethod(['POST']);
        $controller->login();
        break;
        
    case 'logout':
        // POST /auth/logout
        AuthMiddleware::validateMethod(['POST']);
        $controller->logout();
        break;
        
    case 'me':
        // GET /auth/me
        AuthMiddleware::validateMethod(['GET']);
        $userId = AuthMiddleware::authenticate();
        $controller->me($userId);
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint de autenticación no encontrado',
            'data' => null
        ]);
        break;
}
