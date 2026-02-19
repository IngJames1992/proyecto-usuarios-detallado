// ============================================================================
// ARCHIVO: NotificationStrategy.js
// UBICACIÓN: js/NotificationStrategy.js
// PROPÓSITO: Sistema de notificaciones con diferentes estrategias de visualización
// ============================================================================
//
// DESCRIPCIÓN GENERAL:
// Este archivo implementa un sistema completo de notificaciones usando el
// Patrón Strategy. Permite mostrar mensajes al usuario de diferentes formas
// (Toast, Console, Alert) y cambiar entre ellas en tiempo de ejecución.
//
// TECNOLOGÍAS Y CONCEPTOS:
// ============================================================================
// 1. PATRÓN STRATEGY (Estrategia)
//    - Clase base abstracta (NotificationStrategy)
//    - Múltiples implementaciones concretas
//    - Intercambiables en tiempo de ejecución
//    - Gestor que usa las estrategias
//
// 2. HERENCIA EN JAVASCRIPT
//    - extends (herencia de clases)
//    - super() (llamar al constructor padre)
//    - Sobrescritura de métodos
//    - Polimorfismo
//
// 3. DOM MANIPULATION AVANZADO
//    - document.createElement() (crear elementos)
//    - document.body.appendChild() (agregar al DOM)
//    - Object.assign() (asignar estilos)
//    - .remove() (eliminar del DOM)
//
// 4. TEMPORIZADORES
//    - setTimeout() (ejecutar después de X tiempo)
//    - Callback functions
//    - Closure (captura de variables)
//
// 5. ANIMACIONES CSS
//    - @keyframes (definir animaciones)
//    - animation property
//    - transform (translateX)
//    - opacity
//
// 6. THROW Y ERRORES
//    - throw new Error()
//    - Clases abstractas simuladas
//    - Métodos que deben implementarse
//
// PATRÓN DE DISEÑO: STRATEGY
// ============================================================================
// DEFINICIÓN:
// Define una familia de algoritmos, los encapsula y los hace intercambiables.
// Strategy permite que el algoritmo varíe independientemente de los clientes
// que lo utilizan.
//
// COMPONENTES:
// 1. Strategy (Interfaz/Clase base): NotificationStrategy
// 2. Concrete Strategies (Implementaciones):
//    - ToastNotification (notificación visual moderna)
//    - ConsoleNotification (para debugging)
//    - AlertNotification (diálogo nativo del navegador)
// 3. Context (Gestor): NotificationManager
//
// ANALOGÍA:
// Es como elegir medio de transporte para ir al trabajo:
// - Auto (ToastNotification): Rápido, cómodo, moderno
// - Bicicleta (ConsoleNotification): Simple, para desarrollo
// - Caminar (AlertNotification): Básico, siempre disponible
// Todos te llevan al mismo destino (mostrar mensaje), pero de forma diferente.
//
// VENTAJAS:
// ✓ Open/Closed Principle: Agregar nueva estrategia sin modificar existentes
// ✓ Single Responsibility: Cada estrategia maneja su propia lógica
// ✓ Composición sobre herencia
// ✓ Fácil testing (probar cada estrategia independientemente)
// ✓ Flexibilidad en tiempo de ejecución
//
// FLUJO DE USO:
// ============================================================================
// 1. Crear gestor: const notifier = new NotificationManager()
// 2. Usar estrategia por defecto: notifier.notify("Mensaje")
// 3. Cambiar estrategia: notifier.setStrategy(new AlertNotification())
// 4. Usar nueva estrategia: notifier.notify("Otro mensaje")
//
// CASOS DE USO:
// ============================================================================
// PRODUCCIÓN:
// - ToastNotification para usuarios finales (UX profesional)
//
// DESARROLLO:
// - ConsoleNotification para debugging (no interrumpe)
//
// TESTING:
// - AlertNotification para verificación rápida
//
// MÓVIL:
// - Podría implementarse MobileNotification con vibración
//
// ============================================================================

/**
 * ============================================================================
 * CLASE BASE: NotificationStrategy
 * ============================================================================
 * PROPÓSITO:
 * - Definir interfaz común para todas las estrategias
 * - Simular clase abstracta (JavaScript no tiene abstractas nativas)
 * - Forzar implementación de métodos en clases hijas
 * 
 * CONCEPTO: Clase Abstracta (simulada)
 * 
 * En otros lenguajes:
 * - Java: abstract class NotificationStrategy {...}
 * - C#: abstract class NotificationStrategy {...}
 * - PHP: abstract class NotificationStrategy {...}
 * 
 * En JavaScript:
 * - No hay palabra clave 'abstract'
 * - Se simula lanzando errores si se llaman métodos base
 * 
 * MÉTODOS ABSTRACTOS (deben implementarse):
 * - send(message): Enviar la notificación
 * - getName(): Obtener nombre de la estrategia
 * 
 * ============================================================================
 */
class NotificationStrategy {
    // ========================================================================
    // CLASE BASE / INTERFAZ
    // ========================================================================
    // ¿QUÉ ES UNA CLASE BASE?
    // - Clase padre de la que otras heredan
    // - Define la estructura común
    // - Puede tener métodos abstractos (que deben implementarse)
    //
    // ¿POR QUÉ USAR CLASE BASE?
    // - Garantiza que todas las estrategias tengan los mismos métodos
    // - Permite polimorfismo (tratar diferentes estrategias igual)
    // - Documenta qué métodos son necesarios
    //
    // ANALOGÍA:
    // Es como un contrato que dice: "Toda estrategia DEBE poder enviar
    // mensajes (send) y tener un nombre (getName)"
    //
    // EN JAVASCRIPT:
    // - No hay clases abstractas nativas
    // - Se simula lanzando Error si se llama método base
    // - Obliga a sobrescribir en clases hijas
    // ========================================================================

