# FrontBlocks: Hover Background Scale Effect

**Plugin:** FrontBlocks for GeneratePress  
**Feature:** Hover Background Image Scale Effect  
**Version:** 1.0  
**Date:** 2026-01-21

---

## 🎯 Overview

**FrontBlocks Hover Effect** añade un efecto de zoom suave a las imágenes de fondo cuando el usuario hace hover sobre el elemento. Es especialmente útil para galerías, grids de posts, y cualquier diseño que use imágenes de fondo.

Esta funcionalidad forma parte del conjunto de herramientas de animación y efectos de **FrontBlocks**, diseñada específicamente para trabajar de forma nativa con GenerateBlocks.

---

## ✨ Características

- **Zoom configurable**: Ajusta el nivel de escala desde 1.0 hasta 2.0
- **Transición suave**: Animación de 0.4 segundos con ease-in-out
- **Compatible con GenerateBlocks**: Funciona perfectamente con Query Loops y `--inline-bg-image`
- **Compatible con background-image estándar**: También funciona con CSS background-image normal
- **Sin interferencia con contenido**: El contenido del elemento permanece sobre la imagen escalada

---

## 📋 Cómo Usar FrontBlocks Hover Effect

### 1. Activar el Efecto

1. Selecciona cualquier bloque en el editor de Gutenberg
2. Abre el panel de configuración del bloque (sidebar derecho)
3. Busca el panel **"Hover Effects"** (añadido por FrontBlocks)
4. Activa el toggle **"Scale Background Image on Hover"**

### 2. Ajustar la Escala

Usa el slider **"Scale Amount"** para controlar cuánto se escalará la imagen:

- **1.0** = Sin escala (no se nota cambio)
- **1.1** = 110% (zoom sutil, recomendado por defecto)
- **1.2** = 120% (zoom moderado)
- **1.5** = 150% (zoom pronunciado)
- **2.0** = 200% (zoom máximo)

### 3. Aplicar a tu Contenido

El efecto funciona automáticamente en:

- Bloques con `--inline-bg-image` (como GenerateBlocks Query Loop)
- Bloques con `background-image` CSS estándar
- Cualquier elemento que tenga una imagen de fondo

---

## 💡 Ejemplo Práctico: Query Loop con Posts

Este es el caso de uso más común con **GenerateBlocks + FrontBlocks**:

```html
<!-- Estructura generada por GB Query Loop + FrontBlocks -->
<div class="gb-loop-item frbl-hover-bg-scale" style="--frbl-hover-scale: 1.1;">
    <a style="--inline-bg-image: url(imagen.jpg)" href="/post/">
        <h3>Título del Post</h3>
        <p>Ver proyecto</p>
    </a>
</div>
```

**¿Qué hace FrontBlocks?**

1. Detecta el elemento con clase `frbl-hover-bg-scale`
2. Crea un pseudo-elemento `::before` con la imagen de fondo
3. Al hacer hover, aplica `transform: scale(1.1)` al pseudo-elemento
4. El contenido (título y enlace) permanece en su posición original

---

## 🎨 CSS Generado por FrontBlocks

Cuando activas **FrontBlocks Hover Effect**, el plugin añade automáticamente estas clases CSS con el prefijo `.frbl-` (FrontBlocks):

### Clases CSS

```css
.frbl-hover-bg-scale {
    position: relative;
    overflow: hidden; /* Evita que la imagen escalada se salga */
}
```

### Pseudo-elemento para la Imagen

```css
.frbl-hover-bg-scale[style*="--inline-bg-image"]::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: var(--inline-bg-image);
    background-size: cover;
    background-position: center;
    transition: transform 0.4s ease-in-out;
    z-index: 0;
}
```

### Efecto Hover

```css
.frbl-hover-bg-scale:hover::before {
    transform: scale(var(--frbl-hover-scale, 1.1));
}
```

### Contenido sobre la Imagen

```css
.frbl-hover-bg-scale > * {
    position: relative;
    z-index: 1; /* Mantiene el contenido visible sobre la imagen */
}
```

---

## 🔧 Implementación Técnica de FrontBlocks

### Archivos del Plugin FrontBlocks

1. **`includes/Frontend/Animations.php`**
   - Añadidos atributos `frblHoverBgScale` y `frblHoverBgScaleAmount`
   - Lógica para detectar y aplicar el efecto en el frontend
   - Inyección de variable CSS `--frbl-hover-scale`
   - Prefijo `frbl` identifica funcionalidades de FrontBlocks

2. **`assets/animations/frontblocks-animation-option.jsx`**
   - Nuevo panel "Hover Effects" en el editor de bloques
   - ToggleControl para activar/desactivar
   - RangeControl para ajustar la escala
   - Integrado en el módulo de animaciones de FrontBlocks

3. **`assets/animations/frontblocks-animations.css`**
   - Estilos CSS con clase `.frbl-hover-bg-scale`
   - Compatible con `--inline-bg-image` (GenerateBlocks)
   - Compatible con `background-image` estándar

