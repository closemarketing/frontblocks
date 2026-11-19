# Extending FrontBlocks Settings Tabs

FrontBlocks exposes two hooks for companion plugins that need a dedicated tab on the **FrontBlocks** settings screen. Every tab also gets an entry in the FrontBlocks menu of the admin sidebar.

## Add a tab and its panel

Use the `frontblocks_settings_tabs` filter to append a tab definition. Each tab requires a unique `id` and a user-facing `label`.

Add the filter before the `admin_menu` action runs: at the top level of your plugin file as in the example below, or on `plugins_loaded` or `init`. WordPress builds the admin menu before `admin_init`, so a tab added on `admin_init` or later gets no menu entry and no page of its own.

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

## The FrontBlocks menu

FrontBlocks has a menu of its own in the admin sidebar, right below Appearance, with a submenu entry for each tab. Each entry is a page of its own, so a tab has a URL that can be linked, bookmarked and opened from the sidebar:

| Tab id | Page slug | URL |
| --- | --- | --- |
| `blocks` (the first tab) | `frontblocks-settings` | `admin.php?page=frontblocks-settings` |
| any other, for example `my-plugin` | `frontblocks-settings-my-plugin` | `admin.php?page=frontblocks-settings-my-plugin` |

- Your tab appears in the submenu automatically. Add `'menu' => false` to the tab definition to keep it out of the submenu. It is still a tab of the screen: it has no page of its own, so clicking it keeps the current page and adds its `#tab` to the URL.
- The first entry, `admin.php?page=frontblocks-settings`, is always in the submenu, even if a filter removes the `blocks` tab or hides it.
- Link to a tab with `\FrontBlocks\Admin\Settings::get_settings_url( 'my-plugin' )`, not with a hard-coded URL.
- Recognize the settings screens with `\FrontBlocks\Admin\Settings::is_settings_screen( $hook_or_screen_id )`. The screen ID of a tab is not the same on every tab, and it was different when the screen lived under Appearance. Do not compare it with a fixed string such as `appearance_page_frontblocks-settings`.
- Check that the method exists before you call it if your plugin must work with older versions of FrontBlocks, which had the page under Appearance (`themes.php?page=frontblocks-settings`).
- The old Appearance URL still works. It redirects to the new menu and keeps its query arguments and its `#tab`.
- A post type of your plugin can live in the same menu: register it with `'show_in_menu' => \FrontBlocks\Admin\Settings::PAGE_SLUG`. Its entry goes after the settings tabs. WordPress does not add the taxonomy entries of a post type shown in another menu, so add them with `add_submenu_page()` (FrontBlocks does this for the event categories). With older versions of FrontBlocks, keep `'show_in_menu' => true`.

The panel action runs after FrontBlocks closes its settings form. If your tab submits data, provide its own form, nonce, capability checks, and sanitization.
