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
  InnerBlocks = _wp$blockEditor.InnerBlocks,
  useBlockProps = _wp$blockEditor.useBlockProps,
  useInnerBlocksProps = _wp$blockEditor.useInnerBlocksProps;
function extractVimeoId(url) {
  if (!url) return null;
  var match = url.match(/(?:vimeo\.com\/(?:video\/)?|player\.vimeo\.com\/video\/)(\d+)/);
  return match ? match[1] : null;
}
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  TextControl = _wp$components.TextControl,
  RangeControl = _wp$components.RangeControl,
  SelectControl = _wp$components.SelectControl,
  ColorPicker = _wp$components.ColorPicker,
  ColorIndicator = _wp$components.ColorIndicator,
  Dropdown = _wp$components.Dropdown,
  Button = _wp$components.Button;
var __ = wp.i18n.__;
var CONTENT_JUSTIFY = [{
  label: __('Izquierda', 'frontblocks'),
  value: 'flex-start'
}, {
  label: __('Centro', 'frontblocks'),
  value: 'center'
}, {
  label: __('Derecha', 'frontblocks'),
  value: 'flex-end'
}, {
  label: __('Stretch', 'frontblocks'),
  value: 'stretch'
}];
var CONTENT_ALIGN = [{
  label: __('Arriba', 'frontblocks'),
  value: 'flex-start'
}, {
  label: __('Centro', 'frontblocks'),
  value: 'center'
}, {
  label: __('Abajo', 'frontblocks'),
  value: 'flex-end'
}, {
  label: __('Stretch', 'frontblocks'),
  value: 'stretch'
}];
function contentStyle(hAlign, vAlign, contentMaxWidth) {
  var style = {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: vAlign || 'center',
    /* vertical  = main axis in column */
    alignItems: hAlign || 'stretch',
    /* horizontal = cross axis in column */
    width: '100%',
    height: '100%',
    boxSizing: 'border-box'
  };
  if (contentMaxWidth) {
    style.maxWidth = contentMaxWidth;
    style.marginLeft = 'auto';
    style.marginRight = 'auto';
  }
  return style;
}
function VimeoBackgroundEdit(_ref) {
  var attributes = _ref.attributes,
    setAttributes = _ref.setAttributes;
  var vimeoUrl = attributes.vimeoUrl,
    minHeight = attributes.minHeight,
    minHeightUnit = attributes.minHeightUnit,
    overlayColor = attributes.overlayColor,
    overlayOpacity = attributes.overlayOpacity,
    justifyContent = attributes.justifyContent,
    alignItems = attributes.alignItems,
    contentMaxWidth = attributes.contentMaxWidth;
  var blockProps = useBlockProps({
    className: 'frbl-vimeo-bg',
    style: {
      position: 'relative',
      minHeight: minHeight + minHeightUnit,
      overflow: 'hidden'
    }
  });
  var innerBlocksProps = useInnerBlocksProps({
    className: 'frbl-vimeo-bg__content',
    style: _objectSpread(_objectSpread({}, contentStyle(justifyContent, alignItems, contentMaxWidth)), {}, {
      position: 'relative',
      zIndex: 2
    })
  }, {
    renderAppender: InnerBlocks.ButtonBlockAppender
  });
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Vídeo Vimeo', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(TextControl, {
    label: __('URL de Vimeo', 'frontblocks'),
    value: vimeoUrl,
    onChange: function onChange(val) {
      return setAttributes({
        vimeoUrl: val
      });
    },
    placeholder: "https://vimeo.com/123456789",
    help: __('URL del vídeo de Vimeo que se usará como fondo.', 'frontblocks')
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Dimensiones', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Altura mínima', 'frontblocks'),
    value: minHeight,
    onChange: function onChange(val) {
      return setAttributes({
        minHeight: val
      });
    },
    min: 10,
    max: minHeightUnit === 'vh' ? 100 : 1200,
    step: 1
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Unidad altura', 'frontblocks'),
    value: minHeightUnit,
    options: [{
      label: 'vh (% viewport)',
      value: 'vh'
    }, {
      label: 'px',
      value: 'px'
    }],
    onChange: function onChange(val) {
      return setAttributes({
        minHeightUnit: val
      });
    }
  }), /*#__PURE__*/React.createElement(TextControl, {
    label: __('Ancho máximo del contenido', 'frontblocks'),
    value: contentMaxWidth,
    onChange: function onChange(val) {
      return setAttributes({
        contentMaxWidth: val
      });
    },
    placeholder: "1200px / 80% / vac\xEDo = sin l\xEDmite",
    help: __('Limita el ancho del contenido interior. Vacío = ancho completo.', 'frontblocks')
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Alineación del contenido', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Horizontal', 'frontblocks'),
    value: justifyContent,
    options: CONTENT_JUSTIFY,
    onChange: function onChange(val) {
      return setAttributes({
        justifyContent: val
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Vertical', 'frontblocks'),
    value: alignItems,
    options: CONTENT_ALIGN,
    onChange: function onChange(val) {
      return setAttributes({
        alignItems: val
      });
    }
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Overlay', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Opacidad del overlay', 'frontblocks'),
    value: overlayOpacity,
    onChange: function onChange(val) {
      return setAttributes({
        overlayOpacity: val
      });
    },
    min: 0,
    max: 100,
    step: 5
  }), overlayOpacity > 0 && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: '8px',
      marginTop: '8px'
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: '12px'
    }
  }, __('Color', 'frontblocks')), /*#__PURE__*/React.createElement(Dropdown, {
    renderToggle: function renderToggle(_ref2) {
      var isOpen = _ref2.isOpen,
        onToggle = _ref2.onToggle;
      return /*#__PURE__*/React.createElement(Button, {
        onClick: onToggle,
        "aria-expanded": isOpen,
        style: {
          padding: 0,
          minWidth: 0
        }
      }, /*#__PURE__*/React.createElement(ColorIndicator, {
        colorValue: overlayColor
      }));
    },
    renderContent: function renderContent() {
      return /*#__PURE__*/React.createElement(ColorPicker, {
        color: overlayColor,
        onChange: function onChange(val) {
          return setAttributes({
            overlayColor: val
          });
        },
        enableAlpha: false
      });
    }
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: '12px',
      color: '#757575'
    }
  }, overlayColor)))), /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement("div", {
    className: "frbl-vimeo-bg__media",
    style: {
      position: 'absolute',
      inset: '0',
      overflow: 'hidden',
      zIndex: 0,
      pointerEvents: 'none'
    }
  }, function () {
    var videoId = extractVimeoId(vimeoUrl);
    if (videoId) {
      var src = 'https://player.vimeo.com/video/' + videoId + '?background=1&autoplay=1&loop=1&muted=1&title=0&byline=0&portrait=0';
      return /*#__PURE__*/React.createElement("iframe", {
        className: "frbl-vimeo-bg__iframe",
        src: src,
        frameBorder: "0",
        allow: "autoplay; fullscreen",
        title: "",
        "aria-hidden": "true"
      });
    }
    return /*#__PURE__*/React.createElement("div", {
      style: {
        position: 'absolute',
        inset: '0',
        background: 'repeating-linear-gradient(45deg,#e0e0e0 0,#e0e0e0 10px,#f5f5f5 10px,#f5f5f5 20px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }
    }, /*#__PURE__*/React.createElement("p", {
      style: {
        color: '#555',
        margin: 0,
        background: 'rgba(255,255,255,0.8)',
        padding: '12px 16px',
        borderRadius: '4px'
      }
    }, __('← Añade una URL de Vimeo en el panel lateral', 'frontblocks')));
  }()), overlayOpacity > 0 && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      inset: '0',
      backgroundColor: overlayColor,
      opacity: overlayOpacity / 100,
      zIndex: 1,
      pointerEvents: 'none'
    }
  }), /*#__PURE__*/React.createElement("div", innerBlocksProps)));
}
registerBlockType('frontblocks/vimeo-background', {
  title: __('Fondo Vimeo', 'frontblocks'),
  description: __('Sección con vídeo de Vimeo como fondo. Sin bordes negros, a pantalla completa.', 'frontblocks'),
  category: 'media',
  icon: 'format-video',
  keywords: ['vimeo', 'video', 'fondo', 'background', 'hero'],
  supports: {
    align: ['full', 'wide'],
    html: false
  },
  attributes: {
    vimeoUrl: {
      type: 'string',
      default: ''
    },
    minHeight: {
      type: 'number',
      default: 100
    },
    minHeightUnit: {
      type: 'string',
      default: 'vh'
    },
    overlayColor: {
      type: 'string',
      default: '#000000'
    },
    overlayOpacity: {
      type: 'number',
      default: 0
    },
    justifyContent: {
      type: 'string',
      default: 'stretch'
    },
    alignItems: {
      type: 'string',
      default: 'center'
    },
    contentMaxWidth: {
      type: 'string',
      default: ''
    },
    align: {
      type: 'string',
      default: 'full'
    }
  },
  edit: VimeoBackgroundEdit,
  save: function save() {
    return wp.element.createElement(InnerBlocks.Content, null);
  }
});
