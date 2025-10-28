/**
 * FrontBlocks Carousel Editor
 * Initializes carousel in the block editor
 */

(function() {
	const { subscribe } = wp.data;
	let initialized = new Set();

	// Function to initialize carousel for a specific block.
	function initializeCarousel(blockElement, attributes) {
		const blockId = blockElement.getAttribute('data-block');
		
		// Skip if already initialized.
		if (initialized.has(blockId)) {
			return;
		}

		const {
			frblGridOption = 'none',
			frblItemsToView = '4',
			frblResponsiveToView = '1',
			frblAutoplay = '',
			frblButtons = 'arrows',
			frblRewind = true,
			frblButtonColor = 'black',
			frblButtonBgColor = 'transparent',
			frblButtonsPosition = 'side'
		} = attributes;

		// Only initialize if carousel or slider is selected.
		if (frblGridOption !== 'carousel' && frblGridOption !== 'slider') {
			return;
		}

		// Find the grid wrapper inside the block.
		const gridWrapper = blockElement.querySelector('.gb-grid-wrapper, .gb-element');
		if (!gridWrapper) {
			return;
		}

		// Check if already has glide classes.
		if (gridWrapper.classList.contains('glide__slides')) {
			return;
		}

		// Create wrapper structure.
		const parent = gridWrapper.parentNode;
		const trackWrapper = document.createElement('div');
		trackWrapper.classList.add('glide__track');
		trackWrapper.setAttribute('data-glide-el', 'track');

		parent.replaceChild(trackWrapper, gridWrapper);
		trackWrapper.appendChild(gridWrapper);

		const parentwrap = trackWrapper.parentNode;
		const glideWrapper = document.createElement('div');
		glideWrapper.classList.add('glide', 'frontblocks-editor-carousel');

		parentwrap.replaceChild(glideWrapper, trackWrapper);
		glideWrapper.appendChild(trackWrapper);

		// Add classes to grid and children.
		gridWrapper.classList.add('glide__slides', 'frontblocks-carousel');
		gridWrapper.classList.add('glide-' + Math.floor(Math.random() * 1000));

		// Add slide classes to children.
		Array.from(gridWrapper.children).forEach(child => {
			child.classList.add('glide__slide');
		});

		// Add arrows if needed.
		if (frblButtons === 'arrows') {
			const arrows = document.createElement('div');
			arrows.classList.add('glide__arrows');
			
			if (frblButtonsPosition === 'bottom') {
				arrows.classList.add('glide__arrows--bottom');
			} else {
				arrows.classList.add('glide__arrows--top');
			}

			arrows.setAttribute('data-glide-el', 'controls');
			
			const arrowsHTML = `
				<button class="glide__arrow glide__arrow--left" data-glide-dir="<" style="background-color: ${frblButtonBgColor}">
					<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6 1L1 6L6 11" stroke="${frblButtonColor}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
				<button class="glide__arrow glide__arrow--right" data-glide-dir=">" style="background-color: ${frblButtonBgColor}">
					<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 11L6 6L1 1" stroke="${frblButtonColor}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			`;
			
			arrows.innerHTML = arrowsHTML;
			glideWrapper.appendChild(arrows);
		}

		// Add bullets if needed.
		if (frblButtons === 'bullets') {
			const childCount = gridWrapper.children.length;
			const bullets = document.createElement('div');
			bullets.classList.add('glide__bullets');
			bullets.setAttribute('data-glide-el', 'controls[nav]');

			for (let i = 0; i < childCount; i++) {
				const bullet = document.createElement('button');
				bullet.classList.add('glide__bullet');
				bullet.setAttribute('data-glide-dir', '=' + i);
				bullet.style.backgroundColor = frblButtonBgColor;
				bullets.appendChild(bullet);
			}

			glideWrapper.appendChild(bullets);

			// Add custom CSS for active bullet.
			if (!document.getElementById('frontblocks-bullet-style-' + blockId)) {
				const style = document.createElement('style');
				style.id = 'frontblocks-bullet-style-' + blockId;
				style.textContent = `
					.glide__bullet.glide__bullet--active {
						background-color: ${frblButtonColor} !important;
					}
				`;
				document.head.appendChild(style);
			}
		}

		// Initialize Glide.
		if (typeof Glide !== 'undefined') {
			const carouselType = frblGridOption;
			const carouselView = parseInt(frblItemsToView) || 4;
			const carouselResView = parseInt(frblResponsiveToView) || 1;
			const carouselAutoplay = frblAutoplay ? parseInt(frblAutoplay) * 1000 : 0;
			
			try {
				const glideInstance = new Glide(glideWrapper, {
					type: carouselType,
					perView: carouselView,
					startAt: 0,
					autoplay: carouselAutoplay === 0 ? false : carouselAutoplay,
					gap: 20,
					rewind: frblRewind,
					breakpoints: {
						430: { perView: carouselResView },
						600: { perView: carouselResView },
						768: { perView: carouselResView }
					}
				});

				if (carouselType === 'slider') {
					glideInstance.on('run.after', () => {
						const currentIndex = glideInstance.index;
						const lastIndex = glideWrapper.querySelectorAll('.glide__slide').length;
						const actualView = parseInt(currentIndex) + parseInt(carouselView);

						if (actualView > lastIndex) {
							setTimeout(() => {
								glideInstance.go('=0');
							}, 5);
						}
					});
				}

				glideInstance.mount();
				initialized.add(blockId);
			} catch (error) {
				console.error('Error initializing Glide carousel:', error);
			}
		}
	}

	// Function to cleanup carousel.
	function cleanupCarousel(blockId) {
		initialized.delete(blockId);
		const style = document.getElementById('frontblocks-bullet-style-' + blockId);
		if (style) {
			style.remove();
		}
	}

	// Function to process all carousel blocks in the editor.
	function processCarouselBlocks() {
		// Get all blocks with carousel enabled.
		const blocks = document.querySelectorAll('[data-block]');
		
		blocks.forEach(blockElement => {
			const blockId = blockElement.getAttribute('data-block');
			
			// Try to get block data from store.
			try {
				const block = wp.data.select('core/block-editor').getBlock(blockId);
				
				if (!block) {
					return;
				}

				// Check if it's a grid or element block.
				if (block.name !== 'generateblocks/grid' && block.name !== 'generateblocks/element') {
					return;
				}

				// For element blocks, check if it has grid display.
				if (block.name === 'generateblocks/element') {
					const styles = block.attributes.styles || {};
					if (styles.display !== 'grid') {
						return;
					}
				}

				// Check if carousel is enabled.
				const frblGridOption = block.attributes.frblGridOption || 'none';
				if (frblGridOption === 'none') {
					cleanupCarousel(blockId);
					return;
				}

				// Initialize carousel with a delay to ensure DOM is ready.
				setTimeout(() => {
					initializeCarousel(blockElement, block.attributes);
				}, 100);
			} catch (error) {
				// Block might not be ready yet, skip.
			}
		});
	}

	// Subscribe to editor changes.
	let lastBlocks = null;
	
	subscribe(() => {
		const blocks = wp.data.select('core/block-editor').getBlocks();
		
		// Check if blocks have changed.
		if (JSON.stringify(blocks) !== JSON.stringify(lastBlocks)) {
			lastBlocks = blocks;
			
			// Process carousel blocks after a short delay.
			setTimeout(processCarouselBlocks, 300);
		}
	});

	// Initial processing.
	window.addEventListener('load', () => {
		setTimeout(processCarouselBlocks, 500);
	});

	// Process when editor is ready.
	wp.domReady(() => {
		setTimeout(processCarouselBlocks, 500);
	});
})();

