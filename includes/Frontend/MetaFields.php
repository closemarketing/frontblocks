<?php
/**
 * Meta Fields — Dynamic block bindings with editor UI.
 *
 * Fields are stored in the `frbl_meta_fields_config` option as a flat array
 * of { post_type, key, label, type }. A REST endpoint lets the editor
 * register new fields on the fly without touching PHP files.
 *
 * Block bindings source: `frontblocks/post-meta`
 * Bindable blocks: core/paragraph (content), core/heading (content)
 *
 * @package FrontBlocks
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaFields
 */
class MetaFields {

	const OPTION_KEY = 'frbl_meta_fields_config';

	/**
	 * All registered fields (stored + filter).
	 *
	 * @var array<int, array<string, string>>
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
		add_action( 'init', array( $this, 'load_and_register' ), 20 );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'render_block', array( $this, 'render_block_with_meta' ), 10, 3 );
	}

	/**
	 * Load fields from option + developer filter, then register everything.
	 */
	public function load_and_register(): void {
		$stored = (array) get_option( self::OPTION_KEY, [] );

		// Merge fields from developer filter (backward compat).
		$filter_fields = (array) apply_filters( 'frbl_meta_fields', [] );
		foreach ( $filter_fields as $post_type => $type_fields ) {
			foreach ( (array) $type_fields as $field ) {
				if ( ! empty( $field['key'] ) ) {
					$stored[] = array_merge( $field, [ 'post_type' => (string) $post_type ] );
				}
			}
		}

		// Deduplicate by post_type + key.
		$seen         = [];
		$this->fields = [];
		foreach ( $stored as $field ) {
			$id = ( $field['post_type'] ?? '' ) . '|' . ( $field['key'] ?? '' );
			if ( isset( $seen[ $id ] ) || empty( $field['key'] ) || empty( $field['post_type'] ) ) {
				continue;
			}
			$seen[ $id ]    = true;
			$this->fields[] = $field;
		}

		$this->register_post_meta_fields();
	}

	/**
	 * Frontend: replace block content with live meta value.
	 *
	 * Reads metadata.frblMeta (our namespace, not WP block bindings) so the
	 * editor never shows the binding-source label — only the real value.
	 *
	 * @param  string    $block_content  Rendered HTML.
	 * @param  array     $parsed_block   Parsed block data.
	 * @param  \WP_Block $block_instance Block instance (has context for query loops).
	 * @return string
	 */
	public function render_block_with_meta( string $block_content, array $parsed_block, \WP_Block $block_instance ): string {
		$frbl_meta = $parsed_block['attrs']['metadata']['frblMeta'] ?? null;

		if ( empty( $frbl_meta ) || ! is_array( $frbl_meta ) ) {
			return $block_content;
		}

		// Resolve post ID — query loops provide context; singles fall back to global $post.
		$post_id = isset( $block_instance->context['postId'] ) ? (int) $block_instance->context['postId'] : 0;

		if ( ! $post_id ) {
			global $post;
			$post_id = isset( $post->ID ) ? (int) $post->ID : (int) get_the_ID();
		}

		if ( ! $post_id ) {
			return $block_content;
		}

		$block_name = $parsed_block['blockName'] ?? '';

		foreach ( $frbl_meta as $attr_name => $binding ) {
			$key = $binding['key'] ?? '';
			if ( ! $key ) {
				continue;
			}

			$value = get_post_meta( $post_id, $key, true );
			if ( '' === $value ) {
				continue;
			}

			$block_content = $this->inject_value( $block_content, $block_name, $attr_name, (string) $value );
		}

		return $block_content;
	}

	/**
	 * Replace the inner HTML of the matching tag.
	 *
	 * @param  string $html        Block HTML.
	 * @param  string $block_name  Block name (core/paragraph, core/heading).
	 * @param  string $attr_name   Attribute being replaced (content).
	 * @param  string $value       Meta value.
	 * @return string
	 */
	private function inject_value( string $html, string $block_name, string $attr_name, string $value ): string {
		if ( 'content' !== $attr_name ) {
			return $html;
		}

		$safe = wp_kses_post( $value );

		if ( 'core/paragraph' === $block_name ) {
			return preg_replace( '/(<p[^>]*>).*?(<\/p>)/s', '$1' . $safe . '$2', $html, 1 ) ?? $html;
		}

		if ( 'core/heading' === $block_name ) {
			return preg_replace( '/(<h[1-6][^>]*>).*?(<\/h[1-6]>)/s', '$1' . $safe . '$2', $html, 1 ) ?? $html;
		}

		return $html;
	}

