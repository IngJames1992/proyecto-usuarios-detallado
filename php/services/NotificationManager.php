<?php
// ============================================================================
// ARCHIVO: NotificationManager.php
// UBICACIÓN: php/notifications/NotificationManager.php
// PROPÓSITO: Sistema de notificaciones multicanal con múltiples patrones
// ============================================================================
//
// DESCRIPCIÓN GENERAL:
// Este es el archivo más avanzado del proyecto. Implementa TRES patrones
// de diseño trabajando juntos en una sola solución cohesionada:
//
//   1. STRATEGY  → Elegir canal de envío (Email, SMS, Push)
//   2. OBSERVER  → Registrar cada notificación enviada (BD, archivo)
//   3. FACTORY   → Crear estrategias sin conocer las clases concretas
//
// CLASES E INTERFACES:
// ============================================================================
//
//   interface NotificationStrategy  ← Contrato para estrategias de envío
//       ├── EmailNotification        ← Envía por correo electrónico
//       ├── SMSNotification          ← Envía por mensaje de texto
//       └── PushNotification         ← Envía notificación push
//
//   class NotificationFactory        ← Crea estrategias por nombre
//
//   interface Observer               ← Contrato para observadores
//       ├── DatabaseLogger           ← Registra en base de datos
//       └── FileLogger               ← Registra en archivo .log
//
//   class NotificationManager        ← Orquesta Strategy + Observer
//
// TECNOLOGÍAS Y CONCEPTOS:
// ============================================================================
//   • PHP Interfaces (interface / implements)
//   • Type Hinting con interfaces
//   • Patrón Strategy con interface (no clase abstracta)
//   • Patrón Observer (suscriptores/notificación de eventos)
//   • Patrón Factory Method
//   • preg_replace() con expresiones regulares
//   • date() para timestamps
//   • substr() para límite de caracteres
//   • strtolower() en switch de factory
//   • throw new Exception() — lanzar excepciones
//   • try / catch — capturar excepciones
//   • error_log() — registro de errores/info de PHP
//   • require_once — incluir archivos una sola vez
//   • __DIR__ — constante mágica de directorio
//   • dirname() — directorio padre de una ruta
//   • is_dir() — verificar si directorio existe
//   • mkdir() — crear directorios
//   • file_put_contents() con FILE_APPEND
//   • Null Coalescing (??) en arrays
//   • Visibilidad private para métodos internos
//
// PATRONES DE DISEÑO — COLABORACIÓN:
// ============================================================================
//
//   [Cliente]
//       │
//       ▼
//   NotificationManager ──── tiene ──── NotificationStrategy (Strategy)
//       │                                   ├── EmailNotification
//       │                                   ├── SMSNotification
//       │                                   └── PushNotification
//       │
//       └── tiene[] ──────── Observer[] (Observer)
//                                ├── DatabaseLogger
//                                └── FileLogger
//
//   NotificationFactory ──── crea ──── NotificationStrategy
//
// FLUJO COMPLETO (notify):
// ============================================================================
//
//   manager->notify('correo@email.com', 'Bienvenido', 1)
//       │
//       ├─ 1. strategy->send('correo@email.com', 'Bienvenido')
//       │       └─ EmailNotification::send() → $resultado
//       │
//       ├─ 2. $resultado['usuario_id'] = 1
//       │
//       └─ 3. notifyObservers('notification_sent', $resultado)
//               ├─ DatabaseLogger->update(...)  → INSERT en BD
//               └─ FileLogger->update(...)      → Escribe en .log
//
// ============================================================================

// ============================================================================
// INTERFACE: NotificationStrategy
// ============================================================================
// PROPÓSITO:
//   Definir el CONTRATO que deben cumplir todas las estrategias de envío.
//   Cualquier clase que "implemente" esta interface DEBE tener send() y getNombre().
//
// INTERFACE vs CLASE ABSTRACTA:
// ============================================================================
// CLASE ABSTRACTA (abstract class):
//   - Puede tener métodos con implementación
//   - Puede tener propiedades
//   - Una clase solo puede extender UNA clase abstracta (herencia simple)
//
// INTERFACE:
//   - Solo declara métodos (sin implementación)
//   - Sin propiedades de instancia (solo constantes)
//   - Una clase puede implementar MÚLTIPLES interfaces
//
// EJEMPLO:
// class EmailNotification implements NotificationStrategy, Serializable, Loggable
//   → 3 interfaces a la vez (imposible con 3 clases abstractas)
//
// VENTAJA CLAVE DE INTERFACE:
//   El código que usa NotificationStrategy no sabe ni le importa
//   si es Email, SMS o Push. Solo sabe que tiene send() y getNombre().
//
// ANALOGÍA:
//   Una interface es como un enchufe eléctrico estándar.
//   No importa qué aparato conectes (Email, SMS, Push),
//   siempre tiene la misma forma (send, getNombre).
//   El tomacorriente (NotificationManager) acepta cualquiera.

/**
 * Interface: NotificationStrategy
 *
 * CONTRATO: Toda clase que implemente esta interface DEBE definir:
 *   - send(): Enviar la notificación
 *   - getNombre(): Identificar el canal
 */
interface NotificationStrategy {
    // ========================================================================
    // DECLARACIÓN DE INTERFACE
    // ========================================================================
    // SINTAXIS:
    // interface NombreInterface {
    //     public function metodo($param);
    // }
    //
    // REGLAS DE LAS INTERFACES:
    // 1. Solo declaraciones de métodos (sin cuerpo {})
    // 2. Todos los métodos son implícitamente public
    // 3. No pueden tener propiedades de instancia
    // 4. Pueden tener constantes: const VERSION = '1.0';
    //
    // IMPLEMENTACIÓN:
    // class EmailNotification implements NotificationStrategy {
    //     public function send(...) { ... }     ← OBLIGATORIO
    //     public function getNombre() { ... }   ← OBLIGATORIO
    // }
    //
    // ERROR si no se implementan:
    // Fatal error: Class EmailNotification contains 1 abstract method
    // and must therefore be declared abstract or implement the remaining methods
    // ========================================================================

    /**
     * Enviar una notificación
     *
     * @param string $destinatario Email, teléfono o token según canal
     * @param string $mensaje      Contenido del mensaje
     * @return array               Resultado del envío con metadatos
     */
    public function send($destinatario, $mensaje);
    // ========================================================================
    // DECLARACIÓN DE MÉTODO EN INTERFACE (sin cuerpo)
    // ========================================================================
    // Notar: NO hay { } → Solo la firma del método
    // La implementación la hace cada clase concreta
    //
    // ¿QUÉ DEBE DEVOLVER send()?
    // Por convención (documentada), un array con:
    // [
    //     'tipo'         => 'email'|'sms'|'push',
    //     'destinatario' => 'correo@email.com',
    //     'mensaje'      => 'Texto del mensaje',
    //     'timestamp'    => '2024-01-15 10:30:00',
    //     'enviado'      => true|false
    // ]
    //
    // PARÁMETRO $destinatario varía por canal:
    // Email: 'usuario@email.com'
    // SMS:   '+573001234567'
    // Push:  'device_token_abc123'
    // ========================================================================

    /**
     * Obtener nombre identificador del canal
     *
     * @return string Nombre del canal ('Email', 'SMS', 'Push')
     */
    public function getNombre();
}


