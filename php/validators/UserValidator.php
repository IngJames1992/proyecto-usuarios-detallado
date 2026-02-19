<?php
// ============================================================================
// ARCHIVO: UserValidator.php
// UBICACIÓN: php/validators/UserValidator.php
// PROPÓSITO: Validación de datos de usuarios antes de procesar o guardar
// ============================================================================
//
// DESCRIPCIÓN GENERAL:
// Este archivo implementa el PATRÓN VALIDATOR para la entidad User.
// Centraliza TODAS las reglas de validación en una sola clase,
// separándolas completamente de la lógica de negocio y la capa de datos.
//
// RESPONSABILIDAD ÚNICA (Single Responsibility Principle):
// UserValidator SOLO se encarga de:
//   ✓ Verificar que los datos cumplan las reglas definidas
//   ✓ Acumular mensajes de error descriptivos
//   ✓ Informar si hay errores y cuáles son
//
// NO se encarga de:
//   ✗ Guardar datos (eso es UserRepository)
//   ✗ Mostrar datos al usuario (eso es la vista)
//   ✗ Crear objetos User (eso es UserFactory)
//   ✗ Lógica de negocio (eso es el controlador)
//
// DÓNDE SE USA EN EL FLUJO:
// ============================================================================
//
//   [Frontend] → Envía datos → [PHP endpoint]
//                                    │
//                                    ▼
//                              UserValidator::validateUserData($data)
//                                    │
//                          ┌─────────┴──────────┐
//                          │ false (inválido)    │ true (válido)
//                          ▼                    ▼
//                    getErrors()          UserRepository::create()
//                    → ['nombre muy       → INSERT en BD
//                       corto', ...]      → Respuesta 201
//                    → Respuesta 400
//
// VALIDACIONES INCLUIDAS:
// ============================================================================
//   validateEmail()    → Obligatorio, máx 150, formato, dominio con punto
//   validateText()     → Longitud mínima y máxima (reutilizable)
//   validateNombre()   → Usa validateText + solo letras/espacios/acentos
//   validateUserData() → Orquesta todas las validaciones juntas
//
// TECNOLOGÍAS Y CONCEPTOS:
// ============================================================================
//   • Patrón Validator (acumulación de errores)
//   • empty() vs isset() vs strlen() == 0
//   • strlen() para longitud de strings
//   • filter_var() con FILTER_VALIDATE_EMAIL (filtro nativo PHP)
//   • explode() para dividir strings por delimitador
//   • count() para contar elementos de array
//   • strpos() para buscar substring (y truco === false)
//   • preg_match() para validar con expresiones regulares
//   • Regex con caracteres latinos: á é í ó ú ñ Ñ
//   • trim() para eliminar espacios extremos
//   • in_array() para lista blanca de valores
//   • isset() para verificar claves de array
//   • Patrón Early Return en validaciones
//   • Reutilización: validateNombre() llama validateText()
//   • Evaluación de cortocircuito con ||
//   • Parámetros con valores por defecto
//   • Interpolación en mensajes de error: {$minLength}
//
// COMPARACIÓN PHP vs JAVASCRIPT:
// ============================================================================
//   PHP                               JavaScript
//   ─────────────────────────────     ──────────────────────────────
//   filter_var($e, FILTER_VAL_EMAIL)  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)
//   preg_match('/patron/', $str)      /patron/.test(str)
//   strlen($str)                      str.length
//   explode('@', $email)              email.split('@')
//   count($array)                     array.length
//   strpos($str, '.')                 str.indexOf('.')
//   in_array($val, $arr)              arr.includes(val)
//   empty($val)                       !val  (aproximado)
//   trim($str)                        str.trim()
//
// ============================================================================

/**
 * ============================================================================
 * CLASE: UserValidator
 * ============================================================================
 * PROPÓSITO:
 *   Validar datos de usuarios acumulando todos los errores encontrados.
 *   Permite validar campos individualmente o el conjunto completo.
 *
 * PATRÓN: Validator
 *
 * PRINCIPIOS SOLID:
 *   S - Single Responsibility: Solo valida, no guarda ni muestra
 *   O - Open/Closed: Agregar validateTelefono() sin tocar métodos existentes
 *
 * VALIDACIÓN EN CAPAS:
 *   validateText()     ← Base reutilizable (longitud)
 *       └── validateNombre() ← Usa validateText + reglas propias (regex)
 *   validateEmail()    ← Independiente (reglas específicas de email)
 *   validateUserData() ← Orquestador (llama a los anteriores)
 *
 * USO BÁSICO:
 *   $v = new UserValidator();
 *   if (!$v->validateUserData($data)) {
 *       echo json_encode($v->getErrors());
 *   }
 */
class UserValidator {

    // ========================================================================
    // PROPIEDAD: $errors
    // ========================================================================

    private $errors = [];
    // ========================================================================
    // ARRAY $errors — Acumulador de mensajes de error
    // ========================================================================
    // TIPO: array de strings
    // VALOR INICIAL: [] (array vacío = sin errores al inicio)
    // VISIBILIDAD: private (solo esta clase lo gestiona)
    //
    // ¿POR QUÉ ACUMULAR ERRORES EN UN ARRAY?
    //
    // OPCIÓN A: Lanzar excepción al primer error (falla rápido):
    //   validateNombre('A');
    //   → throws Exception("Nombre muy corto")
    //   → El código se detiene, no valida email ni tipo
    //   → El usuario solo ve UN error a la vez
    //   → Tiene que corregir, reenviar, ver otro error, etc.
    //
    // OPCIÓN B: Acumular TODOS los errores (este enfoque) ✓:
    //   validateUserData(['nombre' => 'A', 'email' => 'mal', 'tipo' => '?'])
    //   → $errors = [
    //         'El nombre debe tener al menos 3 caracteres',
    //         'El formato del email no es válido',
    //         'Tipo de usuario inválido'
    //     ]
    //   → El usuario ve TODOS los problemas de una vez
    //   → Mejor UX (User Experience)
    //
    // ANALOGÍA:
    // Es como un corrector de formularios que subraya
    // TODOS los campos con error, no solo el primero.
    //
    // FLUJO DEL ARRAY:
    // Inicio:     $errors = []
    // Error 1:    $errors = ['Nombre muy corto']
    // Error 2:    $errors = ['Nombre muy corto', 'Email inválido']
    // getErrors() → devuelve el array completo
    // clearErrors() → $errors = [] (reiniciar)
    //
    // INICIALIZACIÓN = []:
    // Equivale a: private $errors;
    //             → Sin inicializar, sería null (problemático)
    // Con = []: Siempre es array, incluso sin constructor
    // ========================================================================

