const { registerBlockType }               = wp.blocks;
const { Fragment, useState, useEffect }   = wp.element;
const { useSelect }                       = wp.data;
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

const HEADING_TAGS = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

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

/**
 * Resolve a CSS var like "var(--wp--preset--font-size--large)" to a real value
 * using computed styles on the document root.
 */
function resolveCssVar( value ) {
	if ( ! value || ! value.startsWith( 'var(' ) ) return value;
	const match = value.match( /var\(\s*(--[^,)]+)/ );
	if ( ! match ) return value;
	const resolved = getComputedStyle( document.documentElement )
		.getPropertyValue( match[1] )
		.trim();
	return resolved || value;
}

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

		// ── Current user (with email, needs context=edit) ──────────────────
		const [ currentUser, setCurrentUser ] = useState( null );
		useEffect( () => {
			wp.apiFetch( { path: '/wp/v2/users/me?context=edit' } )
				.then( ( u ) => setCurrentUser( u ) )
				.catch( () => {} );
		}, [] );

		// ── Theme global styles ─────────────────────────────────────────────
		const [ themeTypo, setThemeTypo ] = useState( {} );
		const themeSlug = useSelect( ( select ) => select( 'core' ).getCurrentTheme()?.stylesheet );

		useEffect( () => {
			if ( ! themeSlug ) return;
			wp.apiFetch( { path: '/wp/v2/global-styles/themes/' + themeSlug } )
				.then( ( data ) => {
					const elements = ( data && data.styles && data.styles.elements ) ? data.styles.elements : {};
					// Merge heading base + specific tag (specific wins)
					const base    = HEADING_TAGS.includes( htmlTag )
						? ( elements.heading && elements.heading.typography ? elements.heading.typography : {} )
						: {};
					const tagTypo = ( elements[ htmlTag ] && elements[ htmlTag ].typography )
						? elements[ htmlTag ].typography
						: {};
					const merged = Object.assign( {}, base, tagTypo );
					// Resolve CSS vars to real values for display
					const resolved = {};
					Object.keys( merged ).forEach( ( k ) => {
						resolved[ k ] = resolveCssVar( merged[ k ] );
					} );
					setThemeTypo( resolved );
				} )
				.catch( () => {} );
		}, [ themeSlug, htmlTag ] );

		// ── Preview: replace placeholders with real user data ───────────────
		const previewText = ( pattern ) => {
			if ( ! currentUser ) return pattern;
			const nombre = currentUser.first_name || currentUser.name || '';
			const map = {
				'{nombre}':       nombre,
				'{apellido}':     currentUser.last_name   || '',
				'{display_name}': currentUser.name        || '',
				'{email}':        currentUser.email       || '',
				'{username}':     currentUser.slug        || '',
				'{bio}':          currentUser.description || '',
				'{web}':          currentUser.url         || '',
			};
			return Object.entries( map ).reduce(
				( str, [ key, val ] ) => str.split( key ).join( val ),
				pattern
			);
		};

		// ── Inline styles: only explicit overrides ──────────────────────────
		const inlineStyle = {
			color:      textColor  || undefined,
			fontSize:   fontSize   || undefined,
			fontFamily: fontFamily || undefined,
			fontWeight: fontWeight || undefined,
			textAlign:  textAlign  || undefined,
		};

		const blockProps = useBlockProps();
		const Tag        = htmlTag || 'p';

		// Helper: placeholder showing theme default when field is empty
		const themePlaceholder = ( key, fallback ) => {
			if ( ! themeTypo[ key ] ) return fallback;
			return themeTypo[ key ] + ' (' + __( 'tema', 'frontblocks' ) + ')';
		};

		// For SelectControl (fontWeight): inject theme default into label
		const weightOptions = FONT_WEIGHT_OPTIONS.map( ( opt ) => {
			if ( opt.value !== '' ) return opt;
			const tw = themeTypo.fontWeight;
			return {
				...opt,
				label: tw
					? __( 'Default', 'frontblocks' ) + ' — ' + tw
					: __( 'Default', 'frontblocks' ),
			};
		} );

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Pattern & Data', 'frontblocks' ) } initialOpen={ true }>
						<TextareaControl
							label={ __( 'Text Pattern', 'frontblocks' ) }
							value={ textPattern }
							onChange={ ( val ) => setAttributes( { textPattern: val } ) }
							rows={ 4 }
							help={ __( 'Placeholders: {nombre}, {apellido}, {display_name}, {email}, {username}, {bio}, {web}', 'frontblocks' ) }
						/>
						<TextControl
							label={ __( 'Logged-out Fallback', 'frontblocks' ) }
							value={ loggedOutText }
							onChange={ ( val ) => setAttributes( { loggedOutText: val } ) }
							help={ __( 'Shown when no user is logged in. Leave empty to hide.', 'frontblocks' ) }
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
							placeholder={ themePlaceholder( 'fontSize', '16px, 1.5rem…' ) }
							help={ fontSize
								? __( 'Custom override active. Clear to use theme default.', 'frontblocks' )
								: __( 'Empty = theme default.', 'frontblocks' )
							}
						/>
						<SelectControl
							label={ __( 'Font Weight', 'frontblocks' ) }
							value={ fontWeight }
							options={ weightOptions }
							onChange={ ( val ) => setAttributes( { fontWeight: val } ) }
						/>
						<TextControl
							label={ __( 'Font Family', 'frontblocks' ) }
							value={ fontFamily }
							onChange={ ( val ) => setAttributes( { fontFamily: val } ) }
							placeholder={ themePlaceholder( 'fontFamily', 'Inter, sans-serif…' ) }
							help={ fontFamily
								? __( 'Custom override active. Clear to use theme default.', 'frontblocks' )
								: __( 'Empty = theme default.', 'frontblocks' )
							}
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
					{ previewText( textPattern ) || __( 'Enter a text pattern in the sidebar…', 'frontblocks' ) }
				</Tag>
			</Fragment>
		);
	},

	save: () => null,
} );
