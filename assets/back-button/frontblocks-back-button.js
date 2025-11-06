/**
 * FrontBlocks Back Button
 * 
 * @package FrontBlocks
 * @version 1.0.0
 */

(function() {
	'use strict';

	// Wait for DOM to be ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		const backButton = document.getElementById('frbl-back-button');
		
		if (!backButton) {
			return;
		}

		// Get or initialize navigation counter.
		const STORAGE_KEY = 'frbl_nav_count';
		const ENTRY_URL_KEY = 'frbl_entry_url';
		
		// Get current navigation count.
		let navCount = parseInt(sessionStorage.getItem(STORAGE_KEY) || '0', 10);
		
		// Store entry URL if this is the first page.
		if (navCount === 0) {
			sessionStorage.setItem(ENTRY_URL_KEY, window.location.href);
		}
		
		// Increment navigation counter.
		navCount++;
		sessionStorage.setItem(STORAGE_KEY, navCount.toString());
		
		// Get entry URL.
		const entryUrl = sessionStorage.getItem(ENTRY_URL_KEY);
		const currentUrl = window.location.href;
		
		/**
		 * Check if button should be visible.
		 * 
		 * @return {boolean} True if button should be visible.
		 */
		function shouldShowButton() {
			// Don't show if this is the first page visited.
			if (navCount <= 1) {
				return false;
			}
			
			// Don't show if we're back at the entry page.
			if (currentUrl === entryUrl) {
				return false;
			}
			
			// Don't show if there's no history.
			if (window.history.length <= 1) {
				return false;
			}
			
			return true;
		}
		
		// Initial visibility check.
		const canShowButton = shouldShowButton();
		
		if (canShowButton) {
			// Show button after a short delay.
			setTimeout(function() {
				checkScrollAndShow();
			}, 500);
		}

		// Handle button click.
		backButton.addEventListener('click', function(e) {
			e.preventDefault();
			
			// Decrement counter when going back.
			navCount = Math.max(0, navCount - 1);
			sessionStorage.setItem(STORAGE_KEY, navCount.toString());
			
			// Go back in history.
			window.history.back();
		});

		// Scroll threshold for showing/hiding button.
		const scrollThreshold = 100;
		
		/**
		 * Check scroll position and show/hide button accordingly.
		 */
		function checkScrollAndShow() {
			if (!canShowButton) {
				return;
			}
			
			const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
			
			// Only show button if scrolled down.
			if (scrollTop < scrollThreshold) {
				backButton.classList.remove('frbl-show');
			} else {
				backButton.classList.add('frbl-show');
			}
		}
		
		// Listen to scroll events.
		window.addEventListener('scroll', checkScrollAndShow, { passive: true });
		
		// Listen to popstate (browser back/forward buttons).
		window.addEventListener('popstate', function() {
			// Update navigation count when using browser buttons.
			navCount = parseInt(sessionStorage.getItem(STORAGE_KEY) || '0', 10);
			
			// Hide button if we're back at entry page.
			if (window.location.href === entryUrl || navCount <= 1) {
				backButton.classList.remove('frbl-show');
			}
		});
		
		// Clean up when leaving the site.
		window.addEventListener('beforeunload', function() {
			// Check if we're navigating to an external URL.
			// Note: We can't always detect this, but sessionStorage will be cleared
			// when the user closes the tab/window anyway.
		});
	}
})();

