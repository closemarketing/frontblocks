<?php
/**
 * Product Categories module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Alex Castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * ProductCategories class.
 *
 * @since 1.0.0
 */
class ProductCategories {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_product_categories_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
	}

	/**
    * Enqueue frontend styles for the grid.
    * Usa la constante FRBL_PLUGIN_URL para construir la URL pública correcta.
    *
    * @return void
    */
	public function enqueue_frontend_styles() {
		$style_url = FRBL_PLUGIN_URL . 'assets/product-categories/frontblocks-product-categories.css';

		wp_enqueue_style(
        'frontblocks-product-categories-grid-style',
        $style_url,
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
			'frontblocks-product-categories-option',
			FRBL_PLUGIN_URL . 'assets/product-categories/frontblocks-product-categories.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-editor', 'wp-api-fetch', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-product-categories-option',
			'frblProductCategories',
			array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Register the Product Categories block.
	 *
	 * @return void
	 */
	public function register_product_categories_block() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		register_block_type(
			'frontblocks/product-categories',
			array(
				'editor_script'   => 'frontblocks-product-categories-option',
				'render_callback' => array( $this, 'render_product_categories_block' ),
				'attributes'      => array(
					'count'        => array(
						'type'    => 'number',
						'default' => 5,
					),
					'orderby'      => array(
						'type'    => 'string',
						'default' => 'count',
					),
					'order'        => array(
						'type'    => 'string',
						'default' => 'DESC',
					),
					'hideEmpty'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'className'    => array(
						'type'    => 'string',
						'default' => '',
					),
					'columns'    => array(
						'type'    => 'number',
						'default' => 2,
					),
					'bgColor'    => array(
						'type'    => 'string',
						'default' => 'rgba(255, 255, 255, 0.5)',
					),
					'borderColor'    => array(
						'type'    => 'string',
						'default' => '#dddddd',
					),
					'borderWidth'    => array(
						'type'    => 'number',
						'default' => 1,
					),
					'borderRadius'   => array(
						'type'    => 'number',
						'default' => 20,
					),
					'textColor'    => array(
						'type'    => 'string',
						'default' => 'inherit',
					),
					'hoverBgColor'    => array(
						'type'    => 'string',
						'default' => 'rgba(255, 255, 255, 0.7)',
					),
					'hoverBorderColor'    => array(
						'type'    => 'string',
						'default' => '#555555',
					),
					'hoverTextColor'    => array(
						'type'    => 'string',
						'default' => 'inherit',
					),
				),
			)
		);
	}

	/**
    * Render the Product Categories block on frontend.
    *
    * @param array $attributes Block attributes.
    * @return string HTML output.
    */
	public function render_product_categories_block( $attributes ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '';
		}

      $count      = absint( $attributes['count'] ?? 5 );
      $orderby    = sanitize_key( $attributes['orderby'] ?? 'count' );
      $order      = strtoupper( sanitize_key( $attributes['order'] ?? 'DESC' ) );
      $hide_empty = $attributes['hideEmpty'] ?? false;
      $columns    = absint( $attributes['columns'] ?? 2 ); 

      $bg_color          = sanitize_text_field( $attributes['bgColor'] ?? 'rgba(255, 255, 255, 0.5)' );
      $border_color      = sanitize_text_field( $attributes['borderColor'] ?? '#dddddd' );
      $border_width      = absint( $attributes['borderWidth'] ?? 1 );
      $border_radius     = absint( $attributes['borderRadius'] ?? 20 );
      $text_color        = sanitize_text_field( $attributes['textColor'] ?? 'inherit' );
      $hover_bg_color    = sanitize_text_field( $attributes['hoverBgColor'] ?? 'rgba(255, 255, 255, 0.7)' );
      $hover_border_color= sanitize_text_field( $attributes['hoverBorderColor'] ?? '#555555' );
      $hover_text_color  = sanitize_text_field( $attributes['hoverTextColor'] ?? 'inherit' );

      /**
       * Lógica para "Mostrar todas"
       * Si el 'count' es 999 (el valor máximo en el editor),
       * pasamos 'number' => 0 a get_terms para indicar que no hay límite.
       */
      $query_limit = ( $count === 999 ) ? 0 : $count;

      $args = array(
         'taxonomy'   => 'product_cat',
         'orderby'    => $orderby,
         'order'      => $order,
         'number'     => $query_limit,
         'hide_empty' => (bool) $hide_empty, 
      );

      $categories = get_terms( $args );
      
      if ( is_wp_error( $categories ) || empty( $categories ) ) {
         if ( current_user_can( 'manage_options' ) ) {
            $msg = is_wp_error( $categories ) ? $categories->get_error_message() : 'ATENCIÓN: No se encontraron categorías de producto.';
            return '<div style="padding: 10px; border: 1px solid orange; background: #ffe;">' . $msg . '</div>';
         }
         return '';
      }

      $wrapper_class = 'frbl-product-categories-grid';
      if ( ! empty( $attributes['className'] ) ) {
         $wrapper_class .= ' ' . esc_attr( $attributes['className'] );
      }
      
      $style_vars = sprintf(
         '--frbl-grid-columns: %d; --frbl-bg-color: %s; --frbl-border-color: %s; --frbl-border-width: %dpx; --frbl-text-color: %s; --frbl-hover-bg-color: %s; --frbl-hover-border-color: %s; --frbl-hover-text-color: %s; --frbl-border-radius: %dpx;',
         $columns,
         $bg_color,
         $border_color,
         $border_width,
         $text_color,
         $hover_bg_color,
         $hover_border_color,
         $hover_text_color,
         $border_radius,
      );
      
      $style_attr = sprintf( 'style="%s"', $style_vars );

      ob_start();
      ?>
      <div class="<?php echo esc_attr( $wrapper_class ); ?>" <?php echo $style_attr; ?>>
         <?php 
         foreach ( $categories as $category ) : 
            $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
            $image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src();
            $link         = esc_url( get_term_link( $category, 'product_cat' ) );
         ?>
            <div class="frbl-category-item frbl-category-<?php echo esc_attr( $category->slug ); ?>">
               <a href="<?php echo $link; ?>" class="frbl-category-link">
                  <div class="frbl-category-image-wrap">
                     <img 
                        src="<?php echo esc_url( $image_url ); ?>" 
                        alt="<?php echo esc_attr( $category->name ); ?>" 
                        class="frbl-category-image"
                     />
                  </div>
                  <h3 class="frbl-category-name">
                     <?php echo esc_html( $category->name ); ?> (<?php echo esc_html( $category->count ); ?>)
                  </h3>
               </a>
            </div>
         <?php endforeach; ?>
      </div>
      <?php
      return ob_get_clean();
   }
}

new ProductCategories();
