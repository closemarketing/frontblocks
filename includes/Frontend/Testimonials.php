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
		add_action( 'add_meta_boxes', array( $this, 'add_metabox_stars' ) );
		add_action( 'save_post', array( $this, 'save_metabox_stars' ) );
		add_shortcode( 'frontblocks_testimonials_carousel', array( $this, 'testimonials_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
								$stars_html .= '<span class="star">★</span>';
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
}