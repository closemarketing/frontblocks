<?php
/**
 * Carousel module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Carousel class.
 *
 * @since 1.0.0
 */
class Carousel {

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
		add_action( 'init', array( $this, 'register_frontend_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_canvas_assets' ) );
		add_filter( 'render_block_generateblocks/grid', array( $this, 'add_custom_attributes_to_grid_block' ), 10, 2 );
		add_filter( 'render_block_generateblocks/element', array( $this, 'add_custom_attributes_to_element_block' ), 10, 2 );
		add_filter( 'render_block_core/group', array( $this, 'add_custom_attributes_to_core_group_block' ), 10, 2 );
		add_filter( 'render_block_core/query', array( $this, 'add_custom_attributes_to_query_block' ), 10, 2 );
		add_action( 'init', array( $this, 'register_custom_attributes' ), 5 );
	}

	/**
	 * Register frontend carousel assets for conditional enqueueing.
	 *
	 * @return void
	 */
	public function register_frontend_assets() {
		// Assets are registered in Plugin_Main::register_scripts().
		// This method exists as a hook point for any additional registration needs.
	}

	/**
	 * Enqueue carousel frontend assets when a carousel block is detected.
	 *
	 * @return void
	 */
	private function enqueue_carousel_assets() {
		if ( ! wp_style_is( 'frontblocks-carousel', 'enqueued' ) ) {
			wp_enqueue_style( 'frontblocks-carousel' );
		}
		if ( ! wp_script_is( 'frontblocks-carousel-custom', 'enqueued' ) ) {
			wp_enqueue_script( 'frontblocks-carousel-custom' );
		}
	}

	/**
	 * Enqueue carousel CSS in the editor canvas (iframe).
	 *
	 * @return void
	 */
	public function enqueue_block_canvas_assets() {
		if ( ! is_admin() ) {
			return;
		}
		wp_enqueue_style(
			'frontblocks-carousel-editor',
			FRBL_PLUGIN_URL . 'assets/carousel/frontblocks-carousel-editor.css',
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
			'frontblocks-advanced-option',
			FRBL_PLUGIN_URL . 'assets/carousel/frontblocks-advanced-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post' ),
			FRBL_VERSION,
			true
		);

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-advanced-option',
			'frontblocks'
		);
	}

	/**
	 * Add custom attributes to grid block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_custom_attributes_to_grid_block( $block_content, $block ) {
		$attrs              = $block['attrs'] ?? array();
		$custom_option      = isset( $attrs['frblGridOption'] ) ? sanitize_text_field( $attrs['frblGridOption'] ) : '';
		$items_to_view      = isset( $attrs['frblItemsToView'] ) ? (int) $attrs['frblItemsToView'] : 4;
		$laptop_to_view     = isset( $attrs['frblLaptopToView'] ) ? (int) $attrs['frblLaptopToView'] : 3;
		$tablet_to_view     = isset( $attrs['frblTabletToView'] ) ? (int) $attrs['frblTabletToView'] : 2;
		$responsive_to_view = isset( $attrs['frblResponsiveToView'] ) ? (int) $attrs['frblResponsiveToView'] : 1;
		$autoplay           = isset( $attrs['frblAutoplay'] ) ? ( (int) $attrs['frblAutoplay'] * 1000 ) : '';
		$gap                = isset( $attrs['frblGap'] ) && '' !== $attrs['frblGap'] ? (int) $attrs['frblGap'] : 20;
		$rewind             = isset( $attrs['frblRewind'] ) ? (bool) $attrs['frblRewind'] : true;
		$buttons            = isset( $attrs['frblButtons'] ) ? sanitize_text_field( $attrs['frblButtons'] ) : 'arrows';
		$button_color       = isset( $attrs['frblButtonColor'] ) ? sanitize_text_field( $attrs['frblButtonColor'] ) : '';
		$button_bg_color    = isset( $attrs['frblButtonBgColor'] ) ? sanitize_text_field( $attrs['frblButtonBgColor'] ) : '';
		$buttons_position   = isset( $attrs['frblButtonsPosition'] ) ? sanitize_text_field( $attrs['frblButtonsPosition'] ) : 'side';
		$arrow_left_url    = isset( $attrs['frblArrowLeftUrl'] ) ? esc_url_raw( $attrs['frblArrowLeftUrl'] ) : '';
		$arrow_right_url    = isset( $attrs['frblArrowRightUrl'] ) ? esc_url_raw( $attrs['frblArrowRightUrl'] ) : '';
		$disable_on_desktop = isset( $attrs['frblDisableOnDesktop'] ) ? (bool) $attrs['frblDisableOnDesktop'] : false;

		// Add data attributes to the wrapper div if carousel is enabled.
		if ( 'carousel' === $custom_option || 'slider' === $custom_option ) {
			$attributes = '';
			if ( 'slider' === $custom_option ) {
				$attributes .= ' data-rewind="' . esc_attr( $rewind ) . '"';
			}

			// Add data attributes and the 'frontblocks-carousel' class to the first div in the block content.
			$block_content = preg_replace(
				'/<div([^>]*)class="([^"]*gb-grid-wrapper[^"]*)"([^>]*)>/',
				'<div$1class="$2 frontblocks-carousel"$3' .
					' data-type="' . esc_attr( $custom_option ) . '"' .
					' data-view="' . esc_attr( $items_to_view ) . '"' .
					' data-laptop-view="' . esc_attr( $laptop_to_view ) . '"' .
					' data-tablet-view="' . esc_attr( $tablet_to_view ) . '"' .
					' data-mobile-view="' . esc_attr( $responsive_to_view ) . '"' .
					' data-autoplay="' . esc_attr( $autoplay ) . '"' .
					' data-gap="' . esc_attr( $gap ) . '"' .
					' data-buttons="' . esc_attr( $buttons ) . '"' .
					' data-buttons-color="' . esc_attr( $button_color ) . '"' .
					' data-buttons-background-color="' . esc_attr( $button_bg_color ) . '"' .
					' data-buttons-position="' . esc_attr( $buttons_position ) . '"' .
					( $arrow_left_url ? ' data-arrow-left-url="'  . esc_url( $arrow_left_url)   . '"' : '' ) .
					( $arrow_right_url ? ' data-arrow-right-url="' . esc_url( $arrow_right_url )  . '"' : '' ) .
					' data-disable-on-desktop="' . esc_attr( $disable_on_desktop ? 'true' : 'false' ) . '"' .
					$attributes .
					'>',
				$block_content,
				1 // Only replace the first occurrence.
			);

			$this->enqueue_carousel_assets();
		}

		return $block_content;
	}

	/**
	 * Add custom attributes to element block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_custom_attributes_to_element_block( $block_content, $block ) {
		$attrs = $block['attrs'] ?? array();

		// Check if this element has grid display.
		$styles  = $attrs['styles'] ?? array();
		$display = $styles['display'] ?? '';

		// Only process if it's a grid element.
		if ( 'grid' !== $display ) {
			return $block_content;
		}

		$custom_option      = isset( $attrs['frblGridOption'] ) ? sanitize_text_field( $attrs['frblGridOption'] ) : '';
		$items_to_view      = isset( $attrs['frblItemsToView'] ) ? (int) $attrs['frblItemsToView'] : 4;
		$laptop_to_view     = isset( $attrs['frblLaptopToView'] ) ? (int) $attrs['frblLaptopToView'] : 3;
		$tablet_to_view     = isset( $attrs['frblTabletToView'] ) ? (int) $attrs['frblTabletToView'] : 2;
		$responsive_to_view = isset( $attrs['frblResponsiveToView'] ) ? (int) $attrs['frblResponsiveToView'] : 1;
		$autoplay           = isset( $attrs['frblAutoplay'] ) ? ( (int) $attrs['frblAutoplay'] * 1000 ) : '';
		$gap                = isset( $attrs['frblGap'] ) && '' !== $attrs['frblGap'] ? (int) $attrs['frblGap'] : 20;
		$rewind             = isset( $attrs['frblRewind'] ) ? (bool) $attrs['frblRewind'] : true;
		$buttons            = isset( $attrs['frblButtons'] ) ? sanitize_text_field( $attrs['frblButtons'] ) : 'arrows';
		$button_color       = isset( $attrs['frblButtonColor'] ) ? sanitize_text_field( $attrs['frblButtonColor'] ) : '';
		$button_bg_color    = isset( $attrs['frblButtonBgColor'] ) ? sanitize_text_field( $attrs['frblButtonBgColor'] ) : '';
		$buttons_position   = isset( $attrs['frblButtonsPosition'] ) ? sanitize_text_field( $attrs['frblButtonsPosition'] ) : 'side';
		$arrow_left_url    = isset( $attrs['frblArrowLeftUrl'] ) ? esc_url_raw( $attrs['frblArrowLeftUrl'] ) : '';
		$arrow_right_url    = isset( $attrs['frblArrowRightUrl'] ) ? esc_url_raw( $attrs['frblArrowRightUrl'] ) : '';
		$disable_on_desktop = isset( $attrs['frblDisableOnDesktop'] ) ? (bool) $attrs['frblDisableOnDesktop'] : false;

		// Add data attributes to the wrapper div if carousel is enabled.
		if ( 'carousel' === $custom_option || 'slider' === $custom_option ) {
			$attributes = '';
			if ( 'slider' === $custom_option ) {
				$attributes .= ' data-rewind="' . esc_attr( $rewind ) . '"';
			}

			// Add data attributes and the 'frontblocks-carousel' class to the first div in the block content.
			$block_content = preg_replace(
				'/<div([^>]*)class="([^"]*gb-element-[^"]*)"([^>]*)>/',
				'<div$1class="$2 frontblocks-carousel"$3' .
					' data-type="' . esc_attr( $custom_option ) . '"' .
					' data-view="' . esc_attr( $items_to_view ) . '"' .
					' data-laptop-view="' . esc_attr( $laptop_to_view ) . '"' .
					' data-tablet-view="' . esc_attr( $tablet_to_view ) . '"' .
					' data-mobile-view="' . esc_attr( $responsive_to_view ) . '"' .
					' data-autoplay="' . esc_attr( $autoplay ) . '"' .
					' data-gap="' . esc_attr( $gap ) . '"' .
					' data-buttons="' . esc_attr( $buttons ) . '"' .
					' data-buttons-color="' . esc_attr( $button_color ) . '"' .
					' data-buttons-background-color="' . esc_attr( $button_bg_color ) . '"' .
					' data-buttons-position="' . esc_attr( $buttons_position ) . '"' .
					( $arrow_left_url ? ' data-arrow-left-url="'  . esc_url( $arrow_left_url)   . '"' : '' ) .
					( $arrow_right_url ? ' data-arrow-right-url="' . esc_url( $arrow_right_url )  . '"' : '' ) .
					' data-disable-on-desktop="' . esc_attr( $disable_on_desktop ? 'true' : 'false' ) . '"' .
					$attributes .
					'>',
				$block_content,
				1 // Only replace the first occurrence.
			);

			$this->enqueue_carousel_assets();
		}

		return $block_content;
	}

	/**
	 * Add custom attributes to core/group block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_custom_attributes_to_core_group_block( $block_content, $block ) {
		$attrs = $block['attrs'] ?? array();

		// Check if this group has grid layout.
		$layout      = $attrs['layout'] ?? array();
		$layout_type = $layout['type'] ?? '';

		// Only process if it's a grid layout.
		if ( 'grid' !== $layout_type ) {
			return $block_content;
		}

		$custom_option      = isset( $attrs['frblGridOption'] ) ? sanitize_text_field( $attrs['frblGridOption'] ) : '';
		$items_to_view      = isset( $attrs['frblItemsToView'] ) ? (int) $attrs['frblItemsToView'] : 4;
		$laptop_to_view     = isset( $attrs['frblLaptopToView'] ) ? (int) $attrs['frblLaptopToView'] : 3;
		$tablet_to_view     = isset( $attrs['frblTabletToView'] ) ? (int) $attrs['frblTabletToView'] : 2;
		$responsive_to_view = isset( $attrs['frblResponsiveToView'] ) ? (int) $attrs['frblResponsiveToView'] : 1;
		$autoplay           = isset( $attrs['frblAutoplay'] ) ? ( (int) $attrs['frblAutoplay'] * 1000 ) : '';
		$gap                = isset( $attrs['frblGap'] ) && '' !== $attrs['frblGap'] ? (int) $attrs['frblGap'] : 20;
		$rewind             = isset( $attrs['frblRewind'] ) ? (bool) $attrs['frblRewind'] : true;
		$buttons            = isset( $attrs['frblButtons'] ) ? sanitize_text_field( $attrs['frblButtons'] ) : 'arrows';
		$button_color       = isset( $attrs['frblButtonColor'] ) ? sanitize_text_field( $attrs['frblButtonColor'] ) : '';
		$button_bg_color    = isset( $attrs['frblButtonBgColor'] ) ? sanitize_text_field( $attrs['frblButtonBgColor'] ) : '';
		$buttons_position   = isset( $attrs['frblButtonsPosition'] ) ? sanitize_text_field( $attrs['frblButtonsPosition'] ) : 'side';
		$arrow_left_url    = isset( $attrs['frblArrowLeftUrl'] ) ? esc_url_raw( $attrs['frblArrowLeftUrl'] ) : '';
		$arrow_right_url    = isset( $attrs['frblArrowRightUrl'] ) ? esc_url_raw( $attrs['frblArrowRightUrl'] ) : '';
		$disable_on_desktop = isset( $attrs['frblDisableOnDesktop'] ) ? (bool) $attrs['frblDisableOnDesktop'] : false;

		// Add data attributes to the wrapper div if carousel is enabled.
		if ( 'carousel' === $custom_option || 'slider' === $custom_option ) {
			$attributes = '';
			if ( 'slider' === $custom_option ) {
				$attributes .= ' data-rewind="' . esc_attr( $rewind ) . '"';
			}

			// Add data attributes and the 'frontblocks-carousel' class to the first div in the block content.
			$block_content = preg_replace(
				'/<div([^>]*)class="([^"]*wp-block-group[^"]*)"([^>]*)>/',
				'<div$1class="$2 frontblocks-carousel"$3' .
					' data-type="' . esc_attr( $custom_option ) . '"' .
					' data-view="' . esc_attr( $items_to_view ) . '"' .
					' data-laptop-view="' . esc_attr( $laptop_to_view ) . '"' .
					' data-tablet-view="' . esc_attr( $tablet_to_view ) . '"' .
					' data-mobile-view="' . esc_attr( $responsive_to_view ) . '"' .
					' data-autoplay="' . esc_attr( $autoplay ) . '"' .
					' data-gap="' . esc_attr( $gap ) . '"' .
					' data-buttons="' . esc_attr( $buttons ) . '"' .
					' data-buttons-color="' . esc_attr( $button_color ) . '"' .
					' data-buttons-background-color="' . esc_attr( $button_bg_color ) . '"' .
					' data-buttons-position="' . esc_attr( $buttons_position ) . '"' .
					( $arrow_left_url ? ' data-arrow-left-url="'  . esc_url( $arrow_left_url)   . '"' : '' ) .
					( $arrow_right_url ? ' data-arrow-right-url="' . esc_url( $arrow_right_url )  . '"' : '' ) .
					' data-disable-on-desktop="' . esc_attr( $disable_on_desktop ? 'true' : 'false' ) . '"' .
					$attributes .
					'>',
				$block_content,
				1 // Only replace the first occurrence.
			);

			$this->enqueue_carousel_assets();
		}

		return $block_content;
	}

	/**
	 * Add custom attributes to core/query block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_custom_attributes_to_query_block( $block_content, $block ) {
		$attrs         = $block['attrs'] ?? array();
		$custom_option = isset( $attrs['frblGridOption'] ) ? sanitize_text_field( $attrs['frblGridOption'] ) : '';

		if ( 'carousel' !== $custom_option && 'slider' !== $custom_option ) {
			return $block_content;
		}

		$items_to_view      = isset( $attrs['frblItemsToView'] ) ? (int) $attrs['frblItemsToView'] : 4;
		$laptop_to_view     = isset( $attrs['frblLaptopToView'] ) ? (int) $attrs['frblLaptopToView'] : 3;
		$tablet_to_view     = isset( $attrs['frblTabletToView'] ) ? (int) $attrs['frblTabletToView'] : 2;
		$responsive_to_view = isset( $attrs['frblResponsiveToView'] ) ? (int) $attrs['frblResponsiveToView'] : 1;
		$autoplay           = isset( $attrs['frblAutoplay'] ) ? ( (int) $attrs['frblAutoplay'] * 1000 ) : '';
		$gap                = isset( $attrs['frblGap'] ) && '' !== $attrs['frblGap'] ? (int) $attrs['frblGap'] : 20;
		$rewind             = isset( $attrs['frblRewind'] ) ? (bool) $attrs['frblRewind'] : true;
		$buttons            = isset( $attrs['frblButtons'] ) ? sanitize_text_field( $attrs['frblButtons'] ) : 'arrows';
		$button_color       = isset( $attrs['frblButtonColor'] ) ? sanitize_text_field( $attrs['frblButtonColor'] ) : '';
		$button_bg_color    = isset( $attrs['frblButtonBgColor'] ) ? sanitize_text_field( $attrs['frblButtonBgColor'] ) : '';
		$buttons_position   = isset( $attrs['frblButtonsPosition'] ) ? sanitize_text_field( $attrs['frblButtonsPosition'] ) : 'side';
		$arrow_left_url    = isset( $attrs['frblArrowLeftUrl'] ) ? esc_url_raw( $attrs['frblArrowLeftUrl'] ) : '';
		$arrow_right_url    = isset( $attrs['frblArrowRightUrl'] ) ? esc_url_raw( $attrs['frblArrowRightUrl'] ) : '';
		$disable_on_desktop = isset( $attrs['frblDisableOnDesktop'] ) ? (bool) $attrs['frblDisableOnDesktop'] : false;

		$extra = '';
		if ( 'slider' === $custom_option ) {
			$extra .= ' data-rewind="' . esc_attr( $rewind ) . '"';
		}

		$block_content = preg_replace(
			'/<ul([^>]*)class="([^"]*wp-block-post-template[^"]*)"([^>]*)>/',
			'<ul$1class="$2 frontblocks-carousel"$3' .
				' data-type="' . esc_attr( $custom_option ) . '"' .
				' data-view="' . esc_attr( $items_to_view ) . '"' .
				' data-laptop-view="' . esc_attr( $laptop_to_view ) . '"' .
				' data-tablet-view="' . esc_attr( $tablet_to_view ) . '"' .
				' data-mobile-view="' . esc_attr( $responsive_to_view ) . '"' .
				' data-autoplay="' . esc_attr( $autoplay ) . '"' .
				' data-gap="' . esc_attr( $gap ) . '"' .
				' data-buttons="' . esc_attr( $buttons ) . '"' .
				' data-buttons-color="' . esc_attr( $button_color ) . '"' .
				' data-buttons-background-color="' . esc_attr( $button_bg_color ) . '"' .
				' data-buttons-position="' . esc_attr( $buttons_position ) . '"' .
				( $arrow_left_url ? ' data-arrow-left-url="'  . esc_url( $arrow_left_url)   . '"' : '' ) .
				( $arrow_right_url ? ' data-arrow-right-url="' . esc_url( $arrow_right_url )  . '"' : '' ) .
				' data-disable-on-desktop="' . esc_attr( $disable_on_desktop ? 'true' : 'false' ) . '"' .
				$extra .
				'>',
			$block_content,
			1
		);

		$this->enqueue_carousel_assets();

		return $block_content;
	}

	/**
	 * Register custom attributes for blocks.
	 *
	 * @return void
	 */
	public function register_custom_attributes() {
		// Register attributes before GenerateBlocks registers its blocks.
		add_filter(
			'generateblocks_blocks_registered_block',
			array( $this, 'register_custom_attributes_for_grid_block' ),
			9,
			2
		);

		// Register attributes for core/query block.
		add_filter( 'register_block_type_args', array( $this, 'register_query_block_attributes' ), 10, 2 );

		// Register attributes from frontend side too.
		add_action(
			'enqueue_block_editor_assets',
			array( $this, 'add_inline_script_for_attributes' )
		);
	}

	/**
	 * Register custom attributes for GenerateBlocks Grid and Element blocks.
	 *
	 * @param array  $block_args The block arguments.
	 * @param string $block_type The name of the block.
	 * @return array Modified block arguments.
	 */
	public function register_custom_attributes_for_grid_block( $block_args, $block_type ) {
		if ( 'generateblocks/grid' !== $block_type && 'generateblocks/element' !== $block_type ) {
			return $block_args;
		}

		$block_args['attributes']['frblGridOption']       = array(
			'type'    => 'string',
			'default' => 'none',
		);
		$block_args['attributes']['frblItemsToView']      = array(
			'type'    => 'string',
			'default' => '4',
		);
		$block_args['attributes']['frblLaptopToView']     = array(
			'type'    => 'string',
			'default' => '3',
		);
		$block_args['attributes']['frblTabletToView']     = array(
			'type'    => 'string',
			'default' => '2',
		);
		$block_args['attributes']['frblResponsiveToView'] = array(
			'type'    => 'string',
			'default' => '1',
		);
		$block_args['attributes']['frblAutoplay']         = array(
			'type'    => 'string',
			'default' => '',
		);
		$block_args['attributes']['frblGap']              = array(
			'type'    => 'string',
			'default' => '20',
		);
		$block_args['attributes']['frblRewind']           = array(
			'type'    => 'boolean',
			'default' => true,
		);
		$block_args['attributes']['frblButtons']          = array(
			'type'    => 'string',
			'default' => 'arrows',
		);
		$block_args['attributes']['frblButtonColor']      = array(
			'type'    => 'string',
			'default' => '',
		);
		$block_args['attributes']['frblButtonBgColor']    = array(
			'type'    => 'string',
			'default' => '',
		);
		$block_args['attributes']['frblButtonsPosition']  = array(
			'type'    => 'string',
			'default' => 'side',
		);
		$block_args['attributes']['frblArrowLeftUrl']     = array(
			'type'    => 'string',
			'default' => '',
		);
		$block_args['attributes']['frblArrowRightUrl']    = array(
			'type'    => 'string',
			'default' => '',
		);
		$block_args['attributes']['frblDisableOnDesktop'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $block_args;
	}

	/**
	 * Register custom attributes for core/query block.
	 *
	 * @param array  $args       Block type arguments.
	 * @param string $block_type Block type name.
	 * @return array
	 */
	public function register_query_block_attributes( $args, $block_type ) {
		if ( 'core/query' !== $block_type ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes']['frblGridOption']       = array(
			'type'    => 'string',
			'default' => 'none',
		);
		$args['attributes']['frblItemsToView']      = array(
			'type'    => 'string',
			'default' => '4',
		);
		$args['attributes']['frblLaptopToView']     = array(
			'type'    => 'string',
			'default' => '3',
		);
		$args['attributes']['frblTabletToView']     = array(
			'type'    => 'string',
			'default' => '2',
		);
		$args['attributes']['frblResponsiveToView'] = array(
			'type'    => 'string',
			'default' => '1',
		);
		$args['attributes']['frblAutoplay']         = array(
			'type'    => 'string',
			'default' => '',
		);
		$args['attributes']['frblGap']              = array(
			'type'    => 'string',
			'default' => '20',
		);
		$args['attributes']['frblRewind']           = array(
			'type'    => 'boolean',
			'default' => true,
		);
		$args['attributes']['frblButtons']          = array(
			'type'    => 'string',
			'default' => 'arrows',
		);
		$args['attributes']['frblButtonColor']      = array(
			'type'    => 'string',
			'default' => '',
		);
		$args['attributes']['frblButtonBgColor']    = array(
			'type'    => 'string',
			'default' => '',
		);
		$args['attributes']['frblButtonsPosition']  = array(
			'type'    => 'string',
			'default' => 'side',
		);
		$args['attributes']['frblArrowLeftUrl']     = array(
			'type'    => 'string',
			'default' => '',
		);
		$args['attributes']['frblArrowRightUrl']    = array(
			'type'    => 'string',
			'default' => '',
		);
		$args['attributes']['frblDisableOnDesktop'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $args;
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
				'frontblocks/grid-attributes',
				function( settings, name ) {
					if ( name !== 'generateblocks/grid' && name !== 'generateblocks/element' && name !== 'core/group' && name !== 'core/query' ) {
						return settings;
					}

					settings.attributes = {
						...settings.attributes,
						frblGridOption: {
							type: 'string',
							default: 'none'
						},
						frblItemsToView: {
							type: 'string',
							default: '4'
						},
						frblLaptopToView: {
							type: 'string',
							default: '3'
						},
						frblTabletToView: {
							type: 'string',
							default: '2'
						},
						frblResponsiveToView: {
							type: 'string',
							default: '1'
						},
						frblAutoplay: {
							type: 'string',
							default: ''
						},
						frblGap: {
							type: 'string',
							default: '20'
						},
						frblButtons: {
							type: 'string',
							default: 'arrows'
						},
						frblButtonsPosition: {
							type: 'string',
							default: 'side'
						},
						frblButtonColor: {
							type: 'string',
							default: ''
						},
						frblButtonBgColor: {
							type: 'string',
							default: ''
						},
						frblArrowLeftUrl: {
							type: 'string',
							default: ''
						},
						frblArrowRightUrl: {
							type: 'string',
							default: ''
						},
					frblRewind: {
						type: 'boolean',
						default: true
					},
					frblDisableOnDesktop: {
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
