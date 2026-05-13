const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const {
	InspectorControls,
	useBlockProps,
	RichText,
	PanelColorSettings,
} = wp.blockEditor;
const {
	PanelBody,
	SelectControl,
	RangeControl,
	__experimentalNumberControl: NumberControl,
	__experimentalToggleGroupControl: ToggleGroupControl,
	__experimentalToggleGroupControlOptionIcon: ToggleGroupControlOptionIcon,
} = wp.components;
const { __ } = wp.i18n;

const FONT_SIZE_UNITS = [
	{ label: 'px',  value: 'px'  },
	{ label: 'rem', value: 'rem' },
	{ label: 'em',  value: 'em'  },
	{ label: 'vw',  value: 'vw'  },
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

const ALIGN_LEFT_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M13 15H3v2h10v-2zm0-8H3v2h10V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"/>
	</svg>
);
const ALIGN_CENTER_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/>
	</svg>
);
const ALIGN_RIGHT_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M3 21h18v-2H3v2zm8-4h10v-2H11v2zm-8-4h18v-2H3v2zm8-4h10V7H11v2zM3 3v2h18V3H3z"/>
	</svg>
);
const ALIGN_JUSTIFY_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<path d="M3 21h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18V7H3v2zm0-6v2h18V3H3z"/>
	</svg>
);

const TRANSFORM_NONE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="4" y="17" fontFamily="serif" fontSize="14" fontWeight="bold" fill="currentColor">Ag</text>
		<line x1="3" y1="21" x2="21" y2="3" stroke="currentColor" strokeWidth="1.5"/>
	</svg>
);
const TRANSFORM_UPPERCASE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="2" y="17" fontFamily="serif" fontSize="15" fontWeight="bold" fill="currentColor">AA</text>
	</svg>
);
const TRANSFORM_LOWERCASE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="2" y="17" fontFamily="serif" fontSize="15" fill="currentColor">aa</text>
	</svg>
);
const TRANSFORM_CAPITALIZE_ICON = (
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
		<text x="2" y="17" fontFamily="serif" fontSize="15" fill="currentColor">Aa</text>
	</svg>
);

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
						<p style={ { marginTop: 0, marginBottom: '8px', fontSize: '11px', fontWeight: '500', textTransform: 'uppercase', color: 'rgb(117, 117, 117)' } }>
							{ __( 'Font Size', 'frontblocks' ) }
						</p>
						<div style={ { display: 'flex', gap: '8px' } }>
							<div style={ { flex: 1, marginBottom: 0 } }>
								<NumberControl
									value={ fontSize || '' }
									onChange={ ( value ) => setAttributes( { fontSize: value ? parseFloat( value ) : undefined } ) }
									min={ 1 }
									step={ 1 }
									spinControls="native"
									hideLabelFromVision
									label={ __( 'Font Size', 'frontblocks' ) }
								/>
							</div>
							<div style={ { width: '80px', flexShrink: 0, marginBottom: 0 } }>
								<SelectControl
									value={ fontSizeUnit }
									options={ FONT_SIZE_UNITS }
									onChange={ ( value ) => setAttributes( { fontSizeUnit: value } ) }
									hideLabelFromVision
									label={ __( 'Unit', 'frontblocks' ) }
									__nextHasNoMarginBottom
								/>
							</div>
						</div>
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

					<ToggleGroupControl
						label={ __( 'Text Align', 'frontblocks' ) }
						value={ textAlign }
						onChange={ ( value ) => setAttributes( { textAlign: value || 'left' } ) }
						isBlock
					>
						<ToggleGroupControlOptionIcon value="left"    icon={ ALIGN_LEFT_ICON }    label={ __( 'Left', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="center"  icon={ ALIGN_CENTER_ICON }  label={ __( 'Center', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="right"   icon={ ALIGN_RIGHT_ICON }   label={ __( 'Right', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="justify" icon={ ALIGN_JUSTIFY_ICON } label={ __( 'Justify', 'frontblocks' ) } />
					</ToggleGroupControl>

					<ToggleGroupControl
						label={ __( 'Text Transform', 'frontblocks' ) }
						value={ textTransform }
						onChange={ ( value ) => setAttributes( { textTransform: value || 'none' } ) }
						isBlock
					>
						<ToggleGroupControlOptionIcon value="none"       icon={ TRANSFORM_NONE_ICON }       label={ __( 'None', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="uppercase"  icon={ TRANSFORM_UPPERCASE_ICON }  label={ __( 'Uppercase', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="lowercase"  icon={ TRANSFORM_LOWERCASE_ICON }  label={ __( 'Lowercase', 'frontblocks' ) } />
						<ToggleGroupControlOptionIcon value="capitalize" icon={ TRANSFORM_CAPITALIZE_ICON } label={ __( 'Capitalize', 'frontblocks' ) } />
					</ToggleGroupControl>

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
