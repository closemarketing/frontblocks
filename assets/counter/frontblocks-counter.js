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
var COUNTER_BLOCKS = ['generateblocks/text', 'generateblocks/headline', 'core/heading', 'core/paragraph'];
wp.hooks.addFilter('blocks.registerBlockType', 'frontblocks/add-counter-attribute', function (settings, name) {
  if (!COUNTER_BLOCKS.includes(name)) {
    return settings;
  }
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
  return settings;
});
var withCounterControl = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    if (!COUNTER_BLOCKS.includes(props.name)) {
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
      if (isCounterActive && finalNumber) {
        var block = select('core/block-editor').getBlock(clientId);
        if (!block) return;
        var formattedNumber = "".concat(numberPrefix).concat(finalNumber).concat(numberSuffix);
        if (block.attributes.content !== formattedNumber) {
          dispatch('core/block-editor').updateBlockAttributes(clientId, {
            content: formattedNumber
          });
        }
      }
    }, [isCounterActive, finalNumber, numberPrefix, numberSuffix, clientId]);
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks - Counter Effect', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Activate Counter Effect', 'frontblocks'),
      help: isCounterActive ? __('The number will animate on scroll.', 'frontblocks') : __('Enable to activate counting animation.', 'frontblocks'),
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
      help: __('Number to count up to (e.g.: 100).', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Number Prefix', 'frontblocks'),
      value: numberPrefix,
      onChange: function onChange(val) {
        return setAttributes({
          numberPrefix: val
        });
      },
      help: __('Text before the number (e.g.: $, €).', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Number Suffix', 'frontblocks'),
      value: numberSuffix,
      onChange: function onChange(val) {
        return setAttributes({
          numberSuffix: val
        });
      },
      help: __('Text after the number (e.g.: %, +).', 'frontblocks')
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
      help: __('Duration of the animation.', 'frontblocks')
    })), /*#__PURE__*/React.createElement("p", {
      style: {
        marginTop: '10px',
        fontSize: '12px',
        color: '#777'
      }
    }, /*#__PURE__*/React.createElement("small", null, __("Text must begin with a number (e.g., '123', '+500', '€100').", 'frontblocks'))))));
  };
}, 'withCounterControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/counter-control', withCounterControl);
