// Add custom controls to the Advanced panel of GenerateBlocks Grid block
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls, PanelColorSettings } = wp.blockEditor;
const { SelectControl, TextControl, PanelBody, ToggleControl } = wp.components;
const { __ } = wp.i18n;

function addCustomCarouselPanel(BlockEdit) {
	return (props) => {
		if (props.name !== 'generateblocks/container') {
				return <BlockEdit {...props} />;
		}

		const {
				frblGridOption = 'none',
				frblItemsToView = '4',
				frblResponsiveToView = '1',
				frblAutoplay = '',
				frblButtons = 'arrows',
				frblRewind = true,
				frblButtonColor,
				frblButtonBgColor,
				frblButtonsPosition = 'side',
		} = props.attributes;

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody
						title={__('FullPage Settings', 'frontblocks-pro')}
						initialOpen={true}
					>
						<SelectControl
							label={__('FrontBlocks Grid Option', 'frontblocks-pro')}
							value={frblGridOption}
							options={[
								{ label: __('None', 'frontblocks-pro'), value: 'none' },
								{ label: __('Carousel', 'frontblocks-pro'), value: 'carousel' },
								{ label: __('Slider', 'frontblocks-pro'), value: 'slider' }
							]}
							onChange={(value) => {
								props.setAttributes({ frblGridOption: value });
							}}
							help={__('This option gives the option to make carousel in your grid block.', 'frontblocks-pro')}
						/>
						{frblGridOption !== 'none' && (
							<>
								<TextControl
									label={__('Items to view', 'frontblocks-pro')}
									value={frblItemsToView}
									onChange={(value) => props.setAttributes({ frblItemsToView: value })}
								/>
								<TextControl
									label={__('Responsive to view', 'frontblocks-pro')}
									value={frblResponsiveToView}
									onChange={(value) => props.setAttributes({ frblResponsiveToView: value })}
								/>
								<TextControl
									label={__('Autoplay (seconds)', 'frontblocks-pro')}
									value={frblAutoplay}
									onChange={(value) => props.setAttributes({ frblAutoplay: value })}
								/>
                                {frblGridOption === 'slider' && (
                                    <>
                                        <ToggleControl
                                            label={__('Rewind', 'frontblocks-pro')}
                                            checked={frblRewind}
                                            onChange={(value) => props.setAttributes({ frblRewind: value })}
                                        />
                                    </>
                                )}
								<SelectControl
									label={__('Buttons', 'frontblocks-pro')}
									value={frblButtons}
									options={[
										{ label: __('None', 'frontblocks-pro'), value: 'none' },
										{ label: __('Bullets', 'frontblocks-pro'), value: 'bullets' },
										{ label: __('Arrows', 'frontblocks-pro'), value: 'arrows' }
									]}
									onChange={(value) => props.setAttributes({ frblButtons: value })}
								/>
                                {frblButtons === 'arrows' && (
                                    <>
                                        <SelectControl
                                            label={__('Buttons Position', 'frontblocks-pro')}
                                            value={frblButtonsPosition}
                                            options={[
                                                { label: __('Side', 'frontblocks-pro'), value: 'side' },
                                                { label: __('Bottom', 'frontblocks-pro'), value: 'bottom' },
                                            ]}
                                            onChange={(value) => props.setAttributes({ frblButtonsPosition: value })}
                                        />
                                    </>
                                )}
								<PanelColorSettings
									title={__('Button Colors', 'frontblocks-pro')}
									colorSettings={[
										{
											value: frblButtonColor,
											onChange: (color) => props.setAttributes({ frblButtonColor: color }),
											label: __('Color button', 'frontblocks-pro'),
										},
										{
											value: frblButtonBgColor,
											onChange: (color) => props.setAttributes({ frblButtonBgColor: color }),
											label: __('Color background button', 'frontblocks-pro'),
										},
									]}
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