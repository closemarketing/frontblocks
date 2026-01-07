# Changelog - Full Page Scroll Feature

## [2025-12-19] - Full Page Scroll Implementation

### Added

#### En FrontBlocks (free):
- **Toggle de configuración:** Setting en Admin para activar/desactivar
- **Icono:** `assets/admin/icons/fullpage-scroll.svg`
  - Icono SVG personalizado para settings
  - Representa secciones de página con navegación

#### En FrontBlocks PRO:
- **Nueva funcionalidad PRO:** Full Page Scroll con navegación lateral
- **Clase Frontend:** `includes/Frontend/FullPage.php`
  - Gestiona la carga condicional de assets
  - Verifica licencia PRO activa y válida
  - Lee setting desde FrontBlocks
  - Se activa mediante toggle en settings

- **JavaScript:** `assets/fullpage/frontblocks-fullpage.js`
  - Script de scroll suave tipo fullpage
  - Navegación automática con puntos laterales
  - Control de scroll con rueda del ratón
  - Detección de sección activa
  - Sistema de interrupción de scroll

- **CSS:** `assets/fullpage/frontblocks-fullpage.css`
  - Estilos para contenedor fullpage (`.frontblocks-fullpage`)
  - Estilos para secciones snap (`.frontblocks-fullpage-section`)
  - Navegación lateral con puntos (`.frontblocks-fullpage-nav`, `.frontblocks-fullpage-dot`)
  - Estados hover y activo
  - Tooltips en puntos de navegación
  - Responsive para móvil

- **Documentación:** `docs/FULLPAGE-SCROLL.md`
  - Guía completa de uso
  - Ejemplos de implementación
  - Personalización CSS
  - Notas técnicas

### Modified

#### En FrontBlocks (free):
- **`includes/Admin/Settings.php`**
  - Agregada propiedad `$option_enable_fullpage_scroll`
  - Agregado campo de configuración en sección WooCommerce Features
  - Agregado método `field_enable_fullpage_scroll()`
  - Incluido en array de features PRO
  - Incluido en mapa de iconos
  - Incluido en sanitización de opciones booleanas

#### En FrontBlocks PRO:
- **`includes/Plugin_Main.php`**
  - Registrado nuevo módulo `Frontend\FullPage()`
  - Carga automática del módulo en `load_frontend_modules()`

### Technical Details
- **Clases CSS principales:**
  - `.frontblocks-fullpage`: Activa el modo fullpage
  - `.frontblocks-fullpage-section`: Define cada sección (100vh)
  - `.frontblocks-fullpage-nav`: Navegación lateral (auto-generada)
  - `.frontblocks-fullpage-dot`: Puntos de navegación
  - `.frontblocks-fullpage-dot.active`: Punto activo

- **Comportamiento:**
  - Scroll suave entre secciones
  - Navegación con rueda del ratón (sección por sección)
  - Click en puntos para navegar directamente
  - Tooltips informativos (ocultos en móvil)
  - Permite interrumpir el scroll en curso

- **Validación de licencia:**
  - Solo se activa con FrontBlocks PRO activo
  - Requiere licencia válida
  - Toggle habilitado en settings

- **Compatibilidad:**
  - WordPress 6.7+
  - PHP 7.4+
  - Navegadores modernos (Chrome, Firefox, Safari, Edge)
  - Responsive (adaptado para móvil)

### Usage Example
```html
<div class="frontblocks-fullpage">
    <section class="frontblocks-fullpage-section">
        <!-- Sección 1 -->
    </section>
    <section class="frontblocks-fullpage-section">
        <!-- Sección 2 -->
    </section>
    <section class="frontblocks-fullpage-section">
        <!-- Sección 3 -->
    </section>
</div>
```

### Files Structure

#### FrontBlocks (free):
```
frontblocks/
├── includes/
│   └── Admin/
│       └── Settings.php (MODIFIED - toggle only)
├── assets/
│   └── admin/
│       └── icons/
│           └── fullpage-scroll.svg (NEW)
└── docs/
    ├── FULLPAGE-SCROLL.md (NEW)
    └── CHANGELOG-FULLPAGE.md (NEW)
```

#### FrontBlocks PRO:
```
frontblocks-pro/
├── includes/
│   ├── Frontend/
│   │   └── FullPage.php (NEW - all logic)
│   └── Plugin_Main.php (MODIFIED)
└── assets/
    └── fullpage/ (NEW)
        ├── frontblocks-fullpage.js
        └── frontblocks-fullpage.css
```

### Credits
- **Author:** Closemarketing
- **Based on:** CLOSE Progradent smooth scroll implementation
- **Date:** December 19, 2025
- **Version:** FrontBlocks 1.0.3+
- **License:** GPL-2.0+

### Notes
- Feature requiere FrontBlocks PRO 1.3.1+ con licencia válida
- Script optimizado para performance (throttling, debouncing)
- Compatible con GenerateBlocks
- Sin dependencias externas (vanilla JavaScript)
- CSS puro, sin preprocessadores

### Future Enhancements (Optional)
- [ ] Opción para personalizar colores de puntos desde settings
- [ ] Opción para personalizar posición de navegación
- [ ] Soporte para transiciones personalizadas
- [ ] Integración con animaciones de FrontBlocks
- [ ] Opción para desactivar tooltips
- [ ] Keyboard navigation (arrow keys)
- [ ] Touch gestures para móvil