---

## 🎯 Casos de Uso Comunes

### 1. Galería de Proyectos

Aplica el efecto a cada elemento del loop:

```
Query Loop Block
└── Container (frbl-hover-bg-scale activado)
    ├── Background Image (--inline-bg-image)
    ├── Título
    └── Descripción
```

### 2. Grid de Posts

Añade dinamismo a tu blog:

```
Posts Grid
└── Post Card (frbl-hover-bg-scale activado, escala 1.15)
    ├── Featured Image como background
    ├── Categoría
    ├── Título
    └── Extracto
```

### 3. Sección Hero

Efecto sutil en banners:

```
Hero Section (frbl-hover-bg-scale activado, escala 1.05)
├── Background Image
├── Heading
└── CTA Button
```

---

## ⚙️ Opciones de Configuración

| Opción | Tipo | Rango | Por Defecto | Descripción |
|--------|------|-------|-------------|-------------|
| **Scale Background Image on Hover** | Toggle | On/Off | Off | Activa el efecto de zoom |
| **Scale Amount** | Slider | 1.0 - 2.0 | 1.1 | Cantidad de zoom (1.1 = 110%) |

---

## 🚀 Mejores Prácticas

### Valores Recomendados por Tipo de Contenido

- **Galerías pequeñas** (< 300px): Scale 1.1 - 1.15
- **Cards medianas** (300-500px): Scale 1.1 - 1.2
- **Hero sections** (> 500px): Scale 1.05 - 1.1
- **Imágenes grandes** (fullscreen): Scale 1.05

### Consideraciones de Rendimiento

- ✅ Usa `transform: scale()` (GPU-accelerated)
- ✅ Transición suave de 0.4s (no afecta rendimiento)
- ✅ Usa `overflow: hidden` para evitar reflows
- ✅ Compatible con cualquier tamaño de imagen

### Accesibilidad

- ✅ El efecto solo se aplica en hover (no interfiere con navegación por teclado)
- ✅ El contenido permanece legible durante el efecto
- ✅ No afecta el contraste de color

---

## 🐛 Troubleshooting

### El efecto no funciona

1. **Verifica que el elemento tenga una imagen de fondo**
   - Debe tener `background-image` CSS o `--inline-bg-image`
   
2. **Comprueba que el toggle esté activado**
   - Panel "Hover Effects" → "Scale Background Image on Hover"

3. **Asegúrate de que el bloque se guardó correctamente**
   - Guarda el post/página después de activar el efecto

### La imagen no se ve

1. **Verifica el z-index del contenido**
   - El plugin automáticamente pone el contenido con `z-index: 1`
   
2. **Comprueba que no hay conflictos CSS**
   - Algunos themes pueden sobrescribir los estilos

### El efecto es demasiado rápido/lento

Actualmente la duración es fija (0.4s). Si necesitas personalizarlo, añade CSS personalizado:

```css
.frbl-hover-bg-scale::before {
    transition-duration: 0.6s !important; /* Más lento */
}
```

---

## 📝 Changelog de FrontBlocks Hover Effect

### v1.0 (2026-01-21)

- ✨ Lanzamiento inicial de **FrontBlocks Hover Effect**
- ✨ Soporte nativo para `--inline-bg-image` (GenerateBlocks)
- ✨ Soporte para `background-image` CSS estándar
- ✨ Control configurable de escala (1.0 - 2.0)
- ✨ Integración completa con panel de Animations de FrontBlocks
- ✨ Prefijo `.frbl-` para todas las clases CSS
- ✨ Compatible con GeneratePress y GenerateBlocks

---

## 🔮 Próximas Mejoras

Ideas para futuras versiones:

- [ ] Duración de transición configurable
- [ ] Diferentes tipos de easing (ease, linear, bounce)
- [ ] Efecto de zoom en diferentes direcciones
- [ ] Combinación con efectos de overlay
- [ ] Animación al entrar en viewport (scroll)

---

## 💬 Soporte FrontBlocks

Si tienes problemas o sugerencias sobre **FrontBlocks Hover Effect**:

- **Plugin**: FrontBlocks for GeneratePress
- **Email**: info@close.technology
- **Web**: https://close.technology
- **Documentación**: https://close.technology/wordpress-plugins/frontblocks/
- **WordPress.org**: https://wordpress.org/plugins/frontblocks/

---

## 📄 Licencia

GPL-2.0+ (Open Source)

---

## 🏷️ Sobre FrontBlocks

**FrontBlocks** es un plugin gratuito desarrollado por **Close·technology** que extiende las capacidades de GeneratePress y GenerateBlocks, añadiendo funcionalidades avanzadas como:

- ✨ Animaciones y efectos visuales
- 🎨 Efectos de hover personalizables
- 🎯 Herramientas para Query Loops
- 🔧 Utilidades para desarrollo web moderno

---

**¡Disfruta creando efectos increíbles con FrontBlocks!** 🎉
