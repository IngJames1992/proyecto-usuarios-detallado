<?php
/**
 * =====================================================
 * VALIDADORES - Demostración de SOLID
 * =====================================================
 * 
 * SOLID - S (Single Responsibility):
 *   Cada validador tiene UNA sola responsabilidad
 * 
 * SOLID - I (Interface Segregation):
 *   Interfaces pequeñas y específicas, no una gigante
 */

/**
 * Interfaz para validadores de email
 */
interface EmailValidatorInterface {
    public function validateEmail($email);
}

/**
 * Interfaz para validadores de texto
 */
interface TextValidatorInterface {
    public function validateText($text, $minLength, $maxLength);
}

/**
 * =====================================================
 * CLASE: UserValidator
 * Valida datos de usuarios
 * =====================================================
 * 
 * SOLID - I: Implementa solo las interfaces que necesita
 * SOLID - S: Solo valida, no hace persistencia ni lógica de negocio
 */
class UserValidator implements EmailValidatorInterface, TextValidatorInterface {
    private $errors = [];

    /**
     * Validar email
     * 
     * @param string $email Email a validar
     * @return bool True si es válido
     */
    public function validateEmail($email) {
        // Eliminar espacios
        $email = trim($email);
        
        // Verificar que no esté vacío
        if (empty($email)) {
            $this->errors[] = "El email es obligatorio";
            return false;
        }
        
        // Verificar longitud máxima
        if (strlen($email) > 150) {
            $this->errors[] = "El email es demasiado largo (máx 150 caracteres)";
            return false;
        }
        
        // Validar formato con filtro nativo de PHP
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "El formato del email no es válido";
            return false;
        }
        
        // Validar que el dominio tenga al menos un punto
        $parts = explode('@', $email);
        if (count($parts) !== 2 || strpos($parts[1], '.') === false) {
            $this->errors[] = "El dominio del email no es válido";
            return false;
        }
        
        return true;
    }

    /**
     * Validar texto con longitud
     * 
     * @param string $text Texto a validar
     * @param int $minLength Longitud mínima
     * @param int $maxLength Longitud máxima
     * @return bool True si es válido
     */
    public function validateText($text, $minLength = 1, $maxLength = 255) {
        $text = trim($text);
        $length = strlen($text);
        
        if ($length < $minLength) {
            $this->errors[] = "El texto debe tener al menos {$minLength} caracteres";
            return false;
        }
        
        if ($length > $maxLength) {
            $this->errors[] = "El texto no puede superar {$maxLength} caracteres";
            return false;
        }
        
        return true;
    }

    /**
     * Validar nombre de usuario
     * 
     * @param string $nombre Nombre a validar
     * @return bool True si es válido
     */
    public function validateNombre($nombre) {
        if (!$this->validateText($nombre, 3, 100)) {
            return false;
        }
        
        // Verificar que solo contenga letras, espacios y acentos
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
            $this->errors[] = "El nombre solo puede contener letras y espacios";
            return false;
        }
        
        return true;
    }

    /**
     * Validar datos completos del usuario
     * 
     * @param array $data Datos del usuario
     * @return bool True si todos los datos son válidos
     */
    public function validateUserData($data) {
        $this->errors = []; // Limpiar errores previos
        
        $isValid = true;
        
        // Validar nombre
        if (!isset($data['nombre']) || !$this->validateNombre($data['nombre'])) {
            $isValid = false;
        }
        
        // Validar email
        if (!isset($data['email']) || !$this->validateEmail($data['email'])) {
            $isValid = false;
        }
        
        // Validar tipo de usuario
        if (isset($data['tipo_usuario'])) {
            if (!in_array($data['tipo_usuario'], ['admin', 'normal'])) {
                $this->errors[] = "Tipo de usuario inválido";
                $isValid = false;
            }
        }
        
        return $isValid;
    }

    /**
     * Obtener errores de validación
     * 
     * @return array Array de mensajes de error
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Verificar si hay errores
     * 
     * @return bool True si hay errores
     */
    public function hasErrors() {
        return count($this->errors) > 0;
    }

    /**
     * Limpiar errores
     */
    public function clearErrors() {
        $this->errors = [];
    }
}

/**
 * =====================================================
 * EJEMPLO DE USO
 * =====================================================
 */

/*
// Crear validador
$validator = new UserValidator();

// Datos a validar
$userData = [
    'nombre' => 'Juan Pérez',
    'email' => 'juan@email.com',
    'tipo_usuario' => 'normal'
];

// Validar
if ($validator->validateUserData($userData)) {
    echo "✓ Datos válidos\n";
} else {
    echo "✗ Errores encontrados:\n";
    foreach ($validator->getErrors() as $error) {
        echo "  - $error\n";
    }
}
*/