    // ========================================================================
    // MÉTODO: validateEmail
    // ========================================================================

    /**
     * Validar dirección de email
     *
     * REGLAS (en orden de prioridad):
     * 1. No puede estar vacío
     * 2. Máximo 150 caracteres
     * 3. Formato válido según RFC (filter_var)
     * 4. Dominio debe contener al menos un punto
     *
     * @param  string $email Email a validar
     * @return bool          true si es válido, false si hay errores
     */
    public function validateEmail($email) {

        // REGLA 1: Email obligatorio
        if (empty($email)) {
            // ================================================================
            // empty() — Verificar si una variable está "vacía"
            // ================================================================
            // SINTAXIS:
            // empty($variable)
            //
            // ¿QUÉ CONSIDERA "VACÍO" PHP?
            // empty() devuelve true para:
            //   ""        → String vacío
            //   "0"       → String "cero"
            //   0         → Entero cero
            //   0.0       → Float cero
            //   []        → Array vacío
            //   null      → Valor nulo
            //   false     → Booleano falso
            //   Variable no definida → Sin error (a diferencia de isset())
            //
            // empty() devuelve false para:
            //   "hola"    → String con contenido
            //   " "       → Solo espacios (¡OJO! considerado "no vacío")
            //   1, -1     → Números distintos de 0
            //   [1, 2]    → Array con elementos
            //   true      → Booleano verdadero
            //
            // ¿POR QUÉ empty() PARA EMAIL?
            // - Detecta: "" (campo vacío), null (no enviado), false
            //
            // NOTA IMPORTANTE SOBRE " " (SOLO ESPACIOS):
            // empty("   ") → FALSE (no considera vacío)
            // Por eso en validateText() se usa trim() antes de strlen()
            // "   ".trim() → "" → strlen = 0 → detecta vacío real
            //
            // DIFERENCIA CON isset():
            // isset($var)  → false si no existe O si es null
            // empty($var)  → false si no existe, null, "", 0, false, []
            // empty() es más estricto para detectar "sin valor útil"
            //
            // RELACIÓN CON JAVASCRIPT:
            // PHP:        empty($email)
            // JavaScript: !email || email === ""   (aproximado)
            // ================================================================

            $this->errors[] = "El email es obligatorio";
            return false;
            // ================================================================
            // PATRÓN EARLY RETURN
            // ================================================================
            // Si el email está vacío, no tiene sentido seguir validando:
            // ¿Para qué verificar el formato de un string vacío?
            //
            // return false: indica validación fallida
            // El método termina aquí si el email está vacío
            //
            // $this->errors[]: Agrega el mensaje al array (no reemplaza)
            // ================================================================
        }

        // REGLA 2: Longitud máxima
        if (strlen($email) > 150) {
            // ================================================================
            // strlen() — Longitud de un string
            // ================================================================
            // SINTAXIS:
            // strlen($string)
            //
            // ¿QUÉ DEVUELVE?
            // - El número de BYTES del string (no caracteres Unicode)
            // - Para ASCII: bytes = caracteres (idéntico)
            // - Para UTF-8 (ñ, á, é): 1 carácter = 2 bytes
            //
            // EJEMPLOS:
            // strlen("hola")           → 4
            // strlen("hola mundo")     → 10
            // strlen("")               → 0
            // strlen("ñoño")           → 6  (ñ = 2 bytes, ó = 2 bytes)
            //
            // PARA CONTAR CARACTERES UNICODE CORRECTAMENTE:
            // mb_strlen("ñoño")        → 4 (mb = multi-byte)
            // mb_strlen("hola")        → 4 (igual para ASCII)
            //
            // ¿POR QUÉ MÁXIMO 150?
            // - La columna email en la BD es VARCHAR(150)
            // - Previene truncamiento silencioso en MySQL
            // - Un email real raramente supera 100 caracteres
            //
            // EARLY RETURN:
            // Si supera 150, no vale la pena validar formato
            // (un email tan largo probablemente es inválido de todas formas)
            //
            // RELACIÓN CON JAVASCRIPT:
            // PHP:        strlen($email) > 150
            // JavaScript: email.length > 150
            // ================================================================

            $this->errors[] = "El email es demasiado largo (máx 150 caracteres)";
            return false;
        }

        // REGLA 3: Formato válido con filtro nativo de PHP
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // ================================================================
            // filter_var() con FILTER_VALIDATE_EMAIL
            // ================================================================
            // SINTAXIS:
            // filter_var($variable, $filtro)
            //
            // ¿QUÉ HACE?
            // - Aplica un filtro de validación o saneamiento a una variable
            // - Devuelve el valor filtrado si es válido, o false si falla
            //
            // FILTER_VALIDATE_EMAIL:
            // - Constante de PHP para validar formato de email
            // - Implementa las reglas del RFC 5322 (estándar de emails)
            // - Verifica: usuario@dominio.extension
            //
            // RETORNA:
            // - El string del email si es válido:  'juan@email.com'
            // - false si el formato es inválido
            //
            // EJEMPLOS:
            // filter_var('juan@email.com',   FILTER_VALIDATE_EMAIL) → 'juan@email.com'
            // filter_var('juan@email',       FILTER_VALIDATE_EMAIL) → false
            // filter_var('juanemail.com',    FILTER_VALIDATE_EMAIL) → false (sin @)
            // filter_var('@email.com',       FILTER_VALIDATE_EMAIL) → false (sin usuario)
            // filter_var('j@e.c',            FILTER_VALIDATE_EMAIL) → 'j@e.c' (válido!)
            // filter_var('juan+tag@mail.co', FILTER_VALIDATE_EMAIL) → válido (+ permitido)
            //
            // !filter_var(...):
            // - Si devuelve false → !false = true → entra al if → agrega error
            // - Si devuelve email → !email = false → no entra al if → continúa
            //
            // ¿POR QUÉ USAR FILTRO NATIVO Y NO REGEX MANUAL?
            //
            // REGEX MANUAL (frágil):
            // preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i', $email)
            // - Difícil de mantener
            // - Puede rechazar emails válidos (ej: con caracteres especiales)
            // - Puede aceptar emails inválidos
            //
            // FILTRO NATIVO (robusto):
            // filter_var($email, FILTER_VALIDATE_EMAIL)
            // - Implementado en C (más rápido)
            // - Mantenido por el equipo de PHP
            // - Actualizado con los estándares RFC
            //
            // OTROS FILTROS COMUNES:
            // FILTER_VALIDATE_INT      → Verificar si es entero
            // FILTER_VALIDATE_FLOAT    → Verificar si es decimal
            // FILTER_VALIDATE_URL      → Verificar si es URL válida
            // FILTER_VALIDATE_IP       → Verificar si es dirección IP
            // FILTER_VALIDATE_BOOLEAN  → Verificar si es booleano
            // FILTER_SANITIZE_EMAIL    → Eliminar caracteres inválidos (saneamiento)
            // FILTER_SANITIZE_URL      → Limpiar URL
            //
            // RELACIÓN CON JAVASCRIPT:
            // PHP:        filter_var($email, FILTER_VALIDATE_EMAIL)
            // JavaScript: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)  (manual)
            //             o librerías como validator.js
            // ================================================================

            $this->errors[] = "El formato del email no es válido";
            return false;
        }

