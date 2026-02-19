<?php
// ============================================================================
// ARCHIVO: Database.php
// UBICACIÓN: php/config/Database.php
// PROPÓSITO: Gestionar la conexión única a la base de datos
// 
// PATRÓN DE DISEÑO IMPLEMENTADO: SINGLETON
// ============================================================================
//
// ¿QUÉ ES EL PATRÓN SINGLETON?
// - Garantiza que una clase tenga SOLO UNA INSTANCIA
// - Proporciona un punto de acceso global a esa instancia
// 
// ¿POR QUÉ USAR SINGLETON PARA LA BASE DE DATOS?
// - Evita múltiples conexiones innecesarias
// - Ahorra recursos del servidor
// - Garantiza que todos usen la misma conexión
// 
// ANALOGÍA:
// Es como tener una sola llave maestra para un edificio.
// Todos la usan, pero nadie puede hacer copias.
//
// PRINCIPIOS SOLID APLICADOS:
// ✓ Single Responsibility Principle (SRP)
//   - Esta clase SOLO maneja la conexión a BD
//   - No hace consultas, no procesa datos
// 
// ✓ Dependency Inversion Principle (DIP)
//   - Otros objetos dependen de esta interfaz
//   - No dependen de la implementación específica de MySQL
// ============================================================================

/**
 * ============================================================================
 * BLOQUE DE DOCUMENTACIÓN PHPDoc
 * ============================================================================
 * SINTAXIS:
 * /** (dos asteriscos)
 *  * Texto de documentación
 *  *\/
 * 
 * ¿PARA QUÉ SIRVE?
 * - Documenta clases, métodos y propiedades
 * - Los IDEs lo usan para autocompletar
 * - Herramientas como PHPDocumentor generan documentación
 * 
 * ETIQUETAS COMUNES:
 * @param   → Describe un parámetro
 * @return  → Describe lo que devuelve
 * @throws  → Describe excepciones que puede lanzar
 * @var     → Describe el tipo de una variable
 * @author  → Autor del código
 * @version → Versión
 * ============================================================================
 */

/**
 * Clase Database - Implementación del Patrón Singleton
 * 
 * Esta clase garantiza una sola conexión a la base de datos
 * durante todo el ciclo de vida de la aplicación.
 * 
 * PATRÓN: Singleton
 * TECNOLOGÍA: PDO (PHP Data Objects)
 * BASE DE DATOS: MySQL
 * 
 * @package Config
 * @version 1.0.0
 */
class Database {
    // ========================================================================
    // PALABRA CLAVE: class
    // ========================================================================
    // class Database {
    // └─ class: Define una clase (plantilla de objetos)
    // └─ Database: Nombre de la clase (PascalCase)
    // └─ { }: Llaves que encierran el contenido de la clase
    //
    // ¿QUÉ ES UNA CLASE?
    // - Es un "molde" para crear objetos
    // - Define propiedades (variables) y métodos (funciones)
    // - Es la base de la Programación Orientada a Objetos (POO)
    //
    // CONVENCIÓN DE NOMBRES:
    // - PascalCase (primera letra mayúscula)
    // - Nombres descriptivos
    // - Generalmente un sustantivo
    //
    // ANALOGÍA:
    // class = Plano de una casa
    // objeto = La casa construida con ese plano
    // ========================================================================

    // ========================================================================
    // SECCIÓN: PROPIEDADES ESTÁTICAS (SINGLETON)
    // ========================================================================
    // Las propiedades estáticas pertenecen a la CLASE, no a objetos
    // individuales. Son compartidas por todas las instancias.
    // ========================================================================

    /**
     * @var Database|null Instancia única de la clase (Singleton)
     */
    private static $instance = null;
    // ========================================================================
    // DESGLOSE DE LA DECLARACIÓN:
    // ========================================================================
    // private static $instance = null;
    // └─ private: MODIFICADOR DE ACCESO
    // └─ static: MODIFICADOR ESTÁTICO
    // └─ $instance: NOMBRE DE LA VARIABLE
    // └─ = null: VALOR INICIAL
    //
    // MODIFICADOR "private":
    // - Solo accesible DENTRO de esta clase
    // - Nadie de afuera puede ver ni modificar
    // - Niveles de acceso en PHP:
    //   · public    → Accesible desde cualquier lugar
    //   · protected → Accesible en esta clase y subclases
    //   · private   → Solo dentro de esta clase
    //
    // MODIFICADOR "static":
    // - Pertenece a la CLASE, no a objetos individuales
    // - Se accede con: Database::$instance (no $objeto->instance)
    // - Solo existe UNA copia en memoria
    // - Todos los objetos la comparten
    //
    // DIFERENCIA static vs no static:
    // static $instance       → Una para toda la clase
    // $this->instance        → Una por cada objeto creado
    //
    // VARIABLE $instance:
    // - El símbolo $ indica que es una variable en PHP
    // - Guarda la instancia única del Singleton
    // - Tipo: Database|null (puede ser Database o null)
    //
    // VALOR INICIAL null:
    // - null = "vacío" / "sin valor"
    // - Indica que aún no se ha creado la instancia
    // - Se cambiará cuando se llame a getInstance()
    //
    // ¿POR QUÉ ES CRUCIAL PARA SINGLETON?
    // - Guarda la única instancia permitida
    // - Si es null, creamos la instancia
    // - Si no es null, devolvemos la existente
    // ========================================================================

    // ========================================================================
    // SECCIÓN: PROPIEDADES DE INSTANCIA (CONFIGURACIÓN)
    // ========================================================================
    // Estas propiedades guardan la configuración de la base de datos
    // ========================================================================

