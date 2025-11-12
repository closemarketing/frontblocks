"use strict";

function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
// TEST: Confirmar que el script se carga
console.log('🚀 FrontBlocks Edge Alignment Script LOADED!');
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
 * Check if container uses GeneratePress global max-width.
 *
 * @param {Object} attributes Block attributes.
 * @return {boolean} True if uses global max-width.
 */
function usesGlobalMaxWidth(attributes) {
  // Check if using global max width setting.
  // GenerateBlocks uses 'useGlobalMaxWidth' or checks if maxWidth is empty/not set
  // and innerContainer is enabled with global width.

  // Option 1: Has useGlobalMaxWidth attribute set to true.
  if (attributes.useGlobalMaxWidth === true) {
    return true;
  }

  // Option 2: Has isQueryLoopItem (uses global settings).
  if (attributes.isQueryLoopItem === true) {
    return true;
  }

  // Option 3: maxWidth is set to a CSS variable (--content-width, etc).
  if (attributes.maxWidth && typeof attributes.maxWidth === 'string') {
    if (attributes.maxWidth.includes('var(--') || attributes.maxWidth.includes('content-width') || attributes.maxWidth === 'var(--gp-site-width)' || attributes.maxWidth === 'var(--gp-page-width)') {
      return true;
    }
  }

  // Option 4: Check maxWidthUnit is 'global' or similar.
  if (attributes.maxWidthUnit === 'global' || attributes.maxWidthUnit === '%') {
    return true;
  }

  // Option 5: Inner container with inherit/global width.
  if (attributes.innerContainer === true && !attributes.maxWidth) {
    return true;
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

    // DEBUG: Ver atributos del contenedor
    console.log('=== FrontBlocks Edge Alignment DEBUG ===');
    console.log('Container attributes:', attributes);
    console.log('Uses global width?', usesGlobalMaxWidth(attributes));
    console.log('maxWidth:', attributes.maxWidth);
    console.log('maxWidthUnit:', attributes.maxWidthUnit);
    console.log('useGlobalMaxWidth:', attributes.useGlobalMaxWidth);
    console.log('innerContainer:', attributes.innerContainer);
    console.log('=======================================');

    // TEMPORAL: SIEMPRE MOSTRAR para debug
    // if (!usesGlobalMaxWidth(attributes)) {
    // 	return <BlockEdit {...props} />;
    // }

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
