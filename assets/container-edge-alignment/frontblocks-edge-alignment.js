"use strict";

function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
var addFilter = wp.hooks.addFilter;
var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  CheckboxControl = _wp$components.CheckboxControl;
var __ = wp.i18n.__;

/**
 * Add custom attributes to GenerateBlocks Container block.
 *
 * @param {Object} settings Block settings.
 * @param {string} name Block name.
 * @return {Object} Modified settings.
 */
function addEdgeAlignmentAttributes(settings, name) {
  // Support both old (container) and new (element) GenerateBlocks versions
  if (name !== 'generateblocks/container' && name !== 'generateblocks/element') {
    return settings;
  }
  settings.attributes = Object.assign(settings.attributes, {
    frblEdgeAlignmentLeft: {
      type: 'boolean',
      default: false
    },
    frblEdgeAlignmentRight: {
      type: 'boolean',
      default: false
    }
  });
  return settings;
}
addFilter('blocks.registerBlockType', 'frontblocks/edge-alignment-attributes', addEdgeAlignmentAttributes);

/**
 * Check if container uses GenerateBlocks global container width.
 * Only containers with var(--gb-container-width) should have edge alignment.
 *
 * @param {Object} attributes Block attributes.
 * @return {boolean} True if uses global container width.
 */
function usesGlobalMaxWidth(attributes) {
  // GenerateBlocks stores styles in an object (not array).
  // Check if maxWidth uses var(--gb-container-width) and margins are auto.
  if (!attributes.styles || _typeof(attributes.styles) !== 'object') {
    return false;
  }

  // Check if maxWidth contains the global container width variable.
  if (attributes.styles.maxWidth && attributes.styles.maxWidth.includes('var(--gb-container-width)')) {
    // Also verify it's centered (marginLeft and marginRight are auto).
    if (attributes.styles.marginLeft === 'auto' && attributes.styles.marginRight === 'auto') {
      return true;
    }
  }
  return false;
}

/**
 * Add edge alignment controls to GenerateBlocks Container block inspector.
 * ONLY shows if container uses GeneratePress global max-width.
 */
var withEdgeAlignmentControls = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    // Support both old (container) and new (element) GenerateBlocks versions
    if (props.name !== 'generateblocks/container' && props.name !== 'generateblocks/element') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var frblEdgeAlignmentLeft = attributes.frblEdgeAlignmentLeft,
      frblEdgeAlignmentRight = attributes.frblEdgeAlignmentRight;

    // Only show panel if container uses centered layout (margin auto).
    if (!usesGlobalMaxWidth(attributes)) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Edge Alignment', 'frontblocks'),
      initialOpen: false,
      className: "frbl-edge-alignment-panel"
    }, /*#__PURE__*/React.createElement("p", {
      className: "frbl-edge-alignment-description"
    }, __('Remove padding from one side to create an edge-to-edge effect. Perfect for asymmetric layouts where content extends to the browser edge on one side.', 'frontblocks')), /*#__PURE__*/React.createElement(CheckboxControl, {
      label: __('Remove Left Padding', 'frontblocks'),
      checked: frblEdgeAlignmentLeft,
      onChange: function onChange(value) {
        return setAttributes({
          frblEdgeAlignmentLeft: value
        });
      },
      help: __('Content will align to the left edge of the browser.', 'frontblocks')
    }), /*#__PURE__*/React.createElement(CheckboxControl, {
      label: __('Remove Right Padding', 'frontblocks'),
      checked: frblEdgeAlignmentRight,
      onChange: function onChange(value) {
        return setAttributes({
          frblEdgeAlignmentRight: value
        });
      },
      help: __('Content will align to the right edge of the browser.', 'frontblocks')
    }))));
  };
}, 'withEdgeAlignmentControls');
addFilter('editor.BlockEdit', 'frontblocks/edge-alignment-controls', withEdgeAlignmentControls);

/**
 * Add custom classes to block in editor for visual feedback.
 */
var addEdgeAlignmentClass = createHigherOrderComponent(function (BlockListBlock) {
  return function (props) {
    // Support both old (container) and new (element) GenerateBlocks versions
    if (props.name !== 'generateblocks/container' && props.name !== 'generateblocks/element') {
      return /*#__PURE__*/React.createElement(BlockListBlock, props);
    }
    var attributes = props.attributes;
    var frblEdgeAlignmentLeft = attributes.frblEdgeAlignmentLeft,
      frblEdgeAlignmentRight = attributes.frblEdgeAlignmentRight;
    var additionalClasses = '';
    if (frblEdgeAlignmentLeft) {
      additionalClasses += ' frbl-edge-left';
    }
    if (frblEdgeAlignmentRight) {
      additionalClasses += ' frbl-edge-right';
    }
    if (additionalClasses) {
      return /*#__PURE__*/React.createElement(BlockListBlock, _extends({}, props, {
        className: props.className + additionalClasses
      }));
    }
    return /*#__PURE__*/React.createElement(BlockListBlock, props);
  };
}, 'addEdgeAlignmentClass');
addFilter('editor.BlockListBlock', 'frontblocks/edge-alignment-class', addEdgeAlignmentClass);
