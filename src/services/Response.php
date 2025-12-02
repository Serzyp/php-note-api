<?php

namespace Services;

/**
 * Servicio para generar respuestas JSON unificadas
 */
class Response
{
    /**
     * Enviar respuesta exitosa
     */
    public static function success(mixed $data = null, string $message = 'Operación exitosa', int $code = 200): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Enviar respuesta de error
     */
    public static function error(string $message = 'Error en la operación', int $code = 400, mixed $data = null): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Respuesta de validación fallida
     */
    public static function validationError(array $errors, string $message = 'Errores de validación'): void
    {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Respuesta no autorizado
     */
    public static function unauthorized(string $message = 'No autorizado'): void
    {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Respuesta prohibido
     */
    public static function forbidden(string $message = 'Acceso prohibido'): void
    {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Respuesta no encontrado
     */
    public static function notFound(string $message = 'Recurso no encontrado'): void
    {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Respuesta de error del servidor
     */
    public static function serverError(string $message = 'Error interno del servidor'): void
    {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
