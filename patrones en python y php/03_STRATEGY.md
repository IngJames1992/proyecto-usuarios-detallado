# PRÁCTICA 3: PATRÓN STRATEGY

## ESTRUCTURA DEL PATRÓN

```
┌────────────────┐
│   Estrategia   │  ← Interfaz base
└────────────────┘
         △
         │ implementa
    ┌────┴────┐
    │         │
┌───┴───┐ ┌──┴────┐
│ Est1  │ │ Est2  │  ← TU CÓDIGO: Diferentes algoritmos
└───────┘ └───────┘

┌────────────────┐
│    Contexto    │
├────────────────┤
│ - estrategia   │  ← Guarda la estrategia actual
├────────────────┤
│ + set()        │  ← Cambia estrategia
│ + ejecutar()   │  ← Usa la estrategia actual
└────────────────┘
```

---

## EJEMPLO 1: PYTHON

### ESTRUCTURA BASE
```python
# Interfaz base
class Estrategia:
    def ejecutar(self, datos):
        pass

# ============================================
# TU CÓDIGO: Diferentes estrategias
# ============================================
class EstrategiaA(Estrategia):
    def ejecutar(self, datos):
        return f"Estrategia A: {datos}"

class EstrategiaB(Estrategia):
    def ejecutar(self, datos):
        return f"Estrategia B: {datos}"

# ============================================
# CONTEXTO (usa la estrategia)
# ============================================
class Contexto:
    def __init__(self, estrategia):
        self.estrategia = estrategia
    
    # Cambiar estrategia en tiempo de ejecución
    def set_estrategia(self, estrategia):
        self.estrategia = estrategia
    
    # Usar la estrategia actual
    def hacer_algo(self, datos):
        # ============================================
        # TU CÓDIGO: Llamar a la estrategia
        # ============================================
        return self.estrategia.ejecutar(datos)

# USO
ctx = Contexto(EstrategiaA())
print(ctx.hacer_algo("Hola"))  # Estrategia A: Hola

ctx.set_estrategia(EstrategiaB())
print(ctx.hacer_algo("Hola"))  # Estrategia B: Hola
```

### EJERCICIO: Cálculo de Descuentos

```python
# Interfaz
class EstrategiaDescuento:
    def calcular(self, total):
        pass

# ============================================
# TU CÓDIGO: Diferentes descuentos
# ============================================
class SinDescuento(EstrategiaDescuento):
    def calcular(self, total):
        return total

class Descuento10(EstrategiaDescuento):
    def calcular(self, total):
        return total * 0.9

class Descuento20(EstrategiaDescuento):
    def calcular(self, total):
        return total * 0.8

class DescuentoVIP(EstrategiaDescuento):
    def calcular(self, total):
        return total * 0.5

# ============================================
# CARRITO (contexto)
# ============================================
class Carrito:
    def __init__(self, estrategia_descuento):
        self.items = []
        self.estrategia = estrategia_descuento
    
    def agregar_item(self, precio):
        self.items.append(precio)
    
    def cambiar_descuento(self, estrategia):
        # TU CÓDIGO: Cambiar estrategia
        self.estrategia = estrategia
    
    def calcular_total(self):
        # TU CÓDIGO: Calcular subtotal y aplicar estrategia
        subtotal = sum(self.items)
        return self.estrategia.calcular(subtotal)

# PRUEBA
carrito = Carrito(SinDescuento())
carrito.agregar_item(100)
carrito.agregar_item(50)

print(f"Sin descuento: ${carrito.calcular_total()}")  # $150

carrito.cambiar_descuento(Descuento20())
print(f"Con 20%: ${carrito.calcular_total()}")  # $120

carrito.cambiar_descuento(DescuentoVIP())
print(f"VIP: ${carrito.calcular_total()}")  # $75
```

---

## EJEMPLO 2: PHP

