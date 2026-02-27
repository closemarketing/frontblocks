"use strict";

function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
// Add custom controls to the Advanced panel of GenerateBlocks Grid block
var addFilter = wp.hooks.addFilter;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useEffect = _wp$element.useEffect,
  useRef = _wp$element.useRef;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  PanelColorSettings = _wp$blockEditor.PanelColorSettings;
var _wp$components = wp.components,
  SelectControl = _wp$components.SelectControl,
  TextControl = _wp$components.TextControl,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl;
var __ = wp.i18n.__;

/**
 * Returns the document that renders block content.
 * WordPress 6.x uses an <iframe> for the editor canvas.
 */
function getEditorDocument() {
  var iframe = document.querySelector('iframe[name="editor-canvas"]');
  return iframe && iframe.contentDocument && iframe.contentDocument.body ? iframe.contentDocument : document;
}
var UUID_RE = /^[0-9a-f]{8}-[0-9a-f]{4}-/;

/**
 * Walk an element's subtree to find the container whose DIRECT children
 * are actual blocks (UUID data-block).  Stops at depth 4.
 */
function findSlidesParent(el, depth) {
  if (depth === 0) return null;
  if (Array.from(el.children).some(function (c) {
    return UUID_RE.test(c.dataset.block || '');
  })) {
    return el;
  }
  var _iterator = _createForOfIteratorHelper(el.children),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var child = _step.value;
      var found = findSlidesParent(child, depth - 1);
      if (found) return found;
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
  return null;
}

/**
 * Find the grid DOM element for a given block.
 *
 * For generateblocks/element: [data-block][data-type] IS the gb-element.
 *   Do NOT search inside – that returns a child item.
 * For generateblocks/grid:  look for .gb-grid-wrapper inside.
 * For core/group: walk the subtree to find the element whose children
 *   are the real blocks, because WP nests them differently across versions.
 */
