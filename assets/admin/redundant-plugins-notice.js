/**
 * FrontBlocks Redundant Plugins Notice
 *
 * Handles dismissal of the redundant-plugin admin notices. Unlike the review
 * notice, dismissal here is scoped to a state hash (which plugins/versions
 * were detected): the notice reappears if that state changes later.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var notices = document.querySelectorAll('.frbl-redundant-plugin-notice');

		if (!notices.length) {
			return;
		}

		function sendDismiss(entryId, stateHash) {
			var data = new FormData();
			data.append('action', 'frbl_dismiss_redundant_plugin_notice');
			data.append('nonce', frblRedundantPluginsNotice.nonce);
			data.append('entry_id', entryId);
			data.append('state_hash', stateHash);

			return fetch(frblRedundantPluginsNotice.ajaxurl, {
				method: 'POST',
				body: data
			});
		}

		notices.forEach(function (notice) {
			var entryId = notice.getAttribute('data-entry-id');
			var stateHash = notice.getAttribute('data-state-hash');
			var dismissLink = notice.querySelector('.frbl-dismiss-redundant-plugin');

			function dismiss(e) {
				if (e) {
					e.preventDefault();
				}

				// Only hide once the dismissal is actually saved, so the notice
				// doesn't silently vanish while a dropped request leaves the
				// server-side state undismissed.
				sendDismiss(entryId, stateHash).then(function (response) {
					if (response.ok) {
						notice.style.display = 'none';
					}
				});
			}

			if (dismissLink) {
				dismissLink.addEventListener('click', dismiss);
			}

			// WordPress built-in dismiss button (.notice-dismiss), if present.
			notice.addEventListener('click', function (e) {
				if (e.target && e.target.classList.contains('notice-dismiss')) {
					dismiss();
				}
			});
		});
	});
}());
