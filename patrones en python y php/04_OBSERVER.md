# PRÁCTICA 4: PATRÓN OBSERVER

## ESTRUCTURA DEL PATRÓN

```
┌─────────────┐                ┌──────────────┐
│   Sujeto    │◄───observa─────│  Observador  │
├─────────────┤                └──────────────┘
│ observadores│                       △
├─────────────┤                       │
│ + agregar() │                  ┌────┴────┐
│ + notificar()│                 │         │
└─────────────┘              ┌───┴───┐ ┌──┴────┐
                             │ Obs1  │ │ Obs2  │  ← TU CÓDIGO
                             └───────┘ └───────┘
```

---

## EJEMPLO 1: PYTHON

### ESTRUCTURA BASE
```python
# Interfaz del observador
class Observador:
    def actualizar(self, evento, datos):
        pass

# ============================================
# TU CÓDIGO: Observadores concretos
# ============================================
class ObservadorA(Observador):
    def actualizar(self, evento, datos):
        print(f"ObservadorA recibió: {evento} - {datos}")

class ObservadorB(Observador):
    def actualizar(self, evento, datos):
        print(f"ObservadorB recibió: {evento} - {datos}")

# ============================================
# SUJETO (quien notifica)
# ============================================
class Sujeto:
    def __init__(self):
        self.observadores = []
    
    def agregar_observador(self, observador):
        # ============================================
        # TU CÓDIGO: Agregar a la lista
        # ============================================
        self.observadores.append(observador)
    
    def quitar_observador(self, observador):
        self.observadores.remove(observador)
    
    def notificar(self, evento, datos):
        # ============================================
        # TU CÓDIGO: Notificar a todos
        # ============================================
        for observador in self.observadores:
            observador.actualizar(evento, datos)

# USO
sujeto = Sujeto()
sujeto.agregar_observador(ObservadorA())
sujeto.agregar_observador(ObservadorB())

sujeto.notificar("cambio", "dato importante")
# ObservadorA recibió: cambio - dato importante
# ObservadorB recibió: cambio - dato importante
```

### EJERCICIO: Sistema de Suscripciones a Blog

```python
# Interfaz
class Suscriptor:
    def actualizar(self, articulo):
        pass

# ============================================
# TU CÓDIGO: Diferentes tipos de suscriptores
# ============================================
class SuscriptorEmail(Suscriptor):
    def __init__(self, email):
        self.email = email
    
    def actualizar(self, articulo):
        print(f"📧 Email a {self.email}: Nuevo artículo '{articulo}'")

class SuscriptorSMS(Suscriptor):
    def __init__(self, telefono):
        self.telefono = telefono
    
    def actualizar(self, articulo):
        print(f"📱 SMS a {self.telefono}: '{articulo}'")

# ============================================
# BLOG (sujeto)
# ============================================
class Blog:
    def __init__(self):
        self.suscriptores = []
    
    def suscribir(self, suscriptor):
        # TU CÓDIGO: Agregar suscriptor
        self.suscriptores.append(suscriptor)
    
    def desuscribir(self, suscriptor):
        self.suscriptores.remove(suscriptor)
    
    def publicar_articulo(self, titulo):
        print(f"\n📝 Nuevo artículo publicado: {titulo}")
        # TU CÓDIGO: Notificar a todos
        for suscriptor in self.suscriptores:
            suscriptor.actualizar(titulo)

# PRUEBA
blog = Blog()

# Agregar suscriptores
blog.suscribir(SuscriptorEmail("juan@email.com"))
blog.suscribir(SuscriptorEmail("maria@email.com"))
blog.suscribir(SuscriptorSMS("+573001234567"))

# Publicar artículo
blog.publicar_articulo("Patrones de Diseño en Python")
# 📧 Email a juan@email.com: Nuevo artículo 'Patrones de Diseño en Python'
# 📧 Email a maria@email.com: Nuevo artículo 'Patrones de Diseño en Python'
# 📱 SMS a +573001234567: 'Patrones de Diseño en Python'
```

---

## EJEMPLO 2: PHP

### ESTRUCTURA BASE
```php
<?php
// Interfaz
interface Observador {
    public function actualizar($evento, $datos);
}

// ============================================
// TU CÓDIGO: Observadores concretos
// ============================================
class ObservadorA implements Observador {
    public function actualizar($evento, $datos) {
        echo "ObservadorA: $evento - $datos\n";
    }
}

class ObservadorB implements Observador {
    public function actualizar($evento, $datos) {
        echo "ObservadorB: $evento - $datos\n";
    }
}

// ============================================
// SUJETO
// ============================================
class Sujeto {
    private $observadores = [];
    
    public function agregarObservador(Observador $obs) {
        // ============================================
        // TU CÓDIGO: Agregar
        // ============================================
        $this->observadores[] = $obs;
    }
    
    public function notificar($evento, $datos) {
        // ============================================
        // TU CÓDIGO: Notificar a todos
        // ============================================
        foreach ($this->observadores as $obs) {
            $obs->actualizar($evento, $datos);
        }
    }
}

// USO
$sujeto = new Sujeto();
$sujeto->agregarObservador(new ObservadorA());
$sujeto->agregarObservador(new ObservadorB());

$sujeto->notificar("cambio", "datos");
?>
```

