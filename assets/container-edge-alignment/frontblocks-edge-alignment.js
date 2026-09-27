function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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
      return /*#__PURE__*/React.createElement(BlockListBlock, _objectSpread(_objectSpread({}, props), {}, {
        className: props.className + additionalClasses
      }));
    }
    return /*#__PURE__*/React.createElement(BlockListBlock, props);
  };
}, 'addEdgeAlignmentClass');
addFilter('editor.BlockListBlock', 'frontblocks/edge-alignment-class', addEdgeAlignmentClass);
