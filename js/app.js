// ============================================================================
// ARCHIVO: app.js  
// UBICACIÓN: js/app.js
// PROPÓSITO: Lógica principal del frontend - Sistema CRUD de Usuarios
// 
// DESCRIPCIÓN:
// Este archivo implementa toda la lógica del lado del cliente para gestionar
// usuarios, incluyendo: crear, leer, actualizar y eliminar (CRUD).
//
// TECNOLOGÍAS: JavaScript ES6+, Fetch API, Async/Await, DOM Manipulation
// PATRONES: Observer, Strategy, Template Method
// PRINCIPIOS SOLID: SRP, OCP, DIP
// ============================================================================

// ============================================================================
// SECCIÓN 1: CONFIGURACIÓN GLOBAL
// ============================================================================

const API_URL = 'php/api/';
// ============================================================================
// CONSTANTE API_URL - Ruta base de la API
// ============================================================================
// ¿Qué es const?
// - Declara una CONSTANTE (no se puede reasignar)
// - const valor = 10; valor = 20; // ❌ ERROR
//
// ¿Para qué sirve API_URL?
// - Ruta base del backend
// - Se concatena con endpoints: API_URL + 'get_users.php'
// - Si cambia la ruta, solo modificas aquí
//
// Convención: Constantes globales en SCREAMING_SNAKE_CASE
// ============================================================================

// ============================================================================
// SECCIÓN 2: ESTADO DE LA APLICACIÓN  
// ============================================================================

let usuarios = [];
// ============================================================================
// VARIABLE usuarios - Lista de usuarios cargados
// ============================================================================
// Tipo: Array de objetos
// Contenido: [{id: 1, nombre: 'Juan', email: 'juan@email.com'}, ...]
//
// ¿Por qué global?
// - Múltiples funciones necesitan acceso
// - loadUsers() la llena
// - renderUsersTable() la lee
// - editUser() la busca
// ============================================================================

let usuarioEnEdicion = null;
// ============================================================================
// VARIABLE usuarioEnEdicion - ID del usuario en edición
// ============================================================================
// Valores posibles:
// - null: Modo CREAR (formulario vacío)
// - 5: Modo EDITAR (editando usuario con ID 5)
//
// Flujo:
// CREAR: usuarioEnEdicion = null → botón "Crear Usuario"
// EDITAR: usuarioEnEdicion = 5 → botón "Actualizar Usuario"
// ============================================================================

// ============================================================================
// SECCIÓN 3: INICIALIZACIÓN
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // ========================================================================
    // EVENT LISTENER: DOMContentLoaded
    // ========================================================================
    // ¿Qué hace?
    // - Espera a que el HTML se cargue completamente
    // - Luego ejecuta el código
    //
    // ¿Por qué?
    // - Si intentas acceder a elementos antes de que existan, obtienes null
    // - Garantiza que todos los elementos HTML están disponibles
    //
    // Diferencia:
    // DOMContentLoaded → HTML listo (más rápido)
    // load → HTML + imágenes + CSS listos (más lento)
    // ========================================================================

    console.log('🚀 Sistema de Usuarios iniciado');
    // ========================================================================
    // console.log() - Imprimir en consola para debugging
    // Ver en: F12 → Console
    // ========================================================================

    document.getElementById('user-form').addEventListener('submit', handleSubmit);
    // ========================================================================
    // Configurar formulario
    // 1. getElementById('user-form') → Obtiene el formulario
    // 2. addEventListener('submit', handleSubmit) → Escucha envío
    // 3. Cuando se envía, ejecuta handleSubmit()
    //
    // Nota: handleSubmit sin paréntesis (pasa referencia, no ejecuta)
    // ========================================================================

    loadUsers();
    // ========================================================================
    // Cargar usuarios iniciales
    // - Se ejecuta al cargar la página
    // - Usuario ve datos inmediatamente
    // ========================================================================
});

// ============================================================================
// SECCIÓN 4: OPERACIONES CRUD
// ============================================================================

