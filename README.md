# 📋 Sistema de Gestión de Usuarios - Actividad 2 Detallado

Sistema CRUD completo que demuestra el uso de **Patrones de Diseño** y **Principios SOLID** en un stack full-stack (HTML5 + JavaScript + PHP + MySQL).

## 🎯 Objetivo

Este proyecto es la implementación práctica de la **Actividad 2: Catálogo de Patrones y SOLID**. Demuestra cómo aplicar patrones y principios en un sistema real.

## 🏗️ Arquitectura del Proyecto

```
proyecto-usuarios/
├── index.html                      # Página principal
├── css/
│   └── styles.css                  # Estilos completos
├── js/
│   ├── app.js                      # Lógica principal del frontend
│   ├── patterns/
│   │   └── NotificationStrategy.js # Patrón Strategy
│   └── validators/
│       └── UserValidator.js        # Validación cliente
├── php/
│   ├── config/
│   │   └── Database.php            # Singleton - Conexión BD
│   ├── models/
│   │   └── User.php                # Modelo + Factory
│   ├── validators/
│   │   └── UserValidator.php       # Validación servidor
│   ├── repositories/
│   │   └── UserRepository.php      # Repository pattern
│   ├── services/
│   │   └── NotificationManager.php # Strategy + Observer
│   └── api/
│       ├── create_user.php         # API: Crear usuario
│       ├── get_users.php           # API: Listar usuarios
│       ├── update_user.php         # API: Actualizar usuario
│       └── delete_user.php         # API: Eliminar usuario
└── sql/
    └── schema.sql                  # Script de base de datos
```

## 🎨 Patrones de Diseño Implementados

### 1. **Singleton** (Creacional)
- **Ubicación**: `php/config/Database.php`
- **Propósito**: Garantizar una única conexión a la BD
- **Beneficio**: Ahorro de recursos y conexiones

### 2. **Factory Method** (Creacional)
- **Ubicación**: `php/models/User.php` (clase UserFactory)
- **Propósito**: Crear diferentes tipos de usuarios (User, AdminUser)
- **Beneficio**: Centraliza la lógica de creación

### 3. **Strategy** (Comportamiento)
- **Ubicación**: 
  - `php/services/NotificationManager.php`
  - `js/patterns/NotificationStrategy.js`
- **Propósito**: Diferentes estrategias de notificación (Email, SMS, Push)
- **Beneficio**: Algoritmos intercambiables en tiempo de ejecución

### 4. **Observer** (Comportamiento)
- **Ubicación**: `php/services/NotificationManager.php`
- **Propósito**: Notificar cambios a múltiples observadores (DatabaseLogger, FileLogger)
- **Beneficio**: Bajo acoplamiento entre componentes

### 5. **Repository** (Estructural)
- **Ubicación**: `php/repositories/UserRepository.php`
- **Propósito**: Abstracción de la capa de datos
- **Beneficio**: Separación de responsabilidades

## 🏛️ Principios SOLID Aplicados

### S - Single Responsibility Principle
✅ **Ejemplo**: Cada clase tiene UNA responsabilidad
- `User.php` → Solo datos del usuario
- `UserValidator.php` → Solo validación
- `UserRepository.php` → Solo persistencia

### O - Open/Closed Principle
✅ **Ejemplo**: `UserFactory` permite extender sin modificar
- Agregar nuevos tipos de usuarios (GuestUser, PremiumUser) sin cambiar código existente

### L - Liskov Substitution Principle
✅ **Ejemplo**: `AdminUser extends User`
- AdminUser puede reemplazar a User sin romper funcionalidad

### I - Interface Segregation Principle
✅ **Ejemplo**: `EmailValidatorInterface`, `TextValidatorInterface`
- Interfaces pequeñas y específicas en lugar de una gigante

### D - Dependency Inversion Principle
✅ **Ejemplo**: `UserRepository` depende de `Database` (abstracción)
- No depende directamente de MySQL

## 🚀 Instalación y Configuración

### Prerrequisitos
- PHP 7.4 o superior
- MySQL 8.0 o superior
- Servidor web (Apache/Nginx) o PHP built-in server

### Paso 1: Importar Base de Datos

```bash
mysql -u root -p < sql/schema.sql
```

O importar manualmente `sql/schema.sql` en phpMyAdmin.

### Paso 2: Configurar Conexión

Editar `php/config/Database.php` si es necesario:

```php
private $host = 'localhost';
private $database = 'sistema_usuarios';
private $username = 'root';
private $password = '';
```

### Paso 3: Iniciar Servidor

**Opción A: PHP Built-in Server**
```bash
cd proyecto-usuarios
php -S localhost:8000
```

**Opción B: XAMPP/WAMP**
Copiar el proyecto a `htdocs/proyecto-usuarios`

### Paso 4: Acceder

Abrir en el navegador:
```
http://localhost:8000
```

## 📝 Funcionalidades

