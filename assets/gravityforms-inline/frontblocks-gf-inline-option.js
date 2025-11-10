"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
(function () {
  'use strict';

  var __ = wp.i18n.__;
  var el = wp.element.createElement;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var _wp$components = wp.components,
    PanelBody = _wp$components.PanelBody,
    ToggleControl = _wp$components.ToggleControl,
    RangeControl = _wp$components.RangeControl;
  var addFilter = wp.hooks.addFilter;
  var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
  var _wp = wp,
    apiFetch = _wp.apiFetch;

  // Intercept Gravity Forms API requests and remove custom attributes.
  if (apiFetch && apiFetch.use) {
    apiFetch.use(function (options, next) {
      if (options.path && options.path.includes('/wp/v2/block-renderer/gravityforms/form')) {
        // Remove our custom attributes from the URL.
        options.path = options.path.replace(/&attributes%5BfrblGfInlineEnabled%5D=[^&]*/g, '').replace(/&attributes%5BfrblGfInlineGap%5D=[^&]*/g, '').replace(/attributes%5BfrblGfInlineEnabled%5D=[^&]*&/g, '').replace(/attributes%5BfrblGfInlineGap%5D=[^&]*&/g, '');
      }
      return next(options);
    });
  }

  // Add inline layout controls to the Gravity Forms block.
  addFilter('editor.BlockEdit', 'frontblocks/gf-inline-controls', function (BlockEdit) {
    return function (props) {
      // Only add controls to Gravity Forms block.
      if (props.name !== 'gravityforms/form') {
        return el(BlockEdit, props);
      }

      // Check if a form is selected.
      var attributes = props.attributes,
        setAttributes = props.setAttributes;
      var _attributes$frblGfInl = attributes.frblGfInlineEnabled,
        frblGfInlineEnabled = _attributes$frblGfInl === void 0 ? false : _attributes$frblGfInl,
        _attributes$frblGfInl2 = attributes.frblGfInlineGap,
        frblGfInlineGap = _attributes$frblGfInl2 === void 0 ? 10 : _attributes$frblGfInl2,
        _attributes$formId = attributes.formId,
        formId = _attributes$formId === void 0 ? 0 : _attributes$formId;

      // Only show options if a form is selected.
      var showInlineOptions = formId && formId > 0;
      return el('div', {}, el(BlockEdit, props), showInlineOptions && el(InspectorControls, {}, el(PanelBody, {
        title: __('FrontBlocks From GravityForms', 'frontblocks'),
        initialOpen: false
      }, el(ToggleControl, {
        label: __('Enable Inline Layout', 'frontblocks'),
        checked: frblGfInlineEnabled,
        onChange: function onChange(value) {
          return setAttributes({
            frblGfInlineEnabled: value
          });
        },
        help: __('Display form fields and button on the same line', 'frontblocks')
      }), frblGfInlineEnabled && el(RangeControl, {
        label: __('Gap between elements (px)', 'frontblocks'),
        value: frblGfInlineGap,
        onChange: function onChange(value) {
          return setAttributes({
            frblGfInlineGap: value
          });
        },
        min: 0,
        max: 50,
        step: 1,
        help: __('Space between form fields and button', 'frontblocks')
      }))));
    };
  });

  // Add custom class to block wrapper in editor.
  addFilter('editor.BlockListBlock', 'frontblocks/gf-inline-wrapper-class', createHigherOrderComponent(function (BlockListBlock) {
    return function (props) {
      if (props.name === 'gravityforms/form') {
        var attributes = props.attributes;
        if (attributes.frblGfInlineEnabled) {
          // Apply gap CSS variable to editor preview.
          if (typeof window !== 'undefined') {
            setTimeout(function () {
              var blockElement = document.querySelector('[data-block="' + props.clientId + '"]');
              if (blockElement) {
                blockElement.style.setProperty('--gf-inline-gap', (attributes.frblGfInlineGap || 10) + 'px');
              }
            }, 100);
          }
          return el(BlockListBlock, _objectSpread(_objectSpread({}, props), {}, {
            className: 'frontblocks-gf-inline-preview'
          }));
        }
      }
      return el(BlockListBlock, props);
    };
  }, 'withInlineClass'));
})();
