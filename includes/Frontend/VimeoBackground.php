<?php
/**
 * Vimeo Background block for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

use WP_Block_Type_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * VimeoBackground class.
 */
class VimeoBackground {

	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'frontblocks/vimeo-background' ) ) {
			return;
		}

		register_block_type(
			'frontblocks/vimeo-background',
			array(
				'editor_script'   => 'frontblocks-vimeo-background-editor',
				'render_callback' => array( $this, 'render' ),
				'attributes'      => array(
					'vimeoUrl'        => array( 'type' => 'string',  'default' => '' ),
					'minHeight'       => array( 'type' => 'number',  'default' => 100 ),
					'minHeightUnit'   => array( 'type' => 'string',  'default' => 'vh' ),
					'overlayColor'    => array( 'type' => 'string',  'default' => '#000000' ),
					'overlayOpacity'  => array( 'type' => 'number',  'default' => 0 ),
					'justifyContent'  => array( 'type' => 'string',  'default' => 'stretch' ),
					'alignItems'      => array( 'type' => 'string',  'default' => 'center' ),
					'contentMaxWidth' => array( 'type' => 'string',  'default' => '' ),
					'align'           => array( 'type' => 'string',  'default' => 'full' ),
					'className'       => array( 'type' => 'string',  'default' => '' ),
				),
			)
		);
	}

	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'frontblocks-vimeo-background-editor',
			FRBL_PLUGIN_URL . 'assets/vimeo-background/frontblocks-vimeo-background.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);

		wp_enqueue_style(
			'frontblocks-vimeo-background-style',
			FRBL_PLUGIN_URL . 'assets/vimeo-background/frontblocks-vimeo-background.css',
			array(),
			FRBL_VERSION
		);
	}

	public function enqueue_frontend_assets() {
		if ( ! has_block( 'frontblocks/vimeo-background' ) ) {
			return;
		}

		wp_enqueue_style(
			'frontblocks-vimeo-background-style',
			FRBL_PLUGIN_URL . 'assets/vimeo-background/frontblocks-vimeo-background.css',
			array(),
			FRBL_VERSION
		);
	}

	public function render( $attributes, $content ) {
		$vimeo_url       = sanitize_text_field( $attributes['vimeoUrl'] ?? '' );
		$min_height      = absint( $attributes['minHeight'] ?? 100 );
		$unit            = in_array( $attributes['minHeightUnit'] ?? 'vh', array( 'vh', 'px' ), true ) ? $attributes['minHeightUnit'] : 'vh';
		$overlay_color   = sanitize_hex_color( $attributes['overlayColor'] ?? '#000000' ) ?: '#000000';
		$overlay_opacity   = min( 100, max( 0, absint( $attributes['overlayOpacity'] ?? 0 ) ) );
		$align             = sanitize_text_field( $attributes['align'] ?? 'full' );
		$class_name        = sanitize_text_field( $attributes['className'] ?? '' );
		$justify_content   = sanitize_text_field( $attributes['justifyContent'] ?? 'center' );
		$align_items       = sanitize_text_field( $attributes['alignItems'] ?? 'center' );
		$content_max_width = sanitize_text_field( $attributes['contentMaxWidth'] ?? '' );

		if ( empty( $vimeo_url ) ) {
			return '';
		}

		$video_id = $this->extract_vimeo_id( $vimeo_url );
		if ( ! $video_id ) {
			return '';
		}

		$iframe_src = add_query_arg(
			array(
				'background' => '1',
				'autoplay'   => '1',
				'loop'       => '1',
				'muted'      => '1',
				'title'      => '0',
				'byline'     => '0',
				'portrait'   => '0',
				'dnt'        => '1',
			),
			'https://player.vimeo.com/video/' . $video_id
		);

		$classes        = trim( 'frbl-vimeo-bg' . ( $align ? ' align' . $align : '' ) . ( $class_name ? ' ' . $class_name : '' ) );
		$wrapper_style  = 'min-height:' . $min_height . $unit . ';';

		$content_style  = 'display:flex;flex-direction:column;width:100%;box-sizing:border-box;';
		$content_style .= 'justify-content:' . $align_items . ';';   /* vertical  = main axis */
		$content_style .= 'align-items:' . $justify_content . ';';    /* horizontal = cross axis */
		if ( $content_max_width ) {
			$content_style .= 'max-width:' . $content_max_width . ';margin-left:auto;margin-right:auto;';
		}

		$overlay_html = '';
		if ( $overlay_opacity > 0 ) {
			$overlay_html = sprintf(
				'<div class="frbl-vimeo-bg__overlay" style="background-color:%s;opacity:%s;"></div>',
				esc_attr( $overlay_color ),
				esc_attr( $overlay_opacity / 100 )
			);
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $wrapper_style ); ?>">
			<div class="frbl-vimeo-bg__media">
				<iframe
					class="frbl-vimeo-bg__iframe"
					src="<?php echo esc_url( $iframe_src ); ?>"
					frameborder="0"
					allow="autoplay; fullscreen"
					allowfullscreen
					title=""
					aria-hidden="true"
				></iframe>
			</div>
			<?php echo $overlay_html; ?>
			<div class="frbl-vimeo-bg__content" style="<?php echo esc_attr( $content_style ); ?>">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private function extract_vimeo_id( $url ) {
		if ( preg_match( '/(?:vimeo\.com\/(?:video\/)?|player\.vimeo\.com\/video\/)(\d+)/', $url, $matches ) ) {
			return $matches[1];
		}
		return null;
	}
}
