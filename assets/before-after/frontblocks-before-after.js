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
  RangeControl = _wp$components.RangeControl,
  Button = _wp$components.Button,
  Placeholder = _wp$components.Placeholder,
  TextControl = _wp$components.TextControl,
  ToggleControl = _wp$components.ToggleControl;
var __ = wp.i18n.__;

/**
 * Edit component for Before After block.
 */
function BeforeAfterEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var beforeImageId = attributes.beforeImageId,
    beforeImageUrl = attributes.beforeImageUrl,
    afterImageId = attributes.afterImageId,
    afterImageUrl = attributes.afterImageUrl,
    beforeLabel = attributes.beforeLabel,
    afterLabel = attributes.afterLabel,
    initialPosition = attributes.initialPosition,
    blockHeight = attributes.blockHeight,
    fixedHeight = attributes.fixedHeight;
  var blockProps = useBlockProps();
  var hasImages = beforeImageUrl && afterImageUrl;
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Before Image', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(MediaUploadCheck, null, /*#__PURE__*/React.createElement(MediaUpload, {
    onSelect: function onSelect(media) {
      return setAttributes({
        beforeImageId: media.id,
        beforeImageUrl: media.url
      });
    },
    allowedTypes: ['image'],
    value: beforeImageId,
    render: function render(_ref) {
      var open = _ref.open;
      return /*#__PURE__*/React.createElement("div", null, beforeImageUrl && /*#__PURE__*/React.createElement("img", {
        src: beforeImageUrl,
        alt: "",
        style: {
          width: '100%',
          marginBottom: '8px',
          borderRadius: '2px'
        }
      }), /*#__PURE__*/React.createElement(Button, {
        onClick: open,
        variant: beforeImageUrl ? 'secondary' : 'primary',
        style: {
          width: '100%'
        }
      }, beforeImageUrl ? __('Replace Before Image', 'frontblocks') : __('Select Before Image', 'frontblocks')), beforeImageUrl && /*#__PURE__*/React.createElement(Button, {
        onClick: function onClick() {
          return setAttributes({
            beforeImageId: undefined,
            beforeImageUrl: ''
          });
        },
        variant: "link",
        isDestructive: true,
        style: {
          marginTop: '4px',
          display: 'block'
        }
      }, __('Remove', 'frontblocks')));
    }
  })), /*#__PURE__*/React.createElement(TextControl, {
    label: __('Before Label', 'frontblocks'),
    value: beforeLabel,
    onChange: function onChange(value) {
      return setAttributes({
        beforeLabel: value
      });
    },
    style: {
      marginTop: '12px'
    }
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('After Image', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(MediaUploadCheck, null, /*#__PURE__*/React.createElement(MediaUpload, {
    onSelect: function onSelect(media) {
      return setAttributes({
        afterImageId: media.id,
        afterImageUrl: media.url
      });
    },
    allowedTypes: ['image'],
    value: afterImageId,
    render: function render(_ref2) {
      var open = _ref2.open;
      return /*#__PURE__*/React.createElement("div", null, afterImageUrl && /*#__PURE__*/React.createElement("img", {
        src: afterImageUrl,
        alt: "",
        style: {
          width: '100%',
          marginBottom: '8px',
          borderRadius: '2px'
        }
      }), /*#__PURE__*/React.createElement(Button, {
        onClick: open,
        variant: afterImageUrl ? 'secondary' : 'primary',
        style: {
          width: '100%'
        }
      }, afterImageUrl ? __('Replace After Image', 'frontblocks') : __('Select After Image', 'frontblocks')), afterImageUrl && /*#__PURE__*/React.createElement(Button, {
        onClick: function onClick() {
          return setAttributes({
            afterImageId: undefined,
            afterImageUrl: ''
          });
        },
        variant: "link",
        isDestructive: true,
        style: {
          marginTop: '4px',
          display: 'block'
        }
      }, __('Remove', 'frontblocks')));
    }
  })), /*#__PURE__*/React.createElement(TextControl, {
    label: __('After Label', 'frontblocks'),
    value: afterLabel,
    onChange: function onChange(value) {
      return setAttributes({
        afterLabel: value
      });
    },
    style: {
      marginTop: '12px'
    }
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Slider Settings', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Initial Handle Position (%)', 'frontblocks'),
    value: initialPosition,
    onChange: function onChange(value) {
      return setAttributes({
        initialPosition: value
      });
    },
    min: 0,
    max: 100
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Fixed Height', 'frontblocks'),
    checked: fixedHeight,
    onChange: function onChange(value) {
      return setAttributes({
        fixedHeight: value
      });
    }
  }), fixedHeight && /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Height (px)', 'frontblocks'),
    value: blockHeight,
    onChange: function onChange(value) {
      return setAttributes({
        blockHeight: value
      });
    },
    min: 100,
    max: 1000,
    step: 10
  }))), /*#__PURE__*/React.createElement("div", blockProps, !hasImages ? /*#__PURE__*/React.createElement(Placeholder, {
    icon: "image-flip-horizontal",
    label: __('Before / After', 'frontblocks'),
    instructions: __('Select a "before" image and an "after" image from the sidebar.', 'frontblocks')
  }) : /*#__PURE__*/React.createElement("div", {
    className: 'frbl-before-after frbl-before-after--editor' + (fixedHeight ? ' frbl-before-after--fixed-height' : ''),
    "data-initial-position": initialPosition,
    style: fixedHeight && blockHeight ? {
      height: blockHeight + 'px'
    } : {}
  }, /*#__PURE__*/React.createElement("div", {
    className: "frbl-before-after__after"
  }, /*#__PURE__*/React.createElement("img", {
    src: afterImageUrl,
    alt: ""
  }), afterLabel && /*#__PURE__*/React.createElement("span", {
    className: "frbl-before-after__label frbl-before-after__label--after"
  }, afterLabel)), /*#__PURE__*/React.createElement("div", {
    className: "frbl-before-after__before",
    style: {
      clipPath: "inset(0 ".concat(100 - initialPosition, "% 0 0)")
    }
  }, /*#__PURE__*/React.createElement("img", {
    src: beforeImageUrl,
    alt: ""
  }), beforeLabel && /*#__PURE__*/React.createElement("span", {
    className: "frbl-before-after__label frbl-before-after__label--before"
  }, beforeLabel)), /*#__PURE__*/React.createElement("div", {
    className: "frbl-before-after__handle",
    style: {
      left: "".concat(initialPosition, "%")
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "frbl-before-after__handle-line"
  }), /*#__PURE__*/React.createElement("span", {
    className: "frbl-before-after__handle-thumb"
  }, /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 20 20",
    width: "18",
    height: "18",
    fill: "currentColor",
    "aria-hidden": "true"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M7 4l-4 6 4 6M13 4l4 6-4 6",
    stroke: "currentColor",
    strokeWidth: "2",
    fill: "none",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  }))), /*#__PURE__*/React.createElement("span", {
    className: "frbl-before-after__handle-line"
  })))));
}

// Register the block.
registerBlockType('frontblocks/before-after', {
  title: __('FrontBlocks: Before After', 'frontblocks'),
  description: __('Compare two images with a draggable before/after slider.', 'frontblocks'),
  category: 'media',
  icon: 'image-flip-horizontal',
  keywords: [__('before', 'frontblocks'), __('after', 'frontblocks'), __('comparison', 'frontblocks'), __('slider', 'frontblocks')],
  attributes: {
    beforeImageId: {
      type: 'integer'
    },
    beforeImageUrl: {
      type: 'string',
      default: ''
    },
    afterImageId: {
      type: 'integer'
    },
    afterImageUrl: {
      type: 'string',
      default: ''
    },
    beforeLabel: {
      type: 'string',
      default: 'Before'
    },
    afterLabel: {
      type: 'string',
      default: 'After'
    },
    initialPosition: {
      type: 'number',
      default: 50
    },
    fixedHeight: {
      type: 'boolean',
      default: false
    },
    blockHeight: {
      type: 'number',
      default: 400
    }
  },
  edit: BeforeAfterEdit,
  save: function save() {
    return null; // Dynamic block, rendered server-side.
  }
});
