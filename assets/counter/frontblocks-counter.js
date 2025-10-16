"use strict";

var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
var Fragment = wp.element.Fragment;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  ToggleControl = _wp$components.ToggleControl;
var addFilter = wp.hooks.addFilter;
var BLOCK_NAME = 'generateblocks/headline'; // GenerarteBlocks Headline Block

/**
 * 1. Añade el nuevo atributo al bloque GenerateBlocks Headline.
 */
addFilter('blocks.registerBlockType', 'frontblocks/add-counter-attribute', function (settings, name) {
  if (name === BLOCK_NAME) {
    settings.attributes = Object.assign(settings.attributes, {
      isCounterActive: {
        type: 'boolean',
        default: false
      }
    });
  }
  return settings;
});

/**
 * 2. Componente de Orden Superior (HOC) para añadir el control al editor.
 */
var withHeadlineCounterControl = createHigherOrderComponent(function (BlockEdit) {
  return function (props) {
    // Solo aplica a nuestro bloque objetivo
    if (props.name !== BLOCK_NAME) {
      return /*#__PURE__*/React.createElement(BlockEdit, props);
    }
    var attributes = props.attributes,
      setAttributes = props.setAttributes;
    var isCounterActive = attributes.isCounterActive;
    return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(BlockEdit, props), /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
      title: "Frontblocks - Efecto Contador",
      initialOpen: false
    }, /*#__PURE__*/React.createElement(ToggleControl, {
      label: "Activar Efecto Contador",
      help: isCounterActive ? 'El número en el titular se animará al hacer scroll.' : 'Activar para habilitar la animación de conteo.',
      checked: isCounterActive,
      onChange: function onChange(val) {
        return setAttributes({
          isCounterActive: val
        });
      }
    }), /*#__PURE__*/React.createElement("p", {
      style: {
        marginTop: '10px',
        fontSize: '12px',
        color: '#777'
      }
    }, /*#__PURE__*/React.createElement("small", null, "Aseg\xFArate de que el texto comience con un n\xFAmero (ej., '123', '+500', o '\u20AC100').")))));
  };
}, 'withHeadlineCounterControl');
addFilter('editor.BlockEdit', 'frontblocks/headline-counter-control', withHeadlineCounterControl);
