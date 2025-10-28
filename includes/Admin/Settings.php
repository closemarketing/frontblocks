<?php
/**
 * Settings page
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class
 */
class Settings {

	/**
	 * Option key for testimonials feature.
	 *
	 * @var string
	 */
	private $option_enable_testimonials = 'enable_testimonials';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'frontblocks-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register options page under Appearance.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_theme_page(
			__( 'Frontblocks Settings', 'frontblocks' ),
			__( 'Frontblocks', 'frontblocks' ),
			'edit_theme_options',
			$this->page_slug,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings, sections and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'frontblocks_settings',
			'frontblocks_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'frontblocks_section_features',
			__( 'Features', 'frontblocks' ),
			function () {
				echo '<p>' . esc_html__( 'Enable or disable Frontblocks features.', 'frontblocks' ) . '</p>';
			},
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_testimonials,
			__( 'Enable testimonials', 'frontblocks' ),
			array( $this, 'field_enable_testimonials' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		if ( ! frbl_is_pro_active() ) {
			add_settings_section(
				'frontblocks_section_woocommerce_features',
				__( 'WooCommerce Features', 'frontblocks' ),
				function () {
					echo '<p>' . esc_html__( 'WooCommerce FrontBlocks PRO is a premium plugin that adds more features to WooCommerce FrontBlocks. Customize your WooCommerce store with more features.', 'frontblocks' ) . '</p>';
					echo '<p><a href="https://close.technology/wordpress-plugins/frontblocks-pro/?ref=WordPressPlugin" target="_blank" class="button button-secondary">' . esc_html__( 'Buy WooCommerce FrontBlocks PRO', 'frontblocks' ) . '</a></p>';
				},
				$this->page_slug
			);
		}

		do_action( 'frontblocks_register_settings' );
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Frontblocks Settings', 'frontblocks' ); ?></h1>

			<form method="post" action="options.php" style="margin-top:20px;">
				<?php
				settings_fields( 'frontblocks_settings' );
				do_settings_sections( $this->page_slug );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render checkbox field for enable testimonials.
	 *
	 * @return void
	 */
	public function field_enable_testimonials() {
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = (bool) ( $options[ $this->option_enable_testimonials ] ?? false );
		echo '<label for="' . esc_attr( $this->option_enable_testimonials ) . '">';
		echo '<input type="checkbox" id="' . esc_attr( $this->option_enable_testimonials ) . '" name="frontblocks_settings[' . esc_attr( $this->option_enable_testimonials ) . ']" value="1" ' . checked( true, $enabled, false ) . ' /> ';
		echo esc_html__( 'Enable testimonials', 'frontblocks' );
		echo '</label>';
	}

	/**
	 * Sanitize settings array.
	 *
	 * @param array $value Raw value.
	 * @return array
	 */
	public function sanitize_settings( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $value as $key => $val ) {
			if ( $this->option_enable_testimonials === $key ) {
				$sanitized[ $key ] = (bool) $val;
			} else {
				$sanitized[ $key ] = sanitize_text_field( $val );
			}
		}

		return $sanitized;
	}
}