    /**
     * @var string Nombre del host/servidor de BD
     */
    private $host = 'localhost';
    // ========================================================================
    // PROPIEDAD $host
    // ========================================================================
    // private $host = 'localhost';
    // └─ private: Solo accesible dentro de la clase
    // └─ $host: Nombre de la variable
    // └─ = 'localhost': Valor por defecto
    //
    // NOTA: NO es static
    // - Pertenece a cada objeto (instancia)
    // - Se accede con: $this->host
    //
    // ¿QUÉ ES localhost?
    // - Dirección del servidor local (tu computadora)
    // - Equivalente a 127.0.0.1
    // - En producción sería algo como: 'db.ejemplo.com'
    //
    // TIPO: string (cadena de texto)
    // - En PHP no se declara el tipo (tipado débil)
    // - Pero en versiones modernas se puede: private string $host
    // ========================================================================

    /**
     * @var string Nombre de la base de datos
     */
    private $database = 'sistema_usuarios';
    // ========================================================================
    // PROPIEDAD $database
    // ========================================================================
    // - Nombre de la base de datos a usar
    // - Debe coincidir con el nombre en MySQL
    // - Creada con: CREATE DATABASE sistema_usuarios;
    // ========================================================================

    /**
     * @var string Usuario de la base de datos
     */
    private $username = 'root';
    // ========================================================================
    // PROPIEDAD $username
    // ========================================================================
    // - Usuario para conectarse a MySQL
    // - 'root' es el usuario administrador por defecto
    // - En producción: usar usuario con permisos limitados
    //
    // SEGURIDAD:
    // ❌ MAL: Usar 'root' en producción
    // ✓ BIEN: Crear usuario específico: 'app_usuarios'
    // ========================================================================

    /**
     * @var string Contraseña de la base de datos
     */
    private $password = '';
    // ========================================================================
    // PROPIEDAD $password
    // ========================================================================
    // - Contraseña del usuario de BD
    // - '' (vacío) es común en desarrollo local (XAMPP, WAMP)
    // - En producción: SIEMPRE debe tener contraseña fuerte
    //
    // BUENAS PRÁCTICAS:
    // ✓ Guardar en archivo .env (variables de entorno)
    // ✓ No subir al repositorio Git
    // ✓ Usar contraseñas diferentes por ambiente
    // ========================================================================

    /**
     * @var string Codificación de caracteres
     */
    private $charset = 'utf8mb4';
    // ========================================================================
    // PROPIEDAD $charset
    // ========================================================================
    // - Define la codificación de caracteres
    // - utf8mb4: Versión completa de UTF-8
    // - Soporta emojis y caracteres especiales
    //
    // DIFERENCIA:
    // utf8    → 3 bytes por carácter (limitado)
    // utf8mb4 → 4 bytes por carácter (completo, incluye emojis)
    //
    // IMPORTANTE PARA:
    // ✓ Tildes (á, é, í, ó, ú)
    // ✓ Letra ñ
    // ✓ Emojis (😀, 👍, ❤️)
    // ========================================================================

    /**
     * @var PDO|null Objeto de conexión PDO
     */
    private $connection;
    // ========================================================================
    // PROPIEDAD $connection
    // ========================================================================
    // - Guarda el objeto de conexión PDO
    // - PDO = PHP Data Objects (clase de PHP)
    // - Se crea en el constructor
    //
    // TIPO: PDO|null
    // - Puede ser un objeto PDO
    // - O null si no se ha conectado aún
    //
    // ¿QUÉ ES PDO?
    // - Interfaz unificada para acceder a bases de datos
    // - Funciona con: MySQL, PostgreSQL, SQLite, etc.
    // - Más seguro que mysqli (previene SQL Injection)
    // ========================================================================

    // ========================================================================
    // MÉTODO: CONSTRUCTOR PRIVADO (PATRÓN SINGLETON)
    // ========================================================================
    // El constructor es un método especial que se ejecuta al crear un objeto
    // En Singleton, es PRIVADO para evitar instanciación externa
    // ========================================================================

