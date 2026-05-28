"use strict";

function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
var addFilter = wp.hooks.addFilter;
var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  SelectControl = _wp$components.SelectControl;
var useSelect = wp.data.useSelect;
var __ = wp.i18n.__;
var Fragment = wp.element.Fragment;

/**
 * Blocks that support meta bindings and which of their attributes are bindable.
 */
var BINDABLE_BLOCKS = {
  'core/paragraph': {
    content: __('Contenido', 'frontblocks')
  },
  'core/heading': {
    content: __('Contenido', 'frontblocks')
  },
  'core/image': {
    url: __('URL de imagen', 'frontblocks'),
    alt: __('Texto alternativo', 'frontblocks')
  },
  'core/button': {
    text: __('Texto del botón', 'frontblocks'),
    url: __('URL', 'frontblocks')
  }
};
var SOURCE_KEY = frblMetaFields.sourceKey;
var ALL_FIELDS = frblMetaFields.fields; // { post_type: [ { key, label, type }, … ] }

/**
 * HOC — injects the "Meta dinámico" panel into matching blocks.
 */
var withMetaBindingPanel = createHigherOrderComponent(function (BlockEdit) {
  return function FrblMetaBlockEdit(props) {
    var name = props.name,
      attributes = props.attributes,
      setAttributes = props.setAttributes;
    var bindableAttrs = BINDABLE_BLOCKS[name];

    // Block context postType covers query loops; fall back to current post type for singles.
    var editorPostType = useSelect(function (select) {
      var editor = select('core/editor');
      return editor ? editor.getCurrentPostType() : null;
    });
    var postType = props.context && props.context.postType || editorPostType;
    if (!bindableAttrs || !postType) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var availableFields = ALL_FIELDS[postType] || [];
    if (0 === availableFields.length) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var currentBindings = attributes.metadata && attributes.metadata.bindings ? attributes.metadata.bindings : {};
    var fieldOptions = [{
      label: __('— Ninguno —', 'frontblocks'),
      value: ''
    }].concat(_toConsumableArray(availableFields.map(function (f) {
      return {
        label: f.label + ' — ' + f.key,
        value: f.key
      };
    })));
    function getFieldType(key) {
      var field = availableFields.find(function (f) {
        return f.key === key;
      });
      return field ? field.type : 'text';
    }
    function setBinding(attr, fieldKey) {
      var bindings = Object.assign({}, currentBindings);
      if (!fieldKey) {
        delete bindings[attr];
      } else {
        bindings[attr] = {
          source: SOURCE_KEY,
          args: {
            key: fieldKey,
            type: getFieldType(fieldKey)
          }
        };
      }
      var metadata = Object.assign({}, attributes.metadata || {});
      if (0 === Object.keys(bindings).length) {
        delete metadata.bindings;
      } else {
        metadata.bindings = bindings;
      }
      setAttributes({
        metadata: metadata
      });
    }
    function getBoundKey(attr) {
      var binding = currentBindings[attr];
      return binding && binding.args && binding.args.key ? binding.args.key : '';
    }
    var hasBindings = Object.keys(currentBindings).length > 0;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('Meta dinámico', 'frontblocks'),
      initialOpen: hasBindings
    }, Object.entries(bindableAttrs).map(function (_ref) {
      var _ref2 = _slicedToArray(_ref, 2),
        attr = _ref2[0],
        attrLabel = _ref2[1];
      return /*#__PURE__*/React.createElement(SelectControl, {
        key: attr,
        label: attrLabel,
        value: getBoundKey(attr),
        options: fieldOptions,
        onChange: function onChange(val) {
          return setBinding(attr, val);
        }
      });
    }), hasBindings && /*#__PURE__*/React.createElement("p", {
      style: {
        fontSize: '12px',
        color: '#757575',
        marginTop: '8px',
        marginBottom: 0
      }
    }, __('El valor real se renderiza en el frontend.', 'frontblocks')))));
  };
}, 'withMetaBindingPanel');
addFilter('editor.BlockEdit', 'frontblocks/meta-binding', withMetaBindingPanel);
