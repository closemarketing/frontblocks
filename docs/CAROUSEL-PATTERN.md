# Carousel Pattern - FrontBlocks

Patrón de carrusel para bloques nativos de WordPress usando la funcionalidad de Grid Carousel de FrontBlocks.

## 📋 Descripción

Este patrón permite crear carrusels fluidos y responsivos utilizando bloques nativos de WordPress (Cover Blocks) dentro de un contenedor Grid con opciones de carrusel activadas.

## ✨ Características

- **Transiciones suaves**: Animaciones fluidas entre slides
- **Responsive**: Configuración adaptable para desktop, tablet y mobile
- **Bloques nativos**: Utiliza bloques core de WordPress (Cover, Group, Heading, Button, etc.)
- **Personalizable**: Colores, gradientes, imágenes de fondo y contenido completamente personalizable
- **Arrows navigation**: Navegación con flechas laterales

## 🎨 Casos de Uso

1. **Hero Sliders**
   - Múltiples mensajes principales
   - Promociones destacadas
   - Anuncios importantes

2. **Testimonios**
   - Reseñas de clientes
   - Casos de éxito
   - Opiniones destacadas

3. **Portfolio**
   - Proyectos destacados
   - Galería de trabajos
   - Servicios principales

4. **Llamadas a la acción**
   - Múltiples CTAs
   - Ofertas especiales
   - Eventos próximos

## 🔧 Configuración

### Opciones del Grid

```
frblGridOption="carousel"
frblItemsToView="1"
frblLaptopToView="1"
frblTabletToView="1"
frblMobileToView="1"
frblCarouselButtons="arrows"
frblCarouselButtonsPosition="side"
```

### Estructura HTML

El patrón genera esta estructura:

```html
<div class="glide">
  <div class="glide__track">
    <div class="wp-block-group frontblocks-carousel glide__slides">
      <div class="glide__slide"><!-- Slide 1 --></div>
      <div class="glide__slide"><!-- Slide 2 --></div>
    </div>
  </div>
  <div class="glide__arrows glide__arrows--top">
    <button class="glide__arrow glide__arrow--left">←</button>
    <button class="glide__arrow glide__arrow--right">→</button>
  </div>
</div>
```

## 🚀 Acceso Rápido al Patrón

El patrón está registrado automáticamente en WordPress y aparece en la pestaña **"Patterns"** del editor de bloques.

### Cómo acceder:

1. En el editor de WordPress, haz clic en el botón **+** (Añadir bloque)
2. Ve a la pestaña **"Patterns"** en la parte superior
3. Busca en la categoría **"FrontBlocks"**
4. Selecciona **"Hero Carousel"**
5. El patrón se insertará automáticamente en tu página

También puedes buscarlo escribiendo: `carousel`, `hero`, `slider`, o `banner` en el buscador de patrones.

## 📝 Patrón de Ejemplo

### Carrusel con Cover Blocks

```html
<!-- wp:group {"layout":{"type":"grid","columnCount":1,"minimumColumnWidth":null},"frblGridOption":"carousel","frblItemsToView":"1","frblLaptopToView":"1","frblTabletToView":"1"} -->
<div class="wp-block-group">
  
  <!-- wp:cover {"dimRatio":40,"isUserOverlayColor":true,"customGradient":"linear-gradient(135deg,rgb(224,15,15) 0%,rgb(225,15,15) 100%)","isDark":false,"sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"120px","bottom":"120px"}}}} -->
  <div class="wp-block-cover alignfull is-light" style="padding-top:120px;padding-bottom:120px">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim has-background-gradient" style="background:linear-gradient(135deg,rgb(224,15,15) 0%,rgb(225,15,15) 100%)"></span>
    <div class="wp-block-cover__inner-container">
      
      <!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"800px"}} -->
      <div class="wp-block-group alignwide">
        
        <!-- wp:heading {"textAlign":"left","level":1,"style":{"typography":{"fontSize":"52px","fontWeight":"700","lineHeight":"1.2"}},"textColor":"white"} -->
        <h1 class="wp-block-heading has-text-align-left has-white-color has-text-color" style="font-size:52px;font-weight:700;line-height:1.2">Lorem ipsum</h1>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"align":"left","style":{"typography":{"fontSize":"18px"},"spacing":{"margin":{"top":"20px"}}},"textColor":"white"} -->
        <p class="has-text-align-left has-white-color has-text-color" style="margin-top:20px;font-size:18px">Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum</p>
        <!-- /wp:paragraph -->

        <!-- wp:buttons {"style":{"spacing":{"margin":{"top":"40px"}}}} -->
        <div class="wp-block-buttons" style="margin-top:40px">
          <!-- wp:button {"backgroundColor":"primary","className":"is-style-fill","style":{"border":{"radius":"25px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"15px","bottom":"15px"}}}} -->
          <div class="wp-block-button is-style-fill">
            <a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:25px;padding-top:15px;padding-right:35px;padding-bottom:15px;padding-left:35px">Lorem ipsum</a>
          </div>
          <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
        
      </div>
      <!-- /wp:group -->
      
    </div>
  </div>
  <!-- /wp:cover -->

  <!-- wp:cover {"dimRatio":40,"customOverlayColor":"#006f49","isUserOverlayColor":true,"isDark":false,"sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"120px","bottom":"120px"}}}} -->
  <div class="wp-block-cover alignfull is-light" style="padding-top:120px;padding-bottom:120px">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#006f49"></span>
    <div class="wp-block-cover__inner-container">
      
      <!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"800px"}} -->
      <div class="wp-block-group alignwide">
        
        <!-- wp:heading {"textAlign":"left","level":1,"style":{"typography":{"fontSize":"52px","fontWeight":"700","lineHeight":"1.2"}},"textColor":"white"} -->
        <h1 class="wp-block-heading has-text-align-left has-white-color has-text-color" style="font-size:52px;font-weight:700;line-height:1.2">Lorem ipsum</h1>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"align":"left","style":{"typography":{"fontSize":"18px"},"spacing":{"margin":{"top":"20px"}}},"textColor":"white"} -->
        <p class="has-text-align-left has-white-color has-text-color" style="margin-top:20px;font-size:18px">Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum</p>
        <!-- /wp:paragraph -->

        <!-- wp:buttons {"style":{"spacing":{"margin":{"top":"40px"}}}} -->
        <div class="wp-block-buttons" style="margin-top:40px">
          <!-- wp:button {"backgroundColor":"primary","className":"is-style-fill","style":{"border":{"radius":"25px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"15px","bottom":"15px"}}}} -->
          <div class="wp-block-button is-style-fill">
            <a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:25px;padding-top:15px;padding-right:35px;padding-bottom:15px;padding-left:35px">Lorem ipsum</a>
          </div>
          <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
        
      </div>
      <!-- /wp:group -->
      
    </div>
  </div>
  <!-- /wp:cover -->
  
</div>
<!-- /wp:group -->
```

