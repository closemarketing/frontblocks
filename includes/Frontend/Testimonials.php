<?php
/**
 * Testimonials module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Testimonials class.
 *
 * @since 1.0.0
 */
class Testimonials {
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
		if ( ! $this->is_testimonials_enabled() ) {
				return;
		}

		// Frontend hooks.
		add_action( 'init', array( $this, 'register_cpt_testimonials' ) );
		add_action( 'init', array( $this, 'register_meta_fields' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metabox_stars' ) );
		add_action( 'save_post', array( $this, 'save_metabox_stars' ) );
		add_shortcode( 'frontblocks_testimonials_carousel', array( $this, 'testimonials_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'init', array( $this, 'register_testimonials_block' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
			wp_register_style(
				'frontblocks-testimonials-style',
				FRBL_PLUGIN_URL . 'assets/testimonials/frontblocks-testimonials.css',
				array(),
				FRBL_VERSION
			);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'frontblocks-testimonials-block',
			FRBL_PLUGIN_URL . 'assets/testimonials/frontblocks-testimonials-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-block-editor', 'wp-i18n', 'wp-server-side-render' ),
			FRBL_VERSION,
			true
		);

		wp_enqueue_style(
			'frontblocks-testimonials-editor',
			FRBL_PLUGIN_URL . 'assets/testimonials/frontblocks-testimonials.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Register the Testimonials Carousel block.
	 *
	 * @return void
	 */
	public function register_testimonials_block() {
		register_block_type(
			'frontblocks/testimonials-carousel',
			array(
				'editor_script'   => 'frontblocks-testimonials-block',
				'render_callback' => array( $this, 'render_testimonials_block' ),
				'attributes'      => array(
					'postsCount'      => array(
						'type'    => 'number',
						'default' => -1,
					),
					'orderBy'         => array(
						'type'    => 'string',
						'default' => 'date',
					),
					'order'           => array(
						'type'    => 'string',
						'default' => 'DESC',
					),
					'layoutType'      => array(
						'type'    => 'string',
						'default' => 'carousel',
					),
					'imagePosition'   => array(
						'type'    => 'string',
						'default' => 'top',
					),
					'showStars'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showImage'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'slidesPerView'   => array(
						'type'    => 'number',
						'default' => 3,
					),
					'autoplay'        => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'autoplayDelay'   => array(
						'type'    => 'number',
						'default' => 6000,
					),
					'showNavigation'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showPagination'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'starsPosition'   => array(
						'type'    => 'string',
						'default' => 'below-text',
					),
				'contentOrder'    => array(
					'type'    => 'string',
					'default' => 'image-name-text-stars',
				),
				'nameAlign'       => array(
					'type'    => 'string',
					'default' => 'center',
				),
				'textAlign'       => array(
					'type'    => 'string',
					'default' => 'center',
				),
				'starsAlign'      => array(
					'type'    => 'string',
					'default' => 'center',
				),
				'imageAlign'      => array(
					'type'    => 'string',
					'default' => 'center',
				),
				// FrontBlocks animation attributes.
				'frblAnimation'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'frblAnimationDelay' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'frblAnimationDuration' => array(
					'type'    => 'number',
					'default' => 1,
				),
				'frblAnimationRepeat' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'frblAnimationInfinite' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				// GenerateBlocks condition attributes.
				'gbBlockCondition' => array(
					'type'    => 'string',
					'default' => '',
				),
				'gbBlockConditionInvert' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
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
	 * Register meta fields for REST API.
	 *
	 * @return void
	 */
	public function register_meta_fields() {
		register_post_meta(
			'fbrl_testimonial',
			'frontblocks_stars',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => 0,
			)
		);
		
		register_post_meta(
			'testimonios',
			'_estrellas_valor',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => 0,
			)
		);
	}

	/**
	 * Register testimonials custom post type.
	 *
	 * @return void
	 */
	public function register_cpt_testimonials() {
		$labels = array(
			'name'          => _x( 'Testimonials', 'Post Type General Name', 'frontblocks' ),
			'singular_name' => _x( 'Testimonial', 'Post Type Singular Name', 'frontblocks' ),
			'menu_name'     => __( 'Testimonials', 'frontblocks' ),
			'all_items'     => __( 'All Testimonials', 'frontblocks' ),
			'add_new_item'  => __( 'Add New Testimonial', 'frontblocks' ),
			'add_new'       => __( 'Add New', 'frontblocks' ),
			'new_item'      => __( 'New Testimonial', 'frontblocks' ),
			'edit_item'     => __( 'Edit Testimonial', 'frontblocks' ),
			'update_item'   => __( 'Update Testimonial', 'frontblocks' ),
			'view_item'     => __( 'View Testimonial', 'frontblocks' ),
			'view_items'    => __( 'View Testimonials', 'frontblocks' ),
			'search_items'  => __( 'Search Testimonial', 'frontblocks' ),
		);

		$args = array(
			'label'         => __( 'Testimonials', 'frontblocks' ),
			'labels'        => $labels,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'public'        => true,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-testimonial',
			'can_export'    => true,
			'show_in_rest'  => true,
		);

		register_post_type( 'fbrl_testimonial', $args );
	}

	/**
	 * Add metabox for star rating.
	 *
	 * @return void
	 */
	public function add_metabox_stars() {
		add_meta_box(
			'testimonial_rating_metabox',
			__( 'Rating (Stars)', 'frontblocks' ),
			array( $this, 'render_metabox_stars' ),
			'fbrl_testimonial',
			'side',
			'high'
		);
	}

	/**
	 * Render metabox content.
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function render_metabox_stars( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'frontblocks_testimonials_nonce' );
		$stars = get_post_meta( $post->ID, 'frontblocks_stars', true );
		?>
		<p>
			<label for="frontblocks_stars"><?php esc_html_e( 'Select rating:', 'frontblocks' ); ?></label>
			<select name="frontblocks_stars" id="frontblocks_stars">
				<option value="0" <?php selected( $stars, 0 ); ?>><?php esc_html_e( '0 Stars', 'frontblocks' ); ?></option>
				<option value="1" <?php selected( $stars, 1 ); ?>><?php esc_html_e( '1 Star', 'frontblocks' ); ?></option>
				<option value="2" <?php selected( $stars, 2 ); ?>><?php esc_html_e( '2 Stars', 'frontblocks' ); ?></option>
				<option value="3" <?php selected( $stars, 3 ); ?>><?php esc_html_e( '3 Stars', 'frontblocks' ); ?></option>
				<option value="4" <?php selected( $stars, 4 ); ?>><?php esc_html_e( '4 Stars', 'frontblocks' ); ?></option>
				<option value="5" <?php selected( $stars, 5 ); ?>><?php esc_html_e( '5 Stars', 'frontblocks' ); ?></option>
			</select>
		</p>
		<?php
	}

	/**
	 * Save metabox data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_metabox_stars( $post_id ) {
		if ( ! isset( $_POST['frontblocks_testimonials_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['frontblocks_testimonials_nonce'] ) ), basename( __FILE__ ) ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['frontblocks_stars'] ) ) {
			update_post_meta( $post_id, 'frontblocks_stars', (int) $_POST['frontblocks_stars'] );
		}
	}

	/**
	 * Testimonials carousel shortcode.
	 *
	 * @return string
	 */
	public function testimonials_shortcode() {
			wp_enqueue_style( 'frontblocks-testimonials-style' );
			$args = array(
				'post_type'      => 'fbrl_testimonial',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			);

			$query_testimonios = new \WP_Query( $args );
			ob_start();

			if ( ! $query_testimonios->have_posts() ) {
				return '';
			}
			?>
			<div class="frontblocks-carousel frontblocks-testimonials-carousel" data-type="carousel" data-view="4" data-res-view="1" data-buttons="bullets" data-autoplay="6000">
				<?php
				while ( $query_testimonios->have_posts() ) :
						$query_testimonios->the_post();
						$nombre       = get_the_title();
						$texto_resena = get_the_content();
						$imagen_url   = get_the_post_thumbnail_url( get_the_ID(), 'full' );

						$stars = get_post_meta( get_the_ID(), 'frontblocks_stars', true );

					$stars      = $stars ? intval( $stars ) : 0;
					$stars_html = '';

				for ( $i = 1; $i <= 5; $i++ ) {
					if ( $i <= $stars ) {
							$stars_html .= '<span class="star filled">★</span>';
					} else {
							$stars_html .= '<span class="star">☆</span>';
					}
				}
					?>
								<div class="testimonial-card">
												<div class="testimonial-header">
												<?php if ( $imagen_url ) : ?>
																<div class="testimonial-image-container">
																		<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $nombre ); ?>" class="testimonial-image" />
														</div>
												<?php endif; ?>
										</div>
										<div class="testimonial-content">
												<h3 class="testimonial-name"><?php echo esc_html( $nombre ); ?></h3>
												<p class="testimonial-text">"<?php echo esc_html( $texto_resena ); ?>"</p>
												<div class="stars-container">
														<?php echo $stars_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</div>
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

	/**
	 * Render testimonials block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render_testimonials_block( $attributes ) {
		wp_enqueue_style( 'frontblocks-testimonials-style' );

		// Extract attributes with defaults.
		$posts_count     = isset( $attributes['postsCount'] ) ? (int) $attributes['postsCount'] : -1;
		$order_by        = isset( $attributes['orderBy'] ) ? sanitize_text_field( $attributes['orderBy'] ) : 'date';
		$order           = isset( $attributes['order'] ) ? sanitize_text_field( $attributes['order'] ) : 'DESC';
		$layout_type     = isset( $attributes['layoutType'] ) ? sanitize_text_field( $attributes['layoutType'] ) : 'carousel';
		$image_position  = isset( $attributes['imagePosition'] ) ? sanitize_text_field( $attributes['imagePosition'] ) : 'top';
		$show_stars      = isset( $attributes['showStars'] ) ? (bool) $attributes['showStars'] : true;
		$show_image      = isset( $attributes['showImage'] ) ? (bool) $attributes['showImage'] : true;
		$slides_per_view = isset( $attributes['slidesPerView'] ) ? (int) $attributes['slidesPerView'] : 3;
		$autoplay        = isset( $attributes['autoplay'] ) ? (bool) $attributes['autoplay'] : true;
		$autoplay_delay  = isset( $attributes['autoplayDelay'] ) ? (int) $attributes['autoplayDelay'] : 6000;
		$show_navigation = isset( $attributes['showNavigation'] ) ? (bool) $attributes['showNavigation'] : true;
		$show_pagination = isset( $attributes['showPagination'] ) ? (bool) $attributes['showPagination'] : true;
		$stars_position  = isset( $attributes['starsPosition'] ) ? sanitize_text_field( $attributes['starsPosition'] ) : 'below-text';
		$content_order   = isset( $attributes['contentOrder'] ) ? sanitize_text_field( $attributes['contentOrder'] ) : 'image-name-text-stars';
		$name_align      = isset( $attributes['nameAlign'] ) ? sanitize_text_field( $attributes['nameAlign'] ) : 'center';
		$text_align      = isset( $attributes['textAlign'] ) ? sanitize_text_field( $attributes['textAlign'] ) : 'center';
		$stars_align     = isset( $attributes['starsAlign'] ) ? sanitize_text_field( $attributes['starsAlign'] ) : 'center';
		$image_align     = isset( $attributes['imageAlign'] ) ? sanitize_text_field( $attributes['imageAlign'] ) : 'center';

		// Query testimonials - try both CPTs.
		$post_types = array( 'testimonios', 'fbrl_testimonial' );
		
		$args = array(
			'post_type'      => $post_types,
			'posts_per_page' => $posts_count,
			'orderby'        => $order_by,
			'order'          => $order,
			'post_status'    => 'publish',
		);

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		ob_start();

		// Build carousel data attributes.
		$carousel_attrs = '';
		if ( 'carousel' === $layout_type ) {
			$carousel_attrs = sprintf(
				'data-type="carousel" data-view="%d" data-res-view="1" data-buttons="%s" data-autoplay="%s"',
				esc_attr( $slides_per_view ),
				$show_pagination ? 'bullets' : 'false',
				$autoplay ? esc_attr( $autoplay_delay ) : 'false'
			);
		}

		$wrapper_class = 'frontblocks-testimonials-block';
		$wrapper_class .= ' layout-' . esc_attr( $layout_type );
		$wrapper_class .= ' image-' . esc_attr( $image_position );
		$wrapper_class .= ' content-order-' . esc_attr( $content_order );
		$wrapper_class .= ' name-align-' . esc_attr( $name_align );
		$wrapper_class .= ' text-align-' . esc_attr( $text_align );
		$wrapper_class .= ' stars-align-' . esc_attr( $stars_align );
		$wrapper_class .= ' image-align-' . esc_attr( $image_align );

		if ( 'carousel' === $layout_type ) {
			$wrapper_class .= ' frontblocks-carousel';
		}
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>" <?php echo $carousel_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$nombre       = get_the_title();
				$texto_resena = get_the_content();
				$imagen_url   = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
				
				// Try both meta fields for star rating.
				$stars_value = get_post_meta( get_the_ID(), '_estrellas_valor', true );
				if ( ! $stars_value ) {
					$stars_value = get_post_meta( get_the_ID(), 'frontblocks_stars', true );
				}
				$stars_value = $stars_value ? intval( $stars_value ) : 0;

				// Generate stars HTML.
				$stars_html = '';
				if ( $show_stars ) {
					$stars_html .= '<div class="testimonial-stars stars-' . esc_attr( $stars_position ) . '">';
					for ( $i = 1; $i <= 5; $i++ ) {
						if ( $i <= $stars_value ) {
							$stars_html .= '<span class="star filled">★</span>';
						} else {
							$stars_html .= '<span class="star">☆</span>';
						}
					}
					$stars_html .= '</div>';
				}

				// Generate image HTML.
				$image_html = '';
				if ( $show_image && $imagen_url ) {
					$image_html = sprintf(
						'<div class="testimonial-image"><img src="%s" alt="%s" /></div>',
						esc_url( $imagen_url ),
						esc_attr( $nombre )
					);
				}

				// Generate content parts.
				$name_html = '<h3 class="testimonial-name">' . esc_html( $nombre ) . '</h3>';
				$text_html = '<div class="testimonial-text">"' . esc_html( $texto_resena ) . '"</div>';

				// Build content based on order.
				$content_parts = array(
					'image' => $image_html,
					'name'  => $name_html,
					'text'  => $text_html,
					'stars' => $stars_html,
				);

				// Parse content order.
				$order_array = explode( '-', $content_order );
				?>
				<div class="testimonial-card">
					<div class="testimonial-content">
						<?php
						foreach ( $order_array as $part ) {
							if ( isset( $content_parts[ $part ] ) ) {
								echo $content_parts[ $part ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
						}
						?>
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
