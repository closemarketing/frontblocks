const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, InnerBlocks, useBlockProps, useInnerBlocksProps } = wp.blockEditor;
const { PanelBody, TextControl, RangeControl, SelectControl, ColorPicker, __experimentalDivider: Divider } = wp.components;
const { __ } = wp.i18n;

function VimeoBackgroundEdit( { attributes, setAttributes } ) {
	const { vimeoUrl, minHeight, minHeightUnit, overlayColor, overlayOpacity } = attributes;

	const blockProps = useBlockProps( {
		className: 'frbl-vimeo-bg',
		style: {
			position: 'relative',
			minHeight: minHeight + minHeightUnit,
			overflow: 'hidden',
		},
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'frbl-vimeo-bg__content' },
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

				<PanelBody title={ __( 'Altura', 'frontblocks' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Altura mínima', 'frontblocks' ) }
						value={ minHeight }
						onChange={ ( val ) => setAttributes( { minHeight: val } ) }
						min={ 10 }
						max={ minHeightUnit === 'vh' ? 100 : 1200 }
						step={ 1 }
					/>
					<SelectControl
						label={ __( 'Unidad', 'frontblocks' ) }
						value={ minHeightUnit }
						options={ [
							{ label: 'vh (% viewport)', value: 'vh' },
							{ label: 'px', value: 'px' },
						] }
						onChange={ ( val ) => setAttributes( { minHeightUnit: val } ) }
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
				<div
					className="frbl-vimeo-bg__media"
					style={ {
						position: 'absolute',
						inset: '0',
						background: 'repeating-linear-gradient(45deg,#e0e0e0 0,#e0e0e0 10px,#f5f5f5 10px,#f5f5f5 20px)',
						display: 'flex',
						alignItems: 'center',
						justifyContent: 'center',
						zIndex: 0,
					} }
				>
					{ vimeoUrl ? (
						<p style={ { color: '#333', margin: 0, fontSize: '13px', padding: '20px', textAlign: 'center', background: 'rgba(255,255,255,0.7)', borderRadius: '4px' } }>
							🎬 { vimeoUrl }<br />
							<small>{ __( 'El vídeo se renderiza en el frontend', 'frontblocks' ) }</small>
						</p>
					) : (
						<p style={ { color: '#555', margin: 0, background: 'rgba(255,255,255,0.7)', padding: '12px 16px', borderRadius: '4px' } }>
							{ __( '← Añade una URL de Vimeo en el panel lateral', 'frontblocks' ) }
						</p>
					) }
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
		vimeoUrl:       { type: 'string',  default: '' },
		minHeight:      { type: 'number',  default: 100 },
		minHeightUnit:  { type: 'string',  default: 'vh' },
		overlayColor:   { type: 'string',  default: '#000000' },
		overlayOpacity: { type: 'number',  default: 0 },
		align:          { type: 'string',  default: 'full' },
	},
	edit: VimeoBackgroundEdit,
	save: () => null,
} );
