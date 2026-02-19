// ============================================================================
// ARCHIVO: UserValidator.js
// UBICACIÓN: js/UserValidator.js
// PROPÓSITO: Validación de datos del lado del cliente (Frontend)
// ============================================================================
//
// DESCRIPCIÓN GENERAL:
// Este archivo implementa una clase para validar datos de usuario en el
// navegador ANTES de enviarlos al servidor. Proporciona feedback inmediato
// al usuario sin necesidad de hacer peticiones HTTP.
//
// TECNOLOGÍAS Y CONCEPTOS:
// ============================================================================
// 1. CLASES EN JAVASCRIPT (ES6+)
//    - class (declaración de clase)
//    - constructor (inicialización)
//    - this (referencia al objeto actual)
//    - Métodos de instancia
//
// 2. EXPRESIONES REGULARES (RegEx)
//    - Patrones para validar formato
//    - test() para verificar coincidencia
//    - Caracteres especiales y grupos
//
// 3. VALIDACIÓN DEL LADO DEL CLIENTE
//    - Mejora UX (experiencia de usuario)
//    - Feedback inmediato
//    - Reduce carga del servidor
//
// 4. MANEJO DE ERRORES
//    - Array de errores
//    - Mensajes descriptivos
//    - Acumulación de errores
//
// PATRÓN DE DISEÑO IMPLEMENTADO:
// ============================================================================
// STRATEGY PATTERN (Patrón Estrategia)
//
// ¿QUÉ ES?
// - Define familia de algoritmos (estrategias de validación)
// - Los encapsula (cada método valida de forma diferente)
// - Los hace intercambiables (puedes usar uno u otro)
//
// ESTRATEGIAS EN ESTE ARCHIVO:
// 1. validateEmail()  → Estrategia para validar emails
// 2. validateNombre() → Estrategia para validar nombres
//
// ANALOGÍA:
// Es como tener diferentes herramientas para diferentes trabajos:
// - Martillo para clavos (validateNombre para nombres)
// - Destornillador para tornillos (validateEmail para emails)
// Cada uno tiene su estrategia específica.
//
// BENEFICIOS:
// ✓ Open/Closed Principle: Fácil agregar nuevas validaciones sin modificar existentes
// ✓ Single Responsibility: Cada método valida una cosa específica
// ✓ Reusabilidad: Se puede usar en múltiples formularios
//
// FLUJO DE VALIDACIÓN:
// ============================================================================
// 1. Usuario llena campo del formulario
// 2. JavaScript crea instancia: const validator = new UserValidator()
// 3. Llama método específico: validator.validateNombre("Juan")
// 4. Método valida según sus reglas
// 5. Si válido: return true
// 6. Si inválido: guarda error en this.errors, return false
// 7. UI muestra errores: validator.getErrors()
//
// VALIDACIÓN EN DOS NIVELES:
// ============================================================================
// CLIENTE (este archivo):
// ✓ Feedback inmediato al usuario
// ✓ Mejor experiencia de usuario
// ✓ Reduce peticiones innecesarias al servidor
//
// SERVIDOR (backend PHP):
// ✓ Seguridad (nunca confiar solo en cliente)
// ✓ Validación definitiva
// ✓ Protección contra manipulación
//
// ⚠️ IMPORTANTE: NUNCA confiar solo en validación del cliente
// Un usuario malicioso puede desactivar JavaScript o modificar el código.
// El servidor SIEMPRE debe validar.
//
// ============================================================================

/**
 * ============================================================================
 * CLASE: UserValidator
 * ============================================================================
 * PROPÓSITO:
 * - Validar datos de usuario en el cliente
 * - Proporcionar mensajes de error descriptivos
 * - Mejorar experiencia de usuario con feedback inmediato
 * 
 * PATRÓN: Strategy (diferentes estrategias de validación)
 * PRINCIPIO SOLID: Single Responsibility (solo valida, no hace otra cosa)
 * 
 * USO:
 * const validator = new UserValidator();
 * if (!validator.validateNombre("Juan")) {
 *     console.log(validator.getErrors()); // ["El nombre debe tener al menos 3 caracteres"]
 * }
 * 
 * ============================================================================
 */