async function loadUsers() {
    // ========================================================================
    // FUNCIÓN: loadUsers()
    // OPERACIÓN: READ (Leer usuarios)
    // ========================================================================
    // ¿Qué es async?
    // - Declara función asíncrona
    // - Permite usar await dentro
    // - Siempre devuelve Promise
    //
    // ¿Por qué async?
    // - fetch() toma tiempo (petición HTTP)
    // - await espera sin bloquear la página
    // ========================================================================

    showLoading(true);
    // ========================================================================
    // Mostrar spinner de carga
    // - Feedback visual al usuario
    // - Indica que algo está procesándose
    // ========================================================================

    try {
        // ====================================================================
        // TRY-CATCH: Manejo de errores
        // try: Código que puede fallar
        // catch: Qué hacer si falla
        // finally: Se ejecuta siempre
        // ====================================================================

        const params = new URLSearchParams();
        // ====================================================================
        // URLSearchParams - Construir query string
        // ====================================================================
        // Propósito: Crear parámetros de URL (?clave=valor&otra=valor2)
        //
        // Métodos:
        // params.append('clave', 'valor') → Agregar parámetro  
        // params.toString() → Convertir a string
        //
        // Ejemplo:
        // params.append('busqueda', 'Juan');
        // params.append('tipo', 'admin');
        // params.toString() → "busqueda=Juan&tipo=admin"
        //
        // Ventaja: Encoding automático de caracteres especiales
        // ====================================================================

        const busqueda = document.getElementById('filter-search').value;
        if (busqueda) params.append('busqueda', busqueda);
        // ====================================================================
        // Obtener valor del input y agregarlo si existe
        //
        // .value → Propiedad del input con el texto
        // if (busqueda) → Solo agrega si tiene valor (truthy)
        //
        // Valores truthy: "Juan", "0", 1, []
        // Valores falsy: "", 0, null, undefined, false
        // ====================================================================

        const tipo = document.getElementById('filter-tipo').value;
        if (tipo) params.append('tipo_usuario', tipo);

        const activo = document.getElementById('filter-activo').value;
        if (activo) params.append('activo', activo);

        params.append('orden', 'id');
        params.append('direccion', 'DESC');
        // ====================================================================
        // Parámetros de ordenamiento
        // orden=id&direccion=DESC → Ordena por ID descendente
        // DESC: 5, 4, 3, 2, 1 (más recientes primero)
        // ASC: 1, 2, 3, 4, 5 (más antiguos primero)
        // ====================================================================

        const url = `${API_URL}get_users.php?${params.toString()}`;
        // ====================================================================
        // Template literal - Construir URL completa
        // ====================================================================
        // Sintaxis: `string ${variable}`
        // - Backticks (`) permiten interpolación
        // - ${variable} inserta valor de la variable
        //
        // Resultado ejemplo:
        // "php/api/get_users.php?busqueda=Juan&orden=id&direccion=DESC"
        // ====================================================================

        const response = await fetch(url);
        // ====================================================================
        // fetch() - Hacer petición HTTP
        // ====================================================================
        // ¿Qué hace?
        // - Envía petición GET al servidor
        // - await espera la respuesta
        // - Devuelve objeto Response
        //
        // Métodos HTTP:
        // fetch(url) → GET (por defecto)
        // fetch(url, {method: 'POST'}) → POST
        // fetch(url, {method: 'PUT'}) → PUT
        // fetch(url, {method: 'DELETE'}) → DELETE
        //
        // Response contiene:
        // - response.ok → boolean (true si status 200-299)
        // - response.status → número (200, 404, 500)
        // - response.json() → Promise que parsea JSON
        // ====================================================================

        const data = await response.json();
        // ====================================================================
        // Parsear respuesta JSON
        // ====================================================================
        // response.json():
        // - Lee el body (string JSON)
        // - Lo convierte a objeto JavaScript  
        // - Devuelve Promise (por eso await)
        //
        // Transformación:
        // String: '{"success": true, "data": [{"id": 1}]}'
        // Objeto: {success: true, data: [{id: 1}]}
        //
        // Ahora podemos acceder:
        // data.success → true
        // data.data → [{id: 1}]
        // ====================================================================

        if (!data.success) {
            throw new Error(data.error || 'Error al cargar usuarios');
        }
        // ====================================================================
        // Verificar éxito de la operación
        //
        // !data.success → if (data.success === false)
        // throw → Lanza error, salta a catch
        // data.error || 'Error...' → Usa error del servidor o mensaje por defecto
        // ====================================================================

        usuarios = data.data;
        updateStats(data.stats);
        renderUsersTable();
        // ====================================================================
        // Actualizar estado y UI
        // 1. usuarios = data.data → Guardar en variable global
        // 2. updateStats() → Actualizar tarjetas de estadísticas
        // 3. renderUsersTable() → Generar HTML de la tabla
        // ====================================================================

    } catch (error) {
        console.error('Error:', error);
        showError('Error al cargar usuarios: ' + error.message);
        // ====================================================================
        // Manejar errores
        // - console.error() → Registrar en consola (color rojo)
        // - showError() → Mostrar mensaje al usuario en la UI
        // ====================================================================

    } finally {
        showLoading(false);
        // ====================================================================
        // finally se ejecuta SIEMPRE
        // - Con éxito o con error
        // - Oculta el loading en ambos casos
        // ====================================================================
    }
}

