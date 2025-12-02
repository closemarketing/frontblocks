<?php
/**
 * Testimonials Block for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Blocks;

defined( 'ABSPATH' ) || exit;

/**
 * Testimonials Block class.
 *
 * @since 1.0.0
 */
class Testimonials_Block {
	/**
	 * Option key for testimonials feature.
	 *
	 * @var string
	 */
	private $option_enable_testimonials = 'enable_testimonials';

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! $this->is_testimonials_enabled() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_styles' ) );
	}

	/**
	 * Check if testimonials are enabled.
	 *
	 * @return bool
	 */
	private function is_testimonials_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options[ $this->option_enable_testimonials ] ?? false );
	}

	/**
	 * Register the block.
	 *
	 * @return void
	 */
	public function register_block() {
		register_block_type(
			'frontblocks/testimonials',
			array(
				'attributes'      => array(
					'numberOfTestimonials' => array(
						'type'    => 'number',
						'default' => -1,
					),
					'itemsToView'          => array(
						'type'    => 'number',
						'default' => 3,
					),
					'itemsToViewLaptop'    => array(
						'type'    => 'number',
						'default' => 2,
					),
					'itemsToViewTablet'    => array(
						'type'    => 'number',
						'default' => 2,
					),
					'itemsToViewMobile'    => array(
						'type'    => 'number',
						'default' => 1,
					),
					'autoplay'             => array(
						'type'    => 'number',
						'default' => 6000,
					),
					'navigationStyle'      => array(
						'type'    => 'string',
						'default' => 'bullets',
					),
					'showStars'            => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showImage'            => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'buttonColor'          => array(
						'type'    => 'string',
						'default' => '#000000',
					),
					'buttonBgColor'        => array(
						'type'    => 'string',
						'default' => 'transparent',
					),
				),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Register frontend styles.
	 *
	 * @return void
	 */
	public function register_frontend_styles() {
		// Register testimonials CSS for frontend.
		wp_register_style(
			'frontblocks-testimonials-style',
			FRBL_PLUGIN_URL . 'assets/testimonials/frontblocks-testimonials.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		// Enqueue Glide.js library.
		wp_enqueue_script(
			'frontblocks-carousel',
			FRBL_PLUGIN_URL . 'assets/carousel/glide.min.js',
			array(),
			FRBL_VERSION,
			true
		);

		// Enqueue Glide CSS.
		wp_enqueue_style(
			'frontblocks-carousel-css',
			FRBL_PLUGIN_URL . 'assets/carousel/frontblocks-carousel.css',
			array(),
			FRBL_VERSION
		);

		// Enqueue testimonials CSS for editor.
		wp_enqueue_style(
			'frontblocks-testimonials-style',
			FRBL_PLUGIN_URL . 'assets/testimonials/frontblocks-testimonials.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-testimonials-block',
			FRBL_PLUGIN_URL . 'assets/testimonials-block/frontblocks-testimonials-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-block-editor', 'wp-i18n', 'frontblocks-carousel' ),
			FRBL_VERSION,
			true
		);

		wp_enqueue_style(
			'frontblocks-testimonials-block-editor',
			FRBL_PLUGIN_URL . 'assets/testimonials-block/frontblocks-testimonials-block-editor.css',
			array( 'frontblocks-carousel-css' ),
			FRBL_VERSION
		);

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-testimonials-block',
			'frontblocks'
		);
	}

	/**
	 * Render the block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render_block( $attributes ) {
		$number_of_testimonials = isset( $attributes['numberOfTestimonials'] ) ? (int) $attributes['numberOfTestimonials'] : -1;
		$items_to_view          = isset( $attributes['itemsToView'] ) ? (int) $attributes['itemsToView'] : 3;
		$items_to_view_laptop   = isset( $attributes['itemsToViewLaptop'] ) ? (int) $attributes['itemsToViewLaptop'] : 2;
		$items_to_view_tablet   = isset( $attributes['itemsToViewTablet'] ) ? (int) $attributes['itemsToViewTablet'] : 2;
		$items_to_view_mobile   = isset( $attributes['itemsToViewMobile'] ) ? (int) $attributes['itemsToViewMobile'] : 1;
		$autoplay               = isset( $attributes['autoplay'] ) ? (int) $attributes['autoplay'] : 6000;
		$navigation_style       = isset( $attributes['navigationStyle'] ) ? sanitize_text_field( $attributes['navigationStyle'] ) : 'bullets';
		$show_stars             = isset( $attributes['showStars'] ) ? (bool) $attributes['showStars'] : true;
		$show_image             = isset( $attributes['showImage'] ) ? (bool) $attributes['showImage'] : true;
		$button_color           = isset( $attributes['buttonColor'] ) ? sanitize_hex_color( $attributes['buttonColor'] ) : '#000000';
		$button_bg_color        = isset( $attributes['buttonBgColor'] ) ? sanitize_text_field( $attributes['buttonBgColor'] ) : 'transparent';

		// Query testimonials.
		$args = array(
			'post_type'      => 'fbrl_testimonial',
			'posts_per_page' => $number_of_testimonials,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query_testimonios = new \WP_Query( $args );

		if ( ! $query_testimonios->have_posts() ) {
			return '<div class="frontblocks-testimonials-empty">' . esc_html__( 'No testimonials found.', 'frontblocks' ) . '</div>';
		}

		// Enqueue frontend styles.
		wp_enqueue_style( 'frontblocks-testimonials-style' );

		ob_start();
		?>
		<div class="frontblocks-carousel frontblocks-testimonials-carousel frontblocks-testimonials-block" 
			data-type="carousel" 
			data-view="<?php echo esc_attr( $items_to_view ); ?>" 
			data-laptop-view="<?php echo esc_attr( $items_to_view_laptop ); ?>" 
			data-tablet-view="<?php echo esc_attr( $items_to_view_tablet ); ?>" 
			data-mobile-view="<?php echo esc_attr( $items_to_view_mobile ); ?>" 
			data-buttons="<?php echo esc_attr( $navigation_style ); ?>" 
			data-autoplay="<?php echo esc_attr( $autoplay ); ?>"
			data-buttons-color="<?php echo esc_attr( $button_color ); ?>"
			data-buttons-background-color="<?php echo esc_attr( $button_bg_color ); ?>">
			<?php
			while ( $query_testimonios->have_posts() ) :
				$query_testimonios->the_post();
				$nombre       = get_the_title();
				$texto_resena = get_the_content();
				$imagen_url   = get_the_post_thumbnail_url( get_the_ID(), 'full' );

				$stars = get_post_meta( get_the_ID(), 'frontblocks_stars', true );

				$stars      = $stars ? intval( $stars ) : 0;
				$stars_html = '';

				if ( $show_stars ) {
					for ( $i = 1; $i <= 5; $i++ ) {
						if ( $i <= $stars ) {
							$stars_html .= '<span class="star filled">★</span>';
						} else {
							$stars_html .= '<span class="star">★</span>';
						}
					}
				}
				?>
				<div class="testimonial-card">
					<div class="testimonial-header">
						<?php if ( $show_image && $imagen_url ) : ?>
							<div class="testimonial-image-container">
								<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $nombre ); ?>" class="testimonial-image" />
							</div>
						<?php endif; ?>
					</div>
					<div class="testimonial-content">
						<h3 class="testimonial-name"><?php echo esc_html( $nombre ); ?></h3>
						<p class="testimonial-text">"<?php echo esc_html( $texto_resena ); ?>"</p>
						<?php if ( $show_stars ) : ?>
							<div class="stars-container">
								<?php echo $stars_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			endwhile;
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}
}