    send(message) {
        // ====================================================================
        // MÉTODO ABSTRACTO: send()
        // ====================================================================
        // SINTAXIS:
        // send(message) {...}
        // └─ send: Nombre del método
        // └─ message: Parámetro con el mensaje a mostrar
        // └─ {...}: Cuerpo del método
        //
        // ¿QUÉ HACE?
        // - Lanza error si se llama directamente
        // - Obliga a implementar en clases hijas
        //
        // ¿POR QUÉ throw Error?
        // - JavaScript no tiene abstract keyword
        // - Esta es la forma de simularlo
        // - Si olvidas implementar send() en clase hija, obtienes error claro
        //
        // EJEMPLO DE ERROR:
        // const strategy = new NotificationStrategy();
        // strategy.send("Hola"); // ❌ Error: Método send() debe ser implementado
        //
        // USO CORRECTO:
        // class MiEstrategia extends NotificationStrategy {
        //     send(message) {
        //         console.log(message); // ✓ Implementación propia
        //     }
        // }
        // ====================================================================

        throw new Error('Método send() debe ser implementado');
        // ====================================================================
        // throw new Error() - Lanzar excepción
        // ====================================================================
        // SINTAXIS:
        // throw new Error(mensaje);
        // └─ throw: Palabra clave para lanzar error
        // └─ new Error(): Crear objeto de error
        // └─ mensaje: Descripción del error
        //
        // ¿QUÉ HACE throw?
        // - Detiene ejecución inmediatamente
        // - Lanza error que debe ser capturado (try-catch)
        // - Si no se captura, aparece en consola como error
        //
        // ¿QUÉ ES new Error()?
        // - Constructor de objetos Error
        // - Crea instancia con mensaje personalizado
        //
        // ALTERNATIVAS:
        // throw "Error";              ← String simple (no recomendado)
        // throw new Error("Error");   ← Objeto Error (recomendado)
        // throw new TypeError("...");  ← Tipo específico de error
        //
        // RESULTADO:
        // Uncaught Error: Método send() debe ser implementado
        //     at NotificationStrategy.send (NotificationStrategy.js:X)
        //
        // PATRÓN:
        // Este es un patrón común para simular métodos abstractos en JS
        // ====================================================================
    }

    getName() {
        // ====================================================================
        // MÉTODO ABSTRACTO: getName()
        // ====================================================================
        // Similar a send(), este método también debe implementarse
        //
        // PROPÓSITO:
        // - Devolver nombre descriptivo de la estrategia
        // - Útil para logging y debugging
        // - Permite identificar qué estrategia está activa
        //
        // EJEMPLO DE IMPLEMENTACIÓN:
        // getName() {
        //     return 'Toast'; // ← String descriptivo
        // }
        // ====================================================================

        throw new Error('Método getName() debe ser implementado');
    }
}

// ============================================================================
// ESTRATEGIA 1: ToastNotification
// ============================================================================
// PROPÓSITO: Mostrar notificaciones modernas tipo "toast" en la esquina
// CARACTERÍSTICAS:
// - Visual atractivo
// - No bloquea interacción (no modal)
// - Se auto-elimina después de 3 segundos
// - Animación de entrada/salida
// - Posición fija en esquina inferior derecha
// ============================================================================

/**
 * Estrategia: Notificación Toast (en pantalla)
 * 
 * DESCRIPCIÓN:
 * Muestra notificación visual moderna en esquina inferior derecha.
 * Se anima al aparecer y desaparece automáticamente.
 * 
 * USO:
 * const toast = new ToastNotification();
 * toast.send("Usuario creado exitosamente");
 * 
 * VENTAJAS:
 * ✓ No interrumpe al usuario (no modal)
 * ✓ Visual atractivo y moderno
 * ✓ Se auto-elimina (no requiere clic)
 * 
 * DESVENTAJAS:
 * ✗ Puede perderse si el usuario no mira
 * ✗ No funciona si JavaScript está desactivado
 */
class ToastNotification extends NotificationStrategy {
    // ========================================================================
    // HERENCIA CON extends
    // ========================================================================
    // SINTAXIS:
    // class ToastNotification extends NotificationStrategy {...}
    // └─ class: Declarar clase
    // └─ ToastNotification: Nombre de la clase hija
    // └─ extends: Palabra clave de herencia
    // └─ NotificationStrategy: Clase padre (base)
    //
    // ¿QUÉ ES extends?
    // - Crea relación de herencia entre clases
    // - Clase hija hereda propiedades y métodos del padre
    // - Puede sobrescribir métodos del padre
    //
    // ¿QUÉ HEREDA ToastNotification?
    // - Todos los métodos de NotificationStrategy
    // - En este caso, send() y getName() (pero los sobrescribe)
    //
    // JERARQUÍA:
    // NotificationStrategy (padre)
    //   ├─ ToastNotification (hija)
    //   ├─ ConsoleNotification (hija)
    //   └─ AlertNotification (hija)
    //
    // POLIMORFISMO:
    // Todas las hijas pueden usarse como NotificationStrategy:
    // const strategy: NotificationStrategy = new ToastNotification();
    // strategy.send("Mensaje"); ← Funciona con cualquier estrategia
    //
    // COMPARACIÓN CON OTROS LENGUAJES:
    // Java:       class ToastNotification extends NotificationStrategy
    // C#:         class ToastNotification : NotificationStrategy
    // Python:     class ToastNotification(NotificationStrategy)
    // JavaScript: class ToastNotification extends NotificationStrategy
    // ========================================================================

