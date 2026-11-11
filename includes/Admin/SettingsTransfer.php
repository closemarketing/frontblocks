<?php
/**
 * Settings Export / Import
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Exports the FrontBlocks settings to a JSON file and imports them on another site.
 *
 * Adds a "Backup" tab to the FrontBlocks settings screen. The export contains
 * only settings that make sense on any site; credentials, license keys and
 * values tied to the source site (attachment IDs, registered image sizes) are
 * never written. The import merges: only the keys present in the file change,
 * and every value is validated against a schema before it is stored.
 *
 * Add-ons can also move content, such as the popups of FrontBlocks PRO, in the
 * optional `content` section of the file through the `frontblocks_export_content`
 * and `frontblocks_import_content` filters.
 *
 * See docs/SETTINGS-EXPORT-IMPORT.md for the file format and the extension hooks.
 *
 * @since 1.6.0
 */
class SettingsTransfer {

	/**
	 * Value of the `format` key every export declares.
	 *
	 * @var string
	 */
	const FORMAT = 'frontblocks-settings';

	/**
	 * Version of the file format. Bump it when the structure changes.
	 *
	 * @var int
	 */
	const FORMAT_VERSION = 1;

	/**
	 * Largest file accepted on import, in bytes.
	 *
	 * @var int
	 */
	const MAX_FILE_SIZE = 2097152;

	/**
	 * Option that stores the FrontBlocks settings.
	 *
	 * @var string
	 */
	const OPTION = 'frontblocks_settings';

	/**
	 * FrontBlocks settings page slug (registered in Admin\Settings).
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'frontblocks-settings';

	/**
	 * Id of the settings tab.
	 *
	 * @var string
	 */
	const TAB_ID = 'backup';

	/**
	 * Capability required to export and import (same as the settings screen).
	 *
	 * @var string
	 */
	const CAPABILITY = 'edit_theme_options';

	/**
	 * Action (and nonce action) of the export request.
	 *
	 * @var string
	 */
	const EXPORT_ACTION = 'frbl_export_settings';

	/**
	 * Action (and nonce action) of the import request.
	 *
	 * @var string
	 */
	const IMPORT_ACTION = 'frbl_import_settings';

	/**
	 * Name of the file input of the import form.
	 *
	 * @var string
	 */
	const FILE_FIELD = 'frbl_settings_file';

