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
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_block( $attrs ) {
		$pattern         = isset( $attrs['textPattern'] ) ? $attrs['textPattern'] : '';
		$tag             = isset( $attrs['htmlTag'] ) ? $attrs['htmlTag'] : 'p';
		$text_color      = isset( $attrs['textColor'] ) ? $attrs['textColor'] : '';
		$hover_color     = isset( $attrs['hoverTextColor'] ) ? $attrs['hoverTextColor'] : '';
		$font_size       = isset( $attrs['fontSize'] ) ? $attrs['fontSize'] : '';
		$font_family     = isset( $attrs['fontFamily'] ) ? $attrs['fontFamily'] : '';
		$font_weight     = isset( $attrs['fontWeight'] ) ? $attrs['fontWeight'] : '';
		$text_align      = isset( $attrs['textAlign'] ) ? $attrs['textAlign'] : '';
		$logged_out_text = isset( $attrs['loggedOutText'] ) ? $attrs['loggedOutText'] : '';

		// Allowed HTML tags.
		$allowed_tags = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div' );
		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'p';
		}

		if ( ! is_user_logged_in() ) {
			if ( '' === $logged_out_text ) {
				return '';
			}
			$pattern = $logged_out_text;
		} else {
			$pattern = $this->replace_placeholders( $pattern );
		}

		$style_parts = array();
		if ( '' !== $text_color ) {
			$style_parts[] = 'color:' . sanitize_hex_color( $text_color );
		}
		if ( '' !== $font_size ) {
			$style_parts[] = 'font-size:' . esc_attr( $font_size );
		}
		if ( '' !== $font_family ) {
			$style_parts[] = 'font-family:' . esc_attr( $font_family );
		}
		if ( '' !== $font_weight ) {
			$style_parts[] = 'font-weight:' . esc_attr( $font_weight );
		}
		if ( '' !== $text_align ) {
			$style_parts[] = 'text-align:' . esc_attr( $text_align );
		}

		$style_attr  = ! empty( $style_parts ) ? ' style="' . implode( ';', $style_parts ) . '"' : '';
		$hover_style = '';
		$uid_class   = '';

		if ( '' !== $hover_color ) {
			$uid         = 'frbl-ut-' . substr( md5( wp_json_encode( $attrs ) ), 0, 8 );
			$hover_style = '<style>.frbl-user-text.' . esc_attr( $uid ) . ':hover{color:' . sanitize_hex_color( $hover_color ) . '}</style>';
			$uid_class   = ' ' . $uid;
		}

		return $hover_style . sprintf(
			'<%1$s class="frbl-user-text%4$s"%2$s>%3$s</%1$s>',
			$tag,
			$style_attr,
			wp_kses_post( $pattern ),
			$uid_class
		);
	}

	/**
	 * Replace pattern placeholders with current user data.
	 *
	 * @param string $pattern Text pattern.
	 * @return string
	 */
	private function replace_placeholders( $pattern ) {
		$user   = wp_get_current_user();
		$nombre = $user->first_name ? $user->first_name : $user->display_name;

		$map = array(
			'{nombre}'       => $nombre,
			'{apellido}'     => $user->last_name,
			'{display_name}' => $user->display_name,
			'{email}'        => $user->user_email,
			'{username}'     => $user->user_login,
			'{bio}'          => get_user_meta( $user->ID, 'description', true ),
			'{web}'          => $user->user_url,
		);

		return str_replace( array_keys( $map ), array_values( $map ), $pattern );
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
