<?php
/**
 * Stacked Images module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

use WP_Block_Type_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * StackedImages class.
 *
 * @since 1.0.0
 */
class StackedImages {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_stacked_images_block' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		wp_register_style(
			'frontblocks-stacked-images-style',
			FRBL_PLUGIN_URL . 'assets/stacked-images/frontblocks-stacked-images.css',
			array(),
			FRBL_VERSION
		);

		wp_register_script(
			'frontblocks-stacked-images-frontend',
			FRBL_PLUGIN_URL . 'assets/stacked-images/frontblocks-stacked-images-frontend.js',
			array(),
			FRBL_VERSION,
			true
		);

		if ( is_admin() || has_block( 'frontblocks/stacked-images' ) ) {
			wp_enqueue_style( 'frontblocks-stacked-images-style' );
			wp_enqueue_script( 'frontblocks-stacked-images-frontend' );
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
			'frontblocks-stacked-images-style',
			FRBL_PLUGIN_URL . 'assets/stacked-images/frontblocks-stacked-images.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-stacked-images-option',
			FRBL_PLUGIN_URL . 'assets/stacked-images/frontblocks-stacked-images.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-editor', 'wp-block-editor', 'wp-compose', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Register the Stacked Images block.
	 *
	 * @return void
	 */
	public function register_stacked_images_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$args = array(
			'editor_script'   => 'frontblocks-stacked-images-option',
			'render_callback' => array( $this, 'render_stacked_images_block' ),
			'attributes'      => array(
				'images'            => array(
					'type'    => 'array',
					'default' => array(),
				),
				'direction'         => array(
					'type'    => 'string',
					'default' => 'bottom',
				),
				'animationDuration' => array(
					'type'    => 'number',
					'default' => 1000,
				),
				'animationDelay'    => array(
					'type'    => 'number',
					'default' => 500,
				),
				'containerHeight'   => array(
					'type'    => 'number',
					'default' => 500,
				),
				'className'         => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		);

		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'frontblocks/stacked-images' ) ) {
			register_block_type(
				'frontblocks/stacked-images',
				$args
			);
		}
	}

	/**
	 * Render the Stacked Images block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_stacked_images_block( $attributes ) {
		$images             = $attributes['images'] ?? array();
		$direction          = sanitize_text_field( $attributes['direction'] ?? 'bottom' );
		$animation_duration = absint( $attributes['animationDuration'] ?? 1000 );
		$animation_delay    = absint( $attributes['animationDelay'] ?? 500 );
		$container_height   = absint( $attributes['containerHeight'] ?? 500 );

		if ( empty( $images ) ) {
			return '<div class="frbl-stacked-images-placeholder">' . esc_html__( 'Please add images to the Stacked Images block.', 'frontblocks' ) . '</div>';
		}

		$wrapper_class = 'frbl-stacked-images-wrapper';
		if ( ! empty( $attributes['className'] ) ) {
			$wrapper_class .= ' ' . esc_attr( $attributes['className'] );
		}

		ob_start();
		?>
		<div 
			class="<?php echo esc_attr( $wrapper_class ); ?>" 
			data-direction="<?php echo esc_attr( $direction ); ?>"
			data-duration="<?php echo esc_attr( $animation_duration ); ?>"
			data-delay="<?php echo esc_attr( $animation_delay ); ?>"
			style="height: <?php echo esc_attr( $container_height ); ?>px;"
		>
			<div class="frbl-stacked-images-container">
				<?php foreach ( $images as $index => $image ) : ?>
					<?php
					$image_url = isset( $image['url'] ) ? $image['url'] : '';
					$image_alt = isset( $image['alt'] ) ? $image['alt'] : '';
					$image_id  = isset( $image['id'] ) ? absint( $image['id'] ) : 0;
					?>
					<div 
						class="frbl-stacked-image" 
						data-index="<?php echo esc_attr( $index ); ?>"
						style="z-index: <?php echo esc_attr( $index + 1 ); ?>;"
					>
						<img 
							src="<?php echo esc_url( $image_url ); ?>" 
							alt="<?php echo esc_attr( $image_alt ); ?>"
							loading="lazy"
						/>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

new StackedImages();