        // REGLA 4: Verificar que el dominio tenga al menos un punto
        $parts = explode('@', $email);
        // ====================================================================
        // explode() — Dividir string en array usando delimitador
        // ====================================================================
        // SINTAXIS:
        // explode($delimitador, $string);
        // explode($delimitador, $string, $limite);
        //
        // ¿QUÉ HACE?
        // - Divide el string en partes usando el delimitador
        // - Devuelve un array con las partes
        //
        // EJEMPLOS:
        // explode('@', 'juan@email.com')    → ['juan', 'email.com']
        // explode('@', 'juan@email@com')    → ['juan', 'email', 'com']
        // explode('@', 'juanemail.com')     → ['juanemail.com']  (sin @)
        // explode('.', 'email.com')         → ['email', 'com']
        // explode(',', 'a,b,c')             → ['a', 'b', 'c']
        //
        // CON LÍMITE:
        // explode('@', 'a@b@c', 2) → ['a', 'b@c'] (máximo 2 partes)
        //
        // PARA ESTE EMAIL:
        // $email = 'juan@email.com'
        // $parts = explode('@', 'juan@email.com')
        // $parts = ['juan', 'email.com']
        //
        // $parts[0] = 'juan'       ← Parte local (antes del @)
        // $parts[1] = 'email.com'  ← Dominio (después del @)
        //
        // RELACIÓN CON JAVASCRIPT:
        // PHP:        explode('@', $email)
        // JavaScript: email.split('@')
        // ====================================================================

        if (count($parts) !== 2 || strpos($parts[1], '.') === false) {
            // ================================================================
            // DOS VERIFICACIONES EN UNA CONDICIÓN CON ||
            // ================================================================
            // CONDICIÓN 1: count($parts) !== 2
            // ════════════════════════════════
            // count() → Cantidad de elementos en el array
            //
            // count(['juan', 'email.com']) → 2     ← Correcto
            // count(['juanemail.com'])     → 1     ← Sin @ (1 parte)
            // count(['a', 'b', 'c'])       → 3     ← Dos @ (3 partes)
            //
            // !==: Comparación estricta (valor Y tipo)
            // count() devuelve int → 2 !== 2 = false → condición no se cumple
            //
            // PERO: filter_var ya habría rechazado emails sin @ o con doble @
            // Esta verificación es una capa extra de seguridad (defensa en profundidad)
            //
            // CONDICIÓN 2: strpos($parts[1], '.') === false
            // ════════════════════════════════════════════
            // strpos() — Encontrar posición de substring
            //
            // SINTAXIS:
            // strpos($haystack, $needle)
            // $haystack: String donde buscar ("el pajar")
            // $needle:   String a buscar    ("la aguja")
            //
            // RETORNA:
            // - int: Posición de la primera coincidencia (0 = primera posición)
            // - false: Si no encuentra el substring
            //
            // EJEMPLOS:
            // strpos('email.com', '.')  → 5    (el punto está en posición 5)
            // strpos('emailcom', '.')   → false (no hay punto)
            // strpos('a.b.c', '.')      → 1    (primera posición del punto)
            // strpos('hola', 'l')       → 2    (posición 2)
            // strpos('hola', 'z')       → false (no está)
            //
            // TRUCO CRÍTICO: === false (COMPARACIÓN ESTRICTA)
            // ════════════════════════════════════════════════
            // ¿POR QUÉ === Y NO ==?
            //
            // strpos('.email.com', '.') → 0 (el punto está en posición 0)
            //
            // CON == (comparación no estricta):
            // 0 == false → TRUE  ← ¡ERROR! Interpreta posición 0 como "no encontrado"
            //
            // CON === (comparación estricta):
            // 0 === false → FALSE ← Correcto: posición 0 es válida, no es false
            //
            // RESUMEN:
            // strpos() == false  → BUGGY (0 y false son "iguales" con ==)
            // strpos() === false → CORRECTO (distingue 0 del bool false)
            //
            // ESTE ES UN ERROR CLÁSICO EN PHP:
            // if (strpos($str, $needle))          ← BUG: falla si está en posición 0
            // if (strpos($str, $needle) !== false) ← CORRECTO
            //
            // RELACIÓN CON JAVASCRIPT:
            // PHP:        strpos($str, '.') === false
            // JavaScript: str.indexOf('.') === -1   (JS devuelve -1, no false)
            //
            // || (OR de evaluación en cortocircuito):
            // count($parts) !== 2 → true  → ¡No evalúa strpos! (cortocircuito)
            // count($parts) !== 2 → false → Evalúa strpos (necesario)
            // ================================================================

            $this->errors[] = "El dominio del email no es válido";
            return false;
        }

