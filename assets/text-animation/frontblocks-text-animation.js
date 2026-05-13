"use strict";

function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
var registerBlockType = wp.blocks.registerBlockType;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useState = _wp$element.useState;
var useSelect = wp.data.useSelect;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps,
  RichText = _wp$blockEditor.RichText,
  PanelColorSettings = _wp$blockEditor.PanelColorSettings;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl,
  RangeControl = _wp$components.RangeControl,
  NumberControl = _wp$components.__experimentalNumberControl,
  ToggleGroupControl = _wp$components.__experimentalToggleGroupControl,
  ToggleGroupControlOptionIcon = _wp$components.__experimentalToggleGroupControlOptionIcon;
var __ = wp.i18n.__;
var FONT_SIZE_UNITS = [{
  label: 'px',
  value: 'px'
}, {
  label: 'rem',
  value: 'rem'
}, {
  label: 'em',
  value: 'em'
}, {
  label: 'vw',
  value: 'vw'
}];
var TAG_OPTIONS = [{
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
  label: 'p',
  value: 'p'
}, {
  label: 'span (inline)',
  value: 'span'
}];
var FONT_WEIGHT_OPTIONS = [{
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
var ALIGN_LEFT_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("path", {
  d: "M13 15H3v2h10v-2zm0-8H3v2h10V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"
}));
var ALIGN_CENTER_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("path", {
  d: "M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"
}));
var ALIGN_RIGHT_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("path", {
  d: "M3 21h18v-2H3v2zm8-4h10v-2H11v2zm-8-4h18v-2H3v2zm8-4h10V7H11v2zM3 3v2h18V3H3z"
}));
var ALIGN_JUSTIFY_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("path", {
  d: "M3 21h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18V7H3v2zm0-6v2h18V3H3z"
}));
var TRANSFORM_NONE_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("text", {
  x: "4",
  y: "17",
  fontFamily: "serif",
  fontSize: "14",
  fontWeight: "bold",
  fill: "currentColor"
}, "Ag"), /*#__PURE__*/React.createElement("line", {
  x1: "3",
  y1: "21",
  x2: "21",
  y2: "3",
  stroke: "currentColor",
  strokeWidth: "1.5"
}));
var TRANSFORM_UPPERCASE_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("text", {
  x: "2",
  y: "17",
  fontFamily: "serif",
  fontSize: "15",
  fontWeight: "bold",
  fill: "currentColor"
}, "AA"));
var TRANSFORM_LOWERCASE_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("text", {
  x: "2",
  y: "17",
  fontFamily: "serif",
  fontSize: "15",
  fill: "currentColor"
}, "aa"));
var TRANSFORM_CAPITALIZE_ICON = /*#__PURE__*/React.createElement("svg", {
  xmlns: "http://www.w3.org/2000/svg",
  width: "20",
  height: "20",
  viewBox: "0 0 24 24",
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("text", {
  x: "2",
  y: "17",
  fontFamily: "serif",
  fontSize: "15",
  fill: "currentColor"
}, "Aa"));
var FONT_STYLE_OPTIONS = [{
  label: __('Normal', 'frontblocks'),
  value: 'normal'
}, {
  label: __('Italic', 'frontblocks'),
  value: 'italic'
}];
function TextAnimationEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var _useState = useState(false),
    _useState2 = _slicedToArray(_useState, 2),
    isHovered = _useState2[0],
    setIsHovered = _useState2[1];
  var content = attributes.content,
    htmlTag = attributes.htmlTag,
    fontSize = attributes.fontSize,
    fontSizeUnit = attributes.fontSizeUnit,
    fontFamily = attributes.fontFamily,
    fontWeight = attributes.fontWeight,
    fontStyle = attributes.fontStyle,
    lineHeight = attributes.lineHeight,
    letterSpacing = attributes.letterSpacing,
    textAlign = attributes.textAlign,
    textTransform = attributes.textTransform,
    textColorCustom = attributes.textColorCustom,
    textColorHover = attributes.textColorHover,
    width = attributes.width,
    widthUnit = attributes.widthUnit,
    height = attributes.height,
    heightUnit = attributes.heightUnit;
  var fontFamilyOptions = useSelect(function (select) {
    try {
      var settings = select('core').getSettings();
      var families = settings.fontFamilies || {};
      var all = [].concat(_toConsumableArray(families.theme || []), _toConsumableArray(families.custom || []), _toConsumableArray(families.default || []));
      var options = [];
      all.forEach(function (f) {
        if (f && f.name && f.fontFamily) {
          options.push({
            label: f.name,
            value: f.fontFamily
          });
        }
      });
      return options;
    } catch (e) {
      return [];
    }
  }, []);
  var blockProps = useBlockProps({
    className: 'frbl-text-animation',
    style: {
      fontSize: fontSize ? "".concat(fontSize).concat(fontSizeUnit || 'px') : undefined,
      fontFamily: fontFamily || undefined,
      fontWeight: fontWeight || undefined,
      fontStyle: fontStyle !== 'normal' ? fontStyle : undefined,
      lineHeight: lineHeight || undefined,
      letterSpacing: letterSpacing ? "".concat(letterSpacing, "em") : undefined,
      textAlign: textAlign !== 'left' ? textAlign : undefined,
      textTransform: textTransform !== 'none' ? textTransform : undefined,
      '--frbl-color': textColorCustom || undefined,
      '--frbl-color-hover': textColorHover || undefined,
      color: isHovered && textColorHover ? textColorHover : textColorCustom || undefined,
      transition: 'color 0.3s ease',
      width: width ? "".concat(width).concat(widthUnit || 'px') : undefined,
      height: height ? "".concat(height).concat(heightUnit || 'px') : undefined,
      marginLeft: 0,
      marginRight: 'auto'
    },
    onMouseEnter: function onMouseEnter() {
      return setIsHovered(true);
    },
    onMouseLeave: function onMouseLeave() {
      return setIsHovered(false);
    }
  });
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Typography', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: __('HTML Tag', 'frontblocks'),
    value: htmlTag,
    options: TAG_OPTIONS,
    onChange: function onChange(value) {
      return setAttributes({
        htmlTag: value
      });
    }
  }), fontFamilyOptions.length > 0 && /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Font Family', 'frontblocks'),
    value: fontFamily,
    options: [{
      label: __('Default', 'frontblocks'),
      value: ''
    }].concat(_toConsumableArray(fontFamilyOptions)),
    onChange: function onChange(value) {
      return setAttributes({
        fontFamily: value
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: '16px'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      marginTop: 0,
      marginBottom: '8px',
      fontSize: '11px',
      fontWeight: '500',
      textTransform: 'uppercase',
      color: 'rgb(117, 117, 117)'
    }
  }, __('Font Size', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: '8px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      marginBottom: 0
    }
  }, /*#__PURE__*/React.createElement(NumberControl, {
    value: fontSize || '',
    onChange: function onChange(value) {
      return setAttributes({
        fontSize: value ? parseFloat(value) : undefined
      });
    },
    min: 1,
    step: 1,
    spinControls: "native",
    hideLabelFromVision: true,
    label: __('Font Size', 'frontblocks')
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      width: '80px',
      flexShrink: 0,
      marginBottom: 0
    }
  }, /*#__PURE__*/React.createElement(SelectControl, {
    value: fontSizeUnit,
    options: FONT_SIZE_UNITS,
    onChange: function onChange(value) {
      return setAttributes({
        fontSizeUnit: value
      });
    },
    hideLabelFromVision: true,
    label: __('Unit', 'frontblocks'),
    __nextHasNoMarginBottom: true
  })))), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Font Weight', 'frontblocks'),
    value: fontWeight,
    options: [{
      label: __('Default', 'frontblocks'),
      value: ''
    }].concat(FONT_WEIGHT_OPTIONS),
    onChange: function onChange(value) {
      return setAttributes({
        fontWeight: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Font Style', 'frontblocks'),
    value: fontStyle,
    options: FONT_STYLE_OPTIONS,
    onChange: function onChange(value) {
      return setAttributes({
        fontStyle: value
      });
    }
  }), /*#__PURE__*/React.createElement(ToggleGroupControl, {
    label: __('Text Align', 'frontblocks'),
    value: textAlign,
    onChange: function onChange(value) {
      var newAlign = value || 'left';
      var updates = {
        textAlign: newAlign
      };
      if (newAlign !== 'left') {
        updates.width = 100;
        updates.widthUnit = '%';
      }
      setAttributes(updates);
    },
    isBlock: true
  }, /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "left",
    icon: ALIGN_LEFT_ICON,
    label: __('Left', 'frontblocks')
  }), /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "center",
    icon: ALIGN_CENTER_ICON,
    label: __('Center', 'frontblocks')
  }), /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "right",
    icon: ALIGN_RIGHT_ICON,
    label: __('Right', 'frontblocks')
  }), /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "justify",
    icon: ALIGN_JUSTIFY_ICON,
    label: __('Justify', 'frontblocks')
  })), /*#__PURE__*/React.createElement(ToggleGroupControl, {
    label: __('Text Transform', 'frontblocks'),
    value: textTransform,
    onChange: function onChange(value) {
      return setAttributes({
        textTransform: value || 'none'
      });
    },
    isBlock: true
  }, /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "none",
    icon: TRANSFORM_NONE_ICON,
    label: __('None', 'frontblocks')
  }), /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "uppercase",
    icon: TRANSFORM_UPPERCASE_ICON,
    label: __('Uppercase', 'frontblocks')
  }), /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "lowercase",
    icon: TRANSFORM_LOWERCASE_ICON,
    label: __('Lowercase', 'frontblocks')
  }), /*#__PURE__*/React.createElement(ToggleGroupControlOptionIcon, {
    value: "capitalize",
    icon: TRANSFORM_CAPITALIZE_ICON,
    label: __('Capitalize', 'frontblocks')
  })), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Line Height', 'frontblocks'),
    value: lineHeight,
    onChange: function onChange(value) {
      return setAttributes({
        lineHeight: value
      });
    },
    min: 0.5,
    max: 4,
    step: 0.05,
    initialPosition: 1.5,
    allowReset: true,
    resetFallbackValue: undefined
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Letter Spacing (em)', 'frontblocks'),
    value: letterSpacing,
    onChange: function onChange(value) {
      return setAttributes({
        letterSpacing: value
      });
    },
    min: -0.2,
    max: 1,
    step: 0.01,
    initialPosition: 0,
    allowReset: true,
    resetFallbackValue: undefined
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Dimensions', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: '16px'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      marginTop: 0,
      marginBottom: '8px',
      fontSize: '11px',
      fontWeight: '500',
      textTransform: 'uppercase',
      color: 'rgb(117, 117, 117)'
    }
  }, __('Width', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: '8px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement(NumberControl, {
    value: width || '',
    onChange: function onChange(value) {
      return setAttributes({
        width: value ? parseFloat(value) : undefined
      });
    },
    min: 0,
    step: 1,
    spinControls: "native",
    hideLabelFromVision: true,
    label: __('Width', 'frontblocks'),
    placeholder: "auto"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      width: '80px',
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement(SelectControl, {
    value: widthUnit,
    options: [{
      label: 'px',
      value: 'px'
    }, {
      label: '%',
      value: '%'
    }, {
      label: 'rem',
      value: 'rem'
    }, {
      label: 'em',
      value: 'em'
    }, {
      label: 'vw',
      value: 'vw'
    }, {
      label: 'ch',
      value: 'ch'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        widthUnit: value
      });
    },
    hideLabelFromVision: true,
    label: __('Unit', 'frontblocks'),
    __nextHasNoMarginBottom: true
  })))), /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: '8px'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      marginTop: 0,
      marginBottom: '8px',
      fontSize: '11px',
      fontWeight: '500',
      textTransform: 'uppercase',
      color: 'rgb(117, 117, 117)'
    }
  }, __('Height', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      gap: '8px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1
    }
  }, /*#__PURE__*/React.createElement(NumberControl, {
    value: height || '',
    onChange: function onChange(value) {
      return setAttributes({
        height: value ? parseFloat(value) : undefined
      });
    },
    min: 0,
    step: 1,
    spinControls: "native",
    hideLabelFromVision: true,
    label: __('Height', 'frontblocks'),
    placeholder: "auto"
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      width: '80px',
      flexShrink: 0
    }
  }, /*#__PURE__*/React.createElement(SelectControl, {
    value: heightUnit,
    options: [{
      label: 'px',
      value: 'px'
    }, {
      label: '%',
      value: '%'
    }, {
      label: 'rem',
      value: 'rem'
    }, {
      label: 'em',
      value: 'em'
    }, {
      label: 'vh',
      value: 'vh'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        heightUnit: value
      });
    },
    hideLabelFromVision: true,
    label: __('Unit', 'frontblocks'),
    __nextHasNoMarginBottom: true
  }))))), /*#__PURE__*/React.createElement(PanelColorSettings, {
    title: __('Color', 'frontblocks'),
    initialOpen: false,
    colorSettings: [{
      label: __('Text Color', 'frontblocks'),
      value: textColorCustom,
      onChange: function onChange(value) {
        return setAttributes({
          textColorCustom: value || ''
        });
      }
    }, {
      label: __('Text Color Hover', 'frontblocks'),
      value: textColorHover,
      onChange: function onChange(value) {
        return setAttributes({
          textColorHover: value || ''
        });
      }
    }]
  })), /*#__PURE__*/React.createElement(RichText, _extends({}, blockProps, {
    tagName: htmlTag,
    value: content,
    onChange: function onChange(value) {
      return setAttributes({
        content: value
      });
    },
    placeholder: __('Write your text here…', 'frontblocks'),
    allowedFormats: ['core/bold', 'core/italic', 'core/link', 'core/underline', 'core/strikethrough', 'core/code']
  })));
}
registerBlockType('frontblocks/text-animation', {
  title: __('Text Animation', 'frontblocks'),
  description: __('Animated text block with typography controls. Add animation effects from the sidebar.', 'frontblocks'),
  category: 'text',
  icon: 'editor-textcolor',
  keywords: [__('text', 'frontblocks'), __('animation', 'frontblocks'), __('heading', 'frontblocks'), __('typography', 'frontblocks')],
  supports: {
    anchor: true,
    className: true,
    color: false,
    spacing: {
      margin: true,
      padding: true
    }
  },
  attributes: {
    content: {
      type: 'string',
      source: 'html',
      selector: '.frbl-text-animation',
      default: ''
    },
    htmlTag: {
      type: 'string',
      default: 'h2'
    },
    fontSize: {
      type: 'number',
      default: undefined
    },
    fontSizeUnit: {
      type: 'string',
      default: 'px'
    },
    fontFamily: {
      type: 'string',
      default: ''
    },
    fontWeight: {
      type: 'string',
      default: ''
    },
    fontStyle: {
      type: 'string',
      default: 'normal'
    },
    lineHeight: {
      type: 'number',
      default: undefined
    },
    letterSpacing: {
      type: 'number',
      default: undefined
    },
    textAlign: {
      type: 'string',
      default: 'left'
    },
    textTransform: {
      type: 'string',
      default: 'none'
    },
    textColorCustom: {
      type: 'string',
      default: ''
    },
    textColorHover: {
      type: 'string',
      default: ''
    },
    width: {
      type: 'number',
      default: undefined
    },
    widthUnit: {
      type: 'string',
      default: 'px'
    },
    height: {
      type: 'number',
      default: undefined
    },
    heightUnit: {
      type: 'string',
      default: 'px'
    }
  },
  edit: TextAnimationEdit,
  save: function save(_ref) {
    var attributes = _ref.attributes;
    var content = attributes.content,
      Tag = attributes.htmlTag,
      fontSize = attributes.fontSize,
      fontSizeUnit = attributes.fontSizeUnit,
      fontFamily = attributes.fontFamily,
      fontWeight = attributes.fontWeight,
      fontStyle = attributes.fontStyle,
      lineHeight = attributes.lineHeight,
      letterSpacing = attributes.letterSpacing,
      textAlign = attributes.textAlign,
      textTransform = attributes.textTransform,
      textColorCustom = attributes.textColorCustom,
      textColorHover = attributes.textColorHover,
      width = attributes.width,
      widthUnit = attributes.widthUnit,
      height = attributes.height,
      heightUnit = attributes.heightUnit;
    var style = {};
    if (fontSize) style.fontSize = "".concat(fontSize).concat(fontSizeUnit || 'px');
    if (fontFamily) style.fontFamily = fontFamily;
    if (fontWeight) style.fontWeight = fontWeight;
    if (fontStyle && fontStyle !== 'normal') style.fontStyle = fontStyle;
    if (lineHeight) style.lineHeight = lineHeight;
    if (letterSpacing) style.letterSpacing = "".concat(letterSpacing, "em");
    if (textAlign && textAlign !== 'left') style.textAlign = textAlign;
    if (textTransform && textTransform !== 'none') style.textTransform = textTransform;
    if (textColorCustom) style['--frbl-color'] = textColorCustom;
    if (textColorHover) style['--frbl-color-hover'] = textColorHover;
    if (width) style.width = "".concat(width).concat(widthUnit || 'px');
    if (height) style.height = "".concat(height).concat(heightUnit || 'px');
    var blockProps = wp.blockEditor.useBlockProps.save({
      className: 'frbl-text-animation',
      style: style
    });
    return /*#__PURE__*/React.createElement(Tag, blockProps, /*#__PURE__*/React.createElement(RichText.Content, {
      value: content
    }));
  }
});
