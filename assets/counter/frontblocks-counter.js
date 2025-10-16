"use strict";

var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useEffect = _wp$element.useEffect;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl,
  TextControl = _wp$components.TextControl;
var _wp$data = wp.data,
  select = _wp$data.select,
  dispatch = _wp$data.dispatch;
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
      },
      finalNumber: {
        type: 'string',
        default: ''
      },
      numberPrefix: {
        type: 'string',
        default: ''
      },
      numberSuffix: {
        type: 'string',
        default: ''
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
      setAttributes = props.setAttributes,
      clientId = props.clientId;
    var isCounterActive = attributes.isCounterActive,
      animationDuration = attributes.animationDuration,
      finalNumber = attributes.finalNumber,
      numberPrefix = attributes.numberPrefix,
      numberSuffix = attributes.numberSuffix;
    var durationInSeconds = animationDuration / 1000;
    useEffect(function () {
      if (isCounterActive) {
        var block = select('core/block-editor').getBlock(clientId);
        if (!block) return;
        var formattedNumber = "".concat(numberPrefix).concat(finalNumber).concat(numberSuffix);
        if (finalNumber && block.attributes.content !== formattedNumber) {
          dispatch('core/block-editor').updateBlockAttributes(clientId, {
            content: formattedNumber
          });
        }
      }
    }, [isCounterActive, finalNumber, numberPrefix, numberSuffix, clientId]);
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
    }), isCounterActive && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(TextControl, {
      label: "Final Number",
      value: finalNumber,
      onChange: function onChange(val) {
        return setAttributes({
          finalNumber: val
        });
      },
      help: "The number to count up to (e.g.: 100)."
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: "Number Prefix",
      value: numberPrefix,
      onChange: function onChange(val) {
        return setAttributes({
          numberPrefix: val
        });
      },
      help: "Text to display before the number (e.g.: $, \u20AC)."
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: "Number Suffix",
      value: numberSuffix,
      onChange: function onChange(val) {
        return setAttributes({
          numberSuffix: val
        });
      },
      help: "Text to display after the number (e.g.: %, +)."
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: "Animation Duration (seconds)",
      value: durationInSeconds,
      onChange: function onChange(val) {
        var seconds = parseFloat(val);
        // Convert to milliseconds without default value
        var milliseconds = isNaN(seconds) ? 0 : seconds * 1000;
        setAttributes({
          animationDuration: milliseconds
        });
      },
      type: "number",
      step: "0.1",
      help: "Time in seconds for the animation."
    })), /*#__PURE__*/React.createElement("p", {
      style: {
        marginTop: '10px',
        fontSize: '12px',
        color: '#777'
      }
    }, /*#__PURE__*/React.createElement("small", null, "Make sure the text begins with a number (e.g., '123', '+500', or '\u20AC100').")))));
  };
}, 'withHeadlineCounterControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/headline-counter-control', withHeadlineCounterControl);
