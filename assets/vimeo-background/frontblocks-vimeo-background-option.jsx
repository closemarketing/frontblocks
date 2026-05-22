const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, InnerBlocks, useBlockProps, useInnerBlocksProps } = wp.blockEditor;

function extractVimeoId( url ) {
	if ( ! url ) return null;
	const match = url.match( /(?:vimeo\.com\/(?:video\/)?|player\.vimeo\.com\/video\/)(\d+)/ );
	return match ? match[ 1 ] : null;
}
const { PanelBody, TextControl, RangeControl, SelectControl, ColorPicker, __experimentalUnitControl: UnitControl } = wp.components;
const { __ } = wp.i18n;

const CONTENT_JUSTIFY = [
	{ label: __( 'Izquierda',  'frontblocks' ), value: 'flex-start' },
	{ label: __( 'Centro',     'frontblocks' ), value: 'center' },
	{ label: __( 'Derecha',    'frontblocks' ), value: 'flex-end' },
	{ label: __( 'Stretch',    'frontblocks' ), value: 'stretch' },
];

const CONTENT_ALIGN = [
	{ label: __( 'Arriba',   'frontblocks' ), value: 'flex-start' },
	{ label: __( 'Centro',   'frontblocks' ), value: 'center' },
	{ label: __( 'Abajo',    'frontblocks' ), value: 'flex-end' },
	{ label: __( 'Stretch',  'frontblocks' ), value: 'stretch' },
];

function contentStyle( hAlign, vAlign, contentMaxWidth ) {
	const style = {
		display: 'flex',
		flexDirection: 'column',
		justifyContent: vAlign || 'center',   /* vertical  = main axis in column */
		alignItems: hAlign || 'stretch',       /* horizontal = cross axis in column */
		width: '100%',
		height: '100%',
		boxSizing: 'border-box',
	};
	if ( contentMaxWidth ) {
		style.maxWidth = contentMaxWidth;
		style.marginLeft = 'auto';
		style.marginRight = 'auto';
	}
	return style;
}

