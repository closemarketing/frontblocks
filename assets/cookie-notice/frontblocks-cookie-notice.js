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

	function defineInjectHelper() {
		window.frblCookieNoticeInject = window.frblCookieNoticeInject || function (gtmId, ga4Id) {
			if (gtmId) {
				window.dataLayer = window.dataLayer || [];
				window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });

				var gtmScript = document.createElement('script');
				gtmScript.async = true;
				gtmScript.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(gtmId);
				document.head.appendChild(gtmScript);
			}

			if (ga4Id) {
				var ga4Script = document.createElement('script');
				ga4Script.async = true;
				ga4Script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ga4Id);
				document.head.appendChild(ga4Script);

				window.dataLayer = window.dataLayer || [];
				window.gtag = window.gtag || function () {
					window.dataLayer.push(arguments);
				};
				window.gtag('js', new Date());
				window.gtag('config', ga4Id);
			}
		};
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

	/**
	 * Hide an already-decided banner and, for an accepted visitor, request the
	 * tracking scripts. Normally an inline bootstrap script printed alongside the
	 * banner already does this immediately (before this file even loads, avoiding
	 * any flash of the banner) — this is the fallback for sites whose Content
	 * Security Policy blocks that unnonced inline script, so tracking and the
	 * banner still work correctly there, just without the no-flash guarantee.
	 */
	function runBootstrapIfNeeded() {
		if (window.frblCookieNoticeBootstrapped) {
			return;
		}

		var consent = readCookie(frblCookieNotice.cookieName);
		var banner = document.getElementById('frbl-cookie-notice');

		if (banner && (consent === 'accepted' || consent === 'rejected')) {
			banner.style.display = 'none';
		}

		defineInjectHelper();

		if (consent === 'accepted') {
			fetchAndInjectScripts();
		}

		window.frblCookieNoticeBootstrapped = true;
	}

	function init() {
		if (typeof frblCookieNotice === 'undefined') {
			return;
		}

		runBootstrapIfNeeded();

		var banner = document.getElementById('frbl-cookie-notice');

		if (!banner) {
			return;
		}

		var existingConsent = readCookie(frblCookieNotice.cookieName);

		if (existingConsent === 'accepted' || existingConsent === 'rejected') {
			// Already decided: runBootstrapIfNeeded() (here or via the inline
			// bootstrap script) already hid the banner and requested tracking.
			// Nothing left to wire up.
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
	}
})();
