"use strict";

// Add custom controls to the Advanced panel of GenerateBlocks Container block
var addFilter = wp.hooks.addFilter;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  PanelColorSettings = _wp$blockEditor.PanelColorSettings;
var _wp$components = wp.components,
  SelectControl = _wp$components.SelectControl,
  TextControl = _wp$components.TextControl,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl,
  RangeControl = _wp$components.RangeControl;
var __ = wp.i18n.__;
function addCustomFullpagePanel(BlockEdit) {
  return function (props) {
    if (props.name !== 'generateblocks/container') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var _props$attributes = props.attributes,
      _props$attributes$frb = _props$attributes.frblFullpageEnabled,
      frblFullpageEnabled = _props$attributes$frb === void 0 ? false : _props$attributes$frb,
      _props$attributes$frb2 = _props$attributes.frblShowNavigation,
      frblShowNavigation = _props$attributes$frb2 === void 0 ? true : _props$attributes$frb2,
      _props$attributes$frb3 = _props$attributes.frblShowScrollbar,
      frblShowScrollbar = _props$attributes$frb3 === void 0 ? true : _props$attributes$frb3,
      _props$attributes$frb4 = _props$attributes.frblNavigationPosition,
      frblNavigationPosition = _props$attributes$frb4 === void 0 ? 'right' : _props$attributes$frb4,
      _props$attributes$frb5 = _props$attributes.frblNavigationColor,
      frblNavigationColor = _props$attributes$frb5 === void 0 ? '#000' : _props$attributes$frb5,
      _props$attributes$frb6 = _props$attributes.frblAutoScroll,
      frblAutoScroll = _props$attributes$frb6 === void 0 ? false : _props$attributes$frb6,
      _props$attributes$frb7 = _props$attributes.frblScrollSpeed,
      frblScrollSpeed = _props$attributes$frb7 === void 0 ? 700 : _props$attributes$frb7,
      _props$attributes$frb8 = _props$attributes.frblLoopBottom,
      frblLoopBottom = _props$attributes$frb8 === void 0 ? false : _props$attributes$frb8,
      _props$attributes$frb9 = _props$attributes.frblLoopTop,
      frblLoopTop = _props$attributes$frb9 === void 0 ? false : _props$attributes$frb9,
      _props$attributes$frb0 = _props$attributes.frblScrolloverflow,
      frblScrolloverflow = _props$attributes$frb0 === void 0 ? false : _props$attributes$frb0;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FullPage Settings', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Enable FullPage', 'frontblocks'),
      checked: frblFullpageEnabled,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblFullpageEnabled: value
        });
      },
      help: __('Enable fullpage.js functionality for this container. Child elements will become vertical sections.', 'frontblocks')
    }), frblFullpageEnabled && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Navigation Options', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Show Navigation Dots', 'frontblocks'),
      checked: frblShowNavigation,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblShowNavigation: value
        });
      }
    }), frblShowNavigation && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Navigation Position', 'frontblocks'),
      value: frblNavigationPosition,
      options: [{
        label: __('Right', 'frontblocks'),
        value: 'right'
      }, {
        label: __('Left', 'frontblocks'),
        value: 'left'
      }],
      onChange: function onChange(value) {
        return props.setAttributes({
          frblNavigationPosition: value
        });
      }
    }), /*#__PURE__*/React.createElement(PanelColorSettings, {
      title: __('Navigation Colors', 'frontblocks'),
      colorSettings: [{
        value: frblNavigationColor,
        onChange: function onChange(color) {
          return props.setAttributes({
            frblNavigationColor: color
          });
        },
        label: __('Navigation Color', 'frontblocks')
      }]
    }))), /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Scrollbar Options', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Show Scrollbar', 'frontblocks'),
      checked: frblShowScrollbar,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblShowScrollbar: value
        });
      },
      help: __('Show scrollbar for scrolloverflow sections', 'frontblocks')
    })), /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Scroll Behavior', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Auto Scroll', 'frontblocks'),
      checked: frblAutoScroll,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblAutoScroll: value
        });
      },
      help: __('Automatically scroll through sections', 'frontblocks')
    }), /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Scroll Speed (ms)', 'frontblocks'),
      value: frblScrollSpeed,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblScrollSpeed: value
        });
      },
      min: 100,
      max: 2000,
      step: 100,
      help: __('Speed of scrolling animation in milliseconds', 'frontblocks')
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Loop to Bottom', 'frontblocks'),
      checked: frblLoopBottom,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblLoopBottom: value
        });
      },
      help: __('Loop from last section to first', 'frontblocks')
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Loop to Top', 'frontblocks'),
      checked: frblLoopTop,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblLoopTop: value
        });
      },
      help: __('Loop from first section to last', 'frontblocks')
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Enable Scroll Overflow', 'frontblocks'),
      checked: frblScrolloverflow,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblScrolloverflow: value
        });
      },
      help: __('Allow sections to scroll internally when content is larger than viewport', 'frontblocks')
    }))))));
  };
}
addFilter('editor.BlockEdit', 'frontblocks/gb-container-fullpage-panel', addCustomFullpagePanel);
