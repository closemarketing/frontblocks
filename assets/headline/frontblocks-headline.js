"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl,
  ToggleControl = _wp$components.ToggleControl;
var _wp$i18n = wp.i18n,
  __ = _wp$i18n.__,
  sprintf = _wp$i18n.sprintf;
var LINE_CLASS_PREFIX = 'gb-line-effect-';
var MARQUEE_CLASS = 'gb-marquee-infinite-scroll';
var MARQUEE_SPEED_ATTR = 'frblMarqueeSpeed';
var BLOCK_NAME = 'generateblocks/text';

// Marquee speed presets
var MARQUEE_SPEEDS = {
  fast: 10,
  // 10 seconds - fast
  medium: 20,
  // 20 seconds - medium
  slow: 40 // 40 seconds - slow
};

// Register marquee speed attribute
wp.hooks.addFilter('blocks.registerBlockType', 'frontblocks/add-marquee-attribute', function (settings, name) {
  if (name === BLOCK_NAME) {
    settings.attributes = Object.assign(settings.attributes || {}, _defineProperty({}, MARQUEE_SPEED_ATTR, {
      type: 'string',
      default: 'medium'
    }));
  }
  return settings;
});
var withHeadlineLineControl = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    if (props.name !== BLOCK_NAME) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var existingClasses = attributes.className || '';
    var htmlAttributes = attributes.htmlAttributes || {};
    var marqueeSpeed = attributes[MARQUEE_SPEED_ATTR] || 'medium';
    var cleanExistingLineClasses = function cleanExistingLineClasses(classes) {
      return classes.split(' ').filter(function (cls) {
        return !cls.startsWith(LINE_CLASS_PREFIX);
      }).join(' ').replace(/\s{2,}/g, ' ').trim();
    };
    var cleanMarqueeClass = function cleanMarqueeClass(classes) {
      return classes.split(' ').filter(function (cls) {
        return cls !== MARQUEE_CLASS;
      }).join(' ').replace(/\s{2,}/g, ' ').trim();
    };
    var currentLineStyle = 'none';
    if (existingClasses.includes(LINE_CLASS_PREFIX + 'vertical')) {
      currentLineStyle = 'vertical';
    } else if (existingClasses.includes(LINE_CLASS_PREFIX + 'horizontal')) {
      currentLineStyle = 'horizontal';
    }
    var isMarqueeEnabled = existingClasses.includes(MARQUEE_CLASS);

    /**
    * Maneja el cambio del SelectControl y actualiza las clases CSS.
    */
    var setLineStyle = function setLineStyle(newStyle) {
      var newClasses = cleanExistingLineClasses(existingClasses);
      if (newStyle !== 'none') {
        var classToAdd = LINE_CLASS_PREFIX + newStyle;
        newClasses = (newClasses + ' ' + classToAdd).trim();
      }

      // Preserve marquee class if enabled
      if (isMarqueeEnabled) {
        newClasses = (newClasses + ' ' + MARQUEE_CLASS).trim();
      }
      setAttributes({
        className: newClasses
      });
    };

    /**
    * Maneja el cambio del ToggleControl para el marquee y actualiza las clases CSS.
    */
    var setMarqueeEnabled = function setMarqueeEnabled(enabled) {
      var newClasses = cleanMarqueeClass(existingClasses);
      var updatedHtmlAttributes = _objectSpread({}, htmlAttributes);
      var newAttributes = {
        className: newClasses
      };
      if (enabled) {
        newClasses = (newClasses + ' ' + MARQUEE_CLASS).trim();
        newAttributes.className = newClasses;
        // Set default speed if not already set
        if (!attributes[MARQUEE_SPEED_ATTR]) {
          newAttributes[MARQUEE_SPEED_ATTR] = 'medium';
          updatedHtmlAttributes['data-marquee-speed'] = MARQUEE_SPEEDS.medium;
        } else {
          var speedValue = MARQUEE_SPEEDS[attributes[MARQUEE_SPEED_ATTR]] || MARQUEE_SPEEDS.medium;
          updatedHtmlAttributes['data-marquee-speed'] = speedValue;
        }
        newAttributes.htmlAttributes = updatedHtmlAttributes;
      } else {
        // Remove speed attribute when disabling
        newAttributes[MARQUEE_SPEED_ATTR] = undefined;
        delete updatedHtmlAttributes['data-marquee-speed'];
        newAttributes.htmlAttributes = updatedHtmlAttributes;
      }
      setAttributes(newAttributes);
    };

    /**
    * Maneja el cambio de la velocidad del marquee.
    */
    var setMarqueeSpeed = function setMarqueeSpeed(speedPreset) {
      var speedValue = MARQUEE_SPEEDS[speedPreset] || MARQUEE_SPEEDS.medium;
      var updatedHtmlAttributes = _objectSpread({}, htmlAttributes);
      updatedHtmlAttributes['data-marquee-speed'] = speedValue;
      setAttributes(_defineProperty(_defineProperty({}, MARQUEE_SPEED_ATTR, speedPreset), "htmlAttributes", updatedHtmlAttributes));

      // Update marquee wrapper directly if it exists (for immediate preview)
      setTimeout(function () {
        // Try to find the marquee wrapper in editor
        var blockElement = document.querySelector('[data-block="' + props.clientId + '"]');
        if (blockElement) {
          var marqueeElement = blockElement.querySelector('.gb-marquee-infinite-scroll');
          if (marqueeElement) {
            var wrapper = marqueeElement.querySelector('.gb-marquee-wrapper');
            if (wrapper && typeof wrapper.updateMarqueeSpeed === 'function') {
              wrapper.updateMarqueeSpeed(speedValue);
            } else if (wrapper) {
              // Fallback: update directly
              wrapper.setAttribute('data-marquee-speed', speedValue);
              wrapper.style.setProperty('--marquee-speed', speedValue + 's');
              // Force animation update
              var currentAnimation = wrapper.style.animation;
              if (currentAnimation) {
                var match = currentAnimation.match(/marquee-scroll-[\w-]+/);
                if (match) {
                  var styleId = match[0].replace('marquee-scroll-', '');
                  wrapper.style.animation = 'marquee-scroll-' + styleId + ' ' + speedValue + 's linear infinite';
                  wrapper.style.animationDuration = speedValue + 's';
                }
              }
            }
          }
        }
      }, 50);
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
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Infinite Scrolling Marquee', 'frontblocks'),
      checked: isMarqueeEnabled,
      onChange: setMarqueeEnabled,
      help: isMarqueeEnabled ? __('Marquee effect is active. Text will scroll infinitely.', 'frontblocks') : __('Enable infinite scrolling marquee effect for the headline text.', 'frontblocks')
    }), isMarqueeEnabled && /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Marquee Speed', 'frontblocks'),
      value: marqueeSpeed,
      onChange: setMarqueeSpeed,
      options: [{
        label: __('Fast', 'frontblocks'),
        value: 'fast'
      }, {
        label: __('Medium', 'frontblocks'),
        value: 'medium'
      }, {
        label: __('Slow', 'frontblocks'),
        value: 'slow'
      }],
      help: __('Select the scrolling speed for the marquee effect.', 'frontblocks')
    }))));
  };
}, 'withHeadlineLineControl');
wp.hooks.addFilter('editor.BlockEdit', 'frontblocks/headline-line-control', withHeadlineLineControl);
