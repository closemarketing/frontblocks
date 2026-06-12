<?php
/**
 * Popups module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Popups class.
 *
 * @since 1.0.0
 */
class Popups {

	/**
	 * Option key for popups feature.
	 *
	 * @var string
	 */
	private $option_enable_popups = 'enable_popups';

	/**
	 * CPT slug.
	 *
	 * @var string
	 */
	const CPT = 'frbl_popup';

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! $this->is_popups_enabled() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::CPT, array( $this, 'save_meta' ) );
		add_action( 'wp_footer', array( $this, 'render_popups' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Check if popups are enabled.
	 *
	 * @return bool
	 */
	private function is_popups_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options[ $this->option_enable_popups ] ?? false );
	}

	/**
	 * Register popups custom post type.
	 *
	 * @return void
	 */
	public function register_cpt() {
		$labels = array(
			'name'               => __( 'Popups', 'frontblocks' ),
			'singular_name'      => __( 'Popup', 'frontblocks' ),
			'menu_name'          => __( 'Popups', 'frontblocks' ),
			'all_items'          => __( 'All popups', 'frontblocks' ),
			'add_new'            => __( 'Add popup', 'frontblocks' ),
			'add_new_item'       => __( 'Add new popup', 'frontblocks' ),
			'edit_item'          => __( 'Edit popup', 'frontblocks' ),
			'new_item'           => __( 'New popup', 'frontblocks' ),
			'view_item'          => __( 'View popup', 'frontblocks' ),
			'search_items'       => __( 'Search popups', 'frontblocks' ),
			'not_found'          => __( 'No popups found', 'frontblocks' ),
			'not_found_in_trash' => __( 'No popups in trash', 'frontblocks' ),
		);

		$args = array(
			'label'         => __( 'Popups', 'frontblocks' ),
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'menu_position' => 25,
			'menu_icon'     => 'dashicons-welcome-widgets-menus',
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'       => false,
		);

		register_post_type( self::CPT, $args );
	}

	/**
	 * Add meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'frbl_popup_settings',
			__( 'Popup Settings', 'frontblocks' ),
			array( $this, 'render_meta_box' ),
			self::CPT,
			'side',
			'high'
		);
	}

	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		$meta = $this->get_meta( $post->ID );
		wp_nonce_field( 'frbl_popup_save_meta', 'frbl_popup_meta_nonce' );

		$all_post_types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $all_post_types[ self::CPT ] );

		$all_pages = get_pages(
			array(
				'sort_column' => 'post_title',
				'sort_order'  => 'ASC',
			)
		);
		?>
		<style>
			#frbl_popup_settings .frbl-field { margin-bottom: 14px; }
			#frbl_popup_settings label.frbl-label { font-weight: 600; display: block; margin-bottom: 4px; font-size: 12px; text-transform: uppercase; color: #555; }
			#frbl_popup_settings input[type="number"],
			#frbl_popup_settings input[type="text"],
			#frbl_popup_settings select { width: 100%; }
			#frbl_popup_settings .frbl-sub { margin-top: 8px; padding: 10px; background: #f9f9f9; border-radius: 4px; border: 1px solid #e0e0e0; }
			#frbl_popup_settings select[multiple] { height: 120px; }
			#frbl_popup_settings .frbl-sep { border: none; border-top: 1px solid #e0e0e0; margin: 14px 0; }
			#frbl_popup_settings .frbl-inline { display: flex; align-items: center; gap: 6px; }
			#frbl_popup_settings .frbl-inline input[type="number"],
			#frbl_popup_settings .frbl-inline input[type="text"] { width: auto; flex: 1; }
		</style>

		<div id="frbl_popup_settings">

			<!-- WHERE -->
			<div class="frbl-field">
				<label class="frbl-label"><?php esc_html_e( 'Show on', 'frontblocks' ); ?></label>
				<select name="frbl_popup[where]" id="frbl_popup_where">
					<option value="all" <?php selected( $meta['where'], 'all' ); ?>><?php esc_html_e( 'All pages', 'frontblocks' ); ?></option>
					<option value="homepage" <?php selected( $meta['where'], 'homepage' ); ?>><?php esc_html_e( 'Homepage only', 'frontblocks' ); ?></option>
					<option value="specific_pages" <?php selected( $meta['where'], 'specific_pages' ); ?>><?php esc_html_e( 'Specific pages', 'frontblocks' ); ?></option>
					<option value="specific_post_types" <?php selected( $meta['where'], 'specific_post_types' ); ?>><?php esc_html_e( 'Specific post types', 'frontblocks' ); ?></option>
					<option value="url_contains" <?php selected( $meta['where'], 'url_contains' ); ?>><?php esc_html_e( 'URL contains…', 'frontblocks' ); ?></option>
				</select>

				<!-- Specific pages -->
				<div class="frbl-sub" id="frbl_where_specific_pages" style="<?php echo 'specific_pages' === $meta['where'] ? '' : 'display:none;'; ?>">
					<label><?php esc_html_e( 'Select pages', 'frontblocks' ); ?></label>
					<select name="frbl_popup[specific_pages][]" multiple>
						<?php foreach ( $all_pages as $page ) : ?>
							<option value="<?php echo esc_attr( $page->ID ); ?>" <?php echo in_array( (string) $page->ID, (array) $meta['specific_pages'], true ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $page->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Specific post types -->
				<div class="frbl-sub" id="frbl_where_specific_post_types" style="<?php echo 'specific_post_types' === $meta['where'] ? '' : 'display:none;'; ?>">
					<label><?php esc_html_e( 'Select post types', 'frontblocks' ); ?></label>
					<?php foreach ( $all_post_types as $pt ) : ?>
						<label style="font-weight:normal; display:flex; align-items:center; gap:6px; margin-bottom:4px;">
							<input type="checkbox" name="frbl_popup[specific_post_types][]" value="<?php echo esc_attr( $pt->name ); ?>" <?php echo in_array( $pt->name, (array) $meta['specific_post_types'], true ) ? 'checked' : ''; ?> />
							<?php echo esc_html( $pt->label ); ?>
						</label>
					<?php endforeach; ?>
				</div>

				<!-- URL contains -->
				<div class="frbl-sub" id="frbl_where_url_contains" style="<?php echo 'url_contains' === $meta['where'] ? '' : 'display:none;'; ?>">
					<label><?php esc_html_e( 'URL contains (one per line)', 'frontblocks' ); ?></label>
					<textarea name="frbl_popup[url_contains]" rows="3" style="width:100%;"><?php echo esc_textarea( $meta['url_contains'] ); ?></textarea>
				</div>
			</div>

			<hr class="frbl-sep">

			<!-- TRIGGER -->
			<div class="frbl-field">
				<label class="frbl-label"><?php esc_html_e( 'Trigger', 'frontblocks' ); ?></label>
				<select name="frbl_popup[trigger]" id="frbl_popup_trigger">
					<option value="load" <?php selected( $meta['trigger'], 'load' ); ?>><?php esc_html_e( 'On page load', 'frontblocks' ); ?></option>
					<option value="delay" <?php selected( $meta['trigger'], 'delay' ); ?>><?php esc_html_e( 'After delay', 'frontblocks' ); ?></option>
					<option value="scroll" <?php selected( $meta['trigger'], 'scroll' ); ?>><?php esc_html_e( 'After scroll %', 'frontblocks' ); ?></option>
					<option value="exit_intent" <?php selected( $meta['trigger'], 'exit_intent' ); ?>><?php esc_html_e( 'Exit intent', 'frontblocks' ); ?></option>
					<option value="button" <?php selected( $meta['trigger'], 'button' ); ?>><?php esc_html_e( 'On button click', 'frontblocks' ); ?></option>
					<option value="inactivity" <?php selected( $meta['trigger'], 'inactivity' ); ?>><?php esc_html_e( 'After inactivity', 'frontblocks' ); ?></option>
				</select>

				<!-- Delay seconds -->
				<div class="frbl-sub frbl-inline" id="frbl_trigger_delay" style="<?php echo 'delay' === $meta['trigger'] ? '' : 'display:none;'; ?>">
					<label><?php esc_html_e( 'Delay (seconds)', 'frontblocks' ); ?></label>
					<input type="number" name="frbl_popup[trigger_delay]" value="<?php echo esc_attr( $meta['trigger_delay'] ); ?>" min="0" step="0.5">
				</div>

				<!-- Scroll percent -->
				<div class="frbl-sub frbl-inline" id="frbl_trigger_scroll" style="<?php echo 'scroll' === $meta['trigger'] ? '' : 'display:none;'; ?>">
					<label><?php esc_html_e( 'Scroll percentage', 'frontblocks' ); ?></label>
					<input type="number" name="frbl_popup[trigger_scroll]" value="<?php echo esc_attr( $meta['trigger_scroll'] ); ?>" min="1" max="100" step="1">
					<span>%</span>
				</div>

				<!-- Button selector -->
				<div class="frbl-sub" id="frbl_trigger_button" style="<?php echo 'button' === $meta['trigger'] ? '' : 'display:none;'; ?>">
					<label><?php esc_html_e( 'CSS selector (e.g. #my-btn, .open-popup)', 'frontblocks' ); ?></label>
					<input type="text" name="frbl_popup[trigger_selector]" value="<?php echo esc_attr( $meta['trigger_selector'] ); ?>" placeholder=".open-popup">
				</div>

				<!-- Inactivity seconds -->
				<div class="frbl-sub frbl-inline" id="frbl_trigger_inactivity" style="<?php echo 'inactivity' === $meta['trigger'] ? '' : 'display:none;'; ?>">
					<label><?php esc_html_e( 'Inactivity (seconds)', 'frontblocks' ); ?></label>
					<input type="number" name="frbl_popup[trigger_inactivity]" value="<?php echo esc_attr( $meta['trigger_inactivity'] ); ?>" min="1" step="1">
				</div>
			</div>

			<hr class="frbl-sep">

			<!-- FREQUENCY -->
			<div class="frbl-field">
				<label class="frbl-label"><?php esc_html_e( 'Show frequency', 'frontblocks' ); ?></label>
				<select name="frbl_popup[frequency]">
					<option value="always" <?php selected( $meta['frequency'], 'always' ); ?>><?php esc_html_e( 'Every page load', 'frontblocks' ); ?></option>
					<option value="session" <?php selected( $meta['frequency'], 'session' ); ?>><?php esc_html_e( 'Once per session', 'frontblocks' ); ?></option>
					<option value="daily" <?php selected( $meta['frequency'], 'daily' ); ?>><?php esc_html_e( 'Once per day', 'frontblocks' ); ?></option>
					<option value="weekly" <?php selected( $meta['frequency'], 'weekly' ); ?>><?php esc_html_e( 'Once per week', 'frontblocks' ); ?></option>
					<option value="once" <?php selected( $meta['frequency'], 'once' ); ?>><?php esc_html_e( 'Once ever', 'frontblocks' ); ?></option>
				</select>
			</div>

			<hr class="frbl-sep">

			<!-- APPEARANCE -->
			<div class="frbl-field">
				<label class="frbl-label"><?php esc_html_e( 'Animation', 'frontblocks' ); ?></label>
				<select name="frbl_popup[animation]">
					<option value="fade" <?php selected( $meta['animation'], 'fade' ); ?>><?php esc_html_e( 'Fade', 'frontblocks' ); ?></option>
					<option value="slide_up" <?php selected( $meta['animation'], 'slide_up' ); ?>><?php esc_html_e( 'Slide up', 'frontblocks' ); ?></option>
					<option value="zoom" <?php selected( $meta['animation'], 'zoom' ); ?>><?php esc_html_e( 'Zoom in', 'frontblocks' ); ?></option>
					<option value="none" <?php selected( $meta['animation'], 'none' ); ?>><?php esc_html_e( 'None', 'frontblocks' ); ?></option>
				</select>
			</div>

			<div class="frbl-field">
				<label class="frbl-label"><?php esc_html_e( 'Max width (px)', 'frontblocks' ); ?></label>
				<input type="number" name="frbl_popup[max_width]" value="<?php echo esc_attr( $meta['max_width'] ); ?>" min="200" max="1920" step="10">
			</div>

			<div class="frbl-field">
				<label style="display:flex; align-items:center; gap:8px; font-weight:normal;">
					<input type="checkbox" name="frbl_popup[overlay]" value="1" <?php checked( '1', $meta['overlay'] ); ?>>
					<?php esc_html_e( 'Show overlay', 'frontblocks' ); ?>
				</label>
			</div>

			<div class="frbl-field">
				<label style="display:flex; align-items:center; gap:8px; font-weight:normal;">
					<input type="checkbox" name="frbl_popup[close_on_overlay]" value="1" <?php checked( '1', $meta['close_on_overlay'] ); ?>>
					<?php esc_html_e( 'Close on overlay click', 'frontblocks' ); ?>
				</label>
			</div>

			<div class="frbl-field">
				<label style="display:flex; align-items:center; gap:8px; font-weight:normal;">
					<input type="checkbox" name="frbl_popup[show_close_button]" value="1" <?php checked( '1', $meta['show_close_button'] ); ?>>
					<?php esc_html_e( 'Show close button', 'frontblocks' ); ?>
				</label>
			</div>

			<div class="frbl-field">
				<label class="frbl-label"><?php esc_html_e( 'Background color', 'frontblocks' ); ?></label>
				<input type="color" name="frbl_popup[bg_color]" value="<?php echo esc_attr( $meta['bg_color'] ); ?>" style="width:48px; height:32px; padding:2px; cursor:pointer;">
			</div>

			<div class="frbl-field">
				<label class="frbl-label"><?php esc_html_e( 'Close button color', 'frontblocks' ); ?></label>
				<input type="color" name="frbl_popup[close_color]" value="<?php echo esc_attr( $meta['close_color'] ); ?>" style="width:48px; height:32px; padding:2px; cursor:pointer;">
			</div>

		</div>

		<script>
		(function() {
			var whereSelect  = document.getElementById('frbl_popup_where');
			var triggerSelect = document.getElementById('frbl_popup_trigger');

			var whereSubIds = ['frbl_where_specific_pages', 'frbl_where_specific_post_types', 'frbl_where_url_contains'];
			var triggerSubIds = ['frbl_trigger_delay', 'frbl_trigger_scroll', 'frbl_trigger_button', 'frbl_trigger_inactivity'];

			var whereMap = {
				specific_pages: 'frbl_where_specific_pages',
				specific_post_types: 'frbl_where_specific_post_types',
				url_contains: 'frbl_where_url_contains',
			};
			var triggerMap = {
				delay: 'frbl_trigger_delay',
				scroll: 'frbl_trigger_scroll',
				button: 'frbl_trigger_button',
				inactivity: 'frbl_trigger_inactivity',
			};

			function toggle(ids, showId) {
				ids.forEach(function(id) {
					var el = document.getElementById(id);
					if (el) el.style.display = (id === showId) ? '' : 'none';
				});
			}

			whereSelect.addEventListener('change', function() {
				toggle(whereSubIds, whereMap[this.value] || null);
			});
			triggerSelect.addEventListener('change', function() {
				toggle(triggerSubIds, triggerMap[this.value] || null);
			});
		}());
		</script>
		<?php
	}

	/**
	 * Get popup meta with defaults.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_meta( $post_id ) {
		$defaults = array(
			'where'                => 'all',
			'specific_pages'       => array(),
			'specific_post_types'  => array(),
			'url_contains'         => '',
			'trigger'              => 'load',
			'trigger_delay'        => 3,
			'trigger_scroll'       => 50,
			'trigger_selector'     => '',
			'trigger_inactivity'   => 30,
			'frequency'            => 'session',
			'animation'            => 'fade',
			'max_width'            => 600,
			'overlay'              => '1',
			'close_on_overlay'     => '1',
			'show_close_button'    => '1',
			'bg_color'             => '#ffffff',
			'close_color'          => '#333333',
		);

		$stored = get_post_meta( $post_id, '_frbl_popup', true );

		if ( ! is_array( $stored ) ) {
			return $defaults;
		}

		return array_merge( $defaults, $stored );
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['frbl_popup_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['frbl_popup_meta_nonce'] ) ), 'frbl_popup_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		$raw = isset( $_POST['frbl_popup'] ) ? wp_unslash( $_POST['frbl_popup'] ) : array();

		$data = array(
			'where'               => sanitize_text_field( $raw['where'] ?? 'all' ),
			'specific_pages'      => array_map( 'absint', (array) ( $raw['specific_pages'] ?? array() ) ),
			'specific_post_types' => array_map( 'sanitize_key', (array) ( $raw['specific_post_types'] ?? array() ) ),
			'url_contains'        => sanitize_textarea_field( $raw['url_contains'] ?? '' ),
			'trigger'             => sanitize_text_field( $raw['trigger'] ?? 'load' ),
			'trigger_delay'       => (float) ( $raw['trigger_delay'] ?? 3 ),
			'trigger_scroll'      => absint( $raw['trigger_scroll'] ?? 50 ),
			'trigger_selector'    => sanitize_text_field( $raw['trigger_selector'] ?? '' ),
			'trigger_inactivity'  => absint( $raw['trigger_inactivity'] ?? 30 ),
			'frequency'           => sanitize_text_field( $raw['frequency'] ?? 'session' ),
			'animation'           => sanitize_text_field( $raw['animation'] ?? 'fade' ),
			'max_width'           => absint( $raw['max_width'] ?? 600 ),
			'overlay'             => isset( $raw['overlay'] ) ? '1' : '0',
			'close_on_overlay'    => isset( $raw['close_on_overlay'] ) ? '1' : '0',
			'show_close_button'   => isset( $raw['show_close_button'] ) ? '1' : '0',
			'bg_color'            => sanitize_hex_color( $raw['bg_color'] ?? '#ffffff' ) ?: '#ffffff',
			'close_color'         => sanitize_hex_color( $raw['close_color'] ?? '#333333' ) ?: '#333333',
		);

		update_post_meta( $post_id, '_frbl_popup', $data );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$popups = $this->get_active_popups_for_current_page();
		if ( empty( $popups ) ) {
			return;
		}

		wp_enqueue_style(
			'frontblocks-popups',
			FRBL_PLUGIN_URL . 'assets/popups/frontblocks-popups.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-popups',
			FRBL_PLUGIN_URL . 'assets/popups/frontblocks-popups.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-popups',
			'frblPopups',
			array( 'popups' => $popups )
		);
	}

	/**
	 * Get active popups that match the current page conditions.
	 *
	 * @return array Array of popup config objects.
	 */
	private function get_active_popups_for_current_page() {
		$posts = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			)
		);

		if ( empty( $posts ) ) {
			return array();
		}

		$active = array();

		foreach ( $posts as $post ) {
			$meta = $this->get_meta( $post->ID );

			if ( ! $this->matches_current_page( $meta ) ) {
				continue;
			}

			$active[] = array(
				'id'              => $post->ID,
				'trigger'         => $meta['trigger'],
				'triggerDelay'    => $meta['trigger_delay'],
				'triggerScroll'   => $meta['trigger_scroll'],
				'triggerSelector' => $meta['trigger_selector'],
				'triggerInact'    => $meta['trigger_inactivity'],
				'frequency'       => $meta['frequency'],
				'animation'       => $meta['animation'],
				'overlay'         => '1' === $meta['overlay'],
				'closeOnOverlay'  => '1' === $meta['close_on_overlay'],
			);
		}

		return $active;
	}

	/**
	 * Check whether popup meta conditions match the current page.
	 *
	 * @param array $meta Popup meta.
	 * @return bool
	 */
	private function matches_current_page( $meta ) {
		$where = $meta['where'];

		if ( 'all' === $where ) {
			return true;
		}

		if ( 'homepage' === $where ) {
			return is_front_page();
		}

		if ( 'specific_pages' === $where ) {
			global $post;
			$page_id = $post ? $post->ID : 0;
			return in_array( $page_id, (array) $meta['specific_pages'], false ); // loose compare (int vs string).
		}

		if ( 'specific_post_types' === $where ) {
			return is_singular( $meta['specific_post_types'] );
		}

		if ( 'url_contains' === $where ) {
			$patterns = array_filter( array_map( 'trim', explode( "\n", $meta['url_contains'] ) ) );
			$current  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			foreach ( $patterns as $pattern ) {
				if ( false !== strpos( $current, $pattern ) ) {
					return true;
				}
			}
			return false;
		}

		return true;
	}

	/**
	 * Output popup HTML in wp_footer.
	 *
	 * @return void
	 */
	public function render_popups() {
		$popups = $this->get_active_popups_for_current_page();

		if ( empty( $popups ) ) {
			return;
		}

		foreach ( $popups as $popup_config ) {
			$post_id = $popup_config['id'];
			$meta    = $this->get_meta( $post_id );
			$content = get_post_field( 'post_content', $post_id );
			$content = apply_filters( 'the_content', $content );

			$animation_class   = 'frbl-popup--' . esc_attr( $meta['animation'] );
			$max_width         = absint( $meta['max_width'] );
			$show_close_button = '1' === $meta['show_close_button'];
			$overlay           = '1' === $meta['overlay'];
			$bg_color          = esc_attr( $meta['bg_color'] );
			$close_color       = esc_attr( $meta['close_color'] );
			?>
			<div
				id="frbl-popup-<?php echo esc_attr( $post_id ); ?>"
				class="frbl-popup-wrapper"
				role="dialog"
				aria-modal="true"
				aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
				style="display:none;"
			>
				<?php if ( $overlay ) : ?>
					<div class="frbl-popup-overlay"></div>
				<?php endif; ?>
				<div class="frbl-popup <?php echo esc_attr( $animation_class ); ?>" style="max-width:<?php echo esc_attr( $max_width ); ?>px; background-color:<?php echo $bg_color; ?>;">
					<?php if ( $show_close_button ) : ?>
						<button class="frbl-popup-close" style="color:<?php echo $close_color; ?>;" aria-label="<?php esc_attr_e( 'Close popup', 'frontblocks' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
						</button>
					<?php endif; ?>
					<div class="frbl-popup-content">
						<?php echo wp_kses_post( $content ); ?>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
