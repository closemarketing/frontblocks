<?php
/**
 * Before After Block
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the Before After comparison block.
 */
class BeforeAfter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register block scripts, styles and the block type itself.
	 *
	 * @return void
	 */
	public function register_block() {
		wp_register_script(
			'frontblocks-before-after-editor',
			FRBL_PLUGIN_URL . 'blocks/before-after/edit.js',
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

		wp_register_script(
			'frontblocks-before-after-view',
			FRBL_PLUGIN_URL . 'blocks/before-after/view.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_register_style(
			'frontblocks-before-after-style',
			FRBL_PLUGIN_URL . 'blocks/before-after/style.css',
			array(),
			FRBL_VERSION
		);

		register_block_type(
			FRBL_PLUGIN_PATH . 'blocks/before-after',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render_block( $attributes ) {
		$before_url = isset( $attributes['beforeImageUrl'] ) ? $attributes['beforeImageUrl'] : '';
		$after_url  = isset( $attributes['afterImageUrl'] ) ? $attributes['afterImageUrl'] : '';

		if ( empty( $before_url ) || empty( $after_url ) ) {
			return '';
		}

		$initial_position = isset( $attributes['initialPosition'] ) ? (int) $attributes['initialPosition'] : 50;
		$before_label     = isset( $attributes['beforeLabel'] ) ? $attributes['beforeLabel'] : __( 'Before', 'frontblocks' );
		$after_label      = isset( $attributes['afterLabel'] ) ? $attributes['afterLabel'] : __( 'After', 'frontblocks' );
		$align            = isset( $attributes['align'] ) ? sanitize_html_class( 'align' . $attributes['align'] ) : '';

		$classes = trim( 'wp-block-frontblocks-before-after frbl-before-after ' . $align );

		ob_start();
		?>
		<div
			class="<?php echo esc_attr( $classes ); ?>"
			data-initial-position="<?php echo esc_attr( $initial_position ); ?>"
		>
			<div class="frbl-before-after__after">
				<img src="<?php echo esc_url( $after_url ); ?>" alt="" loading="lazy">
				<span class="frbl-before-after__label frbl-before-after__label--after">
					<?php echo wp_kses_post( $after_label ); ?>
				</span>
			</div>
			<div class="frbl-before-after__before">
				<img src="<?php echo esc_url( $before_url ); ?>" alt="" loading="lazy">
				<span class="frbl-before-after__label frbl-before-after__label--before">
					<?php echo wp_kses_post( $before_label ); ?>
				</span>
			</div>
			<div
				class="frbl-before-after__handle"
				role="slider"
				tabindex="0"
				aria-valuemin="0"
				aria-valuemax="100"
				aria-valuenow="<?php echo esc_attr( $initial_position ); ?>"
				aria-label="<?php esc_attr_e( 'Drag to compare images', 'frontblocks' ); ?>"
			>
				<span class="frbl-before-after__handle-line"></span>
				<span class="frbl-before-after__handle-thumb">
					<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="15 18 9 12 15 6"></polyline>
						<polyline points="9 18 3 12 9 6"></polyline>
						<polyline points="15 18 21 12 15 6"></polyline>
						<polyline points="9 18 15 12 9 6"></polyline>
					</svg>
				</span>
				<span class="frbl-before-after__handle-line"></span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