    /**
     * Constructor privado - Patrón Singleton
     * 
     * IMPORTANTE:
     * Este constructor es PRIVADO, por lo que NO se puede hacer:
     * $db = new Database(); // ❌ ERROR
     * 
     * Se debe usar:
     * $db = Database::getInstance(); // ✓ CORRECTO
     * 
     * @throws Exception Si falla la conexión
     */
    private function __construct() {
        // ====================================================================
        // DECLARACIÓN DE MÉTODO CONSTRUCTOR
        // ====================================================================
        // private function __construct() {
        // └─ private: MODIFICADOR DE ACCESO
        // └─ function: PALABRA CLAVE para definir métodos
        // └─ __construct: NOMBRE ESPECIAL del constructor
        // └─ (): Paréntesis para parámetros (vacío = sin parámetros)
        // └─ {}: Llaves que encierran el código del método
        //
        // ¿QUÉ ES __construct?
        // - Método "mágico" de PHP (empieza con __)
        // - Se ejecuta AUTOMÁTICAMENTE al crear el objeto
        // - No necesita llamarse manualmente
        //
        // EJEMPLO:
        // $obj = new MiClase(); ← Aquí se ejecuta __construct()
        //
        // ¿POR QUÉ ES private?
        // - PATRÓN SINGLETON: Evita crear instancias con 'new'
        // - Solo getInstance() puede llamarlo
        // - Garantiza una sola instancia
        //
        // FLUJO:
        // 1. getInstance() verifica si existe instancia
        // 2. Si no existe, llama a new self() (internamente)
        // 3. Se ejecuta __construct() y crea la conexión
        // 4. Se guarda en $instance
        // 5. Siguientes llamadas devuelven la misma instancia
        // ====================================================================

        // Construir el DSN (Data Source Name)
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
        // ====================================================================
        // DSN: DATA SOURCE NAME (NOMBRE DE ORIGEN DE DATOS)
        // ====================================================================
        // ESTRUCTURA:
        // $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
        // └─ $dsn: Variable que guarda la cadena de conexión
        // └─ "mysql:...": String (cadena de texto)
        // └─ {$this->host}: Interpolación de variables
        //
        // ¿QUÉ ES INTERPOLACIÓN?
        // - Insertar valores de variables dentro de un string
        // - Solo funciona con comillas dobles "
        // - Ejemplo: "Hola {$nombre}" → "Hola Juan"
        //
        // SINTAXIS $this->propiedad:
        // - $this: Referencia al objeto actual
        // - ->: Operador de acceso a propiedades/métodos
        // - host: Nombre de la propiedad
        //
        // ANALOGÍA:
        // $this es como decir "yo mismo" o "este objeto"
        // $this->host = "mi propiedad host"
        //
        // COMPONENTES DEL DSN:
        // mysql:              → Driver de base de datos
        // host=localhost      → Servidor de BD
        // dbname=sistema_usuarios → Nombre de la BD
        // charset=utf8mb4     → Codificación
        //
        // VALOR RESULTANTE:
        // "mysql:host=localhost;dbname=sistema_usuarios;charset=utf8mb4"
        //
        // ¿PARA QUÉ SIRVE EL DSN?
        // - Le dice a PDO CÓMO conectarse
        // - Es como una "dirección completa"
        // - PDO lo usa para establecer la conexión
        // ====================================================================

        // Opciones de configuración de PDO
        $options = [
            // ================================================================
            // ARRAY ASOCIATIVO DE OPCIONES
            // ================================================================
            // SINTAXIS:
            // $options = [clave => valor, ...];
            // └─ []: Corchetes definen un array
            // └─ clave => valor: Par clave-valor
            // └─ ,: Separador de elementos
            //
            // ¿QUÉ ES UN ARRAY ASOCIATIVO?
            // - Colección de pares clave => valor
            // - Similar a objetos en JavaScript
            // - Se accede: $options[PDO::ATTR_ERRMODE]
            //
            // SINTAXIS =>:
            // - Operador de asignación en arrays asociativos
            // - Se lee como "apunta a" o "es igual a"
            //
            // PDO::ATTR_*:
            // - Son CONSTANTES de la clase PDO
            // - :: = Operador de resolución de ámbito
            // - Se usan para configurar el comportamiento de PDO
            // ================================================================

            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // ================================================================
            // OPCIÓN: MODO DE MANEJO DE ERRORES
            // ================================================================
            // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            // └─ ATTR_ERRMODE: Atributo de modo de error
            // └─ ERRMODE_EXCEPTION: Lanzar excepciones
            //
            // ¿QUÉ HACE?
            // - Cuando hay un error de BD, lanza una Exception
            // - Permite capturar errores con try-catch
            //
            // MODOS DISPONIBLES:
            // ERRMODE_SILENT    → No reporta errores (peligroso)
            // ERRMODE_WARNING   → Muestra advertencias PHP
            // ERRMODE_EXCEPTION → Lanza excepciones (RECOMENDADO)
            //
            // ¿POR QUÉ EXCEPTION?
            // ✓ Permite manejar errores elegantemente
            // ✓ No expone detalles técnicos al usuario
            // ✓ Se puede registrar en logs
            // ================================================================

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // ================================================================
            // OPCIÓN: MODO DE OBTENCIÓN DE RESULTADOS
            // ================================================================
            // ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC
            // └─ Define cómo se devuelven los resultados de consultas
            //
            // FETCH_ASSOC:
            // - Devuelve arrays asociativos
            // - Las claves son los nombres de columnas
            //
            // EJEMPLO:
            // Consulta: SELECT id, nombre FROM usuarios
            // 
            // CON FETCH_ASSOC:
            // [
            //     'id' => 1,
            //     'nombre' => 'Juan'
            // ]
            //
            // OTROS MODOS:
            // FETCH_NUM   → Array numérico [1, 'Juan']
            // FETCH_OBJ   → Objeto stdClass
            // FETCH_BOTH  → Asociativo + numérico (duplicado)
            //
            // ¿POR QUÉ FETCH_ASSOC?
            // ✓ Más legible: $row['nombre']
            // ✓ No depende del orden de columnas
            // ✓ Menos memoria que FETCH_BOTH
            // ================================================================

            PDO::ATTR_EMULATE_PREPARES => false,
            // ================================================================
            // OPCIÓN: DESHABILITAR EMULACIÓN DE CONSULTAS PREPARADAS
            // ================================================================
            // ATTR_EMULATE_PREPARES => false
            // └─ false: Usar prepared statements REALES del servidor
            //
            // ¿QUÉ SON PREPARED STATEMENTS?
            // - Consultas SQL con parámetros (?):
            //   SELECT * FROM usuarios WHERE id = ?
            // - El servidor los prepara una vez
            // - Se pueden ejecutar múltiples veces
            //
            // DIFERENCIA:
            // true  → PHP emula, envía SQL completo
            // false → El servidor MySQL lo maneja (MÁS SEGURO)
            //
            // ¿POR QUÉ false?
            // ✓ Mejor protección contra SQL Injection
            // ✓ Validación de tipos en el servidor
            // ✓ Más eficiente para consultas repetidas
            //
            // EJEMPLO DE SQL INJECTION (prevención):
            // Código malicioso: "' OR '1'='1"
            // Con prepared statements: Se trata como texto, no código
            // ================================================================

            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            // ================================================================
            // OPCIÓN: COMANDO DE INICIALIZACIÓN
            // ================================================================
            // MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            // └─ Comando SQL a ejecutar después de conectar
            //
            // SET NAMES charset:
            // - Establece la codificación de la conexión
            // - Afecta cómo se envían y reciben datos
            //
            // ¿POR QUÉ ES NECESARIO?
            // - Garantiza coherencia de caracteres
            // - Evita problemas con tildes y símbolos
            // - Se ejecuta automáticamente al conectar
            //
            // SIN ESTO:
            // ❌ "José" podría verse como "JosÃ©"
            // ❌ Emojis no se guardarían correctamente
            // ================================================================
        ];

        // Intentar conectar a la base de datos
        try {
            // ================================================================
            // BLOQUE TRY-CATCH: MANEJO DE EXCEPCIONES
            // ================================================================
            // ESTRUCTURA:
            // try {
            //     // Código que puede fallar
            // } catch (Exception $e) {
            //     // Qué hacer si falla
            // }
            //
            // ¿PARA QUÉ SIRVE?
            // - Manejar errores elegantemente
            // - Evitar que la aplicación se detenga
            // - Registrar errores en logs
            //
            // FLUJO:
            // 1. Se ejecuta el código en try{}
            // 2. Si NO hay error: continúa normal
            // 3. Si HAY error: salta al catch{}
            // 4. Se ejecuta el código de catch{}
            //
            // ANALOGÍA:
            // try = "Intentar hacer esto"
            // catch = "Si falla, hacer esto otro"
            // ================================================================

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            // ================================================================
            // CREAR CONEXIÓN PDO
            // ================================================================
            // ESTRUCTURA:
            // $this->connection = new PDO($dsn, $username, $password, $options);
            // └─ $this->connection: Propiedad donde guardar la conexión
            // └─ =: Operador de asignación
            // └─ new: Palabra clave para crear objetos
            // └─ PDO: Clase a instanciar
            // └─ (...): Parámetros del constructor
            //
            // PALABRA CLAVE "new":
            // - Crea una nueva instancia de una clase
            // - Llama automáticamente al constructor
            // - Devuelve el objeto creado
            //
            // PARÁMETROS DE PDO:
            // 1. $dsn: Cadena de conexión
            // 2. $username: Usuario de BD
            // 3. $password: Contraseña
            // 4. $options: Array de opciones (opcional)
            //
            // ¿QUÉ HACE ESTA LÍNEA?
            // 1. Crea un objeto PDO
            // 2. Intenta conectarse a MySQL
            // 3. Si falla, lanza PDOException
            // 4. Si tiene éxito, guarda la conexión en $this->connection
            //
            // DESPUÉS DE ESTO:
            // - $this->connection es un objeto PDO funcional
            // - Se puede usar para hacer consultas
            // - Está configurado con las opciones definidas
            // ================================================================

        } catch (PDOException $e) {
            // ================================================================
            // CAPTURAR EXCEPCIONES DE PDO
            // ================================================================
            // catch (PDOException $e) {
            // └─ catch: Palabra clave para capturar excepciones
            // └─ PDOException: TIPO de excepción a capturar
            // └─ $e: Variable que contiene la excepción
            //
            // ¿QUÉ ES PDOException?
            // - Clase especial de excepciones de PDO
            // - Contiene información del error
            // - Se lanza cuando hay problemas de BD
            //
            // CONTENIDO DE $e:
            // $e->getMessage() → Mensaje de error
            // $e->getCode()    → Código de error
            // $e->getFile()    → Archivo donde ocurrió
            // $e->getLine()    → Línea donde ocurrió
            //
            // ¿POR QUÉ ESPECIFICAR PDOException?
            // - Captura SOLO errores de base de datos
            // - Otros errores no son capturados aquí
            // - Permite manejo específico por tipo
            // ================================================================

            // Registrar el error en el log del servidor (seguridad)
            error_log("Error de conexión: " . $e->getMessage());
            // ================================================================
            // FUNCIÓN error_log()
            // ================================================================
            // SINTAXIS:
            // error_log(string $mensaje);
            // └─ Registra un mensaje en el log de errores
            //
            // ¿DÓNDE SE GUARDA?
            // - En el archivo error_log de PHP
            // - Ubicación común:
            //   · XAMPP: C:\xampp\apache\logs\error.log
            //   · Linux: /var/log/apache2/error.log
            //
            // CONCATENACIÓN DE STRINGS:
            // "Error de conexión: " . $e->getMessage()
            // └─ El punto (.) concatena strings
            // └─ Similar a + en JavaScript
            //
            // ¿POR QUÉ USAR error_log()?
            // ✓ Registra errores sin mostrarlos al usuario
            // ✓ Útil para debugging en producción
            // ✓ Mantiene historial de problemas
            //
            // SEGURIDAD:
            // ✓ BIEN: error_log() - Solo los admins ven el log
            // ❌ MAL: echo $e->getMessage() - El usuario ve detalles técnicos
            // ================================================================

            // Lanzar excepción genérica (no exponer detalles de BD)
            throw new Exception("Error al conectar con la base de datos");
            // ================================================================
            // LANZAR NUEVA EXCEPCIÓN
            // ================================================================
            // SINTAXIS:
            // throw new Exception("mensaje");
            // └─ throw: Lanza una excepción
            // └─ new Exception: Crea nueva excepción
            // └─ "mensaje": Texto del error
            //
            // ¿QUÉ HACE throw?
            // - Detiene la ejecución
            // - Busca un catch{} superior que lo maneje
            // - Si no hay catch, la aplicación se detiene
            //
            // ¿POR QUÉ LANZAR NUEVA EXCEPCIÓN?
            // - NO queremos mostrar detalles de la BD al usuario
            // - Mensaje genérico es más seguro
            //
            // DIFERENCIA:
            // $e->getMessage():  "Access denied for user 'root'@'localhost'"
            // Nueva excepción:   "Error al conectar con la base de datos"
            //
            // SEGURIDAD:
            // ✓ Mensaje genérico - No revela estructura de BD
            // ✓ Detalles en error_log() - Solo para admins
            // ❌ Mensaje original - Podría ayudar a atacantes
            // ================================================================
        }
    }

