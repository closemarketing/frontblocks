"use strict";

var registerBlockType = wp.blocks.registerBlockType;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  MediaUpload = _wp$blockEditor.MediaUpload,
  MediaUploadCheck = _wp$blockEditor.MediaUploadCheck,
  useBlockProps = _wp$blockEditor.useBlockProps;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl,
  RangeControl = _wp$components.RangeControl,
  Button = _wp$components.Button,
  Placeholder = _wp$components.Placeholder;
var __ = wp.i18n.__;

/**
 * Edit component for Stacked Images block.
 */
function StackedImagesEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var images = attributes.images,
    direction = attributes.direction,
    animationDuration = attributes.animationDuration,
    animationDelay = attributes.animationDelay,
    containerHeight = attributes.containerHeight;
  var blockProps = useBlockProps();
  var onSelectImages = function onSelectImages(newImages) {
    setAttributes({
      images: newImages.map(function (img) {
        return {
          id: img.id,
          url: img.url,
          alt: img.alt || ''
        };
      })
    });
  };
  var removeImage = function removeImage(indexToRemove) {
    var newImages = images.filter(function (img, index) {
      return index !== indexToRemove;
    });
    setAttributes({
      images: newImages
    });
  };
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Images', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(MediaUploadCheck, null, /*#__PURE__*/React.createElement(MediaUpload, {
    onSelect: onSelectImages,
    allowedTypes: ['image'],
    multiple: true,
    value: images.map(function (img) {
      return img.id;
    }),
    render: function render(_ref) {
      var open = _ref.open;
      return /*#__PURE__*/React.createElement(Button, {
        isPrimary: true,
        onClick: open,
        style: {
          width: '100%',
          marginBottom: '10px'
        }
      }, images.length === 0 ? __('Add Images', 'frontblocks') : __('Change Images (' + images.length + ')', 'frontblocks'));
    }
  })), images.length > 0 && /*#__PURE__*/React.createElement(Button, {
    isDestructive: true,
    onClick: function onClick() {
      return setAttributes({
        images: []
      });
    },
    style: {
      width: '100%'
    }
  }, __('Remove All Images', 'frontblocks'))), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Settings', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Animation Direction', 'frontblocks'),
    value: direction,
    options: [{
      label: __('From Bottom', 'frontblocks'),
      value: 'bottom'
    }, {
      label: __('From Top', 'frontblocks'),
      value: 'top'
    }, {
      label: __('From Left', 'frontblocks'),
      value: 'left'
    }, {
      label: __('From Right', 'frontblocks'),
      value: 'right'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        direction: value
      });
    }
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Animation Duration (ms)', 'frontblocks'),
    value: animationDuration,
    onChange: function onChange(value) {
      return setAttributes({
        animationDuration: value
      });
    },
    min: 200,
    max: 3000,
    step: 100
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Delay Between Images (ms)', 'frontblocks'),
    value: animationDelay,
    onChange: function onChange(value) {
      return setAttributes({
        animationDelay: value
      });
    },
    min: 0,
    max: 2000,
    step: 100
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Container Height (px)', 'frontblocks'),
    value: containerHeight,
    onChange: function onChange(value) {
      return setAttributes({
        containerHeight: value
      });
    },
    min: 200,
    max: 1000,
    step: 50
  }))), /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement("div", {
    className: "frbl-stacked-images-editor"
  }, images.length === 0 ? /*#__PURE__*/React.createElement(Placeholder, {
    icon: "format-gallery",
    label: __('Stacked Images', 'frontblocks'),
    instructions: __('Use the settings panel on the right to add images →', 'frontblocks')
  }) : /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "frbl-stacked-images-preview",
    style: {
      height: containerHeight + 'px',
      position: 'relative'
    }
  }, images.map(function (image, index) {
    return /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "frbl-stacked-image-preview",
      style: {
        position: 'absolute',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        zIndex: index + 1,
        transform: "rotate(".concat(Math.random() * 20 - 10, "deg)")
      }
    }, /*#__PURE__*/React.createElement("img", {
      src: image.url,
      alt: image.alt,
      style: {
        width: '100%',
        height: '100%',
        objectFit: 'contain',
        boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)'
      }
    }));
  })), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: '20px',
      textAlign: 'center',
      padding: '10px',
      background: '#f0f0f0',
      borderRadius: '4px'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      margin: '0',
      fontSize: '13px',
      color: '#666'
    }
  }, /*#__PURE__*/React.createElement("strong", null, images.length), " ", images.length === 1 ? __('image', 'frontblocks') : __('images', 'frontblocks'), " |", __(' Direction: ', 'frontblocks'), " ", /*#__PURE__*/React.createElement("strong", null, direction), " |", __(' Duration: ', 'frontblocks'), " ", /*#__PURE__*/React.createElement("strong", null, animationDuration, "ms"), " |", __(' Delay: ', 'frontblocks'), " ", /*#__PURE__*/React.createElement("strong", null, animationDelay, "ms")))))));
}

// Register the block.
registerBlockType('frontblocks/stacked-images', {
  title: __('FrontBlocks: Stacked Images', 'frontblocks'),
  description: __('Display images with stacked animation effect from different directions.', 'frontblocks'),
  category: 'media',
  icon: 'format-gallery',
  keywords: [__('images', 'frontblocks'), __('stacked', 'frontblocks'), __('animation', 'frontblocks'), __('gallery', 'frontblocks')],
  attributes: {
    images: {
      type: 'array',
      default: []
    },
    direction: {
      type: 'string',
      default: 'bottom'
    },
    animationDuration: {
      type: 'number',
      default: 1000
    },
    animationDelay: {
      type: 'number',
      default: 500
    },
    containerHeight: {
      type: 'number',
      default: 500
    }
  },
  edit: StackedImagesEdit,
  save: function save() {
    return null; // Dynamic block, render on server side.
  }
});
