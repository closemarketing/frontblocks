/**
 * FrontBlocks Review Notice
 *
 * Handles dismissal of the review admin notice.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var notice = document.getElementById('frbl-review-notice');
		var noThanks = document.getElementById('frbl-dismiss-review');

		if (!notice || !noThanks) {
			return;
		}

		function sendDismiss() {
			var data = new FormData();
			data.append('action', 'frbl_dismiss_review_notice');
			data.append('nonce', frblReviewNotice.nonce);

			return fetch(frblReviewNotice.ajaxurl, {
				method: 'POST',
				body: data
			});
		}

		// "No thanks" button: only hide once the dismissal is actually saved —
		// hiding unconditionally would let the notice reappear on the next
		// eligible page after a dropped request or an expired nonce, despite
		// having looked dismissed just now.
		noThanks.addEventListener('click', function (e) {
			e.preventDefault();
			sendDismiss().then(function (response) {
				if (response.ok) {
					notice.style.display = 'none';
				}
			});
		});

		// WordPress built-in dismiss button (.notice-dismiss).
		notice.addEventListener('click', function (e) {
			if (e.target && e.target.classList.contains('notice-dismiss')) {
				sendDismiss();
			}
		});
	});
}());
