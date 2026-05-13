"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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
  Button = _wp$components.Button,
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
var ANIMATION_OPTIONS = [{
  label: __('None', 'frontblocks'),
  value: 'none'
}, {
  label: __('Fade In', 'frontblocks'),
  value: 'fade-in'
}, {
  label: __('Typewriter', 'frontblocks'),
  value: 'typewriter'
}, {
  label: __('Shuffle Text', 'frontblocks'),
  value: 'shuffle-text'
}, {
  label: __('Slide Up', 'frontblocks'),
  value: 'slide-up'
}, {
  label: __('Slide Down', 'frontblocks'),
  value: 'slide-down'
}, {
  label: __('Slide Left', 'frontblocks'),
  value: 'slide-left'
}, {
  label: __('Slide Right', 'frontblocks'),
  value: 'slide-right'
}, {
  label: __('Drop In', 'frontblocks'),
  value: 'drop-in'
}, {
  label: __('Swing', 'frontblocks'),
  value: 'swing'
}, {
  label: __('Pulse', 'frontblocks'),
  value: 'pulse'
}, {
  label: __('Flash', 'frontblocks'),
  value: 'flash'
}, {
  label: __('Rubber Band', 'frontblocks'),
  value: 'rubber-band'
}, {
  label: __('Wave', 'frontblocks'),
  value: 'wave'
}, {
  label: __('Stretch', 'frontblocks'),
  value: 'stretch'
}, {
  label: __('Squeeze', 'frontblocks'),
  value: 'squeeze'
}, {
  label: __('Scale In', 'frontblocks'),
  value: 'scale-in'
}, {
  label: __('Blur In', 'frontblocks'),
  value: 'blur-in'
}, {
  label: __('Glow In', 'frontblocks'),
  value: 'glow-in'
}, {
  label: __('Bounce In', 'frontblocks'),
  value: 'bounce-in'
}, {
  label: __('Flip In', 'frontblocks'),
  value: 'flip-in'
}, {
  label: __('Rotate In', 'frontblocks'),
  value: 'rotate-in'
}];
function stripHtml(html) {
  return html ? html.replace(/<[^>]*>/g, '') : '';
}

