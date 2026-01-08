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
		// Check if module is enabled.
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
		// Get GeneratePress dynamic CSS output.
		$dynamic_css = get_option( 'generate_dynamic_css_output', '' );

		if ( empty( $dynamic_css ) ) {
			return;
		}

		// Generate fluid typography CSS from dynamic CSS.
		$fluid_css = $this->convert_to_fluid_typography( $dynamic_css );

		// DEBUG: Show generated CSS as HTML comment for admins.
		if ( current_user_can( 'manage_options' ) && isset( $_GET['frbl_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action(
				'wp_head',
				function () use ( $fluid_css ) {
					echo "\n<!-- FRBL Fluid Typography DEBUG:\n" . esc_html( $fluid_css ) . "\n-->\n";
				}
			);
		}

		if ( ! empty( $fluid_css ) ) {
			wp_add_inline_style( 'generate-style', $fluid_css );
		}
	}

	/**
	 * Convert GeneratePress static CSS to fluid typography.
	 *
	 * @param string $css Dynamic CSS from GeneratePress.
	 * @return string Fluid typography CSS.
	 */
	private function convert_to_fluid_typography( $css ) {
		$fluid_css = '';

		// Typography selectors to process - starting with simple ones that definitely work.
		$selectors = array(
			'body',  // Body text (paragraphs inherit from this).
			'h1',    // Heading 1.
			'h2',    // Heading 2.
			'h3',    // Heading 3.
			'h4',    // Heading 4.
			'h5',    // Heading 5.
			'h6',    // Heading 6.
		);

		foreach ( $selectors as $selector ) {
			$fluid_rule = $this->extract_and_convert_selector( $css, $selector );

			if ( ! empty( $fluid_rule ) ) {
				$fluid_css .= $fluid_rule . "\n";
			}
		}

		return $fluid_css;
	}

	/**
	 * Extract font sizes from CSS and convert to fluid typography.
	 *
	 * @param string $css CSS content.
	 * @param string $selector CSS selector (e.g., 'h1').
	 * @return string Fluid CSS rule or empty string.
	 */
	private function extract_and_convert_selector( $css, $selector ) {
		$sizes = array(
			'desktop' => null,
			'tablet'  => null,
			'mobile'  => null,
		);

		// Use different regex patterns depending on the selector.
		// Body is in a multiple selector (body, button, input...), headings are standalone.
		$is_multiple_selector = ( 'body' === $selector );

		// Extract base/desktop font-size.
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

		// Extract tablet font-size from @media (max-width: 1024px).
		if ( $is_multiple_selector ) {
			// Pattern for body in media query.
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

		// Extract mobile font-size from @media (max-width: 768px).
		if ( $is_multiple_selector ) {
			// Pattern for body in media query.
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

		// If we don't have enough data, skip.
		if ( ! $sizes['desktop'] || ! $sizes['mobile'] ) {
			return '';
		}

		// Ensure all units are the same.
		if ( $sizes['desktop']['unit'] !== $sizes['mobile']['unit'] ) {
			return '';
		}

		$min_size = $sizes['mobile']['value'];
		$max_size = $sizes['desktop']['value'];
		$unit     = $sizes['desktop']['unit'];

		// Skip if same values.
		if ( $min_size === $max_size ) {
			return '';
		}

		// Generate fluid typography with clamp().
		// Viewport range: 320px (mobile) to 1440px (desktop).
		$viewport_start = 320;
		$viewport_end   = 1440;
		$viewport_diff  = $viewport_end - $viewport_start;

		// Build clamp() formula.
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

		// Generate CSS rule with appropriate specificity.
		if ( in_array( $selector, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			// For headings, use high specificity to ensure they always win over body classes.
			$rule = sprintf( "body %s,\nbody %s.gb-headline {\n\tfont-size: %s !important;\n}", $selector, $selector, $clamp_rule );
		} else {
			// For body, apply to body and paragraphs with GB classes.
			$rule = sprintf( "%s {\n\tfont-size: %s !important;\n}", $selector, $clamp_rule );
			if ( 'body' === $selector ) {
				$rule .= sprintf( "\np.gb-headline-text {\n\tfont-size: %s !important;\n}", $clamp_rule );
			}
		}

		return $rule;
	}
}
