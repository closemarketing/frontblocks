/**
 * FrontBlocks – GenerateBlocks Accordion fix.
 * Ensures GB accordion blocks work when FrontBlocks is active (click toggle, open/close).
 *
 * @package FrontBlocks
 * @author Closemarketing
 */
(function () {
	'use strict';

	var TOGGLE_SELECTOR = '.gb-accordion__toggle';
	var ITEM_SELECTOR = '.gb-accordion__item';
	var CONTENT_SELECTOR = '.gb-accordion__content';

	function setContentVisibility(item, isOpen) {
		var content = item.querySelector(CONTENT_SELECTOR);
		if (!content) return;

		var toggle = item.querySelector(TOGGLE_SELECTOR);
		if (toggle) {
			toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		}

		if (isOpen) {
			item.classList.add('gb-accordion__item-open');
			content.style.display = '';
			content.style.maxHeight = '';
			content.style.overflow = '';
			content.style.visibility = '';
		} else {
			item.classList.remove('gb-accordion__item-open');
			content.style.maxHeight = '0';
			content.style.overflow = 'hidden';
			content.style.visibility = 'hidden';
			content.style.display = 'none';
		}
	}

	function init() {
		document.addEventListener('click', function (e) {
			var toggle = e.target.closest(TOGGLE_SELECTOR);
			if (!toggle) return;

			e.preventDefault();
			var item = toggle.closest(ITEM_SELECTOR);
			if (!item) return;

			var content = item.querySelector(CONTENT_SELECTOR);
			if (!content) return;

			var isOpen = item.classList.contains('gb-accordion__item-open');
			setContentVisibility(item, !isOpen);
		});

		// Set initial state from existing classes (e.g. gb-accordion__item-open).
		document.querySelectorAll(ITEM_SELECTOR).forEach(function (item) {
			var content = item.querySelector(CONTENT_SELECTOR);
			if (!content) return;
			var isOpen = item.classList.contains('gb-accordion__item-open');
			if (!isOpen) {
				content.style.maxHeight = '0';
				content.style.overflow = 'hidden';
				content.style.visibility = 'hidden';
				content.style.display = 'none';
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
