/**
 * FrontBlocks - Column Link (frontend behavior)
 *
 * Makes a column with a configured link URL clickable anywhere in its
 * background, without hijacking clicks that land on a real interactive
 * element (link, button, form field...) already inside it.
 *
 * @package FrontBlocks
 */

(function () {
	'use strict';

	var INTERACTIVE_SELECTOR = 'a, button, input, textarea, select, summary, audio, video, [contenteditable="true"], [role="button"]';

	function findLinkedColumn(target) {
		return target.closest ? target.closest('[data-frbl-column-link-url]') : null;
	}

	function hasInteractiveAncestorWithin(target, column) {
		var interactive = target.closest ? target.closest(INTERACTIVE_SELECTOR) : null;

		return !!interactive && interactive !== column && column.contains(interactive);
	}

	function navigate(column) {
		var url = column.getAttribute('data-frbl-column-link-url');

		if (!url) {
			return;
		}

		if (column.getAttribute('data-frbl-column-link-target') === '_blank') {
			window.open(url, '_blank', 'noopener,noreferrer');
			return;
		}

		window.location.href = url;
	}

	document.addEventListener('click', function (event) {
		var column = findLinkedColumn(event.target);

		if (!column) {
			return;
		}

		if (hasInteractiveAncestorWithin(event.target, column)) {
			return;
		}

		navigate(column);
	});

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Enter' && event.key !== ' ') {
			return;
		}

		var column = findLinkedColumn(event.target);

		// Only when the column itself carries the focus — a nested control
		// (button, input...) handles its own Enter/Space activation.
		if (!column || event.target !== column) {
			return;
		}

		event.preventDefault();
		navigate(column);
	});
})();