    send(message) {
        // ====================================================================
        // SOBRESCRITURA DEL MÉTODO send()
        // ====================================================================
        // Este método REEMPLAZA el send() de la clase base
        //
        // DIFERENCIA:
        // Clase base:  throw new Error(...) ← Lanza error
        // Clase hija:  implementación real ← Hace algo útil
        //
        // POLIMORFISMO:
        // const strategy = new ToastNotification();
        // strategy.send("Hola"); ← Ejecuta ESTE método, no el de la base
        // ====================================================================

        // Paso 1: Crear elemento DOM
        const toast = document.createElement('div');
        // ====================================================================
        // document.createElement() - Crear nuevo elemento HTML
        // ====================================================================
        // SINTAXIS:
        // document.createElement(tagName);
        // └─ document: Objeto global del DOM
        // └─ createElement: Método para crear elementos
        // └─ tagName: Tipo de elemento ('div', 'span', 'button', etc.)
        //
        // ¿QUÉ HACE?
        // - Crea elemento HTML en memoria (no visible aún)
        // - Devuelve referencia al elemento
        // - Aún no está en el DOM (no se ve en la página)
        //
        // EJEMPLO:
        // const div = document.createElement('div');
        // const span = document.createElement('span');
        // const button = document.createElement('button');
        //
        // ¿POR QUÉ 'div'?
        // - Elemento contenedor genérico
        // - Flexible para estilos
        // - Semántica neutral
        //
        // SIGUIENTE PASO:
        // - Configurar propiedades (clase, texto, estilos)
        // - Agregarlo al DOM con appendChild()
        //
        // ANALOGÍA:
        // Es como preparar una caja (div) antes de ponerla en un estante
        // Primero la preparas, luego la colocas
        // ====================================================================

        // Paso 2: Asignar clase CSS
        toast.className = 'toast-notification';
        // ====================================================================
        // .className - Asignar clases CSS
        // ====================================================================
        // SINTAXIS:
        // elemento.className = 'clase1 clase2 clase3';
        //
        // ¿QUÉ HACE?
        // - Asigna clases CSS al elemento
        // - Equivalente a: <div class="toast-notification">
        //
        // ALTERNATIVAS:
        // toast.className = 'clase';          ← Sobrescribe todas las clases
        // toast.classList.add('clase');       ← Agrega clase sin sobrescribir
        // toast.classList.remove('clase');    ← Elimina clase
        // toast.classList.toggle('clase');    ← Alterna clase
        //
        // ¿POR QUÉ className AQUÍ?
        // - Elemento nuevo sin clases previas
        // - Simple y directo
        //
        // NOTA:
        // En este código, los estilos se aplican inline (style)
        // Esta clase es para identificación, no para estilos
        // ====================================================================

        // Paso 3: Asignar contenido de texto
        toast.textContent = message;
        // ====================================================================
        // .textContent - Asignar texto al elemento
        // ====================================================================
        // SINTAXIS:
        // elemento.textContent = 'texto';
        //
        // ¿QUÉ HACE?
        // - Inserta texto plano en el elemento
        // - Escapa caracteres HTML (seguro contra XSS)
        //
        // DIFERENCIA CON .innerHTML:
        // .textContent = '<b>Hola</b>' → Muestra: <b>Hola</b> (texto literal)
        // .innerHTML = '<b>Hola</b>'   → Muestra: Hola (en negrita)
        //
        // SEGURIDAD:
        // .textContent es SEGURO:
        // toast.textContent = '<script>alert("hack")</script>';
        // → Se muestra como texto, NO se ejecuta
        //
        // .innerHTML es PELIGROSO si el contenido viene de usuario:
        // toast.innerHTML = userInput; ← Puede ejecutar código malicioso
        //
        // ¿CUÁNDO USAR CADA UNO?
        // .textContent: Cuando insertas texto plano (PREFERIDO)
        // .innerHTML: Cuando necesitas HTML real (con precaución)
        //
        // EJEMPLO:
        // message = "Usuario creado exitosamente"
        // toast.textContent = message
        // Resultado: <div>Usuario creado exitosamente</div>
        // ====================================================================

        // Paso 4: Aplicar estilos CSS inline
        Object.assign(toast.style, {
            // ================================================================
            // Object.assign() - Asignar múltiples propiedades
            // ================================================================
            // SINTAXIS:
            // Object.assign(destino, origen1, origen2, ...);
            // └─ destino: Objeto que recibe propiedades
            // └─ origen: Objeto(s) con propiedades a copiar
            //
            // ¿QUÉ HACE?
            // - Copia propiedades de origen(es) a destino
            // - Modifica destino (no crea copia)
            // - Devuelve destino modificado
            //
            // APLICACIÓN AQUÍ:
            // Object.assign(toast.style, {...})
            // - toast.style: Objeto con estilos CSS del elemento
            // - {...}: Objeto con estilos a aplicar
            //
            // EQUIVALENTE SIN Object.assign:
            // toast.style.position = 'fixed';
            // toast.style.bottom = '20px';
            // toast.style.right = '20px';
            // ... (muchas líneas)
            //
            // VENTAJA:
            // ✓ Más conciso y legible
            // ✓ Agrupa estilos relacionados
            // ✓ Menos repetitivo
            //
            // EJEMPLO:
            // const obj = {a: 1};
            // Object.assign(obj, {b: 2, c: 3});
            // console.log(obj); // {a: 1, b: 2, c: 3}
            // ================================================================

            position: 'fixed',
            // ================================================================
            // position: 'fixed' - Posicionamiento fijo
            // ================================================================
            // ¿QUÉ HACE?
            // - Elemento se mantiene en posición fija en la ventana
            // - No se mueve al hacer scroll
            // - Se posiciona relativo a la ventana (viewport)
            //
            // VALORES DE position:
            // static:   Por defecto, flujo normal del documento
            // relative: Relativo a posición normal
            // absolute: Relativo al ancestro posicionado más cercano
            // fixed:    Relativo a la ventana (viewport)
            // sticky:   Híbrido entre relative y fixed
            //
            // ¿POR QUÉ fixed AQUÍ?
            // - Notificación siempre visible en misma posición
            // - No afecta el layout del resto de la página
            // - Se mantiene visible al hacer scroll
            //
            // EJEMPLO:
            // Usuario hace scroll → Página se mueve → Toast se mantiene fijo
            // ================================================================

            bottom: '20px',
            right: '20px',
            // ================================================================
            // bottom y right - Posicionamiento desde bordes
            // ================================================================
            // ¿QUÉ HACEN?
            // - bottom: '20px' → 20 píxeles desde el borde inferior
            // - right: '20px' → 20 píxeles desde el borde derecho
            //
            // UBICACIÓN RESULTANTE:
            // Esquina inferior derecha con margen de 20px
            //
            // ALTERNATIVAS:
            // top: '20px'    → Desde arriba
            // left: '20px'   → Desde izquierda
            // bottom: '0'    → Pegado al borde inferior
            //
            // CONVENCIÓN PARA TOASTS:
            // - Esquina inferior derecha (como aquí)
            // - O esquina superior derecha
            // - Evita interferir con contenido principal
            // ================================================================

            background: '#10b981',
            // ================================================================
            // background - Color de fondo
            // ================================================================
            // ¿QUÉ ES '#10b981'?
            // - Color hexadecimal
            // - Verde (similar a Tailwind green-500)
            //
            // FORMATO HEXADECIMAL:
            // #RRGGBB
            // └─ RR: Rojo (00-FF)
            // └─ GG: Verde (00-FF)
            // └─ BB: Azul (00-FF)
            //
            // EJEMPLO:
            // #10b981
            // └─ 10: Rojo bajo
            // └─ b9: Verde alto
            // └─ 81: Azul medio
            // Resultado: Verde agua profesional
            //
            // OTROS FORMATOS:
            // 'red'              ← Nombre de color
            // 'rgb(16, 185, 129)' ← RGB decimal
            // 'rgba(16, 185, 129, 0.5)' ← RGB con transparencia
            // 'hsl(160, 84%, 39%)' ← HSL (Hue, Saturation, Lightness)
            //
            // ¿POR QUÉ VERDE?
            // - Psicología: Verde = éxito, positivo, OK
            // - Convención UX para notificaciones de éxito
            //
            // VARIACIONES POR TIPO:
            // Verde (#10b981): Éxito
            // Rojo (#ef4444):  Error
            // Amarillo (#f59e0b): Advertencia
            // Azul (#3b82f6):  Información
            // ================================================================

            color: 'white',
            // ================================================================
            // color - Color del texto
            // ================================================================
            // Blanco sobre verde → Alto contraste → Fácil de leer
            //
            // ACCESIBILIDAD:
            // - Ratio de contraste debe ser mínimo 4.5:1
            // - Blanco sobre #10b981 cumple con WCAG AA
            // ================================================================

            padding: '1rem 1.5rem',
            // ================================================================
            // padding - Espaciado interno
            // ================================================================
            // SINTAXIS:
            // padding: 'vertical horizontal';
            //
            // ¿QUÉ ES 'rem'?
            // - Unidad relativa al tamaño de fuente raíz
            // - 1rem = tamaño de fuente del <html> (usualmente 16px)
            // - 1rem = 16px (por defecto)
            // - 1.5rem = 24px
            //
            // AQUÍ:
            // '1rem 1.5rem' = '16px 24px'
            // - 16px arriba y abajo
            // - 24px izquierda y derecha
            //
            // SINTAXIS COMPLETA DE padding:
            // padding: '10px';              ← Todos los lados
            // padding: '10px 20px';         ← Vertical Horizontal
            // padding: '10px 20px 15px';    ← Top H-izq-der Bottom
            // padding: '10px 20px 15px 25px'; ← Top Right Bottom Left (horario)
            //
            // ¿POR QUÉ rem Y NO px?
            // ✓ Escalable (respeta preferencias de usuario)
            // ✓ Accesibilidad (usuarios pueden aumentar tamaño de fuente)
            // ✓ Responsive automático
            // ================================================================

            borderRadius: '8px',
            // ================================================================
            // borderRadius - Bordes redondeados
            // ================================================================
            // ¿QUÉ HACE?
            // - Redondea las esquinas del elemento
            // - 8px = radio de redondeo
            //
            // VALORES:
            // '0px':    Sin redondeo (esquinas rectas)
            // '4px':    Levemente redondeado
            // '8px':    Moderadamente redondeado (usado aquí)
            // '12px':   Más redondeado
            // '50%':    Círculo completo (si ancho = alto)
            //
            // TENDENCIA DISEÑO:
            // - 8px es estándar en diseño moderno
            // - Balance entre recto y muy redondeado
            // - Profesional y amigable
            // ================================================================

            boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
            // ================================================================
            // boxShadow - Sombra del elemento
            // ================================================================
            // SINTAXIS:
            // boxShadow: 'offset-x offset-y blur spread color';
            //
            // DESGLOSE:
            // '0 10px 15px -3px rgba(0, 0, 0, 0.1)'
            // └─ 0:      Desplazamiento horizontal (0 = centrado)
            // └─ 10px:   Desplazamiento vertical (sombra hacia abajo)
            // └─ 15px:   Blur (desenfoque)
            // └─ -3px:   Spread (expansión negativa = sombra más pequeña)
            // └─ rgba(0, 0, 0, 0.1): Color negro con 10% opacidad
            //
            // ¿QUÉ ES rgba?
            // - Red, Green, Blue, Alpha (transparencia)
            // - rgba(0, 0, 0, 0.1) = negro muy transparente
            //
            // EFECTO:
            // - Sombra sutil hacia abajo
            // - Da sensación de profundidad (elevación)
            // - El toast parece "flotar" sobre la página
            //
            // MATERIAL DESIGN:
            // Este estilo sigue principios de Material Design
            // Diferentes elevaciones = diferentes niveles de sombra
            // ================================================================

            zIndex: '10000',
            // ================================================================
            // zIndex - Orden de apilamiento (eje Z)
            // ================================================================
            // ¿QUÉ HACE?
            // - Controla qué elementos aparecen encima de otros
            // - Números más altos = más arriba
            //
            // VALORES:
            // auto:    Por defecto (orden en HTML)
            // 0:       Nivel base
            // 100:     Sobre elementos normales
            // 1000:    Modales típicos
            // 10000:   Muy alto (usado aquí)
            //
            // ¿POR QUÉ 10000?
            // - Asegura que toast esté sobre TODO
            // - Incluso sobre modales (z-index ~1000)
            // - Usuario siempre verá la notificación
            //
            // COMPARACIÓN:
            // z-index: 1    → Detrás de muchas cosas
            // z-index: 100  → Sobre contenido normal
            // z-index: 1000 → Sobre la mayoría (modales)
            // z-index: 10000 → Sobre absolutamente todo
            //
            // BUENAS PRÁCTICAS:
            // - No usar valores arbitrariamente altos (999999)
            // - Establecer escala: normal=1-100, modal=1000, toast=10000
            // - Documentar valores usados
            // ================================================================

            animation: 'slideInRight 0.3s ease'
            // ================================================================
            // animation - Aplicar animación CSS
            // ================================================================
            // SINTAXIS:
            // animation: 'name duration timing-function';
            //
            // DESGLOSE:
            // 'slideInRight 0.3s ease'
            // └─ slideInRight: Nombre de la animación (@keyframes)
            // └─ 0.3s: Duración (0.3 segundos = 300ms)
            // └─ ease: Función de tiempo (aceleración)
            //
            // ¿QUÉ ES slideInRight?
            // - Animación definida con @keyframes (más abajo en el código)
            // - Desliza el toast desde la derecha
            //
            // TIMING FUNCTIONS:
            // ease:        Inicio lento, rápido, final lento (por defecto)
            // linear:      Velocidad constante
            // ease-in:     Inicio lento, acelera
            // ease-out:    Inicio rápido, desacelera
            // ease-in-out: Lento al inicio y final
            //
            // ¿POR QUÉ 0.3s?
            // - 300ms es duración estándar para animaciones UI
            // - Suficientemente rápido (no molesta)
            // - Suficientemente lento (se nota)
            //
            // ANIMACIONES TÍPICAS:
            // 100-200ms: Muy rápido (hover effects)
            // 300ms:     Estándar (usado aquí)
            // 500ms:     Lento (transiciones importantes)
            // 1000ms+:   Muy lento (efectos especiales)
            // ================================================================
        });

        // Paso 5: Agregar al DOM
        document.body.appendChild(toast);
        // ====================================================================
        // .appendChild() - Agregar elemento al DOM
        // ====================================================================
        // SINTAXIS:
        // padre.appendChild(hijo);
        // └─ padre: Elemento contenedor
        // └─ appendChild: Método para agregar hijo
        // └─ hijo: Elemento a agregar
        //
        // ¿QUÉ HACE?
        // - Inserta elemento como último hijo del contenedor
        // - Ahora el elemento ES VISIBLE en la página
        //
        // AQUÍ:
        // document.body.appendChild(toast)
        // - document.body: Elemento <body> del HTML
        // - toast: Elemento div que creamos
        // - Resultado: <body>...<div class="toast-notification">...</div></body>
        //
        // ALTERNATIVAS:
        // padre.appendChild(hijo)        ← Al final (usado aquí)
        // padre.insertBefore(hijo, ref)  ← Antes de elemento específico
        // padre.prepend(hijo)            ← Al inicio
        // padre.append(hijo1, hijo2)     ← Múltiples al final
        //
        // ¿POR QUÉ body?
        // - Toast debe estar al nivel más alto
        // - No dentro de contenedores que puedan tener overflow:hidden
        // - Garantiza visibilidad completa
        //
        // MOMENTO:
        // - Hasta AHORA el toast se hace visible
        // - Antes estaba solo en memoria
        // - Con position:fixed aparece en esquina inferior derecha
        // ====================================================================

        // Paso 6: Auto-eliminar después de 3 segundos
        setTimeout(() => {
            // ================================================================
            // setTimeout() - Ejecutar código después de un tiempo
            // ================================================================
            // SINTAXIS:
            // setTimeout(función, milisegundos);
            // └─ función: Código a ejecutar (callback)
            // └─ milisegundos: Tiempo de espera
            //
            // ¿QUÉ HACE?
            // - Programa ejecución de función en el futuro
            // - No bloquea el código (asíncrono)
            // - Devuelve ID que puede usarse para cancelar (clearTimeout)
            //
            // EJEMPLO:
            // setTimeout(() => {
            //     console.log("3 segundos después");
            // }, 3000);
            //
            // CONVERSIÓN:
            // 1000ms = 1 segundo
            // 3000ms = 3 segundos (usado aquí)
            //
            // ¿QUÉ ES () => {...}?
            // - Arrow function (función flecha)
            // - Sintaxis moderna de JavaScript
            // - Equivalente a: function() {...}
            //
            // CLOSURE:
            // La función tiene acceso a 'toast' aunque se ejecute después
            // - toast se captura del scope exterior
            // - Cuando se ejecuta, toast todavía existe
            //
            // FLUJO:
            // Ahora:      Toast aparece en pantalla
            // +3 segundos: Este código se ejecuta
            // ================================================================

            // Cambiar animación a salida
            toast.style.animation = 'slideOutRight 0.3s ease';
            // ================================================================
            // Aplicar animación de salida
            // - slideOutRight: Deslizar hacia la derecha (salir)
            // - 0.3s: Misma duración que entrada
            // - ease: Misma función de tiempo
            //
            // EFECTO:
            // Toast se desliza suavemente hacia la derecha antes de eliminarse
            // ================================================================

            // Eliminar del DOM después de la animación
            setTimeout(() => toast.remove(), 300);
            // ================================================================
            // setTimeout ANIDADO
            // ================================================================
            // ¿POR QUÉ OTRO setTimeout?
            // - Esperar a que termine animación de salida (300ms)
            // - Luego eliminar del DOM
            //
            // SIN ESTE DELAY:
            // - toast.remove() se ejecutaría inmediatamente
            // - No se vería la animación de salida
            // - Desaparición brusca
            //
            // CON ESTE DELAY:
            // - Animación de salida se ejecuta (300ms)
            // - Después se elimina del DOM
            // - Transición suave
            //
            // .remove() - Eliminar elemento del DOM
            // - Remueve el elemento completamente
            // - Libera memoria
            // - Equivalente a: toast.parentNode.removeChild(toast)
            //
            // FLUJO COMPLETO:
            // t=0s:      Toast aparece con animación de entrada (0.3s)
            // t=3s:      Inicia animación de salida (0.3s)
            // t=3.3s:    Se elimina del DOM
            // ================================================================

        }, 3000);
        // ====================================================================
        // 3000 milisegundos = 3 segundos
        // - Tiempo estándar para notificaciones tipo toast
        // - Suficiente para leer mensaje
        // - No molesta quedándose mucho tiempo
        // ====================================================================

        // Paso 7: Retornar información
        return { tipo: 'toast', enviado: true };
        // ====================================================================
        // RETORNAR OBJETO CON INFORMACIÓN
        // ====================================================================
        // ¿QUÉ DEVUELVE?
        // - Objeto con información sobre la notificación enviada
        //
        // ESTRUCTURA:
        // {
        //     tipo: 'toast',     ← Tipo de notificación
        //     enviado: true      ← Confirmación de envío
        // }
        //
        // ¿PARA QUÉ?
        // - Confirmar que notificación se envió
        // - Útil para logging
        // - Permite al código que llama saber qué pasó
        //
        // USO:
        // const result = toast.send("Mensaje");
        // console.log(result.tipo);    // "toast"
        // console.log(result.enviado); // true
        //
        // PATRÓN:
        // Métodos que realizan acciones deberían devolver resultado
        // ====================================================================
    }

