(function() {
    'use strict';

    const { __ } = wp.i18n;
    const { createElement: el } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl, RangeControl } = wp.components;
    const { addFilter } = wp.hooks;
    const { createHigherOrderComponent } = wp.compose;
    const { apiFetch } = wp;

    // Intercept Gravity Forms API requests and remove custom attributes.
    if (apiFetch && apiFetch.use) {
        apiFetch.use(function(options, next) {
            if (options.path && options.path.includes('/wp/v2/block-renderer/gravityforms/form')) {
                // Remove our custom attributes from the URL.
                options.path = options.path
                    .replace(/&attributes%5BfrblGfInlineEnabled%5D=[^&]*/g, '')
                    .replace(/&attributes%5BfrblGfInlineGap%5D=[^&]*/g, '')
                    .replace(/attributes%5BfrblGfInlineEnabled%5D=[^&]*&/g, '')
                    .replace(/attributes%5BfrblGfInlineGap%5D=[^&]*&/g, '');
            }
            return next(options);
        });
    }

    // Add inline layout controls to the Gravity Forms block.
    addFilter(
        'editor.BlockEdit',
        'frontblocks/gf-inline-controls',
        function(BlockEdit) {
            return function(props) {
                // Only add controls to Gravity Forms block.
                if (props.name !== 'gravityforms/form') {
                    return el(BlockEdit, props);
                }

                // Check if a form is selected.
                const { attributes, setAttributes } = props;
                const {
                    frblGfInlineEnabled = false,
                    frblGfInlineGap = 10,
                    formId = 0
                } = attributes;

                // Only show options if a form is selected.
                const showInlineOptions = formId && formId > 0;

                return el(
                    'div',
                    {},
                    el(BlockEdit, props),
                    showInlineOptions && el(
                        InspectorControls,
                        {},
                        el(
                            PanelBody,
                            {
                                title: __('FrontBlocks From GravityForms', 'frontblocks'),
                                initialOpen: false
                            },
                            el(ToggleControl, {
                                label: __('Enable Inline Layout', 'frontblocks'),
                                checked: frblGfInlineEnabled,
                                onChange: (value) => setAttributes({ frblGfInlineEnabled: value }),
                                help: __('Display form fields and button on the same line', 'frontblocks')
                            }),
                            frblGfInlineEnabled && el(
                                RangeControl,
                                {
                                    label: __('Gap between elements (px)', 'frontblocks'),
                                    value: frblGfInlineGap,
                                    onChange: (value) => setAttributes({ frblGfInlineGap: value }),
                                    min: 0,
                                    max: 50,
                                    step: 1,
                                    help: __('Space between form fields and button', 'frontblocks')
                                }
                            )
                        )
                    )
                );
            };
        }
    );

    // Add custom class to block wrapper in editor.
    addFilter(
        'editor.BlockListBlock',
        'frontblocks/gf-inline-wrapper-class',
        createHigherOrderComponent(function(BlockListBlock) {
            return function(props) {
                if (props.name === 'gravityforms/form') {
                    const { attributes } = props;
                    if (attributes.frblGfInlineEnabled) {
                        // Apply gap CSS variable to editor preview.
                        if (typeof window !== 'undefined') {
                            setTimeout(function() {
                                const blockElement = document.querySelector('[data-block="' + props.clientId + '"]');
                                if (blockElement) {
                                    blockElement.style.setProperty('--gf-inline-gap', (attributes.frblGfInlineGap || 10) + 'px');
                                }
                            }, 100);
                        }
                        
                        return el(BlockListBlock, {
                            ...props,
                            className: 'frontblocks-gf-inline-preview'
                        });
                    }
                }
                return el(BlockListBlock, props);
            };
        }, 'withInlineClass')
    );

})();

