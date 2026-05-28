const { addFilter }                  = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { BlockControls }              = wp.blockEditor;
const {
	ToolbarButton,
	Modal,
	TextControl,
	SelectControl,
	Button,
	Spinner,
	Flex,
	FlexItem,
	__experimentalText: Text,
} = wp.components;
const { useState, useEffect, Fragment } = wp.element;
const { useSelect }                     = wp.data;
const { __ }                            = wp.i18n;
const apiFetch                          = wp.apiFetch;

const BINDABLE = {
	'core/paragraph': 'content',
	'core/heading':   'content',
};

function stripHtml( html ) {
	return html ? html.replace( /<[^>]+>/g, '' ) : '';
}

const withConvertToMeta = createHigherOrderComponent( ( BlockEdit ) => {
	return function ConvertToMetaWrapper( props ) {
		const { name, attributes, setAttributes } = props;

		const attrName = BINDABLE[ name ];
		if ( ! attrName ) {
			return <BlockEdit { ...props } />;
		}

		const [ isOpen,           setIsOpen           ] = useState( false );
		const [ mode,             setMode             ] = useState( 'new' );
		const [ metaKey,          setMetaKey          ] = useState( '' );
		const [ metaLabel,        setMetaLabel        ] = useState( '' );
		const [ metaValue,        setMetaValue        ] = useState( '' );
		const [ existingFields,   setExistingFields   ] = useState( [] );
		const [ selectedExisting, setSelectedExisting ] = useState( '' );
		const [ isLoading,        setIsLoading        ] = useState( false );
		const [ errorMsg,         setErrorMsg         ] = useState( '' );

		const { postType, postId } = useSelect( ( select ) => ( {
			postType: select( 'core/editor' ).getCurrentPostType(),
			postId:   select( 'core/editor' ).getCurrentPostId(),
		} ) );

		const frblMeta       = ( attributes.metadata && attributes.metadata.frblMeta ) || {};
		const isAlreadyBound = !! frblMeta[ attrName ];

		useEffect( () => {
			if ( ! isOpen || ! postType ) { return; }
			apiFetch( { path: '/frontblocks/v1/meta-fields?post_type=' + postType } )
				.then( ( fields ) => {
					setExistingFields( fields );
					if ( fields.length > 0 ) {
						setMode( 'existing' );
					}
				} )
				.catch( () => setExistingFields( [] ) );
		}, [ isOpen, postType ] );

		function openModal() {
			setMetaValue( stripHtml( attributes[ attrName ] || '' ) );
			setIsOpen( true );
		}

		function resetForm() {
			setMode( 'new' );
			setMetaKey( '' );
			setMetaLabel( '' );
			setMetaValue( '' );
			setSelectedExisting( '' );
			setErrorMsg( '' );
		}

		async function handleConfirm() {
			setIsLoading( true );
			setErrorMsg( '' );

			let fieldKey  = '';
			let fieldType = 'text';

			try {
				if ( 'new' === mode ) {
					if ( ! metaKey.trim() ) {
						setErrorMsg( __( 'El nombre del campo (key) es obligatorio.', 'frontblocks' ) );
						setIsLoading( false );
						return;
					}
					const res = await apiFetch( {
						path:   '/frontblocks/v1/meta-fields',
						method: 'POST',
						data:   {
							post_type: postType,
							key:       metaKey.trim(),
							label:     metaLabel.trim() || metaKey.trim(),
							type:      'text',
						},
					} );
					fieldKey  = res.field.key;
					fieldType = res.field.type;
				} else {
					if ( ! selectedExisting ) {
						setErrorMsg( __( 'Selecciona un meta existente.', 'frontblocks' ) );
						setIsLoading( false );
						return;
					}
					fieldKey  = selectedExisting;
					fieldType = ( existingFields.find( ( f ) => f.key === selectedExisting ) || {} ).type || 'text';
				}

				if ( metaValue.trim() ) {
					await apiFetch( {
						path:   '/frontblocks/v1/save-meta',
						method: 'POST',
						data:   { post_id: postId, key: fieldKey, value: metaValue.trim() },
					} );
				}

				const newFrblMeta = Object.assign( {}, frblMeta );
				newFrblMeta[ attrName ] = { key: fieldKey, type: fieldType };

				setAttributes( {
					metadata:     Object.assign( {}, attributes.metadata || {}, { frblMeta: newFrblMeta } ),
					[ attrName ]: metaValue.trim() || attributes[ attrName ],
				} );

				setIsOpen( false );
				resetForm();
			} catch ( e ) {
				setErrorMsg( __( 'Error al registrar el meta. Inténtalo de nuevo.', 'frontblocks' ) );
			}

			setIsLoading( false );
		}

		const existingOptions = [
			{ label: __( '— Elige un campo —', 'frontblocks' ), value: '' },
			...existingFields.map( ( f ) => ( {
				label: ( f.label || f.key ) + '  ·  ' + f.key,
				value: f.key,
			} ) ),
		];

		const tabStyle = ( active ) => ( {
			padding:       '6px 14px',
			fontSize:      '13px',
			fontWeight:    active ? '600' : '400',
			border:        '1px solid ' + ( active ? '#007cba' : '#ddd' ),
			borderRadius:  '4px',
			background:    active ? '#007cba' : '#fff',
			color:         active ? '#fff' : '#555',
			cursor:        'pointer',
			transition:    'all 0.15s',
		} );

		const fieldGroupStyle = {
			background:   '#f9f9f9',
			border:       '1px solid #e5e5e5',
			borderRadius: '6px',
			padding:      '16px',
			marginBottom: '16px',
		};

		return (
			<Fragment>
				<BlockEdit { ...props } />

				<BlockControls group="other">
					{ isAlreadyBound ? (
						<ToolbarButton
							icon="database"
							label={ __( 'Meta vinculado: ' + ( frblMeta[ attrName ] ? frblMeta[ attrName ].key : '' ), 'frontblocks' ) }
							onClick={ openModal }
							style={ {
								background:   '#1e1e1e',
								color:        '#fff',
								borderRadius: '2px',
								padding:      '6px',
								width:        'auto',
								height:       'auto',
							} }
						/>
					) : (
						<ToolbarButton
							icon="database-add"
							label={ __( 'Convertir a meta', 'frontblocks' ) }
							onClick={ openModal }
						/>
					) }
				</BlockControls>

				{ isOpen && (
					<Modal
						title={ __( 'Vincular a meta dinámica', 'frontblocks' ) }
						onRequestClose={ () => { setIsOpen( false ); resetForm(); } }
						style={ { maxWidth: '480px', width: '100%' } }
					>
						{ /* Tabs */ }
						<div style={ { display: 'flex', gap: '8px', marginBottom: '20px' } }>
							<button
								style={ tabStyle( 'new' === mode ) }
								onClick={ () => setMode( 'new' ) }
							>
								{ __( '+ Crear nuevo', 'frontblocks' ) }
							</button>
							{ existingFields.length > 0 && (
								<button
									style={ tabStyle( 'existing' === mode ) }
									onClick={ () => setMode( 'existing' ) }
								>
									{ __( 'Usar existente', 'frontblocks' ) }
								</button>
							) }
						</div>

						{ 'new' === mode ? (
							<div style={ fieldGroupStyle }>
								<p style={ { margin: '0 0 12px', fontSize: '12px', color: '#666', textTransform: 'uppercase', letterSpacing: '0.05em', fontWeight: '600' } }>
									{ __( 'Nuevo campo meta', 'frontblocks' ) }
								</p>
								<TextControl
									label={ __( 'Key (nombre interno)', 'frontblocks' ) }
									value={ metaKey }
									onChange={ setMetaKey }
									placeholder="fecha_proyecto"
									help={ __( 'Letras minúsculas, números y guiones bajos.', 'frontblocks' ) }
									__nextHasNoMarginBottom
								/>
								<div style={ { marginTop: '12px' } }>
									<TextControl
										label={ __( 'Etiqueta legible', 'frontblocks' ) }
										value={ metaLabel }
										onChange={ setMetaLabel }
										placeholder={ __( 'Fecha del proyecto', 'frontblocks' ) }
										__nextHasNoMarginBottom
									/>
								</div>
							</div>
						) : (
							<div style={ fieldGroupStyle }>
								<p style={ { margin: '0 0 12px', fontSize: '12px', color: '#666', textTransform: 'uppercase', letterSpacing: '0.05em', fontWeight: '600' } }>
									{ __( 'Campo existente', 'frontblocks' ) }
								</p>
								<SelectControl
									label={ __( 'Selecciona un campo', 'frontblocks' ) }
									value={ selectedExisting }
									options={ existingOptions }
									onChange={ setSelectedExisting }
									__nextHasNoMarginBottom
								/>
							</div>
						) }

						{ /* Value */ }
						<div style={ { ...fieldGroupStyle, background: '#fff' } }>
							<p style={ { margin: '0 0 12px', fontSize: '12px', color: '#666', textTransform: 'uppercase', letterSpacing: '0.05em', fontWeight: '600' } }>
								{ __( 'Valor para este post', 'frontblocks' ) }
							</p>
							<TextControl
								label={ __( 'Contenido', 'frontblocks' ) }
								value={ metaValue }
								onChange={ setMetaValue }
								help={ __( 'Se guardará en la base de datos y se mostrará en el editor.', 'frontblocks' ) }
								__nextHasNoMarginBottom
							/>
						</div>

						{ !! errorMsg && (
							<div style={ { background: '#fff0f0', border: '1px solid #fcc', borderRadius: '4px', padding: '10px 14px', marginBottom: '12px', color: '#cc1818', fontSize: '13px' } }>
								{ errorMsg }
							</div>
						) }

						<div style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end', paddingTop: '4px', borderTop: '1px solid #eee', marginTop: '4px', paddingTop: '16px' } }>
							<Button
								variant="tertiary"
								onClick={ () => { setIsOpen( false ); resetForm(); } }
							>
								{ __( 'Cancelar', 'frontblocks' ) }
							</Button>
							<Button variant="primary" onClick={ handleConfirm } disabled={ isLoading } style={ { minWidth: '100px' } }>
								{ isLoading ? <Spinner /> : __( 'Vincular', 'frontblocks' ) }
							</Button>
						</div>
					</Modal>
				) }
			</Fragment>
		);
	};
}, 'withConvertToMeta' );

addFilter( 'editor.BlockEdit', 'frontblocks/convert-to-meta', withConvertToMeta );
