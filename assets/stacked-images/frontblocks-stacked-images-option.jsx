const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, RangeControl, Button, Placeholder } = wp.components;
const { __ } = wp.i18n;

/**
 * Edit component for Stacked Images block.
 */
function StackedImagesEdit(props) {
	const { attributes, setAttributes } = props;
	const { images, direction, animationDuration, animationDelay, containerHeight } = attributes;

	const blockProps = useBlockProps();

	const onSelectImages = (newImages) => {
		setAttributes({
			images: newImages.map(img => ({
				id: img.id,
				url: img.url,
				alt: img.alt || '',
			})),
		});
	};

	const removeImage = (indexToRemove) => {
		const newImages = images.filter((img, index) => index !== indexToRemove);
		setAttributes({ images: newImages });
	};

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={__('Images', 'frontblocks')} initialOpen={true}>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={onSelectImages}
							allowedTypes={['image']}
							multiple={true}
							value={images.map(img => img.id)}
							render={({ open }) => (
								<Button 
									isPrimary 
									onClick={open}
									style={{ width: '100%', marginBottom: '10px' }}
								>
									{images.length === 0 
										? __('Add Images', 'frontblocks')
										: __('Change Images (' + images.length + ')', 'frontblocks')
									}
								</Button>
							)}
						/>
					</MediaUploadCheck>
					{images.length > 0 && (
						<Button 
							isDestructive 
							onClick={() => setAttributes({ images: [] })}
							style={{ width: '100%' }}
						>
							{__('Remove All Images', 'frontblocks')}
						</Button>
					)}
				</PanelBody>

				<PanelBody title={__('Settings', 'frontblocks')} initialOpen={true}>
					<SelectControl
						label={__('Animation Direction', 'frontblocks')}
						value={direction}
						options={[
							{ label: __('From Bottom', 'frontblocks'), value: 'bottom' },
							{ label: __('From Top', 'frontblocks'), value: 'top' },
							{ label: __('From Left', 'frontblocks'), value: 'left' },
							{ label: __('From Right', 'frontblocks'), value: 'right' },
						]}
						onChange={(value) => setAttributes({ direction: value })}
					/>
					<RangeControl
						label={__('Animation Duration (ms)', 'frontblocks')}
						value={animationDuration}
						onChange={(value) => setAttributes({ animationDuration: value })}
						min={200}
						max={3000}
						step={100}
					/>
					<RangeControl
						label={__('Delay Between Images (ms)', 'frontblocks')}
						value={animationDelay}
						onChange={(value) => setAttributes({ animationDelay: value })}
						min={0}
						max={2000}
						step={100}
					/>
					<RangeControl
						label={__('Container Height (px)', 'frontblocks')}
						value={containerHeight}
						onChange={(value) => setAttributes({ containerHeight: value })}
						min={200}
						max={1000}
						step={50}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="frbl-stacked-images-editor">
					{images.length === 0 ? (
						<Placeholder
							icon="format-gallery"
							label={__('Stacked Images', 'frontblocks')}
							instructions={__('Use the settings panel on the right to add images →', 'frontblocks')}
						/>
					) : (
						<div>
							<div 
								className="frbl-stacked-images-preview" 
								style={{ height: containerHeight + 'px', position: 'relative' }}
							>
								{images.map((image, index) => (
									<div
										key={index}
										className="frbl-stacked-image-preview"
										style={{
											position: 'absolute',
											top: 0,
											left: 0,
											width: '100%',
											height: '100%',
											zIndex: index + 1,
											transform: `rotate(${(Math.random() * 20) - 10}deg)`,
										}}
									>
										<img
											src={image.url}
											alt={image.alt}
											style={{
												width: '100%',
												height: '100%',
												objectFit: 'contain',
												boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
											}}
										/>
									</div>
								))}
							</div>
							<div style={{ marginTop: '20px', textAlign: 'center', padding: '10px', background: '#f0f0f0', borderRadius: '4px' }}>
								<p style={{ margin: '0', fontSize: '13px', color: '#666' }}>
									<strong>{images.length}</strong> {images.length === 1 ? __('image', 'frontblocks') : __('images', 'frontblocks')} | 
									{__(' Direction: ', 'frontblocks')} <strong>{direction}</strong> | 
									{__(' Duration: ', 'frontblocks')} <strong>{animationDuration}ms</strong> | 
									{__(' Delay: ', 'frontblocks')} <strong>{animationDelay}ms</strong>
								</p>
							</div>
						</div>
					)}
				</div>
			</div>
		</Fragment>
	);
}

// Register the block.
registerBlockType('frontblocks/stacked-images', {
	title: __('Stacked Images', 'frontblocks'),
	description: __('Display images with stacked animation effect from different directions.', 'frontblocks'),
	category: 'media',
	icon: 'format-gallery',
	keywords: [
		__('images', 'frontblocks'),
		__('stacked', 'frontblocks'),
		__('animation', 'frontblocks'),
		__('gallery', 'frontblocks'),
	],
	attributes: {
		images: {
			type: 'array',
			default: [],
		},
		direction: {
			type: 'string',
			default: 'bottom',
		},
		animationDuration: {
			type: 'number',
			default: 1000,
		},
		animationDelay: {
			type: 'number',
			default: 500,
		},
		containerHeight: {
			type: 'number',
			default: 500,
		},
	},
	edit: StackedImagesEdit,
	save: function () {
		return null; // Dynamic block, render on server side.
	},
});
