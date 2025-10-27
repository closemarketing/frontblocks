"use strict";

// Add custom controls to the Gallery block
var addFilter = wp.hooks.addFilter;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useEffect = _wp$element.useEffect;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  SelectControl = _wp$components.SelectControl,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl,
  RangeControl = _wp$components.RangeControl;
var __ = wp.i18n.__;
var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
function addCustomGalleryPanel(BlockEdit) {
  return function (props) {
    if (props.name !== 'core/gallery') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var _props$attributes = props.attributes,
      _props$attributes$frb = _props$attributes.frblGalleryLayout,
      frblGalleryLayout = _props$attributes$frb === void 0 ? 'grid' : _props$attributes$frb,
      _props$attributes$frb2 = _props$attributes.frblGutterSize,
      frblGutterSize = _props$attributes$frb2 === void 0 ? 20 : _props$attributes$frb2,
      _props$attributes$frb3 = _props$attributes.frblEnableLightbox,
      frblEnableLightbox = _props$attributes$frb3 === void 0 ? false : _props$attributes$frb3;

    // Apply dynamic styles in the editor.
    useEffect(function () {
      var blockElement = document.querySelector("[data-block=\"".concat(props.clientId, "\"] .wp-block-gallery"));
      if (blockElement) {
        // Remove previous layout classes.
        blockElement.classList.remove('frontblocks-gallery-grid', 'frontblocks-gallery-masonry');

        // Add current layout class.
        if (frblGalleryLayout === 'masonry') {
          blockElement.classList.add('frontblocks-gallery-masonry');
        } else {
          blockElement.classList.add('frontblocks-gallery-grid');
        }

        // Set gutter size as CSS variable.
        blockElement.style.setProperty('--frontblocks-gutter', frblGutterSize + 'px');
      }
    }, [frblGalleryLayout, frblGutterSize, props.clientId]);
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks Gallery Settings', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Gallery Layout', 'frontblocks'),
      value: frblGalleryLayout,
      options: [{
        label: __('Grid', 'frontblocks'),
        value: 'grid'
      }, {
        label: __('Masonry', 'frontblocks'),
        value: 'masonry'
      }],
      onChange: function onChange(value) {
        props.setAttributes({
          frblGalleryLayout: value
        });
      },
      help: __('Choose between grid or masonry layout for your gallery.', 'frontblocks')
    }), /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Gutter Size (px)', 'frontblocks'),
      value: frblGutterSize,
      onChange: function onChange(value) {
        props.setAttributes({
          frblGutterSize: value
        });
      },
      min: 0,
      max: 50,
      step: 5,
      help: __('Set the spacing between gallery items.', 'frontblocks')
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Enable Lightbox', 'frontblocks'),
      checked: frblEnableLightbox,
      onChange: function onChange(value) {
        props.setAttributes({
          frblEnableLightbox: value
        });
      },
      help: __('Enable lightbox functionality for gallery images.', 'frontblocks')
    }))));
  };
}
addFilter('editor.BlockEdit', 'frontblocks/gallery-panel', addCustomGalleryPanel);