function findGridEl(blockEl, name) {
  if (name === 'generateblocks/element') {
    return blockEl;
  }
  if (name === 'generateblocks/grid') {
    if (blockEl.classList.contains('gb-grid-wrapper')) return blockEl;
    var grid = blockEl.querySelector('.gb-grid-wrapper');
    return grid || null;
  }
  if (name === 'core/group') {
    // Walk up to 4 levels deep to find where the child blocks live.
    return findSlidesParent(blockEl, 4) || blockEl;
  }
  return null;
}
function addCustomCarouselPanel(BlockEdit) {
  return function (props) {
    if (props.name !== 'generateblocks/grid' && props.name !== 'generateblocks/element' && props.name !== 'core/group') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    if (props.name === 'generateblocks/element') {
      var styles = props.attributes.styles || {};
      if (styles.display !== 'grid') {
        return /*#__PURE__*/React.createElement(BlockEdit, props);
      }
    }
    if (props.name === 'core/group') {
      var layout = props.attributes.layout || {};
      if (layout.type !== 'grid') {
        return /*#__PURE__*/React.createElement(BlockEdit, props);
      }
    }
    var _props$attributes = props.attributes,
      _props$attributes$frb = _props$attributes.frblGridOption,
      frblGridOption = _props$attributes$frb === void 0 ? 'none' : _props$attributes$frb,
      _props$attributes$frb2 = _props$attributes.frblItemsToView,
      frblItemsToView = _props$attributes$frb2 === void 0 ? '4' : _props$attributes$frb2,
      _props$attributes$frb3 = _props$attributes.frblLaptopToView,
      frblLaptopToView = _props$attributes$frb3 === void 0 ? '3' : _props$attributes$frb3,
      _props$attributes$frb4 = _props$attributes.frblTabletToView,
      frblTabletToView = _props$attributes$frb4 === void 0 ? '2' : _props$attributes$frb4,
      _props$attributes$frb5 = _props$attributes.frblResponsiveToView,
      frblResponsiveToView = _props$attributes$frb5 === void 0 ? '1' : _props$attributes$frb5,
      _props$attributes$frb6 = _props$attributes.frblAutoplay,
      frblAutoplay = _props$attributes$frb6 === void 0 ? '' : _props$attributes$frb6,
      _props$attributes$frb7 = _props$attributes.frblGap,
      frblGap = _props$attributes$frb7 === void 0 ? '20' : _props$attributes$frb7,
      _props$attributes$frb8 = _props$attributes.frblButtons,
      frblButtons = _props$attributes$frb8 === void 0 ? 'arrows' : _props$attributes$frb8,
      _props$attributes$frb9 = _props$attributes.frblRewind,
      frblRewind = _props$attributes$frb9 === void 0 ? true : _props$attributes$frb9,
      frblButtonColor = _props$attributes.frblButtonColor,
      frblButtonBgColor = _props$attributes.frblButtonBgColor,
      _props$attributes$frb0 = _props$attributes.frblButtonsPosition,
      frblButtonsPosition = _props$attributes$frb0 === void 0 ? 'side' : _props$attributes$frb0,
      _props$attributes$frb1 = _props$attributes.frblDisableOnDesktop,
      frblDisableOnDesktop = _props$attributes$frb1 === void 0 ? false : _props$attributes$frb1;

    // ── Editor carousel preview ──────────────────────────────────────────
    var stateRef = useRef(null);
    useEffect(function () {
      var mounted = true;
      var timer = null;
      function cleanup() {
        if (!stateRef.current) return;
        var _stateRef$current = stateRef.current,
          originalParent = _stateRef$current.originalParent,
          wrapper = _stateRef$current.wrapper,
          gridEl = _stateRef$current.gridEl,
          clones = _stateRef$current.clones;
        try {
          // Remove clones first
          (clones || []).forEach(function (c) {
            return c.parentNode && c.parentNode.removeChild(c);
          });
          if (wrapper.parentNode) {
            originalParent.replaceChild(gridEl, wrapper);
          }
          // Remove ALL inline styles we added
          ['display', 'flex-wrap', 'gap', 'transition', 'will-change', 'transform'].forEach(function (p) {
            return gridEl.style.removeProperty(p);
          });

          // Remove slide sizing from children
          Array.from(gridEl.children).filter(function (el) {
            return UUID_RE.test(el.dataset.block || '');
          }).forEach(function (slide) {
            slide.style.removeProperty('flex-shrink');
            slide.style.removeProperty('width');
            slide.style.removeProperty('min-width');
          });
        } catch (e) {}
        stateRef.current = null;
      }
      function init() {
        if (!mounted) return;
        cleanup();
        var editorDoc = getEditorDocument();

        // GB 2.x wraps blocks in a root element that shares the same data-block UUID.
        // Using data-type ensures we pick the inner element that actually IS the block.
        // For native blocks (core/group) there is no double-wrapper, so the fallback
        // to plain [data-block] is safe.
        var blockEl = editorDoc.querySelector("[data-block=\"".concat(props.clientId, "\"][data-type=\"").concat(props.name, "\"]"));
        if (!blockEl) {
          blockEl = editorDoc.querySelector("[data-block=\"".concat(props.clientId, "\"]"));
        }
        if (!blockEl) return;
        var gridEl = findGridEl(blockEl, props.name);
        if (!gridEl || gridEl.closest('.frbl-editor-carousel')) return;

        // Slides = direct children that are real blocks (UUID data-block).
        var slides = Array.from(gridEl.children).filter(function (el) {
          return UUID_RE.test(el.dataset.block || '');
        });
        if (slides.length === 0) return;
        var perView = Math.max(1, parseInt(frblItemsToView) || 4);
        var gap = Math.max(0, parseInt(frblGap) || 20);

        // ── Create the overflow:hidden wrapper ───────────────────────
        var originalParent = gridEl.parentNode;
        var wrapper = editorDoc.createElement('div');
        wrapper.className = 'frbl-editor-carousel';
        originalParent.replaceChild(wrapper, gridEl);
        wrapper.appendChild(gridEl);

        // ── Apply flex layout to the grid element itself ─────────────
        gridEl.style.display = 'flex';
        gridEl.style.flexWrap = 'nowrap';
        gridEl.style.gap = gap + 'px';
        gridEl.style.transition = 'transform 400ms ease';
        gridEl.style.willChange = 'transform';

        // ── Size each slide ──────────────────────────────────────────
        var totalWidth = wrapper.offsetWidth || 600;
        var slideWidth = Math.floor((totalWidth - gap * (perView - 1)) / perView);
        slides.forEach(function (slide) {
          slide.style.flexShrink = '0';
          slide.style.width = slideWidth + 'px';
          slide.style.minWidth = slideWidth + 'px';
        });

        // ── Trailing loop clones ─────────────────────────────────────
        // Add perView clones from the start after the last real slide so
        // the next-arrow transition looks smooth before snapping back.
        var clones = [];
        var directAppender = Array.from(gridEl.children).find(function (c) {
          return c.classList.contains('block-list-appender');
        });
        for (var i = 0; i < perView; i++) {
          var clone = slides[i % slides.length].cloneNode(true);
          clone.removeAttribute('data-block');
          clone.setAttribute('aria-hidden', 'true');
          clone.classList.add('frbl-slide-clone');
          clone.style.flexShrink = '0';
          clone.style.width = slideWidth + 'px';
          clone.style.minWidth = slideWidth + 'px';
          clone.style.pointerEvents = 'none';
          gridEl.insertBefore(clone, directAppender || null);
          clones.push(clone);
        }

        // ── Slide navigation (infinite loop) ─────────────────────────
        var idx = 0;
        var looping = false;
        function slideTo(n) {
          if (looping) return;
          if (n < 0) {
            // Backwards wrap: jump to end of real slides silently, then animate back one.
            gridEl.style.transition = 'none';
            idx = slides.length;
            gridEl.style.transform = "translateX(-".concat(idx * (slideWidth + gap), "px)");
            void gridEl.offsetWidth; // force reflow
            gridEl.style.transition = 'transform 400ms ease';
            idx = slides.length - 1;
            gridEl.style.transform = "translateX(-".concat(idx * (slideWidth + gap), "px)");
            return;
          }
          idx = n;
          gridEl.style.transform = "translateX(-".concat(idx * (slideWidth + gap), "px)");

          // After animating into the trailing clones zone, silently snap back to real position.
          if (idx >= slides.length) {
            looping = true;
            setTimeout(function () {
              gridEl.style.transition = 'none';
              idx = idx % slides.length;
              gridEl.style.transform = "translateX(-".concat(idx * (slideWidth + gap), "px)");
              void gridEl.offsetWidth;
              gridEl.style.transition = 'transform 400ms ease';
              looping = false;
            }, 420);
          }
        }

        // ── Arrows ───────────────────────────────────────────────────
        var btnColor = frblButtonColor || '#fff';
        var btnBg = frblButtonBgColor || 'rgba(0,0,0,0.45)';
        ['prev', 'next'].forEach(function (dir) {
          var btn = editorDoc.createElement('button');
          btn.type = 'button';
          btn.className = "frbl-editor-arrow frbl-editor-arrow-".concat(dir);
          btn.setAttribute('aria-label', dir === 'prev' ? 'Previous slide' : 'Next slide');
          btn.style.backgroundColor = btnBg;
          var d = dir === 'prev' ? 'M6 1L1 6L6 11' : 'M1 11L6 6L1 1';
          btn.innerHTML = "<svg width=\"7\" height=\"12\" viewBox=\"0 0 7 12\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"".concat(d, "\" stroke=\"").concat(btnColor, "\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>");
          btn.addEventListener('click', function (e) {
            e.stopPropagation();
            slideTo(dir === 'prev' ? idx - 1 : idx + 1);
          });
          wrapper.appendChild(btn);
        });
        stateRef.current = {
          originalParent: originalParent,
          wrapper: wrapper,
          gridEl: gridEl,
          clones: clones
        };
      }
      if (frblGridOption !== 'none') {
        timer = setTimeout(init, 300);
      } else {
        cleanup();
      }
      return function () {
        mounted = false;
        if (timer) clearTimeout(timer);
        cleanup();
      };
    }, [frblGridOption, frblItemsToView, frblGap, frblButtonColor, frblButtonBgColor, props.clientId]);
    // ── Inspector panel ──────────────────────────────────────────────────

    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Carousel Settings', 'frontblocks'),
      initialOpen: true
    }, /*#__PURE__*/React.createElement(SelectControl, {
      label: __('FrontBlocks Grid Option', 'frontblocks'),
      value: frblGridOption,
      options: [{
        label: __('None', 'frontblocks'),
        value: 'none'
      }, {
        label: __('Carousel', 'frontblocks'),
        value: 'carousel'
      }, {
        label: __('Slider', 'frontblocks'),
        value: 'slider'
      }],
      onChange: function onChange(value) {
        return props.setAttributes({
          frblGridOption: value
        });
      },
      help: __('This option gives the option to make carousel in your grid block.', 'frontblocks')
    }), frblGridOption !== 'none' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(TextControl, {
      label: __('Items to view (Desktop)', 'frontblocks'),
      value: frblItemsToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblItemsToView: value
        });
      },
      help: __('Number of items to show on desktop (>1200px)', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Items to view (Laptop)', 'frontblocks'),
      value: frblLaptopToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblLaptopToView: value
        });
      },
      help: __('Number of items to show on laptop (992px-1199px)', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Items to view (Tablet)', 'frontblocks'),
      value: frblTabletToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblTabletToView: value
        });
      },
      help: __('Number of items to show on tablet (768px-991px)', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Items to view (Mobile)', 'frontblocks'),
      value: frblResponsiveToView,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblResponsiveToView: value
        });
      },
      help: __('Number of items to show on mobile (<768px)', 'frontblocks')
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Autoplay (seconds)', 'frontblocks'),
      value: frblAutoplay,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblAutoplay: value
        });
      }
    }), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Gap (px)', 'frontblocks'),
      value: frblGap,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblGap: value
        });
      },
      help: __('Space between slides in pixels. Leave empty for 20.', 'frontblocks')
    }), frblGridOption === 'slider' && /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Rewind', 'frontblocks'),
      checked: frblRewind,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblRewind: value
        });
      }
    }), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Buttons', 'frontblocks'),
      value: frblButtons,
      options: [{
        label: __('None', 'frontblocks'),
        value: 'none'
      }, {
        label: __('Bullets', 'frontblocks'),
        value: 'bullets'
      }, {
        label: __('Arrows', 'frontblocks'),
        value: 'arrows'
      }],
      onChange: function onChange(value) {
        return props.setAttributes({
          frblButtons: value
        });
      }
    }), frblButtons === 'arrows' && /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Buttons Position', 'frontblocks'),
      value: frblButtonsPosition,
      options: [{
        label: __('Side', 'frontblocks'),
        value: 'side'
      }, {
        label: __('Bottom', 'frontblocks'),
        value: 'bottom'
      }],
      onChange: function onChange(value) {
        return props.setAttributes({
          frblButtonsPosition: value
        });
      }
    }), /*#__PURE__*/React.createElement(PanelColorSettings, {
      title: __('Button Colors', 'frontblocks'),
      colorSettings: [{
        value: frblButtonColor,
        onChange: function onChange(color) {
          return props.setAttributes({
            frblButtonColor: color
          });
        },
        label: __('Color button', 'frontblocks')
      }, {
        value: frblButtonBgColor,
        onChange: function onChange(color) {
          return props.setAttributes({
            frblButtonBgColor: color
          });
        },
        label: __('Color background button', 'frontblocks')
      }]
    }), /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Disable on Desktop', 'frontblocks'),
      checked: frblDisableOnDesktop,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblDisableOnDesktop: value
        });
      },
      help: __('If enabled, carousel/slider will only work on mobile devices.', 'frontblocks')
    })))));
  };
}
addFilter('editor.BlockEdit', 'frontblocks/gb-grid-carousel-panel', addCustomCarouselPanel);
