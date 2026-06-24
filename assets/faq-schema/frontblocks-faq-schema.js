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
      return wp.element.createElement(BlockEdit, props);
    }
    if (props.name === 'generateblocks/container' && props.attributes.variantRole !== 'accordion') {
      return wp.element.createElement(BlockEdit, props);
    }
    var _props$attributes = props.attributes,
      frblFaqSchema = _props$attributes.frblFaqSchema === undefined ? false : _props$attributes.frblFaqSchema,
      frblSchemaType = _props$attributes.frblSchemaType === undefined ? 'FAQPage' : _props$attributes.frblSchemaType;
    return wp.element.createElement(
      Fragment,
      null,
      wp.element.createElement(BlockEdit, props),
      wp.element.createElement(
        InspectorControls,
        null,
        wp.element.createElement(
          PanelBody,
          {
            title: __('FrontBlocks - Schema', 'frontblocks'),
            initialOpen: false
          },
          wp.element.createElement(ToggleControl, {
            label: __('Add Schema (JSON-LD)', 'frontblocks'),
            help: __('Include this block\'s content in the page structured data.', 'frontblocks'),
            checked: frblFaqSchema,
            onChange: function (value) {
              return props.setAttributes({
                frblFaqSchema: value
              });
            }
          }),
          frblFaqSchema && wp.element.createElement(SelectControl, {
            label: __('Schema type', 'frontblocks'),
            value: frblSchemaType,
            options: SCHEMA_TYPES,
            onChange: function (value) {
              return props.setAttributes({
                frblSchemaType: value
              });
            }
          })
        )
      )
    );
  };
});
