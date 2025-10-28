"use strict";

/**
 * Testimonials Block for FrontBlocks
 *
 * @package FrontBlocks
 */

var __ = wp.i18n.__;
var registerBlockType = wp.blocks.registerBlockType;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl,
  RangeControl = _wp$components.RangeControl,
  ToggleControl = _wp$components.ToggleControl,
  Disabled = _wp$components.Disabled,
  Placeholder = _wp$components.Placeholder,
  Spinner = _wp$components.Spinner;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  createElement = _wp$element.createElement;
var ServerSideRender = wp.serverSideRender;

/**
 * Block Edit Component
 */
function TestimonialsBlockEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var postsCount = attributes.postsCount,
    orderBy = attributes.orderBy,
    order = attributes.order,
    layoutType = attributes.layoutType,
    imagePosition = attributes.imagePosition,
    showStars = attributes.showStars,
    showImage = attributes.showImage,
    slidesPerView = attributes.slidesPerView,
    autoplay = attributes.autoplay,
    autoplayDelay = attributes.autoplayDelay,
    showNavigation = attributes.showNavigation,
    showPagination = attributes.showPagination,
    starsPosition = attributes.starsPosition,
    contentOrder = attributes.contentOrder,
    nameAlign = attributes.nameAlign,
    textAlign = attributes.textAlign,
    starsAlign = attributes.starsAlign,
    imageAlign = attributes.imageAlign;
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Alignment Settings', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Name Alignment', 'frontblocks'),
    value: nameAlign,
    options: [{
      label: __('Left', 'frontblocks'),
      value: 'left'
    }, {
      label: __('Center', 'frontblocks'),
      value: 'center'
    }, {
      label: __('Right', 'frontblocks'),
      value: 'right'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        nameAlign: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Text Alignment', 'frontblocks'),
    value: textAlign,
    options: [{
      label: __('Left', 'frontblocks'),
      value: 'left'
    }, {
      label: __('Center', 'frontblocks'),
      value: 'center'
    }, {
      label: __('Right', 'frontblocks'),
      value: 'right'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        textAlign: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Stars Alignment', 'frontblocks'),
    value: starsAlign,
    options: [{
      label: __('Left', 'frontblocks'),
      value: 'left'
    }, {
      label: __('Center', 'frontblocks'),
      value: 'center'
    }, {
      label: __('Right', 'frontblocks'),
      value: 'right'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        starsAlign: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Image Alignment', 'frontblocks'),
    value: imageAlign,
    options: [{
      label: __('Left', 'frontblocks'),
      value: 'left'
    }, {
      label: __('Center', 'frontblocks'),
      value: 'center'
    }, {
      label: __('Right', 'frontblocks'),
      value: 'right'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        imageAlign: value
      });
    }
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Layout Settings', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Layout Type', 'frontblocks'),
    value: layoutType,
    options: [{
      label: __('Carousel', 'frontblocks'),
      value: 'carousel'
    }, {
      label: __('Grid', 'frontblocks'),
      value: 'grid'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        layoutType: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Content Order', 'frontblocks'),
    value: contentOrder,
    options: [{
      label: __('Image → Name → Text → Stars', 'frontblocks'),
      value: 'image-name-text-stars'
    }, {
      label: __('Name → Image → Text → Stars', 'frontblocks'),
      value: 'name-image-text-stars'
    }, {
      label: __('Stars → Name → Text → Image', 'frontblocks'),
      value: 'stars-name-text-image'
    }, {
      label: __('Image → Stars → Name → Text', 'frontblocks'),
      value: 'image-stars-name-text'
    }, {
      label: __('Text → Name → Stars → Image', 'frontblocks'),
      value: 'text-name-stars-image'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        contentOrder: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Image Position', 'frontblocks'),
    value: imagePosition,
    options: [{
      label: __('Top', 'frontblocks'),
      value: 'top'
    }, {
      label: __('Left', 'frontblocks'),
      value: 'left'
    }, {
      label: __('Right', 'frontblocks'),
      value: 'right'
    }, {
      label: __('None', 'frontblocks'),
      value: 'none'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        imagePosition: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Stars Position', 'frontblocks'),
    value: starsPosition,
    options: [{
      label: __('Above Name', 'frontblocks'),
      value: 'above-name'
    }, {
      label: __('Below Name', 'frontblocks'),
      value: 'below-name'
    }, {
      label: __('Above Text', 'frontblocks'),
      value: 'above-text'
    }, {
      label: __('Below Text', 'frontblocks'),
      value: 'below-text'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        starsPosition: value
      });
    }
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Show Stars', 'frontblocks'),
    checked: showStars,
    onChange: function onChange(value) {
      return setAttributes({
        showStars: value
      });
    }
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Show Image', 'frontblocks'),
    checked: showImage,
    onChange: function onChange(value) {
      return setAttributes({
        showImage: value
      });
    }
  })), /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Query Settings', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Number of Testimonials', 'frontblocks'),
    value: postsCount,
    onChange: function onChange(value) {
      return setAttributes({
        postsCount: value
      });
    },
    min: 1,
    max: 20
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Order By', 'frontblocks'),
    value: orderBy,
    options: [{
      label: __('Date', 'frontblocks'),
      value: 'date'
    }, {
      label: __('Title', 'frontblocks'),
      value: 'title'
    }, {
      label: __('Random', 'frontblocks'),
      value: 'rand'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        orderBy: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Order', 'frontblocks'),
    value: order,
    options: [{
      label: __('Descending', 'frontblocks'),
      value: 'DESC'
    }, {
      label: __('Ascending', 'frontblocks'),
      value: 'ASC'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        order: value
      });
    }
  })), layoutType === 'carousel' && /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Carousel Settings', 'frontblocks'),
    initialOpen: false
  }, /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Slides Per View', 'frontblocks'),
    value: slidesPerView,
    onChange: function onChange(value) {
      return setAttributes({
        slidesPerView: value
      });
    },
    min: 1,
    max: 6
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Autoplay', 'frontblocks'),
    checked: autoplay,
    onChange: function onChange(value) {
      return setAttributes({
        autoplay: value
      });
    }
  }), autoplay && /*#__PURE__*/React.createElement(RangeControl, {
    label: __('Autoplay Delay (ms)', 'frontblocks'),
    value: autoplayDelay,
    onChange: function onChange(value) {
      return setAttributes({
        autoplayDelay: value
      });
    },
    min: 1000,
    max: 10000,
    step: 500
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Show Navigation Arrows', 'frontblocks'),
    checked: showNavigation,
    onChange: function onChange(value) {
      return setAttributes({
        showNavigation: value
      });
    }
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Show Pagination Dots', 'frontblocks'),
    checked: showPagination,
    onChange: function onChange(value) {
      return setAttributes({
        showPagination: value
      });
    }
  }))), createElement(Disabled, null, createElement(ServerSideRender, {
    block: "frontblocks/testimonials-carousel",
    attributes: attributes,
    EmptyResponsePlaceholder: function EmptyResponsePlaceholder() {
      return createElement('div', {
        className: "frontblocks-testimonials-block-editor"
      }, createElement('p', {
        style: {
          textAlign: 'center',
          padding: '40px',
          color: '#666'
        }
      }, __('No testimonials found. Create some testimonials to see the preview.', 'frontblocks')));
    },
    ErrorResponsePlaceholder: function ErrorResponsePlaceholder() {
      return createElement('div', {
        className: "frontblocks-testimonials-block-editor"
      }, createElement('p', {
        style: {
          textAlign: 'center',
          padding: '40px',
          color: '#d63638'
        }
      }, __('Error loading testimonials. Please check your settings.', 'frontblocks')));
    },
    LoadingResponsePlaceholder: function LoadingResponsePlaceholder() {
      return createElement('div', {
        className: "frontblocks-testimonials-block-editor"
      }, createElement('p', {
        style: {
          textAlign: 'center',
          padding: '40px',
          color: '#666'
        }
      }, __('Loading testimonials...', 'frontblocks')));
    }
  })));
}

