<?php

/**
 * Rutas de notas
 * Prefijo: /notes
 */

use Controllers\NotesController;
use Middleware\AuthMiddleware;

// Todas las rutas de notas requieren autenticación
$userId = AuthMiddleware::authenticate();

$controller = new NotesController();

// Obtener ID desde path (/notes/5) o query param (/notes?id=5)
$noteId = null;

// Verificar query param
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $noteId = (int)$_GET['id'];
}

/* 

Tambien podriamos buscar el id en el path así:

if (isset($uriParts[2]) && is_numeric($uriParts[2])) {
    $noteId = (int)$uriParts[2];
}

*/

$method = $_SERVER['REQUEST_METHOD'];

// Routing basado en método HTTP y parámetros
if ($noteId === null) {
    // /notes
    switch ($method) {
        case 'GET':
            // GET /notes - Listar todas las notas
            $controller->getAll($userId);
            break;
            
        case 'POST':
            // POST /notes - Crear nueva nota
            $controller->create($userId);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método HTTP no permitido',
                'data' => null
            ]);
            break;
    }
} else {
    // /notes?id={id}
    switch ($method) {
        case 'GET':
            // GET /notes?id={id} - Obtener una nota específica
            $controller->getOne($userId, $noteId);
            break;
            
        case 'PUT':
            // PUT /notes?id={id} - Actualizar una nota
            $controller->update($userId, $noteId);
            break;
            
        case 'DELETE':
            // DELETE /notes?id={id} - Eliminar una nota
            $controller->delete($userId, $noteId);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método HTTP no permitido',
                'data' => null
            ]);
            break;
    }
}
