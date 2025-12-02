<?php
/**
 * Punto de entrada de la API REST
 * Router principal que maneja todas las peticiones
 */

// Reportar todos los errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Headers para API REST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar peticiones OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Autoload manual de clases
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../src/';
    
    // Mapeo de namespaces a directorios
    $prefixes = [
        'Config\\' => 'config/',
        'Controllers\\' => 'controllers/',
        'Models\\' => 'models/',
        'Middleware\\' => 'middleware/',
        'Services\\' => 'services/',
        'Helpers\\' => 'helpers/',
    ];
    
    foreach ($prefixes as $prefix => $dir) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . $dir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
    
    // Intentar cargar desde src/ directamente
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Cargar variables de entorno
use Config\Env;
Env::load(__DIR__ . '/../.env');

// Obtener método HTTP y URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Eliminar el prefijo del directorio si existe
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/') {
    $uri = str_replace($scriptName, '', $uri);
}

// Limpiar la URI
$uri = trim($uri, '/');
$uriParts = explode('/', $uri);

// Routing principal
try {
    // Ruta raíz - Documentación
    if (empty($uri) || $uri === '') {
        header('Content-Type: text/html; charset=utf-8');
        readfile(__DIR__ . '/../src/docs/swagger.html');
        exit;
    }
    
    // Obtener el primer segmento de la ruta
    $resource = $uriParts[0] ?? '';
    
    switch ($resource) {
        case 'docs':
        case 'api-docs':
            header('Content-Type: text/html; charset=utf-8');
            readfile(__DIR__ . '/../src/docs/swagger.html');
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint no encontrado',
                'data' => null
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'data' => null
    ]);
}