    getName() {
        return 'Toast';
        // ====================================================================
        // Implementación de getName()
        // - Devuelve string identificador
        // - Útil para logging y debugging
        // ====================================================================
    }
}

// ============================================================================
// ESTRATEGIA 2: ConsoleNotification
// ============================================================================
// PROPÓSITO: Imprimir notificaciones en consola del navegador
// CASOS DE USO:
// - Desarrollo y debugging
// - Testing automatizado
// - Ambientes sin UI
// ============================================================================

/**
 * Estrategia: Notificación por Console (para desarrollo)
 * 
 * DESCRIPCIÓN:
 * Imprime notificaciones en la consola del navegador (F12 → Console).
 * Ideal para desarrollo donde no quieres interrupciones visuales.
 * 
 * USO:
 * const console = new ConsoleNotification();
 * console.send("Debug: Usuario autenticado");
 * 
 * VENTAJAS:
 * ✓ No interrumpe desarrollo
 * ✓ Historial completo en consola
 * ✓ Útil para debugging
 * 
 * DESVENTAJAS:
 * ✗ Usuario final no lo ve
 * ✗ Requiere tener consola abierta
 */
class ConsoleNotification extends NotificationStrategy {
    send(message) {
        console.log(`📬 Notificación: ${message}`);
        // ====================================================================
        // console.log() - Imprimir en consola
        // ====================================================================
        // ¿QUÉ HACE?
        // - Imprime mensaje en consola del navegador (F12 → Console)
        // - No visible para usuario final
        // - Útil para debugging
        //
        // TEMPLATE LITERAL:
        // `📬 Notificación: ${message}`
        // - Backticks (`) permiten interpolación
        // - ${message} inserta valor de la variable
        // - Emoji 📬 para identificar visualmente
        //
        // RESULTADO:
        // Si message = "Usuario creado"
        // Consola muestra: "📬 Notificación: Usuario creado"
        //
        // OTROS MÉTODOS DE console:
        // console.log()   ← Información general
        // console.error() ← Errores (rojo)
        // console.warn()  ← Advertencias (amarillo)
        // console.info()  ← Información (azul)
        // console.debug() ← Debug (gris)
        //
        // ¿CUÁNDO USAR?
        // - Desarrollo local
        // - Testing
        // - Debugging
        // - NO en producción para notificaciones de usuario
        // ====================================================================

        return { tipo: 'console', enviado: true };
    }