class UserValidator {
    // ========================================================================
    // DECLARACIÓN DE CLASE
    // ========================================================================
    // SINTAXIS:
    // class NombreClase {
    // └─ class: Palabra clave para declarar clase
    // └─ NombreClase: Nombre en PascalCase (primera letra mayúscula)
    // └─ {}: Llaves que encierran el contenido
    //
    // ¿QUÉ ES UNA CLASE?
    // - Es un "molde" o "plantilla" para crear objetos
    // - Define propiedades (variables) y métodos (funciones)
    // - Los objetos creados con la clase son "instancias"
    //
    // ANALOGÍA:
    // - Clase = Plano de una casa
    // - Objeto = Casa construida con ese plano
    // - Puedes construir muchas casas (objetos) con el mismo plano (clase)
    //
    // DIFERENCIA CON FUNCIÓN:
    // Función:  function validar() {...}
    // Clase:    class Validator {...}
    //
    // VENTAJAS DE CLASES:
    // ✓ Organización del código
    // ✓ Encapsulación (datos y métodos juntos)
    // ✓ Reutilización
    // ✓ Más fácil de mantener
    //
    // CONVENCIÓN DE NOMBRES:
    // - Clases: PascalCase (UserValidator, DatabaseManager)
    // - Variables: camelCase (userName, emailAddress)
    // - Constantes: SCREAMING_SNAKE_CASE (API_URL, MAX_LENGTH)
    // ========================================================================

