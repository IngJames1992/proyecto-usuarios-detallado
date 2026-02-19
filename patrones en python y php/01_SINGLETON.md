# PRÁCTICA 1: PATRÓN SINGLETON

## ESTRUCTURA DEL PATRÓN

```
┌─────────────────────────────┐
│      CLASE SINGLETON        │
├─────────────────────────────┤
│ - instance (static)         │  ← Variable estática privada
│ - __construct() (private)   │  ← Constructor privado
├─────────────────────────────┤
│ + getInstance() (static)    │  ← Método público para obtener instancia
│ + tuMetodo()                │  ← TU CÓDIGO VA AQUÍ
└─────────────────────────────┘
```

---

## EJEMPLO 1: PYTHON

### ESTRUCTURA BASE
```python
class MiSingleton:
    _instance = None  # ← Variable estática
    
    def __new__(cls):
        # Crear instancia solo si no existe
        if cls._instance is None:
            cls._instance = super().__new__(cls)
            # ============================================
            # AQUÍ VA TU CÓDIGO DE INICIALIZACIÓN
            # ============================================
            cls._instance._inicializar()
        return cls._instance
    
    def _inicializar(self):
        # ============================================
        # AQUÍ PONES LOS DATOS/CONFIGURACIÓN
        # ============================================
        self.dato = "valor inicial"
    
    # ============================================
    # AQUÍ VAN TUS MÉTODOS DE NEGOCIO
    # ============================================
    def hacer_algo(self):
        return self.dato
```

### EJERCICIO: Gestor de Configuración

**Objetivo:** Crear un Singleton que guarde configuración de la app

```python
class ConfigManager:
    _instance = None
    
    def __new__(cls):
        if cls._instance is None:
            cls._instance = super().__new__(cls)
            cls._instance._inicializar()
        return cls._instance
    
    def _inicializar(self):
        # ============================================
        # TU CÓDIGO: Define las configuraciones aquí
        # ============================================
        self.config = {
            'app_name': 'Mi App',
            'version': '1.0',
            'debug': True
        }
    
    # ============================================
    # TU CÓDIGO: Agrega métodos para obtener/cambiar config
    # ============================================
    def get(self, key):
        return self.config.get(key)
    
    def set(self, key, value):
        self.config[key] = value

# PRUEBA
config1 = ConfigManager()
print(config1.get('app_name'))  # Mi App

config2 = ConfigManager()
config2.set('app_name', 'Nueva App')

print(config1.get('app_name'))  # Nueva App ← ¡Son el mismo objeto!
print(config1 is config2)  # True
```

---

## EJEMPLO 2: PHP

### ESTRUCTURA BASE
```php
<?php
class MiSingleton {
    private static $instance = null;  // ← Variable estática
    
    // Constructor privado
    private function __construct() {
        // ============================================
        // AQUÍ VA TU CÓDIGO DE INICIALIZACIÓN
        // ============================================
        $this->dato = "valor inicial";
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Obtener instancia única
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ============================================
    // AQUÍ VAN TUS MÉTODOS DE NEGOCIO
    // ============================================
    public function hacerAlgo() {
        return $this->dato;
    }
}
?>
```

### EJERCICIO: Conexión a Base de Datos

**Objetivo:** Crear un Singleton para la conexión MySQL

```php
<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // ============================================
        // TU CÓDIGO: Configura la conexión aquí
        // ============================================
        $host = 'localhost';
        $db   = 'mi_base';
        $user = 'root';
        $pass = '';
        
        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$db",
                $user,
                $pass
            );
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
    
    private function __clone() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ============================================
    // TU CÓDIGO: Agrega métodos para usar la conexión
    // ============================================
    public function query($sql) {
        return $this->connection->query($sql);
    }
}

// PRUEBA
$db1 = Database::getInstance();
$db2 = Database::getInstance();

var_dump($db1 === $db2);  // bool(true) ← ¡Es el mismo objeto!
?>
```

---

## ✏️ EJERCICIO PRÁCTICO

### Implementa un Singleton para Logger

**Python:**
```python
class Logger:
    _instance = None
    
    def __new__(cls):
        if cls._instance is None:
            cls._instance = super().__new__(cls)
            cls._instance._inicializar()
        return cls._instance
    
    def _inicializar(self):
        # TU CÓDIGO: Abre un archivo de log
        self.archivo = open('app.log', 'a')
    
    def log(self, mensaje):
        # TU CÓDIGO: Escribe mensaje en el archivo
        self.archivo.write(f"{mensaje}\n")
        self.archivo.flush()

# Usa el logger
logger1 = Logger()
logger1.log("Usuario inició sesión")

logger2 = Logger()
logger2.log("Usuario cerró sesión")
# Ambos escriben en el MISMO archivo
```

**PHP:**
```php
<?php
class Logger {
    private static $instance = null;
    private $archivo;
    
    private function __construct() {
        // TU CÓDIGO: Abre archivo de log
        $this->archivo = fopen('app.log', 'a');
    }
    
    private function __clone() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log($mensaje) {
        // TU CÓDIGO: Escribe en el archivo
        fwrite($this->archivo, $mensaje . "\n");
    }
}

$log1 = Logger::getInstance();
$log1->log("Usuario inició sesión");
?>
```

---

## 📝 RESUMEN

**DÓNDE VA TU CÓDIGO:**
1. **Inicialización:** En el constructor privado
2. **Métodos de negocio:** Después de getInstance()
3. **Datos:** Como propiedades de la clase

**RECUERDA:**
- Constructor PRIVADO (nadie puede hacer `new`)
- Variable estática para guardar la instancia
- Método público `getInstance()` para acceder