    getName() {
        return 'Console';
    }
}

// ============================================================================
// ESTRATEGIA 3: AlertNotification
// ============================================================================
// PROPÓSITO: Mostrar notificaciones usando alert() nativo del navegador
// CARACTERÍSTICAS:
// - Modal (bloquea interacción)
// - Estilo nativo del navegador
// - Requiere clic para cerrar
// ============================================================================

/**
 * Estrategia: Notificación Alert (simple)
 * 
 * DESCRIPCIÓN:
 * Muestra notificación usando alert() nativo del navegador.
 * Bloquea interacción hasta que usuario haga clic en OK.
 * 
 * USO:
 * const alert = new AlertNotification();
 * alert.send("Operación completada");
 * 
 * VENTAJAS:
 * ✓ Siempre funciona (no requiere estilos)
 * ✓ Usuario debe reconocer (clic en OK)
 * ✓ Simple de implementar
 * 
 * DESVENTAJAS:
 * ✗ Bloquea toda la página
 * ✗ Estilo del sistema (no personalizable)
 * ✗ Interrumpe flujo del usuario
 * ✗ Considerado mala práctica UX
 */
class AlertNotification extends NotificationStrategy {
    send(message) {
        alert(message);
        // ====================================================================
        // alert() - Mostrar diálogo modal nativo
        // ====================================================================
        // ¿QUÉ HACE?
        // - Muestra ventana modal del navegador
        // - Bloquea ejecución de JavaScript hasta que usuario cierre
        // - Estilo nativo (no personalizable)
        //
        // CARACTERÍSTICAS:
        // - Modal: Bloquea interacción con página
        // - Síncrono: Código espera hasta que usuario cierre
        // - Nativo: Apariencia varía por navegador/sistema operativo
        //
        // EJEMPLO:
        // alert("Hola mundo");
        // console.log("Después"); ← NO se ejecuta hasta cerrar alert
        //
        // VENTANA MUESTRA:
        // ┌─────────────────────────┐
        // │ ⚠️ [Título del sitio]   │
        // │                         │
        // │ Hola mundo              │
        // │                         │
        // │           [  OK  ]      │
        // └─────────────────────────┘
        //
        // PROBLEMAS DE UX:
        // ✗ Interrumpe flujo del usuario
        // ✗ Molesto si se usa frecuentemente
        // ✗ No se puede personalizar apariencia
        // ✗ Bloquea TODA la interacción
        //
        // ¿CUÁNDO USAR?
        // - Testing rápido
        // - Errores críticos que requieren atención inmediata
        // - Situaciones de emergencia
        //
        // ¿CUÁNDO NO USAR?
        // - Notificaciones de éxito normales (usar Toast)
        // - Mensajes informativos (usar Toast)
        // - Múltiples notificaciones seguidas
        // - Aplicaciones profesionales modernas
        //
        // ALTERNATIVAS MODERNAS:
        // - Toast (esta implementación)
        // - Modales personalizados
        // - Notificaciones del sistema (Notification API)
        //
        // FUNCIONES RELACIONADAS:
        // alert("Mensaje")         ← Solo muestra mensaje
        // confirm("¿Continuar?")   ← Devuelve true/false (OK/Cancelar)
        // prompt("Nombre:", "")    ← Devuelve string ingresado
        // ====================================================================

        return { tipo: 'alert', enviado: true };
    }

