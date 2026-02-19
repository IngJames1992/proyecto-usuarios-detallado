<?php
// ============================================================================
// ARCHIVO: User.php
// UBICACIÓN: php/models/User.php
// PROPÓSITO: Modelo de datos del Usuario con patrones OOP, Factory y SOLID
// ============================================================================
//
// DESCRIPCIÓN GENERAL:
// Este archivo define TRES clases que trabajan en conjunto:
//
//   1. User          → Modelo base de usuario (propiedades + getters/setters)
//   2. AdminUser     → Usuario extendido con permisos (herencia)
//   3. UserFactory   → Fábrica que crea el tipo correcto (Factory Method)
//
// TECNOLOGÍAS Y CONCEPTOS:
// ============================================================================
//   • PHP Orientado a Objetos (OOP)
//   • Visibilidad: public, private, protected
//   • Getters y Setters (Encapsulación)
//   • Herencia (extends) y parent::
//   • Sobrescritura de métodos (override)
//   • Operador Null Coalescing (??)
//   • Funciones de string: trim, strtolower, strtoupper, substr, explode
//   • Funciones de array: in_array, array_diff
//   • Type Casting: (bool)
//   • Static methods (self::)
//   • switch / case
//   • foreach
//
// PATRONES DE DISEÑO IMPLEMENTADOS:
// ============================================================================
//   • Factory Method (UserFactory): Centraliza creación de objetos
//   • Value Object (User): Encapsula datos con getters/setters
//
// PRINCIPIOS SOLID APLICADOS:
// ============================================================================
//   • S - Single Responsibility: User solo gestiona datos de usuario
//   • O - Open/Closed: User cerrado a modificación, abierto a extensión
//   • L - Liskov Substitution: AdminUser reemplaza a User sin problemas
//
// JERARQUÍA DE CLASES:
// ============================================================================
//
//   User                    ← Clase base
//   └── AdminUser           ← Extiende User, agrega permisos
//
//   UserFactory             ← Crea User o AdminUser según el tipo
//
// FLUJO DE CREACIÓN:
// ============================================================================
//
//   $data = ['tipo_usuario' => 'admin', 'nombre' => 'Juan']
//       ↓
//   UserFactory::create($data)
//       ↓
//   switch('admin') → new AdminUser($data)
//       ↓
//   AdminUser::__construct() → parent::__construct() → User::__construct()
//       ↓
//   Objeto AdminUser listo con todas las propiedades y permisos
//
// ============================================================================

/**
 * ============================================================================
 * CLASE: User
 * ============================================================================
 * PROPÓSITO:
 *   Representar un usuario del sistema como objeto PHP.
 *   Encapsula todos los datos del usuario y controla su acceso.
 *
 * PATRÓN: Value Object / Model
 *
 * RESPONSABILIDAD (Single Responsibility Principle):
 *   - Almacenar datos del usuario (id, nombre, email, etc.)
 *   - Proporcionar acceso controlado (getters/setters)
 *   - Sanitizar datos al asignarlos
 *   - Ofrecer métodos de utilidad (toArray, isAdmin, getIniciales)
 *
 * USO:
 *   $user = new User([
 *       'id'           => 1,
 *       'nombre'       => 'Juan Pérez',
 *       'email'        => 'juan@gmail.com',
 *       'tipo_usuario' => 'normal',
 *       'activo'       => true
 *   ]);
 *   echo $user->getNombre(); // "Juan Pérez"
 */
class User {
    // ========================================================================
    // DECLARACIÓN DE PROPIEDADES
    // ========================================================================
    // En PHP, las propiedades de una clase se declaran aquí (antes del constructor)
    // Esto define QUÉ datos puede almacenar cada objeto User
    //
    // VISIBILIDAD:
    // ┌──────────┬─────────────────────────────────────────────────────────┐
    // │ private  │ Solo accesible DENTRO de esta clase                    │
    // │ protected│ Accesible en esta clase Y clases hijas (extends)       │
    // │ public   │ Accesible desde cualquier lugar                        │
    // └──────────┴─────────────────────────────────────────────────────────┘
    //
    // ¿POR QUÉ protected Y NO private?
    // - private: AdminUser NO podría acceder a $this->nombre
    // - protected: AdminUser SÍ puede acceder (herencia)
    //
    // ENCAPSULACIÓN:
    // - No se accede directamente: $user->nombre ← INCORRECTO (si es protected)
    // - Se accede por getters:     $user->getNombre() ← CORRECTO
    // - Se modifica por setters:   $user->setNombre('Juan') ← CORRECTO
    //
    // VENTAJA DE ENCAPSULACIÓN:
    // - Control total sobre lectura/escritura
    // - Setters pueden sanitizar antes de guardar
    // - Getters pueden transformar antes de devolver
    // - Cambiar implementación interna sin romper código externo
    // ========================================================================

    protected $id;
    // ========================================================================
    // PROPIEDAD $id - Identificador único
    // ========================================================================
    // TIPO: integer (asignado por base de datos) o null (usuario nuevo)
    // VALORES: null (no guardado), 1, 2, 3, ... (guardado en BD)
    //
    // ¿POR QUÉ protected?
    // - AdminUser hereda de User y necesita acceder a $this->id
    //
    // EJEMPLO:
    // Usuario nuevo:    $id = null (no tiene ID aún)
    // Usuario de BD:    $id = 42   (BD lo asignó)
    // ========================================================================

    protected $nombre;
    // ========================================================================
    // PROPIEDAD $nombre - Nombre completo del usuario
    // ========================================================================
    // TIPO: string
    // VALORES: "Juan Pérez", "María José García", etc.
    // SANITIZACIÓN: trim() al asignar (elimina espacios extra)
    // ========================================================================

    protected $email;
    // ========================================================================
    // PROPIEDAD $email - Correo electrónico del usuario
    // ========================================================================
    // TIPO: string
    // VALORES: "juan@gmail.com", "maria@empresa.co"
    // SANITIZACIÓN: strtolower() + trim() al asignar
    // RAZÓN: Emails siempre en minúsculas para consistencia
    // ========================================================================

    protected $tipoUsuario;
    // ========================================================================
    // PROPIEDAD $tipoUsuario - Rol del usuario en el sistema
    // ========================================================================
    // TIPO: string
    // VALORES VÁLIDOS: 'normal' | 'admin'
    // DEFAULT: 'normal'
    //
    // CONVENCIÓN: camelCase en PHP ($tipoUsuario)
    // EN BASE DE DATOS: snake_case (tipo_usuario)
    // La conversión ocurre en toArray() y en el constructor
    // ========================================================================

