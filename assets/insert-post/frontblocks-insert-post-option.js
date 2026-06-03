"use strict";

function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
// Insert Post Block Component
var addFilter = wp.hooks.addFilter;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useState = _wp$element.useState,
  useEffect = _wp$element.useEffect,
  useRef = _wp$element.useRef;
var _wp$blockEditor = wp.blockEditor,
  InspectorControls = _wp$blockEditor.InspectorControls,
  useBlockProps = _wp$blockEditor.useBlockProps,
  RichText = _wp$blockEditor.RichText;
var _wp$components = wp.components,
  SelectControl = _wp$components.SelectControl,
  TextControl = _wp$components.TextControl,
  PanelBody = _wp$components.PanelBody,
  Button = _wp$components.Button,
  Spinner = _wp$components.Spinner,
  Notice = _wp$components.Notice,
  Card = _wp$components.Card,
  CardBody = _wp$components.CardBody,
  CardHeader = _wp$components.CardHeader,
  ToggleControl = _wp$components.ToggleControl;
var __ = wp.i18n.__;
var _wp = wp,
  apiFetch = _wp.apiFetch;

// Custom Insert Post Block
function InsertPostBlock(props) {
  var attributes = props.attributes,
    setAttributes = props.setAttributes;
  var _attributes$selectedP = attributes.selectedPostId,
    selectedPostId = _attributes$selectedP === void 0 ? 0 : _attributes$selectedP,
    _attributes$selectedP2 = attributes.selectedPostType,
    selectedPostType = _attributes$selectedP2 === void 0 ? 'post' : _attributes$selectedP2,
    _attributes$selectedP3 = attributes.selectedPostTitle,
    selectedPostTitle = _attributes$selectedP3 === void 0 ? '' : _attributes$selectedP3,
    _attributes$selectedP4 = attributes.selectedPostContent,
    selectedPostContent = _attributes$selectedP4 === void 0 ? '' : _attributes$selectedP4,
    _attributes$className = attributes.className,
    className = _attributes$className === void 0 ? '' : _attributes$className;
  var _useState = useState(''),
    _useState2 = _slicedToArray(_useState, 2),
    searchTerm = _useState2[0],
    setSearchTerm = _useState2[1];
  var _useState3 = useState(false),
    _useState4 = _slicedToArray(_useState3, 2),
    isSearching = _useState4[0],
    setIsSearching = _useState4[1];
  var _useState5 = useState(''),
    _useState6 = _slicedToArray(_useState5, 2),
    searchError = _useState6[0],
    setSearchError = _useState6[1];
  var _useState7 = useState(false),
    _useState8 = _slicedToArray(_useState7, 2),
    showSearch = _useState8[0],
    setShowSearch = _useState8[1];
  var searchInputRef = useRef(null);

  // Get available post types
  var _useState9 = useState([{
      label: __('Posts', 'frontblocks'),
      value: 'post'
    }, {
      label: __('Pages', 'frontblocks'),
      value: 'page'
    }]),
    _useState0 = _slicedToArray(_useState9, 2),
    postTypes = _useState0[0],
    setPostTypes = _useState0[1];
  useEffect(function () {
    // Get custom post types
    apiFetch({
      path: '/wp/v2/types'
    }).then(function (types) {
      var customTypes = Object.keys(types).filter(function (type) {
        return type !== 'post' && type !== 'page';
      }).map(function (type) {
        return {
          label: types[type].name,
          value: type
        };
      });
      setPostTypes(function (prev) {
        return [].concat(_toConsumableArray(prev), _toConsumableArray(customTypes));
      });
    }).catch(function () {
      // If API fails, continue with default post types
    });
  }, []);

  // Initialize jQuery autocomplete when component mounts or search is shown
  useEffect(function () {
    if (showSearch && searchInputRef.current && typeof jQuery !== 'undefined') {
      var $input = jQuery(searchInputRef.current);

      // Check if autocomplete is already initialized before destroying
      if ($input.hasClass('ui-autocomplete-input')) {
        try {
          $input.autocomplete('destroy');
        } catch (e) {
          // If destroy fails, remove the autocomplete class manually
          $input.removeClass('ui-autocomplete-input');
        }
      }

      // Initialize autocomplete
      $input.autocomplete({
        source: function source(request, response) {
          if (request.term.length < 2) {
            response([]);
            return;
          }
          setIsSearching(true);
          setSearchError('');
          var formData = new FormData();
          formData.append('action', 'frbl_search_posts');
          formData.append('nonce', frblInsertPost.nonce);
          formData.append('search', request.term);
          formData.append('post_type', selectedPostType);
          fetch(frblInsertPost.ajaxUrl, {
            method: 'POST',
            body: formData
          }).then(function (response) {
            return response.json();
          }).then(function (data) {
            setIsSearching(false);
            if (data.success) {
              var results = data.data.map(function (post) {
                return {
                  label: "".concat(post.title, " (").concat(post.type, ")"),
                  value: post.title,
                  post: post
                };
              });
              response(results);
            } else {
              setSearchError(data.data || __('Search failed', 'frontblocks'));
              response([]);
            }
          }).catch(function (error) {
            setIsSearching(false);
            setSearchError(__('Search failed. Please try again.', 'frontblocks'));
            response([]);
          });
        },
        minLength: 2,
        delay: 300,
        select: function select(event, ui) {
          selectPost(ui.item.post);
          return false;
        },
        open: function open() {
          jQuery(this).autocomplete('widget').css('z-index', 999999);
        }
      });

      // Cleanup function
      return function () {
        try {
          if ($input.hasClass('ui-autocomplete-input')) {
            $input.autocomplete('destroy');
          }
        } catch (e) {
          // If destroy fails, just remove the class
          $input.removeClass('ui-autocomplete-input');
        }
      };
    }
  }, [showSearch, selectedPostType]);
  var selectPost = function selectPost(post) {
    setAttributes({
      selectedPostId: post.id,
      selectedPostTitle: post.title,
      selectedPostType: post.type
    });
    setShowSearch(false);
    setSearchTerm('');
    setSearchError('');
  };
  var clearSelection = function clearSelection() {
    setAttributes({
      selectedPostId: 0,
      selectedPostTitle: '',
      selectedPostContent: ''
    });
    setShowSearch(false);
    setSearchTerm('');
    setSearchError('');
  };
  var blockProps = useBlockProps({
    className: "frbl-insert-post-block ".concat(className)
  });
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("div", blockProps, selectedPostId ? /*#__PURE__*/React.createElement("div", {
    className: "frbl-insert-post-preview"
  }, /*#__PURE__*/React.createElement("h2", {
    className: "frbl-insert-post-title"
  }, selectedPostTitle), /*#__PURE__*/React.createElement("div", {
    className: "frbl-insert-post-content"
  }, selectedPostContent || __('Content will be loaded on the frontend', 'frontblocks')), /*#__PURE__*/React.createElement("div", {
    className: "frbl-insert-post-actions"
  }, /*#__PURE__*/React.createElement(Button, {
    isSecondary: true,
    onClick: function onClick() {
      return setShowSearch(true);
    }
  }, __('Change Post', 'frontblocks')), /*#__PURE__*/React.createElement(Button, {
    isDestructive: true,
    onClick: clearSelection
  }, __('Clear Selection', 'frontblocks')))) : /*#__PURE__*/React.createElement("div", {
    className: "frbl-insert-post-empty"
  }, /*#__PURE__*/React.createElement("p", null, __('No post selected. Use the sidebar to search and select a post.', 'frontblocks')), /*#__PURE__*/React.createElement(Button, {
    isPrimary: true,
    onClick: function onClick() {
      return setShowSearch(true);
    }
  }, __('Select Post', 'frontblocks')))), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
    title: __('Insert Post Settings', 'frontblocks'),
    initialOpen: true
  }, (!selectedPostId || showSearch) && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(SelectControl, {
    label: __('Post Type', 'frontblocks'),
    value: selectedPostType,
    options: postTypes,
    onChange: function onChange(value) {
      return setAttributes({
        selectedPostType: value
      });
    }
  }), /*#__PURE__*/React.createElement(TextControl, {
    ref: searchInputRef,
    label: __('Search Posts', 'frontblocks'),
    value: searchTerm,
    onChange: setSearchTerm,
    placeholder: __('Start typing to search posts...', 'frontblocks'),
    help: __('Type at least 2 characters to search', 'frontblocks')
  }), isSearching && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: '10px'
    }
  }, /*#__PURE__*/React.createElement(Spinner, null), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: '8px'
    }
  }, __('Searching...', 'frontblocks'))), searchError && /*#__PURE__*/React.createElement(Notice, {
    status: "error",
    isDismissible: false
  }, searchError)), selectedPostId && !showSearch && /*#__PURE__*/React.createElement("div", {
    className: "frbl-selected-post-info"
  }, /*#__PURE__*/React.createElement("h4", null, __('Selected Post', 'frontblocks')), /*#__PURE__*/React.createElement(Card, null, /*#__PURE__*/React.createElement(CardBody, null, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Title:', 'frontblocks')), " ", selectedPostTitle), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('Type:', 'frontblocks')), " ", selectedPostType), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, __('ID:', 'frontblocks')), " ", selectedPostId))), /*#__PURE__*/React.createElement(Button, {
    isSecondary: true,
    onClick: function onClick() {
      return setShowSearch(true);
    },
    style: {
      marginTop: '10px'
    }
  }, __('Change Post', 'frontblocks'))))));
}

