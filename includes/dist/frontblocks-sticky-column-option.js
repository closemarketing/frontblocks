"use strict";

(function () {
  'use strict';

  var __ = wp.i18n.__;
  var el = wp.element.createElement;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var _wp$components = wp.components,
    PanelBody = _wp$components.PanelBody,
    ToggleControl = _wp$components.ToggleControl,
    RangeControl = _wp$components.RangeControl,
    SelectControl = _wp$components.SelectControl;
  var addFilter = wp.hooks.addFilter;
  var select = wp.data.select;

  // Add sticky column controls to the GenerateBlocks Grid block
  addFilter('editor.BlockEdit', 'frontblocks/sticky-column-controls', function (BlockEdit) {
    return function (props) {
      // Only add controls to GenerateBlocks Grid block
      if (props.name !== 'generateblocks/grid') {
        return el(BlockEdit, props);
      }
      var attributes = props.attributes,
        setAttributes = props.setAttributes;
      var _attributes$frblStick = attributes.frblStickyEnabled,
        frblStickyEnabled = _attributes$frblStick === void 0 ? false : _attributes$frblStick,
        _attributes$frblStick2 = attributes.frblStickyOffset,
        frblStickyOffset = _attributes$frblStick2 === void 0 ? 0 : _attributes$frblStick2;
      var frblStickyColumnIndex = attributes.frblStickyColumnIndex || 0;

      // Get the current block's inner blocks to count columns
      var block = select('core/block-editor').getBlock(props.clientId);
      var columnCount = block ? block.innerBlocks.length : 0;

      // Create column options for the select control
      var columnOptions = [];
      for (var i = 0; i < columnCount; i++) {
        columnOptions.push({
          label: "Column ".concat(i + 1),
          value: i
        });
      }

      // If no columns found, provide a default option
      if (columnOptions.length === 0) {
        columnOptions.push({
          label: __('No columns found', 'frontblocks'),
          value: 0
        });
      }

      // Ensure the selected column index is valid
      if (frblStickyColumnIndex >= columnCount) {
        frblStickyColumnIndex = 0;
      }
      return el('div', {}, el(BlockEdit, props), el(InspectorControls, {}, el(PanelBody, {
        title: __('Sticky Column', 'frontblocks'),
        initialOpen: false
      }, el(ToggleControl, {
        label: __('Enable Sticky Column', 'frontblocks'),
        checked: frblStickyEnabled,
        onChange: function onChange(value) {
          return setAttributes({
            frblStickyEnabled: value
          });
        },
        help: __('Make a column stick to the top when scrolling', 'frontblocks')
      }), frblStickyEnabled && el('div', {}, el(RangeControl, {
        label: __('Sticky Offset (px)', 'frontblocks'),
        value: frblStickyOffset,
        onChange: function onChange(value) {
          return setAttributes({
            frblStickyOffset: value
          });
        },
        min: 0,
        max: 200,
        step: 1,
        help: __('Distance from top when sticky', 'frontblocks')
      }), el(SelectControl, {
        label: __('Sticky Column', 'frontblocks'),
        value: frblStickyColumnIndex,
        options: columnOptions,
        onChange: function onChange(value) {
          return setAttributes({
            frblStickyColumnIndex: parseInt(value)
          });
        },
        help: __('Select which column should be sticky', 'frontblocks')
      })))));
    };
  });
})();