/* ── Animation preview registry ─────────────────────────────
   Each entry: { loop: bool, render( text, style, Tag, key ) → JSX }
   Add a new entry for every new animation type.
──────────────────────────────────────────────────────────── */
var ANIMATION_PREVIEWS = {
  'fade-in': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.8) * 1000;
    },
    render: function FadeInRender(_ref) {
      var text = _ref.text,
        style = _ref.style,
        Tag = _ref.Tag,
        animKey = _ref.animKey;
      var CHAR_DURATION = 0.8;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblFadeIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'rotate-in': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function RotateInRender(_ref2) {
      var text = _ref2.text,
        style = _ref2.style,
        Tag = _ref2.Tag,
        animKey = _ref2.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblRotateIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'flip-in': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.6) * 1000;
    },
    render: function FlipInRender(_ref3) {
      var text = _ref3.text,
        style = _ref3.style,
        Tag = _ref3.Tag,
        animKey = _ref3.animKey;
      var CHAR_DURATION = 0.6;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblFlipIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'bounce-in': {
    duration: function duration(text) {
      return (text.length * 0.08 + 0.6) * 1000;
    },
    render: function BounceInRender(_ref4) {
      var text = _ref4.text,
        style = _ref4.style,
        Tag = _ref4.Tag,
        animKey = _ref4.animKey;
      var CHAR_DURATION = 0.6;
      var CHAR_DELAY = 0.08;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblBounceIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'glow-in': {
    duration: function duration(text) {
      return (text.length * 0.05 + 1) * 1000;
    },
    render: function GlowInRender(_ref5) {
      var text = _ref5.text,
        style = _ref5.style,
        Tag = _ref5.Tag,
        animKey = _ref5.animKey;
      var CHAR_DURATION = 1;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblGlowIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'blur-in': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.8) * 1000;
    },
    render: function BlurInRender(_ref6) {
      var text = _ref6.text,
        style = _ref6.style,
        Tag = _ref6.Tag,
        animKey = _ref6.animKey;
      var CHAR_DURATION = 0.8;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblBlurIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'scale-in': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function ScaleInRender(_ref7) {
      var text = _ref7.text,
        style = _ref7.style,
        Tag = _ref7.Tag,
        animKey = _ref7.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblScaleIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'slide-up': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function SlideUpRender(_ref8) {
      var text = _ref8.text,
        style = _ref8.style,
        Tag = _ref8.Tag,
        animKey = _ref8.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: _objectSpread(_objectSpread({}, style), {}, {
          overflow: 'hidden'
        }),
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblSlideUp ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'squeeze': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function SqueezeRender(_ref9) {
      var text = _ref9.text,
        style = _ref9.style,
        Tag = _ref9.Tag,
        animKey = _ref9.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblSqueeze ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'stretch': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function StretchRender(_ref0) {
      var text = _ref0.text,
        style = _ref0.style,
        Tag = _ref0.Tag,
        animKey = _ref0.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblStretch ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'wave': {
    duration: function duration(text) {
      return (text.length * 0.08 + 0.6) * 1000;
    },
    render: function WaveRender(_ref1) {
      var text = _ref1.text,
        style = _ref1.style,
        Tag = _ref1.Tag,
        animKey = _ref1.animKey;
      var CHAR_DURATION = 0.6;
      var CHAR_DELAY = 0.08;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblWave ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'rubber-band': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.8) * 1000;
    },
    render: function RubberBandRender(_ref10) {
      var text = _ref10.text,
        style = _ref10.style,
        Tag = _ref10.Tag,
        animKey = _ref10.animKey;
      var CHAR_DURATION = 0.8;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblRubberBand ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'flash': {
    duration: function duration(text) {
      return (text.length * 0.05 + 1) * 1000;
    },
    render: function FlashRender(_ref11) {
      var text = _ref11.text,
        style = _ref11.style,
        Tag = _ref11.Tag,
        animKey = _ref11.animKey;
      var CHAR_DURATION = 1;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblFlash ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'pulse': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function PulseRender(_ref12) {
      var text = _ref12.text,
        style = _ref12.style,
        Tag = _ref12.Tag,
        animKey = _ref12.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblPulse ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'swing': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.8) * 1000;
    },
    render: function SwingRender(_ref13) {
      var text = _ref13.text,
        style = _ref13.style,
        Tag = _ref13.Tag,
        animKey = _ref13.animKey;
      var CHAR_DURATION = 0.8;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: style,
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            transformOrigin: 'top center',
            animation: "frblSwing ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'drop-in': {
    duration: function duration(text) {
      return (text.length * 0.06 + 0.6) * 1000;
    },
    render: function DropInRender(_ref14) {
      var text = _ref14.text,
        style = _ref14.style,
        Tag = _ref14.Tag,
        animKey = _ref14.animKey;
      var CHAR_DURATION = 0.6;
      var CHAR_DELAY = 0.06;
      return /*#__PURE__*/React.createElement(Tag, {
        style: _objectSpread(_objectSpread({}, style), {}, {
          overflow: 'hidden'
        }),
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblDropIn ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'slide-right': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function SlideRightRender(_ref15) {
      var text = _ref15.text,
        style = _ref15.style,
        Tag = _ref15.Tag,
        animKey = _ref15.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: _objectSpread(_objectSpread({}, style), {}, {
          overflow: 'hidden'
        }),
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblSlideRight ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'slide-left': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function SlideLeftRender(_ref16) {
      var text = _ref16.text,
        style = _ref16.style,
        Tag = _ref16.Tag,
        animKey = _ref16.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: _objectSpread(_objectSpread({}, style), {}, {
          overflow: 'hidden'
        }),
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblSlideLeft ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'slide-down': {
    duration: function duration(text) {
      return (text.length * 0.05 + 0.5) * 1000;
    },
    render: function SlideDownRender(_ref17) {
      var text = _ref17.text,
        style = _ref17.style,
        Tag = _ref17.Tag,
        animKey = _ref17.animKey;
      var CHAR_DURATION = 0.5;
      var CHAR_DELAY = 0.05;
      return /*#__PURE__*/React.createElement(Tag, {
        style: _objectSpread(_objectSpread({}, style), {}, {
          overflow: 'hidden'
        }),
        key: animKey
      }, text.split('').map(function (char, i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          style: {
            display: 'inline-block',
            whiteSpace: 'pre',
            opacity: 0,
            animation: "frblSlideDown ".concat(CHAR_DURATION, "s forwards"),
            animationDelay: "".concat(i * CHAR_DELAY, "s")
          }
        }, char);
      }));
    }
  },
  'shuffle-text': {
    duration: function duration(text) {
      return (text.replace(/ /g, '').length * 2 + 15) * 30;
    },
    render: function ShuffleTextRender(_ref18) {
      var text = _ref18.text,
        style = _ref18.style,
        Tag = _ref18.Tag,
        animKey = _ref18.animKey;
      var _wp$element2 = wp.element,
        useEffect = _wp$element2.useEffect,
        useRef = _wp$element2.useRef;
      var SYMBOLS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
      var containerRef = useRef(null);
      useEffect(function () {
        var el = containerRef.current;
        if (!el) return;
        el.innerHTML = '';
        var chars = text.split('');
        var spanList = chars.map(function (char) {
          var span = document.createElement('span');
          span.className = 'frbl-char';
          el.appendChild(span);
          return {
            el: span,
            char: char,
            isSpace: char === ' '
          };
        });
        var frame = 0;
        var rafId;
        var timerId;
        function update() {
          var allDone = true;
          spanList.forEach(function (item, i) {
            if (item.isSpace) {
              item.el.textContent = ' ';
              return;
            }
            var startFrame = i * 2;
            var endFrame = startFrame + 15;
            if (frame < startFrame) {
              allDone = false;
              item.el.textContent = '';
            } else if (frame < endFrame) {
              allDone = false;
              item.el.textContent = SYMBOLS[Math.floor(Math.random() * SYMBOLS.length)];
            } else {
              item.el.textContent = item.char;
            }
          });
          frame++;
          if (!allDone) {
            timerId = setTimeout(function () {
              rafId = requestAnimationFrame(update);
            }, 30);
          }
        }
        rafId = requestAnimationFrame(update);
        return function () {
          cancelAnimationFrame(rafId);
          clearTimeout(timerId);
        };
      }, [animKey, text]);
      return /*#__PURE__*/React.createElement(Tag, {
        ref: containerRef,
        style: style,
        key: animKey
      });
    }
  },
  'typewriter': {
    duration: function duration(text) {
      return text.length * 80;
    },
    render: function TypewriterRender(_ref19) {
      var text = _ref19.text,
        style = _ref19.style,
        Tag = _ref19.Tag,
        animKey = _ref19.animKey;
      var _wp$element3 = wp.element,
        useEffect = _wp$element3.useEffect,
        useRef = _wp$element3.useRef;
      var containerRef = useRef(null);
      useEffect(function () {
        var el = containerRef.current;
        if (!el) return;
        el.textContent = '';
        var chars = text.split('');
        var i = 0;
        var timer;
        function type() {
          if (i < chars.length) {
            el.textContent += chars[i];
            i++;
            timer = setTimeout(type, 80);
          }
        }
        type();
        return function () {
          return clearTimeout(timer);
        };
      }, [animKey, text]);
      return /*#__PURE__*/React.createElement(Tag, {
        ref: containerRef,
        style: style,
        key: animKey
      });
    }
  }
};
function AnimationPreview(_ref20) {
  var animationType = _ref20.animationType,
    text = _ref20.text,
    style = _ref20.style,
    Tag = _ref20.Tag;
  var _useState = useState(0),
    _useState2 = _slicedToArray(_useState, 2),
    animKey = _useState2[0],
    setAnimKey = _useState2[1];
  var entry = ANIMATION_PREVIEWS[animationType];
  useState(function () {
    if (!entry) return;
    var ms = entry.duration(text);
    var t = setTimeout(function () {
      return setAnimKey(function (k) {
        return k + 1;
      });
    }, ms);
    return function () {
      return clearTimeout(t);
    };
  });
  if (!entry) return null;
  return entry.render({
    text: text,
    style: style,
    Tag: Tag,
    animKey: animKey
  });
}
function TextAnimationEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var _useState3 = useState(false),
    _useState4 = _slicedToArray(_useState3, 2),
    isHovered = _useState4[0],
    setIsHovered = _useState4[1];
  var _useState5 = useState(true),
    _useState6 = _slicedToArray(_useState5, 2),
    isEditMode = _useState6[0],
    setIsEditMode = _useState6[1];
  var content = attributes.content,
    htmlTag = attributes.htmlTag,
    animationType = attributes.animationType,
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
  var hasAnimation = animationType && animationType !== 'none';
  var showPreview = hasAnimation && !isEditMode;
  var previewStyle = {
    fontSize: blockProps.style.fontSize,
    fontFamily: blockProps.style.fontFamily,
    fontWeight: blockProps.style.fontWeight,
    fontStyle: blockProps.style.fontStyle,
    lineHeight: blockProps.style.lineHeight,
    letterSpacing: blockProps.style.letterSpacing,
    textAlign: blockProps.style.textAlign,
    textTransform: blockProps.style.textTransform,
    color: blockProps.style.color || blockProps.style['--frbl-color'] || undefined,
    width: blockProps.style.width,
    height: blockProps.style.height,
    marginLeft: 0,
    marginRight: 'auto'
  };
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('FrontBlocks Animation Text', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Animation Type', 'frontblocks'),
    value: animationType,
    options: ANIMATION_OPTIONS,
    onChange: function onChange(value) {
      setAttributes({
        animationType: value
      });
      if (value && value !== 'none') {
        var entry = ANIMATION_PREVIEWS[value];
        var text = stripHtml(content) || __('Write your text here…', 'frontblocks');
        var ms = entry ? entry.duration(text) : 2000;
        setIsEditMode(false);
        setTimeout(function () {
          return setIsEditMode(true);
        }, ms);
      } else {
        setIsEditMode(true);
      }
    }
  }), hasAnimation && /*#__PURE__*/React.createElement(Button, {
    variant: "secondary",
    size: "small",
    onClick: function onClick() {
      var entry = ANIMATION_PREVIEWS[animationType];
      var text = stripHtml(content) || __('Write your text here…', 'frontblocks');
      var ms = entry ? entry.duration(text) : 2000;
      setIsEditMode(false);
      setTimeout(function () {
        return setIsEditMode(true);
      }, ms);
    },
    style: {
      marginTop: '8px',
      width: '100%',
      justifyContent: 'center'
    }
  }, __('▶ Preview animation', 'frontblocks'))), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Typography', 'frontblocks'),
    initialOpen: false
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
  })), showPreview ? /*#__PURE__*/React.createElement(AnimationPreview, {
    animationType: animationType,
    text: stripHtml(content) || __('Write your text here…', 'frontblocks'),
    style: previewStyle,
    Tag: htmlTag
  }) : /*#__PURE__*/React.createElement(RichText, _extends({}, blockProps, {
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
    animationType: {
      type: 'string',
      default: 'none'
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
  save: function save(_ref21) {
    var attributes = _ref21.attributes;
    var content = attributes.content,
      Tag = attributes.htmlTag,
      animationType = attributes.animationType,
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
      style: style,
      'data-animation': animationType && animationType !== 'none' ? animationType : undefined
    });
    return /*#__PURE__*/React.createElement(Tag, blockProps, /*#__PURE__*/React.createElement(RichText.Content, {
      value: content
    }));
  }
});