// ============================================================================
// ESTRATEGIA 1: EmailNotification
// ============================================================================
// CANAL: Correo electrónico
// PRODUCCIÓN: Usar PHPMailer, SwiftMailer, SendGrid API, AWS SES
// AQUÍ (simulado): error_log() para demostración
// ============================================================================

/**
 * Estrategia de notificación por Email
 *
 * IMPLEMENTA: NotificationStrategy
 *
 * CARACTERÍSTICAS:
 * - Registro de timestamp automático con date()
 * - Simula envío con error_log()
 * - En producción: integrar con servicio SMTP o API
 */
class EmailNotification implements NotificationStrategy {
    // ========================================================================
    // implements — Implementar una interface
    // ========================================================================
    // SINTAXIS:
    // class NombreClase implements Interface1, Interface2 {
    //     // Implementar TODOS los métodos de cada interface
    // }
    //
    // DIFERENCIA CON extends:
    // extends    → Herencia de clase (una sola)
    // implements → Implementar interface(s) (puede ser múltiple)
    //
    // TAMBIÉN SE PUEDE COMBINAR:
    // class AdminEmail extends EmailNotification implements Serializable {
    //     // Hereda de EmailNotification Y cumple Serializable
    // }
    //
    // VERIFICACIÓN EN TIEMPO DE EJECUCIÓN:
    // $email = new EmailNotification();
    // $email instanceof NotificationStrategy → true
    // $email instanceof EmailNotification    → true
    // ========================================================================

    /**
     * Enviar notificación por email
     *
     * @param  string $destinatario Dirección de email
     * @param  string $mensaje      Contenido del mensaje
     * @return array                Resultado del envío
     */
    public function send($destinatario, $mensaje) {

        $resultado = [
            'tipo'         => 'email',
            'destinatario' => $destinatario,
            'mensaje'      => $mensaje,
            'timestamp'    => date('Y-m-d H:i:s'),
            // ================================================================
            // date() — Obtener fecha/hora formateada
            // ================================================================
            // SINTAXIS:
            // date($formato);         ← Fecha/hora actual del servidor
            // date($formato, $unix);  ← Fecha específica (Unix timestamp)
            //
            // FORMATO 'Y-m-d H:i:s':
            // Y → Año con 4 dígitos:    2024
            // m → Mes con 0 inicial:    01, 02 ... 12
            // d → Día con 0 inicial:    01, 02 ... 31
            // H → Hora 24h con 0:       00, 01 ... 23
            // i → Minutos con 0:        00, 01 ... 59
            // s → Segundos con 0:       00, 01 ... 59
            //
            // RESULTADO: '2024-01-15 10:30:45'
            //
            // ESTE FORMATO ES EL ESTÁNDAR DE MySQL DATETIME
            // Permite guardar directamente en columnas DATETIME de BD
            //
            // OTROS FORMATOS COMUNES:
            // date('d/m/Y')       → '15/01/2024'   (formato Latinoamérica)
            // date('Y-m-d')       → '2024-01-15'   (solo fecha, MySQL DATE)
            // date('H:i')         → '10:30'         (solo hora)
            // date('D, d M Y')    → 'Mon, 15 Jan 2024' (legible)
            // date('U')           → '1705312245'    (Unix timestamp)
            // date('Y')           → '2024'          (solo año)
            // ================================================================
            'enviado'      => true
        ];

        // Simular envío (en producción: PHPMailer, SendGrid, etc.)
        error_log("📧 Email enviado a {$destinatario}: {$mensaje}");
        // ====================================================================
        // error_log() — Registrar mensajes en el log de PHP
        // ====================================================================
        // SINTAXIS:
        // error_log($mensaje);
        // error_log($mensaje, $tipo, $destino);
        //
        // ¿QUÉ HACE?
        // - Escribe en el log de errores de PHP
        // - Por defecto: escribe en el log del servidor web (Apache/Nginx)
        // - En XAMPP: xampp/apache/logs/error.log
        // - En Linux: /var/log/apache2/error.log o /var/log/php/error.log
        //
        // PARÁMETRO $tipo:
        // 0 → Log del sistema (por defecto)
        // 1 → Email (envía por correo)
        // 3 → Archivo específico: error_log($msg, 3, '/ruta/archivo.log')
        //
        // ¿POR QUÉ USAR error_log() AQUÍ?
        // - Simula el envío sin infraestructura real
        // - En desarrollo: ver en el log qué se "enviaría"
        // - En producción: reemplazar por PHPMailer, SendGrid, etc.
        //
        // DIFERENCIA CON echo/print:
        // echo     → Muestra en el navegador (respuesta HTTP)
        // error_log → Escribe en archivo de log (invisible para usuario)
        //
        // NOTA SOBRE {$destinatario}:
        // En strings con comillas dobles "", PHP interpola variables
        // {$variable} → Valor de la variable en tiempo de ejecución
        // ====================================================================

        return $resultado;
    }

    /**
     * @return string Nombre del canal
     */
    public function getNombre() {
        return 'Email';
    }
}


// ============================================================================
// ESTRATEGIA 2: SMSNotification
// ============================================================================
// CANAL: Mensaje de texto (SMS)
// PRODUCCIÓN: Twilio, Nexmo/Vonage, AWS SNS, MessageBird
// CARACTERÍSTICAS:
//   - Limpieza del número de teléfono con preg_replace()
//   - Límite de 160 caracteres (estándar SMS)
// ============================================================================

/**
 * Estrategia de notificación por SMS
 *
 * IMPLEMENTA: NotificationStrategy
 *
 * CARACTERÍSTICAS:
 * - Sanitiza el número de teléfono (solo dígitos y +)
 * - Trunca mensaje a 160 caracteres (límite SMS)
 * - En producción: integrar con Twilio API
 */
class SMSNotification implements NotificationStrategy {

