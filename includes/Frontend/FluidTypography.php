<?php
/**
 * Fluid Typography module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * FluidTypography class.
 *
 * @since 1.0.0
 */
class FluidTypography {

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->init_hooks();
	}

	/**
	 * Check if module is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return ! empty( $options['enable_fluid_typography'] );
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fluid_typography' ), 999 );
	}

	/**
	 * Enqueue fluid typography styles.
	 *
	 * @return void
	 */
	public function enqueue_fluid_typography() {
		$source_css = $this->get_source_css();

		if ( empty( $source_css ) ) {
			return;
		}

		$fluid_css = $this->convert_to_fluid_typography( $source_css );

		if ( current_user_can( 'manage_options' ) && isset( $_GET['frbl_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action(
				'wp_head',
				function () use ( $fluid_css ) {
					echo "\n<!-- FRBL Fluid Typography DEBUG:\n" . esc_html( $fluid_css ) . "\n-->\n";
				}
			);
		}

		if ( empty( $fluid_css ) ) {
			return;
		}

		$style_handle = $this->get_style_handle();
		wp_add_inline_style( $style_handle, $fluid_css );
	}

	/**
	 * Get CSS source depending on active theme.
	 * Priority: GeneratePress → theme.json global stylesheet → theme stylesheet.
	 *
	 * @return string
	 */
	private function get_source_css(): string {
		// 1. GeneratePress — use its pre-compiled dynamic CSS cache.
		if ( function_exists( 'generate_get_default_color_palettes' ) ) {
			$gp_css = get_option( 'generate_dynamic_css_output', '' );
			if ( ! empty( $gp_css ) ) {
				return $gp_css;
			}
		}

		// 2. Block/FSE themes — compile from theme.json via WP core.
		if ( function_exists( 'wp_get_global_stylesheet' ) ) {
			$global_css = wp_get_global_stylesheet();
			if ( ! empty( $global_css ) ) {
				return $global_css;
			}
		}

		// 3. Classic themes — read the theme's style.css.
		$stylesheet = get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet ) ) {
			$css = file_get_contents( $stylesheet ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( ! empty( $css ) ) {
				return $css;
			}
		}

		return '';
	}

	/**
	 * Return the registered style handle to attach the fluid CSS to.
	 * Falls back to 'wp-block-library' (always enqueued) if the theme handle isn't found.
	 *
	 * @return string
	 */
	private function get_style_handle(): string {
		// GeneratePress.
		if ( wp_style_is( 'generate-style', 'enqueued' ) ) {
			return 'generate-style';
		}

		// Common theme handles.
		$candidates = array( 'parent-style', 'child-style', 'theme-style', 'main-style', get_stylesheet() );
		foreach ( $candidates as $handle ) {
			if ( wp_style_is( $handle, 'enqueued' ) ) {
				return $handle;
			}
		}

		// Universal fallback — always present when blocks are used.
		return 'wp-block-library';
	}

	/**
	 * Convert source CSS to fluid typography using clamp().
	 *
	 * @param string $css Source CSS.
	 * @return string Fluid typography CSS.
	 */
	private function convert_to_fluid_typography( $css ) {
		$fluid_css = '';
		$selectors = array( 'body', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );

		$is_generateblocks = class_exists( 'GenerateBlocks' );

		foreach ( $selectors as $selector ) {
			$fluid_rule = $this->extract_and_convert_selector( $css, $selector, $is_generateblocks );

			if ( ! empty( $fluid_rule ) ) {
				$fluid_css .= $fluid_rule . "\n";
			}
		}

		return $fluid_css;
	}

	/**
	 * Extract font sizes from CSS and convert to fluid typography.
	 *
	 * @param string $css                Source CSS.
	 * @param string $selector           CSS selector (e.g. 'h1').
	 * @param bool   $is_generateblocks  Whether GenerateBlocks is active.
	 * @return string Fluid CSS rule or empty string.
	 */
	private function extract_and_convert_selector( $css, $selector, $is_generateblocks = false ) {
		$sizes = array(
			'desktop' => null,
			'tablet'  => null,
			'mobile'  => null,
		);

		$is_multiple_selector = ( 'body' === $selector );

		// Desktop font-size.
		if ( $is_multiple_selector ) {
			$pattern = '/(?:^|,|\})\s*[^{]*\b' . preg_quote( $selector, '/' ) . '\b[^{]*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/i';
		} else {
			$pattern = '/' . preg_quote( $selector, '/' ) . '\s*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/i';
		}

		if ( preg_match( $pattern, $css, $matches ) ) {
			$sizes['desktop'] = array(
				'value' => floatval( $matches[1] ),
				'unit'  => $matches[2],
			);
		}

		// Tablet font-size — @media (max-width: 1024px).
		if ( $is_multiple_selector ) {
			$pattern_tablet = '/@media[^{]*max-width:\s*1024px[^{]*\{[^@]*\b' . preg_quote( $selector, '/' ) . '\b[^{]*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/is';
		} else {
			$pattern_tablet = '/@media[^{]*max-width:\s*1024px[^{]*\{[^}]*' . preg_quote( $selector, '/' ) . '\s*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/is';
		}

		if ( preg_match( $pattern_tablet, $css, $matches ) ) {
			$sizes['tablet'] = array(
				'value' => floatval( $matches[1] ),
				'unit'  => $matches[2],
			);
		}

		// Mobile font-size — @media (max-width: 768px).
		if ( $is_multiple_selector ) {
			$pattern_mobile = '/@media[^{]*max-width:\s*768px[^{]*\{[^@]*\b' . preg_quote( $selector, '/' ) . '\b[^{]*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/is';
		} else {
			$pattern_mobile = '/@media[^{]*max-width:\s*768px[^{]*\{[^}]*' . preg_quote( $selector, '/' ) . '\s*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/is';
		}

		if ( preg_match( $pattern_mobile, $css, $matches ) ) {
			$sizes['mobile'] = array(
				'value' => floatval( $matches[1] ),
				'unit'  => $matches[2],
			);
		}

		if ( ! $sizes['desktop'] || ! $sizes['mobile'] ) {
			return '';
		}

		if ( $sizes['desktop']['unit'] !== $sizes['mobile']['unit'] ) {
			return '';
		}

		$min_size = $sizes['mobile']['value'];
		$max_size = $sizes['desktop']['value'];
		$unit     = $sizes['desktop']['unit'];

		if ( $min_size === $max_size ) {
			return '';
		}

		$viewport_start = 320;
		$viewport_end   = 1440;
		$viewport_diff  = $viewport_end - $viewport_start;

		$fluid_calc = sprintf(
			'calc(%1$s%2$s + (%3$s - %1$s) * ((100vw - %4$spx) / %5$s))',
			$min_size,
			$unit,
			$max_size,
			$viewport_start,
			$viewport_diff
		);

		$clamp_rule = sprintf(
			'clamp(%1$s%2$s, %3$s, %4$s%2$s)',
			$min_size,
			$unit,
			$fluid_calc,
			$max_size
		);

		if ( in_array( $selector, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			$rule = sprintf( "body %s {\n\tfont-size: %s !important;\n}", $selector, $clamp_rule );

			// Add GenerateBlocks-specific selector only when GB is active.
			if ( $is_generateblocks ) {
				$rule .= sprintf( "\nbody %s.gb-headline {\n\tfont-size: %s !important;\n}", $selector, $clamp_rule );
			}
		} else {
			$rule = sprintf( "%s {\n\tfont-size: %s !important;\n}", $selector, $clamp_rule );

			if ( 'body' === $selector && $is_generateblocks ) {
				$rule .= sprintf( "\np.gb-headline-text {\n\tfont-size: %s !important;\n}", $clamp_rule );
			}
		}

		return $rule;
	}
}