    // ========================================================================
    // MÉTODO: PREVENIR CLONACIÓN (__clone)
    // ========================================================================
    // Este método previene que se clone el objeto Singleton
    // ========================================================================

    /**
     * Prevenir la clonación del objeto
     * 
     * Este método DEBE ser privado para mantener el patrón Singleton.
     * Sin esto, alguien podría hacer:
     * $db2 = clone $db1; // ❌ Crearía segunda instancia
     * 
     * @return void
     */
    private function __clone() {
        // ====================================================================
        // MÉTODO MÁGICO __clone()
        // ====================================================================
        // SINTAXIS:
        // private function __clone() {}
        // └─ __clone: Método mágico (empieza con __)
        // └─ Se ejecuta cuando se intenta clonar el objeto
        //
        // ¿QUÉ ES CLONAR?
        // - Crear una copia de un objeto
        // - Sintaxis: $copia = clone $original;
        // - Crea un nuevo objeto con los mismos valores
        //
        // ¿POR QUÉ PREVENIR LA CLONACIÓN?
        // - PATRÓN SINGLETON: Solo debe existir UNA instancia
        // - Clonar rompería esta regla
        //
        // ¿CÓMO FUNCIONA?
        // - Método privado = No se puede llamar desde fuera
        // - Método vacío = No hace nada si se llama internamente
        //
        // INTENTO DE CLONACIÓN:
        // $db1 = Database::getInstance();
        // $db2 = clone $db1; // ❌ ERROR: Call to private method
        //
        // SIN ESTE MÉTODO:
        // $db2 = clone $db1; // ✓ Funcionaría (MAL para Singleton)
        // ====================================================================
        // No hacer nada - simplemente previene la clonación
    }

