<?php
/**
 * Reading Time module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Alex Castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

use WP_Block_Type_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * ReadingTime class.
 *
 * @since 1.0.0
 */
class ReadingTime {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_reading_time_block' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
		add_shortcode( 'frontblocks_reading_time', array( $this, 'reading_time_shortcode' ) );
	}

	/**
	 * Calculate reading time for a post or page.
	 *
	 * @param int|null $post_id Post ID. If null, uses current post.
	 * @return int Reading time in minutes.
	 */
	public function calculate_reading_time( $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return 0;
		}

		// Check if reading time is already stored in post meta.
		$reading_time = get_post_meta( $post_id, 'reading_time', true );

		if ( ! $reading_time ) {
			// Calculate reading time.
			$post_content = get_post_field( 'post_content', $post_id );
			$word_count   = str_word_count( wp_strip_all_tags( $post_content ) );

			// Average reading speed: 225 words per minute.
			$reading_time = ceil( $word_count / 225 );

			// Minimum 1 minute.
			$reading_time = max( 1, $reading_time );
		}

		return (int) $reading_time;
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		wp_register_style(
			'frontblocks-reading-time-style',
			FRBL_PLUGIN_URL . 'assets/reading-time/frontblocks-reading-time.css',
			array(),
			FRBL_VERSION
		);

		if ( is_admin() || has_block( 'frontblocks/reading-time' ) ) {
			wp_enqueue_style( 'frontblocks-reading-time-style' );
		}
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		// Enqueue styles for editor.
		wp_enqueue_style(
			'frontblocks-reading-time-style',
			FRBL_PLUGIN_URL . 'assets/reading-time/frontblocks-reading-time.css',
			array(),
			FRBL_VERSION
		);

		// Register React if not already registered.
		if ( ! wp_script_is( 'react', 'registered' ) ) {
			wp_register_script(
				'react',
				'https://unpkg.com/react@18/umd/react.production.min.js',
				array(),
				'18.0.0',
				true
			);
		}

		if ( ! wp_script_is( 'react-dom', 'registered' ) ) {
			wp_register_script(
				'react-dom',
				'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js',
				array( 'react' ),
				'18.0.0',
				true
			);
		}

		wp_enqueue_script(
			'frontblocks-reading-time-option',
			FRBL_PLUGIN_URL . 'assets/reading-time/frontblocks-reading-time.js',
			array( 'react', 'react-dom', 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-editor', 'wp-block-editor', 'wp-compose', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-reading-time-option',
			'frblReadingTime',
			array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Register the Reading Time block.
	 *
	 * @return void
	 */
	public function register_reading_time_block() {
		$args = array(
			'editor_script'   => 'frontblocks-reading-time-option',
			'render_callback' => array( $this, 'render_reading_time_block' ),
			'attributes'      => array(
				'postId'          => array(
					'type'    => 'number',
					'default' => 0,
				),
				'showIcon'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'prefix'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'suffix'          => array(
					'type'    => 'string',
					'default' => 'min',
				),
				'className'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'textColor'       => array(
					'type'    => 'string',
					'default' => 'inherit',
				),
				'backgroundColor' => array(
					'type'    => 'string',
					'default' => 'transparent',
				),
				'fontSize'        => array(
					'type'    => 'number',
					'default' => 16,
				),
				'iconColor'       => array(
					'type'    => 'string',
					'default' => 'currentColor',
				),
				'alignment'       => array(
					'type'    => 'string',
					'default' => 'left',
				),
				'padding'         => array(
					'type'    => 'number',
					'default' => 10,
				),
				'borderRadius'    => array(
					'type'    => 'number',
					'default' => 5,
				),
			),
		);

		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'frontblocks/reading-time' ) ) {
			register_block_type(
				'frontblocks/reading-time',
				$args
			);
		}
	}

	/**
	 * Render the Reading Time block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_reading_time_block( $attributes ) {
		$post_id = absint( $attributes['postId'] ?? 0 );
		
		// If no post ID specified, use current post.
		if ( 0 === $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return '';
		}

		$reading_time = $this->calculate_reading_time( $post_id );

		$show_icon     = $attributes['showIcon'] ?? true;
		$prefix        = sanitize_text_field( $attributes['prefix'] ?? '' );
		$suffix        = sanitize_text_field( $attributes['suffix'] ?? 'min' );
		$text_color    = sanitize_text_field( $attributes['textColor'] ?? 'inherit' );
		$bg_color      = sanitize_text_field( $attributes['backgroundColor'] ?? 'transparent' );
		$font_size     = absint( $attributes['fontSize'] ?? 16 );
		$icon_color    = sanitize_text_field( $attributes['iconColor'] ?? 'currentColor' );
		$alignment     = sanitize_text_field( $attributes['alignment'] ?? 'left' );
		$padding       = absint( $attributes['padding'] ?? 10 );
		$border_radius = absint( $attributes['borderRadius'] ?? 5 );

		$wrapper_class = 'frbl-reading-time-wrapper align-' . esc_attr( $alignment );
		if ( ! empty( $attributes['className'] ) ) {
			$wrapper_class .= ' ' . esc_attr( $attributes['className'] );
		}

		$style_vars = sprintf(
			'--frbl-text-color: %s; --frbl-bg-color: %s; --frbl-font-size: %dpx; --frbl-icon-color: %s; --frbl-padding: %dpx; --frbl-border-radius: %dpx;',
			$text_color,
			$bg_color,
			$font_size,
			$icon_color,
			$padding,
			$border_radius
		);

		$icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';

		ob_start();
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<div class="frbl-reading-time" style="<?php echo esc_attr( $style_vars ); ?>">
				<?php if ( $show_icon ) : ?>
					<span class="frbl-reading-time-icon"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
				<span class="frbl-reading-time-text">
					<?php
					if ( $prefix ) {
						echo esc_html( $prefix ) . ' ';
					}
					echo esc_html( $reading_time );
					if ( $suffix ) {
						echo ' ' . esc_html( $suffix );
					}
					?>
				</span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Shortcode for reading time.
	 * Usage: [frontblocks_reading_time] or [frontblocks_reading_time post_id="123" prefix="This blog takes" suffix="minutes to read"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function reading_time_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'post_id'       => 0,
				'show_icon'     => 'yes',
				'prefix'        => '',
				'suffix'        => 'min',
				'text_color'    => 'inherit',
				'bg_color'      => 'transparent',
				'font_size'     => 16,
				'icon_color'    => 'currentColor',
				'alignment'     => 'left',
				'padding'       => 10,
				'border_radius' => 5,
			),
			$atts,
			'frontblocks_reading_time'
		);

		$attributes = array(
			'postId'          => absint( $atts['post_id'] ),
			'showIcon'        => 'yes' === strtolower( $atts['show_icon'] ),
			'prefix'          => $atts['prefix'],
			'suffix'          => $atts['suffix'],
			'textColor'       => $atts['text_color'],
			'backgroundColor' => $atts['bg_color'],
			'fontSize'        => absint( $atts['font_size'] ),
			'iconColor'       => $atts['icon_color'],
			'alignment'       => $atts['alignment'],
			'padding'         => absint( $atts['padding'] ),
			'borderRadius'    => absint( $atts['border_radius'] ),
		);

		return $this->render_reading_time_block( $attributes );
	}
}

new ReadingTime();

