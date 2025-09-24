<?php
/**
 * Headline Addon for GenerateBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Headline class.
 *
 * @since 1.1.0
 */
class Headline {

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
		add_action( 'init', array( $this, 'register_headline_attributes' ), 9 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'add_editor_inline_script' ), 15 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ), 100 );

		// Add line after Headline block on render when enabled.
		add_filter( 'render_block_generateblocks/headline', array( $this, 'maybe_append_afterline' ), 10, 2 );
	}

	/**
	 * Register custom attribute for GenerateBlocks Headline.
	 *
	 * @return void
	 */
	public function register_headline_attributes() {
		add_filter(
			'generateblocks_blocks_registered_block',
			function ( $block_args, $block_type ) {
				// Only affect GenerateBlocks Headline.
				if ( 'generateblocks/headline' !== $block_type ) {
					return $block_args;
				}

				if ( ! isset( $block_args['attributes'] ) || ! is_array( $block_args['attributes'] ) ) {
					$block_args['attributes'] = array();
				}

				// Boolean attribute to toggle the after line.
				$block_args['attributes']['frblHeadlineAfterline'] = array(
					'type'    => 'boolean',
					'default' => false,
				);

				return $block_args;
			},
			10,
			2
		);
	}

	/**
	 * Inject editor inline script to add attribute (client side) and a toggle in the Effects panel.
	 *
	 * @return void
	 */
	public function add_editor_inline_script() {
		wp_add_inline_script(
			'wp-blocks',
			"
			( function() {
				var hooks = wp.hooks;
				var element = wp.element;
				var components = wp.components;
				var blockEditor = wp.blockEditor || wp.editor; // Back-compat.

				// Ensure attribute exists client-side.
				hooks.addFilter(
					'blocks.registerBlockType',
					'frontblocks/headline-afterline-attr',
					function( settings, name ) {
						if ( name !== 'generateblocks/headline' ) {
							return settings;
						}

						settings.attributes = Object.assign({}, settings.attributes || {}, {
							frblHeadlineAfterline: {
								type: 'boolean',
								default: false
							}
						});

						return settings;
					}
				);

				// Add a ToggleControl in the Inspector under Effects.
				var createHigherOrderComponent = wp.compose.createHigherOrderComponent;

				var withHeadlineAfterlineControl = createHigherOrderComponent( function( BlockEdit ) {
					return function( props ) {
						if ( props.name !== 'generateblocks/headline' ) {
							return element.createElement( BlockEdit, props );
						}

						var attrs = props.attributes || {};
						var enabled = !!attrs.frblHeadlineAfterline;

						return element.createElement(
							element.Fragment,
							null,
							element.createElement( BlockEdit, props ),
							element.createElement(
								blockEditor.InspectorControls,
								null,
								element.createElement(
									components.PanelBody,
									{ title: 'Effects', initialOpen: false },
									element.createElement( components.ToggleControl, {
										label: 'Line after block',
										checked: enabled,
										onChange: function( value ) { props.setAttributes( { frblHeadlineAfterline: !!value } ); }
									} )
								)
							)
						);
					};
				}, 'withHeadlineAfterlineControl' );

				hooks.addFilter( 'editor.BlockEdit', 'frontblocks/headline-afterline-control', withHeadlineAfterlineControl );
			} )();
			"
		);
	}

	/**
	 * Enqueue minimal frontend styles for the after line element.
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		// Register empty style handle and add inline CSS.
		if ( ! wp_style_is( 'frontblocks-headline', 'registered' ) ) {
			wp_register_style( 'frontblocks-headline', false, array(), FRBL_VERSION );
		}

		wp_enqueue_style( 'frontblocks-headline' );

		$css = '.frbl-headline-line{display:block;border-bottom:1px solid currentColor;opacity:.35;margin-top:.5em;}';
		wp_add_inline_style( 'frontblocks-headline', $css );
	}

	/**
	 * Append an after line element when attribute is enabled.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block data.
	 * @return string
	 */
	public function maybe_append_afterline( $block_content, $block ) {
		$attrs   = ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) ? $block['attrs'] : array();
		$enabled = isset( $attrs['frblHeadlineAfterline'] ) ? (bool) $attrs['frblHeadlineAfterline'] : false;

		// Only append when enabled.
		if ( true !== $enabled ) {
			return $block_content;
		}

		// Append a decorative line element after the block markup.
		return $block_content . '<span class="frbl-headline-line" aria-hidden="true"></span>';
	}
}
