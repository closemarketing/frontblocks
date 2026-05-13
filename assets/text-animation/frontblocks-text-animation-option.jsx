const { registerBlockType } = wp.blocks;
const { Fragment, useState } = wp.element;
const {
	InspectorControls,
	useBlockProps,
	RichText,
	withColors,
	PanelColorSettings,
	FontSizePicker,
	withFontSizes,
} = wp.blockEditor;
const {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	__experimentalUnitControl: UnitControl,
} = wp.components;
const { __ } = wp.i18n;

const FONT_SIZES = [
	{ name: __( 'Small', 'frontblocks' ),    slug: 'small',    size: 14 },
	{ name: __( 'Normal', 'frontblocks' ),   slug: 'normal',   size: 16 },
	{ name: __( 'Medium', 'frontblocks' ),   slug: 'medium',   size: 20 },
	{ name: __( 'Large', 'frontblocks' ),    slug: 'large',    size: 28 },
	{ name: __( 'X-Large', 'frontblocks' ),  slug: 'x-large',  size: 36 },
	{ name: __( 'XX-Large', 'frontblocks' ), slug: 'xx-large', size: 48 },
	{ name: __( 'Huge', 'frontblocks' ),     slug: 'huge',     size: 64 },
];

const TAG_OPTIONS = [
	{ label: 'h1', value: 'h1' },
	{ label: 'h2', value: 'h2' },
	{ label: 'h3', value: 'h3' },
	{ label: 'h4', value: 'h4' },
	{ label: 'h5', value: 'h5' },
	{ label: 'h6', value: 'h6' },
	{ label: 'p',  value: 'p'  },
	{ label: 'span (inline)', value: 'span' },
];

const FONT_WEIGHT_OPTIONS = [
	{ label: __( 'Thin (100)', 'frontblocks' ),       value: '100' },
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
	{ label: __( 'Left', 'frontblocks' ),   value: 'left' },
	{ label: __( 'Center', 'frontblocks' ), value: 'center' },
	{ label: __( 'Right', 'frontblocks' ),  value: 'right' },
	{ label: __( 'Justify', 'frontblocks' ),value: 'justify' },
];

const TEXT_TRANSFORM_OPTIONS = [
	{ label: __( 'None', 'frontblocks' ),       value: 'none' },
	{ label: __( 'Uppercase', 'frontblocks' ),  value: 'uppercase' },
	{ label: __( 'Lowercase', 'frontblocks' ),  value: 'lowercase' },
	{ label: __( 'Capitalize', 'frontblocks' ), value: 'capitalize' },
];

const FONT_STYLE_OPTIONS = [
	{ label: __( 'Normal', 'frontblocks' ), value: 'normal' },
	{ label: __( 'Italic', 'frontblocks' ), value: 'italic' },
];

function TextAnimationEdit( props ) {
	const { attributes, setAttributes } = props;
	const {
		content,
		htmlTag,
		fontSize,
		fontSizeUnit,
		fontWeight,
		fontStyle,
		lineHeight,
		letterSpacing,
		textAlign,
		textTransform,
		textColor,
		textColorCustom,
	} = attributes;

	const blockProps = useBlockProps( {
		style: {
			fontSize:      fontSize ? `${ fontSize }${ fontSizeUnit || 'px' }` : undefined,
			fontWeight:    fontWeight || undefined,
			fontStyle:     fontStyle !== 'normal' ? fontStyle : undefined,
			lineHeight:    lineHeight || undefined,
			letterSpacing: letterSpacing ? `${ letterSpacing }em` : undefined,
			textAlign:     textAlign !== 'left' ? textAlign : undefined,
			textTransform: textTransform !== 'none' ? textTransform : undefined,
			color:         textColorCustom || undefined,
		},
	} );

	return (
		<Fragment>
			<InspectorControls>

				<PanelBody title={ __( 'Typography', 'frontblocks' ) } initialOpen={ true }>

					<SelectControl
						label={ __( 'HTML Tag', 'frontblocks' ) }
						value={ htmlTag }
						options={ TAG_OPTIONS }
						onChange={ ( value ) => setAttributes( { htmlTag: value } ) }
					/>

					<div style={ { marginBottom: '16px' } }>
						<p style={ { marginBottom: '8px', fontWeight: '500' } }>
							{ __( 'Font Size', 'frontblocks' ) }
						</p>
						<FontSizePicker
							fontSizes={ FONT_SIZES }
							value={ fontSize }
							onChange={ ( value ) => setAttributes( { fontSize: value || undefined } ) }
							withSlider
						/>
					</div>

					<SelectControl
						label={ __( 'Font Weight', 'frontblocks' ) }
						value={ fontWeight }
						options={ [ { label: __( 'Default', 'frontblocks' ), value: '' }, ...FONT_WEIGHT_OPTIONS ] }
						onChange={ ( value ) => setAttributes( { fontWeight: value } ) }
					/>

					<SelectControl
						label={ __( 'Font Style', 'frontblocks' ) }
						value={ fontStyle }
						options={ FONT_STYLE_OPTIONS }
						onChange={ ( value ) => setAttributes( { fontStyle: value } ) }
					/>

					<SelectControl
						label={ __( 'Text Align', 'frontblocks' ) }
						value={ textAlign }
						options={ TEXT_ALIGN_OPTIONS }
						onChange={ ( value ) => setAttributes( { textAlign: value } ) }
					/>

					<SelectControl
						label={ __( 'Text Transform', 'frontblocks' ) }
						value={ textTransform }
						options={ TEXT_TRANSFORM_OPTIONS }
						onChange={ ( value ) => setAttributes( { textTransform: value } ) }
					/>

					<RangeControl
						label={ __( 'Line Height', 'frontblocks' ) }
						value={ lineHeight }
						onChange={ ( value ) => setAttributes( { lineHeight: value } ) }
						min={ 0.5 }
						max={ 4 }
						step={ 0.05 }
						initialPosition={ 1.5 }
						allowReset
						resetFallbackValue={ undefined }
					/>

					<RangeControl
						label={ __( 'Letter Spacing (em)', 'frontblocks' ) }
						value={ letterSpacing }
						onChange={ ( value ) => setAttributes( { letterSpacing: value } ) }
						min={ -0.2 }
						max={ 1 }
						step={ 0.01 }
						initialPosition={ 0 }
						allowReset
						resetFallbackValue={ undefined }
					/>

				</PanelBody>

				<PanelColorSettings
					title={ __( 'Color', 'frontblocks' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							label: __( 'Text Color', 'frontblocks' ),
							value: textColorCustom,
							onChange: ( value ) => setAttributes( { textColorCustom: value || '' } ),
						},
					] }
				/>

			</InspectorControls>

			<RichText
				{ ...blockProps }
				tagName={ htmlTag }
				value={ content }
				onChange={ ( value ) => setAttributes( { content: value } ) }
				placeholder={ __( 'Write your text here…', 'frontblocks' ) }
				allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/underline', 'core/strikethrough', 'core/code' ] }
			/>
		</Fragment>
	);
}

