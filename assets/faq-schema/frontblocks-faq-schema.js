"use strict";

var addFilter = wp.hooks.addFilter;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl,
  SelectControl = _wp$components.SelectControl;
var __ = wp.i18n.__;
var FAQ_BLOCKS = ['core/accordion', 'generateblocks/container'];
var SCHEMA_TYPES = [{
  label: __('FAQPage (questions & answers)', 'frontblocks'),
  value: 'FAQPage'
}, {
  label: __('HowTo (steps / how-to guide)', 'frontblocks'),
  value: 'HowTo'
}];

// Register attributes on supported blocks.
addFilter('blocks.registerBlockType', 'frontblocks/faq-schema-attribute', function (settings, name) {
  if (!FAQ_BLOCKS.includes(name)) {
    return settings;
  }
  settings.attributes = Object.assign({}, settings.attributes, {
    frblFaqSchema: {
      type: 'boolean',
      default: false
    },
    frblSchemaType: {
      type: 'string',
      default: 'FAQPage'
    }
  });
  return settings;
});

// Add inspector controls to supported blocks.
addFilter('editor.BlockEdit', 'frontblocks/faq-schema-controls', function (BlockEdit) {
  return function (props) {
    if (!FAQ_BLOCKS.includes(props.name)) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }

    // For generateblocks/container, only show on the accordion variant role.
    if (props.name === 'generateblocks/container' && props.attributes.variantRole !== 'accordion') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var _props$attributes = props.attributes,
      _props$attributes$frb = _props$attributes.frblFaqSchema,
      frblFaqSchema = _props$attributes$frb === void 0 ? false : _props$attributes$frb,
      _props$attributes$frb2 = _props$attributes.frblSchemaType,
      frblSchemaType = _props$attributes$frb2 === void 0 ? 'FAQPage' : _props$attributes$frb2;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks - Schema', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Add Schema (JSON-LD)', 'frontblocks'),
      help: __('Include this block\'s content in the page structured data.', 'frontblocks'),
      checked: frblFaqSchema,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblFaqSchema: value
        });
      }
    }), frblFaqSchema && /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Schema type', 'frontblocks'),
      value: frblSchemaType,
      options: SCHEMA_TYPES,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblSchemaType: value
        });
      }
    }))));
  };
});