    protected $fechaCreacion;
    // ========================================================================
    // PROPIEDAD $fechaCreacion - Timestamp de registro
    // ========================================================================
    // TIPO: string | null
    // VALORES: "2024-01-15 10:30:00" (formato MySQL DATETIME) o null
    // ASIGNADA POR: Base de datos (DEFAULT CURRENT_TIMESTAMP)
    // ========================================================================

    protected $activo;
    // ========================================================================
    // PROPIEDAD $activo - Estado del usuario
    // ========================================================================
    // TIPO: bool (boolean)
    // VALORES: true (activo), false (inactivo/deshabilitado)
    //
    // SOFT DELETE:
    // En lugar de ELIMINAR físicamente de la BD,
    // se pone activo = false (soft delete)
    // - Preserva historial
    // - Permite reactivar
    // - Seguridad de datos
    // ========================================================================

    // ========================================================================
    // CONSTRUCTOR
    // ========================================================================

    /**
     * Constructor: Inicializar usuario con datos del array
     *
     * @param array $data Datos del usuario (de BD o formulario)
     *
     * USO:
     *   $user = new User([
     *       'id'           => 5,
     *       'nombre'       => 'Juan',
     *       'email'        => 'juan@email.com',
     *       'tipo_usuario' => 'normal',
     *       'activo'       => true
     *   ]);
     */
    public function __construct($data = []) {
        // ====================================================================
        // DECLARACIÓN DEL CONSTRUCTOR
        // ====================================================================
        // SINTAXIS:
        // public function __construct($data = []) {...}
        // │       │        │           │       │
        // │       │        │           │       └─ Valor por defecto: array vacío
        // │       │        │           └─ Parámetro $data
        // │       │        └─ Método mágico constructor
        // │       └─ Visibilidad pública
        // └─ Palabra clave para método
        //
        // ¿QUÉ ES __construct?
        // - Método especial que PHP ejecuta automáticamente al crear objeto
        // - El doble guión (__) indica método mágico de PHP
        // - Otros métodos mágicos: __toString, __get, __set, __destruct
        //
        // $data = []  ← Parámetro con valor por defecto
        // - Si se llama new User() sin argumentos → $data = []
        // - Si se llama new User(['nombre' => 'Juan']) → $data = ['nombre' => 'Juan']
        //
        // ¿POR QUÉ RECIBIR UN ARRAY?
        // En lugar de: new User(1, 'Juan', 'juan@email.com', 'normal', ...)
        // Usamos:       new User(['id' => 1, 'nombre' => 'Juan', ...])
        //
        // VENTAJAS DEL ARRAY:
        // ✓ No importa el orden de los parámetros
        // ✓ Campos opcionales (no todos son obligatorios)
        // ✓ Fácil de extender sin romper código existente
        // ✓ Mapeo directo desde BD (PDO devuelve arrays asociativos)
        // ====================================================================

        $this->id           = $data['id']           ?? null;
        // ====================================================================
        // OPERADOR NULL COALESCING (??)
        // ====================================================================
        // SINTAXIS:
        // $variable = $array['clave'] ?? valorPorDefecto;
        //
        // ¿QUÉ HACE ??
        // - Si $array['clave'] EXISTE y no es NULL → usa ese valor
        // - Si $array['clave'] NO EXISTE o ES NULL → usa el valor por defecto
        //
        // HISTORIA:
        // Antes de PHP 7 (antigua forma):
        //   $this->id = isset($data['id']) ? $data['id'] : null;
        //
        // PHP 7+ (operador ?? mucho más limpio):
        //   $this->id = $data['id'] ?? null;
        //
        // COMPARACIÓN:
        // ┌──────────────────────────┬──────────────────────────────────────┐
        // │ Situación                │ Resultado                            │
        // ├──────────────────────────┼──────────────────────────────────────┤
        // │ $data['id'] = 5          │ $this->id = 5                        │
        // │ $data['id'] = 0          │ $this->id = 0  (0 no es null)        │
        // │ $data['id'] = null       │ $this->id = null (valor por defecto) │
        // │ $data no tiene 'id'      │ $this->id = null (valor por defecto) │
        // └──────────────────────────┴──────────────────────────────────────┘
        //
        // $this → Referencia al objeto actual (igual que en JS pero con $)
        // ====================================================================

        $this->nombre       = $data['nombre']       ?? '';
        // ====================================================================
        // Si no hay nombre → string vacío '' (no null)
        // Razón: nombre es string, null causaría problemas
        // ====================================================================

        $this->email        = $data['email']        ?? '';

        $this->tipoUsuario  = $data['tipo_usuario'] ?? 'normal';
        // ====================================================================
        // CLAVE: 'tipo_usuario' (snake_case de BD)
        // PROPIEDAD: $tipoUsuario (camelCase de PHP)
        // DEFAULT: 'normal' (rol más básico)
        // ====================================================================

        $this->fechaCreacion = $data['fecha_creacion'] ?? null;
        // ====================================================================
        // null si no viene de BD (usuario nuevo)
        // BD la asigna automáticamente al insertar
        // ====================================================================

        $this->activo       = $data['activo']       ?? true;
        // ====================================================================
        // DEFAULT: true (usuario activo por defecto)
        // Un usuario nuevo se crea activo automáticamente
        // ====================================================================
    }

    // ========================================================================
    // GETTERS
    // ========================================================================
    // ¿QUÉ SON GETTERS?
    // - Métodos para LEER propiedades encapsuladas
    // - Convención: get + NombrePropiedad() → getNombre()
    // - Excepción: is + NombrePropiedad() para booleanos → isActivo()
    //
    // ¿POR QUÉ USAR GETTERS EN LUGAR DE ACCESO DIRECTO?
    //
    // ACCESO DIRECTO (sin getter):
    //   $user->nombre ← SOLO funciona si $nombre es public
    //
    // CON GETTER:
    //   $user->getNombre() ← Funciona siempre, más control
    //
    // VENTAJAS DE GETTERS:
    // ✓ Encapsulación (propiedades protected/private)
    // ✓ Transformación antes de devolver (ej: formatear fecha)
    // ✓ Logging (registrar qué propiedades se leen)
    // ✓ Lazy loading (calcular valor al primer acceso)
    // ✓ Validación adicional si es necesaria
    // ========================================================================

