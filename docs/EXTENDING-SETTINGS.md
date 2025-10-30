# 🔌 Extending FrontBlocks Settings

## Cómo Agregar Settings desde Otro Plugin

Desde `frontblocks-pro` u otros plugins, puedes agregar tus propios settings a la página de FrontBlocks usando el hook `frontblocks_register_settings`.

---

## 📖 Método 1: Agregar Toggle Switch

### Ejemplo Completo

```php
<?php
/**
 * Example: Add a toggle switch from FrontBlocks PRO
 */

add_action( 'frontblocks_register_settings', 'frblp_register_pro_settings' );

function frblp_register_pro_settings() {
	// 1. Add a new section (optional, puedes usar una existente)
	add_settings_section(
		'frontblocks_pro_section_features',
		__( 'PRO Features', 'frontblocks-pro' ),
		function () {
			echo '<p>' . esc_html__( 'Advanced features available in FrontBlocks PRO.', 'frontblocks-pro' ) . '</p>';
		},
		'frontblocks-settings' // Importante: usar el slug de la página
	);

	// 2. Add your toggle field
	add_settings_field(
		'enable_advanced_animations',
		__( 'Advanced Animations', 'frontblocks-pro' ),
		'frblp_field_advanced_animations',
		'frontblocks-settings', // Importante: mismo slug
		'frontblocks_pro_section_features' // Tu sección
	);
}

/**
 * Render the toggle field
 */
function frblp_field_advanced_animations() {
	$options = get_option( 'frontblocks_settings', array() );
	$enabled = (bool) ( $options['enable_advanced_animations'] ?? false );
	?>
	<div class="tw-flex tw-items-center tw-justify-between">
		<div class="tw-flex-grow">
			<label for="enable_advanced_animations" class="tw-text-sm tw-font-medium tw-text-gray-900 tw-cursor-pointer">
				<?php echo esc_html__( 'Advanced Animations', 'frontblocks-pro' ); ?>
			</label>
			<p class="tw-mt-1 tw-text-sm tw-text-gray-500">
				<?php echo esc_html__( 'Enable advanced animation effects for your blocks. Requires FrontBlocks PRO.', 'frontblocks-pro' ); ?>
			</p>
		</div>
		<label class="frbl-toggle">
			<input type="checkbox" 
				id="enable_advanced_animations" 
				name="frontblocks_settings[enable_advanced_animations]" 
				value="1" 
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>
	</div>
	<?php
}
```

---

## 📖 Método 2: Usar la Misma Sección

Si quieres agregar toggles a la sección "Features" existente:

```php
<?php
add_action( 'frontblocks_register_settings', 'frblp_add_to_features_section' );

function frblp_add_to_features_section() {
	// Agregar a la sección existente 'frontblocks_section_features'
	add_settings_field(
		'enable_pro_feature',
		__( 'PRO Feature', 'frontblocks-pro' ),
		'frblp_field_pro_feature',
		'frontblocks-settings',
		'frontblocks_section_features' // Sección existente
	);
}

function frblp_field_pro_feature() {
	$options = get_option( 'frontblocks_settings', array() );
	$enabled = (bool) ( $options['enable_pro_feature'] ?? false );
	?>
	<div class="tw-flex tw-items-center tw-justify-between">
		<div class="tw-flex-grow">
			<label for="enable_pro_feature" class="tw-text-sm tw-font-medium tw-text-gray-900 tw-cursor-pointer">
				<?php echo esc_html__( 'Enable PRO Feature', 'frontblocks-pro' ); ?>
			</label>
			<p class="tw-mt-1 tw-text-sm tw-text-gray-500">
				<?php echo esc_html__( 'Description of your PRO feature here.', 'frontblocks-pro' ); ?>
			</p>
		</div>
		<label class="frbl-toggle">
			<input type="checkbox" 
				id="enable_pro_feature" 
				name="frontblocks_settings[enable_pro_feature]" 
				value="1" 
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>
	</div>
	<?php
}
```

---

## 🎨 Estructura HTML del Toggle

Para que el toggle se vea correcto, usa esta estructura:

