<?php
/**
 * Block Content Link
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2024 CLOSE
 * @version    1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render Content Link
 *
 * @param array $block_attributes The block attributes.
 * @param array $block_content The block content.
 * @return html
 */
function frbl_content_link_callback( $block_attributes, $block_content ) {
	$block_classes = isset( $block_attributes['className'] )
		? $block_attributes['className'] . 'wp-block-frontblocks-news'
		: 'wp-block-frontblocks-news';

	$args = array(
		'posts_per_page' => -1,
	);

	if ( isset( $block_attributes['category'] ) ) {
		$args['category'] = $block_attributes['category'];
	}

	$news   = get_posts( $args );
	$render = '';
	if ( 0 < count( $news ) ) {
		$render     .= '<div class="' . esc_attr( $block_classes ) . '">';
			$render .= '<h3>Quizás te interese leer esto:</h3>';
			$render .= '<ul className="posts">';
		foreach ( $news as $new ) {
			$render     .= '<li>';
				$render .= "<a href=\"{$new->guid}\">";
				$render .= "{$new->post_title}";
				$render .= '</a>';
			$render     .= '</li>';
		}
			$render .= '</ul>';
		$render     .= '</div>';
	}

	return $render;
}

add_action( 'init', 'frbl_register_blocks' );
/**
 * Regiters block
 *
 * @return void
 */
function frbl_register_blocks() {
	$assets_file = include_once FRBL_PLUGIN_PATH . 'includes/blocks/content-link/build/index.asset.php';

	wp_register_script(
		'frontblocks-content-link',
		FRBL_PLUGIN_URL . 'includes/blocks/content-link/build/index.js',
		$assets_file['dependencies'],
		FRBL_VERSION,
		true
	);

	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'frbl_content_link_callback',
		)
	);
}
