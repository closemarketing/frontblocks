"use strict";

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
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps,
  ColorPalette = _wp$blockEditor.ColorPalette;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl,
  TextControl = _wp$components.TextControl,
  RangeControl = _wp$components.RangeControl,
  SelectControl = _wp$components.SelectControl;
var __ = wp.i18n.__;
var useSelect = wp.data.useSelect;

/**
 * Calculate reading time for content.
 *
 * @param {string} content - Post content.
 * @return {number} Reading time in minutes.
 */
function calculateReadingTime(content) {
  // Ensure content is a string.
  if (!content || typeof content !== 'string') {
    return 5;
  }

  // Strip HTML tags.
  var textContent = content.replace(/<[^>]*>/g, '');

  // Count words.
  var wordCount = textContent.split(/\s+/).filter(function (word) {
    return word.length > 0;
  }).length;

  // Average reading speed: 225 words per minute.
  var readingTime = Math.ceil(wordCount / 225);

  // Minimum 1 minute.
  return Math.max(1, readingTime);
}

/**
 * Edit component for Reading Time block.
 *
 * @param {Object} props - Block properties.
 * @return {JSX.Element} Block edit component.
 */
function ReadingTimeEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var postId = attributes.postId,
    showIcon = attributes.showIcon,
    prefix = attributes.prefix,
    suffix = attributes.suffix,
    textColor = attributes.textColor,
    backgroundColor = attributes.backgroundColor,
    fontSize = attributes.fontSize,
    iconColor = attributes.iconColor,
    alignment = attributes.alignment,
    padding = attributes.padding,
    borderRadius = attributes.borderRadius;
  var _useState = useState(5),
    _useState2 = _slicedToArray(_useState, 2),
    readingTime = _useState2[0],
    setReadingTime = _useState2[1];
  var blockProps = useBlockProps();

  // Get current post content and calculate reading time.
  useEffect(function () {
    try {
      var _wp$data$select, _wp$data$select$getEd;
      // Try to get the post content from the editor.
      var postContent = (_wp$data$select = wp.data.select('core/editor')) === null || _wp$data$select === void 0 || (_wp$data$select$getEd = _wp$data$select.getEditedPostContent) === null || _wp$data$select$getEd === void 0 ? void 0 : _wp$data$select$getEd.call(_wp$data$select);
      if (postContent && typeof postContent === 'string') {
        var calculatedTime = calculateReadingTime(postContent);
        setReadingTime(calculatedTime);
      } else {
        setReadingTime(5);
      }
    } catch (error) {
      // If there's any error, just use default value.
      setReadingTime(5);
    }
  }, [postId]);
  var styleVars = {
    '--frbl-text-color': textColor,
    '--frbl-bg-color': backgroundColor,
    '--frbl-font-size': "".concat(fontSize, "px"),
    '--frbl-icon-color': iconColor,
    '--frbl-padding': "".concat(padding, "px"),
    '--frbl-border-radius': "".concat(borderRadius, "px")
  };
  var iconSvg = /*#__PURE__*/React.createElement("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    width: "1em",
    height: "1em",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  }, /*#__PURE__*/React.createElement("circle", {
    cx: "12",
    cy: "12",
    r: "10"
  }), /*#__PURE__*/React.createElement("polyline", {
    points: "12 6 12 12 16 14"
  }));
  var wrapperClass = "frbl-reading-time-wrapper align-".concat(alignment);
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('General Settings', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Show Icon', 'frontblocks'),
    checked: showIcon,
    onChange: function onChange(value) {
      return setAttributes({
        showIcon: value
      });
    }
  }), /*#__PURE__*/React.createElement(TextControl, {
    label: __('Prefix', 'frontblocks'),
    value: prefix,
    onChange: function onChange(value) {
      return setAttributes({
        prefix: value
      });
    },
    help: __('Text to display before the reading time number. Example: "This blog takes"', 'frontblocks'),
    placeholder: __('Example: This blog takes', 'frontblocks')
  }), /*#__PURE__*/React.createElement(TextControl, {
    label: __('Suffix', 'frontblocks'),
    value: suffix,
    onChange: function onChange(value) {
      return setAttributes({
        suffix: value
      });
    },
    help: __('Text to display after the reading time number. Example: "minutes to read"', 'frontblocks'),
    placeholder: __('Example: minutes to read', 'frontblocks')
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Alignment', 'frontblocks'),
    value: alignment,
    options: [{
      label: __('Left', 'frontblocks'),
      value: 'left'
    }, {
      label: __('Center', 'frontblocks'),
      value: 'center'
    }, {
      label: __('Right', 'frontblocks'),
      value: 'right'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        alignment: value
      });
    }
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Style Settings', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Font Size', 'frontblocks'),
    value: fontSize,
    onChange: function onChange(value) {
      return setAttributes({
        fontSize: value
      });
    },
    min: 10,
    max: 48
  }), /*#__PURE__*/React.createElement("p", {
    style: {
      marginBottom: '8px',
      fontWeight: '500'
    }
  }, __('Text Color', 'frontblocks')), /*#__PURE__*/React.createElement(ColorPalette, {
    value: textColor,
    onChange: function onChange(value) {
      return setAttributes({
        textColor: value || 'inherit'
      });
    }
  }), /*#__PURE__*/React.createElement("p", {
    style: {
      marginBottom: '8px',
      fontWeight: '500'
    }
  }, __('Background Color', 'frontblocks')), /*#__PURE__*/React.createElement(ColorPalette, {
    value: backgroundColor,
    onChange: function onChange(value) {
      return setAttributes({
        backgroundColor: value || 'transparent'
      });
    }
  }), /*#__PURE__*/React.createElement("p", {
    style: {
      marginBottom: '8px',
      fontWeight: '500'
    }
  }, __('Icon Color', 'frontblocks')), /*#__PURE__*/React.createElement(ColorPalette, {
    value: iconColor,
    onChange: function onChange(value) {
      return setAttributes({
        iconColor: value || 'currentColor'
      });
    }
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Padding', 'frontblocks'),
    value: padding,
    onChange: function onChange(value) {
      return setAttributes({
        padding: value
      });
    },
    min: 0,
    max: 50
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Border Radius', 'frontblocks'),
    value: borderRadius,
    onChange: function onChange(value) {
      return setAttributes({
        borderRadius: value
      });
    },
    min: 0,
    max: 50
  }))), /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement("div", {
    className: wrapperClass
  }, /*#__PURE__*/React.createElement("div", {
    className: "frbl-reading-time",
    style: styleVars
  }, showIcon && /*#__PURE__*/React.createElement("span", {
    className: "frbl-reading-time-icon"
  }, iconSvg), /*#__PURE__*/React.createElement("span", {
    className: "frbl-reading-time-text"
  }, prefix && "".concat(prefix, " "), readingTime, suffix && " ".concat(suffix))))));
}

