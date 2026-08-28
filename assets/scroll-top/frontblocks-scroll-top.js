/**
 * FrontBlocks Scroll to Top
 *
 * @package FrontBlocks
 * @version 1.0.0
 */

(function() {
	'use strict';

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		var button = document.getElementById('frbl-scroll-top');

		if (!button) {
			return;
		}

		var scrollThreshold = 300;

		function updateVisibility() {
			var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
			if (scrollTop >= scrollThreshold) {
				button.classList.add('frbl-show');
			} else {
				button.classList.remove('frbl-show');
			}
		}

		button.addEventListener('click', function() {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});

		window.addEventListener('scroll', updateVisibility, { passive: true });

		updateVisibility();
	}
})();
