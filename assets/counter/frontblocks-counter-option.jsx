const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor; 
const { PanelBody, ToggleControl } = wp.components;
// Mantenemos la desestructuración de addFilter por si se usa en otro lugar, 
// pero usaremos la sintaxis larga para el registro final.
const { addFilter } = wp.hooks; 

const BLOCK_NAME = 'generateblocks/text';

/**
 * 1. Añade el nuevo atributo al bloque GenerateBlocks Headline.
 * Usamos wp.hooks.addFilter para mayor consistencia con el código que funciona.
 */
wp.hooks.addFilter(
    'blocks.registerBlockType',
    'frontblocks/add-counter-attribute',
    ( settings, name ) => {
        if ( name === BLOCK_NAME ) {
            settings.attributes = Object.assign( settings.attributes, {
                isCounterActive: {
                    type: 'boolean',
                    default: false,
                },
            } );
        }
        return settings;
    }
);

/**
 * 2. Componente de Orden Superior (HOC) para añadir el control al editor.
 */
const withHeadlineCounterControl = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        // Solo aplica a nuestro bloque objetivo
        // Si el bloque no aparece, confirma que al hacer clic en el titular
        // la consola devuelve: wp.data.select('core/editor').getSelectedBlock()?.name === 'generateblocks/headline'
        if ( props.name !== BLOCK_NAME ) {
            return <BlockEdit { ...props } />;
        }

        const { attributes, setAttributes } = props;
        const { isCounterActive } = attributes;

        return (
            <Fragment>
                <BlockEdit { ...props } />

                <InspectorControls>
                    <PanelBody 
                        title="Frontblocks - Efecto Contador" 
                        initialOpen={ false }
                    >
                        <ToggleControl
                            label="Activar Efecto Contador"
                            help={ isCounterActive ? 
                                'El número en el titular se animará al hacer scroll.' : 
                                'Activar para habilitar la animación de conteo.' 
                            }
                            checked={ isCounterActive }
                            onChange={ ( val ) => setAttributes( { isCounterActive: val } ) }
                        />
                        <p style={ { marginTop: '10px', fontSize: '12px', color: '#777' } }>
                            <small>Asegúrate de que el texto comience con un número (ej., '123', '+500', o '€100').</small>
                        </p>
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'withHeadlineCounterControl' );

/**
 * 3. Engancha el HOC al editor.
 * Usamos wp.hooks.addFilter para asegurar que el hook se registre correctamente.
 */
wp.hooks.addFilter(
    'editor.BlockEdit',
    'frontblocks/headline-counter-control',
    withHeadlineCounterControl
);