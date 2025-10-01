// frontblocks-product-categories-option.jsx
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { 
   PanelBody, 
   RangeControl, 
   SelectControl, 
   ToggleControl 
} = wp.components;
const { __ } = wp.i18n;

// Componente de Edición del Bloque
function ProductCategoriesEdit(props) {
   const { attributes, setAttributes } = props;
   const { 
      count, 
      orderby, 
      order, 
      hideEmpty, 
      columns, // ¡ATRIBUTO RENOMBRADO!
      className 
   } = attributes;

   const blockProps = useBlockProps({
      className: `frbl-product-categories-block ${className}`
   });
   
   // Esto se muestra en el editor de Gutenberg
   return (
      <Fragment>
         {/* 1. Panel Lateral de Configuración (InspectorControls) */}
         <InspectorControls>
            <PanelBody
               title={__('Product Categories Settings', 'frontblocks')}
               initialOpen={true}
            >
               <RangeControl
                  label={__('Number of Categories', 'frontblocks')}
                  value={count}
                  onChange={(newCount) => setAttributes({ count: newCount })}
                  min={1}
                  max={20}
               />
               
               {/* ¡CONTROL DE COLUMNAS! */}
               <RangeControl
                  label={__('Number of Columns', 'frontblocks')}
                  value={columns}
                  onChange={(newColumns) => setAttributes({ columns: newColumns })}
                  min={1}
                  max={6} 
                  help={__('Number of categories shown per row.', 'frontblocks')}
               />

               <SelectControl
                  label={__('Order By', 'frontblocks')}
                  value={orderby}
                  options={[
                     { label: __('Count', 'frontblocks'), value: 'count' },
                     { label: __('Name', 'frontblocks'), value: 'name' },
                     { label: __('ID', 'frontblocks'), value: 'id' },
                     { label: __('Slug', 'frontblocks'), value: 'slug' },
                  ]}
                  onChange={(newOrderby) => setAttributes({ orderby: newOrderby })}
               />

               <SelectControl
                  label={__('Order', 'frontblocks')}
                  value={order}
                  options={[
                     { label: __('Descending (DESC)', 'frontblocks'), value: 'DESC' },
                     { label: __('Ascending (ASC)', 'frontblocks'), value: 'ASC' },
                  ]}
                  onChange={(newOrder) => setAttributes({ order: newOrder })}
               />

               <ToggleControl
                  label={__('Hide Empty Categories', 'frontblocks')}
                  checked={hideEmpty}
                  onChange={(newHideEmpty) => setAttributes({ hideEmpty: newHideEmpty })}
               />
            </PanelBody>
         </InspectorControls>

         {/* 2. Vista en el Editor (Muestra un placeholder) */}
         <div {...blockProps}>
            <div style={{ padding: '15px', border: '1px solid #ccc', textAlign: 'center' }}>
               <p style={{ fontWeight: 'bold' }}>{__('Product Categories Grid', 'frontblocks')}</p>
               <p>{__('Showing', 'frontblocks')} {count} {__('categories in', 'frontblocks')} {columns} {__('columns.', 'frontblocks')}</p>
               <p>{__('The grid will be dynamically rendered on the frontend with images and names.', 'frontblocks')}</p>
            </div>
         </div>
      </Fragment>
   );
}

// Registro del Bloque
registerBlockType('frontblocks/product-categories', {
   title: __('Product Categories', 'frontblocks'),
   description: __('Display a list of WooCommerce Product Categories.', 'frontblocks'),
   category: 'generateblocks', 
   icon: 'store', 
   keywords: [
      __('woo', 'frontblocks'),
      __('categories', 'frontblocks'),
      __('products', 'frontblocks'),
      __('grid', 'frontblocks')
   ],
   attributes: {
      count: { type: 'number', default: 5 },
      orderby: { type: 'string', default: 'count' },
      order: { type: 'string', default: 'DESC' },
      hideEmpty: { type: 'boolean', default: true },
      className: { type: 'string', default: '' },
      // ¡ATRIBUTO RENOMBRADO!
      columns: {
         type: 'number',
         default: 2
      }
   },
   edit: ProductCategoriesEdit,
   save: () => null // Usamos render_callback en PHP
});