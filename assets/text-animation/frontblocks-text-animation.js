"use strict";

function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
var registerBlockType = wp.blocks.registerBlockType;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useState = _wp$element.useState;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps,
  RichText = _wp$blockEditor.RichText,
  withColors = _wp$blockEditor.withColors,
  PanelColorSettings = _wp$blockEditor.PanelColorSettings,
  FontSizePicker = _wp$blockEditor.FontSizePicker,
  withFontSizes = _wp$blockEditor.withFontSizes;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl,
  RangeControl = _wp$components.RangeControl,
  ToggleControl = _wp$components.ToggleControl,
  UnitControl = _wp$components.__experimentalUnitControl;
var __ = wp.i18n.__;
var FONT_SIZES = [{
  name: __('Small', 'frontblocks'),
  slug: 'small',
  size: 14
}, {
  name: __('Normal', 'frontblocks'),
  slug: 'normal',
  size: 16
}, {
  name: __('Medium', 'frontblocks'),
  slug: 'medium',
  size: 20
}, {
  name: __('Large', 'frontblocks'),
  slug: 'large',
  size: 28
}, {
  name: __('X-Large', 'frontblocks'),
  slug: 'x-large',
  size: 36
}, {
  name: __('XX-Large', 'frontblocks'),
  slug: 'xx-large',
  size: 48
}, {
  name: __('Huge', 'frontblocks'),
  slug: 'huge',
  size: 64
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
var TEXT_ALIGN_OPTIONS = [{
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
var TEXT_TRANSFORM_OPTIONS = [{
  label: __('None', 'frontblocks'),
  value: 'none'
}, {
  label: __('Uppercase', 'frontblocks'),
  value: 'uppercase'
}, {
  label: __('Lowercase', 'frontblocks'),
  value: 'lowercase'
}, {
  label: __('Capitalize', 'frontblocks'),
  value: 'capitalize'
}];
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
  var content = attributes.content,
    htmlTag = attributes.htmlTag,
    fontSize = attributes.fontSize,
    fontSizeUnit = attributes.fontSizeUnit,
    fontWeight = attributes.fontWeight,
    fontStyle = attributes.fontStyle,
    lineHeight = attributes.lineHeight,
    letterSpacing = attributes.letterSpacing,
    textAlign = attributes.textAlign,
    textTransform = attributes.textTransform,
    textColor = attributes.textColor,
    textColorCustom = attributes.textColorCustom;
  var blockProps = useBlockProps({
    style: {
      fontSize: fontSize ? "".concat(fontSize).concat(fontSizeUnit || 'px') : undefined,
      fontWeight: fontWeight || undefined,
      fontStyle: fontStyle !== 'normal' ? fontStyle : undefined,
      lineHeight: lineHeight || undefined,
      letterSpacing: letterSpacing ? "".concat(letterSpacing, "em") : undefined,
      textAlign: textAlign !== 'left' ? textAlign : undefined,
      textTransform: textTransform !== 'none' ? textTransform : undefined,
      color: textColorCustom || undefined
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
  }), /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: '16px'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      marginBottom: '8px',
      fontWeight: '500'
    }
  }, __('Font Size', 'frontblocks')), /*#__PURE__*/React.createElement(FontSizePicker, {
    fontSizes: FONT_SIZES,
    value: fontSize,
    onChange: function onChange(value) {
      return setAttributes({
        fontSize: value || undefined
      });
    },
    withSlider: true
  })), /*#__PURE__*/React.createElement(SelectControl, {
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
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Text Align', 'frontblocks'),
    value: textAlign,
    options: TEXT_ALIGN_OPTIONS,
    onChange: function onChange(value) {
      return setAttributes({
        textAlign: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Text Transform', 'frontblocks'),
    value: textTransform,
    options: TEXT_TRANSFORM_OPTIONS,
    onChange: function onChange(value) {
      return setAttributes({
        textTransform: value
      });
    }
  }), /*#__PURE__*/React.createElement(RangeControl, {
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
  })), /*#__PURE__*/React.createElement(PanelColorSettings, {
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
    }
  },
  edit: TextAnimationEdit,
  save: function save(_ref) {
    var attributes = _ref.attributes;
    var content = attributes.content,
      Tag = attributes.htmlTag,
      fontSize = attributes.fontSize,
      fontSizeUnit = attributes.fontSizeUnit,
      fontWeight = attributes.fontWeight,
      fontStyle = attributes.fontStyle,
      lineHeight = attributes.lineHeight,
      letterSpacing = attributes.letterSpacing,
      textAlign = attributes.textAlign,
      textTransform = attributes.textTransform,
      textColorCustom = attributes.textColorCustom;
    var style = {
      fontSize: fontSize ? "".concat(fontSize).concat(fontSizeUnit || 'px') : undefined,
      fontWeight: fontWeight || undefined,
      fontStyle: fontStyle !== 'normal' ? fontStyle : undefined,
      lineHeight: lineHeight || undefined,
      letterSpacing: letterSpacing ? "".concat(letterSpacing, "em") : undefined,
      textAlign: textAlign !== 'left' ? textAlign : undefined,
      textTransform: textTransform !== 'none' ? textTransform : undefined,
      color: textColorCustom || undefined
    };

    // Remove undefined keys.
    Object.keys(style).forEach(function (k) {
      return style[k] === undefined && delete style[k];
    });
    var blockProps = wp.blockEditor.useBlockProps.save();
    return /*#__PURE__*/React.createElement(Tag, _extends({}, blockProps, {
      className: "".concat(blockProps.className || '', " frbl-text-animation").trim(),
      style: style
    }), /*#__PURE__*/React.createElement(RichText.Content, {
      value: content
    }));
  }
});