// Register the block.
registerBlockType('frontblocks/reading-time', {
  title: __('FrontBlocks - Reading Time', 'frontblocks'),
  description: __('Display estimated reading time for the current post or page.', 'frontblocks'),
  category: 'common',
  icon: 'clock',
  keywords: [__('reading', 'frontblocks'), __('time', 'frontblocks'), __('duration', 'frontblocks'), __('blog', 'frontblocks')],
  attributes: {
    postId: {
      type: 'number',
      default: 0
    },
    showIcon: {
      type: 'boolean',
      default: true
    },
    prefix: {
      type: 'string',
      default: ''
    },
    suffix: {
      type: 'string',
      default: 'min'
    },
    className: {
      type: 'string',
      default: ''
    },
    textColor: {
      type: 'string',
      default: 'inherit'
    },
    backgroundColor: {
      type: 'string',
      default: 'transparent'
    },
    fontSize: {
      type: 'number',
      default: 16
    },
    iconColor: {
      type: 'string',
      default: 'currentColor'
    },
    alignment: {
      type: 'string',
      default: 'left'
    },
    padding: {
      type: 'number',
      default: 10
    },
    borderRadius: {
      type: 'number',
      default: 5
    }
  },
  edit: ReadingTimeEdit,
  save: function save() {
    return null; // Dynamic block, render on server side.
  }
});
