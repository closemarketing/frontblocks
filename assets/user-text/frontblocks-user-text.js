"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
var registerBlockType = wp.blocks.registerBlockType;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useState = _wp$element.useState,
  useEffect = _wp$element.useEffect;
var useSelect = wp.data.useSelect;
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
var HEADING_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
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

/**
 * Resolve a CSS var like "var(--wp--preset--font-size--large)" to a real value
 * using computed styles on the document root.
 */
function resolveCssVar(value) {
  if (!value || !value.startsWith('var(')) return value;
  var match = value.match(/var\(\s*(--[^,)]+)/);
  if (!match) return value;
  var resolved = getComputedStyle(document.documentElement).getPropertyValue(match[1]).trim();
  return resolved || value;
}
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

    // ── Current user (with email, needs context=edit) ──────────────────
    var _useState = useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      currentUser = _useState2[0],
      setCurrentUser = _useState2[1];
    useEffect(function () {
      wp.apiFetch({
        path: '/wp/v2/users/me?context=edit'
      }).then(function (u) {
        return setCurrentUser(u);
      }).catch(function () {});
    }, []);

    // ── Theme global styles ─────────────────────────────────────────────
    var _useState3 = useState({}),
      _useState4 = _slicedToArray(_useState3, 2),
      themeTypo = _useState4[0],
      setThemeTypo = _useState4[1];
    var themeSlug = useSelect(function (select) {
      var _select$getCurrentThe;
      return (_select$getCurrentThe = select('core').getCurrentTheme()) === null || _select$getCurrentThe === void 0 ? void 0 : _select$getCurrentThe.stylesheet;
    });
    useEffect(function () {
      if (!themeSlug) return;
      wp.apiFetch({
        path: '/wp/v2/global-styles/themes/' + themeSlug
      }).then(function (data) {
        var elements = data && data.styles && data.styles.elements ? data.styles.elements : {};
        // Merge heading base + specific tag (specific wins)
        var base = HEADING_TAGS.includes(htmlTag) ? elements.heading && elements.heading.typography ? elements.heading.typography : {} : {};
        var tagTypo = elements[htmlTag] && elements[htmlTag].typography ? elements[htmlTag].typography : {};
        var merged = Object.assign({}, base, tagTypo);
        // Resolve CSS vars to real values for display
        var resolved = {};
        Object.keys(merged).forEach(function (k) {
          resolved[k] = resolveCssVar(merged[k]);
        });
        setThemeTypo(resolved);
      }).catch(function () {});
    }, [themeSlug, htmlTag]);

    // ── Preview: replace placeholders with real user data ───────────────
    var previewText = function previewText(pattern) {
      if (!currentUser) return pattern;
      var nombre = currentUser.first_name || currentUser.name || '';
      var map = {
        '{nombre}': nombre,
        '{apellido}': currentUser.last_name || '',
        '{display_name}': currentUser.name || '',
        '{email}': currentUser.email || '',
        '{username}': currentUser.slug || '',
        '{bio}': currentUser.description || '',
        '{web}': currentUser.url || ''
      };
      return Object.entries(map).reduce(function (str, _ref2) {
        var _ref3 = _slicedToArray(_ref2, 2),
          key = _ref3[0],
          val = _ref3[1];
        return str.split(key).join(val);
      }, pattern);
    };

    // ── Inline styles: only explicit overrides ──────────────────────────
    var inlineStyle = {
      color: textColor || undefined,
      fontSize: fontSize || undefined,
      fontFamily: fontFamily || undefined,
      fontWeight: fontWeight || undefined,
      textAlign: textAlign || undefined
    };
    var blockProps = useBlockProps();
    var Tag = htmlTag || 'p';

    // Helper: placeholder showing theme default when field is empty
    var themePlaceholder = function themePlaceholder(key, fallback) {
      if (!themeTypo[key]) return fallback;
      return themeTypo[key] + ' (' + __('tema', 'frontblocks') + ')';
    };

    // For SelectControl (fontWeight): inject theme default into label
    var weightOptions = FONT_WEIGHT_OPTIONS.map(function (opt) {
      if (opt.value !== '') return opt;
      var tw = themeTypo.fontWeight;
      return _objectSpread(_objectSpread({}, opt), {}, {
        label: tw ? __('Default', 'frontblocks') + ' — ' + tw : __('Default', 'frontblocks')
      });
    });
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
      help: __('Placeholders: {nombre}, {apellido}, {display_name}, {email}, {username}, {bio}, {web}', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Logged-out Fallback', 'frontblocks'),
      value: loggedOutText,
      onChange: function onChange(val) {
        return setAttributes({
          loggedOutText: val
        });
      },
      help: __('Shown when no user is logged in. Leave empty to hide.', 'frontblocks')
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
      placeholder: themePlaceholder('fontSize', '16px, 1.5rem…'),
      help: fontSize ? __('Custom override active. Clear to use theme default.', 'frontblocks') : __('Empty = theme default.', 'frontblocks')
    }), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Font Weight', 'frontblocks'),
      value: fontWeight,
      options: weightOptions,
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
      placeholder: themePlaceholder('fontFamily', 'Inter, sans-serif…'),
      help: fontFamily ? __('Custom override active. Clear to use theme default.', 'frontblocks') : __('Empty = theme default.', 'frontblocks')
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
    })), /*#__PURE__*/React.createElement(Tag, _extends({}, blockProps, {
      style: _objectSpread(_objectSpread({}, inlineStyle), {}, {
        margin: 0
      })
    }), previewText(textPattern) || __('Enter a text pattern in the sidebar…', 'frontblocks')));
  },
  save: function save() {
    return null;
  }
});