    /**
     * Obtener ID del usuario
     * @return int|null ID o null si es usuario nuevo
     */
    public function getId() {
        return $this->id;
        // ====================================================================
        // Devuelve el valor de la propiedad $id
        //
        // TIPOS POSIBLES:
        // - null: Usuario no guardado en BD
        // - int: Usuario guardado (BD asignó ID)
        //
        // USO:
        // $id = $user->getId();
        // if ($id === null) { // Usuario nuevo, aún no guardado }
        // ====================================================================
    }

    /**
     * Obtener nombre del usuario
     * @return string Nombre completo
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * Obtener email del usuario
     * @return string Email en minúsculas
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Obtener tipo de usuario
     * @return string 'normal' o 'admin'
     */
    public function getTipoUsuario() {
        return $this->tipoUsuario;
    }

    /**
     * Obtener fecha de creación
     * @return string|null Fecha en formato MySQL o null
     */
    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    /**
     * Verificar si el usuario está activo
     *
     * CONVENCIÓN: Métodos que devuelven bool usan prefijo "is"
     * isActivo(), isAdmin(), isEmpty(), isValid()...
     *
     * @return bool true si activo, false si desactivado
     */
    public function isActivo() {
        return $this->activo;
        // ====================================================================
        // Devuelve el valor booleano de $activo
        // true  → Usuario puede acceder al sistema
        // false → Usuario deshabilitado (soft delete)
        // ====================================================================
    }

    // ========================================================================
    // SETTERS (con sanitización básica)
    // ========================================================================
    // ¿QUÉ SON SETTERS?
    // - Métodos para MODIFICAR propiedades encapsuladas
    // - Convención: set + NombrePropiedad() → setNombre()
    //
    // VENTAJA CLAVE: Sanitización automática
    // - Al asignar por setter, siempre se limpia el dato
    // - Garantiza consistencia de datos
    // - Centraliza la lógica de limpieza
    //
    // EJEMPLO:
    // Sin setter: $user->nombre = "  Juan  "; ← Espacios guardados
    // Con setter: $user->setNombre("  Juan  "); ← Guarda "Juan" (limpio)
    // ========================================================================

