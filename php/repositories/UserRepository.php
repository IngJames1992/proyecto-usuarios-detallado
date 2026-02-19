<?php
// ============================================================================
// ARCHIVO: UserRepository.php
// UBICACIÓN: php/repositories/UserRepository.php
// PROPÓSITO: Capa de acceso a datos para la tabla de usuarios
// ============================================================================
//
// DESCRIPCIÓN GENERAL:
// Este archivo implementa el PATRÓN REPOSITORY para la entidad User.
// Actúa como intermediario entre la lógica de negocio y la base de datos,
// proporcionando métodos CRUD completos más consultas especializadas.
//
// RESPONSABILIDAD ÚNICA (Single Responsibility Principle):
// UserRepository SOLO se encarga de:
//   ✓ Consultar usuarios de la BD (SELECT)
//   ✓ Insertar usuarios en la BD (INSERT)
//   ✓ Actualizar usuarios en la BD (UPDATE)
//   ✓ Eliminar usuarios de la BD (DELETE / soft delete)
//
// NO se encarga de:
//   ✗ Validar datos (eso es UserValidator)
//   ✗ Mostrar datos al usuario (eso es la vista)
//   ✗ Lógica de negocio (eso es el controlador)
//
// TECNOLOGÍAS Y CONCEPTOS:
// ============================================================================
//   • PHP OOP: Clases, propiedades, métodos, $this
//   • PDO: Prepared Statements, placeholders (?), fetch, fetchAll
//   • Patrón Repository: Abstracción de la capa de datos
//   • Patrón Singleton: Database::getInstance()
//   • Type Hinting: User $user en parámetros
//   • SQL Dinámico: WHERE 1=1, concatenación .=
//   • SQL: SELECT, INSERT, UPDATE, DELETE, COUNT, SUM, CASE WHEN
//   • Paginación: LIMIT y OFFSET
//   • Búsqueda: LIKE con wildcards %texto%
//   • Soft Delete: activo = 0 vs DELETE físico
//   • Operador ternario: condición ? verdadero : falso
//   • isset() para verificar claves de array
//   • rowCount() para filas afectadas
//   • lastInsertId() para ID recién insertado
//
// PATRÓN REPOSITORY - CONCEPTO:
// ============================================================================
//
//   PROBLEMA SIN REPOSITORY:
//   ┌─────────────────────────────────────────────┐
//   │ get_users.php                               │
//   │   $pdo = new PDO(...);                      │
//   │   $sql = "SELECT * FROM usuarios";          │
//   │   $stmt = $pdo->query($sql);               │
//   │   // SQL mezclado con lógica de negocio     │
//   └─────────────────────────────────────────────┘
//   ┌─────────────────────────────────────────────┐
//   │ create_user.php                             │
//   │   $pdo = new PDO(...); // DUPLICADO         │
//   │   $sql = "INSERT INTO ..."; // DUPLICADO    │
//   └─────────────────────────────────────────────┘
//
//   SOLUCIÓN CON REPOSITORY:
//   ┌──────────────────────┐     ┌───────────────────────┐
//   │ get_users.php        │     │ create_user.php       │
//   │                      │     │                       │
//   │ $repo = new          │     │ $repo = new           │
//   │  UserRepository();   │     │  UserRepository();    │
//   │ $repo->findAll()     │     │ $repo->create($user)  │
//   └──────────┬───────────┘     └───────────┬───────────┘
//              │                             │
//              └──────────┬──────────────────┘
//                         ↓
//              ┌──────────────────────┐
//              │   UserRepository     │ ← Todo el SQL aquí
//              │  findAll()           │
//              │  findById()          │
//              │  create()            │
//              │  update()            │
//              │  delete()            │
//              └──────────┬───────────┘
//                         ↓
//              ┌──────────────────────┐
//              │   Base de Datos      │
//              │   tabla: usuarios    │
//              └──────────────────────┘
//
// VENTAJAS DEL PATRÓN REPOSITORY:
//   ✓ Centralización: todo el SQL en un solo lugar
//   ✓ Reutilización: múltiples endpoints usan el mismo repo
//   ✓ Mantenimiento: cambiar SQL sin tocar lógica de negocio
//   ✓ Testing: fácil de mockear/simular en pruebas
//   ✓ Abstracción: el resto del código no sabe de SQL
//   ✓ Single Responsibility: cada clase tiene una sola razón de cambio
//
// DEPENDENCIAS:
// ============================================================================
//   • Database.php  → Conexión PDO (Singleton)
//   • User.php      → Modelo User y AdminUser
//   • UserFactory   → Crear objetos User/AdminUser desde arrays
//
// MÉTODOS CRUD:
// ============================================================================
//   CREATE  → create(User $user) : int
//   READ    → findById($id) : User|null
//             findByEmail($email) : User|null
//             findAll($filtros) : array
//   UPDATE  → update(User $user) : bool
//   DELETE  → delete($id) : bool         ← Soft delete
//             forceDelete($id) : bool    ← Hard delete
//
// MÉTODOS ESPECIALES:
// ============================================================================
//   emailExists($email, $excludeId) : bool
//   count($filtros) : int
//   getStatistics() : array
//
// ============================================================================

/**
 * ============================================================================
 * CLASE: UserRepository
 * ============================================================================
 * PROPÓSITO:
 *   Proporcionar acceso a datos de usuarios mediante PDO.
 *   Abstrae todas las consultas SQL relacionadas con la tabla 'usuarios'.
 *
 * PATRÓN: Repository
 *
 * PRINCIPIOS SOLID APLICADOS:
 *   S - Single Responsibility: Solo gestiona acceso a datos de usuarios
 *   D - Dependency Inversion: Depende de Database (abstracción), no PDO directo
 *
 * USO TÍPICO:
 *   $repo = new UserRepository();
 *
 *   // Obtener todos
 *   $users = $repo->findAll();
 *
 *   // Buscar por ID
 *   $user = $repo->findById(5);
 *
 *   // Crear
 *   $user = new User(['nombre' => 'Juan', 'email' => 'j@e.com']);
 *   $id = $repo->create($user);
 */
class UserRepository {

    // ========================================================================
    // PROPIEDADES DE LA CLASE
    // ========================================================================

    private $db;
    // ========================================================================
    // PROPIEDAD $db - Instancia de la conexión a base de datos
    // ========================================================================
    // TIPO: Database (objeto Singleton)
    // VISIBILIDAD: private (solo esta clase usa la conexión directamente)
    //
    // ¿QUÉ ES $db?
    // - Referencia a la instancia única de Database (Singleton)
    // - Database envuelve a PDO (PHP Data Objects)
    // - Se inicializa en el constructor
    //
    // ¿POR QUÉ private?
    // - Nadie externo debería acceder a la BD directamente a través del repo
    // - Encapsulación: el acceso a BD es un detalle interno
    //
    // ANALOGÍA:
    // $db es como el teléfono directo al almacén.
    // Solo el repositorio lo usa; el cliente solo pide productos.
    // ========================================================================

