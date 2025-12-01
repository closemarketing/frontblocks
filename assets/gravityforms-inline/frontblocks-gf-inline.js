/**
 * FrontBlocks - Gravity Forms Inline Layout Runtime
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 */

(function() {
	'use strict';

	/**
	 * Initialize inline layout for Gravity Forms.
	 */
	function initGfInlineLayout() {
		// Find all inline form blocks (multiple class options).
		const gfInlineBlocks = document.querySelectorAll('.frontblocks-gf-inline, .frontblocks-gf-inline-wrapper');

		gfInlineBlocks.forEach(function(block) {
			const gap = block.getAttribute('data-gf-inline-gap') || 10;
			
			// Set CSS custom property for gap.
			block.style.setProperty('--gf-inline-gap', gap + 'px');
		});
	}

	// Initialize on DOM ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initGfInlineLayout);
	} else {
		initGfInlineLayout();
	}

	// Re-initialize after Gravity Forms AJAX submission.
	document.addEventListener('gform_post_render', initGfInlineLayout);

	// Also initialize with jQuery if available (for compatibility).
	if (typeof jQuery !== 'undefined') {
		jQuery(document).on('gform_post_render', initGfInlineLayout);
	}

})();

