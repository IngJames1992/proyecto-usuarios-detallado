# PRÁCTICA 5: PATRÓN DECORATOR

## ESTRUCTURA DEL PATRÓN

```
┌──────────────┐
│  Componente  │  ← Clase base
└──────────────┘
       △
       │ hereda
   ┌───┴────────────┐
   │                │
┌──┴───────┐  ┌────┴────────┐
│ Concreto │  │  Decorador  │  ← Envuelve al componente
└──────────┘  └─────────────┘
                     △
                     │ hereda
                ┌────┴────┐
                │         │
            ┌───┴───┐ ┌──┴────┐
            │ Dec1  │ │ Dec2  │  ← TU CÓDIGO: Agregan funcionalidad
            └───────┘ └───────┘
```

---

## EJEMPLO 1: PYTHON

### ESTRUCTURA BASE
```python
# Componente base
class Componente:
    def operacion(self):
        pass

# Componente concreto
class ComponenteConcreto(Componente):
    def operacion(self):
        return "Componente Base"

# ============================================
# DECORADOR BASE
# ============================================
class Decorador(Componente):
    def __init__(self, componente):
        self._componente = componente
    
    def operacion(self):
        return self._componente.operacion()

# ============================================
# TU CÓDIGO: Decoradores concretos
# ============================================
class DecoradorA(Decorador):
    def operacion(self):
        # Agregar funcionalidad ANTES
        resultado = "DecoradorA + "
        # Llamar al componente envuelto
        resultado += self._componente.operacion()
        return resultado

class DecoradorB(Decorador):
    def operacion(self):
        # Llamar al componente
        resultado = self._componente.operacion()
        # Agregar funcionalidad DESPUÉS
        resultado += " + DecoradorB"
        return resultado

# USO
componente = ComponenteConcreto()
print(componente.operacion())  # Componente Base

# Decorar
decorado = DecoradorA(componente)
print(decorado.operacion())  # DecoradorA + Componente Base

# Decorar múltiple
decorado = DecoradorB(DecoradorA(componente))
print(decorado.operacion())  # DecoradorA + Componente Base + DecoradorB
```

### EJERCICIO: Café con Agregados

```python
# Componente base
class Cafe:
    def costo(self):
        return 5
    
    def descripcion(self):
        return "Café simple"

# ============================================
# DECORADOR BASE
# ============================================
class CafeDecorador(Cafe):
    def __init__(self, cafe):
        self._cafe = cafe
    
    def costo(self):
        return self._cafe.costo()
    
    def descripcion(self):
        return self._cafe.descripcion()

# ============================================
# TU CÓDIGO: Decoradores concretos (agregados)
# ============================================
class ConLeche(CafeDecorador):
    def costo(self):
        # TU CÓDIGO: Costo base + $2
        return self._cafe.costo() + 2
    
    def descripcion(self):
        # TU CÓDIGO: Descripción + "con leche"
        return self._cafe.descripcion() + " + Leche"

class ConCrema(CafeDecorador):
    def costo(self):
        return self._cafe.costo() + 3
    
    def descripcion(self):
        return self._cafe.descripcion() + " + Crema"

class ConAzucar(CafeDecorador):
    def costo(self):
        return self._cafe.costo() + 1
    
    def descripcion(self):
        return self._cafe.descripcion() + " + Azúcar"

# PRUEBA
mi_cafe = Cafe()
print(f"{mi_cafe.descripcion()}: ${mi_cafe.costo()}")
# Café simple: $5

mi_cafe = ConLeche(mi_cafe)
print(f"{mi_cafe.descripcion()}: ${mi_cafe.costo()}")
# Café simple + Leche: $7

mi_cafe = ConCrema(mi_cafe)
print(f"{mi_cafe.descripcion()}: ${mi_cafe.costo()}")
# Café simple + Leche + Crema: $10

mi_cafe = ConAzucar(mi_cafe)
print(f"{mi_cafe.descripcion()}: ${mi_cafe.costo()}")
# Café simple + Leche + Crema + Azúcar: $11
```

---

## EJEMPLO 2: PHP

