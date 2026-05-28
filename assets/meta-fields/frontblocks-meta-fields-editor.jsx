const { addFilter }                  = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { BlockControls }              = wp.blockEditor;
const {
	ToolbarButton,
	Modal,
	TextControl,
	SelectControl,
	RadioControl,
	Button,
	Spinner,
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
		const [ metaType,         setMetaType         ] = useState( 'text' );
		const [ metaValue,        setMetaValue        ] = useState( '' );
		const [ existingFields,   setExistingFields   ] = useState( [] );
		const [ selectedExisting, setSelectedExisting ] = useState( '' );
		const [ isLoading,        setIsLoading        ] = useState( false );
		const [ errorMsg,         setErrorMsg         ] = useState( '' );

		const { postType, postId } = useSelect( ( select ) => ( {
			postType: select( 'core/editor' ).getCurrentPostType(),
			postId:   select( 'core/editor' ).getCurrentPostId(),
		} ) );

		// frblMeta is our namespace — does NOT trigger WP's binding label override.
		const frblMeta      = ( attributes.metadata && attributes.metadata.frblMeta ) || {};
		const isAlreadyBound = !! frblMeta[ attrName ];
		const boundKey       = isAlreadyBound ? frblMeta[ attrName ].key : '';

		useEffect( () => {
			if ( ! isOpen || ! postType ) { return; }
			apiFetch( {
				url:     frblMetaConfig.restUrl + '?post_type=' + postType,
				headers: { 'X-WP-Nonce': frblMetaConfig.nonce },
			} )
				.then( ( fields ) => setExistingFields( fields ) )
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
			setMetaType( 'text' );
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
						url:     frblMetaConfig.restUrl,
						method:  'POST',
						data:    {
							post_type: postType,
							key:       metaKey.trim(),
							label:     metaLabel.trim() || metaKey.trim(),
							type:      metaType,
						},
						headers: { 'X-WP-Nonce': frblMetaConfig.nonce },
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

				// Save meta value directly to DB.
				if ( metaValue.trim() ) {
					await apiFetch( {
						url:     frblMetaConfig.saveMetaUrl,
						method:  'POST',
						data:    { post_id: postId, key: fieldKey, value: metaValue.trim() },
						headers: { 'X-WP-Nonce': frblMetaConfig.nonce },
					} );
				}

				// Store binding in metadata.frblMeta (not metadata.bindings) so WP
				// never replaces the block content with its binding source label.
				const newFrblMeta = Object.assign( {}, frblMeta );
				newFrblMeta[ attrName ] = { key: fieldKey, type: fieldType };

				setAttributes( {
					metadata:     Object.assign( {}, attributes.metadata || {}, { frblMeta: newFrblMeta } ),
					[ attrName ]: metaValue.trim(), // show real value in editor
				} );

				setIsOpen( false );
				resetForm();
			} catch ( e ) {
				setErrorMsg( __( 'Error al registrar. Inténtalo de nuevo.', 'frontblocks' ) );
			}

			setIsLoading( false );
		}

		const existingOptions = [
			{ label: __( '— Elegir —', 'frontblocks' ), value: '' },
			...existingFields.map( ( f ) => ( {
				label: f.label + ' — ' + f.key,
				value: f.key,
			} ) ),
		];

		return (
			<Fragment>
				<BlockEdit { ...props } />

				{ ! isAlreadyBound && (
					<BlockControls group="other">
						<ToolbarButton
							icon="database-add"
							label={ __( 'Convertir a meta', 'frontblocks' ) }
							onClick={ openModal }
						/>
					</BlockControls>
				) }

				{ isOpen && (
					<Modal
						title={ __( 'Meta dinámico', 'frontblocks' ) }
						onRequestClose={ () => { setIsOpen( false ); resetForm(); } }
						style={ { maxWidth: '460px' } }
					>
						{ existingFields.length > 0 && (
							<RadioControl
								label={ __( '¿Qué quieres hacer?', 'frontblocks' ) }
								selected={ mode }
								options={ [
									{ label: __( 'Crear nuevo meta',    'frontblocks' ), value: 'new'      },
									{ label: __( 'Usar meta existente', 'frontblocks' ), value: 'existing' },
								] }
								onChange={ setMode }
							/>
						) }

						{ 'new' === mode ? (
							<Fragment>
								<TextControl
									label={ __( 'Nombre del campo (key)', 'frontblocks' ) }
									value={ metaKey }
									onChange={ setMetaKey }
									placeholder="fecha_proyecto"
									help={ __( 'Solo letras minúsculas, números y guiones bajos.', 'frontblocks' ) }
								/>
								<TextControl
									label={ __( 'Etiqueta legible (opcional)', 'frontblocks' ) }
									value={ metaLabel }
									onChange={ setMetaLabel }
									placeholder={ __( 'Fecha del proyecto', 'frontblocks' ) }
								/>
								<SelectControl
									label={ __( 'Tipo', 'frontblocks' ) }
									value={ metaType }
									options={ [
										{ label: __( 'Texto', 'frontblocks' ), value: 'text' },
									] }
									onChange={ setMetaType }
								/>
							</Fragment>
						) : (
							<SelectControl
								label={ __( 'Meta existente', 'frontblocks' ) }
								value={ selectedExisting }
								options={ existingOptions }
								onChange={ setSelectedExisting }
							/>
						) }

						<TextControl
							label={ __( 'Valor para este post', 'frontblocks' ) }
							value={ metaValue }
							onChange={ setMetaValue }
							help={ __( 'Se guardará como meta de este post y vinculará el bloque.', 'frontblocks' ) }
						/>

						{ !! errorMsg && (
							<p style={ { color: '#cc1818', margin: '8px 0 0' } }>{ errorMsg }</p>
						) }

						<div style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end', marginTop: '20px' } }>
							<Button
								variant="secondary"
								onClick={ () => { setIsOpen( false ); resetForm(); } }
							>
								{ __( 'Cancelar', 'frontblocks' ) }
							</Button>
							<Button variant="primary" onClick={ handleConfirm } disabled={ isLoading }>
								{ isLoading ? <Spinner /> : __( 'Convertir', 'frontblocks' ) }
							</Button>
						</div>
					</Modal>
				) }
			</Fragment>
		);
	};
}, 'withConvertToMeta' );

addFilter( 'editor.BlockEdit', 'frontblocks/convert-to-meta', withConvertToMeta );
