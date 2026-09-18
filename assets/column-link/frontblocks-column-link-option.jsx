const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, ToggleControl } = wp.components;
const { __ } = wp.i18n;

const COLUMN_BLOCK = 'core/column';

// Register the link attributes server-side.
wp.hooks.addFilter(
    'blocks.registerBlockType',
    'frontblocks/add-column-link-attributes',
    ( settings, name ) => {
        if ( COLUMN_BLOCK !== name ) {
            return settings;
        }

        settings.attributes = Object.assign( settings.attributes || {}, {
            frblColumnLinkUrl: {
                type: 'string',
                default: '',
            },
            frblColumnLinkNewTab: {
                type: 'boolean',
                default: false,
            },
        } );

        return settings;
    }
);

// Hint that the column is linked in the editor canvas via a data attribute
// (styled by the inline CSS ColumnLink::enqueue_editor_styles() injects),
// without making the editor canvas itself navigate on click.
const withColumnLinkWrapper = createHigherOrderComponent( ( BlockListBlock ) => {
    return ( props ) => {
        const hasLink = COLUMN_BLOCK === props.name && !! ( props.attributes.frblColumnLinkUrl || '' ).trim();

        if ( ! hasLink ) {
            return <BlockListBlock { ...props } />;
        }

        const wrapperProps = {
            ...( props.wrapperProps || {} ),
            'data-frbl-column-link-active': 'true',
        };

        return <BlockListBlock { ...props } wrapperProps={ wrapperProps } />;
    };
}, 'withColumnLinkWrapper' );

wp.hooks.addFilter(
    'editor.BlockListBlock',
    'frontblocks/column-link-wrapper',
    withColumnLinkWrapper
);

// Add the inspector controls.
const withColumnLinkControl = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        if ( COLUMN_BLOCK !== props.name ) {
            return <BlockEdit { ...props } />;
        }

        const { attributes, setAttributes } = props;
        const url = attributes.frblColumnLinkUrl || '';
        const newTab = !! attributes.frblColumnLinkNewTab;

        return (
            <Fragment>
                <BlockEdit { ...props } />

                <InspectorControls>
                    <PanelBody
                        title={ __( 'FrontBlocks - Column Link', 'frontblocks' ) }
                        initialOpen={ false }
                    >
                        <TextControl
                            label={ __( 'Link URL', 'frontblocks' ) }
                            help={ __( 'Makes the whole column clickable. Leave empty to disable.', 'frontblocks' ) }
                            value={ url }
                            onChange={ ( value ) => setAttributes( { frblColumnLinkUrl: value } ) }
                            type="url"
                        />

                        { url.trim() && (
                            <ToggleControl
                                label={ __( 'Open in new tab', 'frontblocks' ) }
                                checked={ newTab }
                                onChange={ ( value ) => setAttributes( { frblColumnLinkNewTab: value } ) }
                            />
                        ) }
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withColumnLinkControl' );

wp.hooks.addFilter(
    'editor.BlockEdit',
    'frontblocks/column-link-control',
    withColumnLinkControl
);