    /**
     * Establecer nombre del usuario
     * @param string $nombre Nombre (se eliminarán espacios extra)
     */
    public function setNombre($nombre) {
        $this->nombre = trim($nombre);
        // ====================================================================
        // trim() - Eliminar espacios al inicio y final
        // ====================================================================
        // SINTAXIS:
        // trim($string);
        //
        // ¿QUÉ HACE?
        // Elimina espacios en blanco (y 	, 
, ) al inicio y final
        //
        // EJEMPLOS:
        // trim("  Juan  ")    → "Juan"
        // trim("Juan")        → "Juan"   (sin cambios si no tiene espacios)
        // trim("
 Juan 	")  → "Juan"   (elimina tabs y newlines también)
        //
        // ¿POR QUÉ SANITIZAR?
        // - Usuarios pueden ingresar espacios accidentalmente
        // - Formularios web a veces generan espacios extra
        // - Consistencia en BD (no guardar "Juan " y "Juan" como diferentes)
        //
        // FUNCIONES RELACIONADAS:
        // trim($s)   → Elimina inicio Y final
        // ltrim($s)  → Solo inicio (left)
        // rtrim($s)  → Solo final (right)
        // ====================================================================
    }

    /**
     * Establecer email del usuario
     * @param string $email Email (se convertirá a minúsculas)
     */
    public function setEmail($email) {
        $this->email = strtolower(trim($email));
        // ====================================================================
        // strtolower(trim($email)) - Doble sanitización
        // ====================================================================
        // Paso 1: trim($email)
        // "  Juan@GMAIL.COM  " → "Juan@GMAIL.COM"
        //
        // Paso 2: strtolower(...)
        // "Juan@GMAIL.COM" → "juan@gmail.com"
        //
        // ¿POR QUÉ LOWERCASE EN EMAIL?
        // - Emails son case-insensitive (Juan@GMAIL.COM = juan@gmail.com)
        // - Consistencia en base de datos
        // - Evita duplicados: "Juan@gmail.com" y "juan@gmail.com" ← MISMO usuario
        // - Facilita búsquedas (comparación simple)
        //
        // strtolower() → string to lower case
        // strtoupper() → string to upper case (lo opuesto)
        //
        // EJEMPLO:
        // Input:  "  MARIA@EMPRESA.CO  "
        // Output: "maria@empresa.co"
        // ====================================================================
    }

    /**
     * Establecer tipo de usuario con validación
     * @param string $tipo Solo acepta 'admin' o 'normal'
     */
    public function setTipoUsuario($tipo) {
        if (in_array($tipo, ['admin', 'normal'])) {
            // ================================================================
            // in_array() - Verificar si valor está en array
            // ================================================================
            // SINTAXIS:
            // in_array($valor, $array);
            //
            // ¿QUÉ HACE?
            // - Busca $valor dentro del $array
            // - Devuelve true si lo encuentra, false si no
            //
            // AQUÍ:
            // in_array($tipo, ['admin', 'normal'])
            // - Verifica si $tipo es exactamente 'admin' o 'normal'
            // - Si es 'super_admin' → false (no permitido)
            // - Si es 'Admin' → false (case-sensitive por defecto)
            //
            // PARÁMETRO OPCIONAL (tercer argumento):
            // in_array($valor, $array, true)  ← Strict mode (tipo exacto)
            // in_array($valor, $array, false) ← Non-strict (conversión de tipos)
            //
            // EJEMPLOS:
            // in_array('admin',  ['admin', 'normal']) → true  ✓
            // in_array('normal', ['admin', 'normal']) → true  ✓
            // in_array('admin',  ['admin', 'normal']) → true  ✓
            // in_array('jefe',   ['admin', 'normal']) → false ✗
            // in_array('ADMIN',  ['admin', 'normal']) → false ✗ (case-sensitive)
            //
            // LISTA BLANCA (Whitelist):
            // Este patrón se llama "whitelist" (lista blanca)
            // Solo valores explícitamente permitidos son aceptados
            // Más seguro que lista negra (blacklist)
            // ================================================================

            $this->tipoUsuario = $tipo;
            // ================================================================
            // Solo se asigna si pasó la validación
            // Si $tipo no es válido, NO se hace nada (silencioso)
            //
            // ALTERNATIVA: Lanzar excepción si es inválido
            // if (!in_array($tipo, ['admin', 'normal'])) {
            //     throw new InvalidArgumentException("Tipo inválido: $tipo");
            // }
            // $this->tipoUsuario = $tipo;
            // ================================================================
        }
        // Si $tipo no es válido, simplemente se ignora (sin error)
    }

    /**
     * Activar o desactivar el usuario
     * @param mixed $activo Se convierte a bool automáticamente
     */
    public function setActivo($activo) {
        $this->activo = (bool)$activo;
        // ====================================================================
        // (bool) - Type Casting a booleano
        // ====================================================================
        // SINTAXIS:
        // (bool)$variable  ← Convierte a boolean
        //
        // ¿QUÉ ES TYPE CASTING?
        // - Forzar conversión de tipo de un valor
        // - PHP es de tipado débil, permite conversiones implícitas
        // - Type casting es conversión EXPLÍCITA
        //
        // CONVERSIONES A BOOL:
        // ┌────────────────────────┬─────────────┐
        // │ Valor original         │ Resultado   │
        // ├────────────────────────┼─────────────┤
        // │ 1, 2, -1 (int != 0)   │ true        │
        // │ 0 (int cero)           │ false       │
        // │ "1", "hola" (strings)  │ true        │
        // │ "" (string vacío)      │ false       │
        // │ "0" (string cero)      │ false       │
        // │ null                   │ false       │
        // │ [] (array vacío)       │ false       │
        // │ [1,2] (array con datos)│ true        │
        // └────────────────────────┴─────────────┘
        //
        // ¿POR QUÉ USAR (bool)?
        // - BD puede devolver 1/0 en lugar de true/false
        // - Formulario puede enviar "1"/"0" como strings
        // - Con (bool) garantizamos tipo correcto
        //
        // EJEMPLO:
        // $activo = "1";  → (bool)"1" → true
        // $activo = 0;    → (bool)0   → false
        // $activo = null; → (bool)null → false
        //
        // OTROS TIPOS DE CASTING:
        // (int)$var    → Convertir a entero
        // (float)$var  → Convertir a decimal
        // (string)$var → Convertir a string
        // (array)$var  → Convertir a array
        // ====================================================================
    }

    // ========================================================================
    // MÉTODOS DE UTILIDAD
    // ========================================================================

    /**
     * Convertir el usuario a array asociativo
     *
     * PROPÓSITO:
     * - Preparar datos para json_encode() (respuestas API)
     * - Pasar datos a templates o vistas
     * - Facilitar serialización
     *
     * @return array Datos del usuario como array
     */
    public function toArray() {
        return [
            // ================================================================
            // ARRAY ASOCIATIVO: ['clave' => valor, ...]
            // ================================================================
            // SINTAXIS PHP:
            // return [
            //     'clave1' => $valor1,
            //     'clave2' => $valor2,
            // ];
            //
            // ¿POR QUÉ toArray()?
            // 1. JSON: json_encode() necesita array, no objeto
            //    json_encode($user->toArray()) → '{"id":1,"nombre":"Juan",...}'
            //
            // 2. PDO: Al insertar/actualizar, se pasan arrays
            //    $stmt->execute($user->toArray())
            //
            // 3. Conversión: Propiedades camelCase → claves snake_case de BD
            //    $tipoUsuario → 'tipo_usuario'
            //    $fechaCreacion → 'fecha_creacion'
            //
            // MAPEO DE NOMBRES:
            // ┌────────────────────┬──────────────────┐
            // │ Propiedad PHP      │ Clave de array   │
            // ├────────────────────┼──────────────────┤
            // │ $this->id          │ 'id'             │
            // │ $this->nombre      │ 'nombre'         │
            // │ $this->email       │ 'email'          │
            // │ $this->tipoUsuario │ 'tipo_usuario'   │
            // │ $this->fechaCreacion│ 'fecha_creacion'│
            // │ $this->activo      │ 'activo'         │
            // └────────────────────┴──────────────────┘
            // ================================================================

            'id'             => $this->id,
            'nombre'         => $this->nombre,
            'email'          => $this->email,
            'tipo_usuario'   => $this->tipoUsuario,
            'fecha_creacion' => $this->fechaCreacion,
            'activo'         => $this->activo
        ];
    }

    /**
     * Verificar si el usuario tiene rol de administrador
     *
     * PATRÓN: Boolean method con prefijo "is"
     *
     * @return bool true si es admin, false si es normal
     */
    public function isAdmin() {
        return $this->tipoUsuario === 'admin';
        // ====================================================================
        // COMPARACIÓN ESTRICTA ===
        // ====================================================================
        // SINTAXIS:
        // $this->tipoUsuario === 'admin'
        //
        // === vs ==:
        // == (doble igual):  Compara valor (con conversión de tipos)
        //    'admin' == 'admin' → true
        //    1 == '1'           → true (convierte tipos)
        //    1 == true          → true (convierte tipos)
        //
        // === (triple igual): Compara valor Y tipo (sin conversión)
        //    'admin' === 'admin' → true  ✓
        //    1 === '1'           → false ✗ (tipos diferentes: int vs string)
        //    1 === true          → false ✗ (tipos diferentes: int vs bool)
        //
        // ¿POR QUÉ === AQUÍ?
        // - tipoUsuario SIEMPRE debe ser string
        // - Evita comparaciones inesperadas
        // - Más seguro y predecible
        //
        // DEVUELVE BOOL DIRECTAMENTE:
        // return $this->tipoUsuario === 'admin';
        // → Si es 'admin': devuelve true
        // → Si es 'normal': devuelve false
        //
        // EQUIVALENTE (más largo, innecesario):
        // if ($this->tipoUsuario === 'admin') {
        //     return true;
        // } else {
        //     return false;
        // }
        // ====================================================================
    }

    /**
     * Obtener las iniciales del nombre del usuario
     *
     * EJEMPLOS:
     * "Juan Pérez"         → "JP"
     * "María José García"  → "MJ" (máximo 2)
     * "Carlos"             → "C"
     *
     * @return string Iniciales (máximo 2 caracteres, mayúsculas)
     */
    public function getIniciales() {
        $palabras = explode(' ', $this->nombre);
        // ====================================================================
        // explode() - Dividir string en array
        // ====================================================================
        // SINTAXIS:
        // explode($separador, $string);
        //
        // ¿QUÉ HACE?
        // - Divide un string en array usando el separador
        // - Es el opuesto de implode() que une array en string
        //
        // EJEMPLOS:
        // explode(' ', 'Juan Pérez')
        // → ['Juan', 'Pérez']
        //
        // explode(' ', 'María José García')
        // → ['María', 'José', 'García']
        //
        // explode(' ', 'Carlos')
        // → ['Carlos']  (array con un elemento)
        //
        // explode(',', 'a,b,c')
        // → ['a', 'b', 'c']  (otro separador)
        //
        // RELACIÓN CON JavaScript:
        // PHP:        explode(' ', $nombre)
        // JavaScript: nombre.split(' ')
        // ====================================================================

        $iniciales = '';
        // ====================================================================
        // Inicializar string vacío para acumular iniciales
        // ====================================================================

        foreach ($palabras as $palabra) {
            // ================================================================
            // foreach - Iterar sobre array
            // ================================================================
            // SINTAXIS:
            // foreach ($array as $elemento) {
            //     // código con $elemento
            // }
            //
            // ¿QUÉ HACE?
            // - Recorre cada elemento del array
            // - Asigna cada elemento a $elemento
            // - Ejecuta el bloque de código
            //
            // EJEMPLO CON NUESTRO CASO:
            // $palabras = ['Juan', 'Pérez']
            //
            // Iteración 1: $palabra = 'Juan'
            // Iteración 2: $palabra = 'Pérez'
            //
            // DIFERENCIA CON for:
            // for ($i = 0; $i < count($array); $i++) {
            //     $elemento = $array[$i]; // Acceso por índice
            // }
            //
            // foreach es más limpio para arrays
            //
            // RELACIÓN CON JavaScript:
            // PHP:        foreach ($palabras as $palabra)
            // JavaScript: palabras.forEach(palabra => ...)
            //             for (const palabra of palabras)
            // ================================================================

            if (!empty($palabra)) {
                // ============================================================
                // empty() - Verificar si variable está vacía
                // ============================================================
                // SINTAXIS:
                // empty($variable)
                //
                // ¿QUÉ HACE?
                // - Devuelve true si la variable está "vacía"
                // - Valores vacíos: '', 0, '0', null, false, [], 0.0
                //
                // !empty($palabra):
                // - Negación: true si palabra NO está vacía
                // - Evita procesar strings vacíos
                //
                // ¿POR QUÉ VERIFICAR?
                // Si nombre = "Juan  Pérez" (doble espacio):
                // explode(' ', ...) → ['Juan', '', 'Pérez']
                // '' (vacío) generaría problema al hacer $palabra[0]
                // ============================================================

                $iniciales .= strtoupper($palabra[0]);
                // ============================================================
                // OBTENER PRIMERA LETRA Y ACUMULAR
                // ============================================================
                // $palabra[0]
                // - Acceso a carácter por índice (como array)
                // - [0] = primer carácter
                // - 'Juan'[0] = 'J'
                // - 'Pérez'[0] = 'P'
                //
                // strtoupper($palabra[0])
                // - Convierte a MAYÚSCULA
                // - 'j' → 'J'
                // - 'p' → 'P'
                //
                // .= (operador de concatenación asignación)
                // - Equivalente a: $iniciales = $iniciales . $caracter
                // - Agrega al final del string existente
                //
                // EJEMPLO COMPLETO:
                // Inicio:      $iniciales = ''
                // Iteración 1: $iniciales .= 'J' → $iniciales = 'J'
                // Iteración 2: $iniciales .= 'P' → $iniciales = 'JP'
                //
                // RELACIÓN CON JavaScript:
                // PHP:        $iniciales .= strtoupper($palabra[0])
                // JavaScript: iniciales += palabra[0].toUpperCase()
                // ============================================================
            }
        }

        return substr($iniciales, 0, 2);
        // ====================================================================
        // substr() - Extraer parte de un string
        // ====================================================================
        // SINTAXIS:
        // substr($string, $inicio, $longitud);
        //
        // ¿QUÉ HACE?
        // - Devuelve parte del string
        //
        // PARÁMETROS:
        // $string   → String original
        // $inicio   → Posición de inicio (0 = primer carácter)
        // $longitud → Cuántos caracteres tomar
        //
        // AQUÍ: substr($iniciales, 0, 2)
        // - Desde posición 0
        // - Tomar máximo 2 caracteres
        //
        // EJEMPLOS:
        // substr('JP', 0, 2)   → 'JP'   (solo 2 → sin cambios)
        // substr('MJG', 0, 2)  → 'MJ'   (corta a 2)
        // substr('C', 0, 2)    → 'C'    (solo 1 disponible)
        //
        // ¿POR QUÉ MÁXIMO 2?
        // - Avatar/iniciales típicamente muestran 2 letras
        // - "JuanCarlos García" → "JG" (no "JCG")
        //
        // RELACIÓN CON JavaScript:
        // PHP:        substr($iniciales, 0, 2)
        // JavaScript: iniciales.slice(0, 2)
        // ====================================================================
    }
}
// Fin de clase User