    getName() {
        return 'Alert';
    }
}

// ============================================================================
// GESTOR DE NOTIFICACIONES
// ============================================================================
// PROPÓSITO: Clase Context del patrón Strategy
// RESPONSABILIDAD: Gestionar qué estrategia usar y cuándo cambiarla
// ============================================================================

/**
 * ============================================================================
 * CLASE: NotificationManager
 * ============================================================================
 * PROPÓSITO:
 * - Gestionar estrategia de notificación activa (Context del patrón)
 * - Permitir cambio de estrategia en tiempo de ejecución
 * - Proporcionar interfaz simple para enviar notificaciones
 * 
 * PATRÓN: Strategy (Context)
 * 
 * USO BÁSICO:
 * const notifier = new NotificationManager();
 * notifier.notify("Usuario creado"); // Usa Toast por defecto
 * 
 * USO AVANZADO:
 * const notifier = new NotificationManager(new ConsoleNotification());
 * notifier.notify("Debug 1"); // Console
 * notifier.setStrategy(new ToastNotification());
 * notifier.notify("Éxito"); // Toast
 * 
 * ============================================================================
 */
class NotificationManager {
    constructor(strategy = null) {
        // ====================================================================
        // CONSTRUCTOR CON PARÁMETRO OPCIONAL
        // ====================================================================
        // SINTAXIS:
        // constructor(strategy = null) {...}
        // └─ strategy: Parámetro
        // └─ = null: Valor por defecto (opcional)
        //
        // ¿QUÉ ES PARÁMETRO POR DEFECTO?
        // - Si no se pasa valor, usa el por defecto
        // - Si se pasa valor, usa el pasado
        //
        // EJEMPLO:
        // new NotificationManager()              ← strategy = null
        // new NotificationManager(new Toast())   ← strategy = Toast
        //
        // VENTAJA:
        // - Permite uso simple y avanzado
        // - Simple: new NotificationManager() (usa defecto)
        // - Avanzado: new NotificationManager(customStrategy)
        // ====================================================================

        this.strategy = strategy || new ToastNotification();
        // ====================================================================
        // OPERADOR || (OR LÓGICO) PARA VALOR POR DEFECTO
        // ====================================================================
        // SINTAXIS:
        // this.strategy = strategy || new ToastNotification();
        //
        // ¿CÓMO FUNCIONA?
        // - Si strategy es truthy, usa strategy
        // - Si strategy es falsy (null, undefined, false), usa ToastNotification
        //
        // EVALUACIÓN:
        // Caso 1: strategy = new ConsoleNotification()
        // → strategy es truthy → usa strategy
        // → this.strategy = ConsoleNotification
        //
        // Caso 2: strategy = null
        // → strategy es falsy → usa ToastNotification
        // → this.strategy = new ToastNotification()
        //
        // ALTERNATIVA MODERNA:
        // this.strategy = strategy ?? new ToastNotification();
        // - ?? (Nullish coalescing operator)
        // - Solo usa defecto si strategy es null o undefined
        // - Más preciso que ||
        //
        // PATRÓN:
        // Este es un patrón común para valores por defecto
        // ====================================================================
    }

