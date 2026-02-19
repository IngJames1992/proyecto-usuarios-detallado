<?php
/**
 * =====================================================
 * API: Crear Usuario
 * Endpoint: POST /php/api/create_user.php
 * =====================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Incluir dependencias
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../validators/UserValidator.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/NotificationManager.php';

try {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Datos inválidos');
    }
    
    // PASO 1: Validar datos
    $validator = new UserValidator();
    
    if (!$validator->validateUserData($data)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => $validator->getErrors()
        ]);
        exit;
    }
    
    // PASO 2: Verificar que el email no exista
    $repository = new UserRepository();
    
    if ($repository->emailExists($data['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El email ya está registrado'
        ]);
        exit;
    }
    
    // PASO 3: Crear objeto User usando Factory
    $user = UserFactory::create($data);
    
    // PASO 4: Guardar en base de datos
    $userId = $repository->create($user);
    
    if (!$userId) {
        throw new Exception('Error al crear el usuario');
    }
    
    // PASO 5: Enviar notificación de bienvenida
    // Usando patrón Strategy + Observer
    $notificationManager = new NotificationManager(new EmailNotification());
    
    // Agregar observadores
    $notificationManager->addObserver(new DatabaseLogger());
    $notificationManager->addObserver(new FileLogger());
    
    // Enviar notificación
    $notificationManager->notify(
        $user->getEmail(),
        "Bienvenido {$user->getNombre()}! Tu cuenta ha sido creada exitosamente.",
        $userId
    );
    
    // PASO 6: Responder con éxito
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Usuario creado exitosamente',
        'data' => [
            'id' => $userId,
            'nombre' => $user->getNombre(),
            'email' => $user->getEmail(),
            'tipo_usuario' => $user->getTipoUsuario()
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