// Register the custom block
var registerBlockType = wp.blocks.registerBlockType;
registerBlockType('frontblocks/insert-post', {
  title: __('FrontBlocks - Insert Post', 'frontblocks'),
  description: __('Display content from another post or page', 'frontblocks'),
  category: 'generateblocks',
  icon: 'admin-post',
  keywords: [__('post', 'frontblocks'), __('content', 'frontblocks'), __('insert', 'frontblocks'), __('display', 'frontblocks')],
  supports: {
    html: false,
    align: ['wide', 'full']
  },
  attributes: {
    selectedPostId: {
      type: 'number',
      default: 0
    },
    selectedPostType: {
      type: 'string',
      default: 'post'
    },
    selectedPostTitle: {
      type: 'string',
      default: ''
    },
    selectedPostContent: {
      type: 'string',
      default: ''
    },
    className: {
      type: 'string',
      default: ''
    }
  },
  edit: InsertPostBlock,
  save: function save() {
    return null;
  } // Using PHP render callback
});

// Add custom panel to existing GenerateBlocks Grid block
function addInsertPostPanel(BlockEdit) {
  return function (props) {
    if (props.name !== 'generateblocks/grid') {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var _props$attributes$frb = props.attributes.frblInsertPostEnabled,
      frblInsertPostEnabled = _props$attributes$frb === void 0 ? false : _props$attributes$frb;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: __('FrontBlocks - Insert Post', 'frontblocks'),
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: __('Enable Insert Post Grid', 'frontblocks'),
      checked: frblInsertPostEnabled,
      onChange: function onChange(value) {
        return props.setAttributes({
          frblInsertPostEnabled: value
        });
      },
      help: __('Enable insert post functionality for this grid', 'frontblocks')
    }))));
  };
}
addFilter('editor.BlockEdit', 'frontblocks/insert-post-grid-panel', addInsertPostPanel);