### EJERCICIO: Sistema de Alertas de Precios

```php
<?php
// Interfaz
interface AlertaPrecio {
    public function notificar($producto, $precio_anterior, $precio_nuevo);
}

// ============================================
// TU CÓDIGO: Diferentes tipos de alertas
// ============================================
class AlertaEmail implements AlertaPrecio {
    private $email;
    
    public function __construct($email) {
        $this->email = $email;
    }
    
    public function notificar($producto, $precio_anterior, $precio_nuevo) {
        echo "📧 Email a {$this->email}:\n";
        echo "   $producto: $$precio_anterior → $$precio_nuevo\n";
    }
}

class AlertaWhatsApp implements AlertaPrecio {
    private $telefono;
    
    public function __construct($telefono) {
        $this->telefono = $telefono;
    }
    
    public function notificar($producto, $precio_anterior, $precio_nuevo) {
        echo "💬 WhatsApp a {$this->telefono}:\n";
        echo "   ¡$producto bajó a $$precio_nuevo!\n";
    }
}

// ============================================
// PRODUCTO (sujeto)
// ============================================
class Producto {
    private $nombre;
    private $precio;
    private $observadores = [];
    
    public function __construct($nombre, $precio) {
        $this->nombre = $nombre;
        $this->precio = $precio;
    }
    
    public function suscribirAlerta(AlertaPrecio $alerta) {
        // TU CÓDIGO: Agregar observador
        $this->observadores[] = $alerta;
    }
    
    public function cambiarPrecio($nuevo_precio) {
        $anterior = $this->precio;
        $this->precio = $nuevo_precio;
        
        // TU CÓDIGO: Notificar si cambió
        if ($anterior != $nuevo_precio) {
            foreach ($this->observadores as $obs) {
                $obs->notificar($this->nombre, $anterior, $nuevo_precio);
            }
        }
    }
}

// PRUEBA
$laptop = new Producto("Laptop HP", 800);

// Suscribir alertas
$laptop->suscribirAlerta(new AlertaEmail("usuario@email.com"));
$laptop->suscribirAlerta(new AlertaWhatsApp("+573001234567"));

// Cambiar precio
$laptop->cambiarPrecio(650);
// 📧 Email a usuario@email.com:
//    Laptop HP: $800 → $650
// 💬 WhatsApp a +573001234567:
//    ¡Laptop HP bajó a $650!
?>
```

---

## ✏️ EJERCICIO PRÁCTICO

### Implementa un sistema de Stock

**Python:**
```python
class AlertaStock:
    def actualizar(self, producto, cantidad):
        pass

# TU CÓDIGO: Crea AlertaGerente, AlertaProveedor
class AlertaGerente(AlertaStock):
    def actualizar(self, producto, cantidad):
        if cantidad < 10:
            print(f"⚠️ Gerente: {producto} tiene solo {cantidad} unidades")

# TU CÓDIGO: Crea clase Inventario
class Inventario:
    def __init__(self, producto, cantidad):
        self.producto = producto
        self.cantidad = cantidad
        self.alertas = []
    
    def agregar_alerta(self, alerta):
        self.alertas.append(alerta)
    
    def actualizar_stock(self, cantidad):
        self.cantidad = cantidad
        for alerta in self.alertas:
            alerta.actualizar(self.producto, cantidad)

# Prueba
inv = Inventario("Laptops", 50)
inv.agregar_alerta(AlertaGerente())
inv.actualizar_stock(5)  # Dispara alerta
```

**PHP:**
```php
<?php
interface AlertaStock {
    public function actualizar($producto, $cantidad);
}

// TU CÓDIGO: Crea AlertaGerente
class AlertaGerente implements AlertaStock {
    public function actualizar($producto, $cantidad) {
        if ($cantidad < 10) {
            echo "⚠️ Gerente: $producto tiene $cantidad unidades\n";
        }
    }
}

// TU CÓDIGO: Crea Inventario
class Inventario {
    private $producto;
    private $cantidad;
    private $alertas = [];
    
    public function __construct($producto, $cantidad) {
        $this->producto = $producto;
        $this->cantidad = $cantidad;
    }
    
    public function agregarAlerta(AlertaStock $alerta) {
        $this->alertas[] = $alerta;
    }
    
    public function actualizarStock($cantidad) {
        $this->cantidad = $cantidad;
        foreach ($this->alertas as $alerta) {
            $alerta->actualizar($this->producto, $cantidad);
        }
    }
}
?>
```

---

## 📝 RESUMEN

**DÓNDE VA TU CÓDIGO:**
1. **Observadores:** Implementa el método `actualizar()`
2. **Agregar observador:** En el método del sujeto
3. **Notificar:** Loop sobre la lista de observadores

**RECUERDA:**
- El sujeto mantiene una lista de observadores
- Cuando algo cambia, recorre la lista y notifica
- Observadores son independientes entre sí
