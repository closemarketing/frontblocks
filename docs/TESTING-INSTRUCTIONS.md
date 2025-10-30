# 🧪 Instrucciones de Testing - FrontBlocks Settings

## 🚀 Inicio Rápido

La implementación está **lista para usar**. El CSS ya está compilado y no necesitas ejecutar ningún comando adicional.

---

## 🖥️ Cómo Ver los Cambios

### 1. Accede al Admin de WordPress

```
URL: http://tu-sitio.local/wp-admin
```

### 2. Navega a la Página de Settings

```
Apariencia → Frontblocks
```

O directamente:
```
http://tu-sitio.local/wp-admin/themes.php?page=frontblocks-settings
```

---

## ✅ Qué Deberías Ver

### 🎨 Header Section
- **Título**: "FrontBlocks Settings" (grande, bold)
- **Descripción**: "Configure your FrontBlocks features and options."
- **Badge de Versión**: Arriba a la derecha, color **#687DF9** (púrpura-azul)

### 📋 Settings Card
- **Card blanco** con borde gris claro
- **Header con gradiente** gris claro
- **Título de sección**: "Features"
- **Descripción**: "Enable or disable Frontblocks features."

### 🔘 Toggle Switch
- **Label a la izquierda**: "Enable testimonials"
- **Toggle a la derecha**: Switch moderno
  - OFF: círculo gris con fondo gris
  - ON: círculo blanco con fondo **#687DF9**
- **Descripción debajo**: Texto pequeño gris
- **Animación suave** al hacer clic

