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
var __ = wp.i18n.__;
wp.hooks.addFilter('blocks.registerBlockType', 'frontblocks/add-counter-attribute', function (settings, name) {
  if (name === 'generateblocks/text' || name === 'generateblocks/headline') {
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
      title: __('Frontblocks - Counter Effect', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Activate Counter Effect', 'frontblocks'),
      help: isCounterActive ? __('The number in the headline will animate on scroll.', 'frontblocks') : __('Enable to activate counting animation.', 'frontblocks'),
      checked: isCounterActive,
      onChange: function onChange(val) {
        return setAttributes({
          isCounterActive: val
        });
      }
    }), isCounterActive && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(TextControl, {
      label: __('Final Number', 'frontblocks'),
      value: finalNumber,
      onChange: function onChange(val) {
        return setAttributes({
          finalNumber: val
        });
      },
      help: __('The number to count up to (e.g.: 100).', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Number Prefix', 'frontblocks'),
      value: numberPrefix,
      onChange: function onChange(val) {
        return setAttributes({
          numberPrefix: val
        });
      },
      help: __('Text to display before the number (e.g.: $, €).', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Number Suffix', 'frontblocks'),
      value: numberSuffix,
      onChange: function onChange(val) {
        return setAttributes({
          numberSuffix: val
        });
      },
      help: __('Text to display after the number (e.g.: %, +).', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Animation Duration (seconds)', 'frontblocks'),
      value: durationInSeconds,
      onChange: function onChange(val) {
        var seconds = parseFloat(val);
        var milliseconds = isNaN(seconds) ? 0 : seconds * 1000;
        setAttributes({
          animationDuration: milliseconds
        });
      },
      type: "number",
      step: "0.1",
      help: __('Time in seconds for the animation.', 'frontblocks')
    })), /*#__PURE__*/React.createElement("p", {
      style: {
        marginTop: '10px',
        fontSize: '12px',
        color: '#777'
      }
    }, /*#__PURE__*/React.createElement("small", null, __("Make sure the text begins with a number (e.g., '123', '+500', or '€100').", 'frontblocks'))))));
  };
}, 'withHeadlineCounterControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/headline-counter-control', withHeadlineCounterControl);
