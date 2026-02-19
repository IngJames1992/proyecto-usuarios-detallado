<?php
/**
 * =====================================================
 * API: Actualizar Usuario
 * Endpoint: PUT /php/api/update_user.php
 * =====================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../validators/UserValidator.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['id'])) {
        throw new Exception('ID de usuario requerido');
    }
    
    $repository = new UserRepository();
    
    // Verificar que el usuario existe
    $userExistente = $repository->findById($data['id']);
    
    if (!$userExistente) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no encontrado'
        ]);
        exit;
    }
    
    // Validar nuevos datos
    $validator = new UserValidator();
    
    if (!$validator->validateUserData($data)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => $validator->getErrors()
        ]);
        exit;
    }
    
    // Verificar que el email no esté en uso por otro usuario
    if ($repository->emailExists($data['email'], $data['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El email ya está en uso'
        ]);
        exit;
    }
    
    // Actualizar datos del usuario
    $userExistente->setNombre($data['nombre']);
    $userExistente->setEmail($data['email']);
    
    if (isset($data['tipo_usuario'])) {
        $userExistente->setTipoUsuario($data['tipo_usuario']);
    }
    
    // Guardar cambios
    $actualizado = $repository->update($userExistente);
    
    if (!$actualizado) {
        throw new Exception('Error al actualizar el usuario');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado exitosamente',
        'data' => $userExistente->toArray()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
