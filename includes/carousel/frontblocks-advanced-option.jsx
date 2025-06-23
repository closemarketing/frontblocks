// Add custom controls to the Advanced panel of GenerateBlocks Grid block
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls, PanelColorSettings } = wp.blockEditor;
const { SelectControl, TextControl, PanelBody } = wp.components;
const { __ } = wp.i18n;

function addCustomCarouselPanel(BlockEdit) {
    return (props) => {
        if (props.name !== 'generateblocks/grid') {
            return <BlockEdit {...props} />;
        }

        const {
            cmCustomGridOption = 'none',
            cmItemsToView = '4',
            cmResponsiveToView = '1',
            cmAutoplay = '',
            cmButtons = 'arrows',
            cmButtonColor,
            cmButtonBgColor,
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
                            label={__('Custom Grid Option', 'frontblocks')}
                            value={cmCustomGridOption}
                            options={[
                                { label: __('None', 'frontblocks'), value: 'none' },
                                { label: __('Carousel', 'frontblocks'), value: 'carousel' }
                            ]}
                            onChange={(value) => {
                                props.setAttributes({ cmCustomGridOption: value });
                            }}
                            help={__('This is a custom option for the Grid block.', 'frontblocks')}
                        />
                        <TextControl
                            label={__('Items to view', 'frontblocks')}
                            value={cmItemsToView}
                            onChange={(value) => props.setAttributes({ cmItemsToView: value })}
                        />
                        <TextControl
                            label={__('Responsive to view', 'frontblocks')}
                            value={cmResponsiveToView}
                            onChange={(value) => props.setAttributes({ cmResponsiveToView: value })}
                        />
                        <TextControl
                            label={__('Autoplay (ms)', 'frontblocks')}
                            value={cmAutoplay}
                            onChange={(value) => props.setAttributes({ cmAutoplay: value })}
                        />
                        <SelectControl
                            label={__('Buttons', 'frontblocks')}
                            value={cmButtons}
                            options={[
                                { label: __('None', 'frontblocks'), value: 'none' },
                                { label: __('Bullets', 'frontblocks'), value: 'bullets' },
                                { label: __('Arrows', 'frontblocks'), value: 'arrows' }
                            ]}
                            onChange={(value) => props.setAttributes({ cmButtons: value })}
                        />
                        <PanelColorSettings
                            title={__('Button Colors', 'frontblocks')}
                            colorSettings={[
                                {
                                    value: cmButtonColor,
                                    onChange: (color) => props.setAttributes({ cmButtonColor: color }),
                                    label: __('Color button', 'frontblocks'),
                                },
                                {
                                    value: cmButtonBgColor,
                                    onChange: (color) => props.setAttributes({ cmButtonBgColor: color }),
                                    label: __('Color background button', 'frontblocks'),
                                },
                            ]}
                        />
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}

addFilter(
    'editor.BlockEdit',
    'cm/gb-grid-carousel-panel',
    addCustomCarouselPanel
);