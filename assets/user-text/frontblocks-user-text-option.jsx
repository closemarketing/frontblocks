const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const {
	InspectorControls,
	useBlockProps,
	PanelColorSettings,
} = wp.blockEditor;
const {
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
} = wp.components;
const { __ } = wp.i18n;

const TAG_OPTIONS = [
	{ label: 'p',    value: 'p'    },
	{ label: 'h1',   value: 'h1'   },
	{ label: 'h2',   value: 'h2'   },
	{ label: 'h3',   value: 'h3'   },
	{ label: 'h4',   value: 'h4'   },
	{ label: 'h5',   value: 'h5'   },
	{ label: 'h6',   value: 'h6'   },
	{ label: 'span', value: 'span' },
	{ label: 'div',  value: 'div'  },
];

const FONT_WEIGHT_OPTIONS = [
	{ label: __( 'Default', 'frontblocks' ),          value: ''    },
	{ label: __( 'Thin (100)', 'frontblocks' ),        value: '100' },
	{ label: __( 'Extra Light (200)', 'frontblocks' ), value: '200' },
	{ label: __( 'Light (300)', 'frontblocks' ),       value: '300' },
	{ label: __( 'Normal (400)', 'frontblocks' ),      value: '400' },
	{ label: __( 'Medium (500)', 'frontblocks' ),      value: '500' },
	{ label: __( 'Semi Bold (600)', 'frontblocks' ),   value: '600' },
	{ label: __( 'Bold (700)', 'frontblocks' ),        value: '700' },
	{ label: __( 'Extra Bold (800)', 'frontblocks' ),  value: '800' },
	{ label: __( 'Black (900)', 'frontblocks' ),       value: '900' },
];

const TEXT_ALIGN_OPTIONS = [
	{ label: __( 'Default', 'frontblocks' ), value: ''        },
	{ label: __( 'Left', 'frontblocks' ),    value: 'left'    },
	{ label: __( 'Center', 'frontblocks' ),  value: 'center'  },
	{ label: __( 'Right', 'frontblocks' ),   value: 'right'   },
	{ label: __( 'Justify', 'frontblocks' ), value: 'justify' },
];

registerBlockType( 'frontblocks/user-text', {
	title:       __( 'User Text', 'frontblocks' ),
	description: __( 'Text pattern with logged-in user data placeholders.', 'frontblocks' ),
	category:    'text',
	icon:        'admin-users',
	supports:    { html: false },

	attributes: {
		textPattern:    { type: 'string', default: __( 'Hello, {nombre}!', 'frontblocks' ) },
		htmlTag:        { type: 'string', default: 'p'  },
		textColor:      { type: 'string', default: ''   },
		hoverTextColor: { type: 'string', default: ''   },
		fontSize:       { type: 'string', default: ''   },
		fontFamily:     { type: 'string', default: ''   },
		fontWeight:     { type: 'string', default: ''   },
		textAlign:      { type: 'string', default: ''   },
		loggedOutText:  { type: 'string', default: ''   },
	},

	edit: ( { attributes, setAttributes } ) => {
		const {
			textPattern, htmlTag, textColor, hoverTextColor, fontSize,
			fontFamily, fontWeight, textAlign, loggedOutText,
		} = attributes;

		const inlineStyle = {
			color:      textColor  || undefined,
			fontSize:   fontSize   || undefined,
			fontFamily: fontFamily || undefined,
			fontWeight: fontWeight || undefined,
			textAlign:  textAlign  || undefined,
		};

		const blockProps = useBlockProps();
		const Tag        = htmlTag || 'p';

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Pattern & Data', 'frontblocks' ) } initialOpen={ true }>
						<TextareaControl
							label={ __( 'Text Pattern', 'frontblocks' ) }
							value={ textPattern }
							onChange={ ( val ) => setAttributes( { textPattern: val } ) }
							rows={ 4 }
							help={ __( 'Available placeholders: {nombre}, {apellido}, {display_name}, {email}, {username}, {bio}, {web}', 'frontblocks' ) }
						/>
						<TextControl
							label={ __( 'Logged-out Fallback', 'frontblocks' ) }
							value={ loggedOutText }
							onChange={ ( val ) => setAttributes( { loggedOutText: val } ) }
							help={ __( 'Shown when no user is logged in. Leave empty to hide the block.', 'frontblocks' ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Typography', 'frontblocks' ) } initialOpen={ false }>
						<SelectControl
							label={ __( 'HTML Tag', 'frontblocks' ) }
							value={ htmlTag }
							options={ TAG_OPTIONS }
							onChange={ ( val ) => setAttributes( { htmlTag: val } ) }
						/>
						<TextControl
							label={ __( 'Font Size', 'frontblocks' ) }
							value={ fontSize }
							onChange={ ( val ) => setAttributes( { fontSize: val } ) }
							placeholder="16px, 1.5rem, 2em…"
							help={ __( 'Any valid CSS size value.', 'frontblocks' ) }
						/>
						<SelectControl
							label={ __( 'Font Weight', 'frontblocks' ) }
							value={ fontWeight }
							options={ FONT_WEIGHT_OPTIONS }
							onChange={ ( val ) => setAttributes( { fontWeight: val } ) }
						/>
						<TextControl
							label={ __( 'Font Family', 'frontblocks' ) }
							value={ fontFamily }
							onChange={ ( val ) => setAttributes( { fontFamily: val } ) }
							placeholder="Inter, sans-serif…"
						/>
						<SelectControl
							label={ __( 'Text Align', 'frontblocks' ) }
							value={ textAlign }
							options={ TEXT_ALIGN_OPTIONS }
							onChange={ ( val ) => setAttributes( { textAlign: val } ) }
						/>
					</PanelBody>

					<PanelColorSettings
						title={ __( 'Color', 'frontblocks' ) }
						initialOpen={ false }
						colorSettings={ [
							{
								value:    textColor,
								onChange: ( val ) => setAttributes( { textColor: val || '' } ),
								label:    __( 'Text Color', 'frontblocks' ),
							},
							{
								value:    hoverTextColor,
								onChange: ( val ) => setAttributes( { hoverTextColor: val || '' } ),
								label:    __( 'Text Color on Hover', 'frontblocks' ),
							},
						] }
					/>
				</InspectorControls>

				<Tag { ...blockProps } style={ { ...inlineStyle, margin: 0 } }>
					{ textPattern || __( 'Enter a text pattern in the sidebar…', 'frontblocks' ) }
				</Tag>
			</Fragment>
		);
	},

	save: () => null,
} );
