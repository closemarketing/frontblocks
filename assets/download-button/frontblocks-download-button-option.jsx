const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, ToggleControl, Button } = wp.components;
const { __ } = wp.i18n;

const BUTTON_BLOCK = 'core/button';

// Register download attributes for the native button block.
wp.hooks.addFilter(
    'blocks.registerBlockType',
    'frontblocks/add-download-attributes',
    ( settings, name ) => {
        if ( BUTTON_BLOCK === name ) {
            settings.attributes = Object.assign( settings.attributes || {}, {
                frblDownloadEnabled: {
                    type: 'boolean',
                    default: false,
                },
                frblDownloadFileId: {
                    type: 'number',
                    default: 0,
                },
                frblDownloadFileUrl: {
                    type: 'string',
                    default: '',
                },
                frblDownloadFileName: {
                    type: 'string',
                    default: '',
                },
            } );
        }
        return settings;
    }
);

const withDownloadControl = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        if ( BUTTON_BLOCK !== props.name ) {
            return <BlockEdit { ...props } />;
        }

        const { attributes, setAttributes } = props;
        const isDownloadEnabled = !! attributes.frblDownloadEnabled;
        const fileId = attributes.frblDownloadFileId || 0;
        const fileUrl = attributes.frblDownloadFileUrl || '';
        const fileName = attributes.frblDownloadFileName || '';

        const setDownloadEnabled = ( enabled ) => {
            if ( enabled ) {
                setAttributes( { frblDownloadEnabled: true } );
                return;
            }

            const newAttributes = {
                frblDownloadEnabled: false,
                frblDownloadFileId: 0,
                frblDownloadFileUrl: '',
                frblDownloadFileName: '',
            };

            // Clear the button URL only when it still points to the download file.
            if ( fileUrl && attributes.url === fileUrl ) {
                newAttributes.url = undefined;
            }

            setAttributes( newAttributes );
        };

        const onSelectFile = ( media ) => {
            if ( ! media || ! media.url ) {
                return;
            }

            const mediaFileName = media.filename || media.url.split( '/' ).pop();

            setAttributes( {
                frblDownloadFileId: media.id || 0,
                frblDownloadFileUrl: media.url,
                frblDownloadFileName: mediaFileName,
                url: media.url,
            } );
        };

        const onRemoveFile = () => {
            const newAttributes = {
                frblDownloadFileId: 0,
                frblDownloadFileUrl: '',
                frblDownloadFileName: '',
            };

            if ( fileUrl && attributes.url === fileUrl ) {
                newAttributes.url = undefined;
            }

            setAttributes( newAttributes );
        };

        return (
            <Fragment>
                <BlockEdit { ...props } />

                <InspectorControls>
                    <PanelBody
                        title={ __( 'FrontBlocks - Download', 'frontblocks' ) }
                        initialOpen={ false }
                    >
                        <ToggleControl
                            label={ __( 'Download file on click', 'frontblocks' ) }
                            checked={ isDownloadEnabled }
                            onChange={ setDownloadEnabled }
                            help={
                                isDownloadEnabled ?
                                __( 'The button will download the selected file instead of opening a URL.', 'frontblocks' ) :
                                __( 'Enable to make this button download a file.', 'frontblocks' )
                            }
                        />

                        { isDownloadEnabled && (
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={ onSelectFile }
                                    value={ fileId }
                                    render={ ( { open } ) => (
                                        <div>
                                            { fileName && (
                                                <p>
                                                    <strong>{ __( 'Selected file:', 'frontblocks' ) }</strong>
                                                    <br />
                                                    { fileName }
                                                </p>
                                            ) }
                                            <Button
                                                variant="primary"
                                                onClick={ open }
                                            >
                                                { fileUrl ?
                                                    __( 'Replace file', 'frontblocks' ) :
                                                    __( 'Select or upload file', 'frontblocks' )
                                                }
                                            </Button>
                                            { fileUrl && (
                                                <Button
                                                    variant="secondary"
                                                    onClick={ onRemoveFile }
                                                    style={ { marginLeft: '8px' } }
                                                >
                                                    { __( 'Remove file', 'frontblocks' ) }
                                                </Button>
                                            ) }
                                        </div>
                                    ) }
                                />
                            </MediaUploadCheck>
                        ) }
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withDownloadControl' );

wp.hooks.addFilter(
    'editor.BlockEdit',
    'frontblocks/download-button-control',
    withDownloadControl
);
