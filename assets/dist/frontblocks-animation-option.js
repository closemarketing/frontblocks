"use strict";

function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
// Add custom animation controls to any block based on Animate.css
var addFilter = wp.hooks.addFilter;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useEffect = _wp$element.useEffect,
  useRef = _wp$element.useRef;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  SelectControl = _wp$components.SelectControl,
  RangeControl = _wp$components.RangeControl,
  ToggleControl = _wp$components.ToggleControl,
  PanelBody = _wp$components.PanelBody,
  Placeholder = _wp$components.Placeholder,
  Disabled = _wp$components.Disabled,
  Button = _wp$components.Button;
var __ = wp.i18n.__;

// Organizar las animaciones por categorías
var animationOptions = [{
  label: __('None', 'frontblocks'),
  value: ''
}, {
  label: __('Attention Seekers', 'frontblocks'),
  options: [{
    label: 'bounce',
    value: 'bounce'
  }, {
    label: 'flash',
    value: 'flash'
  }, {
    label: 'pulse',
    value: 'pulse'
  }, {
    label: 'rubberBand',
    value: 'rubberBand'
  }, {
    label: 'shakeX',
    value: 'shakeX'
  }, {
    label: 'shakeY',
    value: 'shakeY'
  }, {
    label: 'headShake',
    value: 'headShake'
  }, {
    label: 'swing',
    value: 'swing'
  }, {
    label: 'tada',
    value: 'tada'
  }, {
    label: 'wobble',
    value: 'wobble'
  }, {
    label: 'jello',
    value: 'jello'
  }, {
    label: 'heartBeat',
    value: 'heartBeat'
  }]
}, {
  label: __('Back Entrances', 'frontblocks'),
  options: [{
    label: 'backInDown',
    value: 'backInDown'
  }, {
    label: 'backInLeft',
    value: 'backInLeft'
  }, {
    label: 'backInRight',
    value: 'backInRight'
  }, {
    label: 'backInUp',
    value: 'backInUp'
  }]
}, {
  label: __('Back Exits', 'frontblocks'),
  options: [{
    label: 'backOutDown',
    value: 'backOutDown'
  }, {
    label: 'backOutLeft',
    value: 'backOutLeft'
  }, {
    label: 'backOutRight',
    value: 'backOutRight'
  }, {
    label: 'backOutUp',
    value: 'backOutUp'
  }]
}, {
  label: __('Bouncing Entrances', 'frontblocks'),
  options: [{
    label: 'bounceIn',
    value: 'bounceIn'
  }, {
    label: 'bounceInDown',
    value: 'bounceInDown'
  }, {
    label: 'bounceInLeft',
    value: 'bounceInLeft'
  }, {
    label: 'bounceInRight',
    value: 'bounceInRight'
  }, {
    label: 'bounceInUp',
    value: 'bounceInUp'
  }]
}, {
  label: __('Bouncing Exits', 'frontblocks'),
  options: [{
    label: 'bounceOut',
    value: 'bounceOut'
  }, {
    label: 'bounceOutDown',
    value: 'bounceOutDown'
  }, {
    label: 'bounceOutLeft',
    value: 'bounceOutLeft'
  }, {
    label: 'bounceOutRight',
    value: 'bounceOutRight'
  }, {
    label: 'bounceOutUp',
    value: 'bounceOutUp'
  }]
}, {
  label: __('Fading Entrances', 'frontblocks'),
  options: [{
    label: 'fadeIn',
    value: 'fadeIn'
  }, {
    label: 'fadeInDown',
    value: 'fadeInDown'
  }, {
    label: 'fadeInDownBig',
    value: 'fadeInDownBig'
  }, {
    label: 'fadeInLeft',
    value: 'fadeInLeft'
  }, {
    label: 'fadeInLeftBig',
    value: 'fadeInLeftBig'
  }, {
    label: 'fadeInRight',
    value: 'fadeInRight'
  }, {
    label: 'fadeInRightBig',
    value: 'fadeInRightBig'
  }, {
    label: 'fadeInUp',
    value: 'fadeInUp'
  }, {
    label: 'fadeInUpBig',
    value: 'fadeInUpBig'
  }, {
    label: 'fadeInTopLeft',
    value: 'fadeInTopLeft'
  }, {
    label: 'fadeInTopRight',
    value: 'fadeInTopRight'
  }, {
    label: 'fadeInBottomLeft',
    value: 'fadeInBottomLeft'
  }, {
    label: 'fadeInBottomRight',
    value: 'fadeInBottomRight'
  }]
}, {
  label: __('Fading Exits', 'frontblocks'),
  options: [{
    label: 'fadeOut',
    value: 'fadeOut'
  }, {
    label: 'fadeOutDown',
    value: 'fadeOutDown'
  }, {
    label: 'fadeOutDownBig',
    value: 'fadeOutDownBig'
  }, {
    label: 'fadeOutLeft',
    value: 'fadeOutLeft'
  }, {
    label: 'fadeOutLeftBig',
    value: 'fadeOutLeftBig'
  }, {
    label: 'fadeOutRight',
    value: 'fadeOutRight'
  }, {
    label: 'fadeOutRightBig',
    value: 'fadeOutRightBig'
  }, {
    label: 'fadeOutUp',
    value: 'fadeOutUp'
  }, {
    label: 'fadeOutUpBig',
    value: 'fadeOutUpBig'
  }, {
    label: 'fadeOutTopLeft',
    value: 'fadeOutTopLeft'
  }, {
    label: 'fadeOutTopRight',
    value: 'fadeOutTopRight'
  }, {
    label: 'fadeOutBottomRight',
    value: 'fadeOutBottomRight'
  }, {
    label: 'fadeOutBottomLeft',
    value: 'fadeOutBottomLeft'
  }]
}, {
  label: __('Flippers', 'frontblocks'),
  options: [{
    label: 'flip',
    value: 'flip'
  }, {
    label: 'flipInX',
    value: 'flipInX'
  }, {
    label: 'flipInY',
    value: 'flipInY'
  }, {
    label: 'flipOutX',
    value: 'flipOutX'
  }, {
    label: 'flipOutY',
    value: 'flipOutY'
  }]
}, {
  label: __('Lightspeed', 'frontblocks'),
  options: [{
    label: 'lightSpeedInRight',
    value: 'lightSpeedInRight'
  }, {
    label: 'lightSpeedInLeft',
    value: 'lightSpeedInLeft'
  }, {
    label: 'lightSpeedOutRight',
    value: 'lightSpeedOutRight'
  }, {
    label: 'lightSpeedOutLeft',
    value: 'lightSpeedOutLeft'
  }]
}, {
  label: __('Rotating Entrances', 'frontblocks'),
  options: [{
    label: 'rotateIn',
    value: 'rotateIn'
  }, {
    label: 'rotateInDownLeft',
    value: 'rotateInDownLeft'
  }, {
    label: 'rotateInDownRight',
    value: 'rotateInDownRight'
  }, {
    label: 'rotateInUpLeft',
    value: 'rotateInUpLeft'
  }, {
    label: 'rotateInUpRight',
    value: 'rotateInUpRight'
  }]
}, {
  label: __('Rotating Exits', 'frontblocks'),
  options: [{
    label: 'rotateOut',
    value: 'rotateOut'
  }, {
    label: 'rotateOutDownLeft',
    value: 'rotateOutDownLeft'
  }, {
    label: 'rotateOutDownRight',
    value: 'rotateOutDownRight'
  }, {
    label: 'rotateOutUpLeft',
    value: 'rotateOutUpLeft'
  }, {
    label: 'rotateOutUpRight',
    value: 'rotateOutUpRight'
  }]
}, {
  label: __('Specials', 'frontblocks'),
  options: [{
    label: 'hinge',
    value: 'hinge'
  }, {
    label: 'jackInTheBox',
    value: 'jackInTheBox'
  }, {
    label: 'rollIn',
    value: 'rollIn'
  }, {
    label: 'rollOut',
    value: 'rollOut'
  }]
}, {
  label: __('Zooming Entrances', 'frontblocks'),
  options: [{
    label: 'zoomIn',
    value: 'zoomIn'
  }, {
    label: 'zoomInDown',
    value: 'zoomInDown'
  }, {
    label: 'zoomInLeft',
    value: 'zoomInLeft'
  }, {
    label: 'zoomInRight',
    value: 'zoomInRight'
  }, {
    label: 'zoomInUp',
    value: 'zoomInUp'
  }]
}, {
  label: __('Zooming Exits', 'frontblocks'),
  options: [{
    label: 'zoomOut',
    value: 'zoomOut'
  }, {
    label: 'zoomOutDown',
    value: 'zoomOutDown'
  }, {
    label: 'zoomOutLeft',
    value: 'zoomOutLeft'
  }, {
    label: 'zoomOutRight',
    value: 'zoomOutRight'
  }, {
    label: 'zoomOutUp',
    value: 'zoomOutUp'
  }]
}, {
  label: __('Sliding Entrances', 'frontblocks'),
  options: [{
    label: 'slideInDown',
    value: 'slideInDown'
  }, {
    label: 'slideInLeft',
    value: 'slideInLeft'
  }, {
    label: 'slideInRight',
    value: 'slideInRight'
  }, {
    label: 'slideInUp',
    value: 'slideInUp'
  }]
}, {
  label: __('Sliding Exits', 'frontblocks'),
  options: [{
    label: 'slideOutDown',
    value: 'slideOutDown'
  }, {
    label: 'slideOutLeft',
    value: 'slideOutLeft'
  }, {
    label: 'slideOutRight',
    value: 'slideOutRight'
  }, {
    label: 'slideOutUp',
    value: 'slideOutUp'
  }]
}];

