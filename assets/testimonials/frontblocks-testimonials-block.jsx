/**
 * Testimonials Block for FrontBlocks
 *
 * @package FrontBlocks
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, RangeControl, ToggleControl, Disabled, Placeholder, Spinner } = wp.components;
const { Fragment, createElement } = wp.element;
const ServerSideRender = wp.serverSideRender;

/**
 * Block Edit Component
 */
function TestimonialsBlockEdit(props) {
	const { attributes, setAttributes } = props;
	const {
		postsCount,
		orderBy,
		order,
		layoutType,
		imagePosition,
		showStars,
		showImage,
		slidesPerView,
		autoplay,
		autoplayDelay,
		showNavigation,
		showPagination,
		starsPosition,
		contentOrder,
		nameAlign,
		textAlign,
		starsAlign,
		imageAlign,
	} = attributes;


	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={__('Alignment Settings', 'frontblocks')} initialOpen={true}>
					<SelectControl
						label={__('Name Alignment', 'frontblocks')}
						value={nameAlign}
						options={[
							{ label: __('Left', 'frontblocks'), value: 'left' },
							{ label: __('Center', 'frontblocks'), value: 'center' },
							{ label: __('Right', 'frontblocks'), value: 'right' },
						]}
						onChange={(value) => setAttributes({ nameAlign: value })}
					/>

					<SelectControl
						label={__('Text Alignment', 'frontblocks')}
						value={textAlign}
						options={[
							{ label: __('Left', 'frontblocks'), value: 'left' },
							{ label: __('Center', 'frontblocks'), value: 'center' },
							{ label: __('Right', 'frontblocks'), value: 'right' },
						]}
						onChange={(value) => setAttributes({ textAlign: value })}
					/>

					<SelectControl
						label={__('Stars Alignment', 'frontblocks')}
						value={starsAlign}
						options={[
							{ label: __('Left', 'frontblocks'), value: 'left' },
							{ label: __('Center', 'frontblocks'), value: 'center' },
							{ label: __('Right', 'frontblocks'), value: 'right' },
						]}
						onChange={(value) => setAttributes({ starsAlign: value })}
					/>

					<SelectControl
						label={__('Image Alignment', 'frontblocks')}
						value={imageAlign}
						options={[
							{ label: __('Left', 'frontblocks'), value: 'left' },
							{ label: __('Center', 'frontblocks'), value: 'center' },
							{ label: __('Right', 'frontblocks'), value: 'right' },
						]}
						onChange={(value) => setAttributes({ imageAlign: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Layout Settings', 'frontblocks')} initialOpen={false}>
					<SelectControl
						label={__('Layout Type', 'frontblocks')}
						value={layoutType}
						options={[
							{ label: __('Carousel', 'frontblocks'), value: 'carousel' },
							{ label: __('Grid', 'frontblocks'), value: 'grid' },
						]}
						onChange={(value) => setAttributes({ layoutType: value })}
					/>

					<SelectControl
						label={__('Content Order', 'frontblocks')}
						value={contentOrder}
						options={[
							{ label: __('Image → Name → Text → Stars', 'frontblocks'), value: 'image-name-text-stars' },
							{ label: __('Name → Image → Text → Stars', 'frontblocks'), value: 'name-image-text-stars' },
							{ label: __('Stars → Name → Text → Image', 'frontblocks'), value: 'stars-name-text-image' },
							{ label: __('Image → Stars → Name → Text', 'frontblocks'), value: 'image-stars-name-text' },
							{ label: __('Text → Name → Stars → Image', 'frontblocks'), value: 'text-name-stars-image' },
						]}
						onChange={(value) => setAttributes({ contentOrder: value })}
					/>

					<SelectControl
						label={__('Image Position', 'frontblocks')}
						value={imagePosition}
						options={[
							{ label: __('Top', 'frontblocks'), value: 'top' },
							{ label: __('Left', 'frontblocks'), value: 'left' },
							{ label: __('Right', 'frontblocks'), value: 'right' },
							{ label: __('None', 'frontblocks'), value: 'none' },
						]}
						onChange={(value) => setAttributes({ imagePosition: value })}
					/>

					<SelectControl
						label={__('Stars Position', 'frontblocks')}
						value={starsPosition}
						options={[
							{ label: __('Above Name', 'frontblocks'), value: 'above-name' },
							{ label: __('Below Name', 'frontblocks'), value: 'below-name' },
							{ label: __('Above Text', 'frontblocks'), value: 'above-text' },
							{ label: __('Below Text', 'frontblocks'), value: 'below-text' },
						]}
						onChange={(value) => setAttributes({ starsPosition: value })}
					/>

					<ToggleControl
						label={__('Show Stars', 'frontblocks')}
						checked={showStars}
						onChange={(value) => setAttributes({ showStars: value })}
					/>

					<ToggleControl
						label={__('Show Image', 'frontblocks')}
						checked={showImage}
						onChange={(value) => setAttributes({ showImage: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Query Settings', 'frontblocks')} initialOpen={false}>
					<RangeControl
						label={__('Number of Testimonials', 'frontblocks')}
						value={postsCount}
						onChange={(value) => setAttributes({ postsCount: value })}
						min={1}
						max={20}
					/>

					<SelectControl
						label={__('Order By', 'frontblocks')}
						value={orderBy}
						options={[
							{ label: __('Date', 'frontblocks'), value: 'date' },
							{ label: __('Title', 'frontblocks'), value: 'title' },
							{ label: __('Random', 'frontblocks'), value: 'rand' },
						]}
						onChange={(value) => setAttributes({ orderBy: value })}
					/>

					<SelectControl
						label={__('Order', 'frontblocks')}
						value={order}
						options={[
							{ label: __('Descending', 'frontblocks'), value: 'DESC' },
							{ label: __('Ascending', 'frontblocks'), value: 'ASC' },
						]}
						onChange={(value) => setAttributes({ order: value })}
					/>
				</PanelBody>

				{layoutType === 'carousel' && (
					<PanelBody title={__('Carousel Settings', 'frontblocks')} initialOpen={false}>
						<RangeControl
							label={__('Slides Per View', 'frontblocks')}
							value={slidesPerView}
							onChange={(value) => setAttributes({ slidesPerView: value })}
							min={1}
							max={6}
						/>

						<ToggleControl
							label={__('Autoplay', 'frontblocks')}
							checked={autoplay}
							onChange={(value) => setAttributes({ autoplay: value })}
						/>

						{autoplay && (
							<RangeControl
								label={__('Autoplay Delay (ms)', 'frontblocks')}
								value={autoplayDelay}
								onChange={(value) => setAttributes({ autoplayDelay: value })}
								min={1000}
								max={10000}
								step={500}
							/>
						)}

						<ToggleControl
							label={__('Show Navigation Arrows', 'frontblocks')}
							checked={showNavigation}
							onChange={(value) => setAttributes({ showNavigation: value })}
						/>

						<ToggleControl
							label={__('Show Pagination Dots', 'frontblocks')}
							checked={showPagination}
							onChange={(value) => setAttributes({ showPagination: value })}
						/>
					</PanelBody>
				)}
			</InspectorControls>

			{createElement(Disabled, null,
				createElement(ServerSideRender, {
					block: "frontblocks/testimonials-carousel",
					attributes: attributes,
					EmptyResponsePlaceholder: () => createElement('div', { 
						className: "frontblocks-testimonials-block-editor" 
					}, 
						createElement('p', { 
							style: { textAlign: 'center', padding: '40px', color: '#666' } 
						}, __('No testimonials found. Create some testimonials to see the preview.', 'frontblocks'))
					),
					ErrorResponsePlaceholder: () => createElement('div', { 
						className: "frontblocks-testimonials-block-editor" 
					},
						createElement('p', { 
							style: { textAlign: 'center', padding: '40px', color: '#d63638' } 
						}, __('Error loading testimonials. Please check your settings.', 'frontblocks'))
					),
					LoadingResponsePlaceholder: () => createElement('div', { 
						className: "frontblocks-testimonials-block-editor" 
					},
						createElement('p', { 
							style: { textAlign: 'center', padding: '40px', color: '#666' } 
						}, __('Loading testimonials...', 'frontblocks'))
					)
				})
			)}
		</Fragment>
	);
}

/**
 * Register the block
 */
registerBlockType('frontblocks/testimonials-carousel', {
	title: __('Testimonials Carousel', 'frontblocks'),
	description: __('Display testimonials in a customizable carousel or grid layout', 'frontblocks'),
	category: 'generateblocks',
	icon: 'testimonial',
	keywords: [
		__('testimonials', 'frontblocks'),
		__('reviews', 'frontblocks'),
		__('carousel', 'frontblocks'),
		__('slider', 'frontblocks'),
	],
	supports: {
		html: false,
		align: ['wide', 'full'],
	},
	attributes: {
		postsCount: {
			type: 'number',
			default: -1,
		},
		orderBy: {
			type: 'string',
			default: 'date',
		},
		order: {
			type: 'string',
			default: 'DESC',
		},
		layoutType: {
			type: 'string',
			default: 'carousel',
		},
		imagePosition: {
			type: 'string',
			default: 'top',
		},
		showStars: {
			type: 'boolean',
			default: true,
		},
		showImage: {
			type: 'boolean',
			default: true,
		},
		slidesPerView: {
			type: 'number',
			default: 3,
		},
		autoplay: {
			type: 'boolean',
			default: true,
		},
		autoplayDelay: {
			type: 'number',
			default: 6000,
		},
		showNavigation: {
			type: 'boolean',
			default: true,
		},
		showPagination: {
			type: 'boolean',
			default: true,
		},
		starsPosition: {
			type: 'string',
			default: 'below-text',
		},
		contentOrder: {
			type: 'string',
			default: 'image-name-text-stars',
		},
		nameAlign: {
			type: 'string',
			default: 'center',
		},
		textAlign: {
			type: 'string',
			default: 'center',
		},
		starsAlign: {
			type: 'string',
			default: 'center',
		},
		imageAlign: {
			type: 'string',
			default: 'center',
		},
	},
	edit: TestimonialsBlockEdit,
	save: () => null, // Using PHP render callback
});

