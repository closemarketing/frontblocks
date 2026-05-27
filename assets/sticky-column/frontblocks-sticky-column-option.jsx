(function() {
    'use strict';

    const { __ } = wp.i18n;
    const { createElement: el } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl, RangeControl, SelectControl } = wp.components;
    const { addFilter } = wp.hooks;
    const { select } = wp.data;

    // Add sticky column controls to the GenerateBlocks Grid block
    addFilter(
        'editor.BlockEdit',
        'frontblocks/sticky-column-controls',
        function(BlockEdit) {
            return function(props) {
                // Only add controls to GenerateBlocks Grid block
                if (props.name !== 'generateblocks/grid') {
                    return el(BlockEdit, props);
                }

                const { attributes, setAttributes } = props;
                const {
                    frblStickyEnabled = false,
                    frblStickyOffset = 0
                } = attributes;
                
                let frblStickyColumnIndex = attributes.frblStickyColumnIndex || 0;

                // Get the current block's inner blocks to count columns
                const block = select('core/block-editor').getBlock(props.clientId);
                const columnCount = block ? block.innerBlocks.length : 0;

                // Create column options for the select control
                const columnOptions = [];
                for (let i = 0; i < columnCount; i++) {
                    columnOptions.push({
                        label: `Column ${i + 1}`,
                        value: i
                    });
                }

                // If no columns found, provide a default option
                if (columnOptions.length === 0) {
                    columnOptions.push({
                        label: __('No columns found', 'frontblocks'),
                        value: 0
                    });
                }

                // Ensure the selected column index is valid
                if (frblStickyColumnIndex >= columnCount) {
                    frblStickyColumnIndex = 0;
                }

                return el(
                    'div',
                    {},
                    el(BlockEdit, props),
                    el(
                        InspectorControls,
                        {},
                        el(
                            PanelBody,
                            {
                                title: __('FrontBlocks: Sticky Column', 'frontblocks'),
                                initialOpen: false
                            },
                            el(ToggleControl, {
                                label: __('Enable Sticky Column', 'frontblocks'),
                                checked: frblStickyEnabled,
                                onChange: (value) => setAttributes({ frblStickyEnabled: value }),
                                help: __('Make a column stick to the top when scrolling', 'frontblocks')
                            }),
                            frblStickyEnabled && el(
                                'div',
                                {},
                                el(RangeControl, {
                                    label: __('Sticky Offset (px)', 'frontblocks'),
                                    value: frblStickyOffset,
                                    onChange: (value) => setAttributes({ frblStickyOffset: value }),
                                    min: 0,
                                    max: 200,
                                    step: 1,
                                    help: __('Distance from top when sticky', 'frontblocks')
                                }),
                                el(SelectControl, {
                                    label: __('Sticky Column', 'frontblocks'),
                                    value: frblStickyColumnIndex,
                                    options: columnOptions,
                                    onChange: (value) => setAttributes({ frblStickyColumnIndex: parseInt(value) }),
                                    help: __('Select which column should be sticky', 'frontblocks')
                                })
                            )
                        )
                    )
                );
            };
        }
    );

})(); 