/**
 * FrontBlocks Cookie Notice
 *
 * @package FrontBlocks
 * @version 1.0.0
 */

(function () {
	'use strict';

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function readCookie(name) {
		var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));

		if (!match) {
			return '';
		}

		try {
			return decodeURIComponent(match[1]);
		} catch (e) {
			// Malformed percent-encoding: treat it the same as no cookie at all.
			return '';
		}
	}

	function init() {
		var banner = document.getElementById('frbl-cookie-notice');

		if (!banner || typeof frblCookieNotice === 'undefined') {
			return;
		}

		var existingConsent = readCookie(frblCookieNotice.cookieName);

		if (existingConsent === 'accepted' || existingConsent === 'rejected') {
			// Already decided: the inline bootstrap script printed with the banner
			// already hid it and, for an accepted visitor, already requested the
			// tracking scripts. Nothing left to wire up.
			return;
		}

		var acceptBtn = banner.querySelector('[data-frbl-cookie-action="accept"]');
		var rejectBtn = banner.querySelector('[data-frbl-cookie-action="reject"]');
		var isPopup = banner.classList.contains('frbl-cookie-notice--popup');
		var previouslyFocused = document.activeElement;

		if (isPopup) {
			document.body.classList.add('frbl-cookie-notice-lock-scroll');

			if (acceptBtn) {
				acceptBtn.focus({ preventScroll: true });
			}

			document.addEventListener('keydown', trapFocus);
		}

		if (acceptBtn) {
			acceptBtn.addEventListener('click', function () {
				handleDecision('accepted');
			});
		}

		if (rejectBtn) {
			rejectBtn.addEventListener('click', function () {
				handleDecision('rejected');
			});
		}

		function trapFocus(event) {
			if (event.key !== 'Tab') {
				return;
			}

			var focusable = Array.prototype.slice.call(
				banner.querySelectorAll('a[href], button')
			);

			if (!focusable.length) {
				return;
			}

			var first = focusable[0];
			var last = focusable[focusable.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		}

		function handleDecision(decision) {
			setConsentCookie(decision);
			hideBanner();
			dispatchConsentEvent(decision);
			logDecision(decision);

			if (decision === 'accepted') {
				fetchAndInjectScripts();
			}
		}

		function setConsentCookie(decision) {
			var maxAge = parseInt(frblCookieNotice.expirationDays, 10) * 24 * 60 * 60;
			var secure = window.location.protocol === 'https:' ? '; Secure' : '';

			document.cookie = frblCookieNotice.cookieName + '=' + decision +
				'; path=' + frblCookieNotice.cookiePath + '; max-age=' + maxAge + '; SameSite=Lax' + secure;
		}

		function hideBanner() {
			banner.classList.add('frbl-cookie-notice--hidden');
			document.body.classList.remove('frbl-cookie-notice-lock-scroll');
			document.removeEventListener('keydown', trapFocus);

			if (isPopup && previouslyFocused && typeof previouslyFocused.focus === 'function') {
				previouslyFocused.focus({ preventScroll: true });
			}

			window.setTimeout(function () {
				if (banner.parentNode) {
					banner.parentNode.removeChild(banner);
				}
			}, 300);
		}

		function dispatchConsentEvent(decision) {
			var event;

			try {
				event = new CustomEvent('frblCookieConsent', { detail: { consent: decision } });
			} catch (e) {
				event = document.createEvent('CustomEvent');
				event.initCustomEvent('frblCookieConsent', true, true, { consent: decision });
			}

			document.dispatchEvent(event);
		}

		function logDecision(decision) {
			var nonceForm = new FormData();
			nonceForm.append('action', 'frbl_get_cookie_notice_log_nonce');

			fetch(frblCookieNotice.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: nonceForm
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (response) {
					if (!response || !response.success || !response.data) {
						return;
					}

					var formData = new FormData();
					formData.append('action', 'frbl_log_cookie_consent');
					formData.append('nonce', response.data.nonce);
					formData.append('decision', decision);

					return fetch(frblCookieNotice.ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						body: formData
					});
				})
				.catch(function () {
					// Best-effort: the aggregate stat is not critical to the consent flow.
				});
		}

		function fetchAndInjectScripts() {
			var formData = new FormData();
			formData.append('action', 'frbl_get_cookie_notice_config');

			fetch(frblCookieNotice.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (response) {
					if (response && response.success && response.data && window.frblCookieNoticeInject) {
						window.frblCookieNoticeInject(response.data.gtmId, response.data.ga4Id);
					}
				})
				.catch(function () {
					// Network hiccup: consent is already stored locally; nothing else to do here.
				});
		}
	}
})();
