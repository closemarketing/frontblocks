/**
 * FrontBlocks Testimonials Block
 *
 * A fully customizable testimonials carousel block
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, RangeControl, SelectControl, ToggleControl, ColorPalette } = wp.components;
const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { RawHTML, useEffect, useRef } = wp.element;

registerBlockType('frontblocks/testimonials', {
	title: __('FrontBlocks Testimonials', 'frontblocks'),
	description: __('Display testimonials in a customizable carousel', 'frontblocks'),
	category: 'widgets',
	icon: 'testimonial',
	attributes: {
		numberOfTestimonials: {
			type: 'number',
			default: -1
		},
		itemsToView: {
			type: 'number',
			default: 3
		},
		itemsToViewLaptop: {
			type: 'number',
			default: 2
		},
		itemsToViewTablet: {
			type: 'number',
			default: 2
		},
		itemsToViewMobile: {
			type: 'number',
			default: 1
		},
		autoplay: {
			type: 'number',
			default: 6000
		},
		navigationStyle: {
			type: 'string',
			default: 'bullets'
		},
		showStars: {
			type: 'boolean',
			default: true
		},
		showImage: {
			type: 'boolean',
			default: true
		},
		buttonColor: {
			type: 'string',
			default: '#000000'
		},
		buttonBgColor: {
			type: 'string',
			default: 'transparent'
		}
	},

	edit: function(props) {
		const { attributes, setAttributes } = props;
		const {
			numberOfTestimonials,
			itemsToView,
			itemsToViewLaptop,
			itemsToViewTablet,
			itemsToViewMobile,
			autoplay,
			navigationStyle,
			showStars,
			showImage,
			buttonColor,
			buttonBgColor
		} = attributes;

		const blockProps = useBlockProps({
			className: 'frontblocks-testimonials-block-editor'
		});

		const carouselRef = useRef(null);
		const glideInstanceRef = useRef(null);

		// Get testimonials from WordPress
		const testimonials = useSelect((select) => {
			const records = select('core').getEntityRecords('postType', 'fbrl_testimonial', {
				per_page: numberOfTestimonials === -1 ? 100 : numberOfTestimonials,
				status: 'publish',
				_embed: true
			});

			// Add featured image URL to each testimonial
			if (records) {
				return records.map((record) => {
					let featuredMediaUrl = '';
					if (record._embedded && record._embedded['wp:featuredmedia']) {
						featuredMediaUrl = record._embedded['wp:featuredmedia'][0]?.source_url || '';
					}
					return {
						...record,
						featured_media_url: featuredMediaUrl
					};
				});
			}

			return records;
		}, [numberOfTestimonials]);

		// Initialize carousel when testimonials load
		useEffect(() => {
			if (!testimonials || testimonials.length === 0) {
				return;
			}

			// Wait for DOM to be ready
			const timer = setTimeout(() => {
				const carouselElement = carouselRef.current;
				
				if (!carouselElement || !window.Glide) {
					return;
				}

				// Destroy previous instance if exists
				if (glideInstanceRef.current) {
					try {
						glideInstanceRef.current.destroy();
						glideInstanceRef.current = null;
					} catch (e) {
						console.warn('Error destroying Glide instance:', e);
					}
				}

				// Get original slides container
				const originalSlidesContainer = carouselElement.querySelector('.testimonials-carousel-wrapper');
				if (!originalSlidesContainer) {
					return;
				}

				// Store original content
				const originalSlides = Array.from(originalSlidesContainer.children);
				
				// Create glide wrapper structure from scratch
				const glideWrapper = document.createElement('div');
				glideWrapper.className = 'glide frontblocks-testimonials-glide';
				
				const glideTrack = document.createElement('div');
				glideTrack.className = 'glide__track';
				glideTrack.setAttribute('data-glide-el', 'track');
				
				const slidesContainer = document.createElement('div');
				slidesContainer.className = 'glide__slides';
				
				// Add slides
				originalSlides.forEach(slide => {
					const slideClone = slide.cloneNode(true);
					slideClone.classList.add('glide__slide');
					slidesContainer.appendChild(slideClone);
				});
				
				glideTrack.appendChild(slidesContainer);
				glideWrapper.appendChild(glideTrack);

				// Add navigation if needed
				if (navigationStyle === 'arrows') {
					const arrows = document.createElement('div');
					arrows.className = 'glide__arrows';
					arrows.setAttribute('data-glide-el', 'controls');
					arrows.innerHTML = `
						<button class="glide__arrow glide__arrow--left" data-glide-dir="<" style="background-color: ${buttonBgColor || 'transparent'}">
							<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M6 1L1 6L6 11" stroke="${buttonColor || '#000000'}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
						<button class="glide__arrow glide__arrow--right" data-glide-dir=">" style="background-color: ${buttonBgColor || 'transparent'}">
							<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 11L6 6L1 1" stroke="${buttonColor || '#000000'}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
					`;
					glideWrapper.appendChild(arrows);
				}

				if (navigationStyle === 'bullets') {
					const bullets = document.createElement('div');
					bullets.className = 'glide__bullets';
					bullets.setAttribute('data-glide-el', 'controls[nav]');
					
					for (let i = 0; i < testimonials.length; i++) {
						const bullet = document.createElement('button');
						bullet.className = 'glide__bullet';
						bullet.setAttribute('data-glide-dir', '=' + i);
						bullet.style.backgroundColor = buttonBgColor || 'transparent';
						bullets.appendChild(bullet);
					}
					
					glideWrapper.appendChild(bullets);

					// Remove old style if exists
					const oldStyle = document.getElementById('frontblocks-testimonials-bullets-style');
					if (oldStyle) {
						oldStyle.remove();
					}

					// Add custom CSS for active bullet
					const style = document.createElement('style');
					style.id = 'frontblocks-testimonials-bullets-style';
					style.textContent = `
						.frontblocks-testimonials-glide .glide__bullet.glide__bullet--active {
							background-color: ${buttonColor || '#000000'} !important;
						}
					`;
					document.head.appendChild(style);
				}

				// Clear and add new structure
				carouselElement.innerHTML = '';
				carouselElement.appendChild(glideWrapper);

				// Initialize Glide
				try {
					glideInstanceRef.current = new window.Glide(glideWrapper, {
						type: 'carousel',
						perView: itemsToView || 3,
						startAt: 0,
						autoplay: autoplay > 0 ? autoplay : false,
						gap: 20,
						animationDuration: 400,
						rewind: true,
						breakpoints: {
							768: {
								perView: itemsToViewMobile || 1
							},
							1024: {
								perView: itemsToViewTablet || 2
							},
							1440: {
								perView: itemsToViewLaptop || 2
							}
						}
					});

					glideInstanceRef.current.mount();
				} catch (e) {
					console.error('Error initializing Glide in editor:', e);
				}
			}, 500);

			// Cleanup function
			return () => {
				clearTimeout(timer);
				if (glideInstanceRef.current) {
					try {
						glideInstanceRef.current.destroy();
						glideInstanceRef.current = null;
					} catch (e) {
						console.warn('Error cleaning up Glide instance:', e);
					}
				}
			};
		}, [testimonials, itemsToView, itemsToViewLaptop, itemsToViewTablet, itemsToViewMobile, autoplay, navigationStyle, buttonColor, buttonBgColor, showStars, showImage]);

		return (
			<div {...blockProps}>
				<InspectorControls>
					<PanelBody title={__('Testimonials Settings', 'frontblocks')} initialOpen={true}>
						<RangeControl
							label={__('Number of Testimonials', 'frontblocks')}
							value={numberOfTestimonials}
							onChange={(value) => setAttributes({ numberOfTestimonials: value })}
							min={-1}
							max={20}
							help={__('Set to -1 to show all testimonials', 'frontblocks')}
						/>
						<ToggleControl
							label={__('Show Images', 'frontblocks')}
							checked={showImage}
							onChange={(value) => setAttributes({ showImage: value })}
						/>
						<ToggleControl
							label={__('Show Star Ratings', 'frontblocks')}
							checked={showStars}
							onChange={(value) => setAttributes({ showStars: value })}
						/>
					</PanelBody>

					<PanelBody title={__('Carousel Settings', 'frontblocks')} initialOpen={false}>
						<RangeControl
							label={__('Items to View (Desktop)', 'frontblocks')}
							value={itemsToView}
							onChange={(value) => setAttributes({ itemsToView: value })}
							min={1}
							max={6}
						/>
						<RangeControl
							label={__('Items to View (Laptop)', 'frontblocks')}
							value={itemsToViewLaptop}
							onChange={(value) => setAttributes({ itemsToViewLaptop: value })}
							min={1}
							max={4}
						/>
						<RangeControl
							label={__('Items to View (Tablet)', 'frontblocks')}
							value={itemsToViewTablet}
							onChange={(value) => setAttributes({ itemsToViewTablet: value })}
							min={1}
							max={3}
						/>
						<RangeControl
							label={__('Items to View (Mobile)', 'frontblocks')}
							value={itemsToViewMobile}
							onChange={(value) => setAttributes({ itemsToViewMobile: value })}
							min={1}
							max={2}
						/>
						<RangeControl
							label={__('Autoplay Speed (ms)', 'frontblocks')}
							value={autoplay}
							onChange={(value) => setAttributes({ autoplay: value })}
							min={0}
							max={10000}
							step={500}
							help={__('Set to 0 to disable autoplay', 'frontblocks')}
						/>
						<SelectControl
							label={__('Navigation Style', 'frontblocks')}
							value={navigationStyle}
							options={[
								{ label: __('Bullets', 'frontblocks'), value: 'bullets' },
								{ label: __('Arrows', 'frontblocks'), value: 'arrows' },
								{ label: __('None', 'frontblocks'), value: 'none' }
							]}
							onChange={(value) => setAttributes({ navigationStyle: value })}
						/>
					</PanelBody>

					<PanelBody title={__('Button Colors', 'frontblocks')} initialOpen={false}>
						<p><strong>{__('Button Color', 'frontblocks')}</strong></p>
						<ColorPalette
							value={buttonColor}
							onChange={(value) => setAttributes({ buttonColor: value })}
						/>
						<p><strong>{__('Button Background Color', 'frontblocks')}</strong></p>
						<ColorPalette
							value={buttonBgColor}
							onChange={(value) => setAttributes({ buttonBgColor: value })}
						/>
					</PanelBody>
				</InspectorControls>

				<div className="frontblocks-testimonials-preview">
					{!testimonials && (
						<div className="loading-testimonials">
							<span className="dashicons dashicons-update"></span>
							<p>{__('Loading testimonials...', 'frontblocks')}</p>
						</div>
					)}
					
					{testimonials && testimonials.length === 0 && (
						<div className="no-testimonials">
							<span className="dashicons dashicons-testimonial"></span>
							<p>{__('No testimonials found.', 'frontblocks')}</p>
							<p>{__('Create some testimonials to display them here.', 'frontblocks')}</p>
						</div>
					)}
					
					{testimonials && testimonials.length > 0 && (
						<div className="frontblocks-testimonials-carousel-preview">
							<div ref={carouselRef} className="carousel-container">
								<div className="testimonials-carousel-wrapper">
								{testimonials.map((testimonial) => {
									// Get star rating from meta
									const stars = testimonial.meta?.frontblocks_stars || 0;
									
									return (
										<div key={testimonial.id} className="testimonial-card">
											<div className="testimonial-header">
												{showImage && testimonial.featured_media_url && (
													<div className="testimonial-image-container">
														<img 
															src={testimonial.featured_media_url} 
															alt={testimonial.title.rendered} 
															className="testimonial-image" 
														/>
													</div>
												)}
											</div>
											<div className="testimonial-content">
												<h3 className="testimonial-name">
													<RawHTML>{testimonial.title.rendered}</RawHTML>
												</h3>
												<p className="testimonial-text">
													"<RawHTML>{testimonial.content.rendered}</RawHTML>"
												</p>
												{showStars && stars > 0 && (
													<div className="stars-container">
														{[1, 2, 3, 4, 5].map((i) => (
															<span key={i} className={`star ${i <= stars ? 'filled' : ''}`}>
																★
															</span>
														))}
													</div>
												)}
											</div>
										</div>
									);
								})}
								</div>
							</div>
							<div className="preview-info">
								<span className="dashicons dashicons-info"></span>
								{__('Preview:', 'frontblocks')} {testimonials.length} {__('testimonials', 'frontblocks')} | 
								{__(' Items to view:', 'frontblocks')} {itemsToView} (Desktop), {itemsToViewMobile} (Mobile) | 
								{__(' Navigation:', 'frontblocks')} {navigationStyle}
							</div>
						</div>
					)}
				</div>
			</div>
		);
	},

	save: function() {
		// Render in PHP for better performance and dynamic content
		return null;
	}
});

