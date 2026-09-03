# Extending FrontBlocks Settings Tabs

FrontBlocks exposes two hooks for companion plugins that need a dedicated tab on the **Appearance → FrontBlocks** settings screen.

## Add a tab and its panel

Use the `frontblocks_settings_tabs` filter to append a tab definition. Each tab requires a unique `id` and a user-facing `label`.

Use the `frontblocks_settings_tab_panels` action to output its panel. The panel must use the same ID in its `data-tab-panel` attribute and include the `frbl-tab-panel` class. Add the `hidden` attribute so the standard tab script controls its initial visibility.

```php
add_filter( 'frontblocks_settings_tabs', 'my_plugin_add_settings_tab' );
add_action( 'frontblocks_settings_tab_panels', 'my_plugin_render_settings_tab' );

function my_plugin_add_settings_tab( $tabs ) {
	$tabs[] = array(
		'id'    => 'my-plugin',
		'label' => __( 'My Plugin', 'my-plugin' ),
	);

	return $tabs;
}

function my_plugin_render_settings_tab() {
	?>
	<div class="frbl-tab-panel" data-tab-panel="my-plugin" hidden>
		<h2><?php esc_html_e( 'My Plugin', 'my-plugin' ); ?></h2>
		<p><?php esc_html_e( 'Settings and tools for My Plugin.', 'my-plugin' ); ?></p>
	</div>
	<?php
}
```

The panel action runs after FrontBlocks closes its settings form. If your tab submits data, provide its own form, nonce, capability checks, and sanitization.
