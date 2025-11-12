/**
 * Container Edge Alignment - Frontend Script
 * 
 * Calculates and applies dynamic margins to containers with edge alignment.
 * This ensures the "other side" stays exactly where it was while one side extends to the edge.
 * 
 * @package FrontBlocks
 */

(function() {
	'use strict';

	/**
	 * Calculate and apply edge alignment margins.
	 */
	function applyEdgeAlignmentMargins() {
		// Get all containers with edge alignment.
		const edgeContainers = document.querySelectorAll('.frbl-edge-left, .frbl-edge-right');
		
		if ( ! edgeContainers.length ) {
			return;
		}

		edgeContainers.forEach( function( container ) {
			// Skip if already processed.
			if ( container.hasAttribute( 'data-frbl-processed' ) ) {
				return;
			}

			// Get the container's original width (before edge alignment).
			// We need to temporarily remove the classes to get the original computed width.
			const hasLeft = container.classList.contains( 'frbl-edge-left' );
			const hasRight = container.classList.contains( 'frbl-edge-right' );
			const hasBoth = hasLeft && hasRight;

			// If both sides, just make it full width.
			if ( hasBoth ) {
				container.style.marginLeft = '0';
				container.style.marginRight = '0';
				container.style.width = '100vw';
				container.style.maxWidth = '100vw';
				container.setAttribute( 'data-frbl-processed', 'true' );
				return;
			}

			// Temporarily remove edge classes to get original width.
			container.classList.remove( 'frbl-edge-left', 'frbl-edge-right' );
			
			// Also temporarily clear any inline styles that might interfere.
			const originalInlineStyles = {
				marginLeft: container.style.marginLeft,
				marginRight: container.style.marginRight,
				width: container.style.width,
				maxWidth: container.style.maxWidth
			};
			container.style.marginLeft = '';
			container.style.marginRight = '';
			container.style.width = '';
			container.style.maxWidth = '';
			
			// Force reflow to get accurate measurements.
			void container.offsetWidth;

			// Get the container's original computed width and margins.
			const computedStyle = window.getComputedStyle( container );
			const originalWidth = parseFloat( computedStyle.maxWidth ) || parseFloat( computedStyle.width );
			const originalMarginLeft = parseFloat( computedStyle.marginLeft );
			const originalMarginRight = parseFloat( computedStyle.marginRight );
			
			// Get viewport width.
			const viewportWidth = window.innerWidth;

			// Restore classes.
			if ( hasLeft ) {
				container.classList.add( 'frbl-edge-left' );
			}
			if ( hasRight ) {
				container.classList.add( 'frbl-edge-right' );
			}

			// IMPORTANT: Only apply edge alignment if viewport is wider than container.
			// On small screens (mobile, tablet), disable the effect to avoid overflow.
			if ( viewportWidth <= originalWidth ) {
				// Viewport is too small, reset to normal centered layout.
				container.style.marginLeft = '';
				container.style.marginRight = '';
				container.style.width = '';
				container.style.maxWidth = '';
				container.setAttribute( 'data-frbl-processed', 'true' );
				return;
			}

			// Calculate the margin that was on each side (when centered with auto).
			// If margins are "auto", they will show as equal values.
			// Formula: (viewport - containerWidth) / 2.
			let sideMargin;
			
			// If margins are already calculated (not auto), use them.
			if ( originalMarginLeft > 0 && originalMarginRight > 0 && Math.abs( originalMarginLeft - originalMarginRight ) < 2 ) {
				// Margins are equal, use the actual value.
				sideMargin = originalMarginLeft;
			} else {
				// Calculate from viewport and width.
				sideMargin = ( viewportWidth - originalWidth ) / 2;
			}

			// Apply the calculated values.
			if ( hasLeft ) {
				// Remove left side, keep right side fixed.
				container.style.marginLeft = '0';
				container.style.marginRight = sideMargin + 'px';
				container.style.width = ( viewportWidth - sideMargin ) + 'px';
				container.style.maxWidth = ( viewportWidth - sideMargin ) + 'px';
			} else if ( hasRight ) {
				// Remove right side, keep left side fixed.
				container.style.marginRight = '0';
				container.style.marginLeft = sideMargin + 'px';
				container.style.width = ( viewportWidth - sideMargin ) + 'px';
				container.style.maxWidth = ( viewportWidth - sideMargin ) + 'px';
			}

			// Mark as processed.
			container.setAttribute( 'data-frbl-processed', 'true' );
		});
	}

	/**
	 * Recalculate margins on window resize.
	 */
	function handleResize() {
		// Remove processed flag to allow recalculation.
		const edgeContainers = document.querySelectorAll('.frbl-edge-left, .frbl-edge-right');
		edgeContainers.forEach( function( container ) {
			container.removeAttribute( 'data-frbl-processed' );
		});

		// Recalculate.
		applyEdgeAlignmentMargins();
	}

	// Initialize on DOM ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', applyEdgeAlignmentMargins );
	} else {
		applyEdgeAlignmentMargins();
	}

	// Recalculate on window resize (debounced).
	let resizeTimer;
	window.addEventListener( 'resize', function() {
		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( handleResize, 250 );
	});

	// Also run after page load to catch any late-loading content.
	window.addEventListener( 'load', applyEdgeAlignmentMargins );
})();