### ESTRUCTURA BASE
```php
<?php
// Interfaz
interface Estrategia {
    public function ejecutar($datos);
}

// ============================================
// TU CÓDIGO: Estrategias concretas
// ============================================
class EstrategiaA implements Estrategia {
    public function ejecutar($datos) {
        return "Estrategia A: $datos";
    }
}

class EstrategiaB implements Estrategia {
    public function ejecutar($datos) {
        return "Estrategia B: $datos";
    }
}

// ============================================
// CONTEXTO
// ============================================
class Contexto {
    private $estrategia;
    
    public function __construct(Estrategia $estrategia) {
        $this->estrategia = $estrategia;
    }
    
    public function setEstrategia(Estrategia $estrategia) {
        $this->estrategia = $estrategia;
    }
    
    public function hacerAlgo($datos) {
        // ============================================
        // TU CÓDIGO: Usar estrategia
        // ============================================
        return $this->estrategia->ejecutar($datos);
    }
}

// USO
$ctx = new Contexto(new EstrategiaA());
echo $ctx->hacerAlgo("Hola");  // Estrategia A: Hola
?>
```

### EJERCICIO: Métodos de Envío

```php
<?php
// Interfaz
interface EstrategiaEnvio {
    public function calcular($peso);
}

// ============================================
// TU CÓDIGO: Diferentes métodos de envío
// ============================================
class EnvioNormal implements EstrategiaEnvio {
    public function calcular($peso) {
        return $peso * 5;  // $5 por kg
    }
}

class EnvioExpress implements EstrategiaEnvio {
    public function calcular($peso) {
        return $peso * 15;  // $15 por kg
    }
}

class EnvioInternacional implements EstrategiaEnvio {
    public function calcular($peso) {
        return $peso * 50;  // $50 por kg
    }
}

// ============================================
// CALCULADORA DE ENVÍO (contexto)
// ============================================
class CalculadoraEnvio {
    private $estrategia;
    
    public function __construct(EstrategiaEnvio $estrategia) {
        $this->estrategia = $estrategia;
    }
    
    public function cambiarMetodo(EstrategiaEnvio $estrategia) {
        // TU CÓDIGO: Cambiar método
        $this->estrategia = $estrategia;
    }
    
    public function calcularCosto($peso) {
        // TU CÓDIGO: Calcular usando estrategia actual
        return $this->estrategia->calcular($peso);
    }
}

// PRUEBA
$calculadora = new CalculadoraEnvio(new EnvioNormal());
echo "Normal: $" . $calculadora->calcularCosto(2) . "\n";  // $10

$calculadora->cambiarMetodo(new EnvioExpress());
echo "Express: $" . $calculadora->calcularCosto(2) . "\n";  // $30
?>
```

---

## ✏️ EJERCICIO PRÁCTICO

### Implementa estrategias de Ordenamiento

**Python:**
```python
class EstrategiaOrden:
    def ordenar(self, lista):
        pass

# TU CÓDIGO: Crea Ascendente, Descendente
class Ascendente(EstrategiaOrden):
    def ordenar(self, lista):
        return sorted(lista)

class Descendente(EstrategiaOrden):
    def ordenar(self, lista):
        return sorted(lista, reverse=True)

# TU CÓDIGO: Crea el ordenador
class Ordenador:
    def __init__(self, estrategia):
        self.estrategia = estrategia
    
    def ordenar(self, lista):
        return self.estrategia.ordenar(lista)

# Prueba
numeros = [5, 2, 8, 1, 9]
ord = Ordenador(Ascendente())
print(ord.ordenar(numeros))  # [1, 2, 5, 8, 9]
```

**PHP:**
```php
<?php
interface EstrategiaOrden {
    public function ordenar($array);
}

// TU CÓDIGO: Crea Ascendente
class Ascendente implements EstrategiaOrden {
    public function ordenar($array) {
        sort($array);
        return $array;
    }
}

// TU CÓDIGO: Crea el ordenador
class Ordenador {
    private $estrategia;
    
    public function __construct(EstrategiaOrden $estrategia) {
        $this->estrategia = $estrategia;
    }
    
    public function ordenar($array) {
        return $this->estrategia->ordenar($array);
    }
}
?>
```

---

## 📝 RESUMEN

**DÓNDE VA TU CÓDIGO:**
1. **Algoritmos:** En cada clase de estrategia
2. **Cambio de estrategia:** En `setEstrategia()`
3. **Uso:** En el método del contexto que llama a la estrategia

**RECUERDA:**
- Todas las estrategias tienen la misma interfaz
- El contexto no sabe QUÉ estrategia usa, solo la llama
- Puedes cambiar estrategia en cualquier momento