    /**
     * Enviar notificación por SMS
     *
     * @param  string $destinatario Número de teléfono (se limpiará)
     * @param  string $mensaje      Texto del SMS (máx 160 chars)
     * @return array                Resultado del envío
     */
    public function send($destinatario, $mensaje) {

        $telefono = preg_replace('/[^0-9+]/', '', $destinatario);
        // ====================================================================
        // preg_replace() — Reemplazar usando expresiones regulares
        // ====================================================================
        // SINTAXIS:
        // preg_replace($patron, $reemplazo, $string);
        //
        // PARÁMETROS:
        // $patron     → Patrón regex entre /delimitadores/flags
        // $reemplazo  → Con qué reemplazar las coincidencias
        // $string     → El texto donde buscar
        //
        // DESGLOSE DEL PATRÓN '/[^0-9+]/':
        //
        // /.../ → Delimitadores del patrón (pueden ser #, ~, etc.)
        //
        // [...] → Clase de caracteres: coincide con UN carácter
        //
        // ^ dentro de [...] → Negación
        //   [^0-9+] significa: cualquier carácter que NO sea dígito ni +
        //
        // 0-9 → Rango: dígitos del 0 al 9
        //
        // + → El carácter literal + (para código de país: +57)
        //
        // RESULTADO: /[^0-9+]/
        //   Coincide con cualquier carácter que NO sea dígito ni +
        //   Esos caracteres se reemplazan con '' (string vacío = se eliminan)
        //
        // EJEMPLOS:
        // preg_replace('/[^0-9+]/', '', '+57 (300) 123-4567')
        // → '+573001234567'   ← Solo dígitos y + (limpio)
        //
        // preg_replace('/[^0-9+]/', '', '300.123.4567')
        // → '3001234567'      ← Puntos eliminados
        //
        // preg_replace('/[^0-9+]/', '', '  +1-800-555-1234  ')
        // → '+18005551234'    ← Espacios y guiones eliminados
        //
        // ¿POR QUÉ LIMPIAR EL TELÉFONO?
        // - Usuarios ingresan: '+57 300 123-4567'
        // - APIs SMS esperan: '+573001234567'
        // - preg_replace normaliza el formato automáticamente
        //
        // OTRAS FUNCIONES REGEX EN PHP:
        // preg_match($pat, $str)           → Verificar si coincide (true/false)
        // preg_match_all($pat, $str, $m)   → Encontrar todas las coincidencias
        // preg_replace($pat, $rep, $str)   → Reemplazar coincidencias
        // preg_split($pat, $str)           → Dividir por patrón
        //
        // RELACIÓN CON JavaScript:
        // PHP:        preg_replace('/[^0-9+]/', '', $tel)
        // JavaScript: tel.replace(/[^0-9+]/g, '')
        // ====================================================================

        $resultado = [
            'tipo'         => 'sms',
            'destinatario' => $telefono,
            'mensaje'      => substr($mensaje, 0, 160),
            // ================================================================
            // substr($mensaje, 0, 160) — Limitar a 160 caracteres
            // ================================================================
            // ¿POR QUÉ 160?
            // - Estándar SMS: Un mensaje = máximo 160 caracteres (GSM-7)
            // - Si excede 160 caracteres → se divide en múltiples SMS
            // - Cada SMS adicional = costo adicional
            // - substr() trunca al límite para evitar esto
            //
            // EJEMPLOS:
            // Mensaje de 50 chars   → substr(msg, 0, 160) → Sin cambio
            // Mensaje de 200 chars  → substr(msg, 0, 160) → Truncado a 160
            //
            // NOTA TÉCNICA:
            // 160 chars es para GSM-7 (alfabeto latino básico)
            // Con caracteres especiales (ñ, acentos, emojis): 70 chars
            // ================================================================
            'timestamp'    => date('Y-m-d H:i:s'),
            'enviado'      => true
        ];

        error_log("📱 SMS enviado a {$telefono}: {$mensaje}");

        return $resultado;
    }

    /**
     * @return string Nombre del canal
     */
    public function getNombre() {
        return 'SMS';
    }
}


// ============================================================================
// ESTRATEGIA 3: PushNotification
// ============================================================================
// CANAL: Notificación Push (móvil/web)
// PRODUCCIÓN: Firebase Cloud Messaging (FCM), OneSignal, Apple APNS
// DESTINATARIO: Token de dispositivo (device token)
// ============================================================================

/**
 * Estrategia de notificación Push
 *
 * IMPLEMENTA: NotificationStrategy
 *
 * CARACTERÍSTICAS:
 * - Destinatario es un token de dispositivo
 * - En producción: enviar a Firebase/OneSignal API
 * - Funciona en iOS, Android y navegadores web
 */
class PushNotification implements NotificationStrategy {

    /**
     * Enviar notificación Push
     *
     * @param  string $destinatario Token del dispositivo
     * @param  string $mensaje      Contenido de la notificación
     * @return array                Resultado del envío
     */
    public function send($destinatario, $mensaje) {
        // En producción: llamar a Firebase Cloud Messaging API
        // POST https://fcm.googleapis.com/fcm/send
        // Headers: Authorization: key=SERVER_KEY
        // Body: { "to": "$destinatario", "notification": { "body": "$mensaje" } }

        $resultado = [
            'tipo'         => 'push',
            'destinatario' => $destinatario,
            'mensaje'      => $mensaje,
            'timestamp'    => date('Y-m-d H:i:s'),
            'enviado'      => true
        ];

        error_log("🔔 Push enviada a {$destinatario}: {$mensaje}");

        return $resultado;
    }

    /**
     * @return string Nombre del canal
     */
    public function getNombre() {
        return 'Push';
    }
}


// ============================================================================
// PATRÓN FACTORY: NotificationFactory
// ============================================================================
// PROPÓSITO:
//   Crear instancias de estrategias de notificación por nombre (string)
//   sin que el código cliente conozca las clases concretas.
//
// VENTAJA:
//   En lugar de: new EmailNotification()  ← Saber la clase exacta
//   Se usa:       NotificationFactory::create('email') ← Solo el nombre
//
// ¿CUÁNDO ES ÚTIL?
//   Cuando el tipo viene de:
//   - Base de datos: $row['canal_notificacion']
//   - Configuración: $config['default_channel']
//   - Request HTTP:  $_POST['tipo']
//   - Cualquier string en tiempo de ejecución
// ============================================================================

/**
 * Factory para crear estrategias de notificación
 *
 * PATRÓN: Factory Method
 *
 * USO:
 *   $strategy = NotificationFactory::create('email');
 *   $strategy = NotificationFactory::create('sms');
 *   $strategy = NotificationFactory::create('push');
 */
class NotificationFactory {

    /**
     * Crear estrategia según tipo
     *
     * @param  string               $tipo 'email', 'sms' o 'push'
     * @return NotificationStrategy       Instancia de la estrategia
     * @throws Exception                  Si el tipo no es soportado
     */
    public static function create($tipo) {

        switch (strtolower($tipo)) {
            // ================================================================
            // strtolower() EN EL SWITCH — Normalizar input
            // ================================================================
            // strtolower($tipo) convierte a minúsculas antes de comparar
            //
            // SIN strtolower:
            // create('Email') → no coincide con case 'email' → default → Error
            // create('EMAIL') → no coincide con case 'email' → default → Error
            //
            // CON strtolower:
            // create('Email') → strtolower → 'email' → coincide ✓
            // create('EMAIL') → strtolower → 'email' → coincide ✓
            // create('email') → strtolower → 'email' → coincide ✓
            //
            // ROBUSTEZ:
            // El código funciona independientemente de cómo el usuario
            // escriba el tipo (mayúsculas, minúsculas, mezcla)
            //
            // RELACIÓN CON JS:
            // PHP:        strtolower($tipo)
            // JavaScript: tipo.toLowerCase()
            // ================================================================

            case 'email':
                return new EmailNotification();

            case 'sms':
                return new SMSNotification();

            case 'push':
                return new PushNotification();

            default:
                throw new Exception("Tipo de notificación '{$tipo}' no soportado");
                // ============================================================
                // throw new Exception() — Lanzar excepción
                // ============================================================
                // SINTAXIS:
                // throw new Exception($mensaje);
                // throw new TipoEspecifico($mensaje);
                //
                // ¿QUÉ HACE?
                // - Detiene la ejecución del método
                // - Crea objeto Exception con el mensaje
                // - "Lanza" la excepción hacia arriba en la pila de llamadas
                // - Si nadie la captura → Error fatal en PHP
                //
                // DIFERENCIA CON throw en JavaScript:
                // PHP:        throw new Exception("mensaje")
                // JavaScript: throw new Error("mensaje")
                //
                // TIPOS DE EXCEPCIONES EN PHP:
                // Exception           ← Base (genérica)
                // InvalidArgumentException ← Argumento inválido
                // RuntimeException    ← Error en tiempo de ejecución
                // LogicException      ← Error de lógica del programa
                // PDOException        ← Errores de base de datos
                //
                // ¿POR QUÉ AQUÍ?
                // - Si se pide un tipo desconocido ('whatsapp', 'telegram')
                // - Informar claramente qué salió mal
                // - El error dice EXACTAMENTE cuál tipo falló:
                //   "Tipo de notificación 'whatsapp' no soportado"
                //
                // INTERPOLACIÓN EN EXCEPCIÓN:
                // "{$tipo}" → Incluye el valor inválido en el mensaje
                // Ej: "Tipo de notificación 'whatsapp' no soportado"
                //
                // CAPTURAR LA EXCEPCIÓN (en el código que llama):
                // try {
                //     $s = NotificationFactory::create('whatsapp');
                // } catch (Exception $e) {
                //     echo $e->getMessage(); // "Tipo 'whatsapp' no soportado"
                // }
                // ============================================================
        }
    }
}