    private $table = 'usuarios';
    // ========================================================================
    // PROPIEDAD $table - Nombre de la tabla en la base de datos
    // ========================================================================
    // TIPO: string
    // VALOR: 'usuarios' (tabla en MySQL)
    // VISIBILIDAD: private
    //
    // ¿POR QUÉ UNA CONSTANTE/PROPIEDAD?
    // - Evitar repetir 'usuarios' hardcodeado en cada método
    // - Si la tabla cambia de nombre → solo cambiar aquí
    // - Evita errores de escritura (typos)
    //
    // USO EN SQL:
    // "SELECT * FROM {$this->table}"
    //   → "SELECT * FROM usuarios"
    //
    // ALTERNATIVA: Usar constante de clase
    // const TABLE = 'usuarios';
    // "SELECT * FROM " . self::TABLE
    //
    // ¿POR QUÉ PROPIEDAD Y NO CONSTANTE AQUÍ?
    // - Permite sobreescribir en subclases (AdminUserRepository extends UserRepository)
    // - Más flexible para herencia futura
    // ========================================================================

    // ========================================================================
    // CONSTRUCTOR
    // ========================================================================

    /**
     * Constructor: Obtener conexión a la base de datos
     *
     * Usa el Singleton de Database para obtener la única instancia
     * de la conexión PDO que existe en toda la aplicación.
     */
    public function __construct() {
        $this->db = Database::getInstance();
        // ====================================================================
        // Database::getInstance() - Obtener Singleton de la BD
        // ====================================================================
        // SINTAXIS:
        // Database::getInstance()
        // └─ Database: Clase del Singleton (definida en Database.php)
        // └─ ::        Scope resolution operator (acceso estático)
        // └─ getInstance(): Método estático que devuelve la instancia única
        //
        // ¿QUÉ HACE?
        // - Si ya existe conexión PDO → devuelve la misma
        // - Si no existe → crea una nueva y la guarda
        // - Siempre devuelve UNA SOLA instancia (Singleton)
        //
        // ¿POR QUÉ SINGLETON?
        // - Una sola conexión por petición HTTP
        // - Eficiencia: no crear múltiples conexiones a BD
        // - Si UserRepository y otro objeto usan BD → misma conexión
        //
        // INJECTION ALTERNATIVE (Dependency Injection):
        // public function __construct(Database $db) {
        //     $this->db = $db; // Inyectado desde afuera
        // }
        // Más flexible para testing, pero requiere más configuración
        //
        // FLUJO:
        // new UserRepository()
        //   → Database::getInstance()
        //     → ¿Existe instancia? Sí → devuelve la existente
        //     → ¿Existe instancia? No → new PDO(...) → guarda → devuelve
        //   → $this->db = [instancia Database]
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: create
    // OPERACIÓN SQL: INSERT INTO
    // ========================================================================

    /**
     * Crear un nuevo usuario en la base de datos
     *
     * @param  User $user  Objeto User con los datos a insertar
     * @return int         ID asignado por la BD al nuevo registro
     *
     * USO:
     *   $user = new User(['nombre' => 'Juan', 'email' => 'j@e.com']);
     *   $nuevoId = $repo->create($user);
     *   echo $nuevoId; // 42 (ID asignado por la BD)
     */
    public function create(User $user) {
        // ====================================================================
        // TYPE HINTING EN PARÁMETROS
        // ====================================================================
        // SINTAXIS:
        // public function create(User $user)
        //                       ^^^^
        //                       Type hint: fuerza que $user sea de tipo User
        //
        // ¿QUÉ ES TYPE HINTING?
        // - Declarar qué tipo de dato se espera en el parámetro
        // - PHP verifica el tipo antes de ejecutar el método
        //
        // SIN TYPE HINT:
        // public function create($user) {
        //     $user->getNombre(); // ¿Y si $user no tiene getNombre()? ERROR
        // }
        //
        // CON TYPE HINT:
        // public function create(User $user) {
        //     $user->getNombre(); // Garantizado: $user ES User o AdminUser
        // }
        //
        // ¿ACEPTA AdminUser?
        // SÍ. Por el principio de Liskov Substitution:
        // AdminUser extends User → AdminUser ES un User
        // create(new AdminUser([...])) ← Funciona correctamente
        //
        // ERROR SI SE PASA MAL TIPO:
        // create("Juan") ← TypeError: Argument must be of type User, string given
        // ====================================================================

        $sql = "INSERT INTO {$this->table} (nombre, email, tipo_usuario, activo)
                VALUES (?, ?, ?, ?)";
        // ====================================================================
        // PREPARED STATEMENT con PLACEHOLDERS (?)
        // ====================================================================
        // SINTAXIS SQL:
        // INSERT INTO tabla (col1, col2, ...)
        // VALUES (?, ?, ...)
        //
        // ¿QUÉ SON LOS ? (PLACEHOLDERS)?
        // - Marcadores de posición para valores reales
        // - PDO los reemplaza de forma SEGURA al ejecutar
        // - Previenen INYECCIÓN SQL (SQL Injection)
        //
        // ¿POR QUÉ NO CONCATENAR DIRECTAMENTE?
        // INSEGURO (vulnerable a SQL Injection):
        //   $sql = "INSERT INTO usuarios (nombre) VALUES ('" . $nombre . "')";
        //   Si $nombre = "Juan'; DROP TABLE usuarios; --"
        //   → Destruye la tabla (SQL Injection)
        //
        // SEGURO (con placeholders):
        //   $sql = "INSERT INTO usuarios (nombre) VALUES (?)";
        //   $params = [$nombre]; // PDO lo escapa automáticamente
        //   → Trata el valor como dato, no como código SQL
        //
        // STRING INTERPOLATION: {$this->table}
        // - En strings con comillas dobles "", las variables se interpolan
        // - {$this->table} → 'usuarios' en tiempo de ejecución
        // - Resultado: "INSERT INTO usuarios (nombre, email, tipo_usuario, activo)..."
        //
        // COLUMNAS NO INCLUIDAS:
        // - id: AUTO_INCREMENT (BD lo genera)
        // - fecha_creacion: DEFAULT CURRENT_TIMESTAMP (BD lo genera)
        // ====================================================================

        $params = [
            $user->getNombre(),
            $user->getEmail(),
            $user->getTipoUsuario(),
            $user->isActivo() ? 1 : 0
            // ================================================================
            // OPERADOR TERNARIO para convertir bool a int
            // ================================================================
            // SINTAXIS:
            // condición ? valor_si_verdadero : valor_si_falso
            //
            // $user->isActivo() ? 1 : 0
            // - isActivo() devuelve bool (true/false)
            // - MySQL no tiene tipo boolean nativo en versiones antiguas
            // - Se guarda como 1 (true) o 0 (false) en columna TINYINT
            //
            // EJEMPLOS:
            // isActivo() = true  → 1 (usuario activo)
            // isActivo() = false → 0 (usuario inactivo)
            //
            // EQUIVALENTE CON if:
            // if ($user->isActivo()) {
            //     $activo = 1;
            // } else {
            //     $activo = 0;
            // }
            //
            // RELACIÓN CON JS:
            // PHP:        $activo = $user->isActivo() ? 1 : 0
            // JavaScript: const activo = user.isActivo() ? 1 : 0
            // ================================================================
        ];

        $this->db->query($sql, $params);
        // ====================================================================
        // $this->db->query() - Ejecutar consulta SQL
        // ====================================================================
        // ¿QUÉ HACE?
        // - Pasa el SQL y parámetros a Database
        // - Database usa PDO: prepare() + execute()
        // - PDO reemplaza los ? con los valores de $params
        //
        // INTERNAMENTE EN Database::query():
        // $stmt = $this->connection->prepare($sql);
        // $stmt->execute($params);
        // return $stmt;
        //
        // SEGURIDAD PDO:
        // PDO escapa automáticamente:
        // - Comillas: Juan's → Juan''s (seguro)
        // - SQL especial: DROP, -- → tratado como texto
        // ====================================================================

        return (int) $this->db->lastInsertId();
        // ====================================================================
        // lastInsertId() - Obtener ID del último INSERT
        // ====================================================================
        // ¿QUÉ HACE?
        // - Devuelve el ID auto-generado por el último INSERT
        // - En MySQL: el valor AUTO_INCREMENT asignado
        //
        // ¿POR QUÉ (int)?
        // - lastInsertId() devuelve STRING (no entero)
        // - (int) convierte: "42" → 42
        // - El código que llama espera int, no string
        //
        // EJEMPLO:
        // Antes de create(): tabla tiene IDs 1, 2, 3
        // create() inserta el usuario
        // lastInsertId() → "4"
        // (int) "4" → 4
        //
        // IMPORTANTE:
        // - Siempre obtener lastInsertId() INMEDIATAMENTE después del INSERT
        // - Otro INSERT de otro proceso puede interferir
        //
        // ¿POR QUÉ DEVOLVER EL ID?
        // - El código que llama necesita saber el ID asignado
        // - Ej: redirigir a /usuarios/42 (la página del usuario creado)
        // - Ej: relacionar con otras tablas
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: findById
    // OPERACIÓN SQL: SELECT WHERE id = ?
    // ========================================================================

    /**
     * Encontrar un usuario por su ID
     *
     * @param  int       $id  ID del usuario a buscar
     * @return User|null      Objeto User/AdminUser o null si no existe
     *
     * USO:
     *   $user = $repo->findById(5);
     *   if ($user === null) { echo "No encontrado"; }
     *   else { echo $user->getNombre(); }
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        // ====================================================================
        // SELECT * - Obtener todas las columnas
        // ====================================================================
        // SELECT *: Devuelve todas las columnas de la tabla
        //   id, nombre, email, tipo_usuario, fecha_creacion, activo
        //
        // WHERE id = ?: Filtrar por ID específico
        //   ? → se reemplaza con $id al ejecutar
        //
        // LIMIT 1: Máximo 1 resultado
        //   - id es UNIQUE (clave primaria), solo puede haber uno
        //   - LIMIT 1 es una optimización: MySQL para de buscar al encontrarlo
        //   - Sin LIMIT 1: MySQL revisa TODA la tabla aunque ya encontró el registro
        //
        // ANALOGÍA:
        // Es como buscar en una guía telefónica por número de cédula.
        // Como es único, al encontrarlo, paras de buscar (LIMIT 1).
        // ====================================================================

        $stmt = $this->db->query($sql, [$id]);
        // ====================================================================
        // $this->db->query($sql, [$id])
        // ====================================================================
        // [$id] → Array con UN elemento
        // - query() espera array como segundo parámetro
        // - [5] → PDO reemplaza el ? con 5
        // - Resultado SQL: "SELECT * FROM usuarios WHERE id = 5 LIMIT 1"
        //
        // $stmt → PDOStatement (objeto resultado de la consulta)
        // - Contiene los resultados pero aún no los extraemos
        // - Es como tener una carpeta de resultados lista para abrir
        // ====================================================================

        $data = $stmt->fetch();
        // ====================================================================
        // fetch() - Obtener UNA fila del resultado
        // ====================================================================
        // ¿QUÉ HACE?
        // - Extrae una fila del resultado de la consulta
        // - Devuelve array asociativo por defecto (PDO::FETCH_ASSOC)
        // - Si no hay resultado → devuelve false
        //
        // RESULTADO CUANDO EXISTE:
        // $data = [
        //     'id'             => 5,
        //     'nombre'         => 'Juan Pérez',
        //     'email'          => 'juan@gmail.com',
        //     'tipo_usuario'   => 'normal',
        //     'fecha_creacion' => '2024-01-15 10:30:00',
        //     'activo'         => 1
        // ]
        //
        // RESULTADO CUANDO NO EXISTE:
        // $data = false
        //
        // fetch() vs fetchAll():
        // ┌────────────────┬──────────────────────────────────────────────┐
        // │ fetch()        │ UNA fila como array asociativo               │
        // │ fetchAll()     │ TODAS las filas como array de arrays         │
        // └────────────────┴──────────────────────────────────────────────┘
        //
        // ¿CUÁNDO USAR CADA UNO?
        // fetch():    Cuando esperamos 0 o 1 resultado (findById, findByEmail)
        // fetchAll(): Cuando esperamos múltiples resultados (findAll)
        // ====================================================================

        if (!$data) {
            return null;
            // ================================================================
            // MANEJO DEL CASO "NO ENCONTRADO"
            // ================================================================
            // !$data es true cuando:
            // - fetch() devuelve false (usuario no existe en BD)
            //
            // ¿POR QUÉ DEVOLVER null?
            // - Indica "no existe" de forma semántica
            // - El código que llama puede verificar: if ($user === null)
            //
            // PATRÓN "EARLY RETURN":
            // - Salir del método lo antes posible si no hay datos
            // - Evita anidamiento profundo
            // - Más legible que: if ($data) { ... mucho código ... }
            //
            // USO CORRECTO:
            // $user = $repo->findById(99);
            // if ($user === null) {
            //     // Usuario no encontrado
            //     http_response_code(404);
            //     echo json_encode(['error' => 'Usuario no encontrado']);
            //     exit;
            // }
            // echo $user->getNombre(); // Si llegamos aquí, $user existe
            // ================================================================
        }

        return UserFactory::create($data);
        // ====================================================================
        // UserFactory::create($data) - Crear objeto del tipo correcto
        // ====================================================================
        // ¿POR QUÉ FACTORY Y NO new User($data)?
        //
        // PROBLEMA con new User() directo:
        //   $data['tipo_usuario'] = 'admin'
        //   $user = new User($data); ← Crea User base, NO AdminUser
        //   $user->getPermisos();    ← ERROR: User no tiene getPermisos()
        //
        // SOLUCIÓN con Factory:
        //   $user = UserFactory::create($data);
        //   - Si tipo_usuario = 'admin' → new AdminUser($data)
        //   - Si tipo_usuario = 'normal' → new User($data)
        //   $user->getPermisos(); ← OK si es AdminUser
        //
        // POLIMORFISMO:
        // El resultado puede ser User o AdminUser,
        // pero ambos tienen los métodos básicos de User.
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: findByEmail
    // OPERACIÓN SQL: SELECT WHERE email = ?
    // ========================================================================

    /**
     * Encontrar un usuario por email
     *
     * ÚTIL PARA:
     * - Login (verificar si existe el email)
     * - Evitar duplicados antes de registrar
     *
     * @param  string    $email  Email a buscar
     * @return User|null         Usuario encontrado o null
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        // ====================================================================
        // Igual que findById pero busca por email
        // email tiene índice UNIQUE en la BD → máximo 1 resultado
        // LIMIT 1 igual que antes: optimización de rendimiento
        // ====================================================================

        $stmt = $this->db->query($sql, [$email]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return UserFactory::create($data);
        // ====================================================================
        // Mismo patrón que findById:
        // 1. Consultar BD
        // 2. Si no hay resultado → null
        // 3. Si hay resultado → UserFactory crea objeto correcto
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: findAll
    // OPERACIÓN SQL: SELECT con filtros dinámicos + paginación
    // ========================================================================

    /**
     * Obtener todos los usuarios con filtros opcionales
     *
     * SOPORTA:
     * - Filtro por tipo_usuario
     * - Filtro por estado activo/inactivo
     * - Búsqueda por nombre o email (LIKE)
     * - Ordenamiento dinámico
     * - Paginación (LIMIT + OFFSET)
     *
     * @param  array $filtros Filtros opcionales (ver abajo)
     * @return array          Array de objetos User/AdminUser
     *
     * EJEMPLO DE FILTROS:
     *   $filtros = [
     *       'tipo_usuario' => 'admin',     // Solo admins
     *       'activo'       => true,         // Solo activos
     *       'busqueda'     => 'juan',       // Nombre o email contiene "juan"
     *       'orden'        => 'nombre',     // Ordenar por nombre
     *       'direccion'    => 'ASC',        // Ascendente
     *       'limite'       => 10,           // 10 por página
     *       'offset'       => 20            // Empezar desde el registro 20
     *   ];
     */
    public function findAll($filtros = []) {

        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        // ====================================================================
        // TRUCO: WHERE 1=1
        // ====================================================================
        // ¿QUÉ ES WHERE 1=1?
        // - Condición siempre verdadera
        // - Selecciona TODOS los registros (sin filtrar)
        // - Por sí sola, equivale a no tener WHERE
        //
        // ¿POR QUÉ USARLO?
        // PROBLEMA sin WHERE 1=1:
        //   $sql = "SELECT * FROM usuarios";
        //   if ($filtro1) { $sql .= " WHERE tipo_usuario = ?"; }
        //   if ($filtro2) { $sql .= " ??? activo = ?"; }
        //   // ¿Pongo WHERE o AND? Depende de si filtro1 ya puso WHERE
        //
        // SOLUCIÓN con WHERE 1=1:
        //   $sql = "SELECT * FROM usuarios WHERE 1=1";
        //   if ($filtro1) { $sql .= " AND tipo_usuario = ?"; }
        //   if ($filtro2) { $sql .= " AND activo = ?"; }
        //   // SIEMPRE uso AND porque WHERE 1=1 ya está
        //
        // RESULTADO:
        // Sin filtros: "SELECT * FROM usuarios WHERE 1=1"
        //   → MySQL lo optimiza → equivale a SELECT * FROM usuarios
        //
        // Con filtros: "SELECT * FROM usuarios WHERE 1=1 AND tipo_usuario = ?"
        //   → Filtra correctamente
        // ====================================================================

        $params = [];
        // Inicializar array vacío para acumular parámetros
        // Cada filtro que se agrega al SQL también agrega su valor aquí

        // Filtro por tipo de usuario
        if (isset($filtros['tipo_usuario'])) {
            // ================================================================
            // isset() - Verificar si clave existe en array
            // ================================================================
            // SINTAXIS:
            // isset($array['clave'])
            //
            // ¿QUÉ HACE?
            // - Devuelve true si la clave existe Y no es null
            // - Devuelve false si la clave no existe o es null
            //
            // DIFERENCIA CON empty() Y array_key_exists():
            // isset($a['k'])             → false si no existe O si es null
            // empty($a['k'])             → true si no existe, null, "", 0, false
            // array_key_exists('k', $a)  → true si existe (aunque sea null)
            //
            // ¿POR QUÉ isset() AQUÍ?
            // - Verificar que el filtro fue proporcionado
            // - Si $filtros['tipo_usuario'] no existe → no aplicar filtro
            // - Permite llamar findAll() sin ese filtro sin error
            //
            // EJEMPLO:
            // $filtros = ['activo' => true]  ← Sin 'tipo_usuario'
            // isset($filtros['tipo_usuario']) → false → no se aplica filtro
            //
            // $filtros = ['tipo_usuario' => 'admin']
            // isset($filtros['tipo_usuario']) → true → se aplica filtro
            // ================================================================

            $sql .= " AND tipo_usuario = ?";
            // ================================================================
            // .= (operador concatenación-asignación)
            // ================================================================
            // SINTAXIS: $var .= $texto  equivale a  $var = $var . $texto
            //
            // CONSTRUYENDO SQL DINÁMICO:
            // Inicio:    "SELECT * FROM usuarios WHERE 1=1"
            // + filtro:  "SELECT * FROM usuarios WHERE 1=1 AND tipo_usuario = ?"
            // ================================================================

            $params[] = $filtros['tipo_usuario'];
            // ================================================================
            // Agregar valor del filtro al array de parámetros
            // El ORDER importa: cada ? se reemplaza con el param en la misma posición
            //
            // SQL:    "... WHERE 1=1 AND tipo_usuario = ?"
            //                                           ↑ posición 1
            // Params: ['admin']
            //           ↑ posición 1 → reemplaza el primer ?
            // ================================================================
        }

        // Filtro por estado activo/inactivo
        if (isset($filtros['activo'])) {
            $sql .= " AND activo = ?";
            $params[] = $filtros['activo'] ? 1 : 0;
            // ================================================================
            // Convertir bool → int igual que en create()
            // true → 1, false → 0 (MySQL TINYINT)
            // ================================================================
        }

        // Búsqueda por nombre o email
        if (isset($filtros['busqueda'])) {
            $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
            // ================================================================
            // OPERADOR LIKE - Búsqueda parcial de texto
            // ================================================================
            // SINTAXIS SQL:
            // columna LIKE '%texto%'
            //
            // WILDCARDS (comodines) en LIKE:
            // % → Cualquier cantidad de caracteres (0 o más)
            // _ → Exactamente un carácter
            //
            // EJEMPLOS:
            // LIKE '%juan%'  → Contiene "juan" en cualquier posición
            //   'juan pérez'  ✓
            //   'Dr. juan'    ✓
            //   'juanita'     ✓
            //   'pedro'       ✗
            //
            // LIKE 'juan%'   → Empieza con "juan"
            //   'juanita'     ✓
            //   'Dr. juan'    ✗
            //
            // LIKE '%juan'   → Termina con "juan"
            //   'Dr. juan'    ✓
            //   'juanita'     ✗
            //
            // OR en SQL:
            // nombre LIKE ? OR email LIKE ?
            // - Busca en nombre O en email
            // - Si coincide en cualquiera → devuelve el registro
            //
            // NOTA: Dos ? → Dos parámetros (mismo valor para ambos)
            // ================================================================

            $busqueda = "%{$filtros['busqueda']}%";
            // ================================================================
            // CONSTRUIR EL PATRÓN DE BÚSQUEDA CON WILDCARDS
            // ================================================================
            // {$filtros['busqueda']} → Interpolación en string con {}
            //
            // Si $filtros['busqueda'] = 'juan':
            // "%{juan}%" → "%juan%"
            //
            // RESULTADO:
            // LIKE '%juan%' → Busca "juan" en cualquier posición
            // ================================================================

            $params[] = $busqueda;  // Para el primer ? (nombre LIKE ?)
            $params[] = $busqueda;  // Para el segundo ? (email LIKE ?)
            // ================================================================
            // Se agrega DOS VECES porque hay dos ? en la consulta
            // SQL: "AND (nombre LIKE ? OR email LIKE ?)"
            //                       ↑              ↑
            // Params: [..., '%juan%', '%juan%']
            //                  ↑           ↑
            //             primer ?    segundo ?
            // ================================================================
        }

        // Ordenamiento dinámico
        $orden = $filtros['orden'] ?? 'id';
        $direccion = $filtros['direccion'] ?? 'ASC';
        $sql .= " ORDER BY {$orden} {$direccion}";
        // ====================================================================
        // ORDER BY DINÁMICO
        // ====================================================================
        // $orden: Columna por la que ordenar (id, nombre, email, fecha_creacion)
        // $direccion: Dirección (ASC = ascendente, DESC = descendente)
        //
        // VALORES POR DEFECTO (??):
        // $orden = 'id'   → Sin filtro, ordenar por ID
        // $direccion = 'ASC' → Sin filtro, orden ascendente
        //
        // IMPORTANTE - RIESGO DE SEGURIDAD:
        // $orden y $direccion se interpolán DIRECTAMENTE en el SQL
        // NO se usan como placeholders (?)
        // Razón: ORDER BY no acepta placeholders en PDO
        //
        // MITIGACIÓN:
        // En producción, validar contra lista blanca:
        // $columnasPermitidas = ['id', 'nombre', 'email', 'fecha_creacion'];
        // if (!in_array($orden, $columnasPermitidas)) { $orden = 'id'; }
        // $direccionesPermitidas = ['ASC', 'DESC'];
        // if (!in_array($direccion, $direccionesPermitidas)) { $direccion = 'ASC'; }
        //
        // EJEMPLOS:
        // Sin filtros: "ORDER BY id ASC"
        // Con filtros: "ORDER BY nombre DESC"
        // ====================================================================

        // Paginación: LIMIT (cantidad) y OFFSET (desde dónde)
        if (isset($filtros['limite'])) {
            $sql .= " LIMIT ?";
            $params[] = (int) $filtros['limite'];
            // ================================================================
            // LIMIT - Limitar cantidad de resultados
            // ================================================================
            // SINTAXIS SQL:
            // SELECT * FROM tabla LIMIT 10
            // → Devuelve máximo 10 registros
            //
            // PAGINACIÓN:
            // Página 1: LIMIT 10 OFFSET 0  → registros 1-10
            // Página 2: LIMIT 10 OFFSET 10 → registros 11-20
            // Página 3: LIMIT 10 OFFSET 20 → registros 21-30
            //
            // (int) $filtros['limite']
            // - Forzar entero por seguridad
            // - Previene valores como "10; DROP TABLE" (aunque PDO ya lo evita)
            //
            // PAGINACIÓN EN FRONTEND:
            // En app.js se calcula: offset = (pagina - 1) * limite
            // ================================================================

            if (isset($filtros['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int) $filtros['offset'];
                // ============================================================
                // OFFSET - Saltar N registros antes de devolver
                // ============================================================
                // SINTAXIS SQL:
                // SELECT * FROM tabla LIMIT 10 OFFSET 20
                // → Salta 20 registros, devuelve los siguientes 10
                //
                // CÁLCULO DE OFFSET:
                // offset = (numeroPagina - 1) * registrosPorPagina
                //
                // Página 1: (1-1) * 10 = 0  → OFFSET 0
                // Página 2: (2-1) * 10 = 10 → OFFSET 10
                // Página 3: (3-1) * 10 = 20 → OFFSET 20
                // ============================================================
            }
        }

        $stmt = $this->db->query($sql, $params);
        // ====================================================================
        // SQL FINAL POSIBLE (con todos los filtros):
        // "SELECT * FROM usuarios
        //  WHERE 1=1
        //  AND tipo_usuario = ?
        //  AND activo = ?
        //  AND (nombre LIKE ? OR email LIKE ?)
        //  ORDER BY nombre ASC
        //  LIMIT ?
        //  OFFSET ?"
        //
        // $params = ['admin', 1, '%juan%', '%juan%', 10, 0]
        //              ↑     ↑     ↑          ↑       ↑   ↑
        //           tipo  activo busq1      busq2   lim offset
        // ====================================================================

        $resultados = $stmt->fetchAll();
        // ====================================================================
        // fetchAll() - Obtener TODAS las filas
        // ====================================================================
        // ¿QUÉ HACE?
        // - Extrae TODAS las filas del resultado
        // - Devuelve array de arrays asociativos
        // - Si no hay resultados → array vacío []
        //
        // DIFERENCIA CON fetch():
        // fetch()    → array asociativo (una fila)
        // fetchAll() → array de arrays  (todas las filas)
        //
        // RESULTADO:
        // $resultados = [
        //     ['id' => 1, 'nombre' => 'Juan', ...],
        //     ['id' => 2, 'nombre' => 'María', ...],
        //     ['id' => 3, 'nombre' => 'Carlos', ...]
        // ]
        //
        // O vacío si no hay resultados:
        // $resultados = []
        // ====================================================================

        return UserFactory::createMultiple($resultados);
        // ====================================================================
        // UserFactory::createMultiple() - Convertir array de datos a objetos
        // ====================================================================
        // Recibe: Array de arrays (datos crudos de BD)
        // Devuelve: Array de objetos User/AdminUser
        //
        // INTERNAMENTE (en UserFactory):
        // foreach ($resultados as $userData) {
        //     $resultado[] = self::create($userData); // User o AdminUser
        // }
        //
        // RESULTADO FINAL:
        // [User, AdminUser, User, User, AdminUser, ...]
        // Cada objeto es del tipo correcto según tipo_usuario
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: update
    // OPERACIÓN SQL: UPDATE SET ... WHERE id = ?
    // ========================================================================

    /**
     * Actualizar un usuario existente
     *
     * @param  User $user  Usuario con datos actualizados (debe tener ID)
     * @return bool        true si se actualizó al menos 1 fila, false si no
     */
    public function update(User $user) {
        $sql = "UPDATE {$this->table}
                SET nombre       = ?,
                    email        = ?,
                    tipo_usuario = ?,
                    activo       = ?
                WHERE id = ?";
        // ====================================================================
        // UPDATE SQL
        // ====================================================================
        // SINTAXIS SQL:
        // UPDATE tabla
        // SET columna1 = valor1,
        //     columna2 = valor2
        // WHERE condicion
        //
        // IMPORTANTE: Siempre incluir WHERE
        // Sin WHERE: UPDATE usuarios SET activo = 0
        // → Desactiva TODOS los usuarios (¡catastrófico!)
        //
        // Con WHERE: UPDATE usuarios SET activo = 0 WHERE id = ?
        // → Solo desactiva el usuario específico
        //
        // COLUMNAS NO ACTUALIZADAS:
        // - id: No se cambia (es la clave primaria)
        // - fecha_creacion: No se cambia (registra cuándo se creó)
        // ====================================================================

        $params = [
            $user->getNombre(),
            $user->getEmail(),
            $user->getTipoUsuario(),
            $user->isActivo() ? 1 : 0,  // bool → int
            $user->getId()               // WHERE id = ? ← Va al final
            // ================================================================
            // ORDEN DE PARÁMETROS:
            // Los ? se reemplazan en orden de aparición en el SQL
            //
            // SQL:     SET nombre=?,  email=?,  tipo=?,  activo=?  WHERE id=?
            // Params: [getNombre(), getEmail(), getTipo(), 0/1, getId()]
            //               ↑           ↑          ↑        ↑       ↑
            //             pos 1       pos 2      pos 3    pos 4   pos 5
            // ================================================================
        ];

        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
        // ====================================================================
        // rowCount() - Contar filas afectadas
        // ====================================================================
        // ¿QUÉ HACE?
        // - Devuelve el número de filas modificadas por el último UPDATE/DELETE/INSERT
        //
        // CASOS:
        // rowCount() = 1 → Usuario actualizado correctamente
        // rowCount() = 0 → No se actualizó nada (ID no existe o datos iguales)
        //
        // ¿POR QUÉ > 0?
        // - Si el usuario no existe → 0 filas afectadas → false
        // - Si el usuario existe y se actualiza → 1 fila → true
        //
        // NOTA: Si los datos son IDÉNTICOS a los actuales,
        // MySQL puede devolver 0 incluso si el usuario existe
        // (porque técnicamente no hubo cambio)
        //
        // DEVUELVE BOOL:
        // 0 > 0 → false (no se actualizó)
        // 1 > 0 → true  (se actualizó)
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: delete (SOFT DELETE)
    // OPERACIÓN SQL: UPDATE SET activo = 0 WHERE id = ?
    // ========================================================================

    /**
     * Eliminar lógicamente un usuario (Soft Delete)
     *
     * NO elimina el registro, solo lo marca como inactivo (activo = 0)
     *
     * VENTAJAS DEL SOFT DELETE:
     * - Recuperable: se puede reactivar con UPDATE activo = 1
     * - Historial: el registro sigue existiendo para auditoría
     * - Integridad: no rompe relaciones con otras tablas
     * - Seguridad: evita pérdida accidental de datos
     *
     * @param  int  $id  ID del usuario a desactivar
     * @return bool      true si se desactivó, false si no existe
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET activo = 0 WHERE id = ?";
        // ====================================================================
        // SOFT DELETE: UPDATE en lugar de DELETE
        // ====================================================================
        // HARD DELETE (eliminación física):
        //   DELETE FROM usuarios WHERE id = ?
        //   → El registro DESAPARECE de la BD
        //   → Irrecuperable
        //
        // SOFT DELETE (eliminación lógica):
        //   UPDATE usuarios SET activo = 0 WHERE id = ?
        //   → El registro SIGUE en la BD
        //   → activo = 0 significa "deshabilitado"
        //   → Recuperable: UPDATE usuarios SET activo = 1 WHERE id = ?
        //
        // PRÁCTICA COMÚN en aplicaciones reales:
        // La mayoría de apps profesionales usa soft delete
        // Gmail no borra emails, los mueve a Papelera
        // Facebook no borra cuentas inmediatamente, las "desactiva"
        //
        // activo = 0 (hardcoded, no placeholder)
        // - Es un valor fijo, no viene del usuario
        // - No hay riesgo de inyección
        // - id = ? sí es placeholder (viene del usuario)
        // ====================================================================

        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    // ========================================================================
    // MÉTODO: forceDelete (HARD DELETE)
    // OPERACIÓN SQL: DELETE FROM WHERE id = ?
    // ========================================================================

    /**
     * Eliminar permanentemente un usuario
     *
     * ⚠️ IRREVERSIBLE: El registro se elimina físicamente de la BD
     *
     * ÚSALO CON PRECAUCIÓN:
     * - Solo para datos que definitivamente no se necesitarán
     * - Considera soft delete (delete()) primero
     * - Puede romper integridad referencial con otras tablas
     *
     * @param  int  $id  ID del usuario a eliminar
     * @return bool      true si se eliminó, false si no existe
     */
    public function forceDelete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        // ====================================================================
        // DELETE SQL - Eliminación física
        // ====================================================================
        // SINTAXIS:
        // DELETE FROM tabla WHERE condicion
        //
        // ¿QUÉ HACE?
        // - Elimina el/los registros que cumplen la condición
        // - Operación IRREVERSIBLE
        //
        // RIESGO INTEGRIDAD REFERENCIAL:
        // Si existe tabla 'pedidos' con columna 'usuario_id':
        //   DELETE FROM usuarios WHERE id = 5
        //   → ¿Qué pasa con pedidos donde usuario_id = 5?
        //   Opciones de MySQL:
        //   - RESTRICT: Error, no permite eliminar
        //   - CASCADE: Elimina también los pedidos
        //   - SET NULL: Pone usuario_id = null en pedidos
        //
        // POR ESO soft delete es más seguro en la mayoría de casos
        // ====================================================================

        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    // ========================================================================
    // MÉTODO: emailExists
    // OPERACIÓN SQL: SELECT COUNT(*) WHERE email = ?
    // ========================================================================

    /**
     * Verificar si un email ya está registrado
     *
     * PARÁMETRO $excludeId:
     * Útil al ACTUALIZAR un usuario. Si cambias el email,
     * debes verificar que no lo use otro usuario,
     * pero SÍ puede ser el email del propio usuario.
     *
     * EJEMPLO:
     *   // Verificar antes de registrar nuevo usuario:
     *   if ($repo->emailExists('juan@email.com')) {
     *       echo "Email ya registrado";
     *   }
     *
     *   // Verificar al actualizar usuario ID 5:
     *   if ($repo->emailExists('juan@email.com', 5)) {
     *       // Ignora si el email pertenece al usuario 5
     *       echo "Email en uso por otro usuario";
     *   }
     *
     * @param  string   $email      Email a verificar
     * @param  int|null $excludeId  ID a excluir de la verificación
     * @return bool                 true si el email ya existe
     */
    public function emailExists($email, $excludeId = null) {
        // ====================================================================
        // PARÁMETRO OPCIONAL: $excludeId = null
        // ====================================================================
        // = null → Valor por defecto
        // Si no se pasa: $excludeId = null (no excluir nadie)
        // Si se pasa:    $excludeId = 5    (excluir usuario 5)
        // ====================================================================

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        // ====================================================================
        // COUNT(*) - Contar registros
        // ====================================================================
        // SINTAXIS SQL:
        // SELECT COUNT(*) as count FROM tabla WHERE condicion
        //
        // ¿QUÉ HACE COUNT(*)?
        // - Cuenta cuántas filas cumplen la condición
        // - * significa "contar todas las columnas" (cualquier fila)
        //
        // 'as count': Alias para la columna
        // - Sin alias: $result['COUNT(*)'] ← Difícil de usar
        // - Con alias: $result['count']    ← Fácil de usar
        //
        // RESULTADO:
        // $result = ['count' => 0]  ← Email no existe
        // $result = ['count' => 1]  ← Email existe
        //
        // SELECT COUNT vs SELECT *:
        // SELECT * FROM usuarios WHERE email = ?
        // → Trae todos los datos del usuario (más datos, más lento)
        //
        // SELECT COUNT(*) FROM usuarios WHERE email = ?
        // → Solo devuelve un número (más eficiente)
        // ====================================================================

        $params = [$email];

        if ($excludeId !== null) {
            // ================================================================
            // !== null (diferente de null, estricto)
            // ================================================================
            // ¿Por qué !== y no != ?
            // - !== verifica valor Y tipo
            // - if ($excludeId != null) → true si es 0 (¡incorrecto!)
            //   porque 0 == null en PHP (comparación no estricta)
            // - if ($excludeId !== null) → false si es 0 (correcto)
            //   porque 0 !== null (son diferentes)
            //
            // EJEMPLO:
            // $excludeId = 0 (ID de usuario)
            // 0 != null  → true  ← Incorrecto, no debería excluir
            // 0 !== null → false ← Correcto, no excluye (null = no excluir)
            // ================================================================

            $sql .= " AND id != ?";
            // ================================================================
            // != en SQL (diferente de)
            // ================================================================
            // AND id != ?
            // → Excluir el usuario con este ID
            //
            // RESULTADO COMPLETO:
            // "SELECT COUNT(*) as count FROM usuarios WHERE email = ? AND id != ?"
            //
            // USO:
            // emailExists('juan@email.com', 5)
            // → Busca emails = 'juan@email.com' EXCLUYENDO id = 5
            // → Si solo el usuario 5 tiene ese email → count = 0 → false
            // → Si otro usuario tiene ese email → count = 1 → true
            // ================================================================

            $params[] = $excludeId;
        }

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
        // ====================================================================
        // $result['count'] > 0
        // ====================================================================
        // $result = ['count' => 1] → 1 > 0 → true  (email existe)
        // $result = ['count' => 0] → 0 > 0 → false (email libre)
        //
        // DEVUELVE BOOL DIRECTAMENTE:
        // No necesita if/else, la comparación ya devuelve bool
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: count
    // OPERACIÓN SQL: SELECT COUNT(*) con filtros
    // ========================================================================

    /**
     * Contar usuarios con filtros opcionales
     *
     * ÚTIL PARA:
     * - Total de páginas en paginación
     * - Estadísticas simples
     * - Dashboard (cuántos usuarios activos, etc.)
     *
     * @param  array $filtros Filtros opcionales (tipo_usuario, activo)
     * @return int            Cantidad de usuarios
     */
    public function count($filtros = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];

        if (isset($filtros['tipo_usuario'])) {
            $sql .= " AND tipo_usuario = ?";
            $params[] = $filtros['tipo_usuario'];
        }

        if (isset($filtros['activo'])) {
            $sql .= " AND activo = ?";
            $params[] = $filtros['activo'] ? 1 : 0;
        }
        // ====================================================================
        // Mismo patrón de filtros que findAll()
        // WHERE 1=1 + AND condicion por cada filtro presente
        // ====================================================================

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return (int) $result['count'];
        // ====================================================================
        // (int) $result['count']
        // ====================================================================
        // fetch() devuelve strings por defecto en PDO
        // $result['count'] = "42" (string)
        // (int) "42" → 42 (entero)
        //
        // DEVOLUCIÓN COMO int:
        // Permite usar el resultado en operaciones matemáticas
        // Ej: $totalPaginas = ceil($total / $porPagina)
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: getStatistics
    // OPERACIÓN SQL: SELECT con SUM(CASE WHEN...) 
    // ========================================================================

    /**
     * Obtener estadísticas completas de usuarios
     *
     * DEVUELVE EN UNA SOLA CONSULTA:
     * - Total de usuarios
     * - Cantidad de admins
     * - Cantidad de normales
     * - Cantidad de activos
     * - Cantidad de inactivos
     *
     * @return array Estadísticas ['total', 'admins', 'normales', 'activos', 'inactivos']
     */
    public function getStatistics() {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo_usuario = 'admin'  THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN tipo_usuario = 'normal' THEN 1 ELSE 0 END) as normales,
                    SUM(CASE WHEN activo = 1               THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN activo = 0               THEN 1 ELSE 0 END) as inactivos
                FROM {$this->table}";
        // ====================================================================
        // SQL AVANZADO: SUM(CASE WHEN ... THEN 1 ELSE 0 END)
        // ====================================================================
        // TÉCNICA: Conteo condicional en una sola consulta
        //
        // PROBLEMA a resolver:
        // Necesitamos múltiples conteos de una tabla.
        // Opción A: Múltiples consultas:
        //   SELECT COUNT(*) FROM usuarios                        ← total
        //   SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin'  ← admins
        //   SELECT COUNT(*) FROM usuarios WHERE tipo = 'normal' ← normales
        //   SELECT COUNT(*) FROM usuarios WHERE activo = 1      ← activos
        //   SELECT COUNT(*) FROM usuarios WHERE activo = 0      ← inactivos
        //   → 5 consultas a la BD (lento)
        //
        // Opción B: Una sola consulta (usado aquí):
        //   Obtiene todo en 1 consulta (rápido)
        //
        // DESGLOSE DEL TRUCO SUM(CASE WHEN):
        //
        // CASE WHEN ... THEN ... ELSE ... END
        // - Condicional dentro de SQL (como if/else en PHP)
        // - CASE WHEN condicion THEN valor_si_verdad ELSE valor_si_falso END
        //
        // EJEMPLO para una fila donde tipo_usuario = 'admin':
        //   CASE WHEN tipo_usuario = 'admin' THEN 1 ELSE 0 END → 1
        //   CASE WHEN tipo_usuario = 'admin' THEN 1 ELSE 0 END → 0 (si es normal)
        //
        // SUM() suma todos los resultados de CASE WHEN:
        // Si hay 3 admins y 7 normales en la tabla:
        // Columna CASE para admins: [1, 0, 0, 1, 1, 0, 0, 0, 0, 0]
        //                            ↑ admin  ↑ admin  ↑ admin
        // SUM([1,0,0,1,1,0,0,0,0,0]) = 3 → "admins" = 3
        //
        // RESULTADO FINAL:
        // $stats = [
        //     'total'    => 10,
        //     'admins'   => 3,
        //     'normales' => 7,
        //     'activos'  => 8,
        //     'inactivos'=> 2
        // ]
        //
        // FUNCIÓN COUNT(*) vs SUM(CASE WHEN):
        // COUNT(*): Cuenta TODAS las filas
        // SUM(CASE WHEN): Cuenta filas que cumplen condición específica
        //
        // ANALOGÍA:
        // Tienes 10 estudiantes, quieres saber cuántos aprobaron.
        // Sin CASE: SELECT COUNT(*) WHERE nota >= 60 (consulta separada)
        // Con CASE: SUM(CASE WHEN nota >= 60 THEN 1 ELSE 0 END) (en misma consulta)
        // ====================================================================

        $stmt = $this->db->query($sql);
        // ====================================================================
        // query() SIN parámetros
        // ====================================================================
        // No hay placeholders ? en esta consulta
        // Los valores están hardcoded en el SQL ('admin', 'normal', 1, 0)
        // Estos no vienen del usuario → no hay riesgo de inyección
        // ====================================================================

        return $stmt->fetch();
        // ====================================================================
        // Devuelve el array de estadísticas directamente
        // Una sola fila (COUNT y SUM siempre devuelven 1 fila)
        //
        // RESULTADO:
        // [
        //     'total'    => '10',
        //     'admins'   => '3',
        //     'normales' => '7',
        //     'activos'  => '8',
        //     'inactivos'=> '2'
        // ]
        // Nota: PDO devuelve strings, convertir con (int) si se necesita aritmética
        // ====================================================================
    }
}
// Fin de clase UserRepository

// ============================================================================
// RESUMEN DE CONCEPTOS EXPLICADOS:
// ============================================================================
//
// PATRÓN REPOSITORY:
// - Centralización de acceso a datos
// - Separación BD / lógica de negocio
// - Reutilización de consultas
//
// PDO (PHP Data Objects):
// - Prepared Statements (prepare + execute)
// - Placeholders ? (prevención SQL Injection)
// - fetch() vs fetchAll()
// - rowCount() (filas afectadas)
// - lastInsertId() (último INSERT)
//
// SQL COMPLETO:
// - INSERT INTO ... VALUES (?, ?, ?)
// - SELECT * WHERE + LIMIT 1
// - SELECT con WHERE 1=1 dinámico
// - UPDATE SET ... WHERE id = ?
// - DELETE FROM ... WHERE id = ?
// - SELECT COUNT(*) as count
// - SELECT SUM(CASE WHEN ... THEN 1 ELSE 0 END)
// - LIKE '%busqueda%' (wildcard)
// - ORDER BY columna direccion
// - LIMIT y OFFSET (paginación)
//
// SOFT DELETE vs HARD DELETE:
// - Soft: UPDATE activo = 0 (recuperable)
// - Hard: DELETE FROM (irreversible)
//
// TÉCNICAS PHP:
// - Type Hinting (User $user)
// - isset() vs empty() vs array_key_exists()
// - Operador ternario (? :)
// - Null Coalescing (??)
// - Type Casting (int)
// - Concatenación .=
// - String interpolation ({$var})
//
// PRINCIPIOS SOLID:
// - S: UserRepository solo gestiona acceso a datos de usuarios
// - D: Depende de Database (abstracción), no de PDO directamente
//
// ============================================================================