	/**
	 * Register post meta for REST API access.
	 */
	private function register_post_meta_fields(): void {
		foreach ( $this->fields as $field ) {
			register_post_meta(
				$field['post_type'],
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

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'frontblocks/v1',
			'/save-meta',
			[
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_save_meta' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => [
					'post_id' => [
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
					'key'     => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					],
					'value'   => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			'frontblocks/v1',
			'/meta-fields',
			[
				[
					'methods'             => 'GET',
					'callback'            => array( $this, 'rest_get_fields' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
					'args'                => [
						'post_type' => [
							'required'          => false,
							'sanitize_callback' => 'sanitize_key',
						],
					],
				],
				[
					'methods'             => 'POST',
					'callback'            => array( $this, 'rest_register_field' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
					'args'                => [
						'post_type' => [
							'required'          => true,
							'sanitize_callback' => 'sanitize_key',
						],
						'key'       => [
							'required'          => true,
							'sanitize_callback' => 'sanitize_key',
						],
						'label'     => [
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						],
						'type'      => [
							'required'          => false,
							'sanitize_callback' => 'sanitize_key',
							'default'           => 'text',
						],
					],
				],
			]
		);
	}

	/**
	 * REST: GET /frontblocks/v1/meta-fields
	 *
	 * @param  \WP_REST_Request $request  Request.
	 * @return \WP_REST_Response
	 */
	public function rest_get_fields( \WP_REST_Request $request ): \WP_REST_Response {
		$post_type = $request->get_param( 'post_type' );
		$fields    = $this->fields;

		if ( $post_type ) {
			$fields = array_values(
				array_filter( $fields, fn( $f ) => ( $f['post_type'] ?? '' ) === $post_type )
			);
		}

		return rest_ensure_response( $fields );
	}

	/**
	 * REST: POST /frontblocks/v1/meta-fields
	 *
	 * @param  \WP_REST_Request $request  Request.
	 * @return \WP_REST_Response
	 */
	public function rest_register_field( \WP_REST_Request $request ): \WP_REST_Response {
		$new_field = [
			'post_type' => $request->get_param( 'post_type' ),
			'key'       => $request->get_param( 'key' ),
			'label'     => $request->get_param( 'label' ),
			'type'      => $request->get_param( 'type' ) ?: 'text',
		];

		$stored = (array) get_option( self::OPTION_KEY, [] );

		// Return existing if already registered.
		foreach ( $stored as $existing ) {
			if ( ( $existing['key'] ?? '' ) === $new_field['key'] && ( $existing['post_type'] ?? '' ) === $new_field['post_type'] ) {
				return rest_ensure_response( [ 'success' => true, 'field' => $existing, 'existing' => true ] );
			}
		}

		$stored[] = $new_field;
		update_option( self::OPTION_KEY, $stored );

		// Register immediately so the meta is writable in this same request cycle.
		register_post_meta(
			$new_field['post_type'],
			$new_field['key'],
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => $this->wp_meta_type( $new_field['type'] ),
				'label'         => $new_field['label'],
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);

		return rest_ensure_response( [ 'success' => true, 'field' => $new_field ] );
	}

	/**
	 * REST: POST /frontblocks/v1/save-meta
	 *
	 * @param  \WP_REST_Request $request  Request.
	 * @return \WP_REST_Response
	 */
	public function rest_save_meta( \WP_REST_Request $request ): \WP_REST_Response {
		$post_id = $request->get_param( 'post_id' );
		$key     = $request->get_param( 'key' );
		$value   = $request->get_param( 'value' );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'error' => 'Forbidden' ], 403 );
		}

		update_post_meta( $post_id, $key, $value );
		clean_post_cache( $post_id );

		return rest_ensure_response( [ 'success' => true ] );
	}

	/**
	 * Enqueue the editor plugin script.
	 */
	public function enqueue_editor_assets(): void {
		wp_enqueue_script(
			'frontblocks-meta-fields-editor',
			FRBL_PLUGIN_URL . 'assets/meta-fields/frontblocks-meta-fields-editor.js',
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-hooks', 'wp-compose', 'wp-i18n', 'wp-api-fetch' ],
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-meta-fields-editor',
			'frblMetaConfig',
			[
				'sourceKey' => 'frontblocks/post-meta',
			]
		);

		wp_add_inline_style(
			'wp-components',
			'.components-toolbar-group { padding-top: 4px; padding-bottom: 4px; }'
		);
	}

	/**
	 * Map field type to WordPress meta type.
	 *
	 * @param  string $type  FrontBlocks type.
	 * @return string
	 */
	private function wp_meta_type( string $type ): string {
		$map = [
			'number'   => 'number',
			'image'    => 'string',
			'url'      => 'string',
			'textarea' => 'string',
		];

		return $map[ $type ] ?? 'string';
	}
}
