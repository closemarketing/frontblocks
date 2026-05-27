"use strict";

var addFilter = wp.hooks.addFilter;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl;
var __ = wp.i18n.__;
var FAQ_BLOCKS = ['core/accordion'];

// Register frblFaqSchema attribute on supported blocks.
addFilter('blocks.registerBlockType', 'frontblocks/faq-schema-attribute', function (settings, name) {
  if (!FAQ_BLOCKS.includes(name)) {
    return settings;
  }
  settings.attributes = Object.assign({}, settings.attributes, {
    frblFaqSchema: {
      type: 'boolean',
      default: false
    }
  });
  return settings;
});

// Add inspector toggle to supported blocks.
addFilter('editor.BlockEdit', 'frontblocks/faq-schema-controls', function (BlockEdit) {
  return function (props) {
    if (!FAQ_BLOCKS.includes(props.name)) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var _props$attributes$frb = props.attributes.frblFaqSchema,
      frblFaqSchema = _props$attributes$frb === void 0 ? false : _props$attributes$frb;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FAQ Schema', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Add FAQ Schema (JSON-LD)', 'frontblocks'),
      help: __('Include this block\'s Q&A in the page FAQPage structured data.', 'frontblocks'),
      checked: frblFaqSchema,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblFaqSchema: value
        });
      }
    }))));
  };
});