### ESTRUCTURA BASE
```php
<?php
// Componente base
interface Componente {
    public function operacion();
}

class ComponenteConcreto implements Componente {
    public function operacion() {
        return "Componente Base";
    }
}

// ============================================
// DECORADOR BASE
// ============================================
class Decorador implements Componente {
    protected $componente;
    
    public function __construct(Componente $componente) {
        $this->componente = $componente;
    }
    
    public function operacion() {
        return $this->componente->operacion();
    }
}

// ============================================
// TU CÓDIGO: Decoradores concretos
// ============================================
class DecoradorA extends Decorador {
    public function operacion() {
        return "DecoradorA + " . $this->componente->operacion();
    }
}

// USO
$componente = new ComponenteConcreto();
echo $componente->operacion();  // Componente Base

$decorado = new DecoradorA($componente);
echo $decorado->operacion();  // DecoradorA + Componente Base
?>
```

### EJERCICIO: Notificaciones con Formato

```php
<?php
// Componente base
interface Notificacion {
    public function enviar($mensaje);
}

class NotificacionSimple implements Notificacion {
    public function enviar($mensaje) {
        return $mensaje;
    }
}

// ============================================
// DECORADOR BASE
// ============================================
abstract class NotificacionDecorador implements Notificacion {
    protected $notificacion;
    
    public function __construct(Notificacion $notificacion) {
        $this->notificacion = $notificacion;
    }
    
    public function enviar($mensaje) {
        return $this->notificacion->enviar($mensaje);
    }
}

// ============================================
// TU CÓDIGO: Decoradores que agregan formato
// ============================================
class ConNegritas extends NotificacionDecorador {
    public function enviar($mensaje) {
        // TU CÓDIGO: Agregar negritas
        $mensaje = $this->notificacion->enviar($mensaje);
        return "<b>$mensaje</b>";
    }
}

class ConMayusculas extends NotificacionDecorador {
    public function enviar($mensaje) {
        // TU CÓDIGO: Convertir a mayúsculas
        $mensaje = $this->notificacion->enviar($mensaje);
        return strtoupper($mensaje);
    }
}

class ConEncabezado extends NotificacionDecorador {
    public function enviar($mensaje) {
        // TU CÓDIGO: Agregar encabezado
        $encabezado = "=== NOTIFICACIÓN ===\n";
        $mensaje = $this->notificacion->enviar($mensaje);
        return $encabezado . $mensaje;
    }
}

// PRUEBA
$notif = new NotificacionSimple();
echo $notif->enviar("Hola Mundo");  // Hola Mundo

$notif = new ConNegritas($notif);
echo $notif->enviar("Hola Mundo");  // <b>Hola Mundo</b>

$notif = new ConMayusculas(new ConNegritas(new NotificacionSimple()));
echo $notif->enviar("Hola Mundo");  // <B>HOLA MUNDO</B>
?>
```

---

## ✏️ EJERCICIO PRÁCTICO

### Implementa decoradores para Pizza

**Python:**
```python
class Pizza:
    def costo(self):
        return 10
    
    def descripcion(self):
        return "Pizza base"

class PizzaDecorador(Pizza):
    def __init__(self, pizza):
        self._pizza = pizza
    
    def costo(self):
        return self._pizza.costo()
    
    def descripcion(self):
        return self._pizza.descripcion()

# TU CÓDIGO: Crea ConQueso, ConJamon, ConChampinones
class ConQueso(PizzaDecorador):
    def costo(self):
        return self._pizza.costo() + 3
    
    def descripcion(self):
        return self._pizza.descripcion() + " + Queso"

# Prueba
pizza = Pizza()
pizza = ConQueso(pizza)
print(f"{pizza.descripcion()}: ${pizza.costo()}")
```

**PHP:**
```php
<?php
interface Pizza {
    public function costo();
    public function descripcion();
}

class PizzaBase implements Pizza {
    public function costo() {
        return 10;
    }
    
    public function descripcion() {
        return "Pizza base";
    }
}

abstract class PizzaDecorador implements Pizza {
    protected $pizza;
    
    public function __construct(Pizza $pizza) {
        $this->pizza = $pizza;
    }
}

// TU CÓDIGO: Crea ConQueso
class ConQueso extends PizzaDecorador {
    public function costo() {
        return $this->pizza->costo() + 3;
    }
    
    public function descripcion() {
        return $this->pizza->descripcion() . " + Queso";
    }
}
?>
```

---

## 📝 RESUMEN

**DÓNDE VA TU CÓDIGO:**
1. **Decoradores concretos:** Clases que heredan del decorador base
2. **Funcionalidad extra:** En los métodos del decorador
3. **Composición:** Envolver objetos unos dentro de otros

**RECUERDA:**
- El decorador ENVUELVE al componente
- Llama al componente envuelto y agrega funcionalidad
- Puedes apilar múltiples decoradores
- No modificas el componente original
