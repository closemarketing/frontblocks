"use strict";

(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var _wp$blockEditor = wp.blockEditor,
    MediaUpload = _wp$blockEditor.MediaUpload,
    MediaUploadCheck = _wp$blockEditor.MediaUploadCheck,
    InspectorControls = _wp$blockEditor.InspectorControls,
    RichText = _wp$blockEditor.RichText,
    useBlockProps = _wp$blockEditor.useBlockProps;
  var _wp$components = wp.components,
    PanelBody = _wp$components.PanelBody,
    Button = _wp$components.Button,
    RangeControl = _wp$components.RangeControl,
    TextControl = _wp$components.TextControl,
    Placeholder = _wp$components.Placeholder;
  var __ = wp.i18n.__;
  function EditBeforeAfter(_ref) {
    var attributes = _ref.attributes,
      setAttributes = _ref.setAttributes;
    var beforeImageId = attributes.beforeImageId,
      beforeImageUrl = attributes.beforeImageUrl,
      afterImageId = attributes.afterImageId,
      afterImageUrl = attributes.afterImageUrl,
      beforeLabel = attributes.beforeLabel,
      afterLabel = attributes.afterLabel,
      initialPosition = attributes.initialPosition;
    var blockProps = useBlockProps({
      className: 'frbl-before-after frbl-before-after--editor',
      'data-initial-position': initialPosition
    });
    var hasImages = beforeImageUrl && afterImageUrl;
    return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
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
      render: function render(_ref2) {
        var open = _ref2.open;
        return /*#__PURE__*/React.createElement(React.Fragment, null, beforeImageUrl && /*#__PURE__*/React.createElement("img", {
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
      onChange: function onChange(val) {
        return setAttributes({
          beforeLabel: val
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
      render: function render(_ref3) {
        var open = _ref3.open;
        return /*#__PURE__*/React.createElement(React.Fragment, null, afterImageUrl && /*#__PURE__*/React.createElement("img", {
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
      onChange: function onChange(val) {
        return setAttributes({
          afterLabel: val
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
      onChange: function onChange(val) {
        return setAttributes({
          initialPosition: val
        });
      },
      min: 0,
      max: 100
    }))), !hasImages ? /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement(Placeholder, {
      icon: "image-flip-horizontal",
      label: __('Before / After', 'frontblocks'),
      instructions: __('Select a "before" image and an "after" image from the sidebar.', 'frontblocks')
    })) : /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement("div", {
      className: "frbl-before-after__after"
    }, /*#__PURE__*/React.createElement("img", {
      src: afterImageUrl,
      alt: ""
    }), /*#__PURE__*/React.createElement("span", {
      className: "frbl-before-after__label frbl-before-after__label--after"
    }, afterLabel)), /*#__PURE__*/React.createElement("div", {
      className: "frbl-before-after__before",
      style: {
        clipPath: "inset(0 ".concat(100 - initialPosition, "% 0 0)")
      }
    }, /*#__PURE__*/React.createElement("img", {
      src: beforeImageUrl,
      alt: ""
    }), /*#__PURE__*/React.createElement("span", {
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
      viewBox: "0 0 24 24",
      width: "20",
      height: "20",
      fill: "none",
      stroke: "currentColor",
      strokeWidth: "2",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }, /*#__PURE__*/React.createElement("polyline", {
      points: "15 18 9 12 15 6"
    }), /*#__PURE__*/React.createElement("polyline", {
      points: "9 18 3 12 9 6"
    }), /*#__PURE__*/React.createElement("polyline", {
      points: "15 18 21 12 15 6"
    }), /*#__PURE__*/React.createElement("polyline", {
      points: "9 18 15 12 9 6"
    }))), /*#__PURE__*/React.createElement("span", {
      className: "frbl-before-after__handle-line"
    }))));
  }
  registerBlockType('frontblocks/before-after', {
    edit: EditBeforeAfter,
    save: function save() {
      return null;
    }
  });
})();