function VimeoBackgroundEdit( { attributes, setAttributes } ) {
	const {
		vimeoUrl,
		minHeight, minHeightUnit,
		overlayColor, overlayOpacity,
		justifyContent, alignItems,
		contentMaxWidth,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'frbl-vimeo-bg',
		style: {
			position: 'relative',
			minHeight: minHeight + minHeightUnit,
			overflow: 'hidden',
		},
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: 'frbl-vimeo-bg__content',
			style: { ...contentStyle( justifyContent, alignItems, contentMaxWidth ), position: 'relative', zIndex: 2 },
		},
		{ renderAppender: InnerBlocks.ButtonBlockAppender }
	);

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Vídeo Vimeo', 'frontblocks' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'URL de Vimeo', 'frontblocks' ) }
						value={ vimeoUrl }
						onChange={ ( val ) => setAttributes( { vimeoUrl: val } ) }
						placeholder="https://vimeo.com/123456789"
						help={ __( 'URL del vídeo de Vimeo que se usará como fondo.', 'frontblocks' ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Dimensiones', 'frontblocks' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Altura mínima', 'frontblocks' ) }
						value={ minHeight }
						onChange={ ( val ) => setAttributes( { minHeight: val } ) }
						min={ 10 }
						max={ minHeightUnit === 'vh' ? 100 : 1200 }
						step={ 1 }
					/>
					<SelectControl
						label={ __( 'Unidad altura', 'frontblocks' ) }
						value={ minHeightUnit }
						options={ [
							{ label: 'vh (% viewport)', value: 'vh' },
							{ label: 'px', value: 'px' },
						] }
						onChange={ ( val ) => setAttributes( { minHeightUnit: val } ) }
					/>
					<TextControl
						label={ __( 'Ancho máximo del contenido', 'frontblocks' ) }
						value={ contentMaxWidth }
						onChange={ ( val ) => setAttributes( { contentMaxWidth: val } ) }
						placeholder="1200px / 80% / vacío = sin límite"
						help={ __( 'Limita el ancho del contenido interior. Vacío = ancho completo.', 'frontblocks' ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Alineación del contenido', 'frontblocks' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Horizontal', 'frontblocks' ) }
						value={ justifyContent }
						options={ CONTENT_JUSTIFY }
						onChange={ ( val ) => setAttributes( { justifyContent: val } ) }
					/>
					<SelectControl
						label={ __( 'Vertical', 'frontblocks' ) }
						value={ alignItems }
						options={ CONTENT_ALIGN }
						onChange={ ( val ) => setAttributes( { alignItems: val } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Overlay', 'frontblocks' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Opacidad del overlay', 'frontblocks' ) }
						value={ overlayOpacity }
						onChange={ ( val ) => setAttributes( { overlayOpacity: val } ) }
						min={ 0 }
						max={ 100 }
						step={ 5 }
					/>
					{ overlayOpacity > 0 && (
						<>
							<p style={ { marginBottom: '8px', fontSize: '12px' } }>
								{ __( 'Color del overlay', 'frontblocks' ) }
							</p>
							<ColorPicker
								color={ overlayColor }
								onChange={ ( val ) => setAttributes( { overlayColor: val } ) }
								enableAlpha={ false }
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="frbl-vimeo-bg__media" style={ { position: 'absolute', inset: '0', overflow: 'hidden', zIndex: 0, pointerEvents: 'none' } }>
					{ ( () => {
						const videoId = extractVimeoId( vimeoUrl );
						if ( videoId ) {
							const src = 'https://player.vimeo.com/video/' + videoId + '?background=1&autoplay=1&loop=1&muted=1&title=0&byline=0&portrait=0';
							return (
								<iframe
									className="frbl-vimeo-bg__iframe"
									src={ src }
									frameBorder="0"
									allow="autoplay; fullscreen"
									title=""
									aria-hidden="true"
								/>
							);
						}
						return (
							<div style={ {
								position: 'absolute',
								inset: '0',
								background: 'repeating-linear-gradient(45deg,#e0e0e0 0,#e0e0e0 10px,#f5f5f5 10px,#f5f5f5 20px)',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'center',
							} }>
								<p style={ { color: '#555', margin: 0, background: 'rgba(255,255,255,0.8)', padding: '12px 16px', borderRadius: '4px' } }>
									{ __( '← Añade una URL de Vimeo en el panel lateral', 'frontblocks' ) }
								</p>
							</div>
						);
					} )() }
				</div>

				{ overlayOpacity > 0 && (
					<div
						style={ {
							position: 'absolute',
							inset: '0',
							backgroundColor: overlayColor,
							opacity: overlayOpacity / 100,
							zIndex: 1,
							pointerEvents: 'none',
						} }
					/>
				) }

				<div { ...innerBlocksProps } />
			</div>
		</Fragment>
	);
}

registerBlockType( 'frontblocks/vimeo-background', {
	title: __( 'Fondo Vimeo', 'frontblocks' ),
	description: __( 'Sección con vídeo de Vimeo como fondo. Sin bordes negros, a pantalla completa.', 'frontblocks' ),
	category: 'media',
	icon: 'format-video',
	keywords: [ 'vimeo', 'video', 'fondo', 'background', 'hero' ],
	supports: {
		align: [ 'full', 'wide' ],
		html: false,
	},
	attributes: {
		vimeoUrl:        { type: 'string', default: '' },
		minHeight:       { type: 'number', default: 100 },
		minHeightUnit:   { type: 'string', default: 'vh' },
		overlayColor:    { type: 'string', default: '#000000' },
		overlayOpacity:  { type: 'number', default: 0 },
		justifyContent:  { type: 'string', default: 'stretch' },
		alignItems:      { type: 'string', default: 'center' },
		contentMaxWidth: { type: 'string', default: '' },
		align:           { type: 'string', default: 'full' },
	},
	edit: VimeoBackgroundEdit,
	save: () => wp.element.createElement( InnerBlocks.Content, null ),
} );
