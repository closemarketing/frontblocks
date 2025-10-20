const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor; 
const { PanelBody, SelectControl } = wp.components;
const { __, sprintf } = wp.i18n;

const LINE_CLASS_PREFIX = 'gb-line-effect-'; 
const BLOCK_NAME = 'generateblocks/text'; 

const withHeadlineLineControl = createHigherOrderComponent( ( BlockEdit ) => {
   return ( props ) => {
      if ( props.name !== BLOCK_NAME ) {
         return <BlockEdit { ...props } />;
      }

      const { attributes, setAttributes } = props;
      const existingClasses = attributes.className || '';
        
      const cleanExistingLineClasses = ( classes ) => {
         return classes
            .split(' ')
            .filter(cls => !cls.startsWith(LINE_CLASS_PREFIX))
            .join(' ')
            .replace( /\s{2,}/g, ' ' )
            .trim();
      };

      let currentLineStyle = 'none';
      if (existingClasses.includes(LINE_CLASS_PREFIX + 'vertical')) {
         currentLineStyle = 'vertical';
      } else if (existingClasses.includes(LINE_CLASS_PREFIX + 'horizontal')) {
         currentLineStyle = 'horizontal';
      }


      /**
      * Maneja el cambio del SelectControl y actualiza las clases CSS.
      */
      const setLineStyle = ( newStyle ) => {
         let newClasses = cleanExistingLineClasses(existingClasses);

         if ( newStyle !== 'none' ) {
               const classToAdd = LINE_CLASS_PREFIX + newStyle;
               newClasses = ( newClasses + ' ' + classToAdd ).trim();
            }

         setAttributes( { className: newClasses } );
      };


      return (
         <Fragment>
            <BlockEdit { ...props } />

            <InspectorControls>
               <PanelBody 
                  title={ __( 'Frontblocks - Visual Effects', 'frontblocks' ) }
                  initialOpen={ false }
               >
                  <p style={{ marginTop: 0, marginBottom: '10px' }}>
                     <small>{ __( 'Frontblocks visual effect settings.', 'frontblocks' ) }</small>
                  </p>
                     
                  <SelectControl
                     label={ __( 'Decorative Line Style', 'frontblocks' ) }
                     value={ currentLineStyle }
                     options={[
                        { label: __( 'None', 'frontblocks' ), value: 'none' },
                        { label: __( 'Vertical Line (Right)', 'frontblocks' ), value: 'vertical' },
                        { label: __( 'Horizontal Line (Right)', 'frontblocks' ), value: 'horizontal' },
                     ]}
                     onChange={ setLineStyle }
                     help={ 
                           currentLineStyle === 'none' ? 
                           __( 'Select a line style to add a decorative element.', 'frontblocks' ) : 
                           sprintf( 
                              __( 'Current style: %s.', 'frontblocks' ), 
                              currentLineStyle.charAt(0).toUpperCase() + currentLineStyle.slice(1) 
                           )
                     }
                  />
               </PanelBody>
               
            </InspectorControls>
         </Fragment>
      );
   };
}, 'withHeadlineLineControl' );

wp.hooks.addFilter(
    'editor.BlockEdit',
    'frontblocks/headline-line-control',
    withHeadlineLineControl
);