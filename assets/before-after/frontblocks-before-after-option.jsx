const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
const { PanelBody, RangeControl, Button, Placeholder, TextControl, ToggleControl } = wp.components;
const { __ } = wp.i18n;

/**
 * Edit component for Before After block.
 */
function BeforeAfterEdit( props ) {
	const { attributes, setAttributes } = props;
	const {
		beforeImageId,
		beforeImageUrl,
		afterImageId,
		afterImageUrl,
		beforeLabel,
		afterLabel,
		initialPosition,
		blockHeight,
		fixedHeight,
	} = attributes;

	const blockProps = useBlockProps();

	const hasImages = beforeImageUrl && afterImageUrl;

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Before Image', 'frontblocks' ) } initialOpen={ true }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( {
									beforeImageId: media.id,
									beforeImageUrl: media.url,
								} )
							}
							allowedTypes={ [ 'image' ] }
							value={ beforeImageId }
							render={ ( { open } ) => (
								<div>
									{ beforeImageUrl && (
										<img
											src={ beforeImageUrl }
											alt=""
											style={ { width: '100%', marginBottom: '8px', borderRadius: '2px' } }
										/>
									) }
									<Button
										onClick={ open }
										variant={ beforeImageUrl ? 'secondary' : 'primary' }
										style={ { width: '100%' } }
									>
										{ beforeImageUrl
											? __( 'Replace Before Image', 'frontblocks' )
											: __( 'Select Before Image', 'frontblocks' ) }
									</Button>
									{ beforeImageUrl && (
										<Button
											onClick={ () =>
												setAttributes( {
													beforeImageId: undefined,
													beforeImageUrl: '',
												} )
											}
											variant="link"
											isDestructive
											style={ { marginTop: '4px', display: 'block' } }
										>
											{ __( 'Remove', 'frontblocks' ) }
										</Button>
									) }
								</div>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Before Label', 'frontblocks' ) }
						value={ beforeLabel }
						onChange={ ( value ) => setAttributes( { beforeLabel: value } ) }
						style={ { marginTop: '12px' } }
					/>
				</PanelBody>

				<PanelBody title={ __( 'After Image', 'frontblocks' ) } initialOpen={ true }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( {
									afterImageId: media.id,
									afterImageUrl: media.url,
								} )
							}
							allowedTypes={ [ 'image' ] }
							value={ afterImageId }
							render={ ( { open } ) => (
								<div>
									{ afterImageUrl && (
										<img
											src={ afterImageUrl }
											alt=""
											style={ { width: '100%', marginBottom: '8px', borderRadius: '2px' } }
										/>
									) }
									<Button
										onClick={ open }
										variant={ afterImageUrl ? 'secondary' : 'primary' }
										style={ { width: '100%' } }
									>
										{ afterImageUrl
											? __( 'Replace After Image', 'frontblocks' )
											: __( 'Select After Image', 'frontblocks' ) }
									</Button>
									{ afterImageUrl && (
										<Button
											onClick={ () =>
												setAttributes( {
													afterImageId: undefined,
													afterImageUrl: '',
												} )
											}
											variant="link"
											isDestructive
											style={ { marginTop: '4px', display: 'block' } }
										>
											{ __( 'Remove', 'frontblocks' ) }
										</Button>
									) }
								</div>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'After Label', 'frontblocks' ) }
						value={ afterLabel }
						onChange={ ( value ) => setAttributes( { afterLabel: value } ) }
						style={ { marginTop: '12px' } }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Slider Settings', 'frontblocks' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Initial Handle Position (%)', 'frontblocks' ) }
						value={ initialPosition }
						onChange={ ( value ) => setAttributes( { initialPosition: value } ) }
						min={ 0 }
						max={ 100 }
					/>
					<ToggleControl
						label={ __( 'Fixed Height', 'frontblocks' ) }
						checked={ fixedHeight }
						onChange={ ( value ) => setAttributes( { fixedHeight: value } ) }
					/>
					{ fixedHeight && (
						<RangeControl
							label={ __( 'Height (px)', 'frontblocks' ) }
							value={ blockHeight }
							onChange={ ( value ) => setAttributes( { blockHeight: value } ) }
							min={ 100 }
							max={ 1000 }
							step={ 10 }
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ ! hasImages ? (
					<Placeholder
						icon="image-flip-horizontal"
						label={ __( 'Before / After', 'frontblocks' ) }
						instructions={ __(
							'Select a "before" image and an "after" image from the sidebar.',
							'frontblocks'
						) }
					/>
				) : (
					<div
						className={ 'frbl-before-after frbl-before-after--editor' + ( fixedHeight ? ' frbl-before-after--fixed-height' : '' ) }
						data-initial-position={ initialPosition }
						style={ fixedHeight && blockHeight ? { height: blockHeight + 'px' } : {} }
					>
						<div className="frbl-before-after__after">
							<img src={ afterImageUrl } alt="" />
							<span className="frbl-before-after__label frbl-before-after__label--after">
								{ afterLabel }
							</span>
						</div>
						<div
							className="frbl-before-after__before"
							style={ { clipPath: `inset(0 ${ 100 - initialPosition }% 0 0)` } }
						>
							<img src={ beforeImageUrl } alt="" />
							<span className="frbl-before-after__label frbl-before-after__label--before">
								{ beforeLabel }
							</span>
						</div>
						<div
							className="frbl-before-after__handle"
							style={ { left: `${ initialPosition }%` } }
						>
							<span className="frbl-before-after__handle-line"></span>
							<span className="frbl-before-after__handle-thumb">
								<svg viewBox="0 0 20 20" width="18" height="18" fill="currentColor" aria-hidden="true">
									<path d="M7 4l-4 6 4 6M13 4l4 6-4 6" stroke="currentColor" strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/>
								</svg>
							</span>
							<span className="frbl-before-after__handle-line"></span>
						</div>
					</div>
				) }
			</div>
		</Fragment>
	);
}

// Register the block.
registerBlockType( 'frontblocks/before-after', {
	title: __( 'Before After', 'frontblocks' ),
	description: __( 'Compare two images with a draggable before/after slider.', 'frontblocks' ),
	category: 'media',
	icon: 'image-flip-horizontal',
	keywords: [
		__( 'before', 'frontblocks' ),
		__( 'after', 'frontblocks' ),
		__( 'comparison', 'frontblocks' ),
		__( 'slider', 'frontblocks' ),
	],
	attributes: {
		beforeImageId: {
			type: 'integer',
		},
		beforeImageUrl: {
			type: 'string',
			default: '',
		},
		afterImageId: {
			type: 'integer',
		},
		afterImageUrl: {
			type: 'string',
			default: '',
		},
		beforeLabel: {
			type: 'string',
			default: 'Before',
		},
		afterLabel: {
			type: 'string',
			default: 'After',
		},
		initialPosition: {
			type: 'number',
			default: 50,
		},
		fixedHeight: {
			type: 'boolean',
			default: false,
		},
		blockHeight: {
			type: 'number',
			default: 400,
		},
	},
	edit: BeforeAfterEdit,
	save: function () {
		return null; // Dynamic block, rendered server-side.
	},
} );
