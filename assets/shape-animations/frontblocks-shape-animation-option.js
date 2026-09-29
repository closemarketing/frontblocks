"use strict";

function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
(function () {
  'use strict';

  var __ = wp.i18n.__;
  var addFilter = wp.hooks.addFilter;
  var _wp$element = wp.element,
    Fragment = _wp$element.Fragment,
    useState = _wp$element.useState;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var _wp$components = wp.components,
    PanelBody = _wp$components.PanelBody,
    ToggleControl = _wp$components.ToggleControl,
    TextareaControl = _wp$components.TextareaControl,
    Button = _wp$components.Button,
    Notice = _wp$components.Notice;

  /**
   * Add custom SVG animation controls to GenerateBlocks Shape block.
   */
  function addShapeAnimationControls(BlockEdit) {
    return function (props) {
      // Only add controls to shape and icon blocks.
      if (props.name !== 'generateblocks/shape' && props.name !== 'core/icon') {
        return wp.element.createElement(BlockEdit, props);
      }
      var attributes = props.attributes,
        setAttributes = props.setAttributes;
      var _attributes$frblCusto = attributes.frblCustomSvgAnimationEnabled,
        frblCustomSvgAnimationEnabled = _attributes$frblCusto === void 0 ? false : _attributes$frblCusto,
        _attributes$frblCusto2 = attributes.frblCustomSvgAnimationJson,
        frblCustomSvgAnimationJson = _attributes$frblCusto2 === void 0 ? '' : _attributes$frblCusto2;
      var _useState = useState(''),
        _useState2 = _slicedToArray(_useState, 2),
        jsonError = _useState2[0],
        setJsonError = _useState2[1];
      var _useState3 = useState(null),
        _useState4 = _slicedToArray(_useState3, 2),
        jsonPreview = _useState4[0],
        setJsonPreview = _useState4[1];

      // Detect if JSON is Lottie format.
      var isLottieJson = function isLottieJson(parsed) {
        return parsed.v && parsed.fr && parsed.layers;
      };

      // Validate JSON.
      var validateJson = function validateJson(jsonString) {
        try {
          var parsed = JSON.parse(jsonString);

          // Check if it's Lottie format.
          if (isLottieJson(parsed)) {
            setJsonError('');
            setJsonPreview({
              type: 'lottie',
              name: parsed.nm || 'Lottie Animation',
              version: parsed.v,
              framerate: parsed.fr,
              duration: parsed.op ? (parsed.op / parsed.fr).toFixed(2) + 's' : 'N/A'
            });
            return true;
          }

          // Check if it's custom SVG + CSS format.
          if (!parsed.svg) {
            setJsonError(__('Missing "svg" property in JSON (or not valid Lottie format)', 'frontblocks'));
            return false;
          }
          if (!parsed.animation || !parsed.animation.keyframes || !parsed.animation.name) {
            setJsonError(__('Missing required animation properties (keyframes, name)', 'frontblocks'));
            return false;
          }
          setJsonError('');
          setJsonPreview({
            type: 'css',
            name: parsed.animation.name,
            trigger: parsed.animation.trigger || 'load',
            duration: parsed.animation.duration || '1s'
          });
          return true;
        } catch (e) {
          setJsonError(__('Invalid JSON format: ', 'frontblocks') + e.message);
          return false;
        }
      };

      // Handle JSON change.
      var handleJsonChange = function handleJsonChange(value) {
        setAttributes({
          frblCustomSvgAnimationJson: value
        });
        if (value.trim()) {
          validateJson(value);
        } else {
          setJsonError('');
          setJsonPreview(null);
        }
      };

      // Example JSON template.
      var exampleJson = JSON.stringify({
        "svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" width=\"48\" height=\"48\" fill=\"currentColor\"><path d=\"M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z\"/></svg>",
        "animation": {
          "name": "myCustomAnimation",
          "keyframes": "@keyframes myCustomAnimation { 0% { transform: rotate(0deg) scale(1); } 50% { transform: rotate(180deg) scale(1.2); } 100% { transform: rotate(360deg) scale(1); } }",
          "duration": "2s",
          "delay": "0s",
          "infinite": true,
          "trigger": "load"
        }
      }, null, 2);
      return wp.element.createElement(Fragment, {}, wp.element.createElement(BlockEdit, props), wp.element.createElement(InspectorControls, {}, wp.element.createElement(PanelBody, {
        title: __('FrontBlocks Custom SVG Animation', 'frontblocks'),
        initialOpen: false
      }, wp.element.createElement(ToggleControl, {
        label: __('Enable Custom SVG Animation', 'frontblocks'),
        checked: frblCustomSvgAnimationEnabled,
        onChange: function onChange(value) {
          setAttributes({
            frblCustomSvgAnimationEnabled: value
          });
        },
        help: __('Replace SVG and add custom CSS animations via JSON', 'frontblocks')
      }), frblCustomSvgAnimationEnabled && wp.element.createElement(Fragment, {}, wp.element.createElement('p', {
        style: {
          fontSize: '12px',
          marginBottom: '8px',
          color: '#757575'
        }
      }, __('Paste your JSON configuration below:', 'frontblocks')), wp.element.createElement(TextareaControl, {
        label: __('JSON Configuration', 'frontblocks'),
        value: frblCustomSvgAnimationJson,
        onChange: handleJsonChange,
        rows: 10,
        help: __('JSON with svg and animation properties', 'frontblocks')
      }), jsonError && wp.element.createElement(Notice, {
        status: 'error',
        isDismissible: false
      }, jsonError), jsonPreview && wp.element.createElement(Notice, {
        status: 'success',
        isDismissible: false
      }, __('✓ Valid JSON configuration', 'frontblocks')), wp.element.createElement('details', {
        style: {
          marginTop: '16px',
          fontSize: '12px'
        }
      }, wp.element.createElement('summary', {
        style: {
          cursor: 'pointer',
          fontWeight: '600',
          marginBottom: '8px'
        }
      }, __('📋 Show example JSON', 'frontblocks')), wp.element.createElement('pre', {
        style: {
          background: '#f6f7f7',
          padding: '12px',
          borderRadius: '4px',
          overflow: 'auto',
          fontSize: '11px',
          maxHeight: '300px'
        }
      }, exampleJson), wp.element.createElement(Button, {
        isSecondary: true,
        isSmall: true,
        onClick: function onClick() {
          setAttributes({
            frblCustomSvgAnimationJson: exampleJson
          });
          validateJson(exampleJson);
        },
        style: {
          marginTop: '8px'
        }
      }, __('Use this example', 'frontblocks'))), jsonPreview && wp.element.createElement('div', {
        style: {
          marginTop: '16px',
          padding: '12px',
          background: jsonPreview.type === 'lottie' ? '#e8f5e9' : '#f6f7f7',
          borderRadius: '4px',
          fontSize: '12px'
        }
      }, wp.element.createElement('strong', {}, jsonPreview.type === 'lottie' ? '🎬 Lottie Animation' : '🎨 CSS Animation'), wp.element.createElement('br'), wp.element.createElement('span', {}, __('Name:', 'frontblocks') + ' ' + (jsonPreview.name || 'N/A')), wp.element.createElement('br'), jsonPreview.type === 'lottie' ? wp.element.createElement('span', {}, __('Version:', 'frontblocks') + ' ' + jsonPreview.version + ', ' + __('FPS:', 'frontblocks') + ' ' + jsonPreview.framerate) : wp.element.createElement('span', {}, __('Trigger:', 'frontblocks') + ' ' + jsonPreview.trigger), wp.element.createElement('br'), wp.element.createElement('span', {}, __('Duration:', 'frontblocks') + ' ' + jsonPreview.duration))))));
    };
  }
  addFilter('editor.BlockEdit', 'frontblocks/shape-custom-svg-animation-controls', addShapeAnimationControls);

  /**
   * Add preview and handle size/color inheritance in editor.
   */
  var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
  var _wp$element2 = wp.element,
    useEffect = _wp$element2.useEffect,
    useRef = _wp$element2.useRef;
  var withShapeAnimationPreview = createHigherOrderComponent(function (BlockListBlock) {
    return function (props) {
      if (props.name !== 'generateblocks/shape' && props.name !== 'core/icon') {
        return wp.element.createElement(BlockListBlock, props);
      }
      var attributes = props.attributes;
      var isIconBlock = props.name === 'core/icon';
      var _attributes$frblCusto3 = attributes.frblCustomSvgAnimationEnabled,
        frblCustomSvgAnimationEnabled = _attributes$frblCusto3 === void 0 ? false : _attributes$frblCusto3,
        _attributes$frblCusto4 = attributes.frblCustomSvgAnimationJson,
        frblCustomSvgAnimationJson = _attributes$frblCusto4 === void 0 ? '' : _attributes$frblCusto4,
        _attributes$styles = attributes.styles,
        styles = _attributes$styles === void 0 ? {} : _attributes$styles;
      var lottieContainerRef = useRef(null);
      var lottieInstanceRef = useRef(null);
      useEffect(function () {
        if (!frblCustomSvgAnimationEnabled || !frblCustomSvgAnimationJson) {
          return;
        }
        try {
          var parsed = JSON.parse(frblCustomSvgAnimationJson);

          // Check if it's Lottie.
          var isLottie = parsed.v && parsed.fr && parsed.layers;
          if (isLottie && typeof lottie !== 'undefined') {
            // Wait for DOM to be ready.
            setTimeout(function () {
              var blockElement = document.querySelector('[data-block="' + props.clientId + '"]');
              if (!blockElement) return;

              // Find or create Lottie container in the block.
              var lottieContainer = blockElement.querySelector('.frbl-lottie-preview');
              if (!lottieContainer) {
                // Find the inner element: .gb-shape for generateblocks/shape, .wp-block-icon for core/icon.
                var containerSelector = isIconBlock ? '.wp-block-icon' : '.gb-shape';
                var shapeElement = blockElement.querySelector(containerSelector);
                if (shapeElement) {
                  // HIDE original SVG completely.
                  var originalSvg = shapeElement.querySelector('svg');
                  if (originalSvg) {
                    originalSvg.style.display = 'none';
                  }

                  // Create Lottie preview container.
                  lottieContainer = document.createElement('div');
                  lottieContainer.className = 'frbl-lottie-preview';
                  lottieContainer.style.cssText = 'position: relative; width: 100%; height: 100%; pointer-events: none;';

                  // Replace content with Lottie container.
                  shapeElement.appendChild(lottieContainer);
                }
              }
              if (lottieContainer) {
                // Clean up previous instance.
                if (lottieInstanceRef.current) {
                  lottieInstanceRef.current.destroy();
                }
                lottieContainer.innerHTML = '';

                // Apply size from Shape block styles.
                var svgStyles = styles.svg || {};
                if (svgStyles.width) {
                  lottieContainer.style.width = svgStyles.width;
                }
                if (svgStyles.height) {
                  lottieContainer.style.height = svgStyles.height;
                }

                // Set color BEFORE creating animation.
                if (svgStyles.fill) {
                  lottieContainer.style.setProperty('--lottie-color', svgStyles.fill);
                }

                // Create Lottie animation.
                var loop = parsed.animation && typeof parsed.animation.loop !== 'undefined' ? parsed.animation.loop : true;
                var autoplay = parsed.animation && typeof parsed.animation.autoplay !== 'undefined' ? parsed.animation.autoplay : true;
                var speed = parsed.animation && parsed.animation.speed ? parsed.animation.speed : 1;
                var animation = lottie.loadAnimation({
                  container: lottieContainer,
                  renderer: 'svg',
                  loop: loop,
                  autoplay: autoplay,
                  animationData: parsed
                });
                animation.setSpeed(speed);
                lottieInstanceRef.current = animation;

                // Apply color IMMEDIATELY after creating animation.
                applyColorToLottie(lottieContainer, svgStyles.fill);

                // Apply color immediately after SVG is added to DOM.
                animation.addEventListener('DOMLoaded', function () {
                  applyColorToLottie(lottieContainer, svgStyles.fill);
                });

                // Apply color on EVERY SINGLE FRAME (most aggressive approach).
                animation.addEventListener('enterFrame', function () {
                  applyColorToLottie(lottieContainer, svgStyles.fill);
                });

                // Apply color multiple times immediately for instant coverage.
                setTimeout(function () {
                  applyColorToLottie(lottieContainer, svgStyles.fill);
                }, 0);
                setTimeout(function () {
                  applyColorToLottie(lottieContainer, svgStyles.fill);
                }, 10);
                setTimeout(function () {
                  applyColorToLottie(lottieContainer, svgStyles.fill);
                }, 20);
                setTimeout(function () {
                  applyColorToLottie(lottieContainer, svgStyles.fill);
                }, 50);
                setTimeout(function () {
                  applyColorToLottie(lottieContainer, svgStyles.fill);
                }, 100);
              }
            }, 300);
          }
        } catch (error) {
          console.warn('FrontBlocks: Error rendering Lottie preview', error);
        }

        // Cleanup on unmount.
        return function () {
          if (lottieInstanceRef.current) {
            lottieInstanceRef.current.destroy();
            lottieInstanceRef.current = null;
          }
        };
      }, [frblCustomSvgAnimationEnabled, frblCustomSvgAnimationJson, props.clientId, styles, isIconBlock]);
      return wp.element.createElement(BlockListBlock, props);
    };
  }, 'withShapeAnimationPreview');

  /**
   * Apply color to Lottie animation SVG elements - AGGRESSIVELY.
   */
  function applyColorToLottie(container, color) {
    if (!container) return;

    // If no color provided, return early.
    if (!color) return;

    // Set CSS variable.
    container.style.setProperty('--lottie-color', color);

    // Find ALL SVG elements - be very aggressive.
    var elements = container.querySelectorAll('svg *, [fill], [stroke]');
    elements.forEach(function (element) {
      // Get current fill and stroke.
      var currentFill = element.getAttribute('fill');
      var currentStroke = element.getAttribute('stroke');
      var computedFill = window.getComputedStyle(element).fill;

      // Apply to anything that has color.
      if (currentFill && currentFill !== 'none' && currentFill !== 'transparent') {
        element.setAttribute('fill', color);
        element.style.setProperty('fill', color, 'important');
      }

      // Also check computed style.
      if (computedFill && computedFill !== 'none' && computedFill !== 'transparent' && computedFill !== 'rgba(0, 0, 0, 0)') {
        element.style.setProperty('fill', color, 'important');
      }

      // Also handle stroke if it exists.
      if (currentStroke && currentStroke !== 'none' && currentStroke !== 'transparent') {
        element.setAttribute('stroke', color);
        element.style.setProperty('stroke', color, 'important');
      }
    });

    // Also try to set fill on the SVG itself.
    var svg = container.querySelector('svg');
    if (svg) {
      svg.style.setProperty('fill', color, 'important');
    }
  }
  addFilter('editor.BlockListBlock', 'frontblocks/shape-animation-preview', withShapeAnimationPreview);
})();
