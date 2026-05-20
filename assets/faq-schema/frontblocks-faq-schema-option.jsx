const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;
const { __ } = wp.i18n;

const FAQ_BLOCKS = [ 'core/details', 'generateblocks/accordion' ];

// Register frblFaqSchema attribute on supported blocks.
addFilter(
	'blocks.registerBlockType',
	'frontblocks/faq-schema-attribute',
	( settings, name ) => {
		if ( ! FAQ_BLOCKS.includes( name ) ) {
			return settings;
		}
		settings.attributes = Object.assign( {}, settings.attributes, {
			frblFaqSchema: {
				type: 'boolean',
				default: false,
			},
		} );
		return settings;
	}
);

// Add inspector toggle to supported blocks.
addFilter(
	'editor.BlockEdit',
	'frontblocks/faq-schema-controls',
	( BlockEdit ) => ( props ) => {
		if ( ! FAQ_BLOCKS.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const { frblFaqSchema = false } = props.attributes;

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'FAQ Schema', 'frontblocks' ) }
						initialOpen={ false }
					>
						<ToggleControl
							label={ __( 'Add FAQ Schema (JSON-LD)', 'frontblocks' ) }
							help={ __( 'Include this block\'s Q&A in the page FAQPage structured data.', 'frontblocks' ) }
							checked={ frblFaqSchema }
							onChange={ ( value ) => props.setAttributes( { frblFaqSchema: value } ) }
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	}
);