// ============================================================================
// PATRÓN OBSERVER
// ============================================================================
//
// DEFINICIÓN:
// Define una dependencia uno-a-muchos entre objetos.
// Cuando un objeto (Subject) cambia de estado, todos sus dependientes
// (Observers) son notificados y actualizados automáticamente.
//
// COMPONENTES:
// 1. Subject (Sujeto):      NotificationManager → notifica eventos
// 2. Observer (Observador): Interface Observer  → define cómo reaccionar
// 3. ConcreteObservers:     DatabaseLogger, FileLogger → implementaciones
//
// ANALOGÍA:
// Es como suscribirse a un canal de YouTube.
// - YouTube (Subject) sube un video (evento)
// - Tus notificaciones (Observer) te avisan automáticamente
// - Otros suscriptores también reciben la notificación
// - YouTube no sabe ni le importa quiénes son sus suscriptores
//
// EN ESTE CÓDIGO:
// - NotificationManager envía notificación (evento: 'notification_sent')
// - DatabaseLogger reacciona: INSERT en base de datos
// - FileLogger reacciona: escribe en archivo .log
// - NotificationManager no sabe los detalles de logging
//
// VENTAJAS:
// ✓ Desacoplamiento: Subject no conoce los Observer concretos
// ✓ Extensibilidad: Agregar EmailLogger sin modificar NotificationManager
// ✓ Open/Closed: Abierto a extensión (nuevos observers), cerrado a modificación
// ✓ Principio de responsabilidad única: cada Observer hace una cosa
//
// DESVENTAJAS:
// ✗ Puede ser difícil de debuggear (efectos indirectos)
// ✗ Orden de notificación no siempre predecible
// ============================================================================

/**
 * Interface Observer
 *
 * CONTRATO: Toda clase observadora debe implementar update()
 *
 * @param string $evento Nombre del evento ocurrido
 * @param array  $data   Datos del evento
 */
interface Observer {
    // ========================================================================
    // SEGUNDA INTERFACE DEL ARCHIVO
    // ========================================================================
    // Este archivo define DOS interfaces:
    // 1. NotificationStrategy → Para estrategias de envío
    // 2. Observer             → Para observadores/listeners
    //
    // MÚLTIPLES INTERFACES EN UN ARCHIVO:
    // PHP permite múltiples clases/interfaces en un archivo
    // (aunque las buenas prácticas sugieren un archivo por clase/interface)
    //
    // ¿POR QUÉ INTERFACE PARA OBSERVER?
    // - Garantiza que DatabaseLogger y FileLogger tengan update()
    // - NotificationManager puede llamar $observer->update()
    //   sin saber si es Database, File o cualquier otro tipo
    // ========================================================================

    public function update($evento, $data);
    // ========================================================================
    // MÉTODO update() — El corazón del patrón Observer
    // ========================================================================
    // PARÁMETROS:
    // $evento → String que identifica qué pasó: 'notification_sent'
    // $data   → Array con los detalles del evento
    //
    // DISEÑO:
    // Usar $evento permite que un Observer reaccione solo a ciertos eventos
    // if ($evento === 'notification_sent') { ... }
    // if ($evento === 'user_created') { ... }
    //
    // Sin $evento: El Observer siempre ejecutaría su lógica,
    // aunque el evento no le corresponda
    // ========================================================================
}


// ============================================================================
// OBSERVADOR 1: DatabaseLogger
// ============================================================================
// RESPONSABILIDAD: Registrar notificaciones en la base de datos
// TABLA: logs_notificaciones
// ============================================================================

/**
 * Observer que registra notificaciones en base de datos
 *
 * IMPLEMENTA: Observer
 *
 * TABLA REQUERIDA:
 *   CREATE TABLE logs_notificaciones (
 *       id INT AUTO_INCREMENT PRIMARY KEY,
 *       usuario_id INT,
 *       tipo_notificacion VARCHAR(20),
 *       mensaje TEXT,
 *       enviado TINYINT(1),
 *       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 *   );
 */
class DatabaseLogger implements Observer {

    private $db;
    // Referencia a la instancia Database (Singleton)

    /**
     * Constructor: Obtener conexión a BD
     */
    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        // ====================================================================
        // require_once — Incluir archivo PHP una sola vez
        // ====================================================================
        // SINTAXIS:
        // require_once $ruta;
        //
        // ¿QUÉ HACE?
        // - Incluye el archivo PHP especificado
        // - Ejecuta su contenido (define clases, funciones, variables)
        // - "once" → Solo lo incluye UNA VEZ aunque se llame múltiples veces
        //
        // VARIANTES:
        // include       → Incluye, sigue aunque falle (warning)
        // include_once  → include + no duplicar
        // require       → Incluye, PARA si falla (fatal error)
        // require_once  → require + no duplicar (más usado)
        //
        // ¿POR QUÉ require_once Y NO require?
        // Si DatabaseLogger se crea múltiples veces, Database.php
        // solo se carga una vez (no se redefinen las clases)
        // Sin _once: "Fatal error: Cannot redeclare class Database"
        //
        // __DIR__ — Constante mágica de directorio
        // - Devuelve la ruta absoluta del directorio del archivo actual
        // - Es una "constante mágica" (con doble guión __)
        // - Otras constantes mágicas: __FILE__, __LINE__, __CLASS__, __METHOD__
        //
        // EJEMPLO:
        // Archivo actual: /var/www/html/php/notifications/NotificationManager.php
        // __DIR__ = '/var/www/html/php/notifications'
        //
        // __DIR__ . '/../config/Database.php'
        // = '/var/www/html/php/notifications/../config/Database.php'
        // = '/var/www/html/php/config/Database.php'   (.. = directorio padre)
        //
        // ¿POR QUÉ USAR __DIR__ Y NO RUTA RELATIVA?
        // MALO: require_once '../config/Database.php'
        //   - Relativo al directorio de TRABAJO, no del archivo
        //   - Si el script se llama desde otro directorio → falla
        //
        // BUENO: require_once __DIR__ . '/../config/Database.php'
        //   - Siempre relativo al directorio DEL ARCHIVO actual
        //   - Funciona sin importar desde dónde se ejecute
        // ====================================================================

