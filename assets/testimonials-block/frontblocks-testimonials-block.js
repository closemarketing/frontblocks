"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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
var _wp$element = wp.element,
  RawHTML = _wp$element.RawHTML,
  useEffect = _wp$element.useEffect,
  useRef = _wp$element.useRef;
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
    var carouselRef = useRef(null);
    var glideInstanceRef = useRef(null);

    // Get testimonials from WordPress
    var testimonials = useSelect(function (select) {
      var records = select('core').getEntityRecords('postType', 'fbrl_testimonial', {
        per_page: numberOfTestimonials === -1 ? 100 : numberOfTestimonials,
        status: 'publish',
        _embed: true
      });

      // Add featured image URL to each testimonial
      if (records) {
        return records.map(function (record) {
          var featuredMediaUrl = '';
          if (record._embedded && record._embedded['wp:featuredmedia']) {
            var _record$_embedded$wp;
            featuredMediaUrl = ((_record$_embedded$wp = record._embedded['wp:featuredmedia'][0]) === null || _record$_embedded$wp === void 0 ? void 0 : _record$_embedded$wp.source_url) || '';
          }
          return _objectSpread(_objectSpread({}, record), {}, {
            featured_media_url: featuredMediaUrl
          });
        });
      }
      return records;
    }, [numberOfTestimonials]);

    // Initialize carousel when testimonials load
    useEffect(function () {
      if (!testimonials || testimonials.length === 0) {
        return;
      }

      // Wait for DOM to be ready
      var timer = setTimeout(function () {
        var carouselElement = carouselRef.current;
        if (!carouselElement || !window.Glide) {
          return;
        }

        // Destroy previous instance if exists
        if (glideInstanceRef.current) {
          try {
            glideInstanceRef.current.destroy();
            glideInstanceRef.current = null;
          } catch (e) {
            console.warn('Error destroying Glide instance:', e);
          }
        }

        // Get original slides container
        var originalSlidesContainer = carouselElement.querySelector('.testimonials-carousel-wrapper');
        if (!originalSlidesContainer) {
          return;
        }

        // Store original content
        var originalSlides = Array.from(originalSlidesContainer.children);

        // Create glide wrapper structure from scratch
        var glideWrapper = document.createElement('div');
        glideWrapper.className = 'glide frontblocks-testimonials-glide';
        var glideTrack = document.createElement('div');
        glideTrack.className = 'glide__track';
        glideTrack.setAttribute('data-glide-el', 'track');
        var slidesContainer = document.createElement('div');
        slidesContainer.className = 'glide__slides';

        // Add slides
        originalSlides.forEach(function (slide) {
          var slideClone = slide.cloneNode(true);
          slideClone.classList.add('glide__slide');
          slidesContainer.appendChild(slideClone);
        });
        glideTrack.appendChild(slidesContainer);
        glideWrapper.appendChild(glideTrack);

        // Add navigation if needed
        if (navigationStyle === 'arrows') {
          var arrows = document.createElement('div');
          arrows.className = 'glide__arrows';
          arrows.setAttribute('data-glide-el', 'controls');
          arrows.innerHTML = "\n\t\t\t\t\t\t<button class=\"glide__arrow glide__arrow--left\" data-glide-dir=\"<\" style=\"background-color: ".concat(buttonBgColor || 'transparent', "\">\n\t\t\t\t\t\t\t<svg width=\"7\" height=\"12\" viewBox=\"0 0 7 12\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t\t\t<path d=\"M6 1L1 6L6 11\" stroke=\"").concat(buttonColor || '#000000', "\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button class=\"glide__arrow glide__arrow--right\" data-glide-dir=\">\" style=\"background-color: ").concat(buttonBgColor || 'transparent', "\">\n\t\t\t\t\t\t\t<svg width=\"7\" height=\"12\" viewBox=\"0 0 7 12\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t\t\t<path d=\"M1 11L6 6L1 1\" stroke=\"").concat(buttonColor || '#000000', "\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t</button>\n\t\t\t\t\t");
          glideWrapper.appendChild(arrows);
        }
        if (navigationStyle === 'bullets') {
          var bullets = document.createElement('div');
          bullets.className = 'glide__bullets';
          bullets.setAttribute('data-glide-el', 'controls[nav]');
          for (var i = 0; i < testimonials.length; i++) {
            var bullet = document.createElement('button');
            bullet.className = 'glide__bullet';
            bullet.setAttribute('data-glide-dir', '=' + i);
            bullet.style.backgroundColor = buttonBgColor || 'transparent';
            bullets.appendChild(bullet);
          }
          glideWrapper.appendChild(bullets);

          // Remove old style if exists
          var oldStyle = document.getElementById('frontblocks-testimonials-bullets-style');
          if (oldStyle) {
            oldStyle.remove();
          }

          // Add custom CSS for active bullet
          var style = document.createElement('style');
          style.id = 'frontblocks-testimonials-bullets-style';
          style.textContent = "\n\t\t\t\t\t\t.frontblocks-testimonials-glide .glide__bullet.glide__bullet--active {\n\t\t\t\t\t\t\tbackground-color: ".concat(buttonColor || '#000000', " !important;\n\t\t\t\t\t\t}\n\t\t\t\t\t");
          document.head.appendChild(style);
        }

        // Clear and add new structure
        carouselElement.innerHTML = '';
        carouselElement.appendChild(glideWrapper);

        // Initialize Glide
        try {
          var glideConfig = {
            type: 'carousel',
            perView: itemsToView || 3,
            startAt: 0,
            autoplay: autoplay > 0 ? autoplay : false,
            gap: 20,
            animationDuration: 400,
            animationTimingFunc: 'cubic-bezier(0.165, 0.840, 0.440, 1.000)',
            rewind: true,
            bound: false,
            focusAt: 0,
            peek: 0,
            breakpoints: {
              600: {
                perView: itemsToViewMobile || 1,
                gap: 20
              },
              900: {
                perView: itemsToViewTablet || 2,
                gap: 20
              },
              1200: {
                perView: itemsToViewLaptop || 3,
                gap: 20
              }
            }
          };
          glideInstanceRef.current = new window.Glide(glideWrapper, glideConfig);
          glideInstanceRef.current.mount();

          // Force update on mount to ensure correct sizing
          setTimeout(function () {
            if (glideInstanceRef.current) {
              glideInstanceRef.current.update();
            }
          }, 100);
        } catch (e) {
          console.error('Error initializing Glide in editor:', e);
        }
      }, 500);

      // Cleanup function
      return function () {
        clearTimeout(timer);
        if (glideInstanceRef.current) {
          try {
            glideInstanceRef.current.destroy();
            glideInstanceRef.current = null;
          } catch (e) {
            console.warn('Error cleaning up Glide instance:', e);
          }
        }
      };
    }, [testimonials, itemsToView, itemsToViewLaptop, itemsToViewTablet, itemsToViewMobile, autoplay, navigationStyle, buttonColor, buttonBgColor, showStars, showImage]);
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
    }, !testimonials && /*#__PURE__*/React.createElement("div", {
      className: "loading-testimonials"
    }, /*#__PURE__*/React.createElement("span", {
      className: "dashicons dashicons-update"
    }), /*#__PURE__*/React.createElement("p", null, __('Loading testimonials...', 'frontblocks'))), testimonials && testimonials.length === 0 && /*#__PURE__*/React.createElement("div", {
      className: "no-testimonials"
    }, /*#__PURE__*/React.createElement("span", {
      className: "dashicons dashicons-testimonial"
    }), /*#__PURE__*/React.createElement("p", null, __('No testimonials found.', 'frontblocks')), /*#__PURE__*/React.createElement("p", null, __('Create some testimonials to display them here.', 'frontblocks'))), testimonials && testimonials.length > 0 && /*#__PURE__*/React.createElement("div", {
      className: "frontblocks-testimonials-carousel-preview"
    }, /*#__PURE__*/React.createElement("div", {
      ref: carouselRef,
      className: "carousel-container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "testimonials-carousel-wrapper"
    }, testimonials.map(function (testimonial) {
      var _testimonial$meta;
      // Get star rating from meta
      var stars = ((_testimonial$meta = testimonial.meta) === null || _testimonial$meta === void 0 ? void 0 : _testimonial$meta.frontblocks_stars) || 0;
      return /*#__PURE__*/React.createElement("div", {
        key: testimonial.id,
        className: "testimonial-card"
      }, /*#__PURE__*/React.createElement("div", {
        className: "testimonial-header"
      }, showImage && testimonial.featured_media_url && /*#__PURE__*/React.createElement("div", {
        className: "testimonial-image-container"
      }, /*#__PURE__*/React.createElement("img", {
        src: testimonial.featured_media_url,
        alt: testimonial.title.rendered,
        className: "testimonial-image"
      }))), /*#__PURE__*/React.createElement("div", {
        className: "testimonial-content"
      }, /*#__PURE__*/React.createElement("h3", {
        className: "testimonial-name"
      }, /*#__PURE__*/React.createElement(RawHTML, null, testimonial.title.rendered)), /*#__PURE__*/React.createElement("p", {
        className: "testimonial-text"
      }, "\"", /*#__PURE__*/React.createElement(RawHTML, null, testimonial.content.rendered), "\""), showStars && /*#__PURE__*/React.createElement("div", {
        className: "stars-container"
      }, [1, 2, 3, 4, 5].map(function (i) {
        return /*#__PURE__*/React.createElement("span", {
          key: i,
          className: "star ".concat(i <= stars ? 'filled' : '')
        }, "\u2605");
      }))));
    }))))));
  },
  save: function save() {
    // Render in PHP for better performance and dynamic content
    return null;
  }
});