	/**
	 * Whether an import is writing the option right now.
	 *
	 * @var bool
	 */
	private static $importing = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'frontblocks_settings_tabs', array( $this, 'add_tab' ) );
		add_action( 'frontblocks_settings_tab_panels', array( $this, 'render_panel' ) );
		add_action( 'admin_post_' . self::EXPORT_ACTION, array( $this, 'handle_export' ) );
		add_action( 'admin_post_' . self::IMPORT_ACTION, array( $this, 'handle_import' ) );
	}

	/**
	 * Whether an import is storing the option right now.
	 *
	 * The settings sanitizers read the submitted form and check its nonce, which
	 * an import has neither of. While this is true they hand the value back
	 * untouched, because the import validated every value before saving.
	 *
	 * @return bool
	 */
	public static function is_importing() {
		return self::$importing;
	}

	/**
	 * Schema of the settings that can travel between sites.
	 *
	 * Each key maps to a rule with a `type` (bool, enum, url, text or int) and the
	 * limits that type needs. Anything not listed here is neither exported nor
	 * imported.
	 *
	 * @return array
	 */
	public static function get_schema() {
		$schema = array();

		foreach ( Settings::get_boolean_option_keys() as $key ) {
			$schema[ $key ] = array( 'type' => 'bool' );
		}

		$schema['events_type']                        = array(
			'type'   => 'enum',
			'values' => array( 'cpt', 'posts' ),
		);
		$schema['scroll_top_position']                = array(
			'type'   => 'enum',
			'values' => array( 'bottom-right', 'bottom-left' ),
		);
		$schema['scroll_top_icon_url']                = array( 'type' => 'url' );
		$schema['maintenance_title']                  = array(
			'type' => 'text',
			'max'  => 200,
		);
		$schema['image_format_target']                = array(
			'type'   => 'enum',
			'values' => array( 'none', 'webp', 'avif', 'both' ),
		);
		$schema['image_max_upload_dimension_enabled'] = array( 'type' => 'bool' );
		$schema['image_max_upload_dimension']         = array(
			'type' => 'int',
			'min'  => 300,
			'max'  => 10000,
		);
		$schema['image_format_quality_webp']          = array(
			'type' => 'int',
			'min'  => 1,
			'max'  => 100,
		);
		$schema['image_format_quality_avif']          = array(
			'type' => 'int',
			'min'  => 1,
			'max'  => 100,
		);

		/**
		 * Filters the schema of the settings that can be exported and imported.
		 *
		 * Add-ons such as FrontBlocks PRO add their own keys here. Each rule needs a
		 * `type` of bool, enum (with `values`), url, text (with an optional `max`)
		 * or int (with optional `min` and `max`).
		 *
		 * @since 1.6.0
		 * @param array $schema Setting key => rule.
		 */
		$schema = apply_filters( 'frontblocks_import_settings_schema', $schema );

		return is_array( $schema ) ? $schema : array();
	}

	/**
	 * Validate and normalize one value against a schema rule.
	 *
	 * @param mixed $value Raw value.
	 * @param array $rule  Schema rule.
	 * @return array {
	 *     @type bool   $ok     Whether the value is valid.
	 *     @type mixed  $value  The normalized value, when valid.
	 *     @type string $reason Why the value was rejected, when invalid.
	 * }
	 */
	public static function sanitize_value( $value, $rule ) {
		$type = isset( $rule['type'] ) ? $rule['type'] : '';

		switch ( $type ) {
			case 'bool':
				if ( is_bool( $value ) ) {
					return self::valid( $value );
				}
				if ( in_array( $value, array( 0, 1, '0', '1' ), true ) ) {
					return self::valid( (bool) (int) $value );
				}
				return self::invalid( __( 'expected true or false', 'frontblocks' ) );

			case 'enum':
				$values = isset( $rule['values'] ) ? (array) $rule['values'] : array();
				if ( is_string( $value ) && in_array( $value, $values, true ) ) {
					return self::valid( $value );
				}
				/* translators: %s: comma separated list of allowed values */
				return self::invalid( sprintf( __( 'expected one of: %s', 'frontblocks' ), implode( ', ', $values ) ) );

			case 'url':
				if ( ! is_string( $value ) ) {
					return self::invalid( __( 'expected a URL', 'frontblocks' ) );
				}
				if ( '' === trim( $value ) ) {
					return self::valid( '' );
				}
				$url = esc_url_raw( trim( $value ), array( 'http', 'https' ) );
				if ( '' === $url ) {
					return self::invalid( __( 'expected an http or https URL', 'frontblocks' ) );
				}
				return self::valid( $url );

			case 'text':
				if ( ! is_string( $value ) ) {
					return self::invalid( __( 'expected text', 'frontblocks' ) );
				}
				$max = isset( $rule['max'] ) ? (int) $rule['max'] : 255;
				if ( strlen( $value ) > $max ) {
					/* translators: %d: maximum number of characters */
					return self::invalid( sprintf( __( 'longer than %d characters', 'frontblocks' ), $max ) );
				}
				return self::valid( sanitize_text_field( $value ) );

			case 'int':
				if ( is_string( $value ) && preg_match( '/^-?\d+$/', $value ) ) {
					$value = (int) $value;
				}
				$min = isset( $rule['min'] ) ? (int) $rule['min'] : PHP_INT_MIN;
				$max = isset( $rule['max'] ) ? (int) $rule['max'] : PHP_INT_MAX;
				if ( ! is_int( $value ) || $value < $min || $value > $max ) {
					/* translators: 1: minimum value, 2: maximum value */
					return self::invalid( sprintf( __( 'expected a whole number between %1$d and %2$d', 'frontblocks' ), $min, $max ) );
				}
				return self::valid( $value );
		}

		return self::invalid( __( 'unsupported setting type', 'frontblocks' ) );
	}

	/**
	 * Result of a value that passed validation.
	 *
	 * @param mixed $value Normalized value.
	 * @return array
	 */
	private static function valid( $value ) {
		return array(
			'ok'    => true,
			'value' => $value,
		);
	}

	/**
	 * Result of a value that failed validation.
	 *
	 * @param string $reason Why it was rejected.
	 * @return array
	 */
	private static function invalid( $reason ) {
		return array(
			'ok'     => false,
			'reason' => $reason,
		);
	}

	/**
	 * Build the document that an export downloads.
	 *
	 * @return array
	 */
	public static function build_export_payload() {
		$options = get_option( self::OPTION, array() );
		$options = is_array( $options ) ? $options : array();
		$schema  = self::get_schema();
		$boolean = Settings::get_boolean_option_keys();

		$settings = array();
		foreach ( $schema as $key => $rule ) {
			if ( in_array( $key, $boolean, true ) ) {
				// An unchecked toggle is never saved, so export its effective state.
				$settings[ $key ] = ! empty( $options[ $key ] );
				continue;
			}

			if ( ! array_key_exists( $key, $options ) ) {
				continue;
			}

			$checked = self::sanitize_value( $options[ $key ], $rule );
			if ( $checked['ok'] ) {
				$settings[ $key ] = $checked['value'];
			}
		}

		/**
		 * Filters the settings written to an export file.
		 *
		 * Add-ons can add the keys they declared on `frontblocks_import_settings_schema`.
		 * Never add credentials or values that only make sense on this site.
		 *
		 * @since 1.6.0
		 * @param array $settings Setting key => value.
		 * @param array $options  The stored `frontblocks_settings` option.
		 */
		$settings = apply_filters( 'frontblocks_export_settings', $settings, $options );

		$payload = array(
			'format'         => self::FORMAT,
			'format_version' => self::FORMAT_VERSION,
			'plugin_version' => defined( 'FRBL_VERSION' ) ? FRBL_VERSION : '',
			'exported_at'    => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'source_site'    => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
			'settings'       => is_array( $settings ) ? $settings : array(),
		);

		$content = self::collect_content();
		if ( ! empty( $content ) ) {
			$payload['content'] = $content;
		}

		return $payload;
	}

	/**
	 * Whether a content section id is valid.
	 *
	 * @param mixed $id Section id.
	 * @return bool
	 */
	private static function is_valid_section_id( $id ) {
		return is_string( $id ) && 1 === preg_match( '/^[a-z0-9_-]{1,40}$/', $id );
	}

	/**
	 * Ask the add-ons for the content that goes in the export.
	 *
	 * @return array Section id => data.
	 */
	private static function collect_content() {
		/**
		 * Filters the content written to the `content` section of an export file.
		 *
		 * Add-ons return their data under their own section id (lowercase letters,
		 * numbers, hyphens and underscores). Use it for things that are not settings,
		 * such as the popups of FrontBlocks PRO. Never add credentials, and replace
		 * IDs that only mean something on this site (pages, media) with something
		 * that can be found again on another site, such as a path.
		 *
		 * @since 1.6.0
		 * @param array $content Section id => data to export.
		 */
		$content = apply_filters( 'frontblocks_export_content', array() );
		if ( ! is_array( $content ) ) {
			return array();
		}

		$clean = array();
		foreach ( $content as $id => $data ) {
			if ( self::is_valid_section_id( $id ) && is_array( $data ) && array() !== $data ) {
				$clean[ $id ] = $data;
			}
		}

		return $clean;
	}

	/**
	 * Name of the downloaded file.
	 *
	 * @return string
	 */
	public static function get_export_filename() {
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

		return sanitize_file_name( sprintf( 'frontblocks-settings-%s-%s.json', '' !== $host ? $host : 'site', gmdate( 'Y-m-d' ) ) );
	}

	/**
	 * Decode and check the contents of an import file.
	 *
	 * @param string $raw File contents.
	 * @return array|\WP_Error The decoded document, or an error describing what is wrong with it.
	 */
	public static function parse_file_contents( $raw ) {
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return new \WP_Error( 'frbl_transfer_empty', __( 'The file is empty.', 'frontblocks' ) );
		}

		if ( strlen( $raw ) > self::MAX_FILE_SIZE ) {
			return new \WP_Error( 'frbl_transfer_too_large', __( 'The file is too large to be a FrontBlocks settings export.', 'frontblocks' ) );
		}

		// Editors on Windows can add a byte order mark that JSON does not allow.
		if ( 0 === strpos( $raw, "\xEF\xBB\xBF" ) ) {
			$raw = substr( $raw, 3 );
		}

		$data = json_decode( $raw, true, 16 );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
			return new \WP_Error( 'frbl_transfer_invalid_json', __( 'The file is not valid JSON.', 'frontblocks' ) );
		}

		if ( ! isset( $data['format'] ) || self::FORMAT !== $data['format'] ) {
			return new \WP_Error( 'frbl_transfer_invalid_format', __( 'The file is not a FrontBlocks settings export.', 'frontblocks' ) );
		}

		$version = isset( $data['format_version'] ) ? $data['format_version'] : null;
		if ( ! is_int( $version ) || $version < 1 ) {
			return new \WP_Error( 'frbl_transfer_invalid_version', __( 'The file does not declare a valid format version.', 'frontblocks' ) );
		}

		if ( $version > self::FORMAT_VERSION ) {
			return new \WP_Error( 'frbl_transfer_newer_version', __( 'The file was exported by a newer version of FrontBlocks. Update FrontBlocks on this site and try again.', 'frontblocks' ) );
		}

		if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) || array() === $data['settings'] ) {
			return new \WP_Error( 'frbl_transfer_no_settings', __( 'The file does not contain any settings.', 'frontblocks' ) );
		}

		if ( isset( $data['content'] ) && ! is_array( $data['content'] ) ) {
			return new \WP_Error( 'frbl_transfer_invalid_content', __( 'The content section of the file is not valid.', 'frontblocks' ) );
		}

		return $data;
	}

	/**
	 * Check every incoming setting against the schema.
	 *
	 * @param array $incoming Setting key => raw value from the file.
	 * @return array {
	 *     @type array $valid    Setting key => normalized value, ready to store.
	 *     @type array $skipped  Setting key => reason it was not imported.
	 *     @type array $adjusted Setting key => note about a value that was changed to fit this site.
	 * }
	 */
	public static function validate_settings( $incoming ) {
		$schema   = self::get_schema();
		$valid    = array();
		$skipped  = array();
		$adjusted = array();

		foreach ( $incoming as $key => $value ) {
			$key = is_string( $key ) ? $key : (string) $key;

			if ( ! isset( $schema[ $key ] ) ) {
				$skipped[ $key ] = __( 'unknown setting', 'frontblocks' );
				continue;
			}

			$checked = self::sanitize_value( $value, $schema[ $key ] );
			if ( ! $checked['ok'] ) {
				$skipped[ $key ] = $checked['reason'];
				continue;
			}

			$valid[ $key ] = $checked['value'];
		}

		// An image format the server cannot produce would break image delivery.
		if ( isset( $valid['image_format_target'] ) && class_exists( __NAMESPACE__ . '\\ImageManagement' ) ) {
			$supported = ImageManagement::downgrade_target_to_supported_formats( $valid['image_format_target'] );
			if ( $supported !== $valid['image_format_target'] ) {
				/* translators: 1: requested format, 2: format applied instead */
				$adjusted['image_format_target'] = sprintf( __( 'this server cannot produce "%1$s", so "%2$s" was applied', 'frontblocks' ), $valid['image_format_target'], $supported );
				$valid['image_format_target']    = $supported;
			}
		}

		return array(
			'valid'    => $valid,
			'skipped'  => $skipped,
			'adjusted' => $adjusted,
		);
	}

	/**
	 * Merge validated settings into the stored option.
	 *
	 * @param array $valid    Setting key => validated value.
	 * @param array $adjusted Notes about values changed to fit this site.
	 * @return array {
	 *     @type string[] $applied  Keys that were written.
	 *     @type array    $adjusted Setting key => note.
	 * }
	 */
	public static function apply_settings( $valid, $adjusted = array() ) {
		$current = get_option( self::OPTION, array() );
		$current = is_array( $current ) ? $current : array();
		$merged  = array_merge( $current, $valid );

		// The two short description options exclude each other, as on the settings screen.
		$deactivate = 'deactivate_short_description';
		$move       = 'move_content_to_short_description';
		if ( ! empty( $merged[ $deactivate ] ) && ! empty( $merged[ $move ] ) ) {
			$turn_off = isset( $valid[ $deactivate ] ) && isset( $valid[ $move ] ) ? $move : ( isset( $valid[ $move ] ) ? $deactivate : $move );
			$keep     = $turn_off === $move ? $deactivate : $move;

			$merged[ $turn_off ] = false;
			/* translators: 1: setting turned off, 2: setting kept on */
			$adjusted[ $turn_off ] = sprintf( __( 'turned off, it cannot be on together with "%s"', 'frontblocks' ), $keep );
		}

		self::$importing = true;
		try {
			update_option( self::OPTION, $merged );
		} finally {
			self::$importing = false;
		}

		return array(
			'applied'  => array_keys( $valid ),
			'adjusted' => $adjusted,
		);
	}

	/**
	 * Import the contents of a settings file.
	 *
	 * @param string $raw File contents.
	 * @return array|\WP_Error Summary of what was applied and skipped, or the reason the file was rejected.
	 */
	public static function import_from_string( $raw ) {
		$data = self::parse_file_contents( $raw );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$checked = self::validate_settings( $data['settings'] );
		$applied = self::apply_settings( $checked['valid'], $checked['adjusted'] );

		// Settings go first: an add-on may depend on a toggle that was just imported.
		$content = self::import_content( isset( $data['content'] ) ? $data['content'] : array() );

		return array(
			'applied'        => $applied['applied'],
			'skipped'        => $checked['skipped'],
			'adjusted'       => $applied['adjusted'],
			'content'        => $content,
			'source_site'    => isset( $data['source_site'] ) && is_string( $data['source_site'] ) ? sanitize_text_field( $data['source_site'] ) : '',
			'source_version' => isset( $data['plugin_version'] ) && is_string( $data['plugin_version'] ) ? sanitize_text_field( $data['plugin_version'] ) : '',
		);
	}

	/**
	 * Hand the content sections of a file to the add-ons that know them.
	 *
	 * @param array $content Section id => data from the file.
	 * @return array Section id => report (label, created, updated, skipped and notes).
	 */
	public static function import_content( $content ) {
		if ( ! is_array( $content ) || array() === $content ) {
			return array();
		}

		$sections = array();
		foreach ( $content as $id => $data ) {
			if ( self::is_valid_section_id( $id ) && is_array( $data ) ) {
				$sections[ $id ] = $data;
			}
		}

		/**
		 * Imports the content sections of a settings file.
		 *
		 * Each add-on handles the sections it exported, validates everything it
		 * reads, and adds a report under the section id: `label` (text), `created` and
		 * `updated` (counts), `skipped` (label => reason) and `notes` (label => what
		 * needs a review). It runs after the settings are applied, only for users who
		 * can import settings, and the add-on must still check what the user may create.
		 *
		 * @since 1.6.0
		 * @param array $report   Section id => report, filled by the handlers.
		 * @param array $sections Section id => data from the file.
		 */
		$report = apply_filters( 'frontblocks_import_content', array(), $sections );
		$report = is_array( $report ) ? $report : array();

		$result = array();
		foreach ( array_keys( $sections ) as $id ) {
			$result[ $id ] = isset( $report[ $id ] ) && is_array( $report[ $id ] )
				? self::normalize_section_report( $id, $report[ $id ] )
				: array(
					'label'   => $id,
					'created' => 0,
					'updated' => 0,
					'skipped' => array( $id => __( 'nothing on this site can import this content', 'frontblocks' ) ),
					'notes'   => array(),
				);
		}

		return $result;
	}

	/**
	 * Keep only the expected, plain values of an add-on report.
	 *
	 * @param string $id     Section id.
	 * @param array  $report Report returned by the add-on.
	 * @return array
	 */
	private static function normalize_section_report( $id, $report ) {
		$lists = array();
		foreach ( array( 'skipped', 'notes' ) as $list ) {
			$lists[ $list ] = array();
			foreach ( isset( $report[ $list ] ) && is_array( $report[ $list ] ) ? $report[ $list ] : array() as $label => $text ) {
				if ( is_scalar( $text ) ) {
					$lists[ $list ][ sanitize_text_field( (string) $label ) ] = sanitize_text_field( (string) $text );
				}
			}
		}

		return array(
			'label'   => isset( $report['label'] ) && is_string( $report['label'] ) && '' !== $report['label'] ? sanitize_text_field( $report['label'] ) : $id,
			'created' => isset( $report['created'] ) ? absint( $report['created'] ) : 0,
			'updated' => isset( $report['updated'] ) ? absint( $report['updated'] ) : 0,
			'skipped' => $lists['skipped'],
			'notes'   => $lists['notes'],
		);
	}

	/**
	 * URL of the Backup tab.
	 *
	 * @param array $args Query arguments to add.
	 * @return string
	 */
	private static function settings_url( $args = array() ) {
		$url = add_query_arg( $args, admin_url( 'themes.php?page=' . self::PAGE_SLUG ) );

		return $url . '#' . self::TAB_ID;
	}

	/**
	 * Add the Backup tab, before the License tab.
	 *
	 * @param array $tabs Settings tabs.
	 * @return array
	 */
	public function add_tab( $tabs ) {
		if ( ! is_array( $tabs ) ) {
			$tabs = array();
		}

		$tab      = array(
			'id'    => self::TAB_ID,
			'label' => __( 'Backup', 'frontblocks' ),
		);
		$position = count( $tabs );

		foreach ( $tabs as $index => $existing ) {
			if ( isset( $existing['id'] ) && 'license' === $existing['id'] ) {
				$position = $index;
				break;
			}
		}

		array_splice( $tabs, $position, 0, array( $tab ) );

		return $tabs;
	}

	/**
	 * Download the settings as a JSON file.
	 *
	 * @return void
	 */
	public function handle_export() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to export these settings.', 'frontblocks' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::EXPORT_ACTION );

		$json = wp_json_encode( self::build_export_payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . self::get_export_filename() . '"' );
		header( 'X-Content-Type-Options: nosniff' );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON document sent as a download, not HTML.
		exit;
	}

	/**
	 * Read the uploaded file, import it and go back to the Backup tab with the result.
	 *
	 * @return void
	 */
	public function handle_import() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to import settings.', 'frontblocks' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::IMPORT_ACTION );

		$result = $this->import_uploaded_file();

		self::store_result(
			is_wp_error( $result ) ? array( 'error' => $result->get_error_message() ) : $result
		);

		wp_safe_redirect( self::settings_url( array( 'frbl-transfer' => '1' ) ) );
		exit;
	}

	/**
	 * Import the file that came with the request.
	 *
	 * @return array|\WP_Error
	 */
	private function import_uploaded_file() {
		// The nonce is verified in handle_import(); every field is cast or sanitized as it is read.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$field    = self::FILE_FIELD;
		$error    = isset( $_FILES[ $field ]['error'] ) ? (int) $_FILES[ $field ]['error'] : UPLOAD_ERR_NO_FILE;
		$size     = isset( $_FILES[ $field ]['size'] ) ? (int) $_FILES[ $field ]['size'] : 0;
		$name     = isset( $_FILES[ $field ]['name'] ) ? sanitize_file_name( wp_unslash( $_FILES[ $field ]['name'] ) ) : '';
		$tmp_name = isset( $_FILES[ $field ]['tmp_name'] ) ? (string) $_FILES[ $field ]['tmp_name'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- checked with is_uploaded_file() below.
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( UPLOAD_ERR_NO_FILE === $error ) {
			return new \WP_Error( 'frbl_transfer_no_file', __( 'Choose a settings file to import.', 'frontblocks' ) );
		}

		if ( UPLOAD_ERR_OK !== $error ) {
			return new \WP_Error( 'frbl_transfer_upload_failed', __( 'The file could not be uploaded. Try again.', 'frontblocks' ) );
		}

		if ( $size > self::MAX_FILE_SIZE ) {
			return new \WP_Error( 'frbl_transfer_too_large', __( 'The file is too large to be a FrontBlocks settings export.', 'frontblocks' ) );
		}

		if ( 'json' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			return new \WP_Error( 'frbl_transfer_wrong_extension', __( 'Choose a .json file.', 'frontblocks' ) );
		}

		if ( '' === $tmp_name || ! is_uploaded_file( $tmp_name ) ) {
			return new \WP_Error( 'frbl_transfer_upload_failed', __( 'The file could not be uploaded. Try again.', 'frontblocks' ) );
		}

		$raw = file_get_contents( $tmp_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reads the uploaded temporary file, not a remote URL.
		if ( false === $raw ) {
			return new \WP_Error( 'frbl_transfer_upload_failed', __( 'The file could not be read. Try again.', 'frontblocks' ) );
		}

		return self::import_from_string( $raw );
	}

	/**
	 * Keep the result of an import for the next page load of the current user.
	 *
	 * @param array $result Import summary or error.
	 * @return void
	 */
	private static function store_result( $result ) {
		set_transient( self::result_key(), $result, 5 * MINUTE_IN_SECONDS );
	}

	/**
	 * Take the stored import result, so it is shown once.
	 *
	 * @return array|null
	 */
	private static function consume_result() {
		$result = get_transient( self::result_key() );
		delete_transient( self::result_key() );

		return is_array( $result ) ? $result : null;
	}

	/**
	 * Transient that holds the import result of the current user.
	 *
	 * @return string
	 */
	private static function result_key() {
		return 'frbl_transfer_result_' . get_current_user_id();
	}

	/**
	 * Render the Backup tab panel.
	 *
	 * @return void
	 */
	public function render_panel() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::EXPORT_ACTION ), self::EXPORT_ACTION );
		?>
		<div class="frbl-tab-panel" data-tab-panel="<?php echo esc_attr( self::TAB_ID ); ?>" hidden>
			<div class="frbl-tab-panel-head">
				<div>
					<h2><?php esc_html_e( 'Backup', 'frontblocks' ); ?></h2>
					<p><?php esc_html_e( 'Export the FrontBlocks settings to a file and import them on another site to set it up in one step.', 'frontblocks' ); ?></p>
				</div>
			</div>

			<?php $this->render_result(); ?>

			<div class="tw:space-y-6 tw:bg-white tw:border tw:border-gray-200 tw:rounded-lg tw:p-6 tw:mb-4">
				<div>
					<p class="tw:text-base tw:font-medium tw:text-gray-900 tw:mt-0 tw:mb-2"><?php esc_html_e( 'Export settings', 'frontblocks' ); ?></p>
					<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
						<?php esc_html_e( 'Downloads a JSON file with every module toggle and the options that are not tied to this site, plus the content that add-ons provide, such as the popups of FrontBlocks PRO. It contains no credentials or license keys, so it is safe to keep in a repository.', 'frontblocks' ); ?>
					</p>
					<a href="<?php echo esc_url( $export_url ); ?>" class="frbl-btn-primary">
						<?php esc_html_e( 'Download settings file', 'frontblocks' ); ?>
					</a>
				</div>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="tw:space-y-6 tw:bg-white tw:border tw:border-gray-200 tw:rounded-lg tw:p-6 tw:mb-4">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::IMPORT_ACTION ); ?>" />
				<?php wp_nonce_field( self::IMPORT_ACTION ); ?>

				<div>
					<p class="tw:text-base tw:font-medium tw:text-gray-900 tw:mt-0 tw:mb-2"><?php esc_html_e( 'Import settings', 'frontblocks' ); ?></p>
					<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
						<?php esc_html_e( 'Choose a file exported from FrontBlocks. Only the settings in the file change; the rest stay as they are. Content such as popups is created, or updated when it already exists. Values that are not valid are skipped and listed afterwards.', 'frontblocks' ); ?>
					</p>
					<label for="<?php echo esc_attr( self::FILE_FIELD ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
						<?php esc_html_e( 'Settings file (.json)', 'frontblocks' ); ?>
					</label>
					<input
						type="file"
						id="<?php echo esc_attr( self::FILE_FIELD ); ?>"
						name="<?php echo esc_attr( self::FILE_FIELD ); ?>"
						accept=".json,application/json"
						required
						class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-sm"
					/>
				</div>

				<div class="tw:flex tw:justify-end">
					<button type="submit" class="frbl-btn-primary">
						<?php esc_html_e( 'Import settings', 'frontblocks' ); ?>
					</button>
				</div>
			</form>

			<div class="tw:max-w-xl tw:p-6 tw:bg-blue-50 tw:border tw:border-blue-200 tw:rounded-lg">
				<p class="tw:text-base tw:font-medium tw:text-blue-900 tw:mt-0 tw:mb-2"><?php esc_html_e( 'What is not included', 'frontblocks' ); ?></p>
				<p class="tw:text-sm tw:text-blue-800 tw:mb-0">
					<?php esc_html_e( 'Google Sign-In credentials, license keys, the maintenance image and the image size configuration are left out because they belong to a single site or are secret. Set them up again on each site.', 'frontblocks' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the outcome of the last import, once.
	 *
	 * @return void
	 */
	private function render_result() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag, the result is stored per user.
		if ( ! isset( $_GET['frbl-transfer'] ) ) {
			return;
		}

		$result = self::consume_result();
		if ( null === $result ) {
			return;
		}

		if ( isset( $result['error'] ) ) {
			?>
			<div class="tw:mb-4 tw:p-3 tw:rounded-lg tw:bg-red-50 tw:border tw:border-red-200 tw:text-sm tw:text-red-700" role="alert">
				<?php
				/* translators: %s: reason the file was rejected */
				echo esc_html( sprintf( __( 'Nothing was imported. %s', 'frontblocks' ), $result['error'] ) );
				?>
			</div>
			<?php
			return;
		}

		$applied  = isset( $result['applied'] ) ? (array) $result['applied'] : array();
		$skipped  = isset( $result['skipped'] ) ? (array) $result['skipped'] : array();
		$adjusted = isset( $result['adjusted'] ) ? (array) $result['adjusted'] : array();
		?>
		<div class="tw:mb-4 tw:p-3 tw:rounded-lg tw:bg-green-50 tw:border tw:border-green-200 tw:text-sm tw:text-green-700" role="status">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of settings imported */
					_n( '%d setting imported.', '%d settings imported.', count( $applied ), 'frontblocks' ),
					count( $applied )
				)
			);

			if ( ! empty( $result['source_site'] ) ) {
				echo ' ';
				/* translators: %s: host name of the site the file came from */
				echo esc_html( sprintf( __( 'Exported from %s.', 'frontblocks' ), $result['source_site'] ) );
			}
			?>
		</div>
		<?php
		$this->render_notes( $adjusted, __( 'Adjusted to fit this site', 'frontblocks' ) );
		$this->render_notes( $skipped, __( 'Skipped', 'frontblocks' ) );

		foreach ( isset( $result['content'] ) ? (array) $result['content'] : array() as $section ) {
			$this->render_content_section( (array) $section );
		}
	}

	/**
	 * Show what an add-on did with its content, and what needs a review.
	 *
	 * @param array $section Normalized section report.
	 * @return void
	 */
	private function render_content_section( $section ) {
		$label   = isset( $section['label'] ) ? (string) $section['label'] : '';
		$created = isset( $section['created'] ) ? (int) $section['created'] : 0;
		$updated = isset( $section['updated'] ) ? (int) $section['updated'] : 0;

		if ( $created > 0 || $updated > 0 ) {
			?>
			<div class="tw:mb-4 tw:p-3 tw:rounded-lg tw:bg-green-50 tw:border tw:border-green-200 tw:text-sm tw:text-green-700" role="status">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: kind of content, for example Popups; 2: number created; 3: number updated */
						__( '%1$s: %2$d created, %3$d updated.', 'frontblocks' ),
						$label,
						$created,
						$updated
					)
				);
				?>
			</div>
			<?php
		}

		$this->render_notes(
			isset( $section['notes'] ) ? (array) $section['notes'] : array(),
			/* translators: %s: kind of content, for example Popups */
			sprintf( __( '%s: needs a review', 'frontblocks' ), $label )
		);
		$this->render_notes(
			isset( $section['skipped'] ) ? (array) $section['skipped'] : array(),
			/* translators: %s: kind of content, for example Popups */
			sprintf( __( '%s: skipped', 'frontblocks' ), $label )
		);
	}

	/**
	 * List settings that were adjusted or skipped, with the reason.
	 *
	 * @param array  $notes Setting key => reason.
	 * @param string $title Heading of the list.
	 * @return void
	 */
	private function render_notes( $notes, $title ) {
		if ( empty( $notes ) ) {
			return;
		}
		?>
		<div class="tw:mb-4 tw:p-3 tw:rounded-lg tw:bg-yellow-50 tw:border tw:border-yellow-200 tw:text-sm tw:text-yellow-700" role="status">
			<p class="tw:font-medium tw:mt-0 tw:mb-2"><?php echo esc_html( $title ); ?></p>
			<?php foreach ( $notes as $key => $reason ) : ?>
				<p class="tw:mt-0 tw:mb-1"><code><?php echo esc_html( $key ); ?></code>: <?php echo esc_html( $reason ); ?></p>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
