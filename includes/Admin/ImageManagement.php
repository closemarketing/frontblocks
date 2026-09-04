<?php
/**
 * Image Management settings
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

use FrontBlocks\Frontend\ImageManagement as FrontendImageManagement;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the Image Management section to the FrontBlocks settings page,
 * handles its sanitization, and processes the bulk regenerate/convert
 * AJAX actions.
 */
class ImageManagement {

	/**
	 * Settings page slug (matches Admin\Settings::$page_slug).
	 *
	 * @var string
	 */
	private $page_slug = 'frontblocks-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'frontblocks_register_settings', array( $this, 'register_settings' ) );
		add_filter( 'sanitize_option_frontblocks_settings', array( $this, 'sanitize_settings' ), 20, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		add_action( 'wp_ajax_frbl_list_image_attachment_ids', array( $this, 'ajax_list_attachment_ids' ) );
		add_action( 'wp_ajax_frbl_start_image_bulk_job', array( $this, 'ajax_start_bulk_job' ) );
		add_action( 'wp_ajax_frbl_image_bulk_job_status', array( $this, 'ajax_bulk_job_status' ) );
		add_action( self::PROCESS_ITEM_ACTION, array( $this, 'process_bulk_item' ), 10, 3 );
	}

	/**
	 * Action Scheduler hook used to process one attachment of a bulk job.
	 */
	const PROCESS_ITEM_ACTION = 'frontblocks_image_management_process_item';

	/**
	 * Action Scheduler group every Image Management bulk action is
	 * scheduled under, so they're easy to find/inspect via Tools > Scheduled
	 * Actions independently of any other plugin's (e.g. WooCommerce's) use
	 * of the same shared library.
	 */
	const JOB_GROUP = 'frontblocks-image-management';

	/**
	 * The three supported bulk job types.
	 */
	const JOB_TYPES = array( 'regenerate', 'convert', 'cleanup' );

	/**
	 * Register the settings section and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		add_settings_section(
			'frontblocks_section_image_management',
			__( 'Image Management', 'frontblocks' ),
			array( $this, 'section_callback' ),
			$this->page_slug
		);

		add_settings_field(
			'enable_image_management',
			__( 'Enable Image Management', 'frontblocks' ),
			array( $this, 'field_enable_image_management' ),
			$this->page_slug,
			'frontblocks_section_image_management'
		);
	}

	/**
	 * Section description.
	 *
	 * @return void
	 */
	public function section_callback() {
		echo '<p class="tw:text-sm tw:text-gray-500">' . esc_html__( 'Control which image sizes WordPress generates and automatically serve WebP/AVIF versions of your images.', 'frontblocks' ) . '</p>';
	}

