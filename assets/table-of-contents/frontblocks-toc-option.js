"use strict";

function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
var registerBlockType = wp.blocks.registerBlockType;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps,
  PanelColorSettings = _wp$blockEditor.PanelColorSettings;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  TextControl = _wp$components.TextControl,
  SelectControl = _wp$components.SelectControl,
  ToggleControl = _wp$components.ToggleControl,
  RangeControl = _wp$components.RangeControl,
  Notice = _wp$components.Notice;
var useSelect = wp.data.useSelect;
var __ = wp.i18n.__;

/**
 * Recursively collect every core/heading block in the current post, in
 * document order, so the editor can show a best-effort live preview.
 *
 * This only sees core Heading blocks: GenerateBlocks and other third-party
 * heading output isn't a distinct, reliably identifiable block type in the
 * editor's block tree, so it can't be previewed here. The actual published
 * page is unaffected by this limitation — the frontend discovers every
 * <h1>-<h6> tag in the final rendered HTML regardless of its source block.
 *
 * @param {Array} blocks Blocks to scan (recurses into innerBlocks).
 * @return {Array} { level, text }
 */
function collectHeadings(blocks) {
  var headings = [];
  blocks.forEach(function (block) {
    if ('core/heading' === block.name) {
      var text = (block.attributes.content || '').replace(/<[^>]*>/g, '').trim();
      if (text) {
        headings.push({
          level: block.attributes.level || 2,
          text: text
        });
      }
    }
    if (block.innerBlocks && block.innerBlocks.length) {
      headings.push.apply(headings, _toConsumableArray(collectHeadings(block.innerBlocks)));
    }
  });
  return headings;
}

/**
 * Edit component for the Table of Contents block.
 */
function TableOfContentsEdit(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var title = attributes.title,
    listStyle = attributes.listStyle,
    accentColor = attributes.accentColor,
    collapsible = attributes.collapsible,
    collapsedByDefault = attributes.collapsedByDefault,
    sticky = attributes.sticky,
    minLevel = attributes.minLevel,
    maxLevel = attributes.maxLevel;
  var blockProps = useBlockProps({
    className: 'frbl-toc-editor-preview' + (sticky ? ' frbl-toc-editor-preview--sticky' : '')
  });
  var headings = useSelect(function (select) {
    return collectHeadings(select('core/block-editor').getBlocks());
  }, []).filter(function (heading) {
    return heading.level >= minLevel && heading.level <= maxLevel;
  });
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Table of Contents', 'frontblocks'),
    initialOpen: true
  }, /*#__PURE__*/React.createElement(TextControl, {
    label: __('Title', 'frontblocks'),
    value: title,
    onChange: function onChange(value) {
      return setAttributes({
        title: value
      });
    }
  }), /*#__PURE__*/React.createElement(SelectControl, {
    label: __('List style', 'frontblocks'),
    value: listStyle,
    options: [{
      label: __('Bulleted', 'frontblocks'),
      value: 'unordered'
    }, {
      label: __('Numbered', 'frontblocks'),
      value: 'ordered'
    }, {
      label: __('Plain (no bullets)', 'frontblocks'),
      value: 'plain'
    }],
    onChange: function onChange(value) {
      return setAttributes({
        listStyle: value
      });
    }
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('From heading level', 'frontblocks'),
    value: minLevel,
    min: 2,
    max: 6,
    onChange: function onChange(value) {
      return setAttributes({
        minLevel: value
      });
    }
  }), /*#__PURE__*/React.createElement(RangeControl, {
    label: __('To heading level', 'frontblocks'),
    value: maxLevel,
    min: 2,
    max: 6,
    onChange: function onChange(value) {
      return setAttributes({
        maxLevel: value
      });
    }
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Collapsible', 'frontblocks'),
    checked: collapsible,
    onChange: function onChange(value) {
      return setAttributes({
        collapsible: value
      });
    },
    help: __('Renders as a native, keyboard-accessible expand/collapse control.', 'frontblocks')
  }), collapsible && /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Collapsed by default', 'frontblocks'),
    checked: collapsedByDefault,
    onChange: function onChange(value) {
      return setAttributes({
        collapsedByDefault: value
      });
    }
  }), /*#__PURE__*/React.createElement(ToggleControl, {
    label: __('Sticky', 'frontblocks'),
    checked: sticky,
    onChange: function onChange(value) {
      return setAttributes({
        sticky: value
      });
    },
    help: __('Keeps the Table of Contents in view while visitors scroll.', 'frontblocks')
  })), /*#__PURE__*/React.createElement(PanelColorSettings, {
    title: __('Color', 'frontblocks'),
    colorSettings: [{
      value: accentColor,
      onChange: function onChange(color) {
        return setAttributes({
          accentColor: color || ''
        });
      },
      label: __('Accent color', 'frontblocks')
    }]
  })), /*#__PURE__*/React.createElement("nav", _extends({}, blockProps, {
    style: accentColor ? {
      '--frbl-toc-accent': accentColor
    } : undefined
  }), /*#__PURE__*/React.createElement("p", {
    className: "frbl-toc__title"
  }, title), headings.length > 0 ? /*#__PURE__*/React.createElement(Notice, {
    status: "info",
    isDismissible: false
  }, __('Preview shows core Heading blocks only. GenerateBlocks headings and final anchors are generated when the page is published.', 'frontblocks')) : /*#__PURE__*/React.createElement(Notice, {
    status: "info",
    isDismissible: false
  }, __('No headings found yet in this range. Add some Heading blocks to this post.', 'frontblocks')), headings.length > 0 && /*#__PURE__*/React.createElement("ul", {
    className: 'frbl-toc__list frbl-toc__list--' + listStyle
  }, headings.map(function (heading, index) {
    return /*#__PURE__*/React.createElement("li", {
      key: index,
      className: 'frbl-toc__item frbl-toc__item--level-' + heading.level
    }, heading.text);
  }))));
}
registerBlockType('frontblocks/table-of-contents', {
  title: __('Table of Contents', 'frontblocks'),
  description: __('An accessible table of contents generated from the headings in this post.', 'frontblocks'),
  category: 'widgets',
  icon: 'list-view',
  supports: {
    html: false
  },
  attributes: {
    title: {
      type: 'string',
      default: __('Table of Contents', 'frontblocks')
    },
    listStyle: {
      type: 'string',
      default: 'unordered'
    },
    accentColor: {
      type: 'string',
      default: ''
    },
    collapsible: {
      type: 'boolean',
      default: false
    },
    collapsedByDefault: {
      type: 'boolean',
      default: false
    },
    sticky: {
      type: 'boolean',
      default: false
    },
    minLevel: {
      type: 'number',
      default: 2
    },
    maxLevel: {
      type: 'number',
      default: 4
    }
  },
  edit: TableOfContentsEdit,
  save: function save() {
    return null;
  }
});