// ============================================================================
// CLASE EXTENDIDA: AdminUser
// ============================================================================
// PROPÓSITO:
//   Usuario con permisos especiales de administración
//   Extiende User agregando sistema de permisos granular
//
// PATRONES Y PRINCIPIOS:
//   • Herencia (OCP - Open/Closed Principle):
//     User está CERRADO a modificación, pero ABIERTO a extensión
//     Extendemos User en lugar de modificarlo
//
//   • Liskov Substitution Principle:
//     Donde se espera User, puede usarse AdminUser
//     AdminUser no rompe el contrato de User
//
// EJEMPLO DE LISKOV:
//   function mostrarUsuario(User $u) {
//       echo $u->getNombre(); // Funciona con User Y AdminUser
//   }
//   mostrarUsuario(new User([...]));      ← OK
//   mostrarUsuario(new AdminUser([...])); ← También OK (LSP)
// ============================================================================

/**
 * AdminUser - Usuario con capacidades de administración
 *
 * HEREDA DE: User (todos sus métodos y propiedades)
 * AGREGA:
 *   - Propiedad $permisos (array de permisos)
 *   - getPermisos(), tienePermiso(), agregarPermiso(), quitarPermiso()
 *   - Override de toArray() para incluir permisos
 */
class AdminUser extends User {
    // ========================================================================
    // extends - Herencia de clases en PHP
    // ========================================================================
    // SINTAXIS:
    // class ClaseHija extends ClasePadre {...}
    //
    // ¿QUÉ SIGNIFICA?
    // - AdminUser HEREDA todo lo de User
    // - Como si copiara todo el código de User
    // - Puede usar, sobrescribir o extender métodos/propiedades
    //
    // HEREDA AUTOMÁTICAMENTE:
    // ✓ $id, $nombre, $email, $tipoUsuario, $fechaCreacion, $activo
    // ✓ getId(), getNombre(), getEmail(), getTipoUsuario()
    // ✓ setNombre(), setEmail(), setTipoUsuario(), setActivo()
    // ✓ isAdmin(), isActivo(), getIniciales()
    //
    // AGREGA (exclusivo de AdminUser):
    // + $permisos
    // + getPermisos(), tienePermiso(), agregarPermiso(), quitarPermiso()
    //
    // SOBRESCRIBE (override):
    // ± toArray() → Agrega permisos al array base
    //
    // ANALOGÍA:
    // User es como un empleado básico.
    // AdminUser hereda todo lo del empleado,
    // pero además tiene tarjeta de acceso especial ($permisos).
    // ========================================================================

