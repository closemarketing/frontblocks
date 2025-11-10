/**
 * FrontBlocks Custom SVG Animations
 * Frontend JavaScript for handling custom SVG animations from JSON and Lottie
 */

(function() {
	'use strict';

	/**
	 * Initialize custom SVG animations on page load.
	 */
	function initCustomSvgAnimations() {
		const animatedShapes = document.querySelectorAll('.frbl-custom-svg-animation');
		
		if (!animatedShapes.length) {
			return;
		}

		animatedShapes.forEach(function(shape) {
			const trigger = shape.dataset.shapeTrigger;

			// Set up Intersection Observer for on-load animations.
			if (trigger === 'load') {
				setupIntersectionObserver(shape);
			}

			// Hover animations are handled by CSS, no JS needed.
		});
	}

	/**
	 * Initialize Lottie animations on page load.
	 */
	function initLottieAnimations() {
		const lottieContainers = document.querySelectorAll('.frbl-lottie-animation');
		
		if (!lottieContainers.length || typeof lottie === 'undefined') {
			return;
		}

		lottieContainers.forEach(function(container) {
			try {
				const lottieData = JSON.parse(container.dataset.lottieJson);
				const loop = container.dataset.loop === 'true';
				const autoplay = container.dataset.autoplay === 'true';
				const speed = parseFloat(container.dataset.speed) || 1;

				// Pre-apply color via CSS variable BEFORE animation loads.
				const color = getComputedStyle(container).getPropertyValue('--lottie-color').trim();
				if (color) {
					container.style.setProperty('--lottie-color', color);
				}

				const animation = lottie.loadAnimation({
					container: container,
					renderer: 'svg',
					loop: loop,
					autoplay: autoplay,
					animationData: lottieData
				});

				animation.setSpeed(speed);

				// Store animation instance for potential controls.
				container.lottieInstance = animation;

				// Apply color IMMEDIATELY after creating animation.
				applyColorToLottie(container);

				// Apply color immediately after SVG is added to DOM.
				animation.addEventListener('DOMLoaded', function() {
					applyColorToLottie(container);
				});

				// Apply color on EVERY SINGLE FRAME (most aggressive approach).
				animation.addEventListener('enterFrame', function() {
					applyColorToLottie(container);
				});

				// Apply color multiple times immediately for instant coverage.
				setTimeout(function() { applyColorToLottie(container); }, 0);
				setTimeout(function() { applyColorToLottie(container); }, 10);
				setTimeout(function() { applyColorToLottie(container); }, 20);
				setTimeout(function() { applyColorToLottie(container); }, 50);
				setTimeout(function() { applyColorToLottie(container); }, 100);
			} catch (error) {
				console.error('FrontBlocks: Error initializing Lottie animation', error);
			}
		});
	}

	/**
	 * Set up Intersection Observer for load animations.
	 * 
	 * @param {HTMLElement} element The shape element to observe.
	 */
	function setupIntersectionObserver(element) {
		// Check if IntersectionObserver is supported.
		if (!('IntersectionObserver' in window)) {
			return;
		}

		const options = {
			root: null,
			rootMargin: '0px',
			threshold: 0.1
		};

		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					// Element is in viewport, ensure animation plays.
					entry.target.style.animationPlayState = 'running';
					
					// Check if it's an infinite animation.
					const computedStyle = getComputedStyle(entry.target);
					const iterationCount = computedStyle.animationIterationCount;
					
					// If not infinite, unobserve after first play.
					if (iterationCount !== 'infinite') {
						observer.unobserve(entry.target);
					}
				} else {
					// If infinite animation, pause when out of viewport for performance.
					const computedStyle = getComputedStyle(entry.target);
					const iterationCount = computedStyle.animationIterationCount;
					
					if (iterationCount === 'infinite') {
						entry.target.style.animationPlayState = 'paused';
					}
				}
			});
		}, options);

		observer.observe(element);
	}

	/**
	 * Apply color to Lottie animation SVG elements - AGGRESSIVELY.
	 * 
	 * @param {HTMLElement} container The Lottie container element.
	 */
	function applyColorToLottie(container) {
		if (!container) return;

		const color = getComputedStyle(container).getPropertyValue('--lottie-color').trim();
		if (!color) return;

		// Find ALL SVG elements - be very aggressive.
		const elements = container.querySelectorAll('svg *, [fill], [stroke]');
		
		elements.forEach(function(element) {
			// Get current fill and stroke.
			const currentFill = element.getAttribute('fill');
			const currentStroke = element.getAttribute('stroke');
			const computedFill = window.getComputedStyle(element).fill;
			
			// Apply to anything that has color.
			if (currentFill && currentFill !== 'none' && currentFill !== 'transparent') {
				element.setAttribute('fill', color);
				element.style.setProperty('fill', color, 'important');
			}
			
			// Also check computed style.
			if (computedFill && computedFill !== 'none' && computedFill !== 'transparent' && computedFill !== 'rgba(0, 0, 0, 0)') {
				element.style.setProperty('fill', color, 'important');
			}
			
			// Also handle stroke if it exists.
			if (currentStroke && currentStroke !== 'none' && currentStroke !== 'transparent') {
				element.setAttribute('stroke', color);
				element.style.setProperty('stroke', color, 'important');
			}
		});

		// Also try to set fill on the SVG itself.
		const svg = container.querySelector('svg');
		if (svg) {
			svg.style.setProperty('fill', color, 'important');
		}

		// Mark container as color-applied to make it visible.
		container.classList.add('frbl-color-applied');
	}

	/**
	 * Initialize all animations.
	 */
	function initAllAnimations() {
		initCustomSvgAnimations();
		initLottieAnimations();
	}

	/**
	 * Initialize on DOM ready.
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAllAnimations);
	} else {
		initAllAnimations();
	}

	/**
	 * Re-initialize when new content is loaded dynamically.
	 * Useful for infinite scroll, AJAX loading, etc.
	 */
	if (window.MutationObserver) {
		const bodyObserver = new MutationObserver(function(mutations) {
			let shouldReinit = false;
			
			mutations.forEach(function(mutation) {
				mutation.addedNodes.forEach(function(node) {
					if (node.nodeType === 1) { // Element node.
						if (node.classList && (node.classList.contains('frbl-custom-svg-animation') || node.classList.contains('frbl-lottie-animation'))) {
							shouldReinit = true;
						} else if (node.querySelector && (node.querySelector('.frbl-custom-svg-animation') || node.querySelector('.frbl-lottie-animation'))) {
							shouldReinit = true;
						}
					}
				});
			});

			if (shouldReinit) {
				initAllAnimations();
			}
		});

		bodyObserver.observe(document.body, {
			childList: true,
			subtree: true
		});
	}

})();
