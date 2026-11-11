# Settings Export and Import

Copy the FrontBlocks configuration from one site to another with a JSON file. Open **Appearance > FrontBlocks > Backup**.

## Using it

1. On the site you want to copy, open the **Backup** tab and click **Download settings file**.
2. On the other site, open the **Backup** tab, choose the file and click **Import settings**.
3. A summary lists how many settings were applied, which ones were skipped and why, and which ones were adjusted to fit the site.

Only users with the `edit_theme_options` capability (the same as the settings screen) can export or import, and both requests are protected with nonces.

## What is exported

The `frontblocks_settings` option, limited to the settings that make sense on any site:

| Group | Keys |
| --- | --- |
| Module toggles | Every checkbox of the settings screen (blocks, optional features, WooCommerce, PRO toggles such as `enable_popups` or `enable_language_banner`). A toggle that was never saved is exported as `false`. |
| Options | `events_type`, `scroll_top_position`, `scroll_top_icon_url`, `maintenance_title` |
| Image Management | `image_format_target`, `image_max_upload_dimension_enabled`, `image_max_upload_dimension`, `image_format_quality_webp`, `image_format_quality_avif` |

## What is not exported

These belong to a single site or are secret, so they are never written to the file. Set them up again on each site.

- Google Sign-In credentials (client ID and secret) and any license key.
- The maintenance image: it is a media library attachment ID.
- The image size configuration (disabled, overridden and custom sizes) and the core size options it writes, because they depend on the sizes each theme registers.
- Activation dates, dismissed notices and other per-site state.

The file contains no credentials, so it is safe to keep in a repository.

## File format

```json
{
  "format": "frontblocks-settings",
  "format_version": 1,
  "plugin_version": "1.6.0",
  "exported_at": "2026-11-11T10:00:00Z",
  "source_site": "example.com",
  "settings": {
    "enable_back_button": true,
    "enable_popups": false,
    "scroll_top_position": "bottom-left"
  }
}
```

`format_version` lets a future version refuse or migrate a file it does not understand. A file with a newer version than the site supports is rejected with a message asking to update FrontBlocks.

## How the import works

- **It merges.** Only the keys present in the file change. Everything else on the destination site stays as it is, including the values that are never exported.
- **Every value is validated** against a schema (boolean, one of a list, http or https URL, text up to a length, whole number between two bounds). A value that does not match is skipped and listed, never stored.
- **Unknown keys are skipped** and listed, so a file cannot write arbitrary data into the option.
- **Values that depend on the server are adjusted.** An image format target the server cannot produce, for example AVIF without support, is downgraded the same way the settings screen does, and the summary says so.
- **The two short description options exclude each other**, as they do on the settings screen. If an import would turn both on, one is turned off and the summary says which.
- **Limits.** The file must be valid JSON, declare `"format": "frontblocks-settings"`, be at most 256 KB and have the `.json` extension. The file is only decoded, never executed or included. A rejected file changes nothing.

## Extending it

Add-ons such as FrontBlocks PRO can contribute their own settings with two filters. Declare how a key is validated, then add its value to the export.

```php
add_filter(
	'frontblocks_import_settings_schema',
	function ( $schema ) {
		$schema['my_addon_mode'] = array(
			'type'   => 'enum',
			'values' => array( 'simple', 'advanced' ),
		);
		return $schema;
	}
);

add_filter(
	'frontblocks_export_settings',
	function ( $settings, $options ) {
		if ( isset( $options['my_addon_mode'] ) ) {
			$settings['my_addon_mode'] = $options['my_addon_mode'];
		}
		return $settings;
	},
	10,
	2
);
```

Supported rule types:

| `type` | Extra keys | Accepts |
| --- | --- | --- |
| `bool` | | `true`, `false`, `1`, `0`, `"1"`, `"0"` |
| `enum` | `values` | one of the listed strings |
| `url` | | an empty string or an `http` or `https` URL |
| `text` | `max` (default 255) | text up to `max` characters, sanitized with `sanitize_text_field()` |
| `int` | `min`, `max` | a whole number between the bounds |

Keys that are not in the schema are skipped on import, so an add-on must declare a key before it can travel. Never add credentials or values that only make sense on the source site to the export.

## Code

- `includes/Admin/SettingsTransfer.php`: schema, file validation, import and the Backup tab.
- `Settings::get_boolean_option_keys()` is the single list of toggles, shared with the settings form sanitizer.
- The tab is added through the `frontblocks_settings_tabs` filter and the `frontblocks_settings_tab_panels` action (see [Extending Settings Tabs](./SETTINGS-TAB-EXTENSIONS.md)).
- While an import writes the option, `SettingsTransfer::is_importing()` is true. The settings form sanitizer checks it, because it normally requires the form nonce, which an import does not have.
