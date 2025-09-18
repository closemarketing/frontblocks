"use strict";

// Add custom controls to the Gallery block
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { 
  SelectControl, 
  PanelBody, 
  ToggleControl, 
  RangeControl 
} = wp.components;
const { __ } = wp.i18n;

function addCustomGalleryPanel(BlockEdit) {
  return function (props) {
    if (props.name !== 'core/gallery') {
      return <BlockEdit {...props} />;
    }
    
    const {
      frblGalleryLayout = 'grid',
      frblGutterSize = 20,
      frblEnableLightbox = false
    } = props.attributes;

    return (
      <Fragment>
        <BlockEdit {...props} />
        <InspectorControls>
          <PanelBody
            title={__('FrontBlocks Gallery Settings', 'frontblocks')}
            initialOpen={true}
          >
            <SelectControl
              label={__('Gallery Layout', 'frontblocks')}
              value={frblGalleryLayout}
              options={[
                {
                  label: __('Grid', 'frontblocks'),
                  value: 'grid'
                },
                {
                  label: __('Masonry', 'frontblocks'),
                  value: 'masonry'
                }
              ]}
              onChange={(value) => {
                props.setAttributes({
                  frblGalleryLayout: value
                });
              }}
              help={__('Choose between grid or masonry layout for your gallery.', 'frontblocks')}
            />
            
              <RangeControl
                label={__('Gutter Size (px)', 'frontblocks')}
                value={frblGutterSize}
                onChange={(value) => {
                  props.setAttributes({
                    frblGutterSize: value
                  });
                }}
                min={0}
                max={50}
                step={5}
                help={__('Set the spacing between gallery items.', 'frontblocks')}
              />
            
            <ToggleControl
              label={__('Enable Lightbox', 'frontblocks')}
              checked={frblEnableLightbox}
              onChange={(value) => {
                props.setAttributes({
                  frblEnableLightbox: value
                });
              }}
              help={__('Enable lightbox functionality for gallery images.', 'frontblocks')}
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  };
}

addFilter('editor.BlockEdit', 'frontblocks/gallery-panel', addCustomGalleryPanel); 