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
  if (name === 'core/query') {
    return blockEl.querySelector('.wp-block-post-template') || null;
  }
  return null;
}
function addCustomCarouselPanel(BlockEdit) {
  return function (props) {
    if (props.name !== 'generateblocks/grid' && props.name !== 'generateblocks/element' && props.name !== 'core/group' && props.name !== 'core/query') {
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
          gridEl = _stateRef$current.gridEl,
          slides = _stateRef$current.slides,
          spacer = _stateRef$current.spacer,
          prevBtn = _stateRef$current.prevBtn,
          nextBtn = _stateRef$current.nextBtn,
          resizeObs = _stateRef$current.resizeObs,
          scrollFn = _stateRef$current.scrollFn;

        // Disconnect observer and scroll listener.
        if (resizeObs) resizeObs.disconnect();
        var editorDoc = getEditorDocument();
        var editorScrollEl = editorDoc.documentElement || editorDoc.body;
        if (scrollFn) editorScrollEl.removeEventListener('scroll', scrollFn, true);

        // Remove arrows from outer window body (completely outside React).
        [prevBtn, nextBtn].forEach(function (btn) {
          if (btn && btn.parentNode) btn.parentNode.removeChild(btn);
        });

        // Remove right spacer element.
        if (spacer && spacer.parentNode) spacer.parentNode.removeChild(spacer);

        // Restore gridEl styles.
        try {
          gridEl.classList.remove('frbl-carousel-noscrollbar');
          ['display', 'flex-wrap', 'gap', 'overflow-x', 'scroll-behavior', 'padding-left', 'padding-right'].forEach(function (p) {
            return gridEl.style.removeProperty(p);
          });
          slides.forEach(function (slide) {
            ['flex-shrink', 'width', 'min-width', 'margin-left', 'margin-right', 'grid-column'].forEach(function (p) {
              return slide.style.removeProperty(p);
            });
          });
        } catch (e) {}
        stateRef.current = null;
      }
      function init() {
        if (!mounted) return;
        cleanup();
        var editorDoc = getEditorDocument();

        // GB 2.x wraps blocks in a root element that shares the same data-block UUID.
        // Use data-type to select the correct inner element; fall back for native blocks.
        var blockEl = editorDoc.querySelector("[data-block=\"".concat(props.clientId, "\"][data-type=\"").concat(props.name, "\"]"));
        if (!blockEl) {
          blockEl = editorDoc.querySelector("[data-block=\"".concat(props.clientId, "\"]"));
        }
        if (!blockEl) return;
        var gridEl = findGridEl(blockEl, props.name);
        if (!gridEl) return;

        // Slides = direct children that are real blocks (UUID data-block).
        var slides = Array.from(gridEl.children).filter(function (el) {
          return UUID_RE.test(el.dataset.block || '');
        });
        if (slides.length === 0) return;
        var perView = Math.max(1, parseInt(frblItemsToView) || 4);
        var gap = Math.max(0, parseInt(frblGap) || 20);
        var btnColor = frblButtonColor || '#fff';
        var btnBg = frblButtonBgColor || 'rgba(0,0,0,0.45)';

        // Measure content-box width before any style changes.
        var editorWin = editorDoc.defaultView || window;
        var ARROW_W = 40; // px reserved on each side for arrow buttons.
        var cs = editorWin.getComputedStyle(gridEl);
        var innerW = gridEl.getBoundingClientRect().width - (parseFloat(cs.paddingLeft) || 0) - (parseFloat(cs.paddingRight) || 0);
        var totalWidth = Math.round(innerW) || 600;
        // Content area after reserving space for both arrow buttons.
        var contentW = totalWidth - ARROW_W * 2;
        var slideWidth = Math.round((contentW - gap * (perView - 1)) / perView);
        var step = slideWidth + gap;

        // ── Apply scroll-based layout directly to gridEl ─────────────
        // No DOM restructuring → no React reconciliation conflicts.
        gridEl.classList.add('frbl-carousel-noscrollbar');
        gridEl.style.display = 'flex';
        gridEl.style.flexWrap = 'nowrap';
        gridEl.style.gap = gap + 'px';
        gridEl.style.overflowX = 'scroll';
        gridEl.style.scrollBehavior = 'smooth';
        // paddingLeft creates left space for the prev arrow.
        // paddingRight is NOT used because Chrome ignores it in scroll extent;
        // a spacer flex child is appended instead to guarantee right-side space.
        gridEl.style.paddingLeft = ARROW_W + 'px';
        gridEl.style.paddingRight = '0';
        slides.forEach(function (slide) {
          slide.style.flexShrink = '0';
          slide.style.width = slideWidth + 'px';
          slide.style.minWidth = slideWidth + 'px';
          slide.style.marginLeft = '0';
          slide.style.marginRight = '0';
          slide.style.gridColumn = 'unset';
        });

        // Right-side spacer: Chrome does not include padding-right in horizontal
        // scroll extent, so we use a real flex child to guarantee right space.
        var appender = gridEl.querySelector('.block-list-appender');
        var spacer = editorDoc.createElement('span');
        spacer.setAttribute('data-frbl-spacer', '1');
        spacer.style.cssText = "display:block;flex-shrink:0;width:".concat(ARROW_W, "px;min-width:").concat(ARROW_W, "px;");
        gridEl.insertBefore(spacer, appender || null);

        // ── Navigation (scrollLeft, infinite loop) ───────────────────
        var idx = 0;
        function scrollTo(n) {
          var total = slides.length;
          if (n >= total) {
            // Forward past last: instant snap to 0 then continue.
            gridEl.style.scrollBehavior = 'auto';
            gridEl.scrollLeft = 0;
            void gridEl.offsetWidth;
            gridEl.style.scrollBehavior = 'smooth';
            idx = 0;
            return;
          }
          if (n < 0) {
            // Back past first: instant snap to last.
            gridEl.style.scrollBehavior = 'auto';
            gridEl.scrollLeft = Math.round((total - 1) * step);
            void gridEl.offsetWidth;
            gridEl.style.scrollBehavior = 'smooth';
            idx = total - 1;
            return;
          }
          idx = n;
          gridEl.scrollLeft = Math.round(idx * step);
        }

        // ── Arrows in OUTER document body ────────────────────────────
        // Placing them outside the iframe means React never touches them.
        var iframe = document.querySelector('iframe[name="editor-canvas"]');
        function updateArrowPos() {
          if (!stateRef.current) return;
          var ifrRect = iframe ? iframe.getBoundingClientRect() : {
            top: 0,
            left: 0
          };
          var rect = gridEl.getBoundingClientRect(); // coords in iframe viewport
          var midY = Math.round(ifrRect.top + rect.top + rect.height / 2 - 16);
          prevBtn.style.top = midY + 'px';
          prevBtn.style.left = Math.round(ifrRect.left + rect.left + 8) + 'px';
          nextBtn.style.top = midY + 'px';
          nextBtn.style.right = Math.round(window.innerWidth - (ifrRect.left + rect.right) + 8) + 'px';
        }
        function makeArrow(dir) {
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = "frbl-editor-arrow frbl-editor-arrow-".concat(dir);
          btn.setAttribute('aria-label', dir === 'prev' ? 'Previous slide' : 'Next slide');
          btn.style.cssText = "position:fixed;z-index:99999;background-color:".concat(btnBg, ";");
          var d = dir === 'prev' ? 'M6 1L1 6L6 11' : 'M1 11L6 6L1 1';
          btn.innerHTML = "<svg width=\"7\" height=\"12\" viewBox=\"0 0 7 12\" fill=\"none\"><path d=\"".concat(d, "\" stroke=\"").concat(btnColor, "\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/></svg>");
          btn.addEventListener('click', function (e) {
            e.stopPropagation();
            scrollTo(dir === 'prev' ? idx - 1 : idx + 1);
          });
          document.body.appendChild(btn);
          return btn;
        }
        var prevBtn = makeArrow('prev');
        var nextBtn = makeArrow('next');
        updateArrowPos();

        // Keep arrows positioned correctly when the editor scrolls or resizes.
        var resizeObs = new ResizeObserver(updateArrowPos);
        resizeObs.observe(gridEl);
        var editorScrollEl = editorDoc.documentElement || editorDoc.body;
        var scrollFn = updateArrowPos;
        editorScrollEl.addEventListener('scroll', scrollFn, {
          passive: true,
          capture: true
        });
        stateRef.current = {
          gridEl: gridEl,
          slides: slides,
          spacer: spacer,
          prevBtn: prevBtn,
          nextBtn: nextBtn,
          resizeObs: resizeObs,
          scrollFn: scrollFn
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