    /**
     * Cambiar estrategia en tiempo de ejecución
     * 
     * Permite cambiar cómo se muestran las notificaciones sin
     * recrear el NotificationManager.
     * 
     * EJEMPLO:
     * const notifier = new NotificationManager();
     * notifier.setStrategy(new ConsoleNotification());
     * notifier.notify("Ahora usa console");
     * 
     * @param {NotificationStrategy} strategy - Nueva estrategia
     */
    setStrategy(strategy) {
        // ====================================================================
        // CAMBIAR ESTRATEGIA DINÁMICAMENTE
        // ====================================================================
        // PROPÓSITO:
        // - Permitir cambio de comportamiento en tiempo de ejecución
        // - Sin necesidad de recrear el objeto
        //
        // PATRÓN STRATEGY:
        // Esta es la clave del patrón Strategy
        // - Comportamiento (estrategia) es intercambiable
        // - Se cambia en tiempo de ejecución
        // - Sin modificar el código del gestor
        //
        // EJEMPLO DE USO:
        // // Desarrollo: usar console
        // if (isDevelopment) {
        //     notifier.setStrategy(new ConsoleNotification());
        // }
        // 
        // // Producción: usar toast
        // else {
        //     notifier.setStrategy(new ToastNotification());
        // }
        //
        // VENTAJA:
        // - Flexibilidad máxima
        // - Cambios sin recrear objeto
        // - Adaptar comportamiento según contexto
        // ====================================================================

        this.strategy = strategy;
        // ====================================================================
        // Asignar nueva estrategia
        // - Sobrescribe estrategia anterior
        // - Próximas notificaciones usarán esta
        // ====================================================================
    }

    /**
     * Enviar notificación usando la estrategia actual
     * 
     * Delega el envío a la estrategia configurada.
     * 
     * EJEMPLO:
     * notifier.notify("Operación exitosa");
     * 
     * @param {string} message - Mensaje a mostrar
     * @returns {Object} Información sobre envío
     */
    notify(message) {
        // ====================================================================
        // DELEGAR A LA ESTRATEGIA
        // ====================================================================
        // PROPÓSITO:
        // - Método público simple para enviar notificaciones
        // - Delega ejecución a la estrategia activa
        //
        // DELEGACIÓN:
        // NotificationManager no sabe CÓMO enviar
        // - Solo sabe QUÉ estrategia usar
        // - La estrategia sabe CÓMO enviar
        //
        // POLIMORFISMO:
        // this.strategy.send(message)
        // - this.strategy puede ser cualquier estrategia
        // - Todas tienen método send()
        // - Cada una lo implementa diferente
        //
        // EJEMPLO:
        // Si strategy = ToastNotification:
        // → Crea toast visual
        //
        // Si strategy = ConsoleNotification:
        // → Imprime en consola
        //
        // PATRÓN:
        // Este es el núcleo del patrón Strategy
        // - Context (NotificationManager) delega a Strategy
        // - Strategy implementa algoritmo específico
        // ====================================================================

        return this.strategy.send(message);
        // ====================================================================
        // Ejecutar send() de la estrategia
        // - Devuelve resultado de la estrategia
        // - Permite saber si envío fue exitoso
        // ====================================================================
    }

    /**
     * Obtener nombre de la estrategia actual
     * 
     * Útil para debugging y logging.
     * 
     * EJEMPLO:
     * console.log(notifier.getCurrentStrategy()); // "Toast"
     * 
     * @returns {string} Nombre de la estrategia
     */
    getCurrentStrategy() {
        return this.strategy.getName();
        // ====================================================================
        // Obtener identificador de estrategia actual
        // - Útil para logging
        // - Útil para debugging
        // - Útil para mostrar en UI qué estrategia está activa
        // ====================================================================
    }
}

// ============================================================================
// ESTILOS DE ANIMACIÓN
// ============================================================================
// PROPÓSITO: Definir animaciones CSS para las notificaciones Toast
// ============================================================================

// Crear elemento <style> para agregar CSS al documento
const notificationStyles = document.createElement('style');
// ============================================================================
// document.createElement('style') - Crear elemento CSS dinámico
// ============================================================================
// ¿QUÉ HACE?
// - Crea elemento <style> en memoria
// - Permite agregar CSS mediante JavaScript
// - Se insertará en <head>
//
// ALTERNATIVAS:
// 1. CSS en archivo externo (.css)
// 2. CSS en <style> en HTML
// 3. CSS inline en elementos
// 4. CSS dinámico con JavaScript (usado aquí)
//
// ¿POR QUÉ DINÁMICO?
// - Este archivo JavaScript es autocontenido
// - No depende de CSS externo
// - Fácil de usar: solo incluir este JS
//
// RESULTADO:
// <head>
//   ...
//   <style>
//     @keyframes slideInRight {...}
//   </style>
// </head>
// ============================================================================

notificationStyles.textContent = `
    // ========================================================================
    // .textContent - Asignar contenido del <style>
    // ========================================================================
    // Contiene código CSS puro
    // Se interpreta como CSS cuando se agrega al DOM
    // ========================================================================

    @keyframes slideInRight {
        // ====================================================================
        // @keyframes - Definir animación CSS
        // ====================================================================
        // SINTAXIS:
        // @keyframes nombre {
        //     from { ... }  ← Estado inicial (0%)
        //     to { ... }    ← Estado final (100%)
        // }
        //
        // TAMBIÉN PUEDE SER:
        // @keyframes nombre {
        //     0% { ... }
        //     50% { ... }
        //     100% { ... }
        // }
        //
        // slideInRight:
        // - Nombre de la animación
        // - Usado en: animation: 'slideInRight 0.3s ease'
        //
        // PROPÓSITO:
        // - Animar entrada del toast desde la derecha
        // ====================================================================

        from {
            // ================================================================
            // ESTADO INICIAL (0% de la animación)
            // ================================================================
            transform: translateX(100%);
            // ================================================================
            // transform: translateX() - Desplazamiento horizontal
            // ================================================================
            // ¿QUÉ HACE?
            // - Mueve elemento en eje X (horizontal)
            // - 100% = ancho completo del elemento hacia la derecha
            //
            // EJEMPLO:
            // Si toast tiene 200px de ancho:
            // translateX(100%) = mover 200px a la derecha
            // → Toast está completamente fuera de la pantalla (derecha)
            //
            // VALORES:
            // translateX(0)     ← Posición original
            // translateX(50px)  ← 50px a la derecha
            // translateX(-50px) ← 50px a la izquierda
            // translateX(100%)  ← Ancho completo a la derecha (fuera)
            //
            // OTRAS TRANSFORMACIONES:
            // translateY()  ← Vertical
            // scale()       ← Escala (agrandar/achicar)
            // rotate()      ← Rotación
            // skew()        ← Inclinación
            // ================================================================

            opacity: 0;
            // ================================================================
            // opacity - Transparencia
            // ================================================================
            // VALORES:
            // 0:   Completamente transparente (invisible)
            // 0.5: Semi-transparente
            // 1:   Completamente opaco (visible)
            //
            // AQUÍ: 0 = Toast invisible al inicio
            // ================================================================
        }

        to {
            // ================================================================
            // ESTADO FINAL (100% de la animación)
            // ================================================================
            transform: translateX(0);
            // ================================================================
            // translateX(0) - Posición normal
            // - Toast en su posición final (esquina inferior derecha)
            // - No desplazado
            // ================================================================

            opacity: 1;
            // ================================================================
            // opacity: 1 - Completamente visible
            // ================================================================
        }

        // ====================================================================
        // ANIMACIÓN RESULTANTE:
        // - Toast empieza fuera de pantalla (derecha) e invisible
        // - Se desliza hacia la izquierda mientras aparece
        // - Termina en posición final completamente visible
        // - Duración: 0.3s (definido donde se usa)
        // - Timing: ease (suave)
        // ====================================================================
    }

    @keyframes slideOutRight {
        // ====================================================================
        // Animación de SALIDA (opuesta a slideInRight)
        // ====================================================================

        from {
            // Estado inicial: Posición normal, visible
            transform: translateX(0);
            opacity: 1;
        }

        to {
            // Estado final: Fuera de pantalla (derecha), invisible
            transform: translateX(100%);
            opacity: 0;
        }

        // ====================================================================
        // ANIMACIÓN RESULTANTE:
        // - Toast empieza en posición normal
        // - Se desliza hacia la derecha mientras desaparece
        // - Termina fuera de pantalla e invisible
        // - Se ejecuta antes de eliminar del DOM
        // ====================================================================
    }
`;

// Agregar estilos al <head> del documento
document.head.appendChild(notificationStyles);
// ============================================================================
// document.head.appendChild() - Insertar <style> en <head>
// ============================================================================
// ¿QUÉ HACE?
// - Agrega el elemento <style> al <head>
// - Los estilos ahora están activos
// - Las animaciones @keyframes están disponibles
//
// RESULTADO EN HTML:
// <head>
//   ...estilos existentes...
//   <style>
//     @keyframes slideInRight {...}
//     @keyframes slideOutRight {...}
//   </style>
// </head>
//
// MOMENTO DE EJECUCIÓN:
// - Se ejecuta al cargar el archivo
// - Los estilos están disponibles inmediatamente
// - Toast puede usar las animaciones desde el primer notify()
// ============================================================================

// Confirmación de carga
console.log('✅ NotificationStrategy.js cargado');
// ============================================================================
// Mensaje de confirmación
// - Útil para verificar que archivo se cargó correctamente
// - Aparece en consola del navegador
// - Buena práctica en desarrollo
// ============================================================================

// ============================================================================
// RESUMEN DE CONCEPTOS EXPLICADOS:
// ============================================================================
//
// PATRÓN STRATEGY:
// - Clase base (interfaz)
// - Múltiples implementaciones (estrategias)
// - Context (gestor)
// - Cambio dinámico de estrategia
//
// HERENCIA:
// - extends (herencia de clases)
// - Sobrescritura de métodos
// - Polimorfismo
// - Clase base abstracta (simulada)
//
// DOM MANIPULATION AVANZADO:
// - document.createElement()
// - document.body.appendChild()
// - Object.assign() para estilos
// - .textContent vs .innerHTML
// - .remove() para eliminar elementos
//
// ESTILOS CSS INLINE:
// - position: fixed
// - z-index
// - box-shadow
// - border-radius
// - animation
//
// ANIMACIONES:
// - @keyframes
// - transform (translateX)
// - opacity
// - timing functions (ease, linear)
//
// TEMPORIZADORES:
// - setTimeout()
// - Callbacks
// - Closure
// - setTimeout anidado
//
// THROW Y ERRORES:
// - throw new Error()
// - Simular métodos abstractos
// - Forzar implementación en clases hijas
//
// PRINCIPIOS SOLID:
// - Open/Closed (agregar estrategias sin modificar código)
// - Single Responsibility (cada estrategia una responsabilidad)
// - Liskov Substitution (estrategias intercambiables)
//
// ============================================================================