        return true;
        // ====================================================================
        // Solo llega aquí si PASÓ todas las validaciones
        // return true = email válido en todos los aspectos
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: validateText
    // BASE REUTILIZABLE para validar longitud de cualquier texto
    // ========================================================================

    /**
     * Validar longitud de un texto
     *
     * MÉTODO BASE reutilizado por:
     * - validateNombre(): validateText($nombre, 3, 100)
     * - Cualquier otro campo de texto en el futuro
     *
     * PARÁMETROS CON VALORES POR DEFECTO:
     * $minLength = 1   → Al menos 1 carácter (no vacío)
     * $maxLength = 255 → Máximo 255 (límite común de VARCHAR)
     *
     * @param  string $text      Texto a validar
     * @param  int    $minLength Longitud mínima (default: 1)
     * @param  int    $maxLength Longitud máxima (default: 255)
     * @return bool              true si está dentro del rango
     */
    public function validateText($text, $minLength = 1, $maxLength = 255) {
        // ====================================================================
        // PARÁMETROS CON VALORES POR DEFECTO
        // ====================================================================
        // SINTAXIS:
        // function metodo($param = valorDefault) { ... }
        //
        // $minLength = 1:
        // - Si se llama validateText($text)         → minLength = 1
        // - Si se llama validateText($text, 3)      → minLength = 3
        // - Si se llama validateText($text, 3, 100) → minLength = 3, maxLength = 100
        //
        // PERMITE REUTILIZAR EL MISMO MÉTODO:
        // validateText($nombre, 3, 100)   ← Nombre: 3-100 caracteres
        // validateText($apellido, 2, 80)  ← Apellido: 2-80 caracteres
        // validateText($descripcion)      ← Descripción: 1-255 (default)
        //
        // RESTRICCIÓN:
        // Los parámetros con default DEBEN ir al final
        // CORRECTO:   function f($req, $opt = 1) {}
        // INCORRECTO: function f($opt = 1, $req) {} ← Error de PHP
        //
        // RELACIÓN CON JAVASCRIPT:
        // PHP:        function validateText($text, $minLength = 1, $maxLength = 255)
        // JavaScript: function validateText(text, minLength = 1, maxLength = 255)
        // ====================================================================

        $text = trim($text);
        // ====================================================================
        // trim() — Eliminar espacios al inicio y al final
        // ====================================================================
        // ¿QUÉ HACE?
        // - Elimina: espacios, tabs (\t), saltos de línea (\n, \r)
        // - Solo de los extremos, no del interior
        //
        // EJEMPLOS:
        // trim("  hola  ")      → "hola"
        // trim("\t hola \n")    → "hola"
        // trim("ho la")         → "ho la"  (espacio interior se conserva)
        // trim("")              → ""
        // trim("   ")           → ""  ← ¡Importante! Espacios → vacío
        //
        // ¿POR QUÉ TRIM ANTES DE CONTAR?
        // Sin trim: "   " → strlen = 3 → parece tener 3 caracteres (válido)
        // Con trim: "   " → "" → strlen = 0 → detecta como vacío (correcto)
        //
        // Usuarios suelen copiar/pegar texto con espacios extra
        // trim() normaliza antes de validar longitud real
        //
        // VARIANTES:
        // trim($str)       → Elimina espacios ambos extremos
        // ltrim($str)      → Solo inicio (left trim)
        // rtrim($str)      → Solo final  (right trim)
        // trim($str, 'xX') → Elimina caracteres específicos
        //
        // NOTA: trim() NO modifica $text en el lugar (strings son inmutables)
        // $text = trim($text) → Reasignar la variable local
        // El valor original pasado al método no se modifica (paso por valor)
        //
        // RELACIÓN CON JAVASCRIPT:
        // PHP:        trim($text)
        // JavaScript: text.trim()
        // ====================================================================

        $length = strlen($text);
        // ====================================================================
        // Guardar en variable para no llamar strlen() dos veces
        // Optimización pequeña pero buena práctica
        // ====================================================================

        if ($length < $minLength) {
            $this->errors[] = "El texto debe tener al menos {$minLength} caracteres";
            // ================================================================
            // INTERPOLACIÓN DE VARIABLE EN MENSAJE DE ERROR
            // ================================================================
            // "{$minLength}" → Se reemplaza con el valor actual de $minLength
            //
            // Si $minLength = 3:
            // "El texto debe tener al menos {$minLength} caracteres"
            // → "El texto debe tener al menos 3 caracteres"
            //
            // Si $minLength = 10:
            // → "El texto debe tener al menos 10 caracteres"
            //
            // MENSAJE DINÁMICO vs ESTÁTICO:
            // Estático: "El texto debe tener al menos 3 caracteres" ← Solo para 3
            // Dinámico: "El texto debe tener al menos {$min} caracteres" ← Para cualquier min
            // ================================================================
            return false;
        }

        if ($length > $maxLength) {
            $this->errors[] = "El texto no puede superar {$maxLength} caracteres";
            return false;
        }

        return true;
        // Solo llega aquí si: minLength <= length <= maxLength
    }

