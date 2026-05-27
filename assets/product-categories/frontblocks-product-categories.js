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
  useBlockProps = _wp$blockEditor.useBlockProps;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  RangeControl = _wp$components.RangeControl,
  SelectControl = _wp$components.SelectControl,
  ToggleControl = _wp$components.ToggleControl,
  TextControl = _wp$components.TextControl,
  ColorPicker = _wp$components.ColorPicker,
  ColorIndicator = _wp$components.ColorIndicator,
  Dropdown = _wp$components.Dropdown,
  Button = _wp$components.Button,
  TabPanel = _wp$components.TabPanel,
  Spinner = _wp$components.Spinner;
var __ = wp.i18n.__;
var apiFetch = wp.apiFetch;
function CompactColorPicker(_ref) {
  var label = _ref.label,
    color = _ref.color,
    onChangeComplete = _ref.onChangeComplete,
    enableAlpha = _ref.enableAlpha;
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      marginBottom: '12px'
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: '12px'
    }
  }, label), /*#__PURE__*/React.createElement(Dropdown, {
    renderToggle: function renderToggle(_ref2) {
      var isOpen = _ref2.isOpen,
        onToggle = _ref2.onToggle;
      return /*#__PURE__*/React.createElement(Button, {
        onClick: onToggle,
        "aria-expanded": isOpen,
        style: {
          padding: 0,
          minWidth: 0,
          background: 'none',
          border: 'none',
          boxShadow: 'none'
        }
      }, /*#__PURE__*/React.createElement(ColorIndicator, {
        colorValue: color || 'transparent'
      }));
    },
    renderContent: function renderContent() {
      return /*#__PURE__*/React.createElement("div", {
        style: {
          padding: '8px'
        }
      }, /*#__PURE__*/React.createElement(ColorPicker, {
        color: color,
        onChangeComplete: onChangeComplete,
        disableAlpha: !enableAlpha
      }));
    }
  }));
}
function ProductCategoriesEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var count = attributes.count,
    orderby = attributes.orderby,
    order = attributes.order,
    hideEmpty = attributes.hideEmpty,
    showCount = attributes.showCount,
    columns = attributes.columns,
    bgColor = attributes.bgColor,
    borderColor = attributes.borderColor,
    borderWidth = attributes.borderWidth,
    borderRadius = attributes.borderRadius,
    textColor = attributes.textColor,
    hoverBgColor = attributes.hoverBgColor,
    hoverBorderColor = attributes.hoverBorderColor,
    hoverTextColor = attributes.hoverTextColor,
    showButton = attributes.showButton,
    buttonText = attributes.buttonText,
    btnBgColor = attributes.btnBgColor,
    btnTextColor = attributes.btnTextColor,
    btnBorderColor = attributes.btnBorderColor,
    btnBorderWidth = attributes.btnBorderWidth,
    btnBorderRadius = attributes.btnBorderRadius,
    btnFontSize = attributes.btnFontSize,
    btnPaddingV = attributes.btnPaddingV,
    btnPaddingH = attributes.btnPaddingH,
    btnHoverBgColor = attributes.btnHoverBgColor,
    btnHoverTextColor = attributes.btnHoverTextColor,
    btnHoverBorderColor = attributes.btnHoverBorderColor,
    className = attributes.className;
  var _useState = useState([]),
    _useState2 = _slicedToArray(_useState, 2),
    categories = _useState2[0],
    setCategories = _useState2[1];
  var _useState3 = useState(true),
    _useState4 = _slicedToArray(_useState3, 2),
    isLoading = _useState4[0],
    setIsLoading = _useState4[1];
  var blockProps = useBlockProps({
    className: "frbl-product-categories-block ".concat(className)
  });
  var countHelpText = count === 999 ? __('Currently set to show ALL categories.', 'frontblocks') : __('Number of categories shown. Set to 999 to show all.', 'frontblocks');
  var countLabel = count === 999 ? __('Number of Categories (All)', 'frontblocks') : __('Number of Categories', 'frontblocks');

  // Load categories from API.
  useEffect(function () {
    setIsLoading(true);
    var queryLimit = count === 999 ? 100 : count;
    var orderParam = order.toLowerCase();

    // Build the API path.
    var apiPath = "/wp/v2/product_cat?per_page=".concat(queryLimit, "&orderby=").concat(orderby, "&order=").concat(orderParam, "&hide_empty=").concat(hideEmpty, "&_fields=id,name,slug,count,category_image");
    apiFetch({
      path: apiPath
    }).then(function (data) {
      setCategories(data);
      setIsLoading(false);
    }).catch(function (error) {
      console.error('FrontBlocks: Error loading categories:', error);
      setCategories([]);
      setIsLoading(false);
    });
  }, [count, orderby, order, hideEmpty]);
  var styleVars = {
    '--frbl-grid-columns': columns,
    '--frbl-bg-color': bgColor,
    '--frbl-border-color': borderColor,
    '--frbl-border-width': "".concat(borderWidth, "px"),
    '--frbl-text-color': textColor,
    '--frbl-hover-bg-color': hoverBgColor,
    '--frbl-hover-border-color': hoverBorderColor,
    '--frbl-hover-text-color': hoverTextColor,
    '--frbl-border-radius': "".concat(borderRadius, "px"),
    '--frbl-btn-bg': btnBgColor || 'transparent',
    '--frbl-btn-color': btnTextColor || 'currentColor',
    '--frbl-btn-border-color': btnBorderColor || 'currentColor',
    '--frbl-btn-border-width': "".concat(btnBorderWidth, "px"),
    '--frbl-btn-border-radius': "".concat(btnBorderRadius, "px"),
    '--frbl-btn-font-size': "".concat(btnFontSize, "px"),
    '--frbl-btn-padding-v': "".concat(btnPaddingV, "px"),
    '--frbl-btn-padding-h': "".concat(btnPaddingH, "px"),
    '--frbl-btn-hover-bg': btnHoverBgColor || 'transparent',
    '--frbl-btn-hover-color': btnHoverTextColor || 'currentColor',
    '--frbl-btn-hover-border-color': btnHoverBorderColor || 'currentColor'
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
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Show Product Count', 'frontblocks'),
    checked: showCount,
    onChange: function onChange(newShowCount) {
      return setAttributes({
        showCount: newShowCount
      });
    },
    help: __('Display the number of products in each category.', 'frontblocks')
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Show Button', 'frontblocks'),
    checked: showButton,
    onChange: function onChange(val) {
      return setAttributes({
        showButton: val
      });
    },
    help: __('Display a button at the bottom of each category card.', 'frontblocks')
  }), showButton && /*#__PURE__*/React.createElement(TextControl, {
    label: __('Button Text', 'frontblocks'),
    value: buttonText,
    onChange: function onChange(val) {
      return setAttributes({
        buttonText: val
      });
    },
    placeholder: __('Shop now', 'frontblocks')
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
      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Background Color', 'frontblocks'),
        color: bgColor,
        enableAlpha: true,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            bgColor: value.rgb ? "rgba(".concat(value.rgb.r, ", ").concat(value.rgb.g, ", ").concat(value.rgb.b, ", ").concat(value.rgb.a, ")") : value.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Border Color', 'frontblocks'),
        color: borderColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            borderColor: value.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Text Color', 'frontblocks'),
        color: textColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            textColor: value.hex
          });
        }
      }));
    }
    if (tab.name === 'hover') {
      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Hover Background Color', 'frontblocks'),
        color: hoverBgColor,
        enableAlpha: true,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            hoverBgColor: value.rgb ? "rgba(".concat(value.rgb.r, ", ").concat(value.rgb.g, ", ").concat(value.rgb.b, ", ").concat(value.rgb.a, ")") : value.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Hover Border Color', 'frontblocks'),
        color: hoverBorderColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            hoverBorderColor: value.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Hover Text Color', 'frontblocks'),
        color: hoverTextColor,
        onChangeComplete: function onChangeComplete(value) {
          return setAttributes({
            hoverTextColor: value.hex
          });
        }
      }));
    }
  })), showButton && /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Button Style', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Font Size (px)', 'frontblocks'),
    value: btnFontSize,
    onChange: function onChange(v) {
      return setAttributes({
        btnFontSize: v
      });
    },
    min: 10,
    max: 40
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Padding Vertical (px)', 'frontblocks'),
    value: btnPaddingV,
    onChange: function onChange(v) {
      return setAttributes({
        btnPaddingV: v
      });
    },
    min: 0,
    max: 60
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Padding Horizontal (px)', 'frontblocks'),
    value: btnPaddingH,
    onChange: function onChange(v) {
      return setAttributes({
        btnPaddingH: v
      });
    },
    min: 0,
    max: 100
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Border Width (px)', 'frontblocks'),
    value: btnBorderWidth,
    onChange: function onChange(v) {
      return setAttributes({
        btnBorderWidth: v
      });
    },
    min: 0,
    max: 10
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Border Radius (px)', 'frontblocks'),
    value: btnBorderRadius,
    onChange: function onChange(v) {
      return setAttributes({
        btnBorderRadius: v
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
      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Background', 'frontblocks'),
        color: btnBgColor,
        enableAlpha: true,
        onChangeComplete: function onChangeComplete(v) {
          return setAttributes({
            btnBgColor: v.rgb ? "rgba(".concat(v.rgb.r, ",").concat(v.rgb.g, ",").concat(v.rgb.b, ",").concat(v.rgb.a, ")") : v.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Text Color', 'frontblocks'),
        color: btnTextColor,
        onChangeComplete: function onChangeComplete(v) {
          return setAttributes({
            btnTextColor: v.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Border Color', 'frontblocks'),
        color: btnBorderColor,
        onChangeComplete: function onChangeComplete(v) {
          return setAttributes({
            btnBorderColor: v.hex
          });
        }
      }));
    }
    if (tab.name === 'hover') {
      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Background', 'frontblocks'),
        color: btnHoverBgColor,
        enableAlpha: true,
        onChangeComplete: function onChangeComplete(v) {
          return setAttributes({
            btnHoverBgColor: v.rgb ? "rgba(".concat(v.rgb.r, ",").concat(v.rgb.g, ",").concat(v.rgb.b, ",").concat(v.rgb.a, ")") : v.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Text Color', 'frontblocks'),
        color: btnHoverTextColor,
        onChangeComplete: function onChangeComplete(v) {
          return setAttributes({
            btnHoverTextColor: v.hex
          });
        }
      }), /*#__PURE__*/React.createElement(CompactColorPicker, {
        label: __('Border Color', 'frontblocks'),
        color: btnHoverBorderColor,
        onChangeComplete: function onChangeComplete(v) {
          return setAttributes({
            btnHoverBorderColor: v.hex
          });
        }
      }));
    }
  }))), /*#__PURE__*/React.createElement("div", blockProps, isLoading ? /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      padding: '40px'
    }
  }, /*#__PURE__*/React.createElement(Spinner, null), /*#__PURE__*/React.createElement("p", null, __('Loading categories...', 'frontblocks'))) : categories.length === 0 ? /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '20px',
      border: '2px dashed #ccc',
      borderRadius: '8px',
      textAlign: 'center',
      color: '#666'
    }
  }, /*#__PURE__*/React.createElement("p", null, __('No product categories found.', 'frontblocks')), /*#__PURE__*/React.createElement("p", {
    style: {
      fontSize: '14px'
    }
  }, __('Make sure WooCommerce is active and you have product categories.', 'frontblocks'))) : /*#__PURE__*/React.createElement("div", {
    className: "frbl-product-categories-grid",
    style: styleVars
  }, categories.map(function (category) {
    var imageUrl = category.category_image && category.category_image.src ? category.category_image.src : 'https://placehold.co/600x400/eeeeee/333333?text=Product+Category';
    return /*#__PURE__*/React.createElement("div", {
      key: category.id,
      className: "frbl-category-item frbl-category-".concat(category.slug)
    }, /*#__PURE__*/React.createElement("div", {
      className: "frbl-category-link"
    }, /*#__PURE__*/React.createElement("div", {
      className: "frbl-category-image-wrap"
    }, /*#__PURE__*/React.createElement("img", {
      src: imageUrl,
      alt: category.name,
      className: "frbl-category-image"
    })), /*#__PURE__*/React.createElement("h3", {
      className: "frbl-category-name"
    }, category.name, showCount && " (".concat(category.count, ")")), showButton && /*#__PURE__*/React.createElement("span", {
      className: "frbl-category-button"
    }, buttonText || __('Shop now', 'frontblocks'))));
  }))));
}
registerBlockType('frontblocks/product-categories', {
  title: __('Product Categories', 'frontblocks'),
  description: __('Display a list of WooCommerce Product Categories.', 'frontblocks'),
  category: 'woocommerce',
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
      default: false
    },
    showCount: {
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
    },
    showButton: {
      type: 'boolean',
      default: false
    },
    buttonText: {
      type: 'string',
      default: ''
    },
    btnBgColor: {
      type: 'string',
      default: 'transparent'
    },
    btnTextColor: {
      type: 'string',
      default: ''
    },
    btnBorderColor: {
      type: 'string',
      default: ''
    },
    btnBorderWidth: {
      type: 'number',
      default: 2
    },
    btnBorderRadius: {
      type: 'number',
      default: 4
    },
    btnFontSize: {
      type: 'number',
      default: 14
    },
    btnPaddingV: {
      type: 'number',
      default: 10
    },
    btnPaddingH: {
      type: 'number',
      default: 20
    },
    btnHoverBgColor: {
      type: 'string',
      default: ''
    },
    btnHoverTextColor: {
      type: 'string',
      default: ''
    },
    btnHoverBorderColor: {
      type: 'string',
      default: ''
    }
  },
  edit: ProductCategoriesEdit,
  save: function save() {
    return null;
  }
});
