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

	function init() {
		var banner = document.getElementById('frbl-cookie-notice');

		if (!banner || typeof frblCookieNotice === 'undefined') {
			return;
		}

		var acceptBtn = banner.querySelector('[data-frbl-cookie-action="accept"]');
		var rejectBtn = banner.querySelector('[data-frbl-cookie-action="reject"]');

		if (banner.classList.contains('frbl-cookie-notice--popup')) {
			document.body.classList.add('frbl-cookie-notice-lock-scroll');
		}

		if (acceptBtn) {
			acceptBtn.addEventListener('click', function () {
				handleDecision('accepted');
			});
			acceptBtn.focus({ preventScroll: true });
		}

		if (rejectBtn) {
			rejectBtn.addEventListener('click', function () {
				handleDecision('rejected');
			});
		}

		function handleDecision(decision) {
			setConsentCookie(decision);
			hideBanner();
			dispatchConsentEvent(decision);
			logDecision(decision);
		}

		function setConsentCookie(decision) {
			var maxAge = parseInt(frblCookieNotice.expirationDays, 10) * 24 * 60 * 60;
			var secure = window.location.protocol === 'https:' ? '; Secure' : '';

			document.cookie = frblCookieNotice.cookieName + '=' + decision +
				'; path=/; max-age=' + maxAge + '; SameSite=Lax' + secure;
		}

		function hideBanner() {
			banner.classList.add('frbl-cookie-notice--hidden');
			document.body.classList.remove('frbl-cookie-notice-lock-scroll');

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
			var formData = new FormData();
			formData.append('action', 'frbl_log_cookie_consent');
			formData.append('nonce', frblCookieNotice.nonce);
			formData.append('decision', decision);

			fetch(frblCookieNotice.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (response) {
					if (decision === 'accepted' && response && response.success && response.data) {
						injectAcceptedScripts(response.data);
					}
				})
				.catch(function () {
					// Consent is already stored locally; a failed log request is not fatal.
				});
		}

		function injectAcceptedScripts(data) {
			if (data.gtmId) {
				injectGtm(data.gtmId);
			}

			if (data.ga4Id) {
				injectGa4(data.ga4Id);
			}
		}

		function injectGtm(gtmId) {
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });

			var script = document.createElement('script');
			script.async = true;
			script.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(gtmId);
			document.head.appendChild(script);

			var iframe = document.createElement('iframe');
			iframe.src = 'https://www.googletagmanager.com/ns.html?id=' + encodeURIComponent(gtmId);
			iframe.height = '0';
			iframe.width = '0';
			iframe.style.display = 'none';
			iframe.style.visibility = 'hidden';

			var noscript = document.createElement('noscript');
			noscript.appendChild(iframe);
			document.body.appendChild(noscript);
		}

		function injectGa4(ga4Id) {
			var script = document.createElement('script');
			script.async = true;
			script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ga4Id);
			document.head.appendChild(script);

			window.dataLayer = window.dataLayer || [];
			window.gtag = window.gtag || function () {
				window.dataLayer.push(arguments);
			};

			window.gtag('js', new Date());
			window.gtag('config', ga4Id);
		}
	}
})();
