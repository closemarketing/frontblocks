<?php
/**
 * MCP Abilities module for FrontBlocks.
 *
 * Exposes maintenance mode as an ability via WordPress's Abilities API
 * (`wp_register_ability()`), so an MCP client — such as the mcp-adapter
 * bridge — can read and flip it during a site's go-live checklist instead
 * of requiring a manual toggle in the FrontBlocks settings screen.
 *
 * Cookie consent is intentionally not covered here: FrontBlocks' own Cookie
 * Notice runtime has been removed in favor of the standalone FrontConsent
 * plugin (see AGENTS.md), so an ability for it belongs in FrontConsent's own
 * codebase, not here.
 *
 * The Abilities API ships in WordPress core as of 6.9.0. On older sites it is
 * only available when a plugin providing it (e.g. mcp-adapter) is active, so
 * every entry point here is guarded with `function_exists()` and becomes a
 * no-op otherwise — registering nothing is the safe failure mode, not a
 * fatal error.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * McpAbilities class.
 *
 * @since 1.6.0
 */
class McpAbilities {

	/**
	 * Ability category slug abilities in this class are registered under.
	 *
	 * @var string
	 */
	const CATEGORY = 'frontblocks';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the FrontBlocks ability category.
	 *
	 * @return void
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'FrontBlocks', 'frontblocks' ),
				'description' => __( 'Site-wide controls provided by the FrontBlocks plugin.', 'frontblocks' ),
			)
		);
	}

	/**
	 * Register the maintenance mode ability.
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'frontblocks/maintenance-mode',
			array(
				'label'               => __( 'FrontBlocks Maintenance Mode', 'frontblocks' ),
				'description'         => __( 'Reads or sets whether FrontBlocks\' maintenance mode is on, and reports whether WooCommerce\'s own "Coming soon" site visibility is independently restricting front-end access.', 'frontblocks' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'enabled' => array(
							'type'        => 'boolean',
							'description' => __( 'Pass true or false to turn FrontBlocks maintenance mode on or off. Omit this property to only read the current state.', 'frontblocks' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'enabled'               => array(
							'type'        => 'boolean',
							'description' => __( 'Whether FrontBlocks maintenance mode is on after this call.', 'frontblocks' ),
						),
						'woocommerceComingSoon' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether WooCommerce\'s own "Coming soon" site visibility setting is active, independently of FrontBlocks maintenance mode.', 'frontblocks' ),
						),
					),
					'required'   => array( 'enabled', 'woocommerceComingSoon' ),
				),
				'execute_callback'    => array( $this, 'execute_maintenance_mode' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => array(
					'public'      => true,
					'annotations' => array(
						'destructive' => false,
						'idempotent'  => true,
					),
				),
			)
		);
	}

	/**
	 * Permission check for the maintenance mode ability: site settings, admin-only.
	 *
	 * @return bool
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Execute callback for the `frontblocks/maintenance-mode` ability.
	 *
	 * @param array $input Ability input. May contain a boolean `enabled` key.
	 * @return array
	 */
	public function execute_maintenance_mode( $input ) {
		if ( isset( $input['enabled'] ) ) {
			Maintenance::set_enabled( (bool) $input['enabled'] );
		}

		return array(
			'enabled'               => Maintenance::is_enabled(),
			'woocommerceComingSoon' => 'yes' === get_option( 'woocommerce_coming_soon', 'no' ),
		);
	}
}
