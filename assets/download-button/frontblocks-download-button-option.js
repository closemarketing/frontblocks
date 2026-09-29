"use strict";

var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  MediaUpload = _wp$blockEditor.MediaUpload,
  MediaUploadCheck = _wp$blockEditor.MediaUploadCheck;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl,
  Button = _wp$components.Button;
var __ = wp.i18n.__;
var BUTTON_BLOCK = 'core/button';

// Register download attributes for the native button block.
wp.hooks.addFilter('blocks.registerBlockType', 'frontblocks/add-download-attributes', function (settings, name) {
  if (BUTTON_BLOCK === name) {
    settings.attributes = Object.assign(settings.attributes || {}, {
      frblDownloadEnabled: {
        type: 'boolean',
        default: false
      },
      frblDownloadFileId: {
        type: 'number',
        default: 0
      },
      frblDownloadFileUrl: {
        type: 'string',
        default: ''
      },
      frblDownloadFileName: {
        type: 'string',
        default: ''
      }
    });
  }
  return settings;
});
var withDownloadControl = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    if (BUTTON_BLOCK !== props.name) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var isDownloadEnabled = !!attributes.frblDownloadEnabled;
    var fileId = attributes.frblDownloadFileId || 0;
    var fileUrl = attributes.frblDownloadFileUrl || '';
    var fileName = attributes.frblDownloadFileName || '';
    var setDownloadEnabled = function setDownloadEnabled(enabled) {
      if (enabled) {
        setAttributes({
          frblDownloadEnabled: true
        });
        return;
      }
      var newAttributes = {
        frblDownloadEnabled: false,
        frblDownloadFileId: 0,
        frblDownloadFileUrl: '',
        frblDownloadFileName: ''
      };

      // Clear the button URL only when it still points to the download file.
      if (fileUrl && attributes.url === fileUrl) {
        newAttributes.url = undefined;
      }
      setAttributes(newAttributes);
    };
    var onSelectFile = function onSelectFile(media) {
      if (!media || !media.url) {
        return;
      }
      var mediaFileName = media.filename || media.url.split('/').pop();
      setAttributes({
        frblDownloadFileId: media.id || 0,
        frblDownloadFileUrl: media.url,
        frblDownloadFileName: mediaFileName,
        url: media.url
      });
    };
    var onRemoveFile = function onRemoveFile() {
      var newAttributes = {
        frblDownloadFileId: 0,
        frblDownloadFileUrl: '',
        frblDownloadFileName: ''
      };
      if (fileUrl && attributes.url === fileUrl) {
        newAttributes.url = undefined;
      }
      setAttributes(newAttributes);
    };
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks - Download', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Download file on click', 'frontblocks'),
      checked: isDownloadEnabled,
      onChange: setDownloadEnabled,
      help: isDownloadEnabled ? __('The button will download the selected file instead of opening a URL.', 'frontblocks') : __('Enable to make this button download a file.', 'frontblocks')
    }), isDownloadEnabled && /*#__PURE__*/React.createElement(MediaUploadCheck, null, /*#__PURE__*/React.createElement(MediaUpload, {
      onSelect: onSelectFile,
      value: fileId,
      render: function render(_ref) {
        var open = _ref.open;
        return /*#__PURE__*/React.createElement("div", null, fileName && /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Selected file:', 'frontblocks')), /*#__PURE__*/React.createElement("br", null), fileName), /*#__PURE__*/React.createElement(Button, {
          variant: "primary",
          onClick: open
        }, fileUrl ? __('Replace file', 'frontblocks') : __('Select or upload file', 'frontblocks')), fileUrl && /*#__PURE__*/React.createElement(Button, {
          variant: "secondary",
          onClick: onRemoveFile,
          style: {
            marginLeft: '8px'
          }
        }, __('Remove file', 'frontblocks')));
      }
    })))));
  };
}, 'withDownloadControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/download-button-control', withDownloadControl);
