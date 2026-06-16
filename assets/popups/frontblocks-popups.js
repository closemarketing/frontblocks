(function () {
	'use strict';

	if (!window.frblPopups || !frblPopups.popups || !frblPopups.popups.length) {
		return;
	}

	/**
	 * Cookie helpers.
	 */
	function setCookie(name, value, days) {
		var expires = '';
		if (days) {
			var d = new Date();
			d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
			expires = '; expires=' + d.toUTCString();
		}
		document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
	}

	function getCookie(name) {
		var nameEQ = name + '=';
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i].trimStart();
			if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
		}
		return null;
	}

	/**
	 * Session storage helper (once per session).
	 */
	function sessionKey(id) {
		return 'frbl_popup_' + id;
	}

	/**
	 * Check if the popup should be shown based on frequency.
	 *
	 * @param {Object} config Popup config.
	 * @returns {boolean}
	 */
	function shouldShow(config) {
		var freq = config.frequency;
		var key  = 'frbl_popup_' + config.id;

		if (freq === 'always') return true;

		if (freq === 'session') {
			return !sessionStorage.getItem(sessionKey(config.id));
		}

		if (freq === 'once') {
			return !getCookie(key);
		}

		if (freq === 'daily') {
			var val = getCookie(key);
			if (!val) return true;
			var last = parseInt(val, 10);
			return (Date.now() - last) > 86400000;
		}

		if (freq === 'weekly') {
			var valW = getCookie(key);
			if (!valW) return true;
			var lastW = parseInt(valW, 10);
			return (Date.now() - lastW) > 604800000;
		}

		return true;
	}

	/**
	 * Mark popup as shown (for frequency tracking).
	 *
	 * @param {Object} config Popup config.
	 */
	function markShown(config) {
		var freq = config.frequency;
		var key  = 'frbl_popup_' + config.id;

		if (freq === 'session') {
			sessionStorage.setItem(sessionKey(config.id), '1');
		} else if (freq === 'once') {
			setCookie(key, '1', 3650);
		} else if (freq === 'daily') {
			setCookie(key, String(Date.now()), 1);
		} else if (freq === 'weekly') {
			setCookie(key, String(Date.now()), 7);
		}
	}

	/**
	 * Open a popup element.
	 *
	 * @param {HTMLElement} wrapper Popup wrapper element.
	 * @param {Object} config Popup config.
	 */
	function openPopup(wrapper, config) {
		if (!wrapper || wrapper.dataset.frblOpen === '1') return;

		wrapper.style.display = 'flex';
		wrapper.dataset.frblOpen = '1';

		// Trigger animation next frame.
		requestAnimationFrame(function () {
			wrapper.classList.add('frbl-popup-wrapper--visible');
		});

		// Prevent body scroll.
		document.body.style.overflow = 'hidden';

		markShown(config);
	}

	/**
	 * Close a popup element.
	 *
	 * @param {HTMLElement} wrapper Popup wrapper element.
	 */
	function closePopup(wrapper) {
		if (!wrapper) return;
		wrapper.classList.remove('frbl-popup-wrapper--visible');
		wrapper.classList.add('frbl-popup-wrapper--closing');

		setTimeout(function () {
			wrapper.style.display = 'none';
			wrapper.classList.remove('frbl-popup-wrapper--closing');
			wrapper.dataset.frblOpen = '0';
			document.body.style.overflow = '';
		}, 300);
	}

	/**
	 * Set up a single popup.
	 *
	 * @param {Object} config Popup config object.
	 */
	function initPopup(config) {
		var wrapper = document.getElementById('frbl-popup-' + config.id);
		if (!wrapper) return;

		if (!shouldShow(config)) return;

		// Close button.
		var closeBtn = wrapper.querySelector('.frbl-popup-close');
		if (closeBtn) {
			closeBtn.addEventListener('click', function () {
				closePopup(wrapper);
			});
		}

		// Overlay click.
		if (config.closeOnOverlay) {
			var overlay = wrapper.querySelector('.frbl-popup-overlay');
			if (overlay) {
				overlay.addEventListener('click', function () {
					closePopup(wrapper);
				});
			}
		}

		// ESC key.
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && wrapper.dataset.frblOpen === '1') {
				closePopup(wrapper);
			}
		});

		var trigger = config.trigger;

		if (trigger === 'load') {
			openPopup(wrapper, config);

		} else if (trigger === 'delay') {
			setTimeout(function () {
				openPopup(wrapper, config);
			}, (config.triggerDelay || 3) * 1000);

		} else if (trigger === 'scroll') {
			var scrollPct = config.triggerScroll || 50;
			var scrollFired = false;
			window.addEventListener('scroll', function onScroll() {
				if (scrollFired) return;
				var scrollTop = window.scrollY || document.documentElement.scrollTop;
				var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
				if (docHeight <= 0) return;
				var pct = (scrollTop / docHeight) * 100;
				if (pct >= scrollPct) {
					scrollFired = true;
					window.removeEventListener('scroll', onScroll);
					openPopup(wrapper, config);
				}
			}, { passive: true });

		} else if (trigger === 'exit_intent') {
			var exitFired = false;
			document.addEventListener('mouseleave', function onExit(e) {
				if (exitFired || e.clientY > 0) return;
				exitFired = true;
				document.removeEventListener('mouseleave', onExit);
				openPopup(wrapper, config);
			});

		} else if (trigger === 'button') {
			var selector = config.triggerSelector;
			if (selector) {
				document.querySelectorAll(selector).forEach(function (btn) {
					btn.addEventListener('click', function (e) {
						e.preventDefault();
						openPopup(wrapper, config);
					});
				});
			}

		} else if (trigger === 'inactivity') {
			var inactMs = (config.triggerInact || 30) * 1000;
			var timer;
			var inactFired = false;

			function resetTimer() {
				if (inactFired) return;
				clearTimeout(timer);
				timer = setTimeout(function () {
					inactFired = true;
					openPopup(wrapper, config);
				}, inactMs);
			}

			['mousemove', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
				document.addEventListener(evt, resetTimer, { passive: true });
			});

			resetTimer();
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		frblPopups.popups.forEach(initPopup);
	});
}());