        $this->db = Database::getInstance();
    }

    /**
     * Registrar notificación enviada en la base de datos
     *
     * @param string $evento Nombre del evento ('notification_sent')
     * @param array  $data   Datos de la notificación enviada
     */
    public function update($evento, $data) {

        if ($evento === 'notification_sent') {
            // ================================================================
            // Filtrar: Solo actuar en eventos de notificación enviada
            // Otros eventos ('user_created', etc.) se ignoran aquí
            // ================================================================

            $sql = "INSERT INTO logs_notificaciones
                    (usuario_id, tipo_notificacion, mensaje, enviado)
                    VALUES (?, ?, ?, ?)";

            try {
                // ============================================================
                // try — Bloque de código que puede lanzar excepción
                // ============================================================
                // SINTAXIS:
                // try {
                //     // Código que PUEDE fallar
                // } catch (TipoExcepcion $e) {
                //     // Qué hacer si falla
                // } finally {
                //     // Siempre se ejecuta (opcional)
                // }
                //
                // ¿POR QUÉ try/catch AQUÍ?
                // - La BD puede no estar disponible
                // - La tabla puede no existir
                // - Error de conexión
                // Sin try/catch: Un error de logging haría fallar TODO el sistema
                // Con try/catch: El error de logging se captura, el sistema sigue
                //
                // PRINCIPIO:
                // El logging NO debe interrumpir el flujo principal
                // Si no se puede guardar el log → registrar error → continuar
                // ============================================================

                $this->db->query($sql, [
                    $data['usuario_id'] ?? 0,
                    // ========================================================
                    // ?? 0 → Si 'usuario_id' no existe en $data, usar 0
                    // 0 puede significar "sistema" o "anónimo"
                    // ========================================================

                    $data['tipo']    ?? 'email',
                    // ?? 'email' → Valor por defecto si no hay tipo

                    $data['mensaje'] ?? '',
                    // ?? '' → String vacío si no hay mensaje

                    $data['enviado'] ?? false
                    // ?? false → Asumir no enviado si no se especificó
                ]);

                error_log("✓ Notificación registrada en BD");

            } catch (Exception $e) {
                // ============================================================
                // catch (Exception $e) — Capturar la excepción
                // ============================================================
                // SINTAXIS:
                // catch (TipoExcepcion $variable) { ... }
                //
                // Exception $e:
                // - Exception: Tipo de excepción a capturar
                // - $e: Variable que contiene el objeto Exception
                //
                // MÉTODOS DE $e (objeto Exception):
                // $e->getMessage()  → Mensaje de error
                // $e->getCode()     → Código de error
                // $e->getFile()     → Archivo donde ocurrió
                // $e->getLine()     → Línea donde ocurrió
                // $e->getTrace()    → Stack trace completo
                //
                // CAPTURA ESPECÍFICA:
                // catch (PDOException $e)          → Solo errores PDO
                // catch (InvalidArgumentException $e) → Solo arg inválidos
                // catch (Exception $e)             → Cualquier excepción (genérico)
                //
                // MÚLTIPLES catch (PHP 8+):
                // catch (PDOException | RuntimeException $e) { ... }
                // ============================================================

                error_log("✗ Error al registrar en BD: " . $e->getMessage());
                // ============================================================
                // $e->getMessage() — Obtener mensaje de la excepción
                // - Concatenado con . (operador de concatenación PHP)
                // - Se registra en el log sin interrumpir el flujo
                // ============================================================
            }
        }
    }
}


// ============================================================================
// OBSERVADOR 2: FileLogger
// ============================================================================
// RESPONSABILIDAD: Registrar notificaciones en archivo de texto
// ARCHIVO: logs/notifications.log (por defecto)
// ============================================================================

/**
 * Observer que registra notificaciones en archivo de log
 *
 * IMPLEMENTA: Observer
 *
 * CARACTERÍSTICAS:
 * - Crea el directorio de logs si no existe
 * - Agrega al archivo (no sobreescribe)
 * - Formato: [timestamp] tipo -> destinatario: mensaje
 *
 * EJEMPLO DE LÍNEA EN EL LOG:
 *   [2024-01-15 10:30:45] email -> juan@email.com: Bienvenido al sistema
 */
class FileLogger implements Observer {

    private $logFile;
    // Ruta absoluta al archivo de log

    /**
     * Constructor: Configurar ruta del archivo de log
     *
     * @param string $logFile Nombre del archivo (default: notifications.log)
     */
    public function __construct($logFile = 'notifications.log') {

        $this->logFile = __DIR__ . '/../../logs/' . $logFile;
        // ====================================================================
        // CONSTRUIR RUTA ABSOLUTA AL ARCHIVO DE LOG
        // ====================================================================
        // __DIR__ → Directorio del archivo actual
        //   Ej: '/var/www/html/php/notifications'
        //
        // '/../../logs/' → Subir 2 niveles y entrar a logs
        //   De: '/var/www/html/php/notifications'
        //   A:  '/var/www/html/logs'
        //
        // + $logFile → Nombre del archivo
        //   'notifications.log'
        //
        // RESULTADO:
        // $this->logFile = '/var/www/html/logs/notifications.log'
        //
        // ESTRUCTURA DE DIRECTORIOS:
        // /var/www/html/
        //   ├── php/
        //   │   ├── notifications/
        //   │   │   └── NotificationManager.php  ← __DIR__ apunta aquí
        //   │   └── config/
        //   └── logs/
        //       └── notifications.log            ← El log va aquí
        // ====================================================================

        $dir = dirname($this->logFile);
        // ====================================================================
        // dirname() — Obtener directorio de una ruta
        // ====================================================================
        // SINTAXIS:
        // dirname($ruta);
        //
        // ¿QUÉ HACE?
        // - Devuelve el directorio padre de la ruta dada
        // - Es como hacer cd .. pero en strings
        //
        // EJEMPLOS:
        // dirname('/var/www/html/logs/notifications.log')
        // → '/var/www/html/logs'
        //
        // dirname('/var/www/html/logs')
        // → '/var/www/html'
        //
        // dirname('notifications.log')
        // → '.'   (directorio actual)
        //
        // RELACIÓN CON JavaScript (Node.js):
        // PHP:  dirname(__FILE__)
        // Node: path.dirname(__filename)
        // ====================================================================

        if (!is_dir($dir)) {
            // ================================================================
            // is_dir() — Verificar si un directorio existe
            // ================================================================
            // SINTAXIS:
            // is_dir($ruta)
            //
            // ¿QUÉ HACE?
            // - Devuelve true si $ruta existe Y es un directorio
            // - Devuelve false si no existe o si es un archivo
            //
            // !is_dir($dir):
            // - true si el directorio NO existe → crear
            // - false si el directorio SÍ existe → no hacer nada
            //
            // FUNCIONES RELACIONADAS:
            // is_dir($ruta)    → ¿Es directorio?
            // is_file($ruta)   → ¿Es archivo?
            // file_exists($r)  → ¿Existe (archivo o directorio)?
            // is_readable($r)  → ¿Se puede leer?
            // is_writable($r)  → ¿Se puede escribir?
            // ================================================================

            mkdir($dir, 0777, true);
            // ================================================================
            // mkdir() — Crear directorio
            // ================================================================
            // SINTAXIS:
            // mkdir($ruta, $permisos, $recursivo);
            //
            // PARÁMETROS:
            // $ruta       → Ruta del directorio a crear
            // $permisos   → Permisos en octal (0777)
            // $recursivo  → true = crea directorios intermedios
            //
            // PERMISOS 0777:
            // - Sistema octal de Unix/Linux
            // - 0 → Prefijo octal (PHP)
            // - 7 → rwx (leer, escribir, ejecutar) para propietario
            // - 7 → rwx para grupo
            // - 7 → rwx para otros
            //
            // DESGLOSE OCTAL:
            // 4 = leer (r)
            // 2 = escribir (w)
            // 1 = ejecutar (x)
            // 7 = 4+2+1 = rwx (todos los permisos)
            //
            // OTROS PERMISOS COMUNES:
            // 0755 → rwxr-xr-x (propietario total, resto solo leer/ejecutar)
            // 0644 → rw-r--r-- (solo propietario puede escribir)
            // 0777 → rwxrwxrwx (todos pueden todo — útil en desarrollo)
            //
            // NOTA IMPORTANTE:
            // En producción, 0777 puede ser riesgo de seguridad
            // Usar 0755 o 0750 según necesidad
            //
            // $recursivo = true:
            // mkdir('/logs/sub1/sub2', 0777, true)
            // - Si /logs no existe → lo crea
            // - Si /logs/sub1 no existe → lo crea
            // - Crea toda la cadena de directorios necesaria
            // Sin true: Falla si el directorio padre no existe
            // ================================================================
        }
    }

