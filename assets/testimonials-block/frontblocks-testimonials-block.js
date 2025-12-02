"use strict";

/**
 * FrontBlocks Testimonials Block
 *
 * A fully customizable testimonials carousel block
 */

var registerBlockType = wp.blocks.registerBlockType;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  RangeControl = _wp$components.RangeControl,
  SelectControl = _wp$components.SelectControl,
  ToggleControl = _wp$components.ToggleControl,
  ColorPalette = _wp$components.ColorPalette;
var __ = wp.i18n.__;
var useSelect = wp.data.useSelect;
var RawHTML = wp.element.RawHTML;
registerBlockType('frontblocks/testimonials', {
  title: __('FrontBlocks Testimonials', 'frontblocks'),
  description: __('Display testimonials in a customizable carousel', 'frontblocks'),
  category: 'widgets',
  icon: 'testimonial',
  attributes: {
    numberOfTestimonials: {
      type: 'number',
      default: -1
    },
    itemsToView: {
      type: 'number',
      default: 3
    },
    itemsToViewLaptop: {
      type: 'number',
      default: 2
    },
    itemsToViewTablet: {
      type: 'number',
      default: 2
    },
    itemsToViewMobile: {
      type: 'number',
      default: 1
    },
    autoplay: {
      type: 'number',
      default: 6000
    },
    navigationStyle: {
      type: 'string',
      default: 'bullets'
    },
    showStars: {
      type: 'boolean',
      default: true
    },
    showImage: {
      type: 'boolean',
      default: true
    },
    buttonColor: {
      type: 'string',
      default: '#000000'
    },
    buttonBgColor: {
      type: 'string',
      default: 'transparent'
    }
  },
  edit: function edit(props) {
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var numberOfTestimonials = attributes.numberOfTestimonials,
      itemsToView = attributes.itemsToView,
      itemsToViewLaptop = attributes.itemsToViewLaptop,
      itemsToViewTablet = attributes.itemsToViewTablet,
      itemsToViewMobile = attributes.itemsToViewMobile,
      autoplay = attributes.autoplay,
      navigationStyle = attributes.navigationStyle,
      showStars = attributes.showStars,
      showImage = attributes.showImage,
      buttonColor = attributes.buttonColor,
      buttonBgColor = attributes.buttonBgColor;
    var blockProps = useBlockProps({
      className: 'frontblocks-testimonials-block-editor'
    });

    // Get testimonials from WordPress
    var testimonials = useSelect(function (select) {
      return select('core').getEntityRecords('postType', 'fbrl_testimonial', {
        per_page: numberOfTestimonials === -1 ? 100 : numberOfTestimonials,
        status: 'publish'
      });
    }, [numberOfTestimonials]);
    return /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Testimonials Settings', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Number of Testimonials', 'frontblocks'),
      value: numberOfTestimonials,
      onChange: function onChange(value) {
        return setAttributes({
          numberOfTestimonials: value
        });
      },
      min: -1,
      max: 20,
      help: __('Set to -1 to show all testimonials', 'frontblocks')
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Show Images', 'frontblocks'),
      checked: showImage,
      onChange: function onChange(value) {
        return setAttributes({
          showImage: value
        });
      }
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Show Star Ratings', 'frontblocks'),
      checked: showStars,
      onChange: function onChange(value) {
        return setAttributes({
          showStars: value
        });
      }
    })), /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Carousel Settings', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Items to View (Desktop)', 'frontblocks'),
      value: itemsToView,
      onChange: function onChange(value) {
        return setAttributes({
          itemsToView: value
        });
      },
      min: 1,
      max: 6
    }), /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Items to View (Laptop)', 'frontblocks'),
      value: itemsToViewLaptop,
      onChange: function onChange(value) {
        return setAttributes({
          itemsToViewLaptop: value
        });
      },
      min: 1,
      max: 4
    }), /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Items to View (Tablet)', 'frontblocks'),
      value: itemsToViewTablet,
      onChange: function onChange(value) {
        return setAttributes({
          itemsToViewTablet: value
        });
      },
      min: 1,
      max: 3
    }), /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Items to View (Mobile)', 'frontblocks'),
      value: itemsToViewMobile,
      onChange: function onChange(value) {
        return setAttributes({
          itemsToViewMobile: value
        });
      },
      min: 1,
      max: 2
    }), /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Autoplay Speed (ms)', 'frontblocks'),
      value: autoplay,
      onChange: function onChange(value) {
        return setAttributes({
          autoplay: value
        });
      },
      min: 0,
      max: 10000,
      step: 500,
      help: __('Set to 0 to disable autoplay', 'frontblocks')
    }), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Navigation Style', 'frontblocks'),
      value: navigationStyle,
      options: [{
        label: __('Bullets', 'frontblocks'),
        value: 'bullets'
      }, {
        label: __('Arrows', 'frontblocks'),
        value: 'arrows'
      }, {
        label: __('None', 'frontblocks'),
        value: 'none'
      }],
      onChange: function onChange(value) {
        return setAttributes({
          navigationStyle: value
        });
      }
    })), /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Button Colors', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Button Color', 'frontblocks'))), /*#__PURE__*/React.createElement(ColorPalette, {
      value: buttonColor,
      onChange: function onChange(value) {
        return setAttributes({
          buttonColor: value
        });
      }
    }), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Button Background Color', 'frontblocks'))), /*#__PURE__*/React.createElement(ColorPalette, {
      value: buttonBgColor,
      onChange: function onChange(value) {
        return setAttributes({
          buttonBgColor: value
        });
      }
    }))), /*#__PURE__*/React.createElement("div", {
      className: "frontblocks-testimonials-preview"
    }, /*#__PURE__*/React.createElement("div", {
      className: "testimonials-header"
    }, /*#__PURE__*/React.createElement("span", {
      className: "dashicons dashicons-testimonial"
    }), /*#__PURE__*/React.createElement("h3", null, __('FrontBlocks Testimonials', 'frontblocks'))), !testimonials && /*#__PURE__*/React.createElement("p", null, __('Loading testimonials...', 'frontblocks')), testimonials && testimonials.length === 0 && /*#__PURE__*/React.createElement("div", {
      className: "no-testimonials"
    }, /*#__PURE__*/React.createElement("p", null, __('No testimonials found.', 'frontblocks')), /*#__PURE__*/React.createElement("p", null, __('Create some testimonials to display them here.', 'frontblocks'))), testimonials && testimonials.length > 0 && /*#__PURE__*/React.createElement("div", {
      className: "testimonials-info"
    }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Testimonials to display:', 'frontblocks')), " ", numberOfTestimonials === -1 ? __('All', 'frontblocks') : numberOfTestimonials), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Items per view:', 'frontblocks')), " ", itemsToView, " (Desktop), ", itemsToViewMobile, " (Mobile)"), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Navigation:', 'frontblocks')), " ", navigationStyle), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Total available:', 'frontblocks')), " ", testimonials.length), /*#__PURE__*/React.createElement("div", {
      className: "testimonials-preview-list"
    }, testimonials.slice(0, 3).map(function (testimonial) {
      return /*#__PURE__*/React.createElement("div", {
        key: testimonial.id,
        className: "testimonial-preview-item"
      }, showImage && testimonial.featured_media && /*#__PURE__*/React.createElement("div", {
        className: "testimonial-image"
      }, /*#__PURE__*/React.createElement("img", {
        src: testimonial.featured_media,
        alt: testimonial.title.rendered
      })), /*#__PURE__*/React.createElement("h4", null, /*#__PURE__*/React.createElement(RawHTML, null, testimonial.title.rendered)), /*#__PURE__*/React.createElement("div", {
        className: "testimonial-excerpt"
      }, /*#__PURE__*/React.createElement(RawHTML, null, testimonial.excerpt.rendered)));
    }), testimonials.length > 3 && /*#__PURE__*/React.createElement("p", {
      className: "more-testimonials"
    }, __('... and', 'frontblocks'), " ", testimonials.length - 3, " ", __('more', 'frontblocks'))))));
  },
  save: function save() {
    // Render in PHP for better performance and dynamic content
    return null;
  }
});