    private $permisos;
    // ========================================================================
    // PROPIEDAD $permisos - Lista de permisos del administrador
    // ========================================================================
    // TIPO: array de strings
    // VISIBILIDAD: private (solo AdminUser la usa, User no necesita verla)
    //
    // ¿POR QUÉ private Y NO protected?
    // - $permisos es exclusivo de AdminUser
    // - Si hubiera SuperAdmin extendiendo AdminUser, quizás protected
    // - Por ahora, private es suficiente
    //
    // VALOR: Array de strings con nombres de permisos
    // EJEMPLO:
    // ['crear_usuarios', 'editar_usuarios', 'eliminar_usuarios', ...]
    // ========================================================================

    /**
     * Constructor de AdminUser
     *
     * LLAMA AL CONSTRUCTOR PADRE (parent::__construct)
     * LUEGO fuerza tipo 'admin' y configura permisos
     *
     * @param array $data Datos del usuario
     */
    public function __construct($data = []) {
        parent::__construct($data);
        // ====================================================================
        // parent::__construct() - Llamar constructor de la clase padre
        // ====================================================================
        // SINTAXIS:
        // parent::__construct($argumentos);
        //
        // ¿QUÉ HACE?
        // - Ejecuta el constructor de User (clase padre)
        // - Inicializa: $id, $nombre, $email, $tipoUsuario, $fechaCreacion, $activo
        //
        // ¿POR QUÉ ES NECESARIO?
        // - Si no se llama, las propiedades heredadas NO se inicializan
        // - $this->nombre, $this->email, etc. quedarían sin valor
        //
        // ANALOGÍA:
        // Es como llamar a "mamá" para que haga lo básico,
        // y luego tú agregas lo que necesitas.
        //
        // FLUJO:
        // new AdminUser(['nombre' => 'Juan', ...])
        //   → AdminUser::__construct(['nombre' => 'Juan'])
        //     → parent::__construct(['nombre' => 'Juan'])   ← User::__construct
        //       → $this->nombre = 'Juan' (y las demás props)
        //     ← Vuelve a AdminUser
        //   → $this->tipoUsuario = 'admin'  (forzar tipo)
        //   → $this->permisos = [...]        (inicializar permisos)
        //
        // parent:: (doble dos puntos = scope resolution operator)
        // - Accede a métodos/propiedades de la clase padre
        // - parent::__construct() → método del padre
        // - parent::toArray()     → método toArray del padre
        // ====================================================================

        $this->tipoUsuario = 'admin';
        // ====================================================================
        // FORZAR TIPO ADMIN
        // ====================================================================
        // El padre asignó $tipoUsuario = $data['tipo_usuario'] ?? 'normal'
        // Ahora lo forzamos a 'admin' siempre
        //
        // ¿POR QUÉ?
        // - Un AdminUser SIEMPRE es admin
        // - No debe depender de que $data tenga 'tipo_usuario' = 'admin'
        // - Garantía de consistencia
        //
        // ACCESO A PROPIEDAD HEREDADA:
        // $this->tipoUsuario es protected en User
        // AdminUser puede acceder porque hereda de User
        // Si fuera private, AdminUser NO podría acceder
        // ====================================================================

        $this->permisos = [
            // ================================================================
            // PERMISOS POR DEFECTO DEL ADMINISTRADOR
            // ================================================================
            // Array de strings con nombres de permisos
            // Estos son los permisos iniciales al crear admin
            // Se pueden agregar/quitar con agregarPermiso()/quitarPermiso()
            // ================================================================
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',
            'ver_reportes',
            'gestionar_roles'
        ];
    }

    /**
     * Obtener todos los permisos del administrador
     * @return array Lista de permisos
     */
    public function getPermisos() {
        return $this->permisos;
    }

    /**
     * Verificar si el admin tiene un permiso específico
     *
     * USO:
     * if ($admin->tienePermiso('eliminar_usuarios')) {
     *     // Mostrar botón eliminar
     * }
     *
     * @param string $permiso Nombre del permiso a verificar
     * @return bool true si tiene el permiso
     */
    public function tienePermiso($permiso) {
        return in_array($permiso, $this->permisos);
        // ====================================================================
        // Usa in_array() para buscar $permiso dentro del array $this->permisos
        //
        // EJEMPLOS:
        // tienePermiso('crear_usuarios')  → true  (está en el array)
        // tienePermiso('ver_reportes')    → true  (está en el array)
        // tienePermiso('ver_finanzas')    → false (NO está en el array)
        //
        // APLICACIÓN:
        // Este método permite autorización granular
        // (cada permiso es independiente)
        // ====================================================================
    }

    /**
     * Agregar un nuevo permiso al admin
     *
     * Solo agrega si el permiso no existe ya (evita duplicados)
     *
     * @param string $permiso Permiso a agregar
     */
    public function agregarPermiso($permiso) {
        if (!in_array($permiso, $this->permisos)) {
            // ================================================================
            // VERIFICAR QUE NO EXISTA ANTES DE AGREGAR
            // ================================================================
            // !in_array() → Si permiso NO está en el array
            // Evita duplicados: ['crear', 'crear'] ← No queremos esto
            // ================================================================

            $this->permisos[] = $permiso;
            // ================================================================
            // AGREGAR AL FINAL DEL ARRAY
            // ================================================================
            // SINTAXIS:
            // $array[] = $nuevoElemento;
            //
            // ¿QUÉ HACE?
            // - Agrega elemento al final del array
            // - Equivalente a array_push($this->permisos, $permiso)
            //
            // EJEMPLO:
            // $this->permisos = ['crear', 'editar']
            // $this->permisos[] = 'eliminar'
            // $this->permisos → ['crear', 'editar', 'eliminar']
            //
            // DIFERENCIA CON JAVASCRIPT:
            // PHP:        $array[] = $valor
            // JavaScript: array.push(valor)
            // ================================================================
        }
    }