    // ========================================================================
    // MÉTODO: PREVENIR DESERIALIZACIÓN (__wakeup)
    // ========================================================================
    // Previene que se cree una instancia mediante unserialize()
    // ========================================================================

    /**
     * Prevenir la deserialización del objeto
     * 
     * Este método previene que se cree una nueva instancia
     * mediante la deserialización de una cadena serializada.
     * 
     * Sin esto, alguien podría hacer:
     * $serialized = serialize($db1);
     * $db2 = unserialize($serialized); // ❌ Crearía segunda instancia
     * 
     * @throws Exception Lanza excepción si se intenta deserializar
     * @return void
     */
    public function __wakeup() {
        // ====================================================================
        // MÉTODO MÁGICO __wakeup()
        // ====================================================================
        // SINTAXIS:
        // public function __wakeup() {}
        // └─ __wakeup: Método mágico
        // └─ Se ejecuta al deserializar un objeto
        //
        // ¿QUÉ ES SERIALIZACIÓN?
        // serialize():   Convierte un objeto a string
        // unserialize(): Convierte string a objeto
        //
        // EJEMPLO:
        // $db = Database::getInstance();
        // $string = serialize($db);    // Convierte a string
        // $db2 = unserialize($string); // Reconstruye el objeto
        //
        // ¿POR QUÉ ES PELIGROSO PARA SINGLETON?
        // - unserialize() crea un NUEVO objeto
        // - Rompe la regla de "solo una instancia"
        //
        // ¿CÓMO LO PREVENIMOS?
        // - Lanzando una excepción en __wakeup()
        // - El proceso de deserialización falla
        //
        // NOTA: Es public, no private
        // - PHP no permite __wakeup() privado
        // - Pero lanza excepción si se usa
        // ====================================================================

        throw new Exception("No se puede deserializar un Singleton");
        // ================================================================
        // - Si alguien intenta unserialize()
        // - Este método se ejecuta
        // - Lanza excepción inmediatamente
        // - El proceso falla, no se crea segunda instancia
        // ================================================================
    }

    // ========================================================================
    // MÉTODO: OBTENER INSTANCIA ÚNICA (getInstance) - CORAZÓN DEL SINGLETON
    // ========================================================================
    // Este es el ÚNICO método público para acceder a la clase
    // ========================================================================