// Create flattened options with group labels for SelectControl
var createFlattenedOptions = function createFlattenedOptions() {
  var flattenedOptions = [];
  animationOptions.forEach(function (category) {
    if (category.options) {
      // Add group header
      flattenedOptions.push({
        label: "\u2501\u2501\u2501 ".concat(category.label, " \u2501\u2501\u2501"),
        value: '',
        disabled: true
      });
      // Add options under this group
      category.options.forEach(function (option) {
        flattenedOptions.push({
          label: "  ".concat(option.label),
          value: option.value
        });
      });
    } else if (category.value !== undefined) {
      // Single option (like "None")
      flattenedOptions.push(category);
    }
  });
  return flattenedOptions;
};
function addAnimationControls(BlockEdit) {
  return function (props) {
    // Exclude Gravity Forms blocks from animation controls
    if (props.name && props.name.startsWith('gravityforms/')) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }

    // Extract animation attributes with default values
    var _props$attributes = props.attributes,
      _props$attributes$frb = _props$attributes.frblAnimation,
      frblAnimation = _props$attributes$frb === void 0 ? '' : _props$attributes$frb,
      _props$attributes$frb2 = _props$attributes.frblAnimationDelay,
      frblAnimationDelay = _props$attributes$frb2 === void 0 ? 0 : _props$attributes$frb2,
      _props$attributes$frb3 = _props$attributes.frblAnimationDuration,
      frblAnimationDuration = _props$attributes$frb3 === void 0 ? 1 : _props$attributes$frb3,
      _props$attributes$frb4 = _props$attributes.frblAnimationRepeat,
      frblAnimationRepeat = _props$attributes$frb4 === void 0 ? false : _props$attributes$frb4,
      _props$attributes$frb5 = _props$attributes.frblAnimationInfinite,
      frblAnimationInfinite = _props$attributes$frb5 === void 0 ? false : _props$attributes$frb5;

    // Create flattened options for the SelectControl
    var flattenedOptions = createFlattenedOptions();

    // Function to trigger animation preview
    var triggerAnimationPreview = function triggerAnimationPreview() {
      if (!frblAnimation) return;

      // --- IFRAME SUPPORT ---
      // Try to get the editor-canvas iframe (site editor or block editor)
      var doc = document;
      var iframe = document.querySelector('iframe[name="editor-canvas"], iframe#editor-canvas');
      if (iframe && iframe.contentDocument) {
        doc = iframe.contentDocument;
        console.log('Using iframe document for block search');
      } else {
        console.log('Using main document for block search');
      }

      // Try multiple selectors to find the block element
      var blockElement = doc.querySelector("[data-block=\"".concat(props.clientId, "\"]"));
      if (!blockElement) {
        blockElement = doc.querySelector("[data-block-id=\"".concat(props.clientId, "\"]"));
      }
      if (!blockElement) {
        blockElement = doc.querySelector(".wp-block[data-block=\"".concat(props.clientId, "\"]"));
      }
      if (!blockElement) {
        blockElement = doc.querySelector(".block-editor-block-list__block[data-block=\"".concat(props.clientId, "\"]"));
      }

      // Fallback: search for any element whose outerHTML contains the clientId
      if (!blockElement) {
        var allElements = doc.querySelectorAll('*');
        var _iterator = _createForOfIteratorHelper(allElements),
          _step;
        try {
          for (_iterator.s(); !(_step = _iterator.n()).done;) {
            var el = _step.value;
            if (el.outerHTML && el.outerHTML.includes(props.clientId)) {
              blockElement = el;
              console.log('Found block element by outerHTML containing clientId:', el);
              break;
            }
          }
        } catch (err) {
          _iterator.e(err);
        } finally {
          _iterator.f();
        }
      }

      // Find the first element with the animation classes
      var animatedElement = blockElement.querySelector('.animate__animated') || blockElement;
      // Remove existing animation classes and styles
      animatedElement.classList.remove('animate__animated');
      animationOptions.forEach(function (category) {
        if (category.options) {
          category.options.forEach(function (option) {
            animatedElement.classList.remove("animate__".concat(option.value));
          });
        }
      });
      animatedElement.style.removeProperty('--animate-duration');
      animatedElement.style.removeProperty('--animate-delay');
      animatedElement.style.removeProperty('--animate-repeat');
      animatedElement.style.removeProperty('animation-iteration-count');

      // Force reflow and re-add classes in the next frame
      void animatedElement.offsetWidth; // This is a more reliable reflow trigger

      // Function to apply animation
      var applyAnimation = function applyAnimation() {
        animatedElement.classList.add('animate__animated', "animate__".concat(frblAnimation));
        if (frblAnimationDuration !== 1) {
          animatedElement.style.setProperty('--animate-duration', "".concat(frblAnimationDuration, "s"));
        }
        if (frblAnimationDelay > 0) {
          animatedElement.style.setProperty('--animate-delay', "".concat(frblAnimationDelay, "s"));
        }
        if (frblAnimationInfinite) {
          animatedElement.style.setProperty('--animate-repeat', 'infinite');
          animatedElement.style.setProperty('animation-iteration-count', 'infinite');
        } else if (frblAnimationRepeat) {
          animatedElement.style.setProperty('--animate-repeat', '2');
          animatedElement.style.setProperty('animation-iteration-count', '2');
        }
      };

      // Wait for Animate.css to be available
      var waitForAnimateCSS = function waitForAnimateCSS() {
        var testElem = doc.createElement('div');
        testElem.className = 'animate__animated animate__bounce';
        testElem.style.position = 'absolute';
        testElem.style.left = '-9999px';
        doc.body.appendChild(testElem);
        var checkAnimation = function checkAnimation() {
          var computed = doc.defaultView.getComputedStyle(testElem);
          var hasAnimation = computed.animationName && computed.animationName !== 'none';
          doc.body.removeChild(testElem);
          if (hasAnimation) {
            console.log('Animate.css is ready, applying animation');
            applyAnimation();
          } else {
            console.error('Animate.css failed to load, loading statically...');
            // Fallback: load Animate.css dynamically
            var link = doc.createElement('link');
            link.rel = 'stylesheet';
            link.href = frontblocksAnimationData.customCss;
            link.onload = function () {
              setTimeout(applyAnimation, 50);
            };
            doc.head.appendChild(link);
          }
        };
        setTimeout(checkAnimation, 50);
      };
      waitForAnimateCSS();
    };
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Animations', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Animation Type', 'frontblocks'),
      value: frblAnimation,
      options: flattenedOptions,
      onChange: function onChange(value) {
        props.setAttributes({
          frblAnimation: value
        });
      }
    }), frblAnimation && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Delay (seconds)', 'frontblocks'),
      value: frblAnimationDelay,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblAnimationDelay: value
        });
      },
      min: 0,
      max: 10,
      step: 0.1
    }), /*#__PURE__*/React.createElement(RangeControl, {
      label: __('Duration (seconds)', 'frontblocks'),
      value: frblAnimationDuration,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblAnimationDuration: value
        });
      },
      min: 0.1,
      max: 10,
      step: 0.1
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Repeat animation', 'frontblocks'),
      checked: frblAnimationRepeat,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblAnimationRepeat: value
        });
      }
    }), frblAnimationRepeat && /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Infinite repeat', 'frontblocks'),
      checked: frblAnimationInfinite,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblAnimationInfinite: value
        });
      }
    }), /*#__PURE__*/React.createElement("div", {
      style: {
        marginTop: '16px'
      }
    }, /*#__PURE__*/React.createElement(Button, {
      isPrimary: true,
      onClick: triggerAnimationPreview,
      style: {
        width: '100%'
      }
    }, __('Preview Animation', 'frontblocks')))))));
  };
}

