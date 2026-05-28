<?php
/**
 * User Text block for FrontBlocks.
 *
 * Renders a text pattern with logged-in user data substituted for placeholders.
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * UserText class.
 *
 * @since 1.4.0
 */
class UserText {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register the block type with a server-side render callback.
	 *
	 * @return void
	 */
	public function register_block() {
		wp_register_script(
			'frontblocks-user-text-editor',
			FRBL_PLUGIN_URL . 'assets/user-text/frontblocks-user-text.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
			),
			FRBL_VERSION,
			true
		);

		wp_register_style(
			'frontblocks-user-text-editor',
			FRBL_PLUGIN_URL . 'assets/user-text/frontblocks-user-text.css',
			array(),
			FRBL_VERSION
		);

		register_block_type(
			'frontblocks/user-text',
			array(
				'editor_script'   => 'frontblocks-user-text-editor',
				'editor_style'    => 'frontblocks-user-text-editor',
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'textPattern'    => array(
						'type'    => 'string',
						'default' => '',
					),
					'htmlTag'        => array(
						'type'    => 'string',
						'default' => 'p',
					),
					'textColor'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'hoverTextColor' => array(
						'type'    => 'string',
						'default' => '',
					),
					'fontSize'       => array(
						'type'    => 'string',
						'default' => '',
					),
					'fontFamily'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'fontWeight'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'textAlign'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'loggedOutText'  => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * Render the block on the frontend.
	 *
	 * Actual rendering is handled by FrontBlocks Pro via the frbl_user_text_render filter.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_block( $attrs ) {
		return apply_filters( 'frbl_user_text_render', '', $attrs );
	}

	/**
	 * Enqueue editor-only assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-user-text-editor' );
		wp_enqueue_style( 'frontblocks-user-text-editor' );

		wp_set_script_translations(
			'frontblocks-user-text-editor',
			'frontblocks'
		);
	}
}
