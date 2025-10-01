"use strict";

// frontblocks-product-categories-option.jsx
var registerBlockType = wp.blocks.registerBlockType;
var Fragment = wp.element.Fragment;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  RangeControl = _wp$components.RangeControl,
  SelectControl = _wp$components.SelectControl,
  ToggleControl = _wp$components.ToggleControl;
var __ = wp.i18n.__;

// Componente de Edición del Bloque
function ProductCategoriesEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var count = attributes.count,
    orderby = attributes.orderby,
    order = attributes.order,
    hideEmpty = attributes.hideEmpty,
    columns = attributes.columns,
    className = attributes.className;
  var blockProps = useBlockProps({
    className: "frbl-product-categories-block ".concat(className)
  });

  // Esto se muestra en el editor de Gutenberg
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Product Categories Settings', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Number of Categories', 'frontblocks'),
    value: count,
    onChange: function onChange(newCount) {
      return setAttributes({
        count: newCount
      });
    },
    min: 1,
    max: 20
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
  }))), /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '15px',
      border: '1px solid #ccc',
      textAlign: 'center'
    }
  }, /*#__PURE__*/React.createElement("p", {
    style: {
      fontWeight: 'bold'
    }
  }, __('Product Categories Grid', 'frontblocks')), /*#__PURE__*/React.createElement("p", null, __('Showing', 'frontblocks'), " ", count, " ", __('categories in', 'frontblocks'), " ", columns, " ", __('columns.', 'frontblocks')), /*#__PURE__*/React.createElement("p", null, __('The grid will be dynamically rendered on the frontend with images and names.', 'frontblocks')))));
}

// Registro del Bloque
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
    // ¡ATRIBUTO RENOMBRADO!
    columns: {
      type: 'number',
      default: 2
    }
  },
  edit: ProductCategoriesEdit,
  save: function save() {
    return null;
  } // Usamos render_callback en PHP
});