    /**
     * Registrar notificación en archivo de log
     *
     * @param string $evento Nombre del evento
     * @param array  $data   Datos de la notificación
     */
    public function update($evento, $data) {

        if ($evento === 'notification_sent') {

            $timestamp    = date('Y-m-d H:i:s');
            $tipo         = $data['tipo']         ?? 'unknown';
            $destinatario = $data['destinatario'] ?? 'unknown';
            $mensaje      = $data['mensaje']      ?? '';

            $logMessage = "[{$timestamp}] {$tipo} -> {$destinatario}: {$mensaje}\n";
            // ================================================================
            // CONSTRUIR LÍNEA DE LOG
            // ================================================================
            // Formato: [2024-01-15 10:30:45] email -> juan@email.com: Bienvenido
            //
            // {$timestamp}    → '2024-01-15 10:30:45'
            // {$tipo}         → 'email'
            // {$destinatario} → 'juan@email.com'
            // {$mensaje}      → 'Bienvenido al sistema'
            // \n              → Salto de línea (nueva línea en el archivo)
            //
            // RESULTADO EN EL ARCHIVO:
            // [2024-01-15 10:30:45] email -> juan@email.com: Bienvenido al sistema
            // [2024-01-15 10:31:02] sms -> +57300123: Código de verificación: 1234
            // ================================================================

            file_put_contents($this->logFile, $logMessage, FILE_APPEND);
            // ================================================================
            // file_put_contents() — Escribir en archivo
            // ================================================================
            // SINTAXIS:
            // file_put_contents($ruta, $contenido, $flags);
            //
            // PARÁMETROS:
            // $ruta      → Ruta del archivo donde escribir
            // $contenido → Texto a escribir
            // $flags     → Opciones de escritura
            //
            // FILE_APPEND (flag):
            // - Agrega el contenido AL FINAL del archivo
            // - Sin este flag: SOBREESCRIBE el archivo completo
            //
            // DIFERENCIA:
            // SIN FILE_APPEND:
            //   file_put_contents('log.txt', "Línea 1\n");
            //   file_put_contents('log.txt', "Línea 2\n");
            //   Archivo: "Línea 2\n"  ← Solo la última (sobreescribió)
            //
            // CON FILE_APPEND:
            //   file_put_contents('log.txt', "Línea 1\n", FILE_APPEND);
            //   file_put_contents('log.txt', "Línea 2\n", FILE_APPEND);
            //   Archivo: "Línea 1\nLínea 2\n"  ← Ambas (acumuladas)
            //
            // ¿POR QUÉ FILE_APPEND PARA LOGS?
            // - Un log es un historial acumulativo
            // - No queremos perder registros anteriores
            // - Cada notificación se agrega al final del archivo
            //
            // FUNCIONES RELACIONADAS:
            // file_put_contents($r, $c)         → Escribir (sobreescribe)
            // file_put_contents($r, $c, FILE_APPEND) → Agregar al final
            // file_get_contents($r)             → Leer archivo completo
            // fopen($r, 'r')                    → Abrir archivo (más control)
            // fwrite($handle, $c)               → Escribir con handle abierto
            // fclose($handle)                   → Cerrar archivo
            //
            // RELACIÓN CON JavaScript (Node.js):
            // PHP:  file_put_contents($r, $c, FILE_APPEND)
            // Node: fs.appendFileSync($r, $c)
            // ================================================================
        }
    }
}


// ============================================================================
// CLASE PRINCIPAL: NotificationManager
// ============================================================================
// PROPÓSITO:
//   Orquestar el envío de notificaciones combinando Strategy y Observer.
//   Es el "director de orquesta" que coordina todos los componentes.
//
// PATRONES COMBINADOS:
//   ┌─────────────────────────────────────────────────────────────────┐
//   │ NotificationManager                                             │
//   │                                                                 │
//   │  STRATEGY:                                                      │
//   │  $strategy → EmailNotification | SMSNotification | Push...     │
//   │  setStrategy() → Cambiar canal en tiempo de ejecución          │
//   │  notify() → Delega envío a la estrategia                       │
//   │                                                                 │
//   │  OBSERVER:                                                      │
//   │  $observers[] → [DatabaseLogger, FileLogger, ...]              │
//   │  addObserver() → Suscribir nuevo observador                    │
//   │  notifyObservers() → Avisar a todos los suscriptores           │
//   └─────────────────────────────────────────────────────────────────┘
//
// COMPARACIÓN CON NotificationStrategy.js:
//   JavaScript: Estrategias sin observer (solo Strategy puro)
//   PHP:        Strategy + Observer + Factory (más completo)
// ============================================================================

/**
 * NotificationManager — Gestor principal de notificaciones
 *
 * COMBINA: Patrón Strategy + Patrón Observer
 *
 * USO COMPLETO:
 *   $manager = new NotificationManager(new EmailNotification());
 *   $manager->addObserver(new DatabaseLogger());
 *   $manager->addObserver(new FileLogger());
 *   $manager->notify('juan@email.com', 'Bienvenido', 1);
 *   $manager->setStrategy(new SMSNotification());
 *   $manager->notify('+573001234567', 'Código: 1234', 1);
 */
class NotificationManager {

    private $strategy;
    // ========================================================================
    // PROPIEDAD $strategy — Estrategia de envío actual
    // ========================================================================
    // TIPO: NotificationStrategy (interface)
    //
    // TYPE HINTING CON INTERFACE:
    // La propiedad puede contener CUALQUIER objeto que implemente
    // NotificationStrategy (Email, SMS, Push, o cualquier futura)
    //
    // POLIMORFISMO:
    // $this->strategy->send(...)
    // → Si es EmailNotification: envía email
    // → Si es SMSNotification:   envía SMS
    // → Mismo código, comportamiento diferente según la estrategia
    // ========================================================================

    private $observers = [];
    // ========================================================================
    // PROPIEDAD $observers — Lista de observadores suscritos
    // ========================================================================
    // TIPO: array de objetos Observer
    // VALOR INICIAL: [] (array vacío, sin observadores)
    //
    // DINÁMICA:
    // - Empieza vacío: $observers = []
    // - addObserver() agrega: $observers = [DatabaseLogger]
    // - addObserver() agrega: $observers = [DatabaseLogger, FileLogger]
    //
    // VENTAJA DE ARRAY:
    // - Permite múltiples observadores del mismo tipo
    //   (ej: 2 FileLoggers con rutas distintas)
    // - Orden de notificación: en el orden en que se agregaron
    // ========================================================================

