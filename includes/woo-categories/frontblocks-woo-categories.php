<?php
/**
 * WooCommerce Categories Grid Block
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'frbl_woo_categories_scripts', 99 );
/**
 * Loads Scripts for WooCommerce Categories Grid
 *
 * @return void
 */
function frbl_woo_categories_scripts() {
	$dist_dir = WP_DEBUG ? 'woo-categories/' : 'dist/';
	wp_enqueue_style(
		'frontblocks-woo-categories',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-woo-categories.css',
		array(),
		FRBL_VERSION
	);

	wp_enqueue_script(
		'frontblocks-woo-categories',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-woo-categories.js',
		array( 'jquery' ),
		FRBL_VERSION,
		true
	);
}

add_action( 'init', 'frbl_register_woo_categories_block' );
/**
 * Register WooCommerce Categories Grid Block
 *
 * @return void
 */
function frbl_register_woo_categories_block() {
	// Check if WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Register block.
	register_block_type(
		'frontblocks/woo-categories',
		array(
			'attributes'      => array(
				'columns' => array(
					'type'    => 'number',
					'default' => 3,
				),
				'orderby' => array(
					'type'    => 'string',
					'default' => 'name',
				),
				'order' => array(
					'type'    => 'string',
					'default' => 'ASC',
				),
				'parent' => array(
					'type'    => 'number',
					'default' => 0,
				),
				'hide_empty' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'max_depth' => array(
					'type'    => 'number',
					'default' => 1,
				),
			),
			'render_callback' => 'frbl_render_woo_categories_block',
		)
	);
}

/**
 * Render WooCommerce Categories Grid Block
 *
 * @param array $attributes Block attributes.
 * @return string Rendered block.
 */
function frbl_render_woo_categories_block( $attributes ) {
	// Check if WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '<div class="components-placeholder"><div class="components-placeholder__label">WooCommerce is not active</div></div>';
	}

	// Parse attributes.
	$args = array(
		'taxonomy'   => 'product_cat',
		'orderby'    => isset( $attributes['orderby'] ) ? $attributes['orderby'] : 'name',
		'order'      => isset( $attributes['order'] ) ? $attributes['order'] : 'ASC',
		'parent'     => isset( $attributes['parent'] ) ? $attributes['parent'] : 0,
		'hide_empty' => isset( $attributes['hide_empty'] ) ? $attributes['hide_empty'] : true,
	);

	// Get product categories.
	$product_categories = get_terms( $args );

	if ( empty( $product_categories ) || is_wp_error( $product_categories ) ) {
		return '<div class="components-placeholder"><div class="components-placeholder__label">No product categories found</div></div>';
	}

	// Build the grid.
	$columns = isset( $attributes['columns'] ) ? $attributes['columns'] : 3;
	$output  = '<div class="frbl-woo-categories-grid columns-' . esc_attr( $columns ) . '">';

	foreach ( $product_categories as $category ) {
		$category_link = get_term_link( $category, 'product_cat' );
		$thumbnail_id  = get_term_meta( $category->term_id, 'thumbnail_id', true );
		$image         = $thumbnail_id ? wp_get_attachment_image_src( $thumbnail_id, 'woocommerce_thumbnail' ) : '';
		$image_url     = $image ? $image[0] : wc_placeholder_img_src( 'woocommerce_thumbnail' );

		$output .= '<div class="frbl-woo-category">';
		$output .= '<a href="' . esc_url( $category_link ) . '">';
		$output .= '<div class="frbl-woo-category-image">';
		$output .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $category->name ) . '" />';
		$output .= '</div>';
		$output .= '<h3 class="frbl-woo-category-title">' . esc_html( $category->name ) . '</h3>';
		
		if ( $category->description ) {
			$output .= '<div class="frbl-woo-category-description">' . wp_kses_post( $category->description ) . '</div>';
		}
		
		$output .= '</a>';
		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
} 