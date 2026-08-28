# Redundant Plugins Notice

A persistent admin notice that detects when a third-party plugin duplicates a feature FrontBlocks already provides natively, and suggests deactivating it.

## How it works

- `FrontBlocks\Admin\RedundantPlugins` (`includes/Admin/RedundantPlugins.php`) hooks `admin_notices` and shows one notice per match, to users who can `manage_options`, on the Dashboard, the Plugins screen, and any FrontBlocks admin screen.
- It knows nothing about specific plugins itself. It reads a list of **entries** — each one pairing a FrontBlocks feature with the third-party plugin(s) that duplicate it — from the `frontblocks_redundant_plugins` filter, and checks `is_plugin_active()` for each candidate plugin.
- "Persistent" means dismissal is not permanent: it's scoped to the exact set of active plugins + versions detected for that entry (a hash of `basename@version` pairs). If the site owner dismisses the notice, deactivates the plugin, and later reactivates it (or updates it to a new version), the state hash changes and the notice reappears. Dismissal is stored per user in the `frbl_redundant_plugins_dismissed` user meta key (an array keyed by entry id), through a nonce-protected AJAX request (`frbl_dismiss_redundant_plugin_notice`).

## Adding a new entry

Any feature — in FrontBlocks core, FrontBlocks PRO, or a third-party integration — can register itself without touching `RedundantPlugins.php`, by hooking the `frontblocks_redundant_plugins` filter:

```php
add_filter( 'frontblocks_redundant_plugins', function ( $entries ) {
	$entries['image-optimization'] = array(
		// Human-readable name of the FrontBlocks feature.
		'feature' => __( 'Image Compression', 'frontblocks' ),
		// Whether the feature is actually active/enabled on this site right now
		// (some FrontBlocks features are opt-in via frontblocks_settings).
		'enabled' => true,
		// Plugin basename (as used by is_plugin_active()) => human-readable name.
		'plugins' => array(
			'imagify/imagify.php'                   => 'Imagify',
			'wp-smushit/wp-smush.php'                => 'Smush',
			'shortpixel-image-optimiser/wp-shortpixel.php' => 'ShortPixel Image Optimizer',
		),
		// Optional: link shown on the notice for more information.
		'doc_url' => admin_url( 'themes.php?page=frontblocks-settings' ),
	);

	return $entries;
} );
```

Notes:

- The array key (`image-optimization` above) is the entry id — keep it unique and stable, since it's also the key used to store per-entry dismissals.
- `plugins` accepts more than one basename per entry when several third-party plugins cover the same functionality (as in the example above).
- `enabled` should reflect the real on/off state of the FrontBlocks feature (e.g. read from `get_option( 'frontblocks_settings', array() )` for opt-in features), so the notice doesn't fire for a feature the site owner hasn't turned on.
- See `RedundantPlugins::get_default_entries()` for the two entries FrontBlocks core ships: SVG Upload (vs. *Safe SVG* / *SVG Support*) and Cookie Notice (vs. *GDPR Cookie Compliance*).

## Debugging

There is no dedicated debug flag for this notice. To test a new entry, activate one of its candidate plugins, then load `wp-admin/index.php` (Dashboard) or `wp-admin/plugins.php` as a user with `manage_options`.
