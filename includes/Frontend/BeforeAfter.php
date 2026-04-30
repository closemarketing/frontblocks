<?php
/**
 * Before After Block module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

use WP_Block_Type_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * BeforeAfter class.
 *
 * @since 1.0.0
 */
class BeforeAfter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_before_after_block' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		wp_register_style(
			'frontblocks-before-after-style',
			FRBL_PLUGIN_URL . 'assets/before-after/frontblocks-before-after.css',
			array(),
			FRBL_VERSION
		);

		wp_register_script(
			'frontblocks-before-after-frontend',
			FRBL_PLUGIN_URL . 'assets/before-after/frontblocks-before-after-frontend.js',
			array(),
			FRBL_VERSION,
			true
		);

		if ( is_admin() || has_block( 'frontblocks/before-after' ) ) {
			wp_enqueue_style( 'frontblocks-before-after-style' );
			wp_enqueue_script( 'frontblocks-before-after-frontend' );
		}
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_style(
			'frontblocks-before-after-style',
			FRBL_PLUGIN_URL . 'assets/before-after/frontblocks-before-after.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-before-after-option',
			FRBL_PLUGIN_URL . 'assets/before-after/frontblocks-before-after.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Register the Before After block.
	 *
	 * @return void
	 */
	public function register_before_after_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$args = array(
			'editor_script'   => 'frontblocks-before-after-option',
			'render_callback' => array( $this, 'render_before_after_block' ),
			'attributes'      => array(
				'beforeImageId'   => array(
					'type' => 'integer',
				),
				'beforeImageUrl'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'afterImageId'    => array(
					'type' => 'integer',
				),
				'afterImageUrl'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'beforeLabel'     => array(
					'type'    => 'string',
					'default' => 'Before',
				),
				'afterLabel'      => array(
					'type'    => 'string',
					'default' => 'After',
				),
				'initialPosition' => array(
					'type'    => 'number',
					'default' => 50,
				),
				'fixedHeight'     => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'blockHeight'     => array(
					'type'    => 'number',
					'default' => 400,
				),
			),
		);

		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'frontblocks/before-after' ) ) {
			register_block_type( 'frontblocks/before-after', $args );
		}
	}

	/**
	 * Render the Before After block on the frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_before_after_block( $attributes ) {
		$before_url = isset( $attributes['beforeImageUrl'] ) ? $attributes['beforeImageUrl'] : '';
		$after_url  = isset( $attributes['afterImageUrl'] ) ? $attributes['afterImageUrl'] : '';

		if ( empty( $before_url ) || empty( $after_url ) ) {
			return '';
		}

		$initial_position = isset( $attributes['initialPosition'] ) ? (int) $attributes['initialPosition'] : 50;
		$before_label     = isset( $attributes['beforeLabel'] ) ? $attributes['beforeLabel'] : __( 'Before', 'frontblocks' );
		$after_label      = isset( $attributes['afterLabel'] ) ? $attributes['afterLabel'] : __( 'After', 'frontblocks' );

		$fixed_height  = ! empty( $attributes['fixedHeight'] );
		$block_height  = isset( $attributes['blockHeight'] ) ? (int) $attributes['blockHeight'] : 400;

		$wrapper_class = 'frbl-before-after';
		if ( $fixed_height ) {
			$wrapper_class .= ' frbl-before-after--fixed-height';
		}
		if ( ! empty( $attributes['className'] ) ) {
			$wrapper_class .= ' ' . esc_attr( $attributes['className'] );
		}

		$wrapper_style = $fixed_height && $block_height ? ' style="height:' . esc_attr( $block_height ) . 'px"' : '';

		ob_start();
		?>
		<div
			class="<?php echo esc_attr( $wrapper_class ); ?>"
			data-initial-position="<?php echo esc_attr( $initial_position ); ?>"
			<?php echo $wrapper_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
					<svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M7 4l-4 6 4 6M13 4l4 6-4 6"/>
					</svg>
				</span>
				<span class="frbl-before-after__handle-line"></span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