    // ========================================================================
    // MÉTODO: validateNombre
    // Usa validateText como base + agrega reglas de letras/acentos
    // ========================================================================

    /**
     * Validar nombre de usuario
     *
     * CAPAS DE VALIDACIÓN:
     * 1. validateText($nombre, 3, 100) → Longitud entre 3 y 100 chars
     * 2. preg_match(regex)             → Solo letras, espacios y acentos
     *
     * ACEPTA: 'Juan Pérez', 'María José', 'Ñoño García'
     * RECHAZA: 'Juan123', 'Juan@', 'Juan_Pérez', '12345'
     *
     * @param  string $nombre Nombre a validar
     * @return bool           true si es válido
     */
    public function validateNombre($nombre) {

        // Capa 1: Validar longitud (reutilizando validateText)
        if (!$this->validateText($nombre, 3, 100)) {
            return false;
            // ================================================================
            // COMPOSICIÓN DE MÉTODOS (Reutilización)
            // ================================================================
            // validateNombre NO duplica la lógica de longitud.
            // En su lugar, llama validateText() que ya la tiene.
            //
            // !$this->validateText($nombre, 3, 100):
            // - Si validateText devuelve false → !false = true → entra al if
            //   (hubo error de longitud, validateText ya agregó el mensaje)
            // - Si validateText devuelve true  → !true = false → continúa
            //   (longitud OK, seguir con la siguiente validación)
            //
            // IMPORTANTE: Si validateText falla, YA agregó el error al array.
            // validateNombre solo retorna false para detener la cadena.
            // No agrega un error redundante.
            //
            // PRINCIPIO DRY (Don't Repeat Yourself):
            // Sin composición (código duplicado ❌):
            // validateNombre() {
            //     $length = strlen(trim($nombre));
            //     if ($length < 3) { ... }  ← Duplicado de validateText
            //     if ($length > 100) { ... } ← Duplicado de validateText
            //     preg_match(...)
            // }
            //
            // Con composición (limpio ✓):
            // validateNombre() {
            //     if (!$this->validateText($nombre, 3, 100)) { return false; }
            //     preg_match(...)  ← Solo la regla específica de nombre
            // }
            // ================================================================
        }

        // Capa 2: Verificar que solo contenga letras, espacios y acentos
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
            // ================================================================
            // preg_match() — Verificar si un string cumple un patrón regex
            // ================================================================
            // SINTAXIS:
            // preg_match($patron, $string);
            // preg_match($patron, $string, $matches); ← Capturar grupos
            //
            // RETORNA:
            // 1  → Coincidencia encontrada (como true)
            // 0  → No coincide (como false)
            // false → Error en el patrón (regex mal formado)
            //
            // !preg_match():
            // - Si no coincide (0) → !0 = true → entra al if (error)
            // - Si coincide  (1) → !1 = false → no entra al if (válido)
            //
            // ────────────────────────────────────────────────────────────────
            // DESGLOSE DEL PATRÓN: "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/"
            // ────────────────────────────────────────────────────────────────
            //
            // /.../ → Delimitadores del patrón regex en PHP
            //
            // ^ → Ancla de inicio
            //   Significa: El patrón debe coincidir desde el INICIO del string
            //   Sin ^: El regex puede coincidir en cualquier posición intermedia
            //   Con ^: Debe coincidir desde el primer carácter
            //
            // [...] → Clase de caracteres
            //   Coincide con UN carácter del conjunto definido entre corchetes
            //
            // Dentro de [...]:
            //   a-z  → Todas las letras minúsculas: a, b, c, ..., z
            //   A-Z  → Todas las letras mayúsculas: A, B, C, ..., Z
            //   á é í ó ú   → Vocales con tilde minúsculas (español)
            //   Á É Í Ó Ú   → Vocales con tilde mayúsculas (español)
            //   ñ Ñ          → Ñ minúscula y mayúscula (español)
            //   \s           → Cualquier espacio en blanco (espacio, tab, \n)
            //
            // + → Cuantificador: uno o más del grupo anterior
            //   [...]+ → Uno o más caracteres del conjunto
            //   Sin +: Coincidiría con exactamente 1 carácter
            //   Con +: Coincide con 1, 2, 3... N caracteres
            //
            // $ → Ancla de fin
            //   Significa: El patrón debe coincidir hasta el FINAL del string
            //   Con ^ y $: El COMPLETO string debe coincidir (no solo parte)
            //
            // RESULTADO COMPLETO: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/
            // "El string COMPLETO (^ y $) debe estar formado
            //  por UNO O MÁS (+) caracteres que sean
            //  letras (a-z A-Z), vocales acentuadas, ñ, o espacios (\s)"
            //
            // EJEMPLOS:
            // "Juan Pérez"   → ✓ Letras + espacio + vocales acentuadas
            // "María José"   → ✓ Acentos y espacios
            // "Ñoño"         → ✓ Con ñ
            // "Juan123"      → ✗ Contiene dígitos (no están en la clase)
            // "Juan@Pérez"   → ✗ Contiene @
            // "Juan_García"  → ✗ Contiene _ (guion bajo)
            // "123"          → ✗ Solo dígitos
            // ""             → ✗ Vacío (+ requiere al menos 1)
            //
            // ¿POR QUÉ NO SOLO [a-zA-Z] SIN ACENTOS?
            // - Colombia: Juan Pérez, María García, Sebastián Ñoño
            // - Sin áéíóúñ: rechazaría nombres legítimos latinoamericanos
            // - La validación debe ser culturalmente apropiada
            //
            // RELACIÓN CON JAVASCRIPT:
            // PHP:        preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)
            // JavaScript: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)
            // ================================================================

            $this->errors[] = "El nombre solo puede contener letras y espacios";
            return false;
        }