    /**
     * Quitar un permiso del admin
     *
     * @param string $permiso Permiso a eliminar
     */
    public function quitarPermiso($permiso) {
        $this->permisos = array_diff($this->permisos, [$permiso]);
        // ====================================================================
        // array_diff() - Diferencia entre arrays
        // ====================================================================
        // SINTAXIS:
        // array_diff($array1, $array2);
        //
        // ¿QUÉ HACE?
        // - Devuelve elementos de $array1 que NO están en $array2
        // - Es decir: $array1 MENOS los elementos de $array2
        //
        // AQUÍ:
        // array_diff($this->permisos, [$permiso])
        // - $this->permisos: Array completo de permisos
        // - [$permiso]: Array con el permiso a eliminar (en corchetes)
        //
        // EJEMPLO:
        // $this->permisos = ['crear', 'editar', 'eliminar']
        // $permiso = 'editar'
        //
        // array_diff(['crear', 'editar', 'eliminar'], ['editar'])
        // → ['crear', 'eliminar']  (sin 'editar')
        //
        // ¿POR QUÉ [$permiso] EN LUGAR DE $permiso?
        // - array_diff espera arrays como argumentos
        // - $permiso es string
        // - [$permiso] convierte string a array de un elemento
        //
        // IMPORTANTE: array_diff PRESERVA LAS CLAVES
        // ['crear' => 0, 'editar' => 1, 'eliminar' => 2]
        // Después de array_diff: ['crear' => 0, 'eliminar' => 2]
        // Los índices no se reordenan automáticamente
        // ====================================================================
    }

    /**
     * Override de toArray() para incluir permisos
     *
     * SOBRESCRITURA (Override):
     * AdminUser tiene su propia versión de toArray()
     * que agrega $permisos a los datos base de User
     *
     * @return array Datos del usuario + permisos
     */
    public function toArray() {
        $data = parent::toArray();
        // ====================================================================
        // parent::toArray() - Llamar método del padre
        // ====================================================================
        // ¿QUÉ HACE?
        // - Ejecuta toArray() de la clase User (padre)
        // - Devuelve array con: id, nombre, email, tipo_usuario, etc.
        //
        // IMPORTANTE:
        // - No duplicamos código del padre
        // - Reutilizamos su implementación
        // - Solo agregamos lo que necesitamos (permisos)
        //
        // RESULTADO:
        // $data = [
        //     'id' => 1,
        //     'nombre' => 'Juan',
        //     'email' => 'juan@email.com',
        //     'tipo_usuario' => 'admin',
        //     'fecha_creacion' => '2024-01-15 10:30:00',
        //     'activo' => true
        // ]
        // ====================================================================

        $data['permisos'] = $this->permisos;
        // ====================================================================
        // AGREGAR PERMISOS AL ARRAY
        // ====================================================================
        // - $data ya tiene los campos base de User
        // - Agregamos nueva clave 'permisos'
        //
        // RESULTADO FINAL:
        // $data = [
        //     'id' => 1,
        //     'nombre' => 'Juan',
        //     ...
        //     'permisos' => ['crear_usuarios', 'editar_usuarios', ...]
        // ]
        // ====================================================================

        return $data;
    }
}
// Fin de clase AdminUser


// ============================================================================
// PATRÓN: FACTORY METHOD
// Clase: UserFactory
// ============================================================================
//
// DEFINICIÓN DEL PATRÓN:
// Factory Method define una interfaz para crear objetos,
// pero deja a las subclases decidir qué clase instanciar.
//
// EN TÉRMINOS SIMPLES:
// En lugar de hacer: new User() o new AdminUser() directamente,
// se usa:            UserFactory::create($data)
// La fábrica decide cuál crear según los datos.
//
// PROBLEMA QUE RESUELVE:
// Sin Factory:
//   $tipo = $data['tipo_usuario'];
//   if ($tipo === 'admin') {
//       $user = new AdminUser($data);
//   } else {
//       $user = new User($data);
//   }
//   // Este código se repite en MUCHOS lugares
//
// Con Factory:
//   $user = UserFactory::create($data); // Una sola línea, siempre
//   // La lógica de decisión está centralizada en la fábrica
//
// VENTAJAS:
// ✓ Centralización: lógica de creación en un solo lugar
// ✓ Mantenimiento: si agrego un nuevo tipo, solo cambio la fábrica
// ✓ Principio OCP: agregar GuestUser sin modificar código cliente
// ✓ Principio DIP: cliente depende de la fábrica, no de clases concretas
//
// ANALOGÍA:
// Como una fábrica de vehículos:
// - No construyes tu propio carro
// - Pides a la fábrica que te dé lo que necesitas
// - La fábrica decide qué modelo armar según tus especificaciones
// ============================================================================

/**
 * UserFactory - Fábrica de Usuarios
 *
 * PATRÓN: Factory Method
 *
 * USO:
 *   // En lugar de new User() o new AdminUser() directamente:
 *   $user = UserFactory::create(['tipo_usuario' => 'admin', ...]);
 *   // → Devuelve AdminUser automáticamente
 *
 *   $user = UserFactory::create(['tipo_usuario' => 'normal', ...]);
 *   // → Devuelve User automáticamente
 */
class UserFactory {
    // ========================================================================
    // CLASE DE UTILIDAD ESTÁTICA
    // ========================================================================
    // UserFactory no tiene propiedades de instancia
    // Todos sus métodos son static
    // Nunca se crea instancia: new UserFactory() ← No se necesita
    // Se usa directamente: UserFactory::create($data)
    //
    // ¿POR QUÉ NO EXTENDER NADA?
    // UserFactory no es un User, es una herramienta para crearlos
    // No necesita herencia
    // ========================================================================