```html
<div class="tw-flex tw-items-center tw-justify-between">
	<div class="tw-flex-grow">
		<label for="your_field_id" class="tw-text-sm tw-font-medium tw-text-gray-900 tw-cursor-pointer">
			Tu Título
		</label>
		<p class="tw-mt-1 tw-text-sm tw-text-gray-500">
			Tu descripción aquí
		</p>
	</div>
	<label class="frbl-toggle">
		<input type="checkbox" 
			id="your_field_id" 
			name="frontblocks_settings[your_field_id]" 
			value="1" 
			<?php checked( true, $enabled ); ?>
		/>
		<span></span>
	</label>
</div>
```

---

## 📦 Componentes Disponibles

### 1. Toggle Switch

```html
<label class="frbl-toggle">
	<input type="checkbox" name="..." value="1" <?php checked(...); ?> />
	<span></span>
</label>
```

**Características:**
- Color primario: #687DF9
- Animación suave: 200ms
- Estados: OFF (gris) / ON (púrpura-azul)
- Círculo siempre blanco

### 2. Checkbox (si lo prefieres)

```html
<input type="checkbox" class="frbl-checkbox" name="..." value="1" />
```

### 3. Clases de Tailwind Disponibles

Todas las clases de Tailwind están disponibles con el prefijo `tw-`:

- **Layout**: `tw-flex`, `tw-items-center`, `tw-justify-between`
- **Spacing**: `tw-mt-1`, `tw-mb-2`, `tw-px-4`
- **Typography**: `tw-text-sm`, `tw-font-medium`, `tw-text-gray-900`
- **Colors**: `tw-text-gray-500`, `tw-bg-primary-500`

---

## 🔧 Sanitización de Datos

No olvides sanitizar tus settings. FrontBlocks ya tiene una función de sanitización, pero puedes extenderla:

### Método 1: Usar el Filtro de Sanitización

```php
add_filter( 'sanitize_option_frontblocks_settings', 'frblp_sanitize_pro_settings', 10, 2 );

function frblp_sanitize_pro_settings( $value, $option ) {
	// Sanitizar tus campos PRO
	if ( isset( $value['enable_advanced_animations'] ) ) {
		$value['enable_advanced_animations'] = (bool) $value['enable_advanced_animations'];
	}
	
	if ( isset( $value['enable_pro_feature'] ) ) {
		$value['enable_pro_feature'] = (bool) $value['enable_pro_feature'];
	}
	
	return $value;
}
```

### Método 2: Registrar tu Propio Setting (Separado)

```php
register_setting(
	'frontblocks_settings', // Grupo
	'frontblocks_pro_settings', // Nombre diferente
	array(
		'type'              => 'array',
		'sanitize_callback' => 'frblp_sanitize_settings',
		'default'           => array(),
	)
);

function frblp_sanitize_settings( $value ) {
	// Tu lógica de sanitización
	return $value;
}
```

---

## 📖 Ejemplo Completo: FrontBlocks PRO

