// Add custom controls to the Advanced panel of GenerateBlocks Grid block
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls, PanelColorSettings } = wp.blockEditor;
const { SelectControl, TextControl, PanelBody, ToggleControl } = wp.components;
const { __ } = wp.i18n;

function addCustomCarouselPanel(BlockEdit) {
	return (props) => {
		// Support grid blocks, element blocks with grid display, and core/group blocks
		if (props.name !== 'generateblocks/grid' && 
		    props.name !== 'generateblocks/element' && 
		    props.name !== 'core/group') {
			return <BlockEdit {...props} />;
		}

		// For element blocks, only show carousel options if it has grid display
		if (props.name === 'generateblocks/element') {
			const styles = props.attributes.styles || {};
			if (styles.display !== 'grid') {
				return <BlockEdit {...props} />;
			}
		}

		// For core/group blocks, only show carousel options if it has grid layout
		if (props.name === 'core/group') {
			const layout = props.attributes.layout || {};
			if (layout.type !== 'grid') {
				return <BlockEdit {...props} />;
			}
		}

	const {
			frblGridOption = 'none',
			frblItemsToView = '4',
			frblLaptopToView = '3',
			frblTabletToView = '2',
			frblResponsiveToView = '1',
			frblAutoplay = '',
			frblGap = '20',
			frblButtons = 'arrows',
			frblRewind = true,
			frblButtonColor,
			frblButtonBgColor,
			frblButtonsPosition = 'side',
			frblDisableOnDesktop = false,
	} = props.attributes;

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody
						title={__('Carousel Settings', 'frontblocks')}
						initialOpen={true}
					>
					<SelectControl
						label={__('FrontBlocks Grid Option', 'frontblocks')}
						value={frblGridOption}
						options={[
							{ label: __('None', 'frontblocks'), value: 'none' },
							{ label: __('Carousel', 'frontblocks'), value: 'carousel' },
							{ label: __('Slider', 'frontblocks'), value: 'slider' }
						]}
						onChange={(value) => {
							props.setAttributes({ frblGridOption: value });
						}}
						help={__('This option gives the option to make carousel in your grid block.', 'frontblocks')}
				/>
				{frblGridOption !== 'none' && (
					<>
					<TextControl
						label={__('Items to view (Desktop)', 'frontblocks')}
						value={frblItemsToView}
						onChange={(value) => props.setAttributes({ frblItemsToView: value })}
						help={__('Number of items to show on desktop (>1200px)', 'frontblocks')}
					/>
					<TextControl
						label={__('Items to view (Laptop)', 'frontblocks')}
						value={frblLaptopToView}
						onChange={(value) => props.setAttributes({ frblLaptopToView: value })}
						help={__('Number of items to show on laptop (992px-1199px)', 'frontblocks')}
					/>
					<TextControl
						label={__('Items to view (Tablet)', 'frontblocks')}
						value={frblTabletToView}
						onChange={(value) => props.setAttributes({ frblTabletToView: value })}
						help={__('Number of items to show on tablet (768px-991px)', 'frontblocks')}
					/>
					<TextControl
						label={__('Items to view (Mobile)', 'frontblocks')}
						value={frblResponsiveToView}
						onChange={(value) => props.setAttributes({ frblResponsiveToView: value })}
						help={__('Number of items to show on mobile (<768px)', 'frontblocks')}
				/>
					<TextControl
						label={__('Autoplay (seconds)', 'frontblocks')}
						value={frblAutoplay}
						onChange={(value) => props.setAttributes({ frblAutoplay: value })}
					/>
					<TextControl
						label={__('Gap (px)', 'frontblocks')}
						value={frblGap}
						onChange={(value) => props.setAttributes({ frblGap: value })}
						help={__('Space between slides in pixels. Leave empty for 20.', 'frontblocks')}
					/>
					{frblGridOption === 'slider' && (
						<>
							<ToggleControl
								label={__('Rewind', 'frontblocks')}
								checked={frblRewind}
								onChange={(value) => props.setAttributes({ frblRewind: value })}
							/>
						</>
				)}
					<SelectControl
						label={__('Buttons', 'frontblocks')}
						value={frblButtons}
						options={[
							{ label: __('None', 'frontblocks'), value: 'none' },
							{ label: __('Bullets', 'frontblocks'), value: 'bullets' },
							{ label: __('Arrows', 'frontblocks'), value: 'arrows' }
						]}
						onChange={(value) => props.setAttributes({ frblButtons: value })}
					/>
					{frblButtons === 'arrows' && (
						<>
							<SelectControl
								label={__('Buttons Position', 'frontblocks')}
								value={frblButtonsPosition}
								options={[
									{ label: __('Side', 'frontblocks'), value: 'side' },
									{ label: __('Bottom', 'frontblocks'), value: 'bottom' },
								]}
								onChange={(value) => props.setAttributes({ frblButtonsPosition: value })}
							/>
						</>
				)}
					<PanelColorSettings
						title={__('Button Colors', 'frontblocks')}
						colorSettings={[
							{
								value: frblButtonColor,
								onChange: (color) => props.setAttributes({ frblButtonColor: color }),
								label: __('Color button', 'frontblocks'),
							},
							{
								value: frblButtonBgColor,
								onChange: (color) => props.setAttributes({ frblButtonBgColor: color }),
								label: __('Color background button', 'frontblocks'),
							},
					]}
					/>
					<ToggleControl
						label={__('Disable on Desktop', 'frontblocks')}
						checked={frblDisableOnDesktop}
						onChange={(value) => props.setAttributes({ frblDisableOnDesktop: value })}
						help={__('If enabled, carousel/slider will only work on mobile devices.', 'frontblocks')}
					/>
					</>
				)}
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}

addFilter(
    'editor.BlockEdit',
    'frontblocks/gb-grid-carousel-panel',
    addCustomCarouselPanel
);