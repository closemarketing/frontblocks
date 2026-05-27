<?php
/**
 * Meta Fields — Block Bindings source for dynamic meta in native blocks.
 *
 * Developers register fields via the `frbl_meta_fields` filter:
 *
 *   add_filter( 'frbl_meta_fields', function( $fields ) {
 *       $fields['servicios'] = [
 *           [ 'key' => 'precio',   'label' => 'Precio',    'type' => 'number' ],
 *           [ 'key' => 'duracion', 'label' => 'Duración',  'type' => 'text'   ],
 *           [ 'key' => 'imagen',   'label' => 'Imagen',    'type' => 'image'  ],
 *       ];
 *       return $fields;
 *   } );
 *
 * Supported types: text, textarea, number, url, image.
 * Bindable blocks:  core/paragraph (content), core/heading (content),
 *                   core/image (url, alt), core/button (text, url).
 *
 * @package FrontBlocks
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaFields
 */
class MetaFields {

	/**
	 * Registered fields, keyed by post type.
	 *
	 * @var array<string, array<int, array<string, string>>>
	 */
	private array $fields = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks.
	 */
	private function init_hooks(): void {
		// Load fields after CPTs and plugins have registered.
		add_action( 'init', array( $this, 'load_fields' ), 20 );

		// Register block bindings source and post meta after fields are loaded.
		add_action( 'init', array( $this, 'register_block_bindings_source' ), 30 );
		add_action( 'init', array( $this, 'register_post_meta_fields' ), 30 );

		// Enqueue editor plugin.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Load fields from the filter.
	 */
	public function load_fields(): void {
		$this->fields = (array) apply_filters( 'frbl_meta_fields', [] );
	}

	/**
	 * Register the block bindings source.
	 */
	public function register_block_bindings_source(): void {
		if ( ! function_exists( 'register_block_bindings_source' ) || empty( $this->fields ) ) {
			return;
		}

		register_block_bindings_source(
			'frontblocks/post-meta',
			[
				'label'              => __( 'FrontBlocks Meta Field', 'frontblocks' ),
				'get_value_callback' => array( $this, 'get_binding_value' ),
				'uses_context'       => [ 'postId', 'postType' ],
			]
		);
	}

	/**
	 * Resolve a binding to its actual value.
	 *
	 * @param  array      $source_args     Binding args: key, type.
	 * @param  \WP_Block  $block_instance  Current block instance.
	 * @param  string     $attribute_name  Block attribute being bound.
	 * @return mixed
	 */
	public function get_binding_value( array $source_args, \WP_Block $block_instance, string $attribute_name ) {
		if ( empty( $source_args['key'] ) ) {
			return null;
		}

		$post_id = $block_instance->context['postId'] ?? get_the_ID();

		if ( ! $post_id ) {
			return null;
		}

		$value = get_post_meta( (int) $post_id, sanitize_key( $source_args['key'] ), true );

		if ( '' === $value || false === $value ) {
			return null;
		}

		// Resolve image attachment ID → URL when binding the `url` attribute.
		if ( 'image' === ( $source_args['type'] ?? '' ) && 'url' === $attribute_name && is_numeric( $value ) ) {
			$url = wp_get_attachment_image_url( (int) $value, 'full' );
			return $url ?: null;
		}

		return $value;
	}

	/**
	 * Register post meta for each field so they appear in the REST API
	 * and are editable from the block editor sidebar.
	 */
	public function register_post_meta_fields(): void {
		if ( empty( $this->fields ) ) {
			return;
		}

		foreach ( $this->fields as $post_type => $type_fields ) {
			if ( ! is_array( $type_fields ) ) {
				continue;
			}

			foreach ( $type_fields as $field ) {
				if ( empty( $field['key'] ) ) {
					continue;
				}

				register_post_meta(
					(string) $post_type,
					$field['key'],
					[
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => $this->wp_meta_type( $field['type'] ?? 'text' ),
						'label'         => $field['label'] ?? $field['key'],
						'auth_callback' => function () {
							return current_user_can( 'edit_posts' );
						},
					]
				);
			}
		}
	}

	/**
	 * Enqueue the editor inspector panel script.
	 */
	public function enqueue_editor_assets(): void {
		if ( empty( $this->fields ) ) {
			return;
		}

		// Only load on edit screens for post types that have fields.
		$screen = get_current_screen();
		if ( $screen && 'post' === $screen->base && ! array_key_exists( $screen->post_type, $this->fields ) ) {
			return;
		}

		wp_enqueue_script(
			'frontblocks-meta-fields-editor',
			FRBL_PLUGIN_URL . 'assets/meta-fields/frontblocks-meta-fields-editor.js',
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-hooks', 'wp-compose', 'wp-i18n' ],
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-meta-fields-editor',
			'frblMetaFields',
			[
				'fields'    => $this->fields,
				'sourceKey' => 'frontblocks/post-meta',
			]
		);
	}

	/**
	 * Map field type to a WordPress meta type string.
	 *
	 * @param  string $type  FrontBlocks field type.
	 * @return string
	 */
	private function wp_meta_type( string $type ): string {
		$map = [
			'text'     => 'string',
			'textarea' => 'string',
			'url'      => 'string',
			'image'    => 'string',
			'number'   => 'number',
		];

		return $map[ $type ] ?? 'string';
	}
}