    /**
     * Crear un usuario según su tipo
     *
     * DECISIÓN AUTOMÁTICA:
     * - tipo_usuario = 'admin' → devuelve AdminUser
     * - tipo_usuario = 'normal' (o cualquier otro) → devuelve User
     *
     * @param  array        $data Datos del usuario (de BD o formulario)
     * @return User|AdminUser     Instancia del tipo apropiado
     */
    public static function create($data) {
        // ====================================================================
        // MÉTODO ESTÁTICO
        // ====================================================================
        // SINTAXIS:
        // public static function create($data) {...}
        //
        // ¿QUÉ ES static?
        // - El método pertenece a la CLASE, no a instancias
        // - No necesita crear objeto para usar el método
        //
        // USO NORMAL (instancia):
        // $factory = new UserFactory();
        // $user = $factory->create($data); ← Con ->
        //
        // USO ESTÁTICO (sin instancia):
        // $user = UserFactory::create($data); ← Con :: (scope resolution)
        //
        // ¿CUÁNDO USAR static?
        // - Métodos de utilidad que no necesitan estado
        // - Fábricas y helpers
        // - Cuando no usas $this (no hay objeto)
        //
        // DENTRO DE UN MÉTODO ESTÁTICO:
        // - NO puedes usar $this (no hay objeto)
        // - SÍ puedes usar self:: (referencia a la clase)
        //
        // DIFERENCIA :: vs ->
        // ::  Scope resolution (clase o estático)
        //     UserFactory::create()  ← Método estático
        //     parent::__construct()  ← Método del padre
        //
        // ->  Object operator (instancia)
        //     $user->getNombre()     ← Método de instancia
        // ====================================================================

        $tipo = $data['tipo_usuario'] ?? 'normal';
        // ====================================================================
        // Obtener tipo con valor por defecto 'normal'
        // Si $data no tiene 'tipo_usuario' → asume 'normal'
        // ====================================================================

        switch ($tipo) {
            // ================================================================
            // switch - Estructura de control multi-caso
            // ================================================================
            // SINTAXIS:
            // switch ($variable) {
            //     case 'valor1':
            //         // código
            //         break;
            //     case 'valor2':
            //         // código
            //         break;
            //     default:
            //         // código si ningún caso coincide
            // }
            //
            // ¿QUÉ HACE?
            // - Evalúa $variable
            // - Compara con cada case
            // - Ejecuta el código del case que coincide
            //
            // ¿POR QUÉ switch Y NO if/else?
            // if/else:
            //   if ($tipo === 'admin') { ... }
            //   elseif ($tipo === 'normal') { ... }
            //   elseif ($tipo === 'guest') { ... }   ← Muchos elseif
            //
            // switch: Más limpio cuando hay muchos casos
            //   switch ($tipo) {
            //       case 'admin': ...
            //       case 'normal': ...
            //       case 'guest': ...
            //   }
            //
            // IMPORTANTE: break;
            // - Sin break, el código continúa al siguiente case (fall-through)
            // - Con return no necesitas break (return ya sale del método)
            // ================================================================

            case 'admin':
                return new AdminUser($data);
                // ============================================================
                // Si tipo = 'admin' → Crear AdminUser
                // - new AdminUser($data) llama a AdminUser::__construct($data)
                // - return devuelve el objeto creado
                // - Termina el método (no necesita break)
                // ============================================================

            case 'normal':
            default:
                return new User($data);
                // ============================================================
                // case 'normal': → Si tipo = 'normal'
                // default:       → Si tipo es cualquier otro valor
                //
                // ¿POR QUÉ case 'normal' Y default JUNTOS?
                // - case 'normal': cae a default (sin break entre ellos)
                // - Esto es "fall-through" intencional
                // - Ambos ejecutan: return new User($data)
                //
                // SEGURIDAD:
                // Si alguien envía tipo_usuario = 'superadmin' (no válido)
                // → default: → crea User normal (el más restrictivo)
                // ============================================================
        }
    }

    /**
     * Crear múltiples usuarios desde un array de datos
     *
     * PROPÓSITO:
     * Procesar en lote los resultados de una consulta SQL
     *
     * USO:
     *   $rows = $pdo->query("SELECT * FROM usuarios")->fetchAll();
     *   $users = UserFactory::createMultiple($rows);
     *   // $users = [User, AdminUser, User, User, AdminUser, ...]
     *
     * @param  array $usuarios Array de arrays de datos
     * @return array Array de objetos User/AdminUser
     */
    public static function createMultiple($usuarios) {
        $resultado = [];
        // ====================================================================
        // Inicializar array vacío para acumular objetos
        // ====================================================================

        foreach ($usuarios as $userData) {
            // ================================================================
            // Iterar sobre cada fila de datos
            // $userData = ['id' => 1, 'nombre' => 'Juan', ...]
            // ================================================================

            $resultado[] = self::create($userData);
            // ================================================================
            // self::create() - Llamar método estático de la misma clase
            // ================================================================
            // SINTAXIS:
            // self::metodo()
            //
            // ¿QUÉ ES self::?
            // - Referencia a la clase actual (UserFactory)
            // - Similar a $this pero para métodos/propiedades estáticos
            //
            // DIFERENCIA:
            // $this → Referencia a la instancia actual (para no-estáticos)
            // self:: → Referencia a la clase actual (para estáticos)
            //
            // PODRÍA SER:
            // UserFactory::create($userData) ← Explícito (nombre de clase)
            // self::create($userData)        ← Genérico (usa clase actual)
            //
            // ¿POR QUÉ self:: ES MEJOR?
            // - Si renombramos UserFactory, self:: sigue funcionando
            // - UserFactory:: habría que actualizar
            //
            // RESULTADO:
            // Cada $userData se convierte en User o AdminUser
            // Se agrega al array $resultado
            // ================================================================
        }

        return $resultado;
        // ====================================================================
        // Devolver array de objetos
        //
        // EJEMPLO DE RESULTADO:
        // Input:  [['tipo_usuario'=>'normal'], ['tipo_usuario'=>'admin']]
        // Output: [User, AdminUser]
        // ====================================================================
    }
}
// Fin de clase UserFactory

// ============================================================================
// RESUMEN DE CONCEPTOS EXPLICADOS:
// ============================================================================
//
// PHP ORIENTADO A OBJETOS:
// - Clases (class, propiedades, métodos)
// - Visibilidad (public, protected, private)
// - Constructor (__construct)
// - $this (referencia al objeto)
// - Getters y Setters (encapsulación)
// - Herencia (extends)
// - parent:: (acceder al padre)
// - Sobrescritura de métodos (override)
// - Métodos estáticos (static, self::)
// - Scope resolution operator (::)
//
// OPERADORES Y TIPOS:
// - Null Coalescing (??)
// - Type Casting ((bool))
// - Concatenación (.=)
// - Comparación estricta (===)
// - Acceso a caracteres ($string[0])
//
// FUNCIONES PHP:
// - trim() / ltrim() / rtrim()
// - strtolower() / strtoupper()
// - explode() / implode()
// - in_array()
// - substr()
// - empty()
// - array_diff()
//
// CONTROL DE FLUJO:
// - if / else
// - foreach
// - switch / case / default / break
//
// PATRONES DE DISEÑO:
// - Factory Method (UserFactory)
// - Value Object (User)
// - Template Method (toArray override)
//
// PRINCIPIOS SOLID:
// - S: User solo gestiona datos de usuario
// - O: User abierto a extensión (AdminUser) sin modificación
// - L: AdminUser reemplaza User sin romper comportamiento
//
// ============================================================================