    /**
     * Método público para obtener la instancia única
     * 
     * Este es el ÚNICO punto de acceso a la clase Database.
     * Implementa el patrón Singleton garantizando una sola instancia.
     * 
     * USO:
     * $db = Database::getInstance();
     * $conn = $db->getConnection();
     * 
     * PATRÓN SINGLETON - FUNCIONAMIENTO:
     * 1. Primera llamada: Crea la instancia, la guarda, la devuelve
     * 2. Siguientes llamadas: Devuelve la instancia guardada
     * 
     * @return Database Instancia única de Database
     */
    public static function getInstance() {
        // ====================================================================
        // DECLARACIÓN DEL MÉTODO getInstance()
        // ====================================================================
        // public static function getInstance() {
        // └─ public: MODIFICADOR - Accesible desde cualquier lugar
        // └─ static: MODIFICADOR - Método de clase, no de instancia
        // └─ function: Palabra clave para métodos
        // └─ getInstance: Nombre del método (convención Singleton)
        // └─ (): Sin parámetros
        //
        // ¿POR QUÉ public?
        // - Es el ÚNICO punto de acceso público a la clase
        // - Debe ser llamado desde fuera
        //
        // ¿POR QUÉ static?
        // - Se llama SIN crear objeto primero
        // - Sintaxis: Database::getInstance()
        // - No es: $db->getInstance()
        //
        // DIFERENCIA static vs no static:
        // static:     Database::getInstance()  ← Llamada de clase
        // no static:  $objeto->getInstance()   ← Llamada de instancia
        //
        // ¿POR QUÉ SE LLAMA getInstance()?
        // - Convención del patrón Singleton
        // - Otros nombres comunes: get_instance(), instance()
        // - Comunica claramente su propósito
        // ====================================================================

        if (self::$instance === null) {
            // ================================================================
            // VERIFICAR SI YA EXISTE INSTANCIA
            // ================================================================
            // SINTAXIS:
            // if (self::$instance === null) {
            // └─ if: Condicional
            // └─ self::$instance: Acceso a propiedad estática
            // └─ ===: Operador de comparación estricta
            // └─ null: Valor "vacío"
            //
            // PALABRA CLAVE "self":
            // - Referencia a la clase actual
            // - Similar a $this, pero para elementos static
            // - Se usa con ::, no con ->
            //
            // DIFERENCIA self vs $this:
            // self::$instance     → Propiedad ESTÁTICA (de la clase)
            // $this->instance     → Propiedad de INSTANCIA (del objeto)
            //
            // OPERADOR === (comparación estricta):
            // - Compara valor Y tipo
            // - === null: Verifica que sea exactamente null
            //
            // DIFERENCIA == vs ===:
            // 0 == null    → true  (compara solo valor)
            // 0 === null   → false (compara valor Y tipo)
            // null === null → true
            //
            // ¿QUÉ VERIFICA ESTA CONDICIÓN?
            // - Si $instance es null: Nunca se creó la instancia
            // - Si $instance NO es null: Ya existe la instancia
            //
            // FLUJO:
            // Primera llamada:  $instance === null  → true  → Crear
            // Segunda llamada:  $instance === null  → false → No crear
            // ================================================================

            self::$instance = new self();
            // ================================================================
            // CREAR LA INSTANCIA ÚNICA
            // ================================================================
            // SINTAXIS:
            // self::$instance = new self();
            // └─ self::$instance: Guardar en propiedad estática
            // └─ =: Asignar
            // └─ new self(): Crear instancia de sí misma
            //
            // PALABRA CLAVE "new self()":
            // - self(): Referencia a la clase actual
            // - Equivalente a: new Database()
            // - Pero más flexible (funciona en herencia)
            //
            // ¿POR QUÉ new self() Y NO new Database()?
            // ✓ Más flexible si se extiende la clase
            // ✓ Evita hardcodear el nombre de la clase
            //
            // ¿QUÉ PASA AL EJECUTAR new self()?
            // 1. Se llama al constructor __construct()
            // 2. El constructor crea la conexión PDO
            // 3. Se guarda en $this->connection
            // 4. El objeto completo se guarda en self::$instance
            //
            // DESPUÉS DE ESTA LÍNEA:
            // - self::$instance contiene un objeto Database
            // - El objeto tiene la conexión PDO activa
            // - Ya no es null
            //
            // ¿PUEDE LLAMARSE SI __construct() ES private?
            // - SÍ, porque estamos DENTRO de la clase
            // - private solo impide acceso desde FUERA
            // - Métodos de la misma clase sí pueden acceder
            // ================================================================
        }

        return self::$instance;
        // ====================================================================
        // DEVOLVER LA INSTANCIA
        // ====================================================================
        // PALABRA CLAVE "return":
        // - Devuelve un valor al código que llamó al método
        // - Termina la ejecución del método
        //
        // ¿QUÉ DEVUELVE?
        // - El objeto Database guardado en self::$instance
        //
        // FLUJO COMPLETO DEL PATRÓN SINGLETON:
        // 
        // PRIMERA LLAMADA:
        // 1. getInstance() es llamado
        // 2. self::$instance es null (nunca creado)
        // 3. if (null === null) → true
        // 4. Se ejecuta: self::$instance = new self()
        // 5. Se crea el objeto y se guarda
        // 6. return devuelve el objeto recién creado
        //
        // SEGUNDA LLAMADA:
        // 1. getInstance() es llamado nuevamente
        // 2. self::$instance YA tiene un objeto (no es null)
        // 3. if (objeto === null) → false
        // 4. NO se ejecuta el if{}, se salta
        // 5. return devuelve el objeto EXISTENTE
        //
        // RESULTADO:
        // ✓ Ambas llamadas devuelven el MISMO objeto
        // ✓ Solo se crea UNA conexión a la base de datos
        // ✓ Todos usan la misma conexión
        //
        // PRUEBA EN CÓDIGO:
        // $db1 = Database::getInstance();
        // $db2 = Database::getInstance();
        // var_dump($db1 === $db2); // true - ¡Son el mismo objeto!
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: OBTENER CONEXIÓN PDO
    // ========================================================================
    // Devuelve el objeto PDO para hacer consultas
    // ========================================================================

    /**
     * Obtener la conexión PDO
     * 
     * Devuelve el objeto PDO que se puede usar para ejecutar
     * consultas SQL. Este método debe usarse después de getInstance().
     * 
     * USO:
     * $db = Database::getInstance();
     * $pdo = $db->getConnection();
     * $stmt = $pdo->prepare("SELECT * FROM usuarios");
     * 
     * @return PDO Objeto de conexión a la base de datos
     */
    public function getConnection() {
        // ====================================================================
        // MÉTODO GETTER (getConnection)
        // ====================================================================
        // public function getConnection() {
        // └─ public: Accesible desde fuera
        // └─ NO es static: Se llama en la instancia
        // └─ getConnection: Nombre descriptivo (get = obtener)
        //
        // ¿POR QUÉ NO ES static?
        // - Necesita acceder a $this->connection
        // - Solo funciona en un objeto ya creado
        //
        // USO:
        // $db = Database::getInstance();      ← static
        // $conn = $db->getConnection();       ← no static
        //
        // PATRÓN GETTER:
        // - Método que devuelve el valor de una propiedad privada
        // - Permite acceso controlado a datos internos
        // - Convención: get + NombrePropiedad
        // ====================================================================

        return $this->connection;
        // ====================================================================
        // DEVOLVER LA CONEXIÓN PDO
        // ====================================================================
        // return $this->connection;
        // └─ return: Devolver valor
        // └─ $this->connection: Propiedad del objeto actual
        //
        // ¿QUÉ DEVUELVE?
        // - El objeto PDO creado en el constructor
        // - Ya está conectado y configurado
        // - Listo para hacer consultas
        //
        // USO DEL VALOR RETORNADO:
        // $pdo = $db->getConnection();
        // $pdo->prepare("SELECT ...");  ← Método de PDO
        // $pdo->query("INSERT ...");    ← Método de PDO
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: EJECUTAR CONSULTAS PREPARADAS (query)
    // ========================================================================
    // Método auxiliar para simplificar consultas SQL
    // ========================================================================

    /**
     * Método auxiliar para ejecutar consultas preparadas
     * 
     * Este método simplifica la ejecución de consultas SQL
     * usando prepared statements para mayor seguridad.
     * 
     * USO:
     * $db = Database::getInstance();
     * 
     * // Con parámetros
     * $stmt = $db->query(
     *     "SELECT * FROM usuarios WHERE id = ?",
     *     [5]
     * );
     * 
     * // Sin parámetros
     * $stmt = $db->query("SELECT * FROM usuarios");
     * 
     * // Obtener resultados
     * $usuarios = $stmt->fetchAll();
     * 
     * @param string $sql Consulta SQL con placeholders (?)
     * @param array $params Parámetros para los placeholders (opcional)
     * @return PDOStatement Objeto con resultados de la consulta
     * @throws Exception Si hay error en la consulta
     */
    public function query($sql, $params = []) {
        // ====================================================================
        // DECLARACIÓN CON PARÁMETROS
        // ====================================================================
        // public function query($sql, $params = []) {
        // └─ $sql: Primer parámetro (obligatorio)
        // └─ $params: Segundo parámetro (opcional)
        // └─ = []: Valor por defecto (array vacío)
        //
        // PARÁMETROS:
        // $sql:
        // - Tipo: string
        // - Contiene la consulta SQL
        // - Puede tener placeholders (?)
        //
        // $params:
        // - Tipo: array
        // - Valores para reemplazar los ?
        // - Por defecto: [] (array vacío)
        //
        // VALOR POR DEFECTO:
        // - Si no se pasa $params, usa []
        // - Permite llamar: query($sql) sin segundo parámetro
        //
        // EJEMPLO DE LLAMADAS:
        // Con parámetros:  query("SELECT * WHERE id = ?", [5])
        // Sin parámetros:  query("SELECT * FROM tabla")
        // ====================================================================

        try {
            $stmt = $this->connection->prepare($sql);
            // ================================================================
            // PREPARAR LA CONSULTA
            // ================================================================
            // $stmt = $this->connection->prepare($sql);
            // └─ $this->connection: Objeto PDO
            // └─ ->prepare(): Método de PDO
            // └─ $sql: Consulta a preparar
            // └─ $stmt: PDOStatement (resultado)
            //
            // ¿QUÉ ES prepare()?
            // - Método de PDO
            // - Prepara una consulta SQL con placeholders
            // - NO la ejecuta todavía
            // - Devuelve un objeto PDOStatement
            //
            // ¿QUÉ ES UN PLACEHOLDER?
            // - Símbolo ? en la consulta
            // - Se reemplaza con valores seguros
            // - Previene SQL Injection
            //
            // EJEMPLO:
            // SQL: "SELECT * FROM usuarios WHERE id = ?"
            // Params: [5]
            // Resultado: SELECT * FROM usuarios WHERE id = 5
            //
            // SEGURIDAD:
            // ❌ INSEGURO: "SELECT * WHERE id = " . $_GET['id']
            // ✓ SEGURO: prepare("SELECT * WHERE id = ?") + [5]
            // ================================================================

            $stmt->execute($params);
            // ================================================================
            // EJECUTAR LA CONSULTA
            // ================================================================
            // $stmt->execute($params);
            // └─ $stmt: Objeto PDOStatement
            // └─ ->execute(): Método que ejecuta la consulta
            // └─ $params: Array con valores para los ?
            //
            // ¿QUÉ HACE execute()?
            // - Reemplaza los ? con los valores de $params
            // - Ejecuta la consulta en el servidor
            // - Devuelve true si tuvo éxito
            //
            // REEMPLAZO DE PLACEHOLDERS:
            // SQL:    "SELECT * WHERE id = ? AND activo = ?"
            // Params: [5, 1]
            // Result: SELECT * WHERE id = 5 AND activo = 1
            //
            // ORDEN IMPORTANTE:
            // - El primer ? se reemplaza con $params[0]
            // - El segundo ? se reemplaza con $params[1]
            // - Y así sucesivamente
            //
            // SI $params ESTÁ VACÍO:
            // - No hay reemplazos
            // - La consulta se ejecuta tal cual
            // - Útil para: SELECT * FROM tabla (sin WHERE)
            // ================================================================

            return $stmt;
            // ================================================================
            // DEVOLVER EL PDOStatement
            // ================================================================
            // ¿QUÉ ES PDOStatement?
            // - Objeto que contiene los resultados
            // - Tiene métodos para obtener datos:
            //   · fetch()     → Un registro
            //   · fetchAll()  → Todos los registros
            //   · rowCount()  → Cantidad de filas afectadas
            //
            // USO DEL VALOR RETORNADO:
            // $stmt = $db->query("SELECT * FROM usuarios");
            // $usuarios = $stmt->fetchAll(); ← Obtener resultados
            // ================================================================

        } catch (PDOException $e) {
            // Registrar error sin exponer detalles
            error_log("Error en query: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta");
        }
    }

    // ========================================================================
    // MÉTODO: OBTENER ÚLTIMO ID INSERTADO
    // ========================================================================
    // Útil después de INSERT para saber el ID generado
    // ========================================================================

    /**
     * Obtener el ID del último registro insertado
     * 
     * Este método es útil después de un INSERT para obtener
     * el ID auto-generado del nuevo registro.
     * 
     * USO:
     * $db = Database::getInstance();
     * $db->query("INSERT INTO usuarios (nombre) VALUES (?)", ['Juan']);
     * $nuevoId = $db->lastInsertId();
     * echo "Usuario creado con ID: " . $nuevoId;
     * 
     * @return string ID del último registro insertado
     */
    public function lastInsertId() {
        // ====================================================================
        // MÉTODO lastInsertId()
        // ====================================================================
        // ¿PARA QUÉ SIRVE?
        // - Obtiene el último ID generado por AUTO_INCREMENT
        // - Solo funciona después de un INSERT
        //
        // EJEMPLO:
        // Tabla usuarios: id (AUTO_INCREMENT), nombre
        // INSERT: "INSERT INTO usuarios (nombre) VALUES ('Juan')"
        // MySQL asigna: id = 5 (automático)
        // lastInsertId(): Devuelve "5"
        //
        // ¿POR QUÉ DEVUELVE STRING?
        // - PDO lo devuelve como string
        // - Se puede convertir a int: (int)$db->lastInsertId()
        // ====================================================================

        return $this->connection->lastInsertId();
        // ====================================================================
        // - Llama al método lastInsertId() de PDO
        // - Devuelve el ID del último INSERT
        // ====================================================================
    }

    // ========================================================================
    // MÉTODOS: TRANSACCIONES
    // ========================================================================
    // Las transacciones permiten agrupar varias consultas como una unidad
    // Si una falla, se revierten todas (atomicidad)
    // ========================================================================

    /**
     * Iniciar una transacción
     * 
     * Las transacciones permiten ejecutar múltiples consultas
     * como una unidad atómica. Si una falla, todas se revierten.
     * 
     * USO:
     * $db = Database::getInstance();
     * $db->beginTransaction();
     * try {
     *     $db->query("INSERT INTO usuarios ...");
     *     $db->query("UPDATE cuentas ...");
     *     $db->commit(); // Confirmar cambios
     * } catch (Exception $e) {
     *     $db->rollback(); // Revertir cambios
     * }
     * 
     * @return bool true si se inició correctamente
     */
    public function beginTransaction() {
        // ====================================================================
        // ¿QUÉ ES UNA TRANSACCIÓN?
        // - Agrupa varias consultas SQL
        // - Se ejecutan todas o ninguna (atomicidad)
        //
        // PROPIEDADES ACID:
        // A = Atomicidad  → Todo o nada
        // C = Consistencia → Estado válido siempre
        // I = Isolation   → Transacciones independientes
        // D = Durability  → Cambios permanentes
        //
        // EJEMPLO DE USO:
        // Transferencia bancaria:
        // 1. Restar $100 de cuenta A
        // 2. Sumar $100 a cuenta B
        // Si 2 falla, 1 debe revertirse (rollback)
        // ====================================================================

        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar una transacción
     * 
     * Confirma todos los cambios realizados desde beginTransaction().
     * Los cambios se vuelven permanentes en la base de datos.
     * 
     * @return bool true si se confirmó correctamente
     */
    public function commit() {
        // ====================================================================
        // CONFIRMAR CAMBIOS
        // - Hace permanentes las consultas de la transacción
        // - Se guardan en la base de datos
        // ====================================================================

        return $this->connection->commit();
    }

    /**
     * Revertir una transacción
     * 
     * Revierte todos los cambios realizados desde beginTransaction().
     * La base de datos vuelve al estado anterior a la transacción.
     * 
     * @return bool true si se revirtió correctamente
     */
    public function rollback() {
        // ====================================================================
        // REVERTIR CAMBIOS
        // - Deshace las consultas de la transacción
        // - La BD vuelve al estado anterior
        // - Útil cuando hay errores
        // ====================================================================

        return $this->connection->rollback();
    }
}

// ============================================================================
// FIN DE LA CLASE Database
// ============================================================================
//
// RESUMEN DEL PATRÓN SINGLETON IMPLEMENTADO:
//
// 1. Constructor privado __construct()
//    └─ Evita: new Database()
//    └─ Solo getInstance() puede crear instancias
//
// 2. Propiedad estática $instance
//    └─ Guarda la única instancia
//    └─ Compartida por todos
//
// 3. Método estático getInstance()
//    └─ Único punto de acceso
//    └─ Crea instancia si no existe
//    └─ Devuelve instancia existente
//
// 4. Prevenciones adicionales:
//    └─ __clone() privado: Evita clonación
//    └─ __wakeup() con excepción: Evita deserialización
//
// BENEFICIOS CONSEGUIDOS:
// ✓ Solo una conexión a BD (ahorra recursos)
// ✓ Punto de acceso global consistente
// ✓ Configuración centralizada
// ✓ Protección contra SQL Injection (prepared statements)
// ✓ Manejo elegante de errores
//
// PRINCIPIOS SOLID CUMPLIDOS:
// ✓ SRP: Solo maneja conexión, no lógica de negocio
// ✓ DIP: Otros objetos dependen de esta interfaz
//
// USO TÍPICO EN LA APLICACIÓN:
// $db = Database::getInstance();
// $stmt = $db->query("SELECT * FROM usuarios WHERE id = ?", [5]);
// $usuario = $stmt->fetch();
//
// ============================================================================