## 🎯 Personalización

### Cambiar colores de fondo

**Gradiente:**
```html
customGradient="linear-gradient(135deg,rgb(224,15,15) 0%,rgb(225,15,15) 100%)"
```

**Color sólido:**
```html
customOverlayColor="#006f49"
```

### Agregar imagen de fondo

```html
<div class="wp-block-cover alignfull is-light" style="padding-top:120px;padding-bottom:120px">
  <img class="wp-block-cover__image-background" src="URL_DE_IMAGEN" alt="" />
  <span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim"></span>
  ...
</div>
```

### Ajustar espaciado vertical

```html
style="padding-top:120px;padding-bottom:120px"
```

Cambia los valores `120px` al espaciado deseado.

### Cambiar ancho del contenido

```html
layout":{"type":"constrained","contentSize":"800px"}
```

Ajusta `800px` al ancho deseado (por ejemplo: `600px`, `1000px`, `1200px`).

### Configurar autoplay

Agrega el atributo en el grupo principal:

```html
frblCarouselAutoplay="3000"
```

Donde `3000` son milisegundos (3 segundos).

## 📱 Responsive

El patrón es completamente responsive:

- **Desktop**: Muestra 1 slide con transiciones suaves
- **Laptop**: Configurado para 1 slide (`frblLaptopToView="1"`)
- **Tablet**: Configurado para 1 slide (`frblTabletToView="1"`)
- **Mobile**: Configurado para 1 slide con gestos táctiles

## 🎨 Variaciones

### Con imagen de fondo

Reemplaza el `customGradient` por una imagen:

```html
<div class="wp-block-cover alignfull" style="padding-top:120px;padding-bottom:120px">
  <img decoding="async" class="wp-block-cover__image-background wp-image-123" src="URL_IMAGEN" data-object-fit="cover"/>
  <span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim"></span>
  ...
</div>
```

### Múltiples slides por vista

Para mostrar más de 1 slide a la vez:

```html
frblItemsToView="3"
frblLaptopToView="2"
frblTabletToView="2"
frblMobileToView="1"
```

### Con puntos de navegación (bullets)

Cambia el tipo de botones:

```html
frblCarouselButtons="bullets"
```

## 🔍 Solución de Problemas

### El carrusel no se muestra

1. Verifica que FrontBlocks esté activado
2. Limpia la caché del navegador
3. Asegúrate de que los atributos `frblGridOption="carousel"` estén presentes

### Los slides se muestran partidos (50% cada uno)

- Asegúrate de tener `frblItemsToView="1"` en el grupo
- Verifica que el CSS de Frontblocks esté cargado correctamente

### Las transiciones no son suaves

- El CSS incluye transiciones suaves por defecto
- Si no funciona, verifica conflictos con otros plugins

## 📚 Recursos Relacionados

- [Full Page Scroll Pattern](FULLPAGE-SCROLL.md)
- [Shape Animations](SHAPE-ANIMATIONS.md)
- [Container Edge Alignment](CONTAINER-EDGE-ALIGNMENT.md)

## 🏆 Mejores Prácticas

1. **Contenido conciso**: Mantén los textos breves y claros
2. **Imágenes optimizadas**: Usa imágenes comprimidas para mejor rendimiento
3. **Contraste**: Asegura buen contraste entre texto y fondo
4. **Accesibilidad**: Incluye textos alt en las imágenes
5. **CTA claro**: Botones con acciones específicas y visibles
6. **Número de slides**: Entre 3-5 slides es ideal para mantener la atención
7. **Autoplay moderado**: Si usas autoplay, 4-5 segundos por slide es óptimo

## 📄 Licencia

Implementado por **Closemarketing** - https://close.technology

## 🔄 Changelog

### v1.0 - 2026-02-10
- ✨ Patrón inicial de carrusel con Cover Blocks
- 🎨 Soporte para gradientes y colores personalizados
- 📱 Configuración responsive completa
- ⚡ Transiciones suaves entre slides
- 🎯 Navegación con flechas laterales
