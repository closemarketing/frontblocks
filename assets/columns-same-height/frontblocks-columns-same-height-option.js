"use strict";

var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl;
var __ = wp.i18n.__;
var COLUMNS_BLOCK = 'core/columns';

// Register same-height attribute server-side.
wp.hooks.addFilter('blocks.registerBlockType', 'frontblocks/add-same-height-attribute', function (settings, name) {
  if (COLUMNS_BLOCK !== name) {
    return settings;
  }
  settings.attributes = Object.assign(settings.attributes || {}, {
    frblSameHeight: {
      type: 'boolean',
      default: false
    }
  });
  return settings;
});
var withSameHeightControl = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    if (COLUMNS_BLOCK !== props.name) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var isSameHeight = !!attributes.frblSameHeight;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks - Layout', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Same height columns', 'frontblocks'),
      checked: isSameHeight,
      onChange: function onChange(value) {
        return setAttributes({
          frblSameHeight: value
        });
      },
      help: isSameHeight ? __('All columns stretch to match the tallest one.', 'frontblocks') : __('Enable to force all columns to the same height.', 'frontblocks')
    }))));
  };
}, 'withSameHeightControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/columns-same-height-control', withSameHeightControl);
