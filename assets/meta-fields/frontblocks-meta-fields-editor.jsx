const { addFilter }                  = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls }          = wp.blockEditor;
const { PanelBody, SelectControl }   = wp.components;
const { useSelect }                  = wp.data;
const { __ }                         = wp.i18n;
const { Fragment }                   = wp.element;

/**
 * Blocks that support meta bindings and which of their attributes are bindable.
 */
const BINDABLE_BLOCKS = {
	'core/paragraph': {
		content: __( 'Contenido', 'frontblocks' ),
	},
	'core/heading': {
		content: __( 'Contenido', 'frontblocks' ),
	},
	'core/image': {
		url: __( 'URL de imagen', 'frontblocks' ),
		alt: __( 'Texto alternativo', 'frontblocks' ),
	},
	'core/button': {
		text: __( 'Texto del botón', 'frontblocks' ),
		url:  __( 'URL', 'frontblocks' ),
	},
};

const SOURCE_KEY = frblMetaFields.sourceKey;
const ALL_FIELDS  = frblMetaFields.fields; // { post_type: [ { key, label, type }, … ] }

/**
 * HOC — injects the "Meta dinámico" panel into matching blocks.
 */
const withMetaBindingPanel = createHigherOrderComponent( ( BlockEdit ) => {
	return function FrblMetaBlockEdit( props ) {
		const { name, attributes, setAttributes } = props;

		const bindableAttrs = BINDABLE_BLOCKS[ name ];

		// Block context postType covers query loops; fall back to current post type for singles.
		const editorPostType = useSelect( ( select ) => {
			const editor = select( 'core/editor' );
			return editor ? editor.getCurrentPostType() : null;
		} );
		const postType = ( props.context && props.context.postType ) || editorPostType;

		if ( ! bindableAttrs || ! postType ) {
			return <BlockEdit { ...props } />;
		}

		const availableFields = ALL_FIELDS[ postType ] || [];

		if ( 0 === availableFields.length ) {
			return <BlockEdit { ...props } />;
		}

		const currentBindings = ( attributes.metadata && attributes.metadata.bindings ) ? attributes.metadata.bindings : {};

		const fieldOptions = [
			{ label: __( '— Ninguno —', 'frontblocks' ), value: '' },
			...availableFields.map( ( f ) => ( { label: f.label + ' — ' + f.key, value: f.key } ) ),
		];

		function getFieldType( key ) {
			const field = availableFields.find( ( f ) => f.key === key );
			return field ? field.type : 'text';
		}

		function setBinding( attr, fieldKey ) {
			const bindings = Object.assign( {}, currentBindings );

			if ( ! fieldKey ) {
				delete bindings[ attr ];
			} else {
				bindings[ attr ] = {
					source: SOURCE_KEY,
					args:   { key: fieldKey, type: getFieldType( fieldKey ) },
				};
			}

			const metadata = Object.assign( {}, attributes.metadata || {} );

			if ( 0 === Object.keys( bindings ).length ) {
				delete metadata.bindings;
			} else {
				metadata.bindings = bindings;
			}

			setAttributes( { metadata: metadata } );
		}

		function getBoundKey( attr ) {
			const binding = currentBindings[ attr ];
			return ( binding && binding.args && binding.args.key ) ? binding.args.key : '';
		}

		const hasBindings = Object.keys( currentBindings ).length > 0;

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Meta dinámico', 'frontblocks' ) }
						initialOpen={ hasBindings }
					>
						{ Object.entries( bindableAttrs ).map( ( [ attr, attrLabel ] ) => (
							<SelectControl
								key={ attr }
								label={ attrLabel }
								value={ getBoundKey( attr ) }
								options={ fieldOptions }
								onChange={ ( val ) => setBinding( attr, val ) }
							/>
						) ) }
						{ hasBindings && (
							<p style={ { fontSize: '12px', color: '#757575', marginTop: '8px', marginBottom: 0 } }>
								{ __( 'El valor real se renderiza en el frontend.', 'frontblocks' ) }
							</p>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withMetaBindingPanel' );

addFilter(
	'editor.BlockEdit',
	'frontblocks/meta-binding',
	withMetaBindingPanel
);
