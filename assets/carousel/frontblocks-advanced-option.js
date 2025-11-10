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
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl;
var __ = wp.i18n.__;
function addCustomCarouselPanel(BlockEdit) {
  return function (props) {
    // Support both grid blocks and element blocks with grid display
    if (props.name !== 'generateblocks/grid' && props.name !== 'generateblocks/element') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }

    // For element blocks, only show carousel options if it has grid display
    if (props.name === 'generateblocks/element') {
      var styles = props.attributes.styles || {};
      if (styles.display !== 'grid') {
        return /*#__PURE__*/React.createElement(BlockEdit, props);
      }
    }
    var _props$attributes = props.attributes,
      _props$attributes$frb = _props$attributes.frblGridOption,
      frblGridOption = _props$attributes$frb === void 0 ? 'none' : _props$attributes$frb,
      _props$attributes$frb2 = _props$attributes.frblItemsToView,
      frblItemsToView = _props$attributes$frb2 === void 0 ? '4' : _props$attributes$frb2,
      _props$attributes$frb3 = _props$attributes.frblResponsiveToView,
      frblResponsiveToView = _props$attributes$frb3 === void 0 ? '1' : _props$attributes$frb3,
      _props$attributes$frb4 = _props$attributes.frblAutoplay,
      frblAutoplay = _props$attributes$frb4 === void 0 ? '' : _props$attributes$frb4,
      _props$attributes$frb5 = _props$attributes.frblButtons,
      frblButtons = _props$attributes$frb5 === void 0 ? 'arrows' : _props$attributes$frb5,
      _props$attributes$frb6 = _props$attributes.frblRewind,
      frblRewind = _props$attributes$frb6 === void 0 ? true : _props$attributes$frb6,
      frblButtonColor = _props$attributes.frblButtonColor,
      frblButtonBgColor = _props$attributes.frblButtonBgColor,
      _props$attributes$frb7 = _props$attributes.frblButtonsPosition,
      frblButtonsPosition = _props$attributes$frb7 === void 0 ? 'side' : _props$attributes$frb7,
      _props$attributes$frb8 = _props$attributes.frblDisableOnDesktop,
      frblDisableOnDesktop = _props$attributes$frb8 === void 0 ? false : _props$attributes$frb8;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Carousel Settings', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('FrontBlocks Grid Option', 'frontblocks'),
      value: frblGridOption,
      options: [{
        label: __('None', 'frontblocks'),
        value: 'none'
      }, {
        label: __('Carousel', 'frontblocks'),
        value: 'carousel'
      }, {
        label: __('Slider', 'frontblocks'),
        value: 'slider'
      }],
      onChange: function onChange(value) {
        props.setAttributes({
          frblGridOption: value
        });
      },
      help: __('This option gives the option to make carousel in your grid block.', 'frontblocks')
    }), frblGridOption !== 'none' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(TextControl, {
      label: __('Items to view', 'frontblocks'),
      value: frblItemsToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblItemsToView: value
        });
      }
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Responsive to view', 'frontblocks'),
      value: frblResponsiveToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblResponsiveToView: value
        });
      }
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Autoplay (seconds)', 'frontblocks'),
      value: frblAutoplay,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblAutoplay: value
        });
      }
    }), frblGridOption === 'slider' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Rewind', 'frontblocks'),
      checked: frblRewind,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblRewind: value
        });
      }
    })), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Buttons', 'frontblocks'),
      value: frblButtons,
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
          frblButtons: value
        });
      }
    }), frblButtons === 'arrows' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Buttons Position', 'frontblocks'),
      value: frblButtonsPosition,
      options: [{
        label: __('Side', 'frontblocks'),
        value: 'side'
      }, {
        label: __('Bottom', 'frontblocks'),
        value: 'bottom'
      }],
      onChange: function onChange(value) {
        return props.setAttributes({
          frblButtonsPosition: value
        });
      }
    })), /*#__PURE__*/React.createElement(PanelColorSettings, {
      title: __('Button Colors', 'frontblocks'),
      colorSettings: [{
        value: frblButtonColor,
        onChange: function onChange(color) {
          return props.setAttributes({
            frblButtonColor: color
          });
        },
        label: __('Color button', 'frontblocks')
      }, {
        value: frblButtonBgColor,
        onChange: function onChange(color) {
          return props.setAttributes({
            frblButtonBgColor: color
          });
        },
        label: __('Color background button', 'frontblocks')
      }]
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Disable on Desktop', 'frontblocks'),
      checked: frblDisableOnDesktop,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblDisableOnDesktop: value
        });
      },
      help: __('If enabled, carousel/slider will only work on mobile devices.', 'frontblocks')
    })))));
  };
}
addFilter('editor.BlockEdit', 'frontblocks/gb-grid-carousel-panel', addCustomCarouselPanel);