    constructor() {
        // ====================================================================
        // MÉTODO CONSTRUCTOR
        // ====================================================================
        // SINTAXIS:
        // constructor() {...}
        // └─ constructor: Nombre especial (palabra clave)
        // └─ (): Paréntesis para parámetros (vacío = sin parámetros)
        // └─ {}: Llaves con código de inicialización
        //
        // ¿QUÉ ES EL CONSTRUCTOR?
        // - Método especial que se ejecuta automáticamente al crear objeto
        // - Se usa para inicializar propiedades
        // - Solo puede haber UN constructor por clase
        //
        // ¿CUÁNDO SE EJECUTA?
        // - Cuando haces: new UserValidator()
        // - Automáticamente, no lo llamas manualmente
        //
        // EJEMPLO:
        // const validator = new UserValidator(); ← Aquí se ejecuta constructor()
        //
        // COMPARACIÓN CON PHP:
        // PHP:        public function __construct() {...}
        // JavaScript: constructor() {...}
        //
        // ¿POR QUÉ NO TIENE PARÁMETROS AQUÍ?
        // - No necesitamos configuración inicial
        // - Solo inicializamos el array de errores vacío
        //
        // CON PARÁMETROS:
        // constructor(maxLength) {
        //     this.maxLength = maxLength;
        // }
        // Uso: const validator = new UserValidator(100);
        // ====================================================================

        this.errors = [];
        // ====================================================================
        // INICIALIZAR PROPIEDAD errors
        // ====================================================================
        // SINTAXIS:
        // this.errors = [];
        // └─ this: Referencia al objeto actual
        // └─ .errors: Nombre de la propiedad
        // └─ =: Operador de asignación
        // └─ []: Array vacío
        //
        // ¿QUÉ ES this?
        // - Palabra clave que referencia al objeto actual
        // - "Este objeto", "yo mismo"
        // - Permite acceder a propiedades y métodos de la instancia
        //
        // ANALOGÍA:
        // this es como decir "mi" o "mío"
        // - this.errors = "mi lista de errores"
        // - this.name = "mi nombre"
        //
        // DIFERENCIA CON VARIABLES NORMALES:
        // let errors = [];        → Variable local (solo en constructor)
        // this.errors = [];       → Propiedad del objeto (accesible en todos los métodos)
        //
        // ¿QUÉ ES UN ARRAY?
        // - Lista ordenada de elementos
        // - Puede contener strings, números, objetos, etc.
        // - Se accede por índice: errors[0], errors[1]
        //
        // ¿PARA QUÉ SIRVE this.errors?
        // - Guardar mensajes de error de validación
        // - Se va llenando cuando hay errores
        // - Se lee para mostrar al usuario
        //
        // EJEMPLO DE USO:
        // Inicio:    this.errors = []
        // Error 1:   this.errors = ["Email inválido"]
        // Error 2:   this.errors = ["Email inválido", "Nombre muy corto"]
        //
        // ACCESO DESDE OTROS MÉTODOS:
        // validateEmail() {
        //     this.errors.push("Error"); ← Usa this.errors
        // }
        // getErrors() {
        //     return this.errors; ← Accede a this.errors
        // }
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: validateEmail()
    // ========================================================================
    // ESTRATEGIA: Validación de direcciones de correo electrónico
    // RESPONSABILIDAD: Verificar formato y longitud del email
    // ========================================================================

    /**
     * Validar dirección de correo electrónico
     * 
     * REGLAS DE VALIDACIÓN:
     * 1. No puede estar vacío
     * 2. Máximo 150 caracteres
     * 3. Debe tener formato válido: usuario@dominio.extension
     * 
     * EJEMPLOS:
     * ✓ Válidos:   "juan@gmail.com", "maria.garcia@empresa.co"
     * ✗ Inválidos: "", "juan", "juan@", "@gmail.com", "juan @gmail.com"
     * 
     * @param {string} email - Email a validar
     * @returns {boolean} true si válido, false si inválido
     */
    validateEmail(email) {
        // ====================================================================
        // DECLARACIÓN DEL MÉTODO
        // ====================================================================
        // SINTAXIS:
        // validateEmail(email) {...}
        // └─ validateEmail: Nombre del método (camelCase)
        // └─ email: Parámetro de entrada
        // └─ {...}: Código del método
        //
        // ¿QUÉ ES UN MÉTODO?
        // - Función que pertenece a una clase
        // - Se llama en una instancia: validator.validateEmail("email")
        //
        // DIFERENCIA FUNCIÓN vs MÉTODO:
        // Función:  function validar() {...}  → Independiente
        // Método:   validateEmail() {...}     → Parte de una clase
        //
        // PARÁMETRO email:
        // - Valor que se pasa al método
        // - Tipo: string (cadena de texto)
        // - Ejemplo: validateEmail("juan@gmail.com")
        //
        // TIPO DE RETORNO:
        // - boolean (true o false)
        // - true: Email válido
        // - false: Email inválido
        // ====================================================================

        this.errors = [];
        // ====================================================================
        // LIMPIAR ERRORES PREVIOS
        // ====================================================================
        // ¿Por qué?
        // - Cada validación debe empezar limpia
        // - Evita acumular errores de validaciones anteriores
        //
        // EJEMPLO:
        // Primera llamada:  validateEmail("invalido")  → errors = ["Error 1"]
        // Segunda llamada:  validateEmail("otro")      → errors = [] (limpio)
        //                                                → errors = ["Error 2"]
        //
        // SIN LIMPIAR:
        // Primera:  errors = ["Error 1"]
        // Segunda:  errors = ["Error 1", "Error 2"] ← Acumula errores viejos
        // ====================================================================

        // Validación 1: Campo obligatorio
        if (!email || email.trim() === '') {
            // ================================================================
            // VERIFICAR SI ESTÁ VACÍO
            // ================================================================
            // SINTAXIS:
            // if (!email || email.trim() === '') {...}
            // └─ !email: Negación (NOT)
            // └─ ||: Operador OR (O lógico)
            // └─ email.trim(): Eliminar espacios
            // └─ === '': Comparación estricta con string vacío
            //
            // ¿QUÉ VERIFICA?
            // - !email: Si email es falsy (null, undefined, "")
            // - ||: O (si cualquiera es true, entra al if)
            // - email.trim() === '': Si después de quitar espacios está vacío
            //
            // OPERADOR ! (NOT):
            // !true   → false
            // !false  → true
            // !""     → true (string vacío es falsy)
            // !"hola" → false (string con contenido es truthy)
            //
            // VALORES FALSY:
            // "", 0, null, undefined, false, NaN
            //
            // CASOS QUE DETECTA:
            // !email:
            // - email = undefined ← No se pasó parámetro
            // - email = null      ← Se pasó null explícitamente
            // - email = ""        ← String vacío
            //
            // email.trim() === '':
            // - email = "   "     ← Solo espacios
            // - email = "\t\n"   ← Solo tabs y saltos de línea
            //
            // ¿QUÉ ES .trim()?
            // - Método de strings
            // - Elimina espacios al inicio y fin
            // - Ejemplo: "  hola  ".trim() → "hola"
            //
            // ¿POR QUÉ AMBAS CONDICIONES?
            // - !email: Detecta null/undefined/""
            // - trim() === '': Detecta "   " (solo espacios)
            //
            // EJEMPLO DE EJECUCIÓN:
            // email = undefined  → !email es true  → entra al if
            // email = ""         → !email es true  → entra al if
            // email = "   "      → !email es false, pero trim() === '' es true → entra
            // email = "a@b.c"    → Ambas false → NO entra al if
            // ================================================================

            this.errors.push('El email es obligatorio');
            // ================================================================
            // AGREGAR MENSAJE DE ERROR
            // ================================================================
            // SINTAXIS:
            // this.errors.push(elemento);
            // └─ this.errors: Array de errores
            // └─ .push(): Método que agrega elemento al final del array
            // └─ 'El email es obligatorio': String a agregar
            //
            // ¿QUÉ HACE .push()?
            // - Agrega elemento al final del array
            // - Modifica el array original
            // - Devuelve la nueva longitud del array (no se usa aquí)
            //
            // EJEMPLO:
            // Antes:  this.errors = []
            // push:   this.errors.push("Error 1")
            // Después: this.errors = ["Error 1"]
            //
            // AGREGAR MÚLTIPLES:
            // this.errors.push("Error 1");
            // this.errors.push("Error 2");
            // Resultado: ["Error 1", "Error 2"]
            //
            // ALTERNATIVAS:
            // this.errors[0] = "Error";     ← Por índice (sobrescribe)
            // this.errors = [...errors, x]; ← Spread (crea nuevo array)
            //
            // ¿POR QUÉ push()?
            // ✓ Simple y directo
            // ✓ Modifica in-place (eficiente)
            // ✓ Muy legible
            // ================================================================

            return false;
            // ================================================================
            // RETORNAR false (VALIDACIÓN FALLÓ)
            // ================================================================
            // SINTAXIS:
            // return false;
            // └─ return: Palabra clave para devolver valor
            // └─ false: Valor booleano (validación falló)
            //
            // ¿QUÉ HACE return?
            // - Termina la ejecución del método inmediatamente
            // - Devuelve el valor al código que llamó al método
            //
            // FLUJO:
            // 1. Se detecta error
            // 2. Se agrega mensaje a this.errors
            // 3. return false devuelve false
            // 4. Método termina (no ejecuta código siguiente)
            //
            // USO:
            // if (!validator.validateEmail("")) {
            //     // Entra aquí porque devolvió false
            //     console.log(validator.getErrors()); // ["El email es obligatorio"]
            // }
            //
            // ¿POR QUÉ TERMINAR AQUÍ?
            // - Si está vacío, no tiene sentido validar formato
            // - Evita validaciones innecesarias
            // - Evita errores (no puedes hacer .trim() en undefined)
            //
            // PATRÓN: Early Return (Retorno Temprano)
            // - Verifica condiciones básicas primero
            // - Si fallan, retorna inmediatamente
            // - Evita anidación excesiva de if's
            // ================================================================
        }

        // Si llegamos aquí, email no está vacío
        email = email.trim();
        // ====================================================================
        // NORMALIZAR EMAIL (Eliminar espacios)
        // ====================================================================
        // ¿Por qué trim() de nuevo?
        // - La validación anterior verificó, pero no modificó email
        // - Ahora guardamos la versión sin espacios
        // - Usamos esta versión limpia para las siguientes validaciones
        //
        // EJEMPLO:
        // email = "  juan@gmail.com  "
        // email = email.trim()
        // email = "juan@gmail.com"
        //
        // BUENA PRÁCTICA:
        // - Siempre normalizar datos antes de validar
        // - Eliminar espacios, convertir a minúsculas, etc.
        // ====================================================================

        // Validación 2: Longitud máxima
        if (email.length > 150) {
            // ================================================================
            // VERIFICAR LONGITUD MÁXIMA
            // ================================================================
            // SINTAXIS:
            // if (email.length > 150) {...}
            // └─ email.length: Propiedad con longitud del string
            // └─ >: Operador mayor que
            // └─ 150: Límite máximo
            //
            // ¿QUÉ ES .length?
            // - Propiedad de strings y arrays
            // - Devuelve número de caracteres (en strings)
            // - Devuelve número de elementos (en arrays)
            //
            // EJEMPLO:
            // "hola".length      → 4
            // "".length          → 0
            // "email@e.com".length → 12
            //
            // ¿POR QUÉ 150 CARACTERES?
            // - Límite definido en la base de datos
            // - Campo VARCHAR(150) en MySQL
            // - Evita errores al guardar
            //
            // ¿QUÉ PASA SI ES MÁS LARGO?
            // - Base de datos truncaría (cortaría) el texto
            // - O daría error (depende de configuración)
            // - Mejor validar antes
            //
            // OPERADORES DE COMPARACIÓN:
            // >   Mayor que
            // <   Menor que
            // >=  Mayor o igual
            // <=  Menor o igual
            // === Igual (estricto)
            // !== Diferente (estricto)
            // ================================================================

            this.errors.push('El email es demasiado largo (máx 150 caracteres)');
            return false;
        }

        // Validación 3: Formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        // ====================================================================
        // EXPRESIÓN REGULAR (RegEx) PARA VALIDAR FORMATO DE EMAIL
        // ====================================================================
        // SINTAXIS:
        // const emailRegex = /patrón/;
        // └─ /: Delimitadores de regex
        // └─ patrón: Expresión regular
        //
        // ¿QUÉ ES UNA EXPRESIÓN REGULAR?
        // - Patrón para buscar o validar texto
        // - Muy poderosa pero compleja
        // - Se usa para validar formatos
        //
        // DESGLOSE DEL PATRÓN:
        // /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        //
        // ^           → Inicio del string
        // [^\s@]+     → Uno o más caracteres que NO sean espacio ni @
        // @           → Literal @ (arroba)
        // [^\s@]+     → Uno o más caracteres que NO sean espacio ni @
        // \.          → Literal . (punto)
        // [^\s@]+     → Uno o más caracteres que NO sean espacio ni @
        // $           → Fin del string
        //
        // EXPLICACIÓN DETALLADA:
        //
        // [^\s@]:
        // - []: Conjunto de caracteres
        // - ^: NOT (negación dentro de [])
        // - \s: Espacio en blanco (space, tab, newline)
        // - @: Arroba literal
        // - Significa: "cualquier carácter EXCEPTO espacio y @"
        //
        // +:
        // - Uno o más del elemento anterior
        // - [^\s@]+ significa "uno o más caracteres que no sean espacio ni @"
        //
        // @:
        // - Arroba literal (debe aparecer exactamente)
        //
        // \.:
        // - Punto literal
        // - \ escapa el punto (. en regex significa "cualquier carácter")
        // - \. significa un punto literal
        //
        // EJEMPLOS DE COINCIDENCIA:
        //
        // ✓ VÁLIDOS:
        // "juan@gmail.com"        → Coincide
        // "maria.garcia@empresa.co" → Coincide
        // "user123@test.io"       → Coincide
        //
        // ✗ INVÁLIDOS:
        // "juan"                  → Falta @ y dominio
        // "juan@"                 → Falta dominio
        // "@gmail.com"            → Falta usuario
        // "juan @gmail.com"       → Tiene espacio
        // "juan@@gmail.com"       → Doble @
        // "juan@gmail"            → Falta extensión (.com, .net, etc.)
        //
        // ¿ES PERFECTA ESTA REGEX?
        // - NO, es una validación básica
        // - No valida todas las reglas RFC de emails
        // - Pero es suficiente para la mayoría de casos
        //
        // REGEX PERFECTA:
        // - Sería extremadamente compleja
        // - Esta versión es un balance entre simplicidad y efectividad
        //
        // ELEMENTOS COMUNES DE REGEX:
        // ^      → Inicio de string
        // $      → Fin de string
        // .      → Cualquier carácter
        // \.     → Punto literal
        // *      → Cero o más
        // +      → Uno o más
        // ?      → Cero o uno (opcional)
        // []     → Conjunto de caracteres
        // [^]    → Negación (cualquiera EXCEPTO estos)
        // \s     → Espacio en blanco
        // \d     → Dígito (0-9)
        // \w     → Palabra (a-z, A-Z, 0-9, _)
        // |      → OR (o)
        // ()     → Grupo
        // ====================================================================

        if (!emailRegex.test(email)) {
            // ================================================================
            // PROBAR EMAIL CONTRA LA REGEX
            // ================================================================
            // SINTAXIS:
            // if (!emailRegex.test(email)) {...}
            // └─ emailRegex: Objeto RegExp
            // └─ .test(): Método que verifica coincidencia
            // └─ email: String a verificar
            // └─ !: Negación
            //
            // ¿QUÉ ES .test()?
            // - Método de expresiones regulares
            // - Verifica si el string coincide con el patrón
            // - Devuelve true si coincide, false si no
            //
            // EJEMPLO:
            // const regex = /^\d+$/;  // Solo dígitos
            // regex.test("123")      → true
            // regex.test("abc")      → false
            // regex.test("12a3")     → false
            //
            // CON NUESTRO CASO:
            // emailRegex.test("juan@gmail.com")  → true (formato válido)
            // emailRegex.test("juan")            → false (sin @)
            // emailRegex.test("juan @gmail.com") → false (tiene espacio)
            //
            // !emailRegex.test(email):
            // - ! invierte el resultado
            // - Si test() devuelve false (inválido), ! lo hace true
            // - Entonces entra al if
            //
            // LÓGICA:
            // test() devuelve true  → ! hace false → NO entra al if (válido)
            // test() devuelve false → ! hace true  → SÍ entra al if (inválido)
            //
            // ALTERNATIVA MÁS EXPLÍCITA:
            // if (emailRegex.test(email) === false) {...}
            // Pero !emailRegex.test(email) es más conciso y común
            // ================================================================

            this.errors.push('El formato del email no es válido');
            return false;
        }

        // Si llegamos aquí, todas las validaciones pasaron
        return true;
        // ====================================================================
        // RETORNAR true (VALIDACIÓN EXITOSA)
        // ====================================================================
        // Si llegamos a esta línea:
        // ✓ Email no está vacío
        // ✓ Email no supera 150 caracteres
        // ✓ Email tiene formato válido
        //
        // Por lo tanto, el email es válido
        //
        // USO:
        // if (validator.validateEmail("juan@gmail.com")) {
        //     // Entra aquí, email válido
        //     console.log("Email OK");
        // }
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: validateNombre()
    // ========================================================================
    // ESTRATEGIA: Validación de nombres de personas
    // RESPONSABILIDAD: Verificar longitud y caracteres permitidos
    // ========================================================================

    /**
     * Validar nombre de usuario
     * 
     * REGLAS DE VALIDACIÓN:
     * 1. No puede estar vacío
     * 2. Mínimo 3 caracteres
     * 3. Máximo 100 caracteres
     * 4. Solo letras (incluyendo acentos y ñ) y espacios
     * 
     * EJEMPLOS:
     * ✓ Válidos:   "Juan Pérez", "María José", "José Ángel"
     * ✗ Inválidos: "", "Ab", "Juan123", "Juan@Pérez"
     * 
     * @param {string} nombre - Nombre a validar
     * @returns {boolean} true si válido, false si inválido
     */
    validateNombre(nombre) {
        this.errors = [];

        // Validación 1: Campo obligatorio
        if (!nombre || nombre.trim() === '') {
            this.errors.push('El nombre es obligatorio');
            return false;
        }

        nombre = nombre.trim();

        // Validación 2: Longitud mínima
        if (nombre.length < 3) {
            // ================================================================
            // VERIFICAR LONGITUD MÍNIMA
            // ================================================================
            // ¿Por qué 3 caracteres?
            // - Evita nombres muy cortos como "Jo", "Al"
            // - Aunque son nombres válidos, queremos más específicos
            // - Es una regla de negocio (puede variar)
            //
            // NOMBRES RECHAZADOS:
            // "Ab" → 2 caracteres → Rechazado
            // "A"  → 1 carácter  → Rechazado
            // ""   → 0 caracteres → Ya rechazado antes
            //
            // NOMBRES ACEPTADOS:
            // "Ana"   → 3 caracteres → Aceptado
            // "Juan"  → 4 caracteres → Aceptado
            // ================================================================

            this.errors.push('El nombre debe tener al menos 3 caracteres');
            return false;
        }

        // Validación 3: Longitud máxima
        if (nombre.length > 100) {
            // ================================================================
            // VERIFICAR LONGITUD MÁXIMA
            // ================================================================
            // ¿Por qué 100 caracteres?
            // - Límite de la base de datos (VARCHAR(100))
            // - 100 caracteres es suficiente para nombres completos
            // - Previene intentos de ingresar texto muy largo
            //
            // EJEMPLOS:
            // "Juan Carlos de la Cruz Martínez García"  → ~40 caracteres → OK
            // "María José..."  (101 caracteres)         → Rechazado
            // ================================================================

            this.errors.push('El nombre no puede superar 100 caracteres');
            return false;
        }

        // Validación 4: Solo letras y espacios (con acentos y ñ)
        const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        // ====================================================================
        // REGEX PARA VALIDAR CARACTERES DEL NOMBRE
        // ====================================================================
        // DESGLOSE:
        // /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/
        //
        // ^           → Inicio del string
        // []          → Conjunto de caracteres permitidos
        // a-z         → Letras minúsculas (a, b, c, ..., z)
        // A-Z         → Letras mayúsculas (A, B, C, ..., Z)
        // áéíóú       → Vocales minúsculas con acento
        // ÁÉÍÓÚ       → Vocales mayúsculas con acento
        // ñÑ          → Letra eñe (minúscula y mayúscula)
        // \s          → Espacios en blanco
        // +           → Uno o más de los caracteres anteriores
        // $           → Fin del string
        //
        // EJEMPLOS:
        //
        // ✓ VÁLIDOS:
        // "Juan"              → Solo letras
        // "María"             → Letras con acento
        // "José Ángel"        → Letras, acento y espacio
        // "Peña"              → Con ñ
        // "Ana María Pérez"   → Combinación completa
        //
        // ✗ INVÁLIDOS:
        // "Juan123"           → Contiene números
        // "Juan@Pérez"        → Contiene @
        // "Juan-Carlos"       → Contiene guion
        // "Juan_Pérez"        → Contiene guion bajo
        // "Juan.Carlos"       → Contiene punto
        //
        // ¿POR QUÉ ESTOS CARACTERES?
        // - a-zA-Z: Alfabeto inglés básico
        // - áéíóúÁÉÍÓÚ: Acentos en español
        // - ñÑ: Letra distintiva del español
        // - \s: Permite nombres compuestos
        //
        // ¿QUÉ NO SE PERMITE?
        // - Números: 0-9
        // - Símbolos: @, #, $, %, etc.
        // - Puntuación: ., ,, ;, :
        // - Guiones: -, _
        //
        // NOTA SOBRE UNICODE:
        // - Esta regex es básica, solo cubre español
        // - No cubre otros idiomas (árabe, chino, etc.)
        // - Para internacional, usar: /^[\p{L}\s]+$/u
        // ====================================================================

        if (!nombreRegex.test(nombre)) {
            this.errors.push('El nombre solo puede contener letras y espacios');
            return false;
        }

        return true;
        // ====================================================================
        // Si llegamos aquí, el nombre:
        // ✓ No está vacío
        // ✓ Tiene entre 3 y 100 caracteres
        // ✓ Solo contiene letras (con acentos), espacios y ñ
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: getErrors()
    // ========================================================================
    // RESPONSABILIDAD: Devolver lista de errores de validación
    // ========================================================================

    /**
     * Obtener lista de errores de validación
     * 
     * USO:
     * const validator = new UserValidator();
     * validator.validateEmail("invalido");
     * const errores = validator.getErrors();
     * console.log(errores); // ["El formato del email no es válido"]
     * 
     * @returns {Array<string>} Array de mensajes de error
     */
    getErrors() {
        // ====================================================================
        // MÉTODO GETTER: Devolver propiedad privada
        // ====================================================================
        // PATRÓN: Getter (Accessor)
        //
        // ¿Para qué sirve?
        // - Proporciona acceso controlado a propiedades
        // - Permite leer pero no modificar directamente
        //
        // SIN GETTER:
        // - Usuario podría hacer: validator.errors = []
        // - Podría romper la lógica interna
        //
        // CON GETTER:
        // - Solo lectura: validator.getErrors()
        // - No puede modificar directamente
        //
        // BUENA PRÁCTICA:
        // - Propiedades privadas (no accesibles directamente)
        // - Métodos getter/setter para acceso controlado
        // ====================================================================

        return this.errors;
        // ====================================================================
        // RETORNAR ARRAY DE ERRORES
        // ====================================================================
        // ¿Qué devuelve?
        // - Array de strings con mensajes de error
        // - Puede estar vacío [] si no hay errores
        //
        // EJEMPLOS:
        // Sin errores: []
        // Con 1 error:  ["El email es obligatorio"]
        // Con 2 errores: ["El email es obligatorio", "El nombre es muy corto"]
        //
        // USO TÍPICO:
        // if (!validator.validateEmail("")) {
        //     validator.getErrors().forEach(error => {
        //         console.log(error); // Mostrar cada error
        //     });
        // }
        //
        // O más simple:
        // console.log(validator.getErrors().join(", "));
        // → "El email es obligatorio, El nombre es muy corto"
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: hasErrors()
    // ========================================================================
    // RESPONSABILIDAD: Verificar si hay errores de validación
    // ========================================================================

    /**
     * Verificar si hay errores de validación
     * 
     * Alternativa a verificar getErrors().length > 0
     * 
     * USO:
     * const validator = new UserValidator();
     * validator.validateEmail("invalido");
     * if (validator.hasErrors()) {
     *     console.log("Hay errores:", validator.getErrors());
     * }
     * 
     * @returns {boolean} true si hay errores, false si no
     */
    hasErrors() {
        // ====================================================================
        // MÉTODO DE UTILIDAD: Verificar existencia de errores
        // ====================================================================
        // PROPÓSITO:
        // - Simplificar verificación de errores
        // - Más legible que: validator.getErrors().length > 0
        //
        // COMPARACIÓN:
        // Sin hasErrors():
        // if (validator.getErrors().length > 0) {...}
        //
        // Con hasErrors():
        // if (validator.hasErrors()) {...}
        //
        // VENTAJA:
        // ✓ Más legible
        // ✓ Más semántico
        // ✓ Encapsula la lógica
        // ====================================================================

        return this.errors.length > 0;
        // ====================================================================
        // VERIFICAR SI EL ARRAY TIENE ELEMENTOS
        // ====================================================================
        // SINTAXIS:
        // this.errors.length > 0
        // └─ this.errors: Array de errores
        // └─ .length: Propiedad con cantidad de elementos
        // └─ > 0: Mayor que cero
        //
        // LÓGICA:
        // this.errors = []            → length = 0 → 0 > 0 = false
        // this.errors = ["Error"]     → length = 1 → 1 > 0 = true
        // this.errors = ["E1", "E2"]  → length = 2 → 2 > 0 = true
        //
        // ALTERNATIVAS:
        // this.errors.length !== 0    ← También funciona
        // this.errors.length >= 1     ← También funciona
        // this.errors[0] !== undefined ← Más complejo
        //
        // PREFERENCIA:
        // > 0 es más claro y directo
        // ====================================================================
    }

    // ========================================================================
    // MÉTODO: clearErrors()
    // ========================================================================
    // RESPONSABILIDAD: Limpiar errores acumulados
    // ========================================================================

    /**
     * Limpiar todos los errores acumulados
     * 
     * Útil si quieres reutilizar el mismo validador para múltiples validaciones
     * independientes sin crear nueva instancia.
     * 
     * USO:
     * const validator = new UserValidator();
     * validator.validateEmail("invalido");
     * console.log(validator.getErrors()); // ["Error..."]
     * validator.clearErrors();
     * console.log(validator.getErrors()); // []
     * 
     * @returns {void}
     */
    clearErrors() {
        // ====================================================================
        // MÉTODO DE UTILIDAD: Resetear errores
        // ====================================================================
        // PROPÓSITO:
        // - Limpiar errores manualmente
        // - Útil para reutilizar el mismo validador
        //
        // CASO DE USO:
        // const validator = new UserValidator(); // Crear una vez
        //
        // // Validar primer campo
        // validator.validateEmail("email1");
        // console.log(validator.getErrors());
        //
        // // Limpiar y validar segundo campo
        // validator.clearErrors();
        // validator.validateNombre("nombre1");
        // console.log(validator.getErrors());
        //
        // SIN clearErrors():
        // - Cada método ya limpia al inicio (this.errors = [])
        // - Entonces no es estrictamente necesario
        // - Pero proporciona control explícito
        // ====================================================================

        this.errors = [];
        // ====================================================================
        // RESETEAR ARRAY A VACÍO
        // ====================================================================
        // EFECTO:
        // Antes:  this.errors = ["Error 1", "Error 2"]
        // Después: this.errors = []
        //
        // NOTA:
        // - Cada método validate*() ya hace esto al inicio
        // - clearErrors() es redundante pero útil para claridad
        // - Permite limpiar sin tener que validar algo nuevo
        // ====================================================================
    }
}

// ============================================================================
// MENSAJE DE CONFIRMACIÓN DE CARGA
// ============================================================================
console.log('✅ UserValidator.js cargado');
// ============================================================================
// ¿Para qué?
// - Confirmar que el archivo se cargó correctamente
// - Útil para debugging
// - Se ve en la consola del navegador (F12 → Console)
//
// BUENA PRÁCTICA EN DESARROLLO:
// ✓ Usar en desarrollo para verificar carga
// ✗ Eliminar en producción (o comentar)
// ============================================================================

// ============================================================================
// RESUMEN DE CONCEPTOS EXPLICADOS:
// ============================================================================
//
// JAVASCRIPT ORIENTADO A OBJETOS:
// - class (declaración de clase)
// - constructor (inicialización)
// - this (referencia al objeto)
// - Métodos de instancia
// - new (crear instancias)
//
// VALIDACIÓN:
// - Validación del lado del cliente
// - Mensajes de error descriptivos
// - Early return pattern
// - Normalización de datos (trim)
//
// EXPRESIONES REGULARES:
// - Sintaxis básica /patrón/
// - Caracteres especiales (^, $, [], +, etc.)
// - .test() para verificar coincidencia
// - Validación de formato de email
// - Validación de caracteres en nombres
//
// STRING METHODS:
// - .trim() (eliminar espacios)
// - .length (longitud del string)
//
// ARRAY METHODS:
// - .push() (agregar elemento)
// - .length (cantidad de elementos)
//
// OPERADORES:
// - ! (NOT - negación)
// - || (OR - o lógico)
// - && (AND - y lógico)
// - === (igualdad estricta)
// - !== (desigualdad estricta)
// - > < >= <= (comparación)
//
// CONTROL DE FLUJO:
// - if-else (condicionales)
// - return (retorno de valores)
// - Early return (retorno temprano)
//
// PATRONES DE DISEÑO:
// - Strategy Pattern (diferentes estrategias de validación)
// - Getter Pattern (acceso controlado a propiedades)
//
// PRINCIPIOS SOLID:
// - Single Responsibility (cada método valida una cosa)
// - Open/Closed (fácil agregar validaciones sin modificar existentes)
//
// ============================================================================