registerBlockType( 'frontblocks/text-animation', {
	title:       __( 'Text Animation', 'frontblocks' ),
	description: __( 'Animated text block with typography controls. Add animation effects from the sidebar.', 'frontblocks' ),
	category:    'text',
	icon:        'editor-textcolor',
	keywords: [
		__( 'text', 'frontblocks' ),
		__( 'animation', 'frontblocks' ),
		__( 'heading', 'frontblocks' ),
		__( 'typography', 'frontblocks' ),
	],
	supports: {
		anchor: true,
		className: true,
		color: false,
		spacing: {
			margin:  true,
			padding: true,
		},
	},
	attributes: {
		content: {
			type:    'string',
			source:  'html',
			selector: '.frbl-text-animation',
			default: '',
		},
		htmlTag: {
			type:    'string',
			default: 'h2',
		},
		fontSize: {
			type:    'number',
			default: undefined,
		},
		fontSizeUnit: {
			type:    'string',
			default: 'px',
		},
		fontWeight: {
			type:    'string',
			default: '',
		},
		fontStyle: {
			type:    'string',
			default: 'normal',
		},
		lineHeight: {
			type:    'number',
			default: undefined,
		},
		letterSpacing: {
			type:    'number',
			default: undefined,
		},
		textAlign: {
			type:    'string',
			default: 'left',
		},
		textTransform: {
			type:    'string',
			default: 'none',
		},
		textColorCustom: {
			type:    'string',
			default: '',
		},
	},
	edit: TextAnimationEdit,
	save: function ( { attributes } ) {
		const {
			content,
			htmlTag: Tag,
			fontSize,
			fontSizeUnit,
			fontWeight,
			fontStyle,
			lineHeight,
			letterSpacing,
			textAlign,
			textTransform,
			textColorCustom,
		} = attributes;

		const style = {
			fontSize:      fontSize ? `${ fontSize }${ fontSizeUnit || 'px' }` : undefined,
			fontWeight:    fontWeight || undefined,
			fontStyle:     fontStyle !== 'normal' ? fontStyle : undefined,
			lineHeight:    lineHeight || undefined,
			letterSpacing: letterSpacing ? `${ letterSpacing }em` : undefined,
			textAlign:     textAlign !== 'left' ? textAlign : undefined,
			textTransform: textTransform !== 'none' ? textTransform : undefined,
			color:         textColorCustom || undefined,
		};

		// Remove undefined keys.
		Object.keys( style ).forEach( ( k ) => style[ k ] === undefined && delete style[ k ] );

		const blockProps = wp.blockEditor.useBlockProps.save();

		return (
			<Tag { ...blockProps } className={ `${ blockProps.className || '' } frbl-text-animation`.trim() } style={ style }>
				<RichText.Content value={ content } />
			</Tag>
		);
	},
} );
