"use strict";

var registerBlockType = wp.blocks.registerBlockType;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  InnerBlocks = _wp$blockEditor.InnerBlocks,
  useBlockProps = _wp$blockEditor.useBlockProps,
  useInnerBlocksProps = _wp$blockEditor.useInnerBlocksProps;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  TextControl = _wp$components.TextControl,
  RangeControl = _wp$components.RangeControl,
  SelectControl = _wp$components.SelectControl,
  ColorPicker = _wp$components.ColorPicker,
  Divider = _wp$components.__experimentalDivider;
var __ = wp.i18n.__;
function VimeoBackgroundEdit(_ref) {
  var attributes = _ref.attributes,
    setAttributes = _ref.setAttributes;
  var vimeoUrl = attributes.vimeoUrl,
    minHeight = attributes.minHeight,
    minHeightUnit = attributes.minHeightUnit,
    overlayColor = attributes.overlayColor,
    overlayOpacity = attributes.overlayOpacity;
  var blockProps = useBlockProps({
    className: 'frbl-vimeo-bg',
    style: {
      position: 'relative',
      minHeight: minHeight + minHeightUnit,
      overflow: 'hidden'
    }
  });
  var innerBlocksProps = useInnerBlocksProps({
    className: 'frbl-vimeo-bg__content'
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
    title: __('Altura', 'frontblocks'),
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
    label: __('Unidad', 'frontblocks'),
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
  }), overlayOpacity > 0 && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("p", {
    style: {
      marginBottom: '8px',
      fontSize: '12px'
    }
  }, __('Color del overlay', 'frontblocks')), /*#__PURE__*/React.createElement(ColorPicker, {
    color: overlayColor,
    onChange: function onChange(val) {
      return setAttributes({
        overlayColor: val
      });
    },
    enableAlpha: false
  })))), /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement("div", {
    className: "frbl-vimeo-bg__media",
    style: {
      position: 'absolute',
      inset: '0',
      background: 'repeating-linear-gradient(45deg,#e0e0e0 0,#e0e0e0 10px,#f5f5f5 10px,#f5f5f5 20px)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 0
    }
  }, vimeoUrl ? /*#__PURE__*/React.createElement("p", {
    style: {
      color: '#333',
      margin: 0,
      fontSize: '13px',
      padding: '20px',
      textAlign: 'center',
      background: 'rgba(255,255,255,0.7)',
      borderRadius: '4px'
    }
  }, "\uD83C\uDFAC ", vimeoUrl, /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("small", null, __('El vídeo se renderiza en el frontend', 'frontblocks'))) : /*#__PURE__*/React.createElement("p", {
    style: {
      color: '#555',
      margin: 0,
      background: 'rgba(255,255,255,0.7)',
      padding: '12px 16px',
      borderRadius: '4px'
    }
  }, __('← Añade una URL de Vimeo en el panel lateral', 'frontblocks'))), overlayOpacity > 0 && /*#__PURE__*/React.createElement("div", {
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
    align: {
      type: 'string',
      default: 'full'
    }
  },
  edit: VimeoBackgroundEdit,
  save: function save() {
    return null;
  }
});
