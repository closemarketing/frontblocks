const { createHigherOrderComponent } = wp.compose;
const { Fragment, useEffect } = wp.element;
const { InspectorControls } = wp.blockEditor; 
const { PanelBody, ToggleControl, TextControl } = wp.components;
const { select, dispatch } = wp.data;
const { __ } = wp.i18n;

wp.hooks.addFilter(
    'blocks.registerBlockType',
    'frontblocks/add-counter-attribute',
    ( settings, name ) => {
        if ( name === 'generateblocks/text' || name === 'generateblocks/headline' ) {
            settings.attributes = Object.assign( settings.attributes, {
                isCounterActive: {
                    type: 'boolean',
                    default: false,
                },
                animationDuration: { 
                    type: 'number',
                    default: 2000,
                },
                finalNumber: {
                    type: 'string',
                    default: '',
                },
                numberPrefix: {
                    type: 'string',
                    default: '',
                },
                numberSuffix: {
                    type: 'string',
                    default: '',
                },
            } );
        }
        return settings;
    }
);

const withHeadlineCounterControl = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        if ( props.name !== BLOCK_NAME ) {
            return <BlockEdit { ...props } />;
        }

        const { attributes, setAttributes, clientId } = props;
        const { isCounterActive, animationDuration, finalNumber, numberPrefix, numberSuffix } = attributes;
        
        const durationInSeconds = animationDuration / 1000;

        useEffect(() => {
            if (isCounterActive) {
                const block = select('core/block-editor').getBlock(clientId);
                if (!block) return;

                const formattedNumber = `${numberPrefix}${finalNumber}${numberSuffix}`;
                
                if (finalNumber && block.attributes.content !== formattedNumber) {
                    dispatch('core/block-editor').updateBlockAttributes(clientId, {
                        content: formattedNumber
                    });
                }
            }
        }, [isCounterActive, finalNumber, numberPrefix, numberSuffix, clientId]);

        return (
            <Fragment>
                <BlockEdit { ...props } />

                <InspectorControls>
                    <PanelBody 
                        title={ __( 'FrontBlocks - Counter Effect', 'frontblocks' ) }
                        initialOpen={ false }
                    >
                        <ToggleControl
                            label={ __( 'Activate Counter Effect', 'frontblocks' ) }
                            help={ isCounterActive ? 
                                __( 'The number in the headline will animate on scroll.', 'frontblocks' ) : 
                                __( 'Enable to activate counting animation.', 'frontblocks' )
                            }
                            checked={ isCounterActive }
                            onChange={ ( val ) => setAttributes( { isCounterActive: val } ) }
                        />

                        {isCounterActive && (
                            <>
                                <TextControl
                                    label={ __( 'Final Number', 'frontblocks' ) }
                                    value={ finalNumber }
                                    onChange={ (val) => setAttributes({ finalNumber: val }) }
                                    help={ __( 'The number to count up to (e.g.: 100).', 'frontblocks' ) }
                                />

                                <TextControl
                                    label={ __( 'Number Prefix', 'frontblocks' ) }
                                    value={ numberPrefix }
                                    onChange={ (val) => setAttributes({ numberPrefix: val }) }
                                    help={ __( 'Text to display before the number (e.g.: $, €).', 'frontblocks' ) }
                                />

                                <TextControl
                                    label={ __( 'Number Suffix', 'frontblocks' ) }
                                    value={ numberSuffix }
                                    onChange={ (val) => setAttributes({ numberSuffix: val }) }
                                    help={ __( 'Text to display after the number (e.g.: %, +).', 'frontblocks' ) }
                                />

                                <TextControl
                                    label={ __( 'Animation Duration (seconds)', 'frontblocks' ) }
                                    value={ durationInSeconds }
                                    onChange={ (val) => {
                                        const seconds = parseFloat(val);
                                        const milliseconds = isNaN(seconds) ? 0 : seconds * 1000;
                                        setAttributes({ animationDuration: milliseconds });
                                    } }
                                    type="number"
                                    step="0.1"
                                    help={ __( 'Time in seconds for the animation.', 'frontblocks' ) }
                                />
                            </>
                        )}

                        <p style={ { marginTop: '10px', fontSize: '12px', color: '#777' } }>
                            <small>{ __( "Make sure the text begins with a number (e.g., '123', '+500', or '€100').", 'frontblocks' ) }</small>
                        </p>
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withHeadlineCounterControl' );

wp.hooks.addFilter(
    'editor.BlockEdit',
    'frontblocks/headline-counter-control',

    withHeadlineCounterControl
);