"use strict";

var registerBlockType = wp.blocks.registerBlockType;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  RangeControl = _wp$components.RangeControl,
  SelectControl = _wp$components.SelectControl,
  ToggleControl = _wp$components.ToggleControl,
  ColorPicker = _wp$components.ColorPicker,
  TabPanel = _wp$components.TabPanel;
var __ = wp.i18n.__;
function ProductCategoriesEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var count = attributes.count,
    orderby = attributes.orderby,
    order = attributes.order,
    hideEmpty = attributes.hideEmpty,
    columns = attributes.columns,
    bgColor = attributes.bgColor,
    borderColor = attributes.borderColor,
    borderWidth = attributes.borderWidth,
    borderRadius = attributes.borderRadius,
    textColor = attributes.textColor,
    hoverBgColor = attributes.hoverBgColor,
    hoverBorderColor = attributes.hoverBorderColor,
    hoverTextColor = attributes.hoverTextColor,
    className = attributes.className;
  var blockProps = useBlockProps({
    className: "frbl-product-categories-block ".concat(className)
  });
  var wrapperStyle = {
    padding: '30px',
    border: "".concat(borderWidth, "px solid ").concat(borderColor),
    borderRadius: "".concat(borderRadius, "px"),
    backgroundColor: bgColor,
    textAlign: 'center',
    color: textColor,
    lineHeight: '1.5em'
  };
  var countHelpText = count === 999 ? __('Currently set to show ALL categories.', 'frontblocks') : __('Number of categories shown. Set to 999 to show all.', 'frontblocks');
  var countLabel = count === 999 ? __('Number of Categories (All)', 'frontblocks') : __('Number of Categories', 'frontblocks');
  var colorPickerCompactStyle = {
    maxWidth: '250px'
  };
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Product Categories Settings', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: countLabel,
    value: count,
    onChange: function onChange(newCount) {
      return setAttributes({
        count: newCount
      });
    },
    min: 1,
    max: 999,
    help: countHelpText
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Number of Columns', 'frontblocks'),
    value: columns,
    onChange: function onChange(newColumns) {
      return setAttributes({
        columns: newColumns
      });
    },
    min: 1,
    max: 6,
    help: __('Number of categories shown per row.', 'frontblocks')
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Order By', 'frontblocks'),
    value: orderby,
    options: [{
      label: __('Count', 'frontblocks'),
      value: 'count'
    }, {
      label: __('Name', 'frontblocks'),
      value: 'name'
    }, {
      label: __('ID', 'frontblocks'),
      value: 'id'
    }, {
      label: __('Slug', 'frontblocks'),
      value: 'slug'
    }],
    onChange: function onChange(newOrderby) {
      return setAttributes({
        orderby: newOrderby
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Order', 'frontblocks'),
    value: order,
    options: [{
      label: __('Descending (DESC)', 'frontblocks'),
      value: 'DESC'
    }, {
      label: __('Ascending (ASC)', 'frontblocks'),
      value: 'ASC'
    }],
    onChange: function onChange(newOrder) {
      return setAttributes({
        order: newOrder
      });
    }
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Hide Empty Categories', 'frontblocks'),
    checked: hideEmpty,
    onChange: function onChange(newHideEmpty) {
      return setAttributes({
        hideEmpty: newHideEmpty
      });
    }
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Card Style Settings', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Border Width (px)', 'frontblocks'),
    value: borderWidth,
    onChange: function onChange(value) {
      return setAttributes({
        borderWidth: value
      });
    },
    min: 0,
    max: 10
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Border Radius (px)', 'frontblocks') // 🚨 NUEVO CONTROL
    ,
    value: borderRadius,
    onChange: function onChange(value) {
      return setAttributes({
        borderRadius: value
      });
    },
    min: 0,
    max: 50
  }), /*#__PURE__*/React.createElement(TabPanel, {
    className: "frbl-style-tabs",
    tabs: [{
      name: 'normal',
      title: __('Normal', 'frontblocks'),
      className: 'tab-normal'
    }, {
      name: 'hover',
      title: __('Hover', 'frontblocks'),
      className: 'tab-hover'
    }]
  }, function (tab) {
    if (tab.name === 'normal') {
      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("h4", {
        style: {
          marginTop: '15px'
        }
      }, __('Background Color', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
        style: colorPickerCompactStyle
      }, /*#__PURE__*/React.createElement(ColorPicker, {
        color: bgColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            bgColor: value.rgb ? "rgba(".concat(value.rgb.r, ", ").concat(value.rgb.g, ", ").concat(value.rgb.b, ", ").concat(value.rgb.a, ")") : value.hex
          });
        },
        disableAlpha: false
      })), /*#__PURE__*/React.createElement("h4", {
        style: {
          marginTop: '15px'
        }
      }, __('Border Color', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
        style: colorPickerCompactStyle
      }, /*#__PURE__*/React.createElement(ColorPicker, {
        color: borderColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            borderColor: value.hex
          });
        }
      })), /*#__PURE__*/React.createElement("h4", {
        style: {
          marginTop: '15px'
        }
      }, __('Text Color', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
        style: colorPickerCompactStyle
      }, /*#__PURE__*/React.createElement(ColorPicker, {
        color: textColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            textColor: value.hex
          });
        }
      })));
    }
    if (tab.name === 'hover') {
      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("h4", {
        style: {
          marginTop: '15px'
        }
      }, __('Hover Background Color', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
        style: colorPickerCompactStyle
      }, /*#__PURE__*/React.createElement(ColorPicker, {
        color: hoverBgColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            hoverBgColor: value.rgb ? "rgba(".concat(value.rgb.r, ", ").concat(value.rgb.g, ", ").concat(value.rgb.b, ", ").concat(value.rgb.a, ")") : value.hex
          });
        },
        disableAlpha: false
      })), /*#__PURE__*/React.createElement("h4", {
        style: {
          marginTop: '15px'
        }
      }, __('Hover Border Color', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
        style: colorPickerCompactStyle
      }, /*#__PURE__*/React.createElement(ColorPicker, {
        color: hoverBorderColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            hoverBorderColor: value.hex
          });
        }
      })), /*#__PURE__*/React.createElement("h4", {
        style: {
          marginTop: '15px'
        }
      }, __('Hover Text Color', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
        style: colorPickerCompactStyle
      }, /*#__PURE__*/React.createElement(ColorPicker, {
        color: hoverTextColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            hoverTextColor: value.hex
          });
        }
      })));
    }
  }))), /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement("div", {
    style: wrapperStyle
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontWeight: 'bold'
    }
  }, __('Product Categories Grid', 'frontblocks')), /*#__PURE__*/React.createElement("p", null, __('Showing', 'frontblocks'), " ", count === 999 ? __('All', 'frontblocks') : count, " ", __('categories in', 'frontblocks'), " ", columns, " ", __('columns.', 'frontblocks')), /*#__PURE__*/React.createElement("p", {
    style: {
      color: textColor
    }
  }, __('The background, border, and text colors are previewed here.', 'frontblocks')))));
}
registerBlockType('frontblocks/product-categories', {
  title: __('Product Categories', 'frontblocks'),
  description: __('Display a list of WooCommerce Product Categories.', 'frontblocks'),
  category: 'generateblocks',
  icon: 'store',
  keywords: [__('woo', 'frontblocks'), __('categories', 'frontblocks'), __('products', 'frontblocks'), __('grid', 'frontblocks')],
  attributes: {
    count: {
      type: 'number',
      default: 5
    },
    orderby: {
      type: 'string',
      default: 'count'
    },
    order: {
      type: 'string',
      default: 'DESC'
    },
    hideEmpty: {
      type: 'boolean',
      default: true
    },
    className: {
      type: 'string',
      default: ''
    },
    columns: {
      type: 'number',
      default: 2
    },
    bgColor: {
      type: 'string',
      default: 'rgba(255, 255, 255, 0.5)'
    },
    borderColor: {
      type: 'string',
      default: '#dddddd'
    },
    borderWidth: {
      type: 'number',
      default: 1
    },
    borderRadius: {
      type: 'number',
      default: 20
    },
    textColor: {
      type: 'string',
      default: 'inherit'
    },
    hoverBgColor: {
      type: 'string',
      default: 'rgba(255, 255, 255, 0.7)'
    },
    hoverBorderColor: {
      type: 'string',
      default: '#555555'
    },
    hoverTextColor: {
      type: 'string',
      default: 'inherit'
    }
  },
  edit: ProductCategoriesEdit,
  save: function save() {
    return null;
  }
});
