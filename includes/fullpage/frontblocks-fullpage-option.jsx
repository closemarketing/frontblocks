// Add custom controls to the Advanced panel of GenerateBlocks Container block
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls, PanelColorSettings } = wp.blockEditor;
const { SelectControl, TextControl, PanelBody, ToggleControl, RangeControl } = wp.components;
const { __ } = wp.i18n;

function addCustomFullpagePanel(BlockEdit) {
	return (props) => {
		if (props.name !== 'generateblocks/container') {
			return <BlockEdit {...props} />;
		}

		const {
			frblFullpageEnabled = false,
			frblShowNavigation = true,
			frblShowScrollbar = true,
			frblNavigationPosition = 'right',
			frblNavigationColor = '#000',
			frblAutoScroll = false,
			frblScrollSpeed = 700,
			frblLoopBottom = false,
			frblLoopTop = false,
			frblScrolloverflow = false,
		} = props.attributes;

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody
						title={__('FullPage Settings', 'frontblocks')}
						initialOpen={false}
					>
						<ToggleControl
							label={__('Enable FullPage', 'frontblocks')}
							checked={frblFullpageEnabled}
							onChange={(value) => props.setAttributes({ frblFullpageEnabled: value })}
							help={__('Enable fullpage.js functionality for this container. Child elements will become vertical sections.', 'frontblocks')}
						/>
						
						{frblFullpageEnabled && (
							<>
								<PanelBody
									title={__('Navigation Options', 'frontblocks')}
									initialOpen={true}
								>
									<ToggleControl
										label={__('Show Navigation Dots', 'frontblocks')}
										checked={frblShowNavigation}
										onChange={(value) => props.setAttributes({ frblShowNavigation: value })}
									/>
									
									{frblShowNavigation && (
										<>
											<SelectControl
												label={__('Navigation Position', 'frontblocks')}
												value={frblNavigationPosition}
												options={[
													{ label: __('Right', 'frontblocks'), value: 'right' },
													{ label: __('Left', 'frontblocks'), value: 'left' },
												]}
												onChange={(value) => props.setAttributes({ frblNavigationPosition: value })}
											/>
											<PanelColorSettings
												title={__('Navigation Colors', 'frontblocks')}
												colorSettings={[
													{
														value: frblNavigationColor,
														onChange: (color) => props.setAttributes({ frblNavigationColor: color }),
														label: __('Navigation Color', 'frontblocks'),
													},
												]}
											/>
										</>
									)}
								</PanelBody>



								<PanelBody
									title={__('Scrollbar Options', 'frontblocks')}
									initialOpen={true}
								>
									<ToggleControl
										label={__('Show Scrollbar', 'frontblocks')}
										checked={frblShowScrollbar}
										onChange={(value) => props.setAttributes({ frblShowScrollbar: value })}
										help={__('Show scrollbar for scrolloverflow sections', 'frontblocks')}
									/>
								</PanelBody>

								<PanelBody
									title={__('Scroll Behavior', 'frontblocks')}
									initialOpen={true}
								>
									<ToggleControl
										label={__('Auto Scroll', 'frontblocks')}
										checked={frblAutoScroll}
										onChange={(value) => props.setAttributes({ frblAutoScroll: value })}
										help={__('Automatically scroll through sections', 'frontblocks')}
									/>
									
									<RangeControl
										label={__('Scroll Speed (ms)', 'frontblocks')}
										value={frblScrollSpeed}
										onChange={(value) => props.setAttributes({ frblScrollSpeed: value })}
										min={100}
										max={2000}
										step={100}
										help={__('Speed of scrolling animation in milliseconds', 'frontblocks')}
									/>
									
									<ToggleControl
										label={__('Loop to Bottom', 'frontblocks')}
										checked={frblLoopBottom}
										onChange={(value) => props.setAttributes({ frblLoopBottom: value })}
										help={__('Loop from last section to first', 'frontblocks')}
									/>
									
									<ToggleControl
										label={__('Loop to Top', 'frontblocks')}
										checked={frblLoopTop}
										onChange={(value) => props.setAttributes({ frblLoopTop: value })}
										help={__('Loop from first section to last', 'frontblocks')}
									/>
									
									<ToggleControl
										label={__('Enable Scroll Overflow', 'frontblocks')}
										checked={frblScrolloverflow}
										onChange={(value) => props.setAttributes({ frblScrolloverflow: value })}
										help={__('Allow sections to scroll internally when content is larger than viewport', 'frontblocks')}
									/>
								</PanelBody>
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
	'frontblocks/gb-container-fullpage-panel',
	addCustomFullpagePanel
);