"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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

// Add data attribute to the block wrapper in the editor for CSS targeting.
var withSameHeightWrapper = createHigherOrderComponent(function (BlockListBlock) {
  return function (props) {
    if (COLUMNS_BLOCK !== props.name || !props.attributes.frblSameHeight) {
      return /*#__PURE__*/React.createElement(BlockListBlock, props);
    }
    var wrapperProps = _objectSpread(_objectSpread({}, props.wrapperProps || {}), {}, {
      'data-frbl-same-height': 'true'
    });
    return /*#__PURE__*/React.createElement(BlockListBlock, _extends({}, props, {
      wrapperProps: wrapperProps
    }));
  };
}, 'withSameHeightWrapper');
wp.hooks.addFilter('editor.BlockListBlock', 'frontblocks/columns-same-height-wrapper', withSameHeightWrapper);

// Add the inspector toggle.
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