async function handleSubmit(e) {
    // ========================================================================
    // FUNCIÓN: handleSubmit()
    // PROPÓSITO: Manejar envío del formulario
    // ========================================================================

    e.preventDefault();
    // ========================================================================
    // Prevenir recarga de página
    // - Sin esto, la página se recargaría al enviar el formulario
    // - Con esto, JavaScript maneja todo
    // ========================================================================

    hideError();

    const formData = {
        nombre: document.getElementById('nombre').value.trim(),
        email: document.getElementById('email').value.trim(),
        tipo_usuario: document.getElementById('tipo_usuario').value
    };
    // ========================================================================
    // Obtener datos del formulario
    // - .trim() elimina espacios al inicio/fin
    // - Crea objeto con los datos
    // ========================================================================

    const validator = new UserValidator();
    if (!validator.validateNombre(formData.nombre)) {
        showError(validator.getErrors().join(', '));
        return;
    }
    if (!validator.validateEmail(formData.email)) {
        showError(validator.getErrors().join(', '));
        return;
    }
    // ========================================================================
    // Validar datos
    // - UserValidator es una clase separada (patrón Strategy)
    // - Si validación falla, muestra error y termina
    // - return detiene la ejecución
    // ========================================================================

    const userId = document.getElementById('user-id').value;
    const isEdit = userId && userId !== '';
    // ========================================================================
    // Detectar modo: CREAR o EDITAR
    // - userId vacío → CREAR
    // - userId con valor → EDITAR
    // ========================================================================

    if (isEdit) {
        formData.id = parseInt(userId);
        await updateUser(formData);
    } else {
        await createUser(formData);
    }
}

