"use strict";

var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl,
  TextControl = _wp$components.TextControl;
var BLOCK_NAME = 'generateblocks/text';
wp.hooks.addFilter('blocks.registerBlockType', 'frontblocks/add-counter-attribute', function (settings, name) {
  if (name === BLOCK_NAME) {
    settings.attributes = Object.assign(settings.attributes, {
      isCounterActive: {
        type: 'boolean',
        default: false
      },
      animationDuration: {
        type: 'number',
        default: 2000
      }
    });
  }
  return settings;
});
var withHeadlineCounterControl = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    if (props.name !== BLOCK_NAME) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var isCounterActive = attributes.isCounterActive,
      animationDuration = attributes.animationDuration;
    var durationInSeconds = animationDuration / 1000;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: "Frontblocks - Counter Effect",
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: "Activate Counter Effect",
      help: isCounterActive ? 'The number in the headline will animate on scroll.' : 'Enable to activate counting animation.',
      checked: isCounterActive,
      onChange: function onChange(val) {
        return setAttributes({
          isCounterActive: val
        });
      }
    }), isCounterActive && /*#__PURE__*/React.createElement(TextControl, {
      label: "Animation Duration (seconds)",
      value: durationInSeconds,
      onChange: function onChange(val) {
        var seconds = parseFloat(val);
        var milliseconds = isNaN(seconds) ? 2000 : seconds * 1000;
        setAttributes({
          animationDuration: milliseconds
        });
      },
      type: "number",
      min: "0.5",
      step: "0.1",
      help: "Time in seconds (e.g.: 2 seconds)."
    }), /*#__PURE__*/React.createElement("p", {
      style: {
        marginTop: '10px',
        fontSize: '12px',
        color: '#777'
      }
    }, /*#__PURE__*/React.createElement("small", null, "Make sure the text begins with a number (e.g., '123', '+500', or '\u20AC100').")))));
  };
}, 'withHeadlineCounterControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/headline-counter-control', withHeadlineCounterControl);
