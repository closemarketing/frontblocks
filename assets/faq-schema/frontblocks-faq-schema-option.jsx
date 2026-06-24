const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl, SelectControl } = wp.components;
const { __ } = wp.i18n;

const FAQ_BLOCKS = [ 'core/accordion', 'generateblocks/container' ];

const SCHEMA_TYPES = [
	{ label: __( 'FAQPage (questions & answers)', 'frontblocks' ), value: 'FAQPage' },
	{ label: __( 'HowTo (steps / how-to guide)', 'frontblocks' ), value: 'HowTo' },
];

// Register attributes on supported blocks.
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
			frblSchemaType: {
				type: 'string',
				default: 'FAQPage',
			},
		} );
		return settings;
	}
);

// Add inspector controls to supported blocks.
addFilter(
	'editor.BlockEdit',
	'frontblocks/faq-schema-controls',
	( BlockEdit ) => ( props ) => {
		if ( ! FAQ_BLOCKS.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		// For generateblocks/container, only show on the accordion variant role.
		if ( props.name === 'generateblocks/container' && props.attributes.variantRole !== 'accordion' ) {
			return <BlockEdit { ...props } />;
		}

		const { frblFaqSchema = false, frblSchemaType = 'FAQPage' } = props.attributes;

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'FrontBlocks - Schema', 'frontblocks' ) }
						initialOpen={ false }
					>
						<ToggleControl
							label={ __( 'Add Schema (JSON-LD)', 'frontblocks' ) }
							help={ __( 'Include this block\'s content in the page structured data.', 'frontblocks' ) }
							checked={ frblFaqSchema }
							onChange={ ( value ) => props.setAttributes( { frblFaqSchema: value } ) }
						/>
						{ frblFaqSchema && (
							<SelectControl
								label={ __( 'Schema type', 'frontblocks' ) }
								value={ frblSchemaType }
								options={ SCHEMA_TYPES }
								onChange={ ( value ) => props.setAttributes( { frblSchemaType: value } ) }
							/>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	}
);