	/**
	 * Never persist a target the server can't actually produce — the select
	 * shown to the user already hides unsupported options, but the saved
	 * option is re-checked here too, in case support changed since the page
	 * was loaded (e.g. a value saved on a different host, or a PHP extension
	 * that was removed after the settings screen was last opened).
	 *
	 * @param string $target Requested target: 'none', 'webp', 'avif', or 'both'.
	 * @return string A target the current server can produce, or 'none'.
	 */
	private function downgrade_target_to_supported_formats( $target ) {
		$webp_supported = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
		$avif_supported = wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) );

		if ( 'both' === $target ) {
			if ( $webp_supported && $avif_supported ) {
				return 'both';
			}
			$target = $avif_supported ? 'avif' : ( $webp_supported ? 'webp' : 'none' );
		}

		if ( 'webp' === $target && ! $webp_supported ) {
			$target = 'none';
		}

		if ( 'avif' === $target && ! $avif_supported ) {
			$target = 'none';
		}

		return $target;
	}

	/**
	 * Registered size names (core + additional), used by both the field
	 * render and sanitization.
	 *
	 * @return string[]
	 */
	private function get_registered_size_names() {
		global $_wp_additional_image_sizes;

		return array_values( array_unique( array_merge( FrontendImageManagement::CORE_SIZES, array_keys( (array) $_wp_additional_image_sizes ) ) ) );
	}

	/**
	 * Build the list of registered sizes with dimensions/crop/source, for
	 * the settings-page table and for JS localization.
	 *
	 * @return array
	 */
	private function get_registered_sizes_info() {
		global $_wp_additional_image_sizes;

		$options          = get_option( 'frontblocks_settings', array() );
		$own_custom_names = wp_list_pluck( (array) ( $options['image_sizes_custom'] ?? array() ), 'name' );

		$sizes = array();

		foreach ( FrontendImageManagement::CORE_SIZES as $name ) {
			$sizes[] = array(
				'name'   => $name,
				'width'  => (int) get_option( "{$name}_size_w" ),
				'height' => (int) get_option( "{$name}_size_h" ),
				'crop'   => (bool) get_option( "{$name}_crop" ),
				'source' => 'core',
			);
		}

		foreach ( (array) $_wp_additional_image_sizes as $name => $data ) {
			// Our own custom sizes are registered via add_image_size() too,
			// but are listed separately (and are editable/removable) in the
			// "custom" section below — skip them here to avoid duplicates.
			if ( in_array( $name, FrontendImageManagement::CORE_SIZES, true ) || in_array( $name, $own_custom_names, true ) ) {
				continue;
			}
			$sizes[] = array(
				'name'   => $name,
				'width'  => (int) ( $data['width'] ?? 0 ),
				'height' => (int) ( $data['height'] ?? 0 ),
				'crop'   => (bool) ( $data['crop'] ?? false ),
				'source' => 'theme/plugin',
			);
		}

		return $sizes;
	}

	/**
	 * Render the enable toggle plus the sizes table, format options, and
	 * bulk-action controls.
	 *
	 * @return void
	 */
	public function field_enable_image_management() {
		$options                      = get_option( 'frontblocks_settings', array() );
		$enabled                      = (bool) ( $options['enable_image_management'] ?? false );
		$disabled                     = (array) ( $options['image_sizes_disabled'] ?? array() );
		$overrides                    = (array) ( $options['image_sizes_overrides'] ?? array() );
		$custom                       = (array) ( $options['image_sizes_custom'] ?? array() );
		$format_target                = (string) ( $options['image_format_target'] ?? 'none' );
		$quality_by_fmt               = FrontendImageManagement::get_quality_settings( $options );
		$max_upload_dimension_enabled = (bool) ( $options['image_max_upload_dimension_enabled'] ?? true );
		$max_upload_dimension         = (int) ( $options['image_max_upload_dimension'] ?? 2048 );

		$sizes_info = $this->get_registered_sizes_info();
		$usage      = FrontendImageManagement::estimate_disk_usage_by_size( wp_list_pluck( $sizes_info, 'name' ) );

		$config = array(
			'sizes'     => $sizes_info,
			'disabled'  => $disabled,
			'overrides' => $overrides,
			'custom'    => $custom,
			'usage'     => $usage,
		);

		$sizes_config_json = wp_json_encode(
			array(
				'disabled'  => $disabled,
				'overrides' => $overrides,
				'custom'    => $custom,
			)
		);
		?>
		<div class="frbl-image-management">
			<div class="tw:flex tw:items-center tw:justify-between tw:mb-4">
				<label for="enable_image_management" class="tw:text-base tw:font-medium tw:text-gray-900">
					<?php echo esc_html__( 'Enable Image Management', 'frontblocks' ); ?>
				</label>
				<label class="frbl-toggle">
					<input type="checkbox"
						id="enable_image_management"
						name="frontblocks_settings[enable_image_management]"
						value="1"
						<?php checked( true, $enabled ); ?>
					/>
					<span></span>
				</label>
			</div>

			<div id="image-management-fields-wrapper" style="<?php echo $enabled ? '' : 'display: none;'; ?>">
				<input type="hidden" id="frbl-image-sizes-config" name="frontblocks_settings[image_sizes_config]" value="<?php echo esc_attr( $sizes_config_json ); ?>" />

				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200 tw:mb-4">
					<p class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html__( 'Registered image sizes', 'frontblocks' ); ?></p>
					<div id="frbl-image-sizes-table"></div>
				</div>

				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200 tw:mb-4">
					<div class="tw:flex tw:items-center tw:justify-between">
						<div>
							<label for="image_max_upload_dimension_enabled" class="tw:text-sm tw:font-medium tw:text-gray-700"><?php echo esc_html__( 'Limit max upload dimension', 'frontblocks' ); ?></label>
							<p class="tw:text-xs tw:text-gray-500 tw:mt-1"><?php echo esc_html__( 'On upload, downscale the stored original if it exceeds this size, so oversized source images don\'t bloat storage or slow down thumbnail generation. Off: keep full-size originals untouched.', 'frontblocks' ); ?></p>
						</div>
						<label class="frbl-toggle">
							<input type="checkbox"
								id="image_max_upload_dimension_enabled"
								name="frontblocks_settings[image_max_upload_dimension_enabled]"
								value="1"
								<?php checked( true, $max_upload_dimension_enabled ); ?>
							/>
							<span></span>
						</label>
					</div>
					<div class="tw:mt-3" style="<?php echo $max_upload_dimension_enabled ? '' : 'display: none;'; ?>" id="image-max-upload-dimension-field">
						<label for="image_max_upload_dimension" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html__( 'Max width/height (pixels)', 'frontblocks' ); ?></label>
						<input type="number" id="image_max_upload_dimension" name="frontblocks_settings[image_max_upload_dimension]" min="300" max="10000" step="1" value="<?php echo esc_attr( $max_upload_dimension ); ?>" class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base" />
					</div>
				</div>

				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200 tw:mb-4">
					<div class="tw:grid tw:grid-cols-2 tw:gap-4">
						<div>
							<?php
							$webp_supported = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
							$avif_supported = wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) );
							?>
							<label for="image_format_target" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html__( 'Modern format', 'frontblocks' ); ?></label>
							<select id="image_format_target" name="frontblocks_settings[image_format_target]" class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base">
								<option value="none" <?php selected( $format_target, 'none' ); ?>><?php echo esc_html__( 'Off', 'frontblocks' ); ?></option>
								<?php if ( $webp_supported ) : ?>
									<option value="webp" <?php selected( $format_target, 'webp' ); ?>><?php echo esc_html__( 'WebP', 'frontblocks' ); ?></option>
								<?php endif; ?>
								<?php if ( $avif_supported ) : ?>
									<option value="avif" <?php selected( $format_target, 'avif' ); ?>><?php echo esc_html__( 'AVIF', 'frontblocks' ); ?></option>
								<?php endif; ?>
								<?php if ( $webp_supported && $avif_supported ) : ?>
									<option value="both" <?php selected( $format_target, 'both' ); ?>><?php echo esc_html__( 'WebP and AVIF', 'frontblocks' ); ?></option>
								<?php endif; ?>
							</select>
							<?php if ( ! $webp_supported || ! $avif_supported ) : ?>
								<p class="tw:text-xs tw:text-amber-600 tw:mt-2">
									<?php
									echo $avif_supported
										? esc_html__( 'This server does not support generating WebP images, so that option is hidden.', 'frontblocks' )
										: ( $webp_supported
											? esc_html__( 'This server does not support generating AVIF images, so that option is hidden.', 'frontblocks' )
											: esc_html__( 'This server does not support generating WebP or AVIF images. Ask your host to enable the Imagick or GD PHP extension with modern-format support.', 'frontblocks' ) );
									?>
								</p>
							<?php endif; ?>
						</div>
						<div>
							<label for="image_format_quality_webp" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html__( 'WebP quality', 'frontblocks' ); ?> (<span id="frbl-image-quality-webp-value"><?php echo esc_html( $quality_by_fmt['webp'] ); ?></span>)</label>
							<input type="range" id="image_format_quality_webp" name="frontblocks_settings[image_format_quality_webp]" min="1" max="100" value="<?php echo esc_attr( $quality_by_fmt['webp'] ); ?>" class="tw:block tw:w-full tw:mb-4" />
							<label for="image_format_quality_avif" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html__( 'AVIF quality', 'frontblocks' ); ?> (<span id="frbl-image-quality-avif-value"><?php echo esc_html( $quality_by_fmt['avif'] ); ?></span>)</label>
							<input type="range" id="image_format_quality_avif" name="frontblocks_settings[image_format_quality_avif]" min="1" max="100" value="<?php echo esc_attr( $quality_by_fmt['avif'] ); ?>" class="tw:block tw:w-full" />
							<p class="tw:text-xs tw:text-gray-500 tw:mt-1"><?php echo esc_html__( 'AVIF defaults lower than WebP: the same numeric quality produces a noticeably smaller AVIF file at a visually comparable result.', 'frontblocks' ); ?></p>
						</div>
					</div>
				</div>

				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200">
					<p class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html__( 'Existing media library items', 'frontblocks' ); ?></p>
					<p class="tw:text-xs tw:text-gray-500 tw:mb-3"><?php echo esc_html__( 'Save your settings above before running these — they use the saved size and format configuration.', 'frontblocks' ); ?></p>
					<div class="tw:flex tw:gap-3 tw:mb-3">
						<button type="button" class="button" id="frbl-bulk-regenerate"><?php echo esc_html__( 'Regenerate thumbnails', 'frontblocks' ); ?></button>
						<button type="button" class="button" id="frbl-bulk-convert"><?php echo esc_html__( 'Convert to modern formats', 'frontblocks' ); ?></button>
						<button type="button" class="button" id="frbl-bulk-cleanup"><?php echo esc_html__( 'Delete files for disabled sizes', 'frontblocks' ); ?></button>
					</div>
					<div id="frbl-image-bulk-progress" class="frbl-image-bulk-progress" style="display: none;">
						<div class="frbl-image-bulk-progress__bar"><div class="frbl-image-bulk-progress__fill"></div></div>
						<p class="frbl-image-bulk-progress__label"></p>
					</div>
				</div>
			</div>
		</div>
		<script type="application/json" id="frbl-image-management-config"><?php echo wp_json_encode( $config ); ?></script>
		<?php
	}

	/**
	 * Sanitize the module's settings. Hooked onto the same core filter
	 * register_setting() uses for Settings::sanitize_settings(), which
	 * starts from the previously saved option and only copies forward
	 * keys it explicitly recognizes — so our fields must be read directly
	 * from $_POST rather than from $value. See docs/EXTENDING-SETTINGS.md.
	 *
	 * @param array  $value  Sanitized value built by Settings::sanitize_settings().
	 * @param string $option Option name.
	 * @return array
	 */
	public function sanitize_settings( $value, $option ) {
		if ( 'frontblocks_settings' !== $option || ! is_array( $value ) ) {
			return $value;
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'frontblocks_settings-options' ) ) {
			return $value;
		}

		if ( ! isset( $_POST['frontblocks_settings'] ) || ! is_array( $_POST['frontblocks_settings'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- nonce verified above.
			return $value;
		}

		$posted = wp_unslash( $_POST['frontblocks_settings'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above; every value read from $posted below is sanitized individually.

		$value['enable_image_management'] = ! empty( $posted['enable_image_management'] );

		$allowed_targets              = array( 'none', 'webp', 'avif', 'both' );
		$posted_target                = $posted['image_format_target'] ?? 'none';
		$requested_target             = in_array( $posted_target, $allowed_targets, true ) ? $posted_target : 'none';
		$value['image_format_target'] = $this->downgrade_target_to_supported_formats( $requested_target );

		$value['image_max_upload_dimension_enabled'] = ! empty( $posted['image_max_upload_dimension_enabled'] );

		$max_dimension                       = absint( $posted['image_max_upload_dimension'] ?? 2048 );
		$value['image_max_upload_dimension'] = $max_dimension >= 300 ? min( $max_dimension, 10000 ) : 2048;

		$webp_quality                       = absint( $posted['image_format_quality_webp'] ?? 82 );
		$value['image_format_quality_webp'] = $webp_quality > 0 ? min( $webp_quality, 100 ) : 82;

		$avif_quality                       = absint( $posted['image_format_quality_avif'] ?? 60 );
		$value['image_format_quality_avif'] = $avif_quality > 0 ? min( $avif_quality, 100 ) : 60;

		$config = array();
		if ( ! empty( $posted['image_sizes_config'] ) ) {
			$decoded = json_decode( $posted['image_sizes_config'], true );
			if ( is_array( $decoded ) ) {
				$config = $decoded;
			}
		}

		$allowed_size_names = $this->get_registered_size_names();

		$value['image_sizes_disabled'] = array_values(
			array_intersect(
				array_map( 'sanitize_key', (array) ( $config['disabled'] ?? array() ) ),
				$allowed_size_names
			)
		);

		$overrides = array();
		foreach ( (array) ( $config['overrides'] ?? array() ) as $name => $size ) {
			$name = sanitize_key( $name );
			if ( ! in_array( $name, $allowed_size_names, true ) || ! is_array( $size ) ) {
				continue;
			}
			$overrides[ $name ] = array(
				'width'  => absint( $size['width'] ?? 0 ),
				'height' => absint( $size['height'] ?? 0 ),
				'crop'   => ! empty( $size['crop'] ),
			);
		}
		$value['image_sizes_overrides'] = $overrides;

		$custom = array();
		foreach ( (array) ( $config['custom'] ?? array() ) as $size ) {
			if ( ! is_array( $size ) || empty( $size['name'] ) ) {
				continue;
			}
			$custom[] = array(
				'name'           => sanitize_key( $size['name'] ),
				'width'          => absint( $size['width'] ?? 0 ),
				'height'         => absint( $size['height'] ?? 0 ),
				'crop'           => ! empty( $size['crop'] ),
				'label'          => sanitize_text_field( $size['label'] ?? '' ),
				'show_in_picker' => ! empty( $size['show_in_picker'] ),
			);
		}
		$value['image_sizes_custom'] = $custom;

		// Core sizes (thumbnail/medium/medium_large/large) are stored as
		// their own wp_options, not via add_image_size(), so apply those
		// overrides here once at save time instead of on every request.
		foreach ( FrontendImageManagement::CORE_SIZES as $core_name ) {
			if ( empty( $overrides[ $core_name ] ) ) {
				continue;
			}
			update_option( "{$core_name}_size_w", $overrides[ $core_name ]['width'] );
			update_option( "{$core_name}_size_h", $overrides[ $core_name ]['height'] );
			update_option( "{$core_name}_crop", $overrides[ $core_name ]['crop'] );
		}

		return $value;
	}

	/**
	 * Enqueue the settings-page script/style for this module.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( 'appearance_page_' . $this->page_slug !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'frontblocks-image-management',
			FRBL_PLUGIN_URL . 'assets/image-management/frontblocks-image-management.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-image-management',
			FRBL_PLUGIN_URL . 'assets/image-management/frontblocks-image-management.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-image-management',
			'frblImageManagement',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'frbl_image_management' ),
				'i18n'    => array(
					/* translators: %1$d: items processed so far, %2$d: total items. */
					'processing' => __( 'Processing %1$d of %2$d…', 'frontblocks' ),
					/* translators: %d: total items processed. */
					'done'       => __( 'Done — processed %d items.', 'frontblocks' ),
					'error'      => __( 'Something went wrong, please try again.', 'frontblocks' ),
				),
			)
		);
	}

	/**
	 * AJAX: list all image attachment IDs, used by the bulk-action JS to
	 * build its batch queue.
	 *
	 * @return void
	 */
	public function ajax_list_attachment_ids() {
		check_ajax_referer( 'frbl_image_management', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'frontblocks' ) ), 403 );
		}

		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		wp_send_json_success( array( 'ids' => array_map( 'intval', $ids ) ) );
	}

	/**
	 * AJAX: start a bulk job (regenerate/convert/cleanup) — schedules one
	 * Action Scheduler action per image attachment on a background queue,
	 * so processing continues even if this browser tab is closed, and each
	 * attachment is its own request-sized unit of work (no bulk-timeout risk
	 * on large libraries).
	 *
	 * @return void
	 */
	public function ajax_start_bulk_job() {
		check_ajax_referer( 'frbl_image_management', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'frontblocks' ) ), 403 );
		}

		$job_type = isset( $_POST['job_type'] ) ? sanitize_key( wp_unslash( $_POST['job_type'] ) ) : '';
		if ( ! in_array( $job_type, self::JOB_TYPES, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid job type.', 'frontblocks' ) ), 400 );
		}

		if ( ! function_exists( 'as_enqueue_async_action' ) ) {
			wp_send_json_error( array( 'message' => __( 'The background job library is unavailable.', 'frontblocks' ) ), 500 );
		}

		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$job_id = wp_generate_uuid4();

		update_option(
			$this->job_option_name( $job_id ),
			array(
				'total' => count( $ids ),
				'done'  => 0,
			),
			false
		);

		foreach ( $ids as $attachment_id ) {
			as_enqueue_async_action(
				self::PROCESS_ITEM_ACTION,
				array( $job_type, (int) $attachment_id, $job_id ),
				self::JOB_GROUP
			);
		}

		wp_send_json_success(
			array(
				'job_id' => $job_id,
				'total'  => count( $ids ),
			)
		);
	}

	/**
	 * AJAX: report a bulk job's progress so far.
	 *
	 * @return void
	 */
	public function ajax_bulk_job_status() {
		check_ajax_referer( 'frbl_image_management', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'frontblocks' ) ), 403 );
		}

		$job_id      = isset( $_POST['job_id'] ) ? sanitize_key( wp_unslash( $_POST['job_id'] ) ) : '';
		$option_name = $this->job_option_name( $job_id );
		$progress    = get_option( $option_name, false );

		if ( false === $progress || ! isset( $progress['total'], $progress['done'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown job.', 'frontblocks' ) ), 404 );
		}

		if ( $progress['done'] >= $progress['total'] ) {
			delete_option( $option_name );
		}

		wp_send_json_success( $progress );
	}

	/**
	 * Action Scheduler callback: process one attachment for a bulk job.
	 *
	 * @param string $job_type      One of self::JOB_TYPES.
	 * @param int    $attachment_id Attachment post ID.
	 * @param string $job_id        Job id, to update its progress counter.
	 * @return void
	 */
	public function process_bulk_item( $job_type, $attachment_id, $job_id ) {
		switch ( $job_type ) {
			case 'regenerate':
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$file = get_attached_file( $attachment_id );
				if ( $file && file_exists( $file ) ) {
					$metadata = wp_generate_attachment_metadata( $attachment_id, $file );
					if ( ! is_wp_error( $metadata ) && ! empty( $metadata ) ) {
						wp_update_attachment_metadata( $attachment_id, $metadata );
					}
				}
				break;

			case 'convert':
				FrontendImageManagement::convert_attachment( $attachment_id );
				break;

			case 'cleanup':
				FrontendImageManagement::cleanup_disabled_size_files( $attachment_id );
				break;
		}

		$option_name = $this->job_option_name( $job_id );
		$progress    = get_option( $option_name, false );
		if ( false !== $progress && isset( $progress['total'], $progress['done'] ) ) {
			$progress['done'] = min( $progress['total'], $progress['done'] + 1 );
			update_option( $option_name, $progress, false );
		}
	}

	/**
	 * Build the option name storing one bulk job's progress.
	 *
	 * @param string $job_id Job id.
	 * @return string
	 */
	private function job_option_name( $job_id ) {
		return 'frbl_image_job_' . $job_id;
	}
}
