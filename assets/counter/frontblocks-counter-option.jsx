const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor; 
const { PanelBody, ToggleControl, TextControl } = wp.components;

const BLOCK_NAME = 'generateblocks/text';

wp.hooks.addFilter(
    'blocks.registerBlockType',
    'frontblocks/add-counter-attribute',
    ( settings, name ) => {
        if ( name === BLOCK_NAME ) {
            settings.attributes = Object.assign( settings.attributes, {
                isCounterActive: {
                    type: 'boolean',
                    default: false,
                },
                animationDuration: { 
                    type: 'number',
                    default: 2000,
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

        const { attributes, setAttributes } = props;
        const { isCounterActive, animationDuration } = attributes;
        
        const durationInSeconds = animationDuration / 1000;

        return (
            <Fragment>
                <BlockEdit { ...props } />

                <InspectorControls>
                    <PanelBody 
                        title="Frontblocks - Counter Effect" 
                        initialOpen={ false }
                    >
                        <ToggleControl
                            label="Activate Counter Effect"
                            help={ isCounterActive ? 
                                'The number in the headline will animate on scroll.' : 
                                'Enable to activate counting animation.' 
                            }
                            checked={ isCounterActive }
                            onChange={ ( val ) => setAttributes( { isCounterActive: val } ) }
                        />

                        {isCounterActive && (
                            <TextControl
                                label="Animation Duration (seconds)"
                                value={ durationInSeconds }
                                onChange={ (val) => {
                                    const seconds = parseFloat(val);

                                    const milliseconds = isNaN(seconds) ? 2000 : seconds * 1000;
                                    setAttributes({ animationDuration: milliseconds });
                                } }
                                type="number"
                                min="0.5"
                                step="0.1"
                                help="Time in seconds (e.g.: 2 seconds)."
                            />
                        )}

                        <p style={ { marginTop: '10px', fontSize: '12px', color: '#777' } }>
                            <small>Make sure the text begins with a number (e.g., '123', '+500', or '€100').</small>
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