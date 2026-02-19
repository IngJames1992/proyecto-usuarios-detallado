# PRÁCTICA 6: PATRÓN ADAPTER

## ESTRUCTURA DEL PATRÓN

```
┌────────────┐         ┌─────────────┐
│   Cliente  │────────▶│  Interfaz   │  ← Lo que el cliente espera
└────────────┘         │   Target    │
                       └─────────────┘
                              △
                              │ implementa
                       ┌──────┴───────┐
                       │   ADAPTER    │  ← TU CÓDIGO: Traduce
                       └──────────────┘
                              │ usa
                              ▼
                       ┌──────────────┐
                       │   Adaptado   │  ← Sistema incompatible
                       └──────────────┘
```

---

## EJEMPLO 1: PYTHON

### ESTRUCTURA BASE
```python
# ============================================
# INTERFAZ QUE EL CLIENTE ESPERA
# ============================================
class InterfazNueva:
    def metodo_nuevo(self):
        pass

# ============================================
# SISTEMA VIEJO (incompatible)
# ============================================
class SistemaViejo:
    def metodo_antiguo(self):
        return "Datos del sistema viejo"

# ============================================
# ADAPTER (traduce entre ambos)
# TU CÓDIGO VA AQUÍ
# ============================================
class Adapter(InterfazNueva):
    def __init__(self, sistema_viejo):
        self.sistema_viejo = sistema_viejo
    
    def metodo_nuevo(self):
        # ============================================
        # TU CÓDIGO: Llamar al método viejo y adaptar
        # ============================================
        datos_viejos = self.sistema_viejo.metodo_antiguo()
        # Transformar/adaptar los datos si es necesario
        return f"Adaptado: {datos_viejos}"

# USO
sistema_viejo = SistemaViejo()
adapter = Adapter(sistema_viejo)

# El cliente usa la interfaz nueva
print(adapter.metodo_nuevo())  # Adaptado: Datos del sistema viejo
```

### EJERCICIO: Adaptar API Antigua a Nueva

```python
# ============================================
# API ANTIGUA (no podemos modificarla)
# ============================================
class APIAntigua:
    def obtener_datos_usuario(self, id_usuario):
        # Retorna formato antiguo
        return {
            'id_usuario': id_usuario,
            'nombre_completo': 'Juan Pérez',
            'correo_electronico': 'juan@email.com',
            'fecha_registro': '2020-01-15'
        }

# ============================================
# INTERFAZ NUEVA (lo que nuestro código espera)
# ============================================
class APINueva:
    def get_user(self, user_id):
        # Debe retornar formato nuevo
        return {
            'userId': user_id,
            'fullName': 'Nombre',
            'email': 'email',
            'registeredAt': 'fecha'
        }

# ============================================
# ADAPTER
# TU CÓDIGO: Traduce de formato antiguo a nuevo
# ============================================
class APIAdapter(APINueva):
    def __init__(self, api_antigua):
        self.api_antigua = api_antigua
    
    def get_user(self, user_id):
        # TU CÓDIGO: Obtener datos en formato viejo
        datos_viejos = self.api_antigua.obtener_datos_usuario(user_id)
        
        # TU CÓDIGO: Traducir a formato nuevo
        datos_nuevos = {
            'userId': datos_viejos['id_usuario'],
            'fullName': datos_viejos['nombre_completo'],
            'email': datos_viejos['correo_electronico'],
            'registeredAt': datos_viejos['fecha_registro']
        }
        
        return datos_nuevos

# PRUEBA
api_vieja = APIAntigua()

# Usar adapter para que parezca API nueva
adapter = APIAdapter(api_vieja)
usuario = adapter.get_user(123)

print(usuario)
# {'userId': 123, 'fullName': 'Juan Pérez', 
#  'email': 'juan@email.com', 'registeredAt': '2020-01-15'}
```

---

## EJEMPLO 2: PHP

### ESTRUCTURA BASE
```php
<?php
// ============================================
// INTERFAZ QUE EL CLIENTE ESPERA
// ============================================
interface InterfazNueva {
    public function metodoNuevo();
}

// ============================================
// SISTEMA VIEJO (incompatible)
// ============================================
class SistemaViejo {
    public function metodoAntiguo() {
        return "Datos del sistema viejo";
    }
}

// ============================================
// ADAPTER
// TU CÓDIGO VA AQUÍ
// ============================================
class Adapter implements InterfazNueva {
    private $sistemaViejo;
    
    public function __construct(SistemaViejo $sistemaViejo) {
        $this->sistemaViejo = $sistemaViejo;
    }
    
    public function metodoNuevo() {
        // ============================================
        // TU CÓDIGO: Llamar método viejo y adaptar
        // ============================================
        $datosViejos = $this->sistemaViejo->metodoAntiguo();
        return "Adaptado: $datosViejos";
    }
}

// USO
$sistemaViejo = new SistemaViejo();
$adapter = new Adapter($sistemaViejo);

echo $adapter->metodoNuevo();  // Adaptado: Datos del sistema viejo
?>
```

### EJERCICIO: Adaptar Sistema de Pagos