        return true;
    }

    // ========================================================================
    // MÉTODO: validateUserData
    // ORQUESTADOR: Valida todos los campos del usuario juntos
    // ========================================================================

    /**
     * Validar datos completos de un usuario
     *
     * ORQUESTA todas las validaciones y acumula TODOS los errores.
     * No se detiene al encontrar el primer error (mejor UX).
     *
     * CAMPOS VALIDADOS:
     * - nombre:       Obligatorio, 3-100 chars, solo letras/espacios
     * - email:        Obligatorio, formato válido, dominio con punto
     * - tipo_usuario: Opcional, si existe debe ser 'admin' o 'normal'
     *
     * @param  array $data ['nombre' => ..., 'email' => ..., 'tipo_usuario' => ...]
     * @return bool        true si TODOS los campos son válidos
     */
    public function validateUserData($data) {

        $this->errors = [];
        // ====================================================================
        // LIMPIAR ERRORES PREVIOS ANTES DE VALIDAR
        // ====================================================================
        // ¿POR QUÉ LIMPIAR?
        // Si el validador se reutiliza:
        // $v->validateUserData($data1); // Falla, errors = ['Error A']
        // $v->validateUserData($data2); // Sin limpiar, errors = ['Error A', 'Error B']
        //
        // CON LIMPIEZA:
        // $v->validateUserData($data1); // Falla, errors = ['Error A']
        // $v->validateUserData($data2); // Limpia, errors = ['Error B'] ← Solo de data2
        //
        // = [] vs clearErrors():
        // Ambos hacen lo mismo. Aquí se hace directamente por conveniencia.
        // clearErrors() sería para limpiar manualmente desde afuera de la clase.
        // ====================================================================

        $isValid = true;
        // ====================================================================
        // BANDERA DE VALIDEZ
        // ====================================================================
        // Inicia en true (asumimos que todo es válido)
        // Si alguna validación falla → $isValid = false
        // Al final se devuelve esta bandera
        //
        // ¿POR QUÉ NO RETORNAR INMEDIATAMENTE AL FALLAR?
        // OPCIÓN A (retorno inmediato al primer error):
        //   if (!validateNombre) { return false; } // No valida email
        //
        // OPCIÓN B (bandera, continúa aunque haya errores) ✓:
        //   if (!validateNombre) { $isValid = false; } // Continúa
        //   if (!validateEmail)  { $isValid = false; } // También valida email
        //   return $isValid;
        //
        // Opción B es mejor: el usuario ve TODOS los errores de una vez
        // ====================================================================

        // Validar nombre
        if (!isset($data['nombre']) || !$this->validateNombre($data['nombre'])) {
            $isValid = false;
            // ================================================================
            // CONDICIÓN COMPUESTA CON || (OR)
            // ================================================================
            // PARTE 1: !isset($data['nombre'])
            // ─────────────────────────────────
            // isset($data['nombre']):
            //   true  → La clave 'nombre' existe en el array Y no es null
            //   false → La clave no existe O es null
            //
            // !isset($data['nombre']):
            //   true  → No existe la clave → debe fallar
            //   false → Existe la clave → continuar con la parte 2
            //
            // PARTE 2: !$this->validateNombre($data['nombre'])
            // ─────────────────────────────────────────────────
            // Se evalúa SOLO si isset() devolvió true (cortocircuito)
            // (Si no existe 'nombre', acceder a $data['nombre'] daría error)
            //
            // validateNombre($data['nombre']):
            //   true  → Nombre válido → !true = false → no suma error aquí
            //   false → Nombre inválido → !false = true → $isValid = false
            //
            // EVALUACIÓN DE CORTOCIRCUITO (Short-circuit evaluation):
            // Si la PARTE 1 es true (no existe la clave):
            //   → PHP NO evalúa la PARTE 2 (no es necesario)
            //   → $isValid = false (la condición completa es true)
            //
            // Si la PARTE 1 es false (existe la clave):
            //   → PHP EVALÚA la PARTE 2 (necesita saber si es válido)
            //
            // RELACIÓN CON JAVASCRIPT:
            // PHP:        !isset($data['nombre']) || !$this->validateNombre(...)
            // JavaScript: !data.nombre || !this.validateNombre(data.nombre)
            //             (JS no tiene isset, undefined es falsy)
            //
            // NOTA SOBRE MENSAJES DE ERROR:
            // - Si !isset → No hay mensaje específico (validateNombre no se llamó)
            //   El campo simplemente falta → considerarlo como error
            // - Si !validateNombre → validateNombre ya agregó el error al array
            //
            // MEJORA POSIBLE:
            // if (!isset($data['nombre'])) {
            //     $this->errors[] = "El nombre es obligatorio";
            //     $isValid = false;
            // } elseif (!$this->validateNombre($data['nombre'])) {
            //     $isValid = false;
            // }
            // Más explícito pero más código
            // ================================================================
        }

        // Validar email
        if (!isset($data['email']) || !$this->validateEmail($data['email'])) {
            $isValid = false;
            // Mismo patrón que nombre: verificar existencia + validar valor
        }

        // Validar tipo de usuario (campo OPCIONAL)
        if (isset($data['tipo_usuario'])) {
            // ================================================================
            // CAMPO OPCIONAL: tipo_usuario
            // ================================================================
            // Si NO existe en $data → No validar (es opcional, OK sin él)
            // Si SÍ existe en $data → Debe tener un valor válido
            //
            // CONTRASTE CON NOMBRE Y EMAIL (campos obligatorios):
            // Nombre/Email: !isset → error (son requeridos)
            // tipo_usuario: !isset → sin error (es opcional)
            //               isset  → debe ser 'admin' o 'normal'
            //
            // CASOS:
            // $data = ['nombre' => 'Juan', 'email' => 'j@e.com']
            //   → tipo_usuario no existe → No se valida → OK
            //
            // $data = ['nombre' => 'Juan', 'email' => 'j@e.com', 'tipo_usuario' => 'admin']
            //   → tipo_usuario existe → Se valida → OK (está en la lista)
            //
            // $data = ['nombre' => 'Juan', 'email' => 'j@e.com', 'tipo_usuario' => 'superadmin']
            //   → tipo_usuario existe → Se valida → ERROR (no está en la lista)
            // ================================================================

            if (!in_array($data['tipo_usuario'], ['admin', 'normal'])) {
                // ============================================================
                // in_array() — Verificar si un valor existe en un array
                // ============================================================
                // SINTAXIS:
                // in_array($valor, $array);
                // in_array($valor, $array, $strict);
                //
                // ¿QUÉ HACE?
                // - Busca $valor dentro de $array
                // - Devuelve true si lo encuentra, false si no
                //
                // EJEMPLOS:
                // in_array('admin',      ['admin', 'normal']) → true
                // in_array('normal',     ['admin', 'normal']) → true
                // in_array('superadmin', ['admin', 'normal']) → false
                // in_array('Admin',      ['admin', 'normal']) → false (case-sensitive)
                // in_array(1,            [1, 2, 3])           → true
                //
                // PARÁMETRO $strict (3er argumento):
                // Sin $strict (por defecto false):
                //   in_array("1", [1, 2]) → true  (PHP convierte "1" a 1)
                // Con $strict = true:
                //   in_array("1", [1, 2], true) → false (tipos diferentes)
                //
                // PATRÓN WHITELIST (Lista Blanca):
                // ['admin', 'normal'] es la lista de valores PERMITIDOS
                // Solo estos dos valores son aceptados
                // Cualquier otro → error de validación
                //
                // SEGURIDAD:
                // Si alguien envía tipo_usuario = 'superadmin' o 'root'
                // → in_array lo rechaza → protege contra manipulación
                //
                // RELACIÓN CON JAVASCRIPT:
                // PHP:        in_array($val, ['admin', 'normal'])
                // JavaScript: ['admin', 'normal'].includes(val)
                // ============================================================

                $this->errors[] = "Tipo de usuario inválido";
                $isValid = false;
            }
        }

        return $isValid;
        // ====================================================================
        // Devuelve:
        // true  → Todos los campos son válidos (errors = [])
        // false → Al menos un campo es inválido (errors tiene mensajes)
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: getErrors
    // ========================================================================

    /**
     * Obtener todos los mensajes de error acumulados
     *
     * @return array Array de strings con los mensajes de error
     *
     * USO:
     *   if (!$v->validateUserData($data)) {
     *       echo json_encode($v->getErrors());
     *       // → ["El nombre es muy corto", "El email no es válido"]
     *   }
     */
    public function getErrors() {
        return $this->errors;
        // ====================================================================
        // GETTER SIMPLE
        // ====================================================================
        // Devuelve el array de errores tal cual
        //
        // ENCAPSULACIÓN:
        // $errors es private → código externo NO puede leer directamente
        // getErrors() → acceso controlado y explícito
        //
        // FORMATO DEL RESULTADO:
        // Sin errores: []
        // Con errores: [
        //     'El nombre debe tener al menos 3 caracteres',
        //     'El formato del email no es válido'
        // ]
        //
        // USO EN RESPUESTA JSON:
        // echo json_encode(['errors' => $v->getErrors()]);
        // → {"errors": ["El nombre es muy corto", "Email inválido"]}
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: hasErrors
    // ========================================================================

    /**
     * Verificar rápidamente si hay errores
     *
     * @return bool true si HAY errores, false si TODO está bien
     *
     * USO:
     *   $v->validateUserData($data);
     *   if ($v->hasErrors()) { ... }  // Más legible que count($v->getErrors()) > 0
     */
    public function hasErrors() {
        return count($this->errors) > 0;
        // ====================================================================
        // count() — Contar elementos de un array
        // ====================================================================
        // SINTAXIS:
        // count($array)
        //
        // RETORNA:
        // - El número de elementos en el array
        // - 0 si el array está vacío
        //
        // EJEMPLOS:
        // count([])                         → 0  (sin errores)
        // count(['Error 1'])                → 1
        // count(['Error 1', 'Error 2'])     → 2
        //
        // count($this->errors) > 0:
        // 0 > 0 → false ← Sin errores (validación exitosa)
        // 1 > 0 → true  ← Hay errores
        // 3 > 0 → true  ← Hay errores
        //
        // DEVUELVE BOOL DIRECTAMENTE:
        // La comparación > devuelve bool → se devuelve directamente
        //
        // VENTAJA DE ESTE MÉTODO:
        // Sin hasErrors():
        //   if (count($v->getErrors()) > 0) { ... }  ← Verboso
        //
        // Con hasErrors():
        //   if ($v->hasErrors()) { ... }              ← Legible y semántico
        //
        // ALTERNATIVA (menos explícita):
        // return !empty($this->errors);
        // empty([])  → true  → !true = false  ← Sin errores
        // empty(['E'])→ false → !false = true  ← Con errores
        //
        // RELACIÓN CON JAVASCRIPT:
        // PHP:        count($this->errors) > 0
        // JavaScript: this.errors.length > 0
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: clearErrors
    // ========================================================================

    /**
     * Limpiar todos los errores acumulados
     *
     * ÚTIL CUANDO:
     * - Se va a revalidar con nuevos datos
     * - Se quiere reiniciar el estado del validador
     *
     * USO:
     *   $v->validateEmail('mal');    // errors = ['Email inválido']
     *   $v->clearErrors();           // errors = []
     *   $v->validateEmail('ok@e.co'); // errors = [] (limpio)
     */
    public function clearErrors() {
        $this->errors = [];
        // ====================================================================
        // Reiniciar el array de errores
        // = [] asigna un array vacío (elimina todos los elementos anteriores)
        //
        // DIFERENCIA CON unset($this->errors):
        // unset($this->errors) → La propiedad deja de existir
        // $this->errors = []   → La propiedad sigue pero vacía
        //
        // [] es mejor porque:
        // - $this->errors siempre existe (no hay riesgo de "undefined")
        // - count([]) funciona (count de variable no definida daría warning)
        // ====================================================================
    }
}
// Fin de clase UserValidator


// ============================================================================
// EJEMPLO DE USO COMPLETO (comentado para referencia)
// ============================================================================
/*

// ─────────────────────────────────────────────────────────────────────────────
// CASO 1: Datos válidos
// ─────────────────────────────────────────────────────────────────────────────

$validator = new UserValidator();

$userData = [
    'nombre'       => 'Juan Pérez',
    'email'        => 'juan@email.com',
    'tipo_usuario' => 'normal'
];

if ($validator->validateUserData($userData)) {
    echo "✓ Datos válidos\n";
    // → Proceder a guardar con UserRepository
} else {
    echo "✗ Errores encontrados:\n";
    foreach ($validator->getErrors() as $error) {
        echo " - $error\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// CASO 2: Múltiples errores
// ─────────────────────────────────────────────────────────────────────────────

$dataMala = [
    'nombre'       => 'J',            // Muy corto (mín 3)
    'email'        => 'noesemail',    // Sin @
    'tipo_usuario' => 'superadmin'    // No existe
];

$validator->validateUserData($dataMala);
// $validator->getErrors() devuelve:
// [
//     "El texto debe tener al menos 3 caracteres",
//     "El formato del email no es válido",
//     "Tipo de usuario inválido"
// ]

// Respuesta HTTP 400:
http_response_code(400);
echo json_encode([
    'success' => false,
    'errors'  => $validator->getErrors()
]);
// → {"success":false,"errors":["El texto debe tener al menos 3 caracteres","..."]}

// ─────────────────────────────────────────────────────────────────────────────
// CASO 3: Validar campo individual
// ─────────────────────────────────────────────────────────────────────────────

$validator->clearErrors();
$esEmailValido = $validator->validateEmail('usuario@dominio.com');
if (!$esEmailValido) {
    print_r($validator->getErrors());
}

// ─────────────────────────────────────────────────────────────────────────────
// CASO 4: Uso en endpoint PHP (api/users.php)
// ─────────────────────────────────────────────────────────────────────────────

$data = json_decode(file_get_contents('php://input'), true);
$validator = new UserValidator();

if (!$validator->validateUserData($data)) {
    http_response_code(400);
    echo json_encode(['errors' => $validator->getErrors()]);
    exit;
}

// Datos válidos → crear usuario
$user = UserFactory::create($data);
$repo = new UserRepository();
$id = $repo->create($user);

http_response_code(201);
echo json_encode(['id' => $id, 'message' => 'Usuario creado']);

*/

// ============================================================================
// RESUMEN DE CONCEPTOS EXPLICADOS:
// ============================================================================
//
// PATRÓN VALIDATOR:
// - Acumulación de errores (no exception al primer error)
// - Separación de responsabilidades (solo valida)
// - Reutilización: validateNombre llama validateText
// - Validación en capas
//
// FUNCIONES PHP:
// - empty($var)                     → ¿Vacío? ("", 0, null, [], false)
// - strlen($str)                    → Longitud en bytes
// - filter_var($v, FILTER_*)        → Filtros nativos PHP
// - FILTER_VALIDATE_EMAIL           → Valida formato RFC 5322
// - explode($del, $str)             → Dividir string en array
// - count($arr)                     → Contar elementos
// - strpos($str, $needle)           → Posición de substring
// - === false                       → Comparación estricta (¡truco strpos!)
// - preg_match($pat, $str)          → Verificar con regex
// - trim($str)                      → Eliminar espacios extremos
// - in_array($val, $arr)            → Buscar en array (whitelist)
// - isset($var)                     → ¿Existe y no es null?
//
// REGEX DESGLOSADO: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/
// - ^       → Inicio del string
// - [...]   → Clase de caracteres
// - a-z     → Letras minúsculas
// - A-Z     → Letras mayúsculas
// - áéíóúñÑ → Caracteres españoles
// - \s      → Espacios en blanco
// - +       → Uno o más
// - $       → Final del string
//
// EVALUACIÓN CORTOCIRCUITO:
// !isset($data['k']) || !$this->validate($data['k'])
// Si !isset es true → no evalúa la segunda parte (seguro)
//
// PATRÓN EARLY RETURN:
// if (empty) { $errors[] = '...'; return false; }
// if (strlen > max) { $errors[] = '...'; return false; }
// return true; ← Solo si pasó todo
//
// PRINCIPIOS SOLID:
// S: Solo valida, no guarda ni muestra
// O: Agregar validateTelefono() sin modificar métodos existentes
// D: validateNombre depende de validateText (abstracción)
//
// ============================================================================
