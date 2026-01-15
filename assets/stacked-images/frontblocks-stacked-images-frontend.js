/**
 * FrontBlocks Stacked Images Frontend Animation
 */

(function () {
	'use strict';

	// Track initialized containers to avoid re-initialization.
	const initializedContainers = new WeakSet();

	/**
	 * Initialize stacked images animation.
	 */
	function initStackedImages() {
		const containers = document.querySelectorAll('.frbl-stacked-images-wrapper:not(.frbl-initialized)');

		containers.forEach(container => {
			// Skip if already initialized.
			if (initializedContainers.has(container)) {
				return;
			}

			const direction = container.dataset.direction || 'bottom';
			const duration = parseInt(container.dataset.duration) || 1000;
			const delay = parseInt(container.dataset.delay) || 500;
			const images = container.querySelectorAll('.frbl-stacked-image');

			if (images.length === 0) {
				return;
			}

			// Mark as initialized.
			container.classList.add('frbl-initialized');
			initializedContainers.add(container);

			// Set initial position for each image based on direction.
			images.forEach((image, index) => {
				// Set transition FIRST.
				image.style.transition = `opacity ${duration}ms ease-out, transform ${duration}ms ease-out`;
				image.style.position = 'absolute';
				image.style.top = '0';
				image.style.left = '0';
				image.style.width = '100%';
				image.style.height = '100%';
				
				// Generate random rotation between -10 and 10 degrees.
				const randomRotation = (Math.random() * 20) - 10;
				image.dataset.rotation = randomRotation;
				
				// Force a reflow to ensure styles are applied.
				void image.offsetHeight;

				// Then set initial state (hidden and off-screen).
				image.style.opacity = '0';
				
				// Set initial transform based on direction with rotation.
				let initialTransform = '';
				switch (direction) {
					case 'bottom':
						initialTransform = `translateY(100%) rotate(${randomRotation}deg)`;
						break;
					case 'top':
						initialTransform = `translateY(-100%) rotate(${randomRotation}deg)`;
						break;
					case 'left':
						initialTransform = `translateX(-100%) rotate(${randomRotation}deg)`;
						break;
					case 'right':
						initialTransform = `translateX(100%) rotate(${randomRotation}deg)`;
						break;
				}
				image.style.transform = initialTransform;
			});

			// Animate images one by one using Intersection Observer.
			const observer = new IntersectionObserver(
				(entries) => {
					entries.forEach(entry => {
						if (entry.isIntersecting) {
							animateImages(images, duration, delay);
							observer.unobserve(entry.target);
						}
					});
				},
				{
					threshold: 0.2,
					rootMargin: '0px',
				}
			);

			observer.observe(container);
		});
	}

	/**
	 * Animate images sequentially.
	 */
	function animateImages(images, duration, delay) {
		images.forEach((image, index) => {
			setTimeout(() => {
				const rotation = image.dataset.rotation || 0;
				image.style.opacity = '1';
				// Final state: no translation, but keep the random rotation.
				image.style.transform = `translate(0, 0) rotate(${rotation}deg)`;
			}, index * delay);
		});
	}

	// Initialize on DOM ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initStackedImages);
	} else {
		initStackedImages();
	}
})();
