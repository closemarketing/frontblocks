const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor; 
const { PanelBody, SelectControl, ToggleControl, RangeControl } = wp.components;
const { __, sprintf } = wp.i18n;

const LINE_CLASS_PREFIX = 'gb-line-effect-'; 
const MARQUEE_CLASS = 'gb-marquee-infinite-scroll';
const MARQUEE_SPEED_ATTR = 'frblMarqueeSpeed';
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

      const cleanMarqueeClass = ( classes ) => {
         return classes
            .split(' ')
            .filter(cls => cls !== MARQUEE_CLASS)
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

      const isMarqueeEnabled = existingClasses.includes(MARQUEE_CLASS);


      /**
      * Maneja el cambio del SelectControl y actualiza las clases CSS.
      */
      const setLineStyle = ( newStyle ) => {
         let newClasses = cleanExistingLineClasses(existingClasses);

         if ( newStyle !== 'none' ) {
               const classToAdd = LINE_CLASS_PREFIX + newStyle;
               newClasses = ( newClasses + ' ' + classToAdd ).trim();
            }

         // Preserve marquee class if enabled
         if ( isMarqueeEnabled ) {
            newClasses = ( newClasses + ' ' + MARQUEE_CLASS ).trim();
         }

         setAttributes( { className: newClasses } );
      };

      /**
      * Maneja el cambio del ToggleControl para el marquee y actualiza las clases CSS.
      */
      const setMarqueeEnabled = ( enabled ) => {
         let newClasses = cleanMarqueeClass(existingClasses);

         if ( enabled ) {
            newClasses = ( newClasses + ' ' + MARQUEE_CLASS ).trim();
         }

         setAttributes( { className: newClasses } );
      };


      return (
         <Fragment>
            <BlockEdit { ...props } />

            <InspectorControls>
               <PanelBody 
                  title={ __( 'FrontBlocks - Visual Effects', 'frontblocks' ) }
                  initialOpen={ false }
               >
                  <p style={{ marginTop: 0, marginBottom: '10px' }}>
                     <small>{ __( 'FrontBlocks visual effect settings.', 'frontblocks' ) }</small>
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

                  <ToggleControl
                     label={ __( 'Infinite Scrolling Marquee', 'frontblocks' ) }
                     checked={ isMarqueeEnabled }
                     onChange={ setMarqueeEnabled }
                     help={ 
                        isMarqueeEnabled ? 
                        __( 'Marquee effect is active. Text will scroll infinitely.', 'frontblocks' ) : 
                        __( 'Enable infinite scrolling marquee effect for the headline text.', 'frontblocks' )
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