    /**
     * Constructor: Configurar estrategia inicial
     *
     * @param NotificationStrategy|null $strategy Estrategia inicial (default: Email)
     */
    public function __construct(NotificationStrategy $strategy = null) {
        // ====================================================================
        // TYPE HINTING CON INTERFACE EN CONSTRUCTOR
        // ====================================================================
        // NotificationStrategy $strategy = null
        //
        // TYPE HINT: NotificationStrategy
        // - Acepta cualquier objeto que implemente la interface
        // - Rechaza objetos que NO implementen la interface
        //
        // = null: Parámetro opcional
        // - Se puede crear: new NotificationManager()         → usa Email
        // - O con estrategia: new NotificationManager(new SMS()) → usa SMS
        //
        // PHP VERIFICA:
        // new NotificationManager("email") ← TypeError: string dado, se espera interface
        // new NotificationManager(new EmailNotification()) ← OK ✓
        // ====================================================================

        $this->strategy = $strategy ?? new EmailNotification();
        // ====================================================================
        // VALOR POR DEFECTO CON ??
        // ====================================================================
        // $strategy es null (no se pasó argumento)
        //   → ?? new EmailNotification() → crea Email por defecto
        //
        // $strategy es new SMSNotification()
        //   → ?? no se evalúa → usa el SMS pasado
        //
        // Email como defecto es una decisión de diseño:
        // - El canal más universal
        // - Siempre disponible en cualquier contexto web
        // ====================================================================
    }

    /**
     * Cambiar la estrategia de notificación (PATRÓN STRATEGY)
     *
     * Permite cambiar el canal de envío en tiempo de ejecución
     * sin recrear el NotificationManager.
     *
     * @param NotificationStrategy $strategy Nueva estrategia
     */
    public function setStrategy(NotificationStrategy $strategy) {
        $this->strategy = $strategy;
        // ====================================================================
        // NÚCLEO DEL PATRÓN STRATEGY
        // ====================================================================
        // Este método es la esencia del Strategy Pattern:
        // cambiar el algoritmo (estrategia) sin cambiar el contexto.
        //
        // EJEMPLO DE USO:
        // $manager->setStrategy(new SMSNotification());
        // $manager->notify('+57300...', 'Código: 1234');
        // // Ahora usa SMS
        //
        // $manager->setStrategy(NotificationFactory::create('push'));
        // $manager->notify('device_token', 'Nuevo mensaje');
        // // Ahora usa Push, sin recrear el manager
        // ====================================================================
    }

    /**
     * Agregar un observador (PATRÓN OBSERVER — Suscribir)
     *
     * Los observadores reciben notificación cada vez que se envía
     * una notificación exitosa.
     *
     * @param Observer $observer Observador a agregar
     */
    public function addObserver(Observer $observer) {
        $this->observers[] = $observer;
        // ====================================================================
        // SUSCRIBIR OBSERVADOR
        // ====================================================================
        // TYPE HINT: Observer $observer
        // - Solo acepta objetos que implementen la interface Observer
        // - Garantiza que tienen el método update()
        //
        // $this->observers[] = $observer
        // - Agrega al final del array de observadores
        // - El manager puede tener 0, 1, 2, N observadores
        //
        // DESACOPLAMIENTO:
        // NotificationManager no sabe QUÉ tipo de observer es
        // Solo sabe que tiene update()
        // addObserver(new DatabaseLogger()) ← OK
        // addObserver(new FileLogger())     ← OK
        // addObserver(new SlackLogger())    ← También OK (si existe)
        // ====================================================================
    }

    /**
     * Notificar a todos los observadores (PATRÓN OBSERVER — Notificar)
     *
     * PRIVADO: Solo el manager decide cuándo notificar
     *
     * @param string $evento Nombre del evento
     * @param array  $data   Datos del evento
     */
    private function notifyObservers($evento, $data) {
        // ====================================================================
        // MÉTODO PRIVADO
        // ====================================================================
        // private: Solo puede llamarse desde DENTRO de NotificationManager
        //
        // ¿POR QUÉ PRIVADO?
        // - Los observadores se notifican solo cuando el manager lo decide
        // - Nadie externo debería poder disparar eventos manualmente
        // - Encapsula la lógica de notificación
        //
        // CONTRASTE:
        // addObserver()     → public  (cualquiera puede suscribirse)
        // notifyObservers() → private (solo el manager notifica)
        // ====================================================================

        foreach ($this->observers as $observer) {
            // ================================================================
            // ITERAR SOBRE TODOS LOS OBSERVADORES
            // ================================================================
            // Si $this->observers = [DatabaseLogger, FileLogger]:
            //
            // Iteración 1: $observer = DatabaseLogger
            //   $observer->update('notification_sent', $data)
            //   → INSERT en base de datos
            //
            // Iteración 2: $observer = FileLogger
            //   $observer->update('notification_sent', $data)
            //   → Escribe en archivo .log
            //
            // POLIMORFISMO:
            // Mismo método update() llamado, pero cada observer
            // lo implementa diferente
            // ================================================================

            $observer->update($evento, $data);
            // ================================================================
            // LLAMAR update() DE CADA OBSERVER
            // ================================================================
            // $observer puede ser cualquier clase que implemente Observer
            // → DatabaseLogger::update($evento, $data)
            // → FileLogger::update($evento, $data)
            // → SlackLogger::update($evento, $data)  ← Extensible sin cambiar esto
            //
            // OPEN/CLOSED PRINCIPLE:
            // Para agregar un nuevo canal de logging (ej: SlackLogger):
            // 1. Crear class SlackLogger implements Observer { ... }
            // 2. $manager->addObserver(new SlackLogger());
            // 3. notifyObservers() automáticamente lo incluye
            // NO se modifica notifyObservers() ni NotificationManager
            // ================================================================
        }
    }

    /**
     * Enviar notificación usando la estrategia actual
     *
     * FLUJO INTERNO:
     * 1. Delegar envío a la estrategia (Strategy)
     * 2. Agregar usuario_id al resultado
     * 3. Notificar a todos los observers (Observer)
     * 4. Devolver resultado del envío
     *
     * @param  string   $destinatario  Email, teléfono o device token
     * @param  string   $mensaje       Contenido del mensaje
     * @param  int|null $usuarioId     ID del usuario (para logging)
     * @return array                   Resultado completo del envío
     */
    public function notify($destinatario, $mensaje, $usuarioId = null) {

        $resultado = $this->strategy->send($destinatario, $mensaje);
        // ====================================================================
        // DELEGAR A LA ESTRATEGIA (PATRÓN STRATEGY)
        // ====================================================================
        // $this->strategy puede ser Email, SMS o Push
        // Todos tienen send() → polimorfismo
        //
        // RESULTADO varía según la estrategia:
        // Email: ['tipo' => 'email', 'destinatario' => '...', ...]
        // SMS:   ['tipo' => 'sms',   'destinatario' => '+57...', ...]
        // Push:  ['tipo' => 'push',  'destinatario' => 'token', ...]
        // ====================================================================

        $resultado['usuario_id'] = $usuarioId;
        // ====================================================================
        // AGREGAR METADATO: usuario_id
        // ====================================================================
        // - La estrategia no sabe quién es el usuario
        // - El manager tiene esta información (viene del endpoint)
        // - Se agrega al resultado para que los observers lo tengan
        //
        // Si $usuarioId = null (no se pasó):
        // $resultado['usuario_id'] = null
        //   → DatabaseLogger: ?? 0 → 0 (usuario anónimo o sistema)
        // ====================================================================

        $this->notifyObservers('notification_sent', $resultado);
        // ====================================================================
        // DISPARAR EVENTO A OBSERVERS (PATRÓN OBSERVER)
        // ====================================================================
        // Evento: 'notification_sent'
        // Datos: El array completo con tipo, destinatario, mensaje, etc.
        //
        // Cada observer decide qué hacer con estos datos
        // ====================================================================

        return $resultado;
        // ====================================================================
        // DEVOLVER RESULTADO AL CÓDIGO QUE LLAMA
        // ====================================================================
        // El endpoint (get_users.php, etc.) puede usar este resultado:
        // $result = $manager->notify('correo@email.com', 'Bienvenido', 1);
        // echo json_encode($result);
        // ====================================================================
    }

