(function() {
	'use strict';

	// Store carousel instances.
	const carouselInstances = new Map();

	/**
	 * Check if device is mobile/tablet (< 768px).
	 */
	function isMobile() {
		return window.innerWidth < 768;
	}

	/**
	 * Initialize a single carousel.
	 */
	function initCarousel(item) {
		// Check if already initialized.
		if (carouselInstances.has(item)) {
			return;
		}

		// Check if carousel should be disabled on desktop.
		const disableOnDesktop = item.getAttribute('data-disable-on-desktop') === 'true';
		
		// Skip initialization if disabled on desktop and current viewport is desktop.
		if (disableOnDesktop && !isMobile()) {
			return;
		}

		// Store original parent to be able to restore.
		const originalParent = item.parentNode;

		// First Parent - Create track wrapper.
		const wrapper = document.createElement('div');
		wrapper.classList.add('glide__track');
		wrapper.setAttribute('data-glide-el', 'track');

		originalParent.replaceChild(wrapper, item);
		wrapper.appendChild(item);

		// Second Parent - Create glide wrapper.
		const wrapperParent = document.createElement('div');
		wrapperParent.classList.add('glide');

		wrapper.parentNode.replaceChild(wrapperParent, wrapper);
		wrapperParent.appendChild(wrapper);

		// Get carousel options.
		const carouselType = item.getAttribute('data-type') || 'carousel';
		const carouselbuttons = item.getAttribute('data-buttons') || 'bullets';
		const carouselView = parseInt(item.getAttribute('data-view')) || 4;
		const carouselLaptopView = parseInt(item.getAttribute('data-laptop-view')) || 3;
		const carouselTabletView = parseInt(item.getAttribute('data-tablet-view')) || 2;
		const carouselMobileView = parseInt(item.getAttribute('data-mobile-view')) || 1;
		const carouselAutoplay = item.getAttribute('data-autoplay') || 0;
		const carouselRewind = item.getAttribute('data-rewind') === 'true';
		const carouselbuttonsColor = item.getAttribute('data-buttons-color') || 'black';
		const carouselbuttonsBackgroundColor = item.getAttribute('data-buttons-background-color') || 'transparent';
		const carouselbuttonsPosition = item.getAttribute('data-buttons-position') || 'side';

		// Add required Glide classes.
		item.classList.add('glide__slides');
		const randomClass = 'glide-' + Math.floor(Math.random() * 1000);
		item.classList.add(randomClass);

		for (const child of item.children) {
			child.classList.add('glide__slide');
		}

		// Add bullets if needed.
		let showBullets = false;
		if (window.innerWidth < 768 && item.children.length <= 10) {
			showBullets = true;
		} else if (window.innerWidth >= 768) {
			showBullets = true;
		}

		if (showBullets && carouselbuttons === 'bullets') {
			const bullets = document.createElement('div');
			bullets.classList.add('glide__bullets');
			bullets.setAttribute('data-glide-el', 'controls[nav]');

			for (let i = 0; i < item.children.length; i++) {
				const bullet = document.createElement('button');
				bullet.classList.add('glide__bullet');
				bullet.setAttribute('data-glide-dir', '=' + i);
				bullet.style.backgroundColor = carouselbuttonsBackgroundColor;
				bullets.appendChild(bullet);
			}

			wrapperParent.appendChild(bullets);

			// Add custom CSS for active bullet color.
			const style = document.createElement('style');
			style.id = 'glide-bullet-style-' + randomClass;
			style.textContent = `
				.${randomClass} .glide__bullet.glide__bullet--active {
					background-color: ${carouselbuttonsColor} !important;
				}
			`;
			document.head.appendChild(style);
		}

		// Add arrows if needed.
		if (carouselbuttons === 'arrows') {
			const arrows = document.createElement('div');
			arrows.classList.add('glide__arrows');
			
			if (carouselbuttonsPosition === 'bottom') {
				arrows.classList.add('glide__arrows--bottom');
			} else {
				arrows.classList.add('glide__arrows--top');
			}

			arrows.setAttribute('data-glide-el', 'controls');
			
			const arrowsHTML = `
				<button class="glide__arrow glide__arrow--left" data-glide-dir="<" style="background-color: ${carouselbuttonsBackgroundColor}">
					<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6 1L1 6L6 11" stroke="${carouselbuttonsColor}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
				<button class="glide__arrow glide__arrow--right" data-glide-dir=">" style="background-color: ${carouselbuttonsBackgroundColor}">
					<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 11L6 6L1 1" stroke="${carouselbuttonsColor}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			`;
			
			arrows.innerHTML = arrowsHTML;
			wrapperParent.appendChild(arrows);
		}

		// Initialize Glide.
		const glideFrontBlocks = new Glide(wrapperParent, {
			type: carouselType,
			perView: carouselView,
			startAt: 0,
			autoplay: carouselAutoplay == 0 ? false : parseInt(carouselAutoplay),
			gap: 20,
			rewind: carouselRewind,
			breakpoints: {
				768: {
					perView: carouselMobileView
				},
				1024: {
					perView: carouselTabletView
				},
				1440: {
					perView: carouselLaptopView
				}
			}
		});

		// Handle slider type.
		if (carouselType === 'slider') {
			glideFrontBlocks.on('run.after', () => {
				const currentIndex = glideFrontBlocks.index;
				const lastIndex = wrapperParent.querySelectorAll('.glide__slide:not(.glide__slide--clone)').length;
				const actualView = parseInt(currentIndex) + parseInt(carouselView);

				if (actualView > lastIndex) {
					setTimeout(() => {
						glideFrontBlocks.go('=0');
					}, 5);
				}
			});
		}

		glideFrontBlocks.mount();

		// Store instance data.
		carouselInstances.set(item, {
			instance: glideFrontBlocks,
			wrapper: wrapperParent,
			track: wrapper,
			originalParent: originalParent,
			randomClass: randomClass
		});
	}

	/**
	 * Destroy a carousel and restore original structure.
	 */
	function destroyCarousel(item) {
		if (!carouselInstances.has(item)) {
			return;
		}

		const data = carouselInstances.get(item);

		// Destroy Glide instance.
		if (data.instance && typeof data.instance.destroy === 'function') {
			data.instance.destroy();
		}

		// Remove custom styles.
		const style = document.getElementById('glide-bullet-style-' + data.randomClass);
		if (style) {
			style.remove();
		}

		// Remove Glide classes from children.
		for (const child of item.children) {
			child.classList.remove('glide__slide');
		}

		// Remove Glide classes from item.
		item.classList.remove('glide__slides', data.randomClass);

		// Restore original structure.
		if (data.wrapper && data.wrapper.parentNode) {
			data.wrapper.parentNode.replaceChild(item, data.wrapper);
		}

		carouselInstances.delete(item);
	}

	/**
	 * Process all carousels based on viewport.
	 */
	function processCarousels() {
		const carouselItems = document.querySelectorAll('.frontblocks-carousel');

		carouselItems.forEach((item) => {
			const disableOnDesktop = item.getAttribute('data-disable-on-desktop') === 'true';

			if (disableOnDesktop) {
				// Handle responsive carousel.
				if (isMobile()) {
					// Initialize on mobile.
					initCarousel(item);
				} else {
					// Destroy on desktop.
					destroyCarousel(item);
				}
			} else {
				// Always initialize if not disabled on desktop.
				initCarousel(item);
			}
		});
	}

	// Initialize on load.
	window.addEventListener('load', processCarousels);

	// Handle resize with debounce.
	let resizeTimeout;
	window.addEventListener('resize', function() {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(processCarousels, 250);
	});
})();