/**
 * Register the block
 */
registerBlockType('frontblocks/testimonials-carousel', {
  title: __('Testimonials Carousel', 'frontblocks'),
  description: __('Display testimonials in a customizable carousel or grid layout', 'frontblocks'),
  category: 'generateblocks',
  icon: 'testimonial',
  keywords: [__('testimonials', 'frontblocks'), __('reviews', 'frontblocks'), __('carousel', 'frontblocks'), __('slider', 'frontblocks')],
  supports: {
    html: false,
    align: ['wide', 'full']
  },
  attributes: {
    postsCount: {
      type: 'number',
      default: -1
    },
    orderBy: {
      type: 'string',
      default: 'date'
    },
    order: {
      type: 'string',
      default: 'DESC'
    },
    layoutType: {
      type: 'string',
      default: 'carousel'
    },
    imagePosition: {
      type: 'string',
      default: 'top'
    },
    showStars: {
      type: 'boolean',
      default: true
    },
    showImage: {
      type: 'boolean',
      default: true
    },
    slidesPerView: {
      type: 'number',
      default: 3
    },
    autoplay: {
      type: 'boolean',
      default: true
    },
    autoplayDelay: {
      type: 'number',
      default: 6000
    },
    showNavigation: {
      type: 'boolean',
      default: true
    },
    showPagination: {
      type: 'boolean',
      default: true
    },
    starsPosition: {
      type: 'string',
      default: 'below-text'
    },
    contentOrder: {
      type: 'string',
      default: 'image-name-text-stars'
    },
    nameAlign: {
      type: 'string',
      default: 'center'
    },
    textAlign: {
      type: 'string',
      default: 'center'
    },
    starsAlign: {
      type: 'string',
      default: 'center'
    },
    imageAlign: {
      type: 'string',
      default: 'center'
    }
  },
  edit: TestimonialsBlockEdit,
  save: function save() {
    return null;
  } // Using PHP render callback
});
