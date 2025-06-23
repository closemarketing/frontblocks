"use strict";

// Add custom controls to the Advanced panel of GenerateBlocks Grid block
var addFilter = wp.hooks.addFilter;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  PanelColorSettings = _wp$blockEditor.PanelColorSettings;
var _wp$components = wp.components,
  SelectControl = _wp$components.SelectControl,
  TextControl = _wp$components.TextControl,
  PanelBody = _wp$components.PanelBody;
var __ = wp.i18n.__;
function addCustomCarouselPanel(BlockEdit) {
  return function (props) {
    if (props.name !== 'generateblocks/grid') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var _props$attributes = props.attributes,
      _props$attributes$cmC = _props$attributes.cmCustomGridOption,
      cmCustomGridOption = _props$attributes$cmC === void 0 ? 'none' : _props$attributes$cmC,
      _props$attributes$cmI = _props$attributes.cmItemsToView,
      cmItemsToView = _props$attributes$cmI === void 0 ? '4' : _props$attributes$cmI,
      _props$attributes$cmR = _props$attributes.cmResponsiveToView,
      cmResponsiveToView = _props$attributes$cmR === void 0 ? '1' : _props$attributes$cmR,
      _props$attributes$cmA = _props$attributes.cmAutoplay,
      cmAutoplay = _props$attributes$cmA === void 0 ? '' : _props$attributes$cmA,
      _props$attributes$cmB = _props$attributes.cmButtons,
      cmButtons = _props$attributes$cmB === void 0 ? 'arrows' : _props$attributes$cmB,
      cmButtonColor = _props$attributes.cmButtonColor,
      cmButtonBgColor = _props$attributes.cmButtonBgColor;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Carousel Settings', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Custom Grid Option', 'frontblocks'),
      value: cmCustomGridOption,
      options: [{
        label: __('None', 'frontblocks'),
        value: 'none'
      }, {
        label: __('Carousel', 'frontblocks'),
        value: 'carousel'
      }],
      onChange: function onChange(value) {
        props.setAttributes({
          cmCustomGridOption: value
        });
      },
      help: __('This is a custom option for the Grid block.', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Items to view', 'frontblocks'),
      value: cmItemsToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          cmItemsToView: value
        });
      }
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Responsive to view', 'frontblocks'),
      value: cmResponsiveToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          cmResponsiveToView: value
        });
      }
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Autoplay (ms)', 'frontblocks'),
      value: cmAutoplay,
      onChange: function onChange(value) {
        return props.setAttributes({
          cmAutoplay: value
        });
      }
    }), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Buttons', 'frontblocks'),
      value: cmButtons,
      options: [{
        label: __('None', 'frontblocks'),
        value: 'none'
      }, {
        label: __('Bullets', 'frontblocks'),
        value: 'bullets'
      }, {
        label: __('Arrows', 'frontblocks'),
        value: 'arrows'
      }],
      onChange: function onChange(value) {
        return props.setAttributes({
          cmButtons: value
        });
      }
    }), /*#__PURE__*/React.createElement(PanelColorSettings, {
      title: __('Button Colors', 'frontblocks'),
      colorSettings: [{
        value: cmButtonColor,
        onChange: function onChange(color) {
          return props.setAttributes({
            cmButtonColor: color
          });
        },
        label: __('Color button', 'frontblocks')
      }, {
        value: cmButtonBgColor,
        onChange: function onChange(color) {
          return props.setAttributes({
            cmButtonBgColor: color
          });
        },
        label: __('Color background button', 'frontblocks')
      }]
    }))));
  };
}
addFilter('editor.BlockEdit', 'cm/gb-grid-carousel-panel', addCustomCarouselPanel);
