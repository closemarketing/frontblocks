<?php
/**
 * Insert Post module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * InsertPost class.
 *
 * @since 1.0.0
 */
class InsertPost {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_ajax_frbl_search_posts', array( $this, 'search_posts_callback' ) );
		add_action( 'wp_ajax_nopriv_frbl_search_posts', array( $this, 'search_posts_callback' ) );
		add_action( 'init', array( $this, 'register_insert_post_block' ) );
		add_filter( 'render_block_generateblocks/grid', array( $this, 'add_insert_post_attributes_to_grid_block' ), 10, 2 );
		add_action( 'init', array( $this, 'register_custom_attributes' ), 5 );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'frontblocks-insert-post',
			FRBL_PLUGIN_URL . 'assets/insert-post/frontblocks-insert-post.css',
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
		// Enqueue jQuery UI for autocomplete.
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_style( 'jquery-ui', FRBL_PLUGIN_URL . 'assets/insert-post/jquery-ui.css', array(), '1.13.2' );

		wp_enqueue_script(
			'frontblocks-insert-post-option',
			FRBL_PLUGIN_URL . 'assets/insert-post/frontblocks-insert-post-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-api-fetch', 'jquery', 'jquery-ui-autocomplete' ),
			FRBL_VERSION,
			true
		);

		// Localize script with AJAX URL and nonce.
		wp_localize_script(
			'frontblocks-insert-post-option',
			'frblInsertPost',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'frbl_insert_post_nonce' ),
			)
		);
	}

	/**
	 * AJAX callback for searching posts.
	 *
	 * @return void
	 */
	public function search_posts_callback() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'frbl_insert_post_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$search_term = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
		$post_type   = sanitize_text_field( wp_unslash( $_POST['post_type'] ?? 'post' ) );

		if ( empty( $search_term ) ) {
			wp_send_json_error( 'Search term is required' );
		}

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			's'              => $search_term,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$query = new \WP_Query( $args );
		$posts = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$posts[] = array(
					'id'    => get_the_ID(),
					'title' => get_the_title(),
					'type'  => get_post_type(),
				);
			}
			wp_reset_postdata();
		}

		wp_send_json_success( $posts );
	}

	/**
	 * Register the Insert Post block.
	 *
	 * @return void
	 */
	public function register_insert_post_block() {
		register_block_type(
			'frontblocks/insert-post',
			array(
				'editor_script'   => 'frontblocks-insert-post-option',
				'render_callback' => array( $this, 'render_insert_post_block' ),
				'attributes'      => array(
					'selectedPostId'      => array(
						'type'    => 'number',
						'default' => 0,
					),
					'selectedPostType'    => array(
						'type'    => 'string',
						'default' => 'post',
					),
					'selectedPostTitle'   => array(
						'type'    => 'string',
						'default' => '',
					),
					'selectedPostContent' => array(
						'type'    => 'string',
						'default' => '',
					),
					'className'           => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * Render the Insert Post block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_insert_post_block( $attributes ) {
		$post_id = $attributes['selectedPostId'] ?? 0;

		if ( ! $post_id ) {
			return '<div class="frbl-insert-post-empty">' . __( 'No post selected', 'frontblocks' ) . '</div>';
		}

		$post = get_post( $post_id );

		if ( ! $post || 'publish' !== $post->post_status ) {
			return '<div class="frbl-insert-post-error">' . __( 'Selected post not found or not published', 'frontblocks' ) . '</div>';
		}

		$title   = get_the_title( $post_id );
		$content = $post->post_content;

		if ( empty( $content ) ) {
			return '';
		}

		$wrapper_class = 'frbl-insert-post';
		if ( ! empty( $attributes['className'] ) ) {
			$wrapper_class .= ' ' . esc_attr( $attributes['className'] );
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h2 class="frbl-insert-post-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			
			<div class="frbl-insert-post-content">
				<?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add insert post attributes to grid block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_insert_post_attributes_to_grid_block( $block_content, $block ) {
		$attrs               = $block['attrs'] ?? array();
		$insert_post_enabled = isset( $attrs['frblInsertPostEnabled'] ) ? (bool) $attrs['frblInsertPostEnabled'] : false;

		if ( $insert_post_enabled ) {
			// Add data attribute to indicate insert post functionality.
			$block_content = preg_replace(
				'/<div([^>]*)class="([^"]*gb-grid-wrapper[^"]*)"([^>]*)>/',
				'<div$1class="$2 frbl-insert-post-grid"$3 data-insert-post="true">',
				$block_content,
				1
			);
		}

		return $block_content;
	}

	/**
	 * Register custom attributes for blocks.
	 *
	 * @return void
	 */
	public function register_custom_attributes() {
		add_filter(
			'generateblocks_blocks_registered_block',
			array( $this, 'register_insert_post_attributes_for_grid_block' ),
			9,
			2
		);

		add_action(
			'enqueue_block_editor_assets',
			array( $this, 'add_inline_script_for_attributes' )
		);
	}

	/**
	 * Register insert post attributes for GenerateBlocks Grid block.
	 *
	 * @param array  $block_args The block arguments.
	 * @param string $block_type The name of the block.
	 * @return array Modified block arguments.
	 */
	public function register_insert_post_attributes_for_grid_block( $block_args, $block_type ) {
		if ( 'generateblocks/grid' !== $block_type ) {
			return $block_args;
		}

		if ( ! isset( $block_args['attributes'] ) ) {
			$block_args['attributes'] = array();
		}

		$block_args['attributes']['frblInsertPostEnabled'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $block_args;
	}

	/**
	 * Add inline script for block attributes.
	 *
	 * @return void
	 */
	public function add_inline_script_for_attributes() {
		wp_add_inline_script(
			'wp-blocks',
			"
			wp.hooks.addFilter(
				'blocks.registerBlockType',
				'frontblocks/insert-post-grid-attributes',
				function( settings, name ) {
					if ( name !== 'generateblocks/grid' ) {
						return settings;
					}

					settings.attributes = {
						...settings.attributes,
						frblInsertPostEnabled: {
							type: 'boolean',
							default: false
						}
					};

					return settings;
				}
			);
			"
		);
	}
}
