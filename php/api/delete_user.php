<?php
/**
 * =====================================================
 * API: Eliminar Usuario
 * Endpoint: DELETE /php/api/delete_user.php?id=X
 * =====================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

try {
    // Obtener ID desde query string o desde body
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? null;
    }
    
    if (!$id) {
        throw new Exception('ID de usuario requerido');
    }
    
    $repository = new UserRepository();
    
    // Verificar que el usuario existe
    $user = $repository->findById($id);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no encontrado'
        ]);
        exit;
    }
    
    // Eliminar usuario (soft delete)
    $eliminado = $repository->delete($id);
    
    if (!$eliminado) {
        throw new Exception('Error al eliminar el usuario');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario eliminado exitosamente'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
