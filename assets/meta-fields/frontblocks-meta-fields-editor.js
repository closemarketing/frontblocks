"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
var addFilter = wp.hooks.addFilter;
var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var BlockControls = wp.blockEditor.BlockControls;
var _wp$components = wp.components,
  ToolbarButton = _wp$components.ToolbarButton,
  Modal = _wp$components.Modal,
  TextControl = _wp$components.TextControl,
  SelectControl = _wp$components.SelectControl,
  Button = _wp$components.Button,
  Spinner = _wp$components.Spinner,
  Flex = _wp$components.Flex,
  FlexItem = _wp$components.FlexItem,
  Text = _wp$components.__experimentalText;
var _wp$element = wp.element,
  useState = _wp$element.useState,
  useEffect = _wp$element.useEffect,
  Fragment = _wp$element.Fragment;
var useSelect = wp.data.useSelect;
var __ = wp.i18n.__;
var apiFetch = wp.apiFetch;
var BINDABLE = {
  'core/paragraph': 'content',
  'core/heading': 'content'
};
function stripHtml(html) {
  return html ? html.replace(/<[^>]+>/g, '') : '';
}
var withConvertToMeta = createHigherOrderComponent(function (BlockEdit) {
  return function ConvertToMetaWrapper(props) {
    var name = props.name,
      attributes = props.attributes,
      setAttributes = props.setAttributes;
    var attrName = BINDABLE[name];
    if (!attrName) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var _useState = useState(false),
      _useState2 = _slicedToArray(_useState, 2),
      isOpen = _useState2[0],
      setIsOpen = _useState2[1];
    var _useState3 = useState('new'),
      _useState4 = _slicedToArray(_useState3, 2),
      mode = _useState4[0],
      setMode = _useState4[1];
    var _useState5 = useState(''),
      _useState6 = _slicedToArray(_useState5, 2),
      metaKey = _useState6[0],
      setMetaKey = _useState6[1];
    var _useState7 = useState(''),
      _useState8 = _slicedToArray(_useState7, 2),
      metaLabel = _useState8[0],
      setMetaLabel = _useState8[1];
    var _useState9 = useState(''),
      _useState0 = _slicedToArray(_useState9, 2),
      metaValue = _useState0[0],
      setMetaValue = _useState0[1];
    var _useState1 = useState([]),
      _useState10 = _slicedToArray(_useState1, 2),
      existingFields = _useState10[0],
      setExistingFields = _useState10[1];
    var _useState11 = useState(''),
      _useState12 = _slicedToArray(_useState11, 2),
      selectedExisting = _useState12[0],
      setSelectedExisting = _useState12[1];
    var _useState13 = useState(false),
      _useState14 = _slicedToArray(_useState13, 2),
      isLoading = _useState14[0],
      setIsLoading = _useState14[1];
    var _useState15 = useState(''),
      _useState16 = _slicedToArray(_useState15, 2),
      errorMsg = _useState16[0],
      setErrorMsg = _useState16[1];
    var _useSelect = useSelect(function (select) {
        return {
          postType: select('core/editor').getCurrentPostType(),
          postId: select('core/editor').getCurrentPostId()
        };
      }),
      postType = _useSelect.postType,
      postId = _useSelect.postId;
    var frblMeta = attributes.metadata && attributes.metadata.frblMeta || {};
    var isAlreadyBound = !!frblMeta[attrName];
    var boundKey = isAlreadyBound ? frblMeta[attrName].key || '' : '';
    useEffect(function () {
      if (!isOpen || !postType) {
        return;
      }
      apiFetch({
        path: '/frontblocks/v1/meta-fields?post_type=' + postType
      }).then(function (fields) {
        setExistingFields(fields);
        // Only auto-switch to existing when not already bound (bound blocks set mode in openModal).
        if (fields.length > 0 && !isAlreadyBound) {
          setMode('existing');
        }
      }).catch(function () {
        return setExistingFields([]);
      });
    }, [isOpen, postType]);
    function openModal() {
      setMetaValue(stripHtml(attributes[attrName] || ''));
      if (isAlreadyBound) {
        setMode('existing');
        setSelectedExisting(boundKey);
      }
      setIsOpen(true);
    }
    function resetForm() {
      setMode('new');
      setMetaKey('');
      setMetaLabel('');
      setMetaValue('');
      setSelectedExisting('');
      setErrorMsg('');
    }
    function handleConfirm() {
      return _handleConfirm.apply(this, arguments);
    }
    function _handleConfirm() {
      _handleConfirm = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var fieldKey, fieldType, res, newFrblMeta, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              setIsLoading(true);
              setErrorMsg('');
              fieldKey = '';
              fieldType = 'text';
              _context.p = 1;
              if (!('new' === mode)) {
                _context.n = 4;
                break;
              }
              if (metaKey.trim()) {
                _context.n = 2;
                break;
              }
              setErrorMsg(__('El nombre del campo (key) es obligatorio.', 'frontblocks'));
              setIsLoading(false);
              return _context.a(2);
            case 2:
              _context.n = 3;
              return apiFetch({
                path: '/frontblocks/v1/meta-fields',
                method: 'POST',
                data: {
                  post_type: postType,
                  key: metaKey.trim(),
                  label: metaLabel.trim() || metaKey.trim(),
                  type: 'text'
                }
              });
            case 3:
              res = _context.v;
              fieldKey = res.field.key;
              fieldType = res.field.type;
              _context.n = 6;
              break;
            case 4:
              if (selectedExisting) {
                _context.n = 5;
                break;
              }
              setErrorMsg(__('Selecciona un meta existente.', 'frontblocks'));
              setIsLoading(false);
              return _context.a(2);
            case 5:
              fieldKey = selectedExisting;
              fieldType = (existingFields.find(function (f) {
                return f.key === selectedExisting;
              }) || {}).type || 'text';
            case 6:
              if (!metaValue.trim()) {
                _context.n = 7;
                break;
              }
              _context.n = 7;
              return apiFetch({
                path: '/frontblocks/v1/save-meta',
                method: 'POST',
                data: {
                  post_id: postId,
                  key: fieldKey,
                  value: metaValue.trim()
                }
              });
            case 7:
              newFrblMeta = Object.assign({}, frblMeta);
              newFrblMeta[attrName] = {
                key: fieldKey,
                type: fieldType
              };
              setAttributes(_defineProperty({
                metadata: Object.assign({}, attributes.metadata || {}, {
                  frblMeta: newFrblMeta
                })
              }, attrName, metaValue.trim() || attributes[attrName]));
              setIsOpen(false);
              resetForm();
              _context.n = 9;
              break;
            case 8:
              _context.p = 8;
              _t = _context.v;
              setErrorMsg(__('Error al registrar el meta. Inténtalo de nuevo.', 'frontblocks'));
            case 9:
              setIsLoading(false);
            case 10:
              return _context.a(2);
          }
        }, _callee, null, [[1, 8]]);
      }));
      return _handleConfirm.apply(this, arguments);
    }
    var existingOptions = [{
      label: __('— Elige un campo —', 'frontblocks'),
      value: ''
    }].concat(_toConsumableArray(existingFields.map(function (f) {
      return {
        label: (f.label || f.key) + '  ·  ' + f.key,
        value: f.key
      };
    })));
    var tabStyle = function tabStyle(active) {
      return {
        padding: '6px 14px',
        fontSize: '13px',
        fontWeight: active ? '600' : '400',
        border: '1px solid ' + (active ? '#007cba' : '#ddd'),
        borderRadius: '4px',
        background: active ? '#007cba' : '#fff',
        color: active ? '#fff' : '#555',
        cursor: 'pointer',
        transition: 'all 0.15s'
      };
    };
    var fieldGroupStyle = {
      background: '#f9f9f9',
      border: '1px solid #e5e5e5',
      borderRadius: '6px',
      padding: '16px',
      marginBottom: '16px'
    };
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(BlockControls, {
      group: "other"
    }, isAlreadyBound ? /*#__PURE__*/React.createElement(ToolbarButton, {
      icon: "database",
      label: __('Meta vinculado: ' + boundKey, 'frontblocks'),
      onClick: function onClick() {
        return openModal();
      },
      className: "frbl-meta-bound-btn"
    }) : /*#__PURE__*/React.createElement(ToolbarButton, {
      icon: "database-add",
      label: __('Convertir a meta', 'frontblocks'),
      onClick: openModal
    })), isOpen && /*#__PURE__*/React.createElement(Modal, {
      title: __('Vincular a meta dinámica', 'frontblocks'),
      onRequestClose: function onRequestClose() {
        setIsOpen(false);
        resetForm();
      },
      style: {
        maxWidth: '480px',
        width: '100%'
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        gap: '8px',
        marginBottom: '20px'
      }
    }, /*#__PURE__*/React.createElement("button", {
      style: tabStyle('new' === mode),
      onClick: function onClick() {
        return setMode('new');
      }
    }, __('+ Crear nuevo', 'frontblocks')), existingFields.length > 0 && /*#__PURE__*/React.createElement("button", {
      style: tabStyle('existing' === mode),
      onClick: function onClick() {
        return setMode('existing');
      }
    }, __('Usar existente', 'frontblocks'))), 'new' === mode ? /*#__PURE__*/React.createElement("div", {
      style: fieldGroupStyle
    }, /*#__PURE__*/React.createElement("p", {
      style: {
        margin: '0 0 12px',
        fontSize: '12px',
        color: '#666',
        textTransform: 'uppercase',
        letterSpacing: '0.05em',
        fontWeight: '600'
      }
    }, __('Nuevo campo meta', 'frontblocks')), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Key (nombre interno)', 'frontblocks'),
      value: metaKey,
      onChange: setMetaKey,
      placeholder: "fecha_proyecto",
      help: __('Letras minúsculas, números y guiones bajos.', 'frontblocks'),
      __nextHasNoMarginBottom: true
    }), /*#__PURE__*/React.createElement("div", {
      style: {
        marginTop: '12px'
      }
    }, /*#__PURE__*/React.createElement(TextControl, {
      label: __('Etiqueta legible', 'frontblocks'),
      value: metaLabel,
      onChange: setMetaLabel,
      placeholder: __('Fecha del proyecto', 'frontblocks'),
      __nextHasNoMarginBottom: true
    }))) : /*#__PURE__*/React.createElement("div", {
      style: fieldGroupStyle
    }, /*#__PURE__*/React.createElement("p", {
      style: {
        margin: '0 0 12px',
        fontSize: '12px',
        color: '#666',
        textTransform: 'uppercase',
        letterSpacing: '0.05em',
        fontWeight: '600'
      }
    }, __('Campo existente', 'frontblocks')), /*#__PURE__*/React.createElement(SelectControl, {
      label: __('Selecciona un campo', 'frontblocks'),
      value: selectedExisting,
      options: existingOptions,
      onChange: setSelectedExisting,
      __nextHasNoMarginBottom: true
    })), /*#__PURE__*/React.createElement("div", {
      style: _objectSpread(_objectSpread({}, fieldGroupStyle), {}, {
        background: '#fff'
      })
    }, /*#__PURE__*/React.createElement("p", {
      style: {
        margin: '0 0 12px',
        fontSize: '12px',
        color: '#666',
        textTransform: 'uppercase',
        letterSpacing: '0.05em',
        fontWeight: '600'
      }
    }, __('Valor para este post', 'frontblocks')), /*#__PURE__*/React.createElement(TextControl, {
      label: __('Contenido', 'frontblocks'),
      value: metaValue,
      onChange: setMetaValue,
      help: __('Se guardará en la base de datos y se mostrará en el editor.', 'frontblocks'),
      __nextHasNoMarginBottom: true
    })), !!errorMsg && /*#__PURE__*/React.createElement("div", {
      style: {
        background: '#fff0f0',
        border: '1px solid #fcc',
        borderRadius: '4px',
        padding: '10px 14px',
        marginBottom: '12px',
        color: '#cc1818',
        fontSize: '13px'
      }
    }, errorMsg), /*#__PURE__*/React.createElement("div", {
      style: _defineProperty({
        display: 'flex',
        gap: '8px',
        justifyContent: 'flex-end',
        paddingTop: '4px',
        borderTop: '1px solid #eee',
        marginTop: '4px'
      }, "paddingTop", '16px')
    }, /*#__PURE__*/React.createElement(Button, {
      variant: "tertiary",
      onClick: function onClick() {
        setIsOpen(false);
        resetForm();
      }
    }, __('Cancelar', 'frontblocks')), /*#__PURE__*/React.createElement(Button, {
      variant: "primary",
      onClick: handleConfirm,
      disabled: isLoading,
      style: {
        minWidth: '100px'
      }
    }, isLoading ? /*#__PURE__*/React.createElement(Spinner, null) : __('Vincular', 'frontblocks')))));
  };
}, 'withConvertToMeta');
addFilter('editor.BlockEdit', 'frontblocks/convert-to-meta', withConvertToMeta);
