# 📚 PRÁCTICAS DE PATRONES DE DISEÑO

## Guía Completa con Ejemplos Python y PHP

---

## 📋 CONTENIDO

1. **SINGLETON** - Una sola instancia
2. **FACTORY METHOD** - Crear objetos sin especificar la clase
3. **STRATEGY** - Algoritmos intercambiables
4. **OBSERVER** - Notificar a múltiples objetos
5. **DECORATOR** - Agregar funcionalidad dinámicamente
6. **ADAPTER** - Conectar interfaces incompatibles

---

## 🎯 CÓMO USAR ESTAS PRÁCTICAS

### Para cada patrón encontrarás:

1. **Estructura visual** del patrón
2. **Código base** con comentarios "TU CÓDIGO AQUÍ"
3. **Ejercicio completo** en Python
4. **Ejercicio completo** en PHP
5. **Ejercicio para practicar** tú mismo

### Metodología de estudio:

1. **Lee la estructura** - Entiende los componentes
2. **Estudia el código base** - Ve dónde va tu código
3. **Analiza los ejercicios** - Ejemplos funcionales
4. **Implementa tu ejercicio** - Practica con el ejercicio propuesto

---

## 📝 RESUMEN RÁPIDO DE CADA PATRÓN

### 1. SINGLETON
**Problema:** Necesito UNA sola instancia
**Solución:** Constructor privado + método getInstance()
**Ejemplo:** Conexión a base de datos

### 2. FACTORY METHOD
**Problema:** Crear diferentes tipos de objetos
**Solución:** Fábrica que decide qué clase instanciar
**Ejemplo:** Sistema de notificaciones (Email, SMS, Push)

### 3. STRATEGY
**Problema:** Cambiar algoritmo en tiempo de ejecución
**Solución:** Interfaz común + diferentes implementaciones
**Ejemplo:** Métodos de pago, descuentos

### 4. OBSERVER
**Problema:** Notificar a múltiples objetos cuando algo cambia
**Solución:** Lista de observadores + método notificar()
**Ejemplo:** Suscripciones, alertas de precio

### 5. DECORATOR
**Problema:** Agregar funcionalidad sin modificar la clase
**Solución:** Envolver objetos dentro de otros
**Ejemplo:** Café con agregados (leche, crema, azúcar)

### 6. ADAPTER
**Problema:** Conectar dos sistemas incompatibles
**Solución:** Clase intermedia que traduce
**Ejemplo:** API antigua → API nueva

---

## 🔑 IDENTIFICAR CUÁNDO USAR CADA PATRÓN

### Preguntas clave:

**¿Solo debe haber UNA instancia?**
→ SINGLETON

**¿Necesitas crear objetos pero no sabes cuál hasta el final?**
→ FACTORY METHOD

**¿El algoritmo/método debe cambiar en tiempo de ejecución?**
→ STRATEGY

**¿Múltiples objetos deben reaccionar a un cambio?**
→ OBSERVER

**¿Necesitas agregar funcionalidad sin tocar la clase original?**
→ DECORATOR

**¿Tienes dos sistemas que no pueden comunicarse directamente?**
→ ADAPTER

---

## 💡 CONSEJOS GENERALES

### Al escribir tu código:

1. **Lee el patrón completo primero**
2. **Identifica las partes:** ¿qué es el componente base? ¿qué es concreto?
3. **Sigue los comentarios** "TU CÓDIGO AQUÍ"
4. **Prueba paso a paso** - No escribas todo de golpe
5. **Compara con el ejemplo** si te atascas

### Errores comunes:

❌ Olvidar hacer el constructor privado en Singleton
❌ No llamar al componente envuelto en Decorator
❌ Confundir Strategy con Factory (son diferentes)
❌ No mantener la lista de observadores actualizada
❌ Olvidar implementar la interfaz en el Adapter

---

## 📖 ORDEN DE ESTUDIO RECOMENDADO

### Nivel Básico (empezar aquí):
1. **SINGLETON** - El más simple
2. **FACTORY METHOD** - Muy usado

### Nivel Intermedio:
3. **STRATEGY** - Práctico y útil
4. **OBSERVER** - Importante para eventos

### Nivel Avanzado:
5. **DECORATOR** - Composición compleja
6. **ADAPTER** - Situaciones específicas

---

## 🎓 PARA TU CATÁLOGO

Cuando documentes en tu catálogo:

1. **Copia el código** que funciona
2. **Explica con tus palabras** qué hace cada parte
3. **Dibuja el diagrama** (aunque sea simple)
4. **Escribe un caso de uso** de tu propia experiencia
5. **Nota ventajas y desventajas**

---

## 🚀 SIGUIENTE PASO

1. Abre el archivo del patrón que quieres aprender
2. Lee la estructura visual
3. Copia el código base
4. Implementa el ejercicio
5. Compara con la solución

**¡No intentes aprender los 6 de golpe!**
Domina uno antes de pasar al siguiente.

---

## 📚 ESTRUCTURA DE ARCHIVOS

```
practicas/
├── 00_INDICE.md          ← Este archivo
├── 01_SINGLETON.md       ← Patrón Singleton
├── 02_FACTORY.md         ← Patrón Factory Method
├── 03_STRATEGY.md        ← Patrón Strategy
├── 04_OBSERVER.md        ← Patrón Observer
├── 05_DECORATOR.md       ← Patrón Decorator
└── 06_ADAPTER.md         ← Patrón Adapter
```

---

**¡Éxito en tu aprendizaje de patrones de diseño!** 🎯
