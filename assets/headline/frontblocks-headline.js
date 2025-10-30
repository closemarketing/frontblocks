"use strict";

var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl;
var _wp$i18n = wp.i18n,
  __ = _wp$i18n.__,
  sprintf = _wp$i18n.sprintf;
var LINE_CLASS_PREFIX = 'gb-line-effect-';
var BLOCK_NAME = 'generateblocks/text';
var withHeadlineLineControl = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    if (props.name !== BLOCK_NAME) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var existingClasses = attributes.className || '';
    var cleanExistingLineClasses = function cleanExistingLineClasses(classes) {
      return classes.split(' ').filter(function (cls) {
        return !cls.startsWith(LINE_CLASS_PREFIX);
      }).join(' ').replace(/\s{2,}/g, ' ').trim();
    };
    var currentLineStyle = 'none';
    if (existingClasses.includes(LINE_CLASS_PREFIX + 'vertical')) {
      currentLineStyle = 'vertical';
    } else if (existingClasses.includes(LINE_CLASS_PREFIX + 'horizontal')) {
      currentLineStyle = 'horizontal';
    }

    /**
    * Maneja el cambio del SelectControl y actualiza las clases CSS.
    */
    var setLineStyle = function setLineStyle(newStyle) {
      var newClasses = cleanExistingLineClasses(existingClasses);
      if (newStyle !== 'none') {
        var classToAdd = LINE_CLASS_PREFIX + newStyle;
        newClasses = (newClasses + ' ' + classToAdd).trim();
      }
      setAttributes({
        className: newClasses
      });
    };
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks - Visual Effects', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement("p", {
      style: {
        marginTop: 0,
        marginBottom: '10px'
      }
    }, /*#__PURE__*/React.createElement("small", null, __('FrontBlocks visual effect settings.', 'frontblocks'))), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Decorative Line Style', 'frontblocks'),
      value: currentLineStyle,
      options: [{
        label: __('None', 'frontblocks'),
        value: 'none'
      }, {
        label: __('Vertical Line (Right)', 'frontblocks'),
        value: 'vertical'
      }, {
        label: __('Horizontal Line (Right)', 'frontblocks'),
        value: 'horizontal'
      }],
      onChange: setLineStyle,
      help: currentLineStyle === 'none' ? __('Select a line style to add a decorative element.', 'frontblocks') : sprintf(__('Current style: %s.', 'frontblocks'), currentLineStyle.charAt(0).toUpperCase() + currentLineStyle.slice(1))
    }))));
  };
}, 'withHeadlineLineControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/headline-line-control', withHeadlineLineControl);