```php
<?php
// ============================================
// PROCESADOR DE PAGOS VIEJO (no podemos cambiar)
// ============================================
class ProcesadorPagosViejo {
    public function procesarTransaccion($tarjeta, $monto, $moneda) {
        // Retorna formato viejo
        return [
            'id_transaccion' => rand(1000, 9999),
            'estado' => 'aprobado',
            'monto_cobrado' => $monto,
            'tipo_moneda' => $moneda
        ];
    }
}

// ============================================
// INTERFAZ NUEVA (lo que nuestro sistema espera)
// ============================================
interface ProcesadorModerno {
    public function processPayment($cardNumber, $amount);
}

// ============================================
// ADAPTER
// TU CÓDIGO: Traduce entre sistemas
// ============================================
class PagoAdapter implements ProcesadorModerno {
    private $procesadorViejo;
    
    public function __construct(ProcesadorPagosViejo $procesador) {
        $this->procesadorViejo = $procesador;
    }
    
    public function processPayment($cardNumber, $amount) {
        // TU CÓDIGO: Llamar al sistema viejo
        $resultado_viejo = $this->procesadorViejo->procesarTransaccion(
            $cardNumber,
            $amount,
            'USD'  // Moneda fija
        );
        
        // TU CÓDIGO: Traducir respuesta a formato nuevo
        return [
            'transactionId' => $resultado_viejo['id_transaccion'],
            'status' => $resultado_viejo['estado'] === 'aprobado' ? 'success' : 'failed',
            'amount' => $resultado_viejo['monto_cobrado'],
            'currency' => $resultado_viejo['tipo_moneda']
        ];
    }
}

// PRUEBA
$procesadorViejo = new ProcesadorPagosViejo();

// Usar adapter
$adapter = new PagoAdapter($procesadorViejo);
$resultado = $adapter->processPayment('1234-5678-9012-3456', 100);

print_r($resultado);
// Array (
//     [transactionId] => 1234
//     [status] => success
//     [amount] => 100
//     [currency] => USD
// )
?>
```

---

## ✏️ EJERCICIO PRÁCTICO

### Adaptar sistema de temperatura

**Python:**
```python
# Sistema viejo (solo usa Fahrenheit)
class SensorFahrenheit:
    def obtener_temperatura(self):
        return 68  # °F

# Interfaz nueva (espera Celsius)
class ISensorCelsius:
    def get_temperature(self):
        pass

# TU CÓDIGO: Crea el adapter
class TemperatureAdapter(ISensorCelsius):
    def __init__(self, sensor_fahrenheit):
        self.sensor = sensor_fahrenheit
    
    def get_temperature(self):
        # TU CÓDIGO: Obtener en F y convertir a C
        fahrenheit = self.sensor.obtener_temperatura()
        celsius = (fahrenheit - 32) * 5/9
        return round(celsius, 2)

# Prueba
sensor_viejo = SensorFahrenheit()
adapter = TemperatureAdapter(sensor_viejo)
print(f"Temperatura: {adapter.get_temperature()}°C")  # 20°C
```

**PHP:**
```php
<?php
// Sistema viejo
class SensorFahrenheit {
    public function obtenerTemperatura() {
        return 68;  // °F
    }
}

// Interfaz nueva
interface ISensorCelsius {
    public function getTemperature();
}

// TU CÓDIGO: Crea adapter
class TemperatureAdapter implements ISensorCelsius {
    private $sensor;
    
    public function __construct(SensorFahrenheit $sensor) {
        $this->sensor = $sensor;
    }
    
    public function getTemperature() {
        // TU CÓDIGO: Convertir F a C
        $fahrenheit = $this->sensor->obtenerTemperatura();
        $celsius = ($fahrenheit - 32) * 5/9;
        return round($celsius, 2);
    }
}

$adapter = new TemperatureAdapter(new SensorFahrenheit());
echo $adapter->getTemperature() . "°C";  // 20°C
?>
```

---

## OTRO EJEMPLO: Adaptar Base de Datos

**Python:**
```python
# Base de datos vieja
class MySQLViejo:
    def ejecutar_consulta(self, sql):
        return [{'id': 1, 'nombre': 'Juan'}]

# Nueva interfaz estándar
class DatabaseInterface:
    def query(self, sql):
        pass

# TU CÓDIGO: Adapter
class MySQLAdapter(DatabaseInterface):
    def __init__(self, mysql_viejo):
        self.db = mysql_viejo
    
    def query(self, sql):
        # TU CÓDIGO: Traducir método
        return self.db.ejecutar_consulta(sql)

# Uso
db_vieja = MySQLViejo()
db = MySQLAdapter(db_vieja)
resultados = db.query("SELECT * FROM usuarios")
```

**PHP:**
```php
<?php
class MySQLViejo {
    public function ejecutarConsulta($sql) {
        return [['id' => 1, 'nombre' => 'Juan']];
    }
}

interface DatabaseInterface {
    public function query($sql);
}

// TU CÓDIGO: Adapter
class MySQLAdapter implements DatabaseInterface {
    private $db;
    
    public function __construct(MySQLViejo $db) {
        $this->db = $db;
    }
    
    public function query($sql) {
        return $this->db->ejecutarConsulta($sql);
    }
}
?>
```

---

## 📝 RESUMEN

**DÓNDE VA TU CÓDIGO:**
1. **Inicializar:** Guardar referencia al sistema viejo
2. **Traducir:** En los métodos de la interfaz nueva
3. **Convertir:** Transformar datos de un formato a otro

**RECUERDA:**
- El adapter implementa la interfaz nueva
- Internamente usa el sistema viejo
- Traduce entre ambos formatos
- No modificas el código viejo ni el nuevo
