# Full Page Scroll Feature

## Resumen

Se ha implementado una nueva funcionalidad PRO de **Full Page Scroll** que permite crear experiencias de scroll tipo fullpage con navegación lateral mediante puntos.

**Arquitectura:**
- **FrontBlocks (free):** Solo contiene el toggle de configuración en Settings
- **FrontBlocks PRO:** Contiene toda la lógica, clases y assets

## Archivos en FrontBlocks (free)

### Settings Admin
**Archivo modificado:** `includes/Admin/Settings.php`
- Agregada nueva propiedad: `$option_enable_fullpage_scroll`
- Agregado nuevo campo en sección WooCommerce Features
- Agregado método: `field_enable_fullpage_scroll()`
- Incluido en array de features PRO
- Incluido en mapa de iconos
- Incluido en lista de opciones booleanas para sanitización

### Icono SVG
**Archivo:** `assets/admin/icons/fullpage-scroll.svg`
- Icono personalizado para el toggle en settings
- Representa visualmente secciones de página con puntos de navegación

## Archivos en FrontBlocks PRO

### 1. Clase Frontend
**Archivo:** `includes/Frontend/FullPage.php`
- Clase que gestiona la carga de assets (CSS y JavaScript)
- Verifica que la licencia PRO esté activa y válida
- Lee el setting desde FrontBlocks settings
- Solo se activa si el toggle está habilitado

### 2. Assets JavaScript
**Archivo:** `assets/fullpage/frontblocks-fullpage.js`
- Script de scroll suave tipo fullpage
- Detecta contenedores con clase `.contenedor-fullpage`
- Detecta secciones con clase `.seccion-snap`
- Crea navegación lateral con puntos automáticamente
- Gestiona el scroll con rueda del ratón
- Actualiza el punto activo según la sección visible
- Permite interrumpir el scroll en curso

### 3. Assets CSS
**Archivo:** `assets/fullpage/frontblocks-fullpage.css`
- Estilos para contenedor fullpage
- Estilos para secciones snap
- Navegación lateral con puntos
- Efectos hover y estados activos
- Tooltips en los puntos de navegación
- Responsive para móvil

### 4. Plugin Main
**Archivo modificado:** `includes/Plugin_Main.php`
- Registrado el módulo `Frontend\FullPage()`
- Se carga automáticamente con FrontBlocks PRO

## Cómo Usar

### 1. Activar la funcionalidad
1. Ir a **FrontBlocks → Settings**
2. En la sección **WooCommerce Features**
3. Activar el toggle **"Enable Full Page Scroll"** (requiere licencia PRO válida)
4. Guardar cambios

### 2. Implementar en el sitio web
Agregar las clases CSS necesarias en tu contenido:

#### Estructura HTML:
```html
<div class="frontblocks-fullpage">
    <section class="frontblocks-fullpage-section">
        <!-- Contenido sección 1 -->
    </section>
    <section class="frontblocks-fullpage-section">
        <!-- Contenido sección 2 -->
    </section>
    <section class="frontblocks-fullpage-section">
        <!-- Contenido sección 3 -->
    </section>
</div>
```

#### Con GenerateBlocks:
1. Crear un Container con clase `frontblocks-fullpage`
2. Dentro, crear varios Containers con clase `frontblocks-fullpage-section`
3. Cada sección ocupará el 100% del alto de viewport (100vh)

### 3. Navegación automática
- Se creará automáticamente una navegación lateral con puntos
- Los puntos se actualizan según la sección visible
- Puedes hacer clic en los puntos para navegar
- El scroll con rueda del ratón avanza sección por sección

## Características

✅ **Scroll suave:** Animación fluida entre secciones
✅ **Navegación lateral:** Puntos automáticos con estados activos
✅ **Tooltips:** Información de la sección al hacer hover
✅ **Control con rueda:** Scroll controlado sección por sección
✅ **Interrupción:** Permite interrumpir el scroll en curso
✅ **Responsive:** Adaptado para móviles
✅ **PRO Feature:** Requiere licencia válida de FrontBlocks PRO

## Clases CSS Disponibles

| Clase | Descripción |
|-------|-------------|
| `.frontblocks-fullpage` | Contenedor principal que activa el fullpage |
| `.frontblocks-fullpage-section` | Cada sección de contenido (100vh) |
| `.frontblocks-fullpage-nav` | Navegación lateral (se crea automáticamente) |
| `.frontblocks-fullpage-dot` | Cada punto de navegación |
| `.frontblocks-fullpage-dot.active` | Punto activo actual |

## Personalización CSS

Puedes personalizar los colores y estilos de los puntos:

```css
/* Cambiar color de puntos */
.frontblocks-fullpage-dot {
    border-color: #tu-color;
}

.frontblocks-fullpage-dot.active {
    background: #tu-color;
    border-color: #tu-color;
}

/* Cambiar posición de navegación */
.frontblocks-fullpage-nav {
    right: 50px; /* Por defecto 30px */
}
```

## Compatibilidad

- **WordPress:** 6.7+
- **PHP:** 7.4+
- **Navegadores:** Chrome, Firefox, Safari, Edge (últimas versiones)
- **Responsive:** Sí (adaptado para móvil)
- **FrontBlocks:** 1.0.3+
- **FrontBlocks PRO:** 1.3.1+ con licencia válida

## Notas Técnicas

- El script solo se carga si el toggle está activado
- Verifica que exista el contenedor `.frontblocks-fullpage` antes de ejecutar
- Verifica que existan secciones `.frontblocks-fullpage-section` antes de crear la navegación
- Usa `scroll-snap-type: y mandatory` para mejor compatibilidad
- Los tooltips se ocultan en móvil para mejor UX
- El scroll permite interrupción después de 150ms

## Ejemplo de Implementación

### Caso de uso típico:
1. **Landing page con secciones completas**
   - Hero section
   - Features
   - Pricing
   - Contact

2. **Portfolio con proyectos**
   - Cada proyecto en una sección

3. **Presentación de producto**
   - Intro
   - Características
   - Testimonios
   - CTA

## Créditos

Implementado por **Closemarketing** - https://close.technology
Basado en el script de smooth scroll de **CLOSE Progradent**