    /**
     * Enviar la misma notificación a múltiples destinatarios
     *
     * @param  array    $destinatarios Lista de destinatarios
     * @param  string   $mensaje       Mensaje a enviar a todos
     * @param  int|null $usuarioId     ID del usuario (para logging)
     * @return array                   Array de resultados (uno por destinatario)
     */
    public function notifyMultiple($destinatarios, $mensaje, $usuarioId = null) {
        $resultados = [];

        foreach ($destinatarios as $destinatario) {
            // ================================================================
            // REUTILIZAR notify() para cada destinatario
            // ================================================================
            // En lugar de duplicar la lógica, llama a notify() interno
            // Esto incluye automáticamente:
            // - Envío por la estrategia actual
            // - Notificación a observers
            // ================================================================

            $resultados[] = $this->notify($destinatario, $mensaje, $usuarioId);
        }

        return $resultados;
        // ====================================================================
        // DEVUELVE:
        // [
        //     ['tipo' => 'email', 'destinatario' => 'juan@...', ...],
        //     ['tipo' => 'email', 'destinatario' => 'maria@...', ...],
        //     ['tipo' => 'email', 'destinatario' => 'carlos@...', ...]
        // ]
        // ====================================================================
    }

    /**
     * Obtener nombre de la estrategia actualmente configurada
     *
     * @return string Nombre del canal activo ('Email', 'SMS', 'Push')
     */
    public function getCurrentStrategy() {
        return $this->strategy->getNombre();
        // ====================================================================
        // Delega a getNombre() de la estrategia actual
        // Útil para debugging y para mostrar en UI
        //
        // Ej: "Canal actual: " . $manager->getCurrentStrategy()
        // → "Canal actual: Email"
        // ====================================================================
    }
}
// Fin de clase NotificationManager


// ============================================================================
// EJEMPLO DE USO COMPLETO (comentado para referencia)
// ============================================================================
/*
// ─────────────────────────────────────────────────────────────────────────────
// CASO 1: Bienvenida por Email con logging
// ─────────────────────────────────────────────────────────────────────────────

// Crear manager con estrategia Email
$manager = new NotificationManager(new EmailNotification());

// Agregar observadores (se ejecutan en orden de adición)
$manager->addObserver(new DatabaseLogger());  // Registra en BD
$manager->addObserver(new FileLogger());      // Registra en archivo

// Enviar notificación
$result = $manager->notify('usuario@email.com', 'Bienvenido al sistema', 1);
// → EmailNotification::send() ejecuta
// → DatabaseLogger::update() ejecuta → INSERT en BD
// → FileLogger::update() ejecuta → Escribe en .log

// ─────────────────────────────────────────────────────────────────────────────
// CASO 2: Cambiar canal dinámicamente (STRATEGY en acción)
// ─────────────────────────────────────────────────────────────────────────────

// Cambiar a SMS sin recrear el manager
$manager->setStrategy(new SMSNotification());
$manager->notify('+573001234567', 'Código de verificación: 1234', 1);
// → SMSNotification::send() ejecuta
// → Observers siguen activos (DatabaseLogger y FileLogger)

// Cambiar a Push
$manager->setStrategy(new PushNotification());
$manager->notify('device_token_abc123', 'Tienes un nuevo mensaje', 1);

// ─────────────────────────────────────────────────────────────────────────────
// CASO 3: Usando Factory para crear estrategias
// ─────────────────────────────────────────────────────────────────────────────

// El tipo puede venir de BD, configuración, o request
$tipoCanal = 'sms'; // O de: $config['notification_channel']

try {
    $strategy = NotificationFactory::create($tipoCanal);
    $manager->setStrategy($strategy);
    $manager->notify('+57300...', 'Alerta del sistema', 1);
} catch (Exception $e) {
    error_log("Canal no soportado: " . $e->getMessage());
}

// ─────────────────────────────────────────────────────────────────────────────
// CASO 4: Notificación masiva
// ─────────────────────────────────────────────────────────────────────────────

$destinatarios = [
    'juan@email.com',
    'maria@email.com',
    'carlos@email.com'
];

$manager->setStrategy(new EmailNotification());
$resultados = $manager->notifyMultiple($destinatarios, 'Mantenimiento programado');
// → 3 envíos → 3 logs en BD → 3 líneas en archivo

// ─────────────────────────────────────────────────────────────────────────────
// CASO 5: Logger personalizado (OPEN/CLOSED en acción)
// ─────────────────────────────────────────────────────────────────────────────

// Nueva clase sin modificar NotificationManager:
// class SlackLogger implements Observer {
//     public function update($evento, $data) {
//         if ($evento === 'notification_sent') {
//             // Enviar a canal de Slack vía webhook
//             file_get_contents('https://hooks.slack.com/...');
//         }
//     }
// }
// $manager->addObserver(new SlackLogger()); ← Funciona sin cambiar nada

echo "Estrategia actual: " . $manager->getCurrentStrategy(); // "Email"
*/

// ============================================================================
// RESUMEN DE CONCEPTOS EXPLICADOS:
// ============================================================================
//
// TRES PATRONES DE DISEÑO COMBINADOS:
// ┌────────────────┬──────────────────────────────────────────────────────┐
// │ Patrón         │ Implementación en este archivo                       │
// ├────────────────┼──────────────────────────────────────────────────────┤
// │ Strategy       │ interface NotificationStrategy                       │
// │                │ EmailNotification, SMSNotification, PushNotification │
// │                │ NotificationManager::setStrategy()                   │
// ├────────────────┼──────────────────────────────────────────────────────┤
// │ Observer       │ interface Observer                                   │
// │                │ DatabaseLogger, FileLogger                           │
// │                │ NotificationManager::addObserver()                   │
// │                │ NotificationManager::notifyObservers() (privado)     │
// ├────────────────┼──────────────────────────────────────────────────────┤
// │ Factory        │ NotificationFactory::create($tipo)                   │
// │                │ switch con strtolower(), throw Exception             │
// └────────────────┴──────────────────────────────────────────────────────┘
//
// PHP:
// - interface / implements
// - Type hinting con interfaces
// - preg_replace() + Regex
// - date() con formatos
// - substr() para límite SMS
// - strtolower() en factory
// - throw new Exception()
// - try / catch
// - error_log()
// - require_once
// - __DIR__ (constante mágica)
// - dirname()
// - is_dir() + mkdir() con permisos octal
// - file_put_contents() + FILE_APPEND
// - Null coalescing ?? en arrays
// - Métodos private vs public
//
// PRINCIPIOS SOLID:
// - S: Cada clase tiene una responsabilidad
// - O: Agregar observers/estrategias sin modificar NotificationManager
// - L: Cualquier NotificationStrategy reemplazable entre sí
// - D: Depende de interfaces, no de clases concretas
//
// ============================================================================
