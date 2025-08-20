/**
 * FrontBlocks FullPage JavaScript
 * Initializes fullpage.js for GenerateBlocks Container elements
 */

(function() {
	'use strict';

	// Wait for DOM to be ready
	document.addEventListener('DOMContentLoaded', function() {
		initializeFullPage();
	});

	// Also initialize when content is loaded dynamically (for AJAX content)
	document.addEventListener('wp-load', function() {
		initializeFullPage();
	});

	function initializeFullPage() {
		const fullpageContainers = document.querySelectorAll('.frontblocks-fullpage[data-fullpage="true"]');
		
		fullpageContainers.forEach(function(container) {
			// Skip if already initialized
			if (container.classList.contains('fp-initialized')) {
				return;
			}

			// Get configuration from data attributes
			const config = getFullPageConfig(container);
			
			// Prepare child elements as sections
			prepareSections(container);
			
			// Initialize fullpage.js
			initFullPageInstance(container, config);
		});
	}

	function getFullPageConfig(container) {
		const config = {
			// Basic options
			licenseKey: window.frblFullpageData ? window.frblFullpageData.licenseKey : '',
			scrollingSpeed: parseInt(container.dataset.scrollSpeed) || 700,
			loopBottom: container.dataset.loopBottom === 'true',
			loopTop: container.dataset.loopTop === 'true',
			
			// Navigation options
			navigation: container.dataset.navigation === 'true',
			navigationPosition: container.dataset.navigationPosition || 'right',
			
			// Arrow options
			showActiveTooltip: false,
			controlArrows: false, // Always disabled as per request
			
			// Scrollbar options
			scrollBar: false, // Disable default scrollbar for smoother experience
			
			// Auto scroll - use the toggle from Gutenberg
			autoScrolling: container.dataset.autoScroll === 'true',
			
			// Smooth scrolling optimization
			scrollHorizontally: false,
			scrollHorizontallyKey: null,
			bigSectionsDestination: null,
			continuousVertical: false,
			scrollOverflowReset: true,
			scrollOverflowResetKey: null,
			
			// Touch and mouse wheel optimization
			touchSensitivity: 15,
			normalScrollElements: null,
			scrollOverflowResetKey: null,
			
			// Mobile-specific optimizations
			allowTouchMove: true,
			allowPageOneByOne: true,
			keyboardScrolling: true,
			keyboardScrollingForTouch: true,
			
			// Prevent skipping and improve smoothness
			fitToSection: true,
			fitToSectionDelay: 600,
			anchors: [], // Let fullpage generate anchors automatically
			lockAnchors: false,
			animateAnchor: true,
			recordHistory: true,
			menu: false,
			slidesNavigation: false,
			slidesNavPosition: 'bottom',
			
			// Scrolloverflow configuration
			scrollOverflow: container.dataset.scrolloverflow === 'true',
			scrollOverflowReset: true,
			scrollOverflowOptions: {
				scrollbars: container.dataset.scrollbar === 'true',
				mouseWheel: true,
				hideScrollbars: container.dataset.scrollbar !== 'true',
				fadeScrollbars: false,
				disableMouse: false,
				click: true,
				preventDefaultException: { tagName: /^(INPUT|TEXTAREA|BUTTON|SELECT|A)$/ }
			},
			
			// Responsive options - Keep fullpage active on mobile
			responsiveWidth: 0, // Disable responsive breakpoint to keep fullpage active on mobile
			responsiveHeight: 0,
			
			// Callbacks
			afterLoad: function(origin, destination, direction) {
				// Add custom after load logic here
				console.log('Section loaded:', destination.index);
			},
			
			onLeave: function(origin, destination, direction) {
				// Add custom on leave logic here
				console.log('Leaving section:', origin.index);
			},
			
			// Optimize scrolling behavior
			beforeLeave: function(origin, destination, direction) {
				// Prevent skipping intermediate sections
				return true;
			},
			
			afterRender: function() {
				// Optimize after fullpage is rendered
				console.log('FullPage rendered and optimized');
			},
			
			onSlideLeave: function(section, origin, destination, direction) {
				// Handle slide transitions smoothly
			}
		};

		return config;
	}

	function prepareSections(container) {
		// Get all direct child elements that should become sections
		const children = Array.from(container.children);
		
		// Filter out hr elements and other non-section elements
		const sectionElements = children.filter(function(child) {
			// Skip hr elements
			if (child.tagName.toLowerCase() === 'hr') {
				return false;
			}
			
			// Skip elements that shouldn't be sections
			const skipTags = ['hr', 'br', 'meta', 'link', 'script', 'style'];
			if (skipTags.includes(child.tagName.toLowerCase())) {
				return false;
			}
			
			return true;
		});
		
		// Ensure we have at least one child element
		if (sectionElements.length === 0) {
			console.warn('No valid child elements found for fullpage sections');
			return;
		}
		
		sectionElements.forEach(function(child, index) {
			// Add section class if not already present
			if (!child.classList.contains('section')) {
				child.classList.add('section');
			}
			
			// Ensure proper height and visibility
			child.style.height = '100vh';
			child.style.display = 'block';
			child.style.visibility = 'visible';
			child.style.opacity = '1';
			
			// Add data attributes for fullpage
			child.setAttribute('data-anchor', 'section-' + (index + 1));
			
			console.log('Prepared section', index + 1, ':', child);
		});
		
		console.log('Prepared', sectionElements.length, 'sections for fullpage (filtered from', children.length, 'total children)');
	}

	function initFullPageInstance(container, config) {
		try {
			// Check if fullpage.js is available
			if (typeof fullpage === 'undefined') {
				console.error('FullPage.js is not loaded');
				return;
			}

			// Check if scrolloverflow is enabled
			if (config.scrollOverflow) {
				console.log('Scrolloverflow is enabled - using fullpage.extensions.min.js');
			}

			// Mobile optimizations
			const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768;
			if (isMobile) {
				console.log('Mobile device detected - applying mobile optimizations');
				config.touchSensitivity = 20; // Slightly higher sensitivity for mobile
				config.scrollingSpeed = Math.min(config.scrollingSpeed, 500); // Faster scrolling on mobile
				
				// Ensure auto scroll works on mobile
				if (container.dataset.autoScroll === 'true') {
					config.autoScrolling = true;
					console.log('Auto scroll enabled for mobile device');
				}
			}

			// Initialize fullpage
			const fullpageInstance = fullpage(container, config);
			
			// Mark as initialized
			container.classList.add('fp-initialized');
			if (isMobile) {
				container.classList.add('fp-mobile');
			}
			
			// Apply custom styles based on data attributes
			applyCustomStyles(container);
			
			// Store instance for potential future use
			container.fullpageInstance = fullpageInstance;
			
			console.log('FullPage initialized for container:', container, 'with scrolloverflow:', config.scrollOverflow, 'mobile:', isMobile, 'autoScroll:', container.dataset.autoScroll === 'true');
			
		} catch (error) {
			console.error('Error initializing FullPage:', error);
		}
	}



	function applyCustomStyles(container) {
		// Apply navigation color
		const navigationColor = container.dataset.navigationColor;
		if (navigationColor) {
			document.documentElement.style.setProperty('--fp-navigation-color', navigationColor);
			document.documentElement.style.setProperty('--fp-navigation-color-hover', adjustColor(navigationColor, -20));
		}
	}

	function adjustColor(color, amount) {
		// Simple color adjustment for hover states
		// This is a basic implementation - you might want to use a proper color library
		return color;
	}

	// Public API for external use
	window.FrontBlocksFullPage = {
		initialize: initializeFullPage,
		destroy: function(container) {
			if (container && container.fullpageInstance) {
				fullpage_api.destroy('all');
				container.classList.remove('fp-initialized');
				delete container.fullpageInstance;
			}
		},
		reload: function(container) {
			if (container) {
				this.destroy(container);
				setTimeout(() => {
					initializeFullPage();
				}, 100);
			}
		}
	};

	// Handle window resize
	window.addEventListener('resize', function() {
		// Reinitialize fullpage on resize if needed
		setTimeout(function() {
			const containers = document.querySelectorAll('.frontblocks-fullpage.fp-initialized');
			containers.forEach(function(container) {
				if (container.fullpageInstance) {
					// Trigger fullpage resize
					if (typeof fullpage_api !== 'undefined') {
						fullpage_api.reBuild();
					}
				}
			});
		}, 250);
	});

})();
