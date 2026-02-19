# PRÁCTICA 2: PATRÓN FACTORY METHOD

## ESTRUCTURA DEL PATRÓN

```
┌──────────────────┐
│  Clase Producto  │  ← Clase base/interfaz
└──────────────────┘
         △
         │ hereda
    ┌────┴────┐
    │         │
┌───┴───┐ ┌──┴────┐
│ Prod1 │ │ Prod2 │  ← TU CÓDIGO: Diferentes productos
└───────┘ └───────┘

┌──────────────────┐
│     Factory      │  ← Clase fábrica
├──────────────────┤
│ + crear(tipo)    │  ← TU CÓDIGO: Lógica de creación
└──────────────────┘
```

---

## EJEMPLO 1: PYTHON

### ESTRUCTURA BASE
```python
# Clase base (interfaz)
class Producto:
    def hacer_algo(self):
        pass

# ============================================
# TU CÓDIGO: Crea productos concretos
# ============================================
class ProductoA(Producto):
    def hacer_algo(self):
        return "Soy Producto A"

class ProductoB(Producto):
    def hacer_algo(self):
        return "Soy Producto B"

# ============================================
# LA FÁBRICA
# ============================================
class Factory:
    @staticmethod
    def crear(tipo):
        # ============================================
        # TU CÓDIGO: Lógica para decidir qué crear
        # ============================================
        if tipo == 'A':
            return ProductoA()
        elif tipo == 'B':
            return ProductoB()
        else:
            raise ValueError(f"Tipo {tipo} no existe")

# USO
producto = Factory.crear('A')
print(producto.hacer_algo())  # Soy Producto A
```

### EJERCICIO: Sistema de Notificaciones

```python
# Clase base
class Notificacion:
    def enviar(self, mensaje):
        pass

# ============================================
# TU CÓDIGO: Diferentes tipos de notificaciones
# ============================================
class Email(Notificacion):
    def enviar(self, mensaje):
        return f"📧 Email: {mensaje}"

class SMS(Notificacion):
    def enviar(self, mensaje):
        return f"📱 SMS: {mensaje}"

class Push(Notificacion):
    def enviar(self, mensaje):
        return f"🔔 Push: {mensaje}"

# ============================================
# LA FÁBRICA
# ============================================
class NotificacionFactory:
    @staticmethod
    def crear(tipo):
        # TU CÓDIGO: Retorna el tipo correcto
        tipos = {
            'email': Email,
            'sms': SMS,
            'push': Push
        }
        
        clase = tipos.get(tipo.lower())
        if clase:
            return clase()
        raise ValueError(f"Tipo '{tipo}' no válido")

# PRUEBA
notif = NotificacionFactory.crear('email')
print(notif.enviar("Hola Usuario"))  # 📧 Email: Hola Usuario

notif = NotificacionFactory.crear('sms')
print(notif.enviar("Código: 1234"))  # 📱 SMS: Código: 1234
```

---

## EJEMPLO 2: PHP

### ESTRUCTURA BASE
```php
<?php
// Interfaz base
interface Producto {
    public function hacerAlgo();
}

// ============================================
// TU CÓDIGO: Productos concretos
// ============================================
class ProductoA implements Producto {
    public function hacerAlgo() {
        return "Soy Producto A";
    }
}

class ProductoB implements Producto {
    public function hacerAlgo() {
        return "Soy Producto B";
    }
}

// ============================================
// LA FÁBRICA
// ============================================
class Factory {
    public static function crear($tipo) {
        // ============================================
        // TU CÓDIGO: Decide qué crear
        // ============================================
        switch($tipo) {
            case 'A':
                return new ProductoA();
            case 'B':
                return new ProductoB();
            default:
                throw new Exception("Tipo $tipo no existe");
        }
    }
}

// USO
$producto = Factory::crear('A');
echo $producto->hacerAlgo();  // Soy Producto A
?>
```

### EJERCICIO: Procesadores de Pago

```php
<?php
// Interfaz
interface ProcesadorPago {
    public function procesar($monto);
}

// ============================================
// TU CÓDIGO: Diferentes procesadores
// ============================================
class PagoTarjeta implements ProcesadorPago {
    public function procesar($monto) {
        return "💳 Procesando $$monto con tarjeta";
    }
}

class PagoPayPal implements ProcesadorPago {
    public function procesar($monto) {
        return "🅿️ Procesando $$monto con PayPal";
    }
}

class PagoTransferencia implements ProcesadorPago {
    public function procesar($monto) {
        return "🏦 Procesando $$monto con transferencia";
    }
}

// ============================================
// LA FÁBRICA
// ============================================
class PagoFactory {
    public static function crear($metodo) {
        // TU CÓDIGO: Retorna el procesador correcto
        $procesadores = [
            'tarjeta' => PagoTarjeta::class,
            'paypal' => PagoPayPal::class,
            'transferencia' => PagoTransferencia::class
        ];
        
        if (isset($procesadores[$metodo])) {
            $clase = $procesadores[$metodo];
            return new $clase();
        }
        
        throw new Exception("Método '$metodo' no válido");
    }
}

// PRUEBA
$pago = PagoFactory::crear('paypal');
echo $pago->procesar(100);  // 🅿️ Procesando $100 con PayPal
?>
```

---

## ✏️ EJERCICIO PRÁCTICO

### Implementa una fábrica de Vehículos

**Python:**
```python
class Vehiculo:
    def moverse(self):
        pass

# TU CÓDIGO: Crea Auto, Moto, Camion
class Auto(Vehiculo):
    def moverse(self):
        return "🚗 Auto en movimiento"

class Moto(Vehiculo):
    def moverse(self):
        return "🏍️ Moto en movimiento"

# TU CÓDIGO: Crea la fábrica
class VehiculoFactory:
    @staticmethod
    def crear(tipo):
        if tipo == 'auto':
            return Auto()
        elif tipo == 'moto':
            return Moto()

# Prueba
v = VehiculoFactory.crear('auto')
print(v.moverse())
```

**PHP:**
```php
<?php
interface Vehiculo {
    public function moverse();
}

// TU CÓDIGO: Crea Auto, Moto
class Auto implements Vehiculo {
    public function moverse() {
        return "🚗 Auto en movimiento";
    }
}

// TU CÓDIGO: Crea la fábrica
class VehiculoFactory {
    public static function crear($tipo) {
        if ($tipo === 'auto') {
            return new Auto();
        }
    }
}
?>
```

---

## 📝 RESUMEN

**DÓNDE VA TU CÓDIGO:**
1. **Productos concretos:** Clases que heredan/implementan la base
2. **Lógica de creación:** Dentro del método `crear()` de la fábrica
3. **Nuevos tipos:** Solo agregas nueva clase + una línea en la fábrica

**RECUERDA:**
- Todos los productos heredan de una clase base común
- La fábrica decide QUÉ crear basándose en un parámetro
- El cliente no usa `new`, usa `Factory.crear()`
