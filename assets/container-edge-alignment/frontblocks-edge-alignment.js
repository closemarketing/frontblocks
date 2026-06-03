"use strict";

function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
var addFilter = wp.hooks.addFilter;
var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl;
var __ = wp.i18n.__;
var GB_BLOCKS = ['generateblocks/container', 'generateblocks/element'];
var NATIVE_BLOCKS = ['core/group', 'core/columns'];
var ALL_SUPPORTED_BLOCKS = [].concat(GB_BLOCKS, NATIVE_BLOCKS);

/**
 * Add custom attributes to supported blocks.
 */
function addEdgeAlignmentAttributes(settings, name) {
  if (!ALL_SUPPORTED_BLOCKS.includes(name)) {
    return settings;
  }
  settings.attributes = Object.assign(settings.attributes, {
    frblEdgeAlignment: {
      type: 'string',
      default: ''
    }
  });
  return settings;
}
addFilter('blocks.registerBlockType', 'frontblocks/edge-alignment-attributes', addEdgeAlignmentAttributes);

/**
 * Check if a GenerateBlocks container uses the global container width.
 * Only containers with var(--gb-container-width) should have edge alignment.
 */
function usesGlobalMaxWidth(attributes) {
  if (!attributes.styles || _typeof(attributes.styles) !== 'object') {
    return false;
  }
  if (attributes.styles.maxWidth && attributes.styles.maxWidth.includes('var(--gb-container-width)')) {
    if (attributes.styles.marginLeft === 'auto' && attributes.styles.marginRight === 'auto') {
      return true;
    }
  }
  return false;
}

/**
 * Check if a native block uses a constrained (centered, max-width) layout.
 * Matches core/group and core/columns with constrained layout or inherited layout.
 */
function usesConstrainedLayout(attributes) {
  // No explicit layout — inherits from theme, which is typically constrained.
  if (!attributes.layout) {
    return true;
  }
  return attributes.layout.type === 'constrained';
}

/**
 * Add edge alignment controls to supported block inspector panels.
 */
var withEdgeAlignmentControls = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    if (!ALL_SUPPORTED_BLOCKS.includes(props.name)) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var frblEdgeAlignment = attributes.frblEdgeAlignment;
    var isGBBlock = GB_BLOCKS.includes(props.name);

    // Guard: only show panel when the block uses a centered/constrained layout.
    var shouldShowPanel = isGBBlock ? usesGlobalMaxWidth(attributes) : usesConstrainedLayout(attributes);
    if (!shouldShowPanel) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks Edge Alignment', 'frontblocks'),
      initialOpen: false,
      className: "frbl-edge-alignment-panel"
    }, /*#__PURE__*/React.createElement("p", {
      className: "frbl-edge-alignment-description"
    }, __('Remove padding from one side to create an edge-to-edge effect. Perfect for asymmetric layouts where content extends to the browser edge on one side.', 'frontblocks')), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Align to Edge', 'frontblocks'),
      value: frblEdgeAlignment,
      options: [{
        label: __('None', 'frontblocks'),
        value: ''
      }, {
        label: __('Remove Left Padding', 'frontblocks'),
        value: 'left'
      }, {
        label: __('Remove Right Padding', 'frontblocks'),
        value: 'right'
      }],
      onChange: function onChange(value) {
        return setAttributes({
          frblEdgeAlignment: value
        });
      },
      help: __('Choose which side should extend to the browser edge.', 'frontblocks')
    }))));
  };
}, 'withEdgeAlignmentControls');
addFilter('editor.BlockEdit', 'frontblocks/edge-alignment-controls', withEdgeAlignmentControls);

/**
 * Add visual feedback classes in the editor.
 */
var addEdgeAlignmentClass = createHigherOrderComponent(function (BlockListBlock) {
  return function (props) {
    if (!ALL_SUPPORTED_BLOCKS.includes(props.name)) {
      return /*#__PURE__*/React.createElement(BlockListBlock, props);
    }
    var attributes = props.attributes;
    var frblEdgeAlignment = attributes.frblEdgeAlignment;
    var additionalClasses = '';
    if (frblEdgeAlignment === 'left') {
      additionalClasses = ' frbl-edge-left';
    } else if (frblEdgeAlignment === 'right') {
      additionalClasses = ' frbl-edge-right';
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
