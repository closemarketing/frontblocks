/**
 * Reading Progress Bar functionality
 *
 * @package FrontBlocks
 */

(function() {
	'use strict';

	// Wait for DOM to be ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initReadingProgress);
	} else {
		initReadingProgress();
	}

	/**
	 * Initialize reading progress bar.
	 */
	function initReadingProgress() {
		const progressBar = document.querySelector('.frbl-reading-progress-bar');
		const progressFill = document.querySelector('.frbl-reading-progress-fill');

		if (!progressBar || !progressFill) {
			return;
		}

		// Add class to body to adjust content padding.
		document.body.classList.add('frbl-reading-progress-active');

		// Get the main content area (article or post content).
		const article = document.querySelector('article') || document.querySelector('.entry-content') || document.querySelector('main');

		if (!article) {
			return;
		}

		/**
		 * Update progress bar based on scroll position.
		 */
		function updateProgress() {
			// Get article boundaries.
			const articleRect = article.getBoundingClientRect();
			const articleTop = articleRect.top + window.pageYOffset;
			const articleHeight = articleRect.height;
			const windowHeight = window.innerHeight;

			// Calculate scroll progress.
			const scrollTop = window.pageYOffset;
			const scrollStart = articleTop;
			const scrollEnd = articleTop + articleHeight - windowHeight;
			const scrollRange = scrollEnd - scrollStart;

			// Calculate percentage.
			let percentage = 0;
			if (scrollTop > scrollStart) {
				percentage = Math.min(((scrollTop - scrollStart) / scrollRange) * 100, 100);
			}

			// Ensure percentage is between 0 and 100.
			percentage = Math.max(0, Math.min(100, percentage));

			// Update progress bar height.
			progressFill.style.height = percentage + '%';

			// Update ARIA attribute for accessibility.
			progressBar.setAttribute('aria-valuenow', Math.round(percentage));
		}

		// Update on scroll (throttled for performance).
		let ticking = false;
		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					updateProgress();
					ticking = false;
				});
				ticking = true;
			}
		});

		// Update on resize.
		let resizeTimer;
		window.addEventListener('resize', function() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function() {
				updateProgress();
			}, 150);
		});

		// Initial update.
		updateProgress();
	}
})();

