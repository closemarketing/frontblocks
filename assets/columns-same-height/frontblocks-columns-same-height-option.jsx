const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;
const { __ } = wp.i18n;

const COLUMNS_BLOCK = 'core/columns';

// Register same-height attribute server-side.
wp.hooks.addFilter(
    'blocks.registerBlockType',
    'frontblocks/add-same-height-attribute',
    ( settings, name ) => {
        if ( COLUMNS_BLOCK !== name ) {
            return settings;
        }

        settings.attributes = Object.assign( settings.attributes || {}, {
            frblSameHeight: {
                type: 'boolean',
                default: false,
            },
        } );

        return settings;
    }
);

const withSameHeightControl = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        if ( COLUMNS_BLOCK !== props.name ) {
            return <BlockEdit { ...props } />;
        }

        const { attributes, setAttributes } = props;
        const isSameHeight = !! attributes.frblSameHeight;

        return (
            <Fragment>
                <BlockEdit { ...props } />

                <InspectorControls>
                    <PanelBody
                        title={ __( 'FrontBlocks - Layout', 'frontblocks' ) }
                        initialOpen={ false }
                    >
                        <ToggleControl
                            label={ __( 'Same height columns', 'frontblocks' ) }
                            checked={ isSameHeight }
                            onChange={ ( value ) => setAttributes( { frblSameHeight: value } ) }
                            help={
                                isSameHeight ?
                                __( 'All columns stretch to match the tallest one.', 'frontblocks' ) :
                                __( 'Enable to force all columns to the same height.', 'frontblocks' )
                            }
                        />
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withSameHeightControl' );

wp.hooks.addFilter(
    'editor.BlockEdit',
    'frontblocks/columns-same-height-control',
    withSameHeightControl
);
