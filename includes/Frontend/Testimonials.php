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
		add_action( 'add_meta_boxes', array( $this, 'add_metabox_estrellas' ) );
		add_action( 'save_post', array( $this, 'save_metabox_estrellas' ) );
		add_shortcode( 'frontblocks_testimonials_carousel', array( $this, 'testimonials_shortcode' ) );
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
		);

		register_post_type( 'fbrl_testimonial', $args );
	}

	/**
	 * Add metabox for star rating.
	 *
	 * @return void
	 */
	public function add_metabox_estrellas() {
		add_meta_box(
			'testimonial_rating_metabox',
			__( 'Rating (Stars)', 'frontblocks' ),
			array( $this, 'render_metabox_estrellas' ),
			'testimonial',
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
	public function render_metabox_estrellas( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'frontblocks_testimonials_nonce' );
		$estrellas = get_post_meta( $post->ID, 'frontblocks_stars', true );
		?>
		<p>
			<label for="frontblocks_stars"><?php esc_html_e( 'Select rating:', 'frontblocks' ); ?></label>
			<select name="frontblocks_stars" id="frontblocks_stars">
				<option value="0" <?php selected( $estrellas, 0 ); ?>><?php esc_html_e( '0 Stars', 'frontblocks' ); ?></option>
				<option value="1" <?php selected( $estrellas, 1 ); ?>><?php esc_html_e( '1 Star', 'frontblocks' ); ?></option>
				<option value="2" <?php selected( $estrellas, 2 ); ?>><?php esc_html_e( '2 Stars', 'frontblocks' ); ?></option>
				<option value="3" <?php selected( $estrellas, 3 ); ?>><?php esc_html_e( '3 Stars', 'frontblocks' ); ?></option>
				<option value="4" <?php selected( $estrellas, 4 ); ?>><?php esc_html_e( '4 Stars', 'frontblocks' ); ?></option>
				<option value="5" <?php selected( $estrellas, 5 ); ?>><?php esc_html_e( '5 Stars', 'frontblocks' ); ?></option>
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
	public function save_metabox_estrellas( $post_id ) {
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
		// Enqueue Swiper assets.
		wp_enqueue_style( 'swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css' );
		wp_enqueue_script( 'swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), '11.0.5', true );

		$args = array(
			'post_type'      => 'testimonial',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query_testimonios = new \WP_Query( $args );
		ob_start();

		if ( $query_testimonios->have_posts() ) {
			?>
			<div class="swiper-container testimonials-carousel">
				<div class="swiper-wrapper">
					<?php
					while ( $query_testimonios->have_posts() ) :
						$query_testimonios->the_post();
						$nombre = get_the_title();
						$texto_resena = get_the_content();
						$imagen_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
						$stars = get_post_meta( get_the_ID(), '_stars', true );
						$stars = $stars ? intval( $stars ) : 0;
						$estrellas_html = '';

						for ( $i = 1; $i <= 5; $i++ ) {
							if ( $i <= $stars ) {
								$estrellas_html .= '<span class="star filled">★</span>';
							} else {
								$estrellas_html .= '<span class="star">★</span>';
							}
						}
						?>
							<div class="swiper-slide">
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
										<?php echo $estrellas_html; ?>
									</div>
								</div>
							</div>
						</div>
						<?php
					endwhile;
					?>
				</div>
				<div class="swiper-pagination"></div>
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>
			<script>
			document.addEventListener("DOMContentLoaded", function() {
				var swiper = new Swiper(".testimonials-carousel", {
					loop: true,
					navigation: {
						nextEl: ".swiper-button-next",
						prevEl: ".swiper-button-prev",
					},
					pagination: {
						el: ".swiper-pagination",
						clickable: true,
					},
					breakpoints: {
						320: {
							slidesPerView: 1,
							spaceBetween: 20
						},
						768: {
							slidesPerView: 2,
							spaceBetween: 30
						},
						1024: {
							slidesPerView: 4,
							spaceBetween: 40
						}
					}
				});
			});
			</script>
			<?php
		} else {
			echo '<p>' . esc_html__( 'No testimonials available.', 'frontblocks' ) . '</p>';
		}

		wp_reset_postdata();
		return ob_get_clean();
	}
}