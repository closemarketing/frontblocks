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

use WP_Block_Type_Registry;

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
		add_action( 'init', array( $this, 'register_product_categories_block' ), 20 );
		add_action( 'init', array( $this, 'enable_rest_api_for_product_cat' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_fields' ) );
	}

	/**
	 * Enable REST API for product_cat taxonomy.
	 *
	 * @return void
	 */
	public function enable_rest_api_for_product_cat() {
		global $wp_taxonomies;

		if ( isset( $wp_taxonomies['product_cat'] ) ) {
			$wp_taxonomies['product_cat']->show_in_rest          = true;
			$wp_taxonomies['product_cat']->rest_base             = 'product_cat';
			$wp_taxonomies['product_cat']->rest_controller_class = 'WP_REST_Terms_Controller';
		}
	}

	/**
	 * Register custom REST API fields for product_cat taxonomy.
	 *
	 * @return void
	 */
	public function register_rest_fields() {
		register_rest_field(
			'product_cat',
			'category_image',
			array(
				'get_callback' => array( $this, 'get_category_image' ),
				'schema'       => array(
					'description' => __( 'Category thumbnail image.', 'frontblocks' ),
					'type'        => 'object',
				),
			)
		);
	}

	/**
	 * Get category image for REST API.
	 *
	 * @param array $term_data Category term data.
	 * @return array|null Image data.
	 */
	public function get_category_image( $term_data ) {
		$thumbnail_id = get_term_meta( $term_data['id'], 'thumbnail_id', true );

		if ( $thumbnail_id ) {
			$image_url = wp_get_attachment_image_url( $thumbnail_id, 'woocommerce_thumbnail' );
			if ( $image_url ) {
				return array(
					'src' => $image_url,
					'id'  => $thumbnail_id,
				);
			}
		}

		if ( function_exists( 'wc_placeholder_img_src' ) ) {
			return array(
				'src' => wc_placeholder_img_src(),
				'id'  => 0,
			);
		}

		return null;
	}

	/**
	 * Enqueue frontend styles for the grid.
	 * Usa la constante FRBL_PLUGIN_URL para construir la URL pública correcta.
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		wp_register_style(
			'frontblocks-product-categories-grid-style',
			FRBL_PLUGIN_URL . 'assets/product-categories/frontblocks-product-categories.css',
			array(),
			FRBL_VERSION
		);

		if ( is_admin() || has_block( 'frontblocks/product-categories' ) ) {
			wp_enqueue_style( 'frontblocks-product-categories-grid-style' );
		}
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		// Enqueue styles for editor.
		wp_enqueue_style(
			'frontblocks-product-categories-grid-style',
			FRBL_PLUGIN_URL . 'assets/product-categories/frontblocks-product-categories.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-product-categories-option',
			FRBL_PLUGIN_URL . 'assets/product-categories/frontblocks-product-categories.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-editor', 'wp-api-fetch', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-product-categories-option',
			'frontblocks'
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

		$args = array(
			'editor_script'   => 'frontblocks-product-categories-option',
			'render_callback' => array( $this, 'render_product_categories_block' ),
			'attributes'      => array(
				'count'            => array(
					'type'    => 'number',
					'default' => 5,
				),
				'orderby'          => array(
					'type'    => 'string',
					'default' => 'count',
				),
				'order'            => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'hideEmpty'        => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'showCount'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'className'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'columns'          => array(
					'type'    => 'number',
					'default' => 2,
				),
				'bgColor'          => array(
					'type'    => 'string',
					'default' => 'rgba(255, 255, 255, 0.5)',
				),
				'borderColor'      => array(
					'type'    => 'string',
					'default' => '#dddddd',
				),
				'borderWidth'      => array(
					'type'    => 'number',
					'default' => 1,
				),
				'borderRadius'     => array(
					'type'    => 'number',
					'default' => 20,
				),
				'textColor'        => array(
					'type'    => 'string',
					'default' => 'inherit',
				),
				'hoverBgColor'     => array(
					'type'    => 'string',
					'default' => 'rgba(255, 255, 255, 0.7)',
				),
				'hoverBorderColor' => array(
					'type'    => 'string',
					'default' => '#555555',
				),
				'hoverTextColor'   => array(
					'type'    => 'string',
					'default' => 'inherit',
				),
				'showButton'          => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'buttonText'          => array(
					'type'    => 'string',
					'default' => '',
				),
				'btnBgColor'          => array(
					'type'    => 'string',
					'default' => 'transparent',
				),
				'btnTextColor'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'btnBorderColor'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'btnBorderWidth'      => array(
					'type'    => 'number',
					'default' => 2,
				),
				'btnBorderRadius'     => array(
					'type'    => 'number',
					'default' => 4,
				),
				'btnFontSize'         => array(
					'type'    => 'number',
					'default' => 14,
				),
				'btnPaddingV'         => array(
					'type'    => 'number',
					'default' => 10,
				),
				'btnPaddingH'         => array(
					'type'    => 'number',
					'default' => 20,
				),
				'btnHoverBgColor'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'btnHoverTextColor'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'btnHoverBorderColor' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		);

		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'frontblocks/product-categories' ) ) {
			register_block_type(
				'frontblocks/product-categories',
				$args
			);
		}
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
		$show_count = $attributes['showCount'] ?? true;
		$columns    = absint( $attributes['columns'] ?? 2 );

		$bg_color           = sanitize_text_field( $attributes['bgColor'] ?? 'rgba(255, 255, 255, 0.5)' );
		$border_color       = sanitize_text_field( $attributes['borderColor'] ?? '#dddddd' );
		$border_width       = absint( $attributes['borderWidth'] ?? 1 );
		$border_radius      = absint( $attributes['borderRadius'] ?? 20 );
		$text_color         = sanitize_text_field( $attributes['textColor'] ?? 'inherit' );
		$hover_bg_color     = sanitize_text_field( $attributes['hoverBgColor'] ?? 'rgba(255, 255, 255, 0.7)' );
		$hover_border_color = sanitize_text_field( $attributes['hoverBorderColor'] ?? '#555555' );
		$hover_text_color   = sanitize_text_field( $attributes['hoverTextColor'] ?? 'inherit' );
		$show_button           = ! empty( $attributes['showButton'] );
		$button_text           = sanitize_text_field( $attributes['buttonText'] ?? '' );
		$btn_bg_color          = sanitize_text_field( $attributes['btnBgColor'] ?? 'transparent' );
		$btn_text_color        = sanitize_text_field( $attributes['btnTextColor'] ?? '' );
		$btn_border_color      = sanitize_text_field( $attributes['btnBorderColor'] ?? '' );
		$btn_border_width      = absint( $attributes['btnBorderWidth'] ?? 2 );
		$btn_border_radius     = absint( $attributes['btnBorderRadius'] ?? 4 );
		$btn_font_size         = absint( $attributes['btnFontSize'] ?? 14 );
		$btn_padding_v         = absint( $attributes['btnPaddingV'] ?? 10 );
		$btn_padding_h         = absint( $attributes['btnPaddingH'] ?? 20 );
		$btn_hover_bg_color    = sanitize_text_field( $attributes['btnHoverBgColor'] ?? '' );
		$btn_hover_text_color  = sanitize_text_field( $attributes['btnHoverTextColor'] ?? '' );
		$btn_hover_border_color = sanitize_text_field( $attributes['btnHoverBorderColor'] ?? '' );

		/**
		 * Lógica para "Mostrar todas"
		 * Si el 'count' es 999 (el valor máximo en el editor),
		 * pasamos 'number' => 0 a get_terms para indicar que no hay límite.
		 */
		$query_limit = ( 999 === $count ) ? 0 : $count;

		$args       = array(
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
			'--frbl-grid-columns:%d;--frbl-bg-color:%s;--frbl-border-color:%s;--frbl-border-width:%dpx;--frbl-text-color:%s;--frbl-hover-bg-color:%s;--frbl-hover-border-color:%s;--frbl-hover-text-color:%s;--frbl-border-radius:%dpx;--frbl-btn-bg:%s;--frbl-btn-color:%s;--frbl-btn-border-color:%s;--frbl-btn-border-width:%dpx;--frbl-btn-border-radius:%dpx;--frbl-btn-font-size:%dpx;--frbl-btn-padding-v:%dpx;--frbl-btn-padding-h:%dpx;--frbl-btn-hover-bg:%s;--frbl-btn-hover-color:%s;--frbl-btn-hover-border-color:%s;',
			$columns,
			$bg_color,
			$border_color,
			$border_width,
			$text_color,
			$hover_bg_color,
			$hover_border_color,
			$hover_text_color,
			$border_radius,
			$btn_bg_color,
			$btn_text_color ? $btn_text_color : 'currentColor',
			$btn_border_color ? $btn_border_color : 'currentColor',
			$btn_border_width,
			$btn_border_radius,
			$btn_font_size,
			$btn_padding_v,
			$btn_padding_h,
			$btn_hover_bg_color ? $btn_hover_bg_color : 'transparent',
			$btn_hover_text_color ? $btn_hover_text_color : 'currentColor',
			$btn_hover_border_color ? $btn_hover_border_color : 'currentColor'
		);

		ob_start();
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>" style="<?php echo esc_attr( $style_vars ); ?>">
			<?php
			foreach ( $categories as $category ) :
				$thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );

				if ( $thumbnail_id ) {
					$image_url = wp_get_attachment_image_url( $thumbnail_id, 'woocommerce_thumbnail' );
				} elseif ( function_exists( 'wc_placeholder_img_src' ) ) {
					$image_url = wc_placeholder_img_src();
				} else {
					$image_url = 'https://placehold.co/600x400/eeeeee/333333?text=Product+Category';
				}

				$link = esc_url( get_term_link( $category, 'product_cat' ) );
				?>
				<div class="frbl-category-item frbl-category-<?php echo esc_attr( $category->slug ); ?>">
					<a href="<?php echo esc_url( $link ); ?>" class="frbl-category-link">
						<div class="frbl-category-image-wrap">
							<img
								src="<?php echo esc_url( $image_url ); ?>"
								alt="<?php echo esc_attr( $category->name ); ?>"
								class="frbl-category-image"
							/>
						</div>
						<h3 class="frbl-category-name">
							<?php echo esc_html( $category->name ); ?><?php echo $show_count ? ' (' . esc_html( $category->count ) . ')' : ''; ?>
						</h3>
						<?php if ( $show_button ) : ?>
							<span class="frbl-category-button">
								<?php echo esc_html( $button_text ? $button_text : __( 'Shop now', 'frontblocks' ) ); ?>
							</span>
						<?php endif; ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

new ProductCategories();