### 💾 Save Button
- **Color de fondo**: **#687DF9**
- **Icono**: Checkmark a la izquierda
- **Texto**: "Save Settings"
- **Hover**: Color se oscurece ligeramente (#5565ed)

### 👣 Footer
- **Texto**: "Made with ❤️ by Close·marketing"
- **Link**: Color **#687DF9**

---

## 🧪 Tests Funcionales

### Test 1: Toggle Switch
1. ✅ Haz clic en el toggle
2. ✅ Debería animarse suavemente (200ms)
3. ✅ El color debería cambiar a #687DF9
4. ✅ Haz clic de nuevo, debería volver a gris

### Test 2: Guardar Settings
1. ✅ Activa el toggle
2. ✅ Haz clic en "Save Settings"
3. ✅ La página debería recargar
4. ✅ El toggle debería mantener su estado (ON)
5. ✅ Mensaje de éxito de WordPress debería aparecer

### Test 3: Hover Effects
1. ✅ Pasa el mouse sobre el toggle → color debería cambiar
2. ✅ Pasa el mouse sobre "Save Settings" → color más oscuro
3. ✅ Pasa el mouse sobre el card → sombra más pronunciada
4. ✅ Pasa el mouse sobre el link footer → color más oscuro

### Test 4: Keyboard Navigation
1. ✅ Presiona Tab para navegar
2. ✅ El toggle debería recibir focus (anillo visible)
3. ✅ El botón "Save Settings" debería recibir focus
4. ✅ Presiona Space sobre el toggle → debería cambiar estado
5. ✅ Presiona Enter sobre el botón → debería guardar

---

## 📱 Tests Responsive

### Desktop (> 1024px)
1. ✅ Header en una línea (título izq, badge der)
2. ✅ Toggle a la derecha del label
3. ✅ Botón "Save Settings" a la derecha
4. ✅ Todo bien espaciado

### Tablet (640px - 1024px)
1. ✅ Layout similar a desktop
2. ✅ Padding reducido
3. ✅ Todo legible y funcional

### Mobile (< 640px)
1. ✅ Resize la ventana a 375px
2. ✅ Header debería apilar (título arriba, badge abajo)
3. ✅ Toggle debería mantenerse a la derecha
4. ✅ Botón "Save Settings" debería estar centrado o abajo
5. ✅ Cards con padding reducido

**Cómo testearlo:**
- Chrome DevTools: F12 → Toggle device toolbar (Ctrl+Shift+M)
- Selecciona iPhone 12, iPad, etc.

---

## 🎨 Verificación de Colores

### Color Primario: #687DF9

Deberías ver este color en:

1. ✅ **Badge de versión** (fondo más claro: #e4e6fd)
2. ✅ **Toggle activo** (fondo del toggle)
3. ✅ **Botón "Save Settings"** (fondo)
4. ✅ **Link footer** (texto)
5. ✅ **Focus rings** (al navegar con teclado)

**Verificación visual**:
- El color es un **púrpura-azul** vibrante
- NO es azul estándar (#2563eb)
- Se ve consistente en todos los elementos

---

## 🔍 DevTools Inspection

### Chrome DevTools

1. **Abre DevTools**: F12
2. **Inspecciona el toggle**:
   ```
   Busca: <label class="frbl-toggle">
   ```
3. **Verifica el color**:
   ```css
   background-color: #687df9
   ```

4. **Inspecciona el botón**:
   ```
   Busca: <button type="submit"
   Clase: tw-bg-primary-500
   ```

5. **Verifica CSS compilado**:
   ```
   Network tab → settings.css
   Tamaño: ~21KB
   Status: 200
   ```

---

## 🐛 Troubleshooting

### El toggle no se ve
**Problema**: CSS no cargado  
**Solución**:
```bash
cd /path/to/frontblocks
npm run build:css
```

### Los colores no son #687DF9
**Problema**: CSS antiguo cacheado  
**Solución**:
1. Ctrl+Shift+R (hard refresh)
2. O limpia caché del navegador

### El toggle no funciona
**Problema**: JavaScript error (no debería haber JS)  
**Solución**:
1. Abre Console (F12 → Console)
2. Verifica si hay errores
3. Reporta el error

### El diseño se ve roto
**Problema**: Conflicto con otros plugins  
**Solución**:
1. Desactiva otros plugins temporalmente
2. Verifica si el problema persiste
3. Reporta qué plugin causa conflicto

---

## 📸 Screenshots de Referencia

### Toggle OFF
```
Enable testimonials                                    OFF
                                                    [○─────]
Enable testimonials custom post type and functionality.
When enabled, you can create and manage testimonials...
```

### Toggle ON
```
Enable testimonials                                    ON
                                                    [─────○]
Enable testimonials custom post type and functionality.
When enabled, you can create and manage testimonials...
```

### Full Page Layout
```
╔═══════════════════════════════════════════════════════════╗
║ FrontBlocks Settings                    [Version 1.1.0]  ║
║ Configure your FrontBlocks features and options.         ║
╚═══════════════════════════════════════════════════════════╝

┌─────────────────────────────────────────────────────────┐
│ ╔═══════════════════════════════════════════════════╗   │
│ ║ Features                                          ║   │
│ ║ Enable or disable Frontblocks features.          ║   │
│ ╚═══════════════════════════════════════════════════╝   │
│                                                         │
│ Enable testimonials                          [─────○]  │
│ Enable testimonials custom post type...                │
│                                                         │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ ╔═══════════════════════════════════════════════════╗   │
│ ║ WooCommerce Features                              ║   │
│ ║ WooCommerce FrontBlocks PRO is a premium...       ║   │
│ ║ [Buy WooCommerce FrontBlocks PRO]                 ║   │
│ ╚═══════════════════════════════════════════════════╝   │
└─────────────────────────────────────────────────────────┘

───────────────────────────────────────────────────────────
Changes will be applied        ┌──────────────────────┐
immediately after saving.      │ ✓ Save Settings      │
                               └──────────────────────┘

              Made with ❤️ by Close·marketing
```

---

## ✅ Checklist Completo

Marca cada ítem después de verificarlo:

### Visual
- [ ] Page se ve moderna y profesional
- [ ] Color #687DF9 está presente
- [ ] Toggle switch se ve bien
- [ ] Cards tienen sombras y bordes
- [ ] Badge de versión visible
- [ ] Footer con link

### Interactividad
- [ ] Toggle hace clic correctamente
- [ ] Toggle cambia de color al activar
- [ ] Animación suave (200ms)
- [ ] Hover effects funcionan
- [ ] Botón "Save" guarda correctamente

### Responsive
- [ ] Desktop (1920px) se ve bien
- [ ] Tablet (768px) se ve bien
- [ ] Mobile (375px) se ve bien
- [ ] No scroll horizontal
- [ ] Todo es legible en mobile

### Accesibilidad
- [ ] Tab navigation funciona
- [ ] Focus visible en todos los elementos
- [ ] Toggle puede activarse con Space
- [ ] Labels correctos
- [ ] Contraste suficiente

### Funcionalidad
- [ ] Settings se guardan
- [ ] Settings persisten después de guardar
- [ ] No hay errores en console
- [ ] No hay errores PHP
- [ ] Mensaje de éxito aparece

---

## 🎯 Resultado Esperado

Después de completar todos los tests, deberías tener:

✅ Una página de settings moderna y profesional  
✅ Toggle switches funcionando perfectamente  
✅ Color primario #687DF9 en todos los elementos  
✅ Diseño responsive en todos los dispositivos  
✅ Navegación por teclado funcional  
✅ Settings guardando correctamente  

---

## 📞 Reportar Problemas

Si encuentras algún problema:

1. **Captura screenshot** del problema
2. **Abre Console** (F12) y copia errores
3. **Verifica versión** del plugin (debería ser 1.1.0)
4. **Reporta** con toda la información

---

## 🎉 ¡Listo!

La implementación está completa y lista para usar. Solo necesitas:

1. Ir a **Apariencia → Frontblocks**
2. Ver la nueva interfaz
3. Probar el toggle switch
4. Verificar que todo funcione

**¡Disfruta tu nueva página de settings!** 🚀

---

**Última actualización**: 30 de Octubre, 2025  
**Versión**: 1.1.0

