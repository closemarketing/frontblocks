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
const { RawHTML } = wp.element;

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

		// Get testimonials from WordPress
		const testimonials = useSelect((select) => {
			return select('core').getEntityRecords('postType', 'fbrl_testimonial', {
				per_page: numberOfTestimonials === -1 ? 100 : numberOfTestimonials,
				status: 'publish'
			});
		}, [numberOfTestimonials]);

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
					<div className="testimonials-header">
						<span className="dashicons dashicons-testimonial"></span>
						<h3>{__('FrontBlocks Testimonials', 'frontblocks')}</h3>
					</div>
					
					{!testimonials && (
						<p>{__('Loading testimonials...', 'frontblocks')}</p>
					)}
					
					{testimonials && testimonials.length === 0 && (
						<div className="no-testimonials">
							<p>{__('No testimonials found.', 'frontblocks')}</p>
							<p>{__('Create some testimonials to display them here.', 'frontblocks')}</p>
						</div>
					)}
					
					{testimonials && testimonials.length > 0 && (
						<div className="testimonials-info">
							<p>
								<strong>{__('Testimonials to display:', 'frontblocks')}</strong> {numberOfTestimonials === -1 ? __('All', 'frontblocks') : numberOfTestimonials}
							</p>
							<p>
								<strong>{__('Items per view:', 'frontblocks')}</strong> {itemsToView} (Desktop), {itemsToViewMobile} (Mobile)
							</p>
							<p>
								<strong>{__('Navigation:', 'frontblocks')}</strong> {navigationStyle}
							</p>
							<p>
								<strong>{__('Total available:', 'frontblocks')}</strong> {testimonials.length}
							</p>
							<div className="testimonials-preview-list">
								{testimonials.slice(0, 3).map((testimonial) => (
									<div key={testimonial.id} className="testimonial-preview-item">
										{showImage && testimonial.featured_media && (
											<div className="testimonial-image">
												<img src={testimonial.featured_media} alt={testimonial.title.rendered} />
											</div>
										)}
										<h4><RawHTML>{testimonial.title.rendered}</RawHTML></h4>
										<div className="testimonial-excerpt">
											<RawHTML>{testimonial.excerpt.rendered}</RawHTML>
										</div>
									</div>
								))}
								{testimonials.length > 3 && (
									<p className="more-testimonials">
										{__('... and', 'frontblocks')} {testimonials.length - 3} {__('more', 'frontblocks')}
									</p>
								)}
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

