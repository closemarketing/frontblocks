<?php
/**
 * Filter menu to menu blocks
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter Menu.
 *
 * @since 1.0.0
 */
class MenuBlocks_Filter {

	/**
	 * Construct of Class
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'styles_scripts' ) );
		add_filter( 'wp_nav_menu_items', array( $this, 'filter_menu_block' ), 10, 2 );
	}
	/**
	 * Styles and scripts
	 *
	 * @return void
	 */
	public function styles_scripts() {
		wp_enqueue_style(
			'menu-blocks',
			MEBL_PLUGIN_URL . 'includes/public/assets/menu-blocks.css',
			array(),
			MEBL_VERSION
		);
	}

	/**
	 * Filters menu blocks
	 *
	 * @param array  $items Items of menu.
	 * @param object $args Arguments of menu.
	 * @return html
	 */
	public function filter_menu_block( $items, $args ) {
		if ( 'primary' === $args->theme_location ) {
			$args_query = array(
				'post_type'      => 'menu_block',
				'posts_per_page' => -1,
				'post_parent'    => 0,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			);

			$the_query = new WP_Query( $args_query );
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$menu_block_title   = get_the_title();
					$menu_block_content = apply_filters( 'the_content', get_the_content() );
					$menu_block_url     = get_post_meta( get_the_ID(), 'menu_block_url_parent', true );
					$menu_block_url     = ! empty( $menu_block_url ) ? $menu_block_url : '#';
					$menu_block_css     = get_post_meta( get_the_ID(), 'menu_block_css_class', true );

					$items .= '<li class="menu-item wrap-menu mega-menu';
					$items .= ! empty( $menu_block_css ) ? ' ' . esc_attr( $menu_block_css ) : '';
					$items .= '">';
					$items .= '<a class="item-menu_block mega-menu" href="';
					$items .= ! empty( $menu_block_url ) ? esc_url( $menu_block_url ) : '#';
					$items .= '">';
					$items .= esc_html( $menu_block_title ) . '</a>';
					$items .= '<ul class="sub-menu mega-menu">';
					$items .= '<li class="menu-item menu-item-has-children">';
					$items .= $menu_block_content;
					$items .= '</li>';
					$items .= '</ul>';
					$items .= '</li>';
				}
				wp_reset_postdata();
			}
		}
		return $items;
	}
}

new MenuBlocks_Filter();