### ✅ Crear Usuarios
- Formulario con validación cliente y servidor
- Tipos: Normal y Administrador
- Notificación automática por email (simulado)

### ✅ Listar Usuarios
- Tabla con todos los usuarios
- Muestra iniciales, nombre, email, tipo, estado, fecha
- Estadísticas en tiempo real

### ✅ Filtrar y Buscar
- Búsqueda por nombre o email
- Filtro por tipo (Admin/Normal)
- Filtro por estado (Activo/Inactivo)

### ✅ Actualizar Usuarios
- Edición inline desde la tabla
- Validación completa
- Previene emails duplicados

### ✅ Eliminar Usuarios
- Soft delete (marca como inactivo)
- Confirmación antes de eliminar

## 🔍 Demostración de Patrones

### Ejemplo: Crear Usuario

```php
// 1. Validar (Single Responsibility)
$validator = new UserValidator();
$validator->validateUserData($data);

// 2. Crear usuario con Factory (Factory Method)
$user = UserFactory::create($data);

// 3. Guardar con Repository (Repository Pattern)
$repository = new UserRepository();
$userId = $repository->create($user);

// 4. Notificar con Strategy + Observer
$notificationManager = new NotificationManager(new EmailNotification());
$notificationManager->addObserver(new DatabaseLogger());
$notificationManager->notify($user->getEmail(), "Bienvenido!", $userId);
```

## 🧪 Probar el Sistema

### Test 1: Crear Usuario Normal
1. Llenar formulario con:
   - Nombre: "Juan Pérez"
   - Email: "juan@test.com"
   - Tipo: Normal
2. Verificar que aparece en la tabla
3. Revisar que las estadísticas se actualizan

### Test 2: Crear Usuario Admin
1. Crear usuario tipo Administrador
2. Verificar badge azul "🔑 Admin"

### Test 3: Editar Usuario
1. Click en botón "✏️ Editar"
2. Modificar nombre
3. Guardar cambios
4. Verificar actualización en tabla

### Test 4: Filtros
1. Crear varios usuarios (normal y admin)
2. Usar filtros para mostrar solo admins
3. Buscar por nombre

### Test 5: Eliminar
1. Eliminar un usuario
2. Verificar que se marca como inactivo
3. Filtrar por inactivos para verlo

## 📊 Base de Datos

### Tabla: usuarios
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nombre (VARCHAR 100)
- email (VARCHAR 150, UNIQUE)
- tipo_usuario (ENUM 'admin', 'normal')
- fecha_creacion (TIMESTAMP)
- fecha_actualizacion (TIMESTAMP)
- activo (BOOLEAN)
```

### Tabla: logs_notificaciones
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FOREIGN KEY)
- tipo_notificacion (ENUM 'email', 'sms', 'push')
- mensaje (TEXT)
- enviado (BOOLEAN)
- fecha_envio (TIMESTAMP)
```

## 🎓 Para tu Catálogo Personal

Este proyecto te sirve como:

1. **Referencia de código** - Código funcional y bien comentado
2. **Ejemplos de patrones** - Implementación real de 5 patrones
3. **SOLID en acción** - Los 5 principios aplicados
4. **Base para experimentar** - Puedes modificar y extender

### Ejercicios de Extensión

1. **Agregar patrón Decorator**: Agregar decoradores para usuarios (UserWithAvatar, UserWithNotifications)
2. **Agregar patrón Adapter**: Integrar con una API externa de emails real
3. **Mejorar Strategy**: Agregar más estrategias de notificación (Telegram, WhatsApp)
4. **Implementar Command**: Crear sistema de deshacer/rehacer

## 🐛 Solución de Problemas

### Error: "Error de conexión a BD"
- Verificar que MySQL esté corriendo
- Revisar credenciales en `Database.php`
- Asegurarse de que la BD `sistema_usuarios` existe

### Error: "Failed to fetch"
- Verificar que el servidor PHP esté corriendo
- Verificar rutas en `app.js` (const API_URL)

### Error: "Table doesn't exist"
- Importar `sql/schema.sql` en MySQL

## 📚 Recursos de Aprendizaje

- [Patrones de Diseño - Refactoring Guru](https://refactoring.guru/es/design-patterns)
- [SOLID Principles](https://www.digitalocean.com/community/conceptual_articles/s-o-l-i-d-the-first-five-principles-of-object-oriented-design)
- [PHP The Right Way](https://phptherightway.com/)

## 👨‍💻 Autor

Actividad 2 - Módulo de Patrones de Diseño de Software
Stack: HTML5 + JavaScript + PHP + MySQL

---

**💡 Consejo**: Usa este proyecto como base para tu catálogo. Estudia cada archivo, entiende cómo se aplican los patrones y luego documéntalos con tus propias palabras.

**🎯 Siguiente paso**: Comparar este código con el de la Actividad 1 (sin patrones) y documentar las mejoras.
