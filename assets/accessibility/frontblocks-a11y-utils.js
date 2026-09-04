/**
 * Shared, framework-free accessibility check utilities for FrontBlocks
 * block inspector panels (issue #261).
 *
 * These are pure functions with no dependency on `wp.*` globals so they
 * can be unit tested directly with Node's built-in test runner
 * (see tests/js/accessibility-utils.test.js) and reused across any
 * block's InspectorControls.
 *
 * Loaded as a plain global (no build step) so it can be registered as a
 * WordPress script dependency and consumed as `window.FrontBlocksA11y`.
 */
( function ( root, factory ) {
	if ( typeof module === 'object' && module.exports ) {
		module.exports = factory();
	} else {
		root.FrontBlocksA11y = factory();
	}
} )( typeof window !== 'undefined' ? window : this, function () {
	// WCAG 2.2.2 (Pause, Stop, Hide): auto-advancing content that lasts
	// longer than 5 seconds needs a way for the user to pause it.
	var MIN_SAFE_AUTOPLAY_SECONDS = 5;

	// WCAG 1.4.11 (Non-text Contrast): UI components (e.g. carousel
	// arrows/dots) need at least a 3:1 contrast ratio against their
	// background.
	var MIN_NON_TEXT_CONTRAST_RATIO = 3;

	/**
	 * Parse a CSS color string (#rgb, #rrggbb, or rgb()/rgba()) into an
	 * { r, g, b } object. Returns null if the color can't be parsed.
	 *
	 * @param {string} color
	 * @return {{r: number, g: number, b: number}|null}
	 */
	function parseColor( color ) {
		if ( ! color || typeof color !== 'string' ) {
			return null;
		}

		var value = color.trim();

		var hexMatch = value.match( /^#([0-9a-f]{3}|[0-9a-f]{6})$/i );
		if ( hexMatch ) {
			var hex = hexMatch[ 1 ];
			if ( hex.length === 3 ) {
				hex = hex[ 0 ] + hex[ 0 ] + hex[ 1 ] + hex[ 1 ] + hex[ 2 ] + hex[ 2 ];
			}
			return {
				r: parseInt( hex.substring( 0, 2 ), 16 ),
				g: parseInt( hex.substring( 2, 4 ), 16 ),
				b: parseInt( hex.substring( 4, 6 ), 16 ),
			};
		}

		var rgbMatch = value.match( /^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i );
		if ( rgbMatch ) {
			return {
				r: parseInt( rgbMatch[ 1 ], 10 ),
				g: parseInt( rgbMatch[ 2 ], 10 ),
				b: parseInt( rgbMatch[ 3 ], 10 ),
			};
		}

		return null;
	}

	/**
	 * Relative luminance of an sRGB color, per the WCAG 2.x formula.
	 *
	 * @param {{r: number, g: number, b: number}} rgb
	 * @return {number}
	 */
	function relativeLuminance( rgb ) {
		var channels = [ rgb.r, rgb.g, rgb.b ].map( function ( channel ) {
			var srgb = channel / 255;
			return srgb <= 0.03928 ? srgb / 12.92 : Math.pow( ( srgb + 0.055 ) / 1.055, 2.4 );
		} );

		return 0.2126 * channels[ 0 ] + 0.7152 * channels[ 1 ] + 0.0722 * channels[ 2 ];
	}

	/**
	 * WCAG contrast ratio between two colors (1 to 21). Returns null if
	 * either color can't be parsed.
	 *
	 * @param {string} colorA
	 * @param {string} colorB
	 * @return {number|null}
	 */
	function getContrastRatio( colorA, colorB ) {
		var rgbA = parseColor( colorA );
		var rgbB = parseColor( colorB );

		if ( ! rgbA || ! rgbB ) {
			return null;
		}

		var luminanceA = relativeLuminance( rgbA );
		var luminanceB = relativeLuminance( rgbB );
		var lighter = Math.max( luminanceA, luminanceB );
		var darker = Math.min( luminanceA, luminanceB );

		return ( lighter + 0.05 ) / ( darker + 0.05 );
	}

	/**
	 * Check the contrast between a control's foreground and background
	 * color (e.g. carousel arrow color vs. its background) against the
	 * WCAG 1.4.11 non-text contrast threshold (3:1).
	 *
	 * Only applicable (and only warns) when BOTH colors are set — this
	 * plugin never invents a "default" color to audit against, per the
	 * issue's requirement to only check FrontBlocks-owned configuration.
	 *
	 * @param {string|undefined} foreground
	 * @param {string|undefined} background
	 * @return {{applicable: boolean, ratio: number|null, warn: boolean}}
	 */
	function checkControlContrast( foreground, background ) {
		if ( ! foreground || ! background ) {
			return { applicable: false, ratio: null, warn: false };
		}

		var ratio = getContrastRatio( foreground, background );
		if ( null === ratio ) {
			return { applicable: false, ratio: null, warn: false };
		}

		return {
			applicable: true,
			ratio: ratio,
			warn: ratio < MIN_NON_TEXT_CONTRAST_RATIO,
		};
	}

	/**
	 * Parse a value like the Carousel's "Autoplay (seconds)" field, which
	 * is stored as a free-text string, into seconds. Returns 0 when
	 * autoplay is disabled or unparsable.
	 *
	 * @param {string|number} value
	 * @return {number}
	 */
	function parseAutoplaySeconds( value ) {
		var seconds = parseFloat( value );
		return isNaN( seconds ) || seconds <= 0 ? 0 : seconds;
	}

	/**
	 * Check whether auto-advancing content provides a way to pause it,
	 * per WCAG 2.2.2. Advisory only: autoplay with no pause control is
	 * flagged regardless of timing, since even a single un-pausable
	 * advance can be disorienting for some users.
	 *
	 * @param {string|number} autoplayValue Raw autoplay field value (seconds).
	 * @param {boolean}       hasPauseControl Whether a pause-on-hover/focus control is enabled.
	 * @return {{applicable: boolean, warn: boolean}}
	 */
	function checkAutoplayPauseControl( autoplayValue, hasPauseControl ) {
		var seconds = parseAutoplaySeconds( autoplayValue );

		if ( seconds <= 0 ) {
			return { applicable: false, warn: false };
		}

		return { applicable: true, warn: ! hasPauseControl };
	}

	/**
	 * Check whether an autoplay interval is long enough for users to
	 * read and process the content before it advances (WCAG 2.2.2's
	 * 5-second guidance).
	 *
	 * @param {string|number} autoplayValue Raw autoplay field value (seconds).
	 * @return {{applicable: boolean, warn: boolean, seconds: number}}
	 */
	function checkAutoplayTiming( autoplayValue ) {
		var seconds = parseAutoplaySeconds( autoplayValue );

		if ( seconds <= 0 ) {
			return { applicable: false, warn: false, seconds: seconds };
		}

		return {
			applicable: true,
			warn: seconds < MIN_SAFE_AUTOPLAY_SECONDS,
			seconds: seconds,
		};
	}

	/**
	 * Check that an image configured by a FrontBlocks feature (e.g.
	 * Before/After) has an accessible text label. Only applicable when
	 * the image itself is actually set.
	 *
	 * @param {string|undefined} imageUrl
	 * @param {string|undefined} label
	 * @return {{applicable: boolean, warn: boolean}}
	 */
	function checkImageLabel( imageUrl, label ) {
		if ( ! imageUrl ) {
			return { applicable: false, warn: false };
		}

		var trimmedLabel = ( label || '' ).trim();

		return { applicable: true, warn: '' === trimmedLabel };
	}

	return {
		MIN_SAFE_AUTOPLAY_SECONDS: MIN_SAFE_AUTOPLAY_SECONDS,
		MIN_NON_TEXT_CONTRAST_RATIO: MIN_NON_TEXT_CONTRAST_RATIO,
		parseColor: parseColor,
		relativeLuminance: relativeLuminance,
		getContrastRatio: getContrastRatio,
		checkControlContrast: checkControlContrast,
		parseAutoplaySeconds: parseAutoplaySeconds,
		checkAutoplayPauseControl: checkAutoplayPauseControl,
		checkAutoplayTiming: checkAutoplayTiming,
		checkImageLabel: checkImageLabel,
	};
} );
