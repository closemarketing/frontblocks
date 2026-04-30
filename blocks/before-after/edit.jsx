( function () {
	const { registerBlockType } = wp.blocks;
	const {
		MediaUpload,
		MediaUploadCheck,
		InspectorControls,
		RichText,
		useBlockProps,
	} = wp.blockEditor;
	const { PanelBody, Button, RangeControl, TextControl, Placeholder } =
		wp.components;
	const { __ } = wp.i18n;

	function EditBeforeAfter( { attributes, setAttributes } ) {
		const {
			beforeImageId,
			beforeImageUrl,
			afterImageId,
			afterImageUrl,
			beforeLabel,
			afterLabel,
			initialPosition,
		} = attributes;

		const blockProps = useBlockProps( {
			className: 'frbl-before-after frbl-before-after--editor',
			'data-initial-position': initialPosition,
		} );

		const hasImages = beforeImageUrl && afterImageUrl;

		return (
			<>
				<InspectorControls>
					<PanelBody
						title={ __( 'Before Image', 'frontblocks' ) }
						initialOpen={ true }
					>
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
									<>
										{ beforeImageUrl && (
											<img
												src={ beforeImageUrl }
												alt=""
												style={ {
													width: '100%',
													marginBottom: '8px',
													borderRadius: '2px',
												} }
											/>
										) }
										<Button
											onClick={ open }
											variant={
												beforeImageUrl
													? 'secondary'
													: 'primary'
											}
											style={ { width: '100%' } }
										>
											{ beforeImageUrl
												? __(
														'Replace Before Image',
														'frontblocks'
												  )
												: __(
														'Select Before Image',
														'frontblocks'
												  ) }
										</Button>
										{ beforeImageUrl && (
											<Button
												onClick={ () =>
													setAttributes( {
														beforeImageId:
															undefined,
														beforeImageUrl: '',
													} )
												}
												variant="link"
												isDestructive
												style={ {
													marginTop: '4px',
													display: 'block',
												} }
											>
												{ __(
													'Remove',
													'frontblocks'
												) }
											</Button>
										) }
									</>
								) }
							/>
						</MediaUploadCheck>
						<TextControl
							label={ __( 'Before Label', 'frontblocks' ) }
							value={ beforeLabel }
							onChange={ ( val ) =>
								setAttributes( { beforeLabel: val } )
							}
							style={ { marginTop: '12px' } }
						/>
					</PanelBody>

					<PanelBody
						title={ __( 'After Image', 'frontblocks' ) }
						initialOpen={ true }
					>
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
									<>
										{ afterImageUrl && (
											<img
												src={ afterImageUrl }
												alt=""
												style={ {
													width: '100%',
													marginBottom: '8px',
													borderRadius: '2px',
												} }
											/>
										) }
										<Button
											onClick={ open }
											variant={
												afterImageUrl
													? 'secondary'
													: 'primary'
											}
											style={ { width: '100%' } }
										>
											{ afterImageUrl
												? __(
														'Replace After Image',
														'frontblocks'
												  )
												: __(
														'Select After Image',
														'frontblocks'
												  ) }
										</Button>
										{ afterImageUrl && (
											<Button
												onClick={ () =>
													setAttributes( {
														afterImageId:
															undefined,
														afterImageUrl: '',
													} )
												}
												variant="link"
												isDestructive
												style={ {
													marginTop: '4px',
													display: 'block',
												} }
											>
												{ __(
													'Remove',
													'frontblocks'
												) }
											</Button>
										) }
									</>
								) }
							/>
						</MediaUploadCheck>
						<TextControl
							label={ __( 'After Label', 'frontblocks' ) }
							value={ afterLabel }
							onChange={ ( val ) =>
								setAttributes( { afterLabel: val } )
							}
							style={ { marginTop: '12px' } }
						/>
					</PanelBody>

					<PanelBody
						title={ __( 'Slider Settings', 'frontblocks' ) }
						initialOpen={ false }
					>
						<RangeControl
							label={ __(
								'Initial Handle Position (%)',
								'frontblocks'
							) }
							value={ initialPosition }
							onChange={ ( val ) =>
								setAttributes( { initialPosition: val } )
							}
							min={ 0 }
							max={ 100 }
						/>
					</PanelBody>
				</InspectorControls>

				{ ! hasImages ? (
					<div { ...blockProps }>
						<Placeholder
							icon="image-flip-horizontal"
							label={ __( 'Before / After', 'frontblocks' ) }
							instructions={ __(
								'Select a "before" image and an "after" image from the sidebar.',
								'frontblocks'
							) }
						/>
					</div>
				) : (
					<div { ...blockProps }>
						<div className="frbl-before-after__after">
							<img src={ afterImageUrl } alt="" />
							<span className="frbl-before-after__label frbl-before-after__label--after">
								{ afterLabel }
							</span>
						</div>
						<div
							className="frbl-before-after__before"
							style={ {
								clipPath: `inset(0 ${ 100 - initialPosition }% 0 0)`,
							} }
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
								<svg
									viewBox="0 0 24 24"
									width="20"
									height="20"
									fill="none"
									stroke="currentColor"
									strokeWidth="2"
									strokeLinecap="round"
									strokeLinejoin="round"
								>
									<polyline points="15 18 9 12 15 6"></polyline>
									<polyline points="9 18 3 12 9 6"></polyline>
									<polyline points="15 18 21 12 15 6"></polyline>
									<polyline points="9 18 15 12 9 6"></polyline>
								</svg>
							</span>
							<span className="frbl-before-after__handle-line"></span>
						</div>
					</div>
				) }
			</>
		);
	}

	registerBlockType( 'frontblocks/before-after', {
		edit: EditBeforeAfter,
		save: function () {
			return null;
		},
	} );
} )();
