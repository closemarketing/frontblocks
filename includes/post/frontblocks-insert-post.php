<?php
/**
 * Class Insert Post Block
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'frbl_insert_post_scripts', 99 );
/**
 * Loads Scripts for Insert Post Block
 *
 * @return void
 */
function frbl_insert_post_scripts() {
	$dist_dir = WP_DEBUG ? 'post/' : 'dist/';
	wp_enqueue_style(
		'frontblocks-insert-post',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-insert-post.css',
		array(),
		FRBL_VERSION
	);
}

add_action( 'enqueue_block_editor_assets', 'frbl_enqueue_insert_post_editor_assets' );
/**
 * Enqueue insert post block editor script
 *
 * @return void
 */
function frbl_enqueue_insert_post_editor_assets() {
	// Enqueue jQuery UI for autocomplete
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css' );

	wp_enqueue_script(
		'frontblocks-insert-post-option',
		FRBL_PLUGIN_URL . 'includes/dist/frontblocks-insert-post-option.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-api-fetch', 'jquery', 'jquery-ui-autocomplete' ),
		FRBL_VERSION,
		true
	);

	// Localize script with AJAX URL and nonce
	wp_localize_script(
		'frontblocks-insert-post-option',
		'frblInsertPost',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'frbl_insert_post_nonce' ),
		)
	);
}

// AJAX handler for searching posts
add_action( 'wp_ajax_frbl_search_posts', 'frbl_search_posts_callback' );
add_action( 'wp_ajax_nopriv_frbl_search_posts', 'frbl_search_posts_callback' );

/**
 * AJAX callback for searching posts
 *
 * @return void
 */
function frbl_search_posts_callback() {
	// Verify nonce
	if ( ! wp_verify_nonce( $_POST['nonce'], 'frbl_insert_post_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	$search_term = sanitize_text_field( $_POST['search'] ?? '' );
	$post_type   = sanitize_text_field( $_POST['post_type'] ?? 'post' );

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

	$query = new WP_Query( $args );
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

// Register the custom block
add_action( 'init', 'frbl_register_insert_post_block' );

/**
 * Register the Insert Post block
 *
 * @return void
 */
function frbl_register_insert_post_block() {
	register_block_type(
		'frontblocks_insert_post',
		array(
			'editor_script' => 'frontblocks-insert-post-option',
			'render_callback' => 'frbl_render_insert_post_block',
			'attributes' => array(
				'selectedPostId' => array(
					'type' => 'number',
					'default' => 0,
				),
				'selectedPostType' => array(
					'type' => 'string',
					'default' => 'post',
				),
				'selectedPostTitle' => array(
					'type' => 'string',
					'default' => '',
				),
				'selectedPostContent' => array(
					'type' => 'string',
					'default' => '',
				),
				'className' => array(
					'type' => 'string',
					'default' => '',
				),
			),
		)
	);
}

/**
 * Render the Insert Post block on frontend
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function frbl_render_insert_post_block( $attributes ) {
	$post_id = $attributes['selectedPostId'] ?? 0;
	
	if ( ! $post_id ) {
		return '<div class="frbl-insert-post-empty">' . __( 'No post selected', 'frontblocks' ) . '</div>';
	}

	$post = get_post( $post_id );
	
	if ( ! $post || 'publish' !== $post->post_status ) {
		return '<div class="frbl-insert-post-error">' . __( 'Selected post not found or not published', 'frontblocks' ) . '</div>';
	}

	$title = get_the_title( $post_id );
	$content = apply_filters( 'the_content', $post->post_content );
	
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
		
		<?php if ( ! empty( $content ) ) : ?>
			<div class="frbl-insert-post-content">
				<?php echo wp_kses_post( $content ); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

// Add custom attributes to existing GenerateBlocks blocks
add_filter( 'render_block_generateblocks/grid', 'frbl_add_insert_post_attributes_to_grid_block', 10, 2 );

/**
 * Hook to filter the block output on frontend for insert post functionality
 *
 * @param string $block_content Block content.
 * @param array  $block Block attributes.
 * @return string Modified block content.
 */
function frbl_add_insert_post_attributes_to_grid_block( $block_content, $block ) {
	$attrs = $block['attrs'] ?? array();
	$insert_post_enabled = isset( $attrs['frblInsertPostEnabled'] ) ? (bool) $attrs['frblInsertPostEnabled'] : false;
	
	if ( $insert_post_enabled ) {
		// Add data attribute to indicate insert post functionality
		$block_content = preg_replace(
			'/<div([^>]*)class="([^"]*gb-grid-wrapper[^"]*)"([^>]*)>/',
			'<div$1class="$2 frbl-insert-post-grid"$3 data-insert-post="true">',
			$block_content,
			1
		);
	}

	return $block_content;
}

// Register attributes for existing blocks
add_action(
	'init',
	function () {
		add_filter(
			'generateblocks_blocks_registered_block',
			'frbl_register_insert_post_attributes_for_grid_block',
			9,
			2
		);

		add_action(
			'enqueue_block_editor_assets',
			function () {
				wp_add_inline_script(
					'wp-blocks',
					"
					wp.hooks.addFilter(
						'blocks.registerBlockType',
						'frontblocks_insert_post_grid_attributes',
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
		);
	},
	5
);

/**
 * Register insert post attributes for GenerateBlocks Grid block
 *
 * @param array  $block_args The block arguments.
 * @param string $block_type The name of the block.
 * @return array Modified block arguments.
 */
function frbl_register_insert_post_attributes_for_grid_block( $block_args, $block_type ) {
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