```php
<?php
/**
 * FrontBlocks PRO - Settings Integration
 * 
 * File: frontblocks-pro/includes/Admin/Settings.php
 */

namespace FrontBlocksPro\Admin;

class Settings {
	
	public function __construct() {
		add_action( 'frontblocks_register_settings', array( $this, 'register_settings' ) );
		add_filter( 'sanitize_option_frontblocks_settings', array( $this, 'sanitize_settings' ), 10, 2 );
	}
	
	/**
	 * Register PRO settings
	 */
	public function register_settings() {
		// Add PRO section
		add_settings_section(
			'frontblocks_pro_section',
			__( 'PRO Features', 'frontblocks-pro' ),
			array( $this, 'section_callback' ),
			'frontblocks-settings'
		);
		
		// Add fields
		add_settings_field(
			'enable_advanced_gallery',
			__( 'Advanced Gallery', 'frontblocks-pro' ),
			array( $this, 'field_advanced_gallery' ),
			'frontblocks-settings',
			'frontblocks_pro_section'
		);
		
		add_settings_field(
			'enable_woocommerce_features',
			__( 'WooCommerce Features', 'frontblocks-pro' ),
			array( $this, 'field_woocommerce_features' ),
			'frontblocks-settings',
			'frontblocks_pro_section'
		);
	}
	
	/**
	 * Section description
	 */
	public function section_callback() {
		echo '<p>' . esc_html__( 'Enable advanced features available in FrontBlocks PRO.', 'frontblocks-pro' ) . '</p>';
	}
	
	/**
	 * Advanced Gallery toggle
	 */
	public function field_advanced_gallery() {
		$this->render_toggle_field(
			'enable_advanced_gallery',
			__( 'Advanced Gallery', 'frontblocks-pro' ),
			__( 'Enable advanced gallery features with lightbox, filtering, and masonry layouts.', 'frontblocks-pro' )
		);
	}
	
	/**
	 * WooCommerce Features toggle
	 */
	public function field_woocommerce_features() {
		$this->render_toggle_field(
			'enable_woocommerce_features',
			__( 'WooCommerce Integration', 'frontblocks-pro' ),
			__( 'Enable advanced WooCommerce blocks and customizations.', 'frontblocks-pro' )
		);
	}
	
	/**
	 * Helper: Render toggle field
	 */
	private function render_toggle_field( $field_id, $label, $description ) {
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = (bool) ( $options[ $field_id ] ?? false );
		?>
		<div class="tw-flex tw-items-center tw-justify-between">
			<div class="tw-flex-grow">
				<label for="<?php echo esc_attr( $field_id ); ?>" class="tw-text-sm tw-font-medium tw-text-gray-900 tw-cursor-pointer">
					<?php echo esc_html( $label ); ?>
				</label>
				<p class="tw-mt-1 tw-text-sm tw-text-gray-500">
					<?php echo esc_html( $description ); ?>
				</p>
			</div>
			<label class="frbl-toggle">
				<input type="checkbox" 
					id="<?php echo esc_attr( $field_id ); ?>" 
					name="frontblocks_settings[<?php echo esc_attr( $field_id ); ?>]" 
					value="1" 
					<?php checked( true, $enabled ); ?>
				/>
				<span></span>
			</label>
		</div>
		<?php
	}
	
	/**
	 * Sanitize PRO settings
	 */
	public function sanitize_settings( $value, $option ) {
		if ( isset( $value['enable_advanced_gallery'] ) ) {
			$value['enable_advanced_gallery'] = (bool) $value['enable_advanced_gallery'];
		}
		
		if ( isset( $value['enable_woocommerce_features'] ) ) {
			$value['enable_woocommerce_features'] = (bool) $value['enable_woocommerce_features'];
		}
		
		return $value;
	}
}

// Initialize
new Settings();
```

---

## 🎯 Cómo Leer los Settings en tu Código

```php
<?php
// Obtener todos los settings
$settings = get_option( 'frontblocks_settings', array() );

// Verificar si una feature está habilitada
if ( isset( $settings['enable_advanced_animations'] ) && $settings['enable_advanced_animations'] ) {
	// Cargar funcionalidad PRO
}

// Helper function
function frblp_is_feature_enabled( $feature ) {
	$settings = get_option( 'frontblocks_settings', array() );
	return isset( $settings[ $feature ] ) && $settings[ $feature ];
}

// Uso
if ( frblp_is_feature_enabled( 'enable_advanced_gallery' ) ) {
	// ...
}
```

---

## 🔍 Debugging

Para ver todos los settings guardados:

```php
<?php
$settings = get_option( 'frontblocks_settings' );
var_dump( $settings );
```

---

## ✅ Checklist de Integración

- [ ] Hook añadido: `add_action( 'frontblocks_register_settings', ... )`
- [ ] Sección creada con `add_settings_section()`
- [ ] Campos añadidos con `add_settings_field()`
- [ ] Usar estructura HTML correcta para toggles
- [ ] Usar prefijo `tw-` en clases de Tailwind
- [ ] Sanitización implementada
- [ ] Settings guardados en `frontblocks_settings`
- [ ] Verificado que los toggles se vean correctamente

---

## 📞 Soporte

Si tienes problemas integrando settings desde FrontBlocks PRO, revisa:

1. **Hook correcto**: `frontblocks_register_settings`
2. **Page slug correcto**: `frontblocks-settings`
3. **Clases CSS**: Usar prefijo `tw-`
4. **Settings guardados**: En opción `frontblocks_settings`

---

**Ejemplo funcional incluido en**: `EXTENDING-SETTINGS.md`  
**Fecha**: 30 de Octubre, 2025  
**Versión FrontBlocks**: 1.1.0

