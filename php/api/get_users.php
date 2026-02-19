<?php
/**
 * =====================================================
 * API: Obtener Usuarios
 * Endpoint: GET /php/api/get_users.php
 * =====================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

try {
    $repository = new UserRepository();
    
    // Construir filtros desde parámetros GET
    $filtros = [];
    
    if (isset($_GET['tipo_usuario'])) {
        $filtros['tipo_usuario'] = $_GET['tipo_usuario'];
    }
    
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' || $_GET['activo'] === 'true';
    }
    
    if (isset($_GET['busqueda'])) {
        $filtros['busqueda'] = $_GET['busqueda'];
    }
    
    // Ordenamiento
    $filtros['orden'] = $_GET['orden'] ?? 'id';
    $filtros['direccion'] = $_GET['direccion'] ?? 'DESC';
    
    // Obtener usuarios
    $usuarios = $repository->findAll($filtros);
    
    // Convertir objetos User a arrays
    $usuariosArray = array_map(function($user) {
        return $user->toArray();
    }, $usuarios);
    
    // Obtener estadísticas
    $stats = $repository->getStatistics();
    
    echo json_encode([
        'success' => true,
        'data' => $usuariosArray,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
