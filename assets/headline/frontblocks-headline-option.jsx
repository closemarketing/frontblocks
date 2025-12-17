const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor; 
const { PanelBody, SelectControl, ToggleControl } = wp.components;
const { __, sprintf } = wp.i18n;

const LINE_CLASS_PREFIX = 'gb-line-effect-'; 
const MARQUEE_CLASS = 'gb-marquee-infinite-scroll';
const MARQUEE_SPEED_ATTR = 'frblMarqueeSpeed';
const BLOCK_NAME = 'generateblocks/text';

// Marquee speed presets
const MARQUEE_SPEEDS = {
    fast: 10,    // 10 seconds - fast
    medium: 20,  // 20 seconds - medium
    slow: 40     // 40 seconds - slow
};

// Register marquee speed attribute
wp.hooks.addFilter(
    'blocks.registerBlockType',
    'frontblocks/add-marquee-attribute',
    ( settings, name ) => {
        if ( name === BLOCK_NAME ) {
            settings.attributes = Object.assign( settings.attributes || {}, {
                [MARQUEE_SPEED_ATTR]: {
                    type: 'string',
                    default: 'medium',
                },
            } );
        }
        return settings;
    }
); 

const withHeadlineLineControl = createHigherOrderComponent( ( BlockEdit ) => {
   return ( props ) => {
      if ( props.name !== BLOCK_NAME ) {
         return <BlockEdit { ...props } />;
      }

      const { attributes, setAttributes } = props;
      const existingClasses = attributes.className || '';
      const htmlAttributes = attributes.htmlAttributes || {};
      const marqueeSpeed = attributes[MARQUEE_SPEED_ATTR] || 'medium';
        
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
         const updatedHtmlAttributes = { ...htmlAttributes };
         const newAttributes = { className: newClasses };

         if ( enabled ) {
            newClasses = ( newClasses + ' ' + MARQUEE_CLASS ).trim();
            newAttributes.className = newClasses;
            // Set default speed if not already set
            if ( !attributes[MARQUEE_SPEED_ATTR] ) {
               newAttributes[MARQUEE_SPEED_ATTR] = 'medium';
               updatedHtmlAttributes['data-marquee-speed'] = MARQUEE_SPEEDS.medium;
            } else {
               const speedValue = MARQUEE_SPEEDS[attributes[MARQUEE_SPEED_ATTR]] || MARQUEE_SPEEDS.medium;
               updatedHtmlAttributes['data-marquee-speed'] = speedValue;
            }
            newAttributes.htmlAttributes = updatedHtmlAttributes;
         } else {
            // Remove speed attribute when disabling
            newAttributes[MARQUEE_SPEED_ATTR] = undefined;
            delete updatedHtmlAttributes['data-marquee-speed'];
            newAttributes.htmlAttributes = updatedHtmlAttributes;
         }

         setAttributes( newAttributes );
      };

      /**
      * Maneja el cambio de la velocidad del marquee.
      */
      const setMarqueeSpeed = ( speedPreset ) => {
         const speedValue = MARQUEE_SPEEDS[speedPreset] || MARQUEE_SPEEDS.medium;
         const updatedHtmlAttributes = { ...htmlAttributes };
         updatedHtmlAttributes['data-marquee-speed'] = speedValue;
         setAttributes( { 
            [MARQUEE_SPEED_ATTR]: speedPreset,
            htmlAttributes: updatedHtmlAttributes
         } );
         
         // Update marquee wrapper directly if it exists (for immediate preview)
         setTimeout(function() {
            // Try to find the marquee wrapper in editor
            const blockElement = document.querySelector('[data-block="' + props.clientId + '"]');
            if (blockElement) {
               const marqueeElement = blockElement.querySelector('.gb-marquee-infinite-scroll');
               if (marqueeElement) {
                  const wrapper = marqueeElement.querySelector('.gb-marquee-wrapper');
                  if (wrapper && typeof wrapper.updateMarqueeSpeed === 'function') {
                     wrapper.updateMarqueeSpeed(speedValue);
                  } else if (wrapper) {
                     // Fallback: update directly
                     wrapper.setAttribute('data-marquee-speed', speedValue);
                     wrapper.style.setProperty('--marquee-speed', speedValue + 's');
                     // Force animation update
                     const currentAnimation = wrapper.style.animation;
                     if (currentAnimation) {
                        const match = currentAnimation.match(/marquee-scroll-[\w-]+/);
                        if (match) {
                           const styleId = match[0].replace('marquee-scroll-', '');
                           wrapper.style.animation = 'marquee-scroll-' + styleId + ' ' + speedValue + 's linear infinite';
                           wrapper.style.animationDuration = speedValue + 's';
                        }
                     }
                  }
               }
            }
         }, 50);
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

                  { isMarqueeEnabled && (
                     <SelectControl
                        label={ __( 'Marquee Speed', 'frontblocks' ) }
                        value={ marqueeSpeed }
                        onChange={ setMarqueeSpeed }
                        options={[
                           { label: __( 'Fast', 'frontblocks' ), value: 'fast' },
                           { label: __( 'Medium', 'frontblocks' ), value: 'medium' },
                           { label: __( 'Slow', 'frontblocks' ), value: 'slow' },
                        ]}
                        help={ __( 'Select the scrolling speed for the marquee effect.', 'frontblocks' ) }
                     />
                  )}
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