async function createUser(userData) {
    // ========================================================================
    // FUNCIÓN: createUser()
    // OPERACIÓN: CREATE (Crear usuario)
    // ========================================================================

    const button = document.getElementById('btn-submit');
    button.disabled = true;
    button.innerHTML = '⏳ Creando...';
    // ========================================================================
    // Deshabilitar botón mientras procesa
    // - Evita clics múltiples
    // - Cambia texto para feedback
    // ========================================================================

    try {
        const response = await fetch(`${API_URL}create_user.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        // ====================================================================
        // fetch() con método POST
        // ====================================================================
        // Configuración:
        // - method: 'POST' → Enviar datos al servidor
        // - headers → Content-Type indica que enviamos JSON
        // - body → JSON.stringify() convierte objeto a string JSON
        //
        // JSON.stringify():
        // Objeto: {nombre: "Juan", email: "juan@email.com"}
        // String: '{"nombre":"Juan","email":"juan@email.com"}'
        // ====================================================================

        const data = await response.json();

        if (!data.success) {
            if (data.errors) {
                showError(data.errors.join(', '));
            } else {
                showError(data.error || 'Error al crear usuario');
            }
            return;
        }

        showNotification('success', '✅ Usuario creado exitosamente');
        document.getElementById('user-form').reset();
        await loadUsers();
        // ====================================================================
        // Si tiene éxito:
        // 1. Mostrar notificación verde
        // 2. Limpiar formulario con reset()
        // 3. Recargar lista de usuarios
        // ====================================================================

    } catch (error) {
        console.error('Error:', error);
        showError('Error al crear usuario: ' + error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = '➕ Crear Usuario';
        // ====================================================================
        // Restaurar botón
        // - Habilitar de nuevo
        // - Cambiar texto original
        // ====================================================================
    }
}

async function updateUser(userData) {
    // ========================================================================
    // FUNCIÓN: updateUser()
    // OPERACIÓN: UPDATE (Actualizar usuario)
    // Similar a createUser() pero usa método PUT
    // ========================================================================

    const button = document.getElementById('btn-submit');
    button.disabled = true;
    button.innerHTML = '⏳ Actualizando...';

    try {
        const response = await fetch(`${API_URL}update_user.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        // ====================================================================
        // Método PUT para actualizar
        // - PUT: Actualizar recurso completo
        // - PATCH: Actualizar parcialmente (no usado aquí)
        // ====================================================================

        const data = await response.json();

        if (!data.success) {
            if (data.errors) {
                showError(data.errors.join(', '));
            } else {
                showError(data.error || 'Error al actualizar usuario');
            }
            return;
        }

        showNotification('success', '✅ Usuario actualizado exitosamente');
        cancelEdit();
        await loadUsers();

    } catch (error) {
        console.error('Error:', error);
        showError('Error al actualizar usuario: ' + error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = '💾 Guardar Cambios';
    }
}

async function deleteUser(id) {
    // ========================================================================
    // FUNCIÓN: deleteUser()
    // OPERACIÓN: DELETE (Eliminar usuario)
    // ========================================================================

    if (!confirm('¿Estás seguro de que quieres eliminar este usuario?')) {
        return;
    }
    // ========================================================================
    // confirm() - Diálogo de confirmación
    // - Muestra ventana con OK/Cancelar
    // - Devuelve true si OK, false si Cancelar
    // - Si false, return detiene la ejecución
    // ========================================================================

    try {
        const response = await fetch(`${API_URL}delete_user.php?id=${id}`, {
            method: 'DELETE'
        });
        // ====================================================================
        // Método DELETE
        // - URL incluye ID como parámetro: ?id=5
        // - No lleva body (solo se envía el ID)
        // ====================================================================

        const data = await response.json();

        if (!data.success) {
            showError(data.error || 'Error al eliminar usuario');
            return;
        }

        showNotification('success', '✅ Usuario eliminado exitosamente');
        await loadUsers();

    } catch (error) {
        console.error('Error:', error);
        showError('Error al eliminar usuario: ' + error.message);
    }
}

function renderUsersTable() {
    // ========================================================================
    // FUNCIÓN: renderUsersTable()
    // PROPÓSITO: Generar HTML de la tabla de usuarios
    // PATRÓN: Template Method
    // ========================================================================

    const tbody = document.getElementById('users-tbody');
    const noResults = document.getElementById('no-results');

    if (usuarios.length === 0) {
        tbody.innerHTML = '';
        noResults.classList.remove('hidden');
        return;
    }
    // ========================================================================
    // Si no hay usuarios, mostrar mensaje "No hay resultados"
    // ========================================================================

    noResults.classList.add('hidden');

    tbody.innerHTML = usuarios.map(user => {
        // ====================================================================
        // Array.map() - Transformar cada elemento
        // ====================================================================
        // ¿Qué hace?
        // - Recorre el array usuarios
        // - Para cada usuario, ejecuta la función
        // - Devuelve un NUEVO array con los resultados
        //
        // Ejemplo:
        // usuarios = [{id: 1, nombre: 'Juan'}, {id: 2, nombre: 'María'}]
        // .map(user => `<tr>${user.nombre}</tr>`)
        // Resultado: ['<tr>Juan</tr>', '<tr>María</tr>']
        //
        // .join('') une el array en un string:
        // '<tr>Juan</tr><tr>María</tr>'
        // ====================================================================

        const iniciales = getIniciales(user.nombre);
        const tipoBadge = user.tipo_usuario === 'admin' ? 'badge-admin' : 'badge-normal';
        const tipoTexto = user.tipo_usuario === 'admin' ? '🔑 Admin' : '👤 Normal';
        const estadoBadge = user.activo ? 'badge-active' : 'badge-inactive';
        const estadoTexto = user.activo ? 'Activo' : 'Inactivo';
        const fecha = formatDate(user.fecha_creacion);
        // ====================================================================
        // Preparar datos para la fila
        // - Operador ternario: condicion ? siTrue : siFalse
        // - Ejemplo: user.activo ? 'Activo' : 'Inactivo'
        //   Si activo=true → 'Activo', si false → 'Inactivo'
        // ====================================================================

        return `
            <tr>
                <td>
                    <div class="user-avatar">${iniciales}</div>
                </td>
                <td>
                    <div class="user-name">${user.nombre}</div>
                    <div class="user-email">${user.email}</div>
                </td>
                <td>
                    <span class="badge ${tipoBadge}">${tipoTexto}</span>
                </td>
                <td>
                    <span class="badge ${estadoBadge}">${estadoTexto}</span>
                </td>
                <td>${fecha}</td>
                <td>
                    <button onclick="editUser(${user.id})" class="btn-icon" title="Editar">
                        ✏️
                    </button>
                    <button onclick="deleteUser(${user.id})" class="btn-icon btn-danger" title="Eliminar">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
        // ====================================================================
        // Template literal multi-línea
        // - Backticks permiten saltos de línea
        // - ${variable} inserta valores dinámicamente
        // - Genera una fila <tr> completa por usuario
        // ====================================================================

    }).join('');
    // ========================================================================
    // .join('') - Unir array en string
    // - map() devuelve array de strings HTML
    // - join('') los une sin separador
    // - innerHTML lo inserta en el DOM
    // ========================================================================
}

function editUser(id) {
    // ========================================================================
    // FUNCIÓN: editUser()
    // PROPÓSITO: Cargar datos del usuario en el formulario para editar
    // ========================================================================

    const usuario = usuarios.find(u => u.id === id);
    // ========================================================================
    // Array.find() - Buscar elemento que cumpla condición
    // ========================================================================
    // ¿Qué hace?
    // - Recorre usuarios[]
    // - Devuelve el PRIMER elemento donde u.id === id sea true
    // - Si no encuentra, devuelve undefined
    //
    // Arrow function: u => u.id === id
    // - u: Parámetro (cada usuario)
    // - => Operador arrow function
    // - u.id === id: Condición a cumplir
    //
    // Equivalente:
    // usuarios.find(function(u) {
    //     return u.id === id;
    // });
    // ========================================================================

    if (!usuario) return;

    document.getElementById('nombre').value = usuario.nombre;
    document.getElementById('email').value = usuario.email;
    document.getElementById('tipo_usuario').value = usuario.tipo_usuario;
    document.getElementById('user-id').value = usuario.id;
    // ========================================================================
    // Llenar formulario con datos del usuario
    // - Asignar valores a cada input
    // - user-id es campo hidden que guarda el ID
    // ========================================================================

    usuarioEnEdicion = id;

    document.getElementById('form-title').textContent = '✏️ Editar Usuario';
    document.getElementById('btn-submit').textContent = '💾 Guardar Cambios';
    document.getElementById('btn-cancel').classList.remove('hidden');
    // ========================================================================
    // Cambiar interfaz a modo EDITAR
    // - Cambiar título del formulario
    // - Cambiar texto del botón
    // - Mostrar botón Cancelar
    // ========================================================================
}

function cancelEdit() {
    // ========================================================================
    // FUNCIÓN: cancelEdit()
    // PROPÓSITO: Cancelar edición y volver a modo CREAR
    // ========================================================================

    document.getElementById('user-form').reset();
    document.getElementById('user-id').value = '';
    usuarioEnEdicion = null;

    document.getElementById('form-title').textContent = '➕ Agregar Nuevo Usuario';
    document.getElementById('btn-submit').textContent = '➕ Crear Usuario';
    document.getElementById('btn-cancel').classList.add('hidden');
    hideError();
    // ========================================================================
    // Restaurar formulario a modo CREAR
    // - reset() limpia todos los campos
    // - Cambiar títulos y textos
    // - Ocultar botón Cancelar
    // - Limpiar errores
    // ========================================================================
}

// ============================================================================
// FUNCIONES DE UTILIDAD
// ============================================================================

function getIniciales(nombre) {
    return nombre
        .split(' ')
        .map(palabra => palabra[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
    // ========================================================================
    // Obtener iniciales de un nombre
    // ========================================================================
    // Ejemplo: "Juan Pérez" → "JP"
    //
    // Paso a paso:
    // 1. .split(' ') → ['Juan', 'Pérez']
    // 2. .map(p => p[0]) → ['J', 'P'] (primer caracter de cada palabra)
    // 3. .join('') → 'JP'
    // 4. .toUpperCase() → 'JP' (mayúsculas)
    // 5. .slice(0, 2) → 'JP' (máximo 2 caracteres)
    // ========================================================================
}

function formatDate(fechaString) {
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    // ========================================================================
    // Formatear fecha
    // ========================================================================
    // Entrada: "2024-01-15 10:30:00"
    // Salida: "15 ene 2024"
    //
    // new Date() → Crear objeto Date
    // toLocaleDateString() → Formatear según idioma
    // 'es-ES' → Español de España
    // ========================================================================
}

function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total || 0;
    document.getElementById('stat-admins').textContent = stats.admins || 0;
    document.getElementById('stat-normales').textContent = stats.normales || 0;
    document.getElementById('stat-activos').textContent = stats.activos || 0;
    // ========================================================================
    // Actualizar tarjetas de estadísticas
    // - stats.total || 0: Si total es undefined, usa 0
    // - textContent: Cambiar texto del elemento
    // ========================================================================
}

function showError(mensaje) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = mensaje;
    errorDiv.classList.remove('hidden');
}

function hideError() {
    document.getElementById('error-message').classList.add('hidden');
}

function showNotification(tipo, mensaje) {
    // Implementación de notificación temporal (toast)
    const notification = document.createElement('div');
    notification.className = `notification notification-${tipo}`;
    notification.textContent = mensaje;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function showLoading(show) {
    const loading = document.getElementById('loading');
    if (show) {
        loading.classList.remove('hidden');
    } else {
        loading.classList.add('hidden');
    }
}

function clearFilters() {
    document.getElementById('filter-search').value = '';
    document.getElementById('filter-tipo').value = '';
    document.getElementById('filter-activo').value = '';
    loadUsers();
}

// ============================================================================
// RESUMEN DE CONCEPTOS EXPLICADOS:
// ============================================================================
//
// JAVASCRIPT MODERNO:
// - const/let (variables y constantes)
// - async/await (código asíncrono)
// - Template literals (`string ${variable}`)
// - Arrow functions (param => resultado)
// - Array methods (.map, .find, .filter, .join)
//
// DOM MANIPULATION:
// - getElementById (obtener elementos)
// - addEventListener (escuchar eventos)
// - innerHTML / textContent (modificar contenido)
// - classList (agregar/quitar clases)
// - .value (leer/escribir inputs)
//
// HTTP / API:
// - fetch() (peticiones HTTP)
// - Métodos: GET, POST, PUT, DELETE
// - Headers y Content-Type
// - JSON.stringify() / response.json()
// - URLSearchParams (query strings)
//
// MANEJO DE ERRORES:
// - try-catch-finally
// - throw new Error()
// - console.error()
//
// EVENTOS:
// - DOMContentLoaded
// - submit
// - preventDefault()
//
// PATRONES:
// - Observer (estadísticas)
// - Strategy (validación)
// - Template Method (renderizado)
//
// PRINCIPIOS SOLID:
// - Single Responsibility
// - Open/Closed
// - Dependency Inversion
//
// ============================================================================
