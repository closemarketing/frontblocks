"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var registerBlockType = wp.blocks.registerBlockType;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps,
  PanelColorSettings = _wp$blockEditor.PanelColorSettings;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl,
  TextControl = _wp$components.TextControl,
  TextareaControl = _wp$components.TextareaControl;
var __ = wp.i18n.__;
var TAG_OPTIONS = [{
  label: 'p',
  value: 'p'
}, {
  label: 'h1',
  value: 'h1'
}, {
  label: 'h2',
  value: 'h2'
}, {
  label: 'h3',
  value: 'h3'
}, {
  label: 'h4',
  value: 'h4'
}, {
  label: 'h5',
  value: 'h5'
}, {
  label: 'h6',
  value: 'h6'
}, {
  label: 'span',
  value: 'span'
}, {
  label: 'div',
  value: 'div'
}];
var FONT_WEIGHT_OPTIONS = [{
  label: __('Default', 'frontblocks'),
  value: ''
}, {
  label: __('Thin (100)', 'frontblocks'),
  value: '100'
}, {
  label: __('Extra Light (200)', 'frontblocks'),
  value: '200'
}, {
  label: __('Light (300)', 'frontblocks'),
  value: '300'
}, {
  label: __('Normal (400)', 'frontblocks'),
  value: '400'
}, {
  label: __('Medium (500)', 'frontblocks'),
  value: '500'
}, {
  label: __('Semi Bold (600)', 'frontblocks'),
  value: '600'
}, {
  label: __('Bold (700)', 'frontblocks'),
  value: '700'
}, {
  label: __('Extra Bold (800)', 'frontblocks'),
  value: '800'
}, {
  label: __('Black (900)', 'frontblocks'),
  value: '900'
}];
var TEXT_ALIGN_OPTIONS = [{
  label: __('Default', 'frontblocks'),
  value: ''
}, {
  label: __('Left', 'frontblocks'),
  value: 'left'
}, {
  label: __('Center', 'frontblocks'),
  value: 'center'
}, {
  label: __('Right', 'frontblocks'),
  value: 'right'
}, {
  label: __('Justify', 'frontblocks'),
  value: 'justify'
}];
registerBlockType('frontblocks/user-text', {
  title: __('User Text', 'frontblocks'),
  description: __('Text pattern with logged-in user data placeholders.', 'frontblocks'),
  category: 'text',
  icon: 'admin-users',
  supports: {
    html: false
  },
  attributes: {
    textPattern: {
      type: 'string',
      default: __('Hello, {nombre}!', 'frontblocks')
    },
    htmlTag: {
      type: 'string',
      default: 'p'
    },
    textColor: {
      type: 'string',
      default: ''
    },
    hoverTextColor: {
      type: 'string',
      default: ''
    },
    fontSize: {
      type: 'string',
      default: ''
    },
    fontFamily: {
      type: 'string',
      default: ''
    },
    fontWeight: {
      type: 'string',
      default: ''
    },
    textAlign: {
      type: 'string',
      default: ''
    },
    loggedOutText: {
      type: 'string',
      default: ''
    }
  },
  edit: function edit(_ref) {
    var attributes = _ref.attributes,
      setAttributes = _ref.setAttributes;
    var textPattern = attributes.textPattern,
      htmlTag = attributes.htmlTag,
      textColor = attributes.textColor,
      hoverTextColor = attributes.hoverTextColor,
      fontSize = attributes.fontSize,
      fontFamily = attributes.fontFamily,
      fontWeight = attributes.fontWeight,
      textAlign = attributes.textAlign,
      loggedOutText = attributes.loggedOutText;
    var inlineStyle = {
      color: textColor || undefined,
      fontSize: fontSize || undefined,
      fontFamily: fontFamily || undefined,
      fontWeight: fontWeight || undefined,
      textAlign: textAlign || undefined
    };
    var blockProps = useBlockProps({
      className: 'frbl-user-text-editor'
    });
    var Tag = htmlTag || 'p';
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Pattern & Data', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(TextareaControl, {
      label: __('Text Pattern', 'frontblocks'),
      value: textPattern,
      onChange: function onChange(val) {
        return setAttributes({
          textPattern: val
        });
      },
      rows: 4,
      help: __('Available placeholders: {nombre}, {apellido}, {display_name}, {email}, {username}, {bio}, {web}', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Logged-out Fallback', 'frontblocks'),
      value: loggedOutText,
      onChange: function onChange(val) {
        return setAttributes({
          loggedOutText: val
        });
      },
      help: __('Shown when no user is logged in. Leave empty to hide the block.', 'frontblocks')
    })), /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Typography', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('HTML Tag', 'frontblocks'),
      value: htmlTag,
      options: TAG_OPTIONS,
      onChange: function onChange(val) {
        return setAttributes({
          htmlTag: val
        });
      }
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Font Size', 'frontblocks'),
      value: fontSize,
      onChange: function onChange(val) {
        return setAttributes({
          fontSize: val
        });
      },
      placeholder: "16px, 1.5rem, 2em\u2026",
      help: __('Any valid CSS size value.', 'frontblocks')
    }), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Font Weight', 'frontblocks'),
      value: fontWeight,
      options: FONT_WEIGHT_OPTIONS,
      onChange: function onChange(val) {
        return setAttributes({
          fontWeight: val
        });
      }
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Font Family', 'frontblocks'),
      value: fontFamily,
      onChange: function onChange(val) {
        return setAttributes({
          fontFamily: val
        });
      },
      placeholder: "Inter, sans-serif\u2026"
    }), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Text Align', 'frontblocks'),
      value: textAlign,
      options: TEXT_ALIGN_OPTIONS,
      onChange: function onChange(val) {
        return setAttributes({
          textAlign: val
        });
      }
    })), /*#__PURE__*/React.createElement(PanelColorSettings, {
      title: __('Color', 'frontblocks'),
      initialOpen: false,
      colorSettings: [{
        value: textColor,
        onChange: function onChange(val) {
          return setAttributes({
            textColor: val || ''
          });
        },
        label: __('Text Color', 'frontblocks')
      }, {
        value: hoverTextColor,
        onChange: function onChange(val) {
          return setAttributes({
            hoverTextColor: val || ''
          });
        },
        label: __('Text Color on Hover', 'frontblocks')
      }]
    })), /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement(Tag, {
      style: _objectSpread(_objectSpread({}, inlineStyle), {}, {
        margin: 0
      })
    }, textPattern || __('Enter a text pattern in the sidebar…', 'frontblocks')), /*#__PURE__*/React.createElement("span", {
      className: "frbl-user-text-hint"
    }, __('Placeholders replaced with user data on frontend.', 'frontblocks'))));
  },
  save: function save() {
    return null;
  }
});
