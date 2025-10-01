<?php
/**
 * Headline module for FrontBlocks (GenerateBlocks Headline enhancements).
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
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
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ), 100 );
		add_filter( 'generateblocks_attr_headline', array( $this, 'add_line_class_attribute' ), 10 );
	}

	/**
	 * Registra los scripts y estilos necesarios.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_enqueue_style( 'frontblocks-headline-styles', FRBL_PLUGIN_URL . 'assets/headline/frontblocks-headline.css', array(), FRBL_VERSION );

		$asset_file = array(
			'dependencies' => array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-compose',
				'wp-hooks',
				'wp-data',
			),
			'version'      => FRBL_VERSION,
		);

		wp_enqueue_script( 'frontblocks-headline-editor', FRBL_PLUGIN_URL . 'assets/headline/frontblocks-headline.js', $asset_file['dependencies'], $asset_file['version'], true );
	}

	/**
	 * Mantiene el filtro de atributos de GenerateBlocks, sin lógica de color.
	 * Se renombra a add_line_class_attribute por claridad.
	 *
	 * @param array $attributes Array de atributos actuales de la etiqueta.
	 * @return array Atributos modificados.
	 */
	public function add_line_class_attribute( $attributes ) {
		return $attributes;
	}
}