// Add the animation controls to all blocks
addFilter('editor.BlockEdit', 'frontblocks_animation_controls', addAnimationControls);

// Add custom class to blocks with animations
addFilter('blocks.getSaveContent.extraProps', 'frontblocks_apply_animations', function (props, blockType, attributes) {
  var frblAnimation = attributes.frblAnimation,
    frblAnimationDelay = attributes.frblAnimationDelay,
    frblAnimationDuration = attributes.frblAnimationDuration,
    frblAnimationRepeat = attributes.frblAnimationRepeat,
    frblAnimationInfinite = attributes.frblAnimationInfinite;
  if (frblAnimation) {
    // Add animate.css base class and the specific animation
    props.className = props.className ? "".concat(props.className, " animate__animated animate__").concat(frblAnimation) : "animate__animated animate__".concat(frblAnimation);

    // Add style attribute if needed
    if (!props.style) {
      props.style = {};
    }

    // Set animation properties as inline styles
    if (frblAnimationDuration) {
      props.style['--animate-duration'] = "".concat(frblAnimationDuration, "s");
    }
    if (frblAnimationDelay) {
      props.style['--animate-delay'] = "".concat(frblAnimationDelay, "s");
    }
    if (frblAnimationInfinite) {
      props.style['--animate-repeat'] = 'infinite';
    } else if (frblAnimationRepeat) {
      props.style['--animate-repeat'] = '2';
    }
  }
  return props;
});
