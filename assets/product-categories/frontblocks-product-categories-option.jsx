const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { 
   PanelBody, 
   RangeControl, 
   SelectControl, 
   ToggleControl,
   ColorPicker, 
   TabPanel,
} = wp.components;
const { __ } = wp.i18n;

function ProductCategoriesEdit(props) {
   const { attributes, setAttributes } = props;
   const { 
      count, 
      orderby, 
      order, 
      hideEmpty, 
      columns,
      bgColor,
      borderColor,
      borderWidth,
      borderRadius,
      textColor,
      hoverBgColor,
      hoverBorderColor,
      hoverTextColor,
      className,
   } = attributes;

   const blockProps = useBlockProps({
      className: `frbl-product-categories-block ${className}`
   });

   const wrapperStyle = {
      padding: '30px', 
      border: `${borderWidth}px solid ${borderColor}`,
      borderRadius: `${borderRadius}px`,
      backgroundColor: bgColor,
      textAlign: 'center',
      color: textColor,
      lineHeight: '1.5em'
   };

   const countHelpText = count === 999 ? 
      __('Currently set to show ALL categories.', 'frontblocks') : 
      __('Number of categories shown. Set to 999 to show all.', 'frontblocks');

   const countLabel = count === 999 ? 
      __('Number of Categories (All)', 'frontblocks') : 
      __('Number of Categories', 'frontblocks');

   const colorPickerCompactStyle = { maxWidth: '250px' };

   return (
      <Fragment>
         <InspectorControls>
            <PanelBody
               title={__('Product Categories Settings', 'frontblocks')}
               initialOpen={true}
            >
               <RangeControl
                  label={countLabel}
                  value={count}
                  onChange={(newCount) => setAttributes({ count: newCount })}
                  min={1}
                  max={999}
                  help={countHelpText}
               />
               
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

            <PanelBody
               title={__('Card Style Settings', 'frontblocks')}
               initialOpen={false}
            >
               <RangeControl
                  label={__('Border Width (px)', 'frontblocks')}
                  value={borderWidth}
                  onChange={(value) => setAttributes({ borderWidth: value })}
                  min={0}
                  max={10}
               />

               <RangeControl
                  label={__('Border Radius (px)', 'frontblocks')} // 🚨 NUEVO CONTROL
                  value={borderRadius}
                  onChange={(value) => setAttributes({ borderRadius: value })}
                  min={0}
                  max={50}
               />
               
               <TabPanel 
                  className="frbl-style-tabs"
                  tabs={[
                     { name: 'normal', title: __('Normal', 'frontblocks'), className: 'tab-normal' },
                     { name: 'hover', title: __('Hover', 'frontblocks'), className: 'tab-hover' },
                  ]}
               >
                  {(tab) => {
                     if (tab.name === 'normal') {
                        return (
                           <Fragment>
                              <h4 style={{marginTop: '15px'}}>{__('Background Color', 'frontblocks')}</h4>
                              <div style={colorPickerCompactStyle}>
                                 <ColorPicker
                                    color={bgColor}
                                    onChangeComplete={(value) => setAttributes({ bgColor: value.rgb ? `rgba(${value.rgb.r}, ${value.rgb.g}, ${value.rgb.b}, ${value.rgb.a})` : value.hex })}
                                    disableAlpha={false}
                                 />
                              </div>

                              <h4 style={{marginTop: '15px'}}>{__('Border Color', 'frontblocks')}</h4>
                              <div style={colorPickerCompactStyle}>
                                 <ColorPicker
                                    color={borderColor}
                                    onChangeComplete={(value) => setAttributes({ borderColor: value.hex })}
                                 />
                              </div>

                              <h4 style={{marginTop: '15px'}}>{__('Text Color', 'frontblocks')}</h4>
                              <div style={colorPickerCompactStyle}>
                                 <ColorPicker
                                    color={textColor}
                                    onChangeComplete={(value) => setAttributes({ textColor: value.hex })}
                                 />
                              </div>
                           </Fragment>
                        );
                     }
                     if (tab.name === 'hover') {
                        return (
                           <Fragment>
                              <h4 style={{marginTop: '15px'}}>{__('Hover Background Color', 'frontblocks')}</h4>
                              <div style={colorPickerCompactStyle}>
                                 <ColorPicker
                                    color={hoverBgColor}
                                    onChangeComplete={(value) => setAttributes({ hoverBgColor: value.rgb ? `rgba(${value.rgb.r}, ${value.rgb.g}, ${value.rgb.b}, ${value.rgb.a})` : value.hex })}
                                    disableAlpha={false}
                                 />
                              </div>

                              <h4 style={{marginTop: '15px'}}>{__('Hover Border Color', 'frontblocks')}</h4>
                              <div style={colorPickerCompactStyle}>
                                 <ColorPicker
                                    color={hoverBorderColor}
                                    onChangeComplete={(value) => setAttributes({ hoverBorderColor: value.hex })}
                                 />
                              </div>

                              <h4 style={{marginTop: '15px'}}>{__('Hover Text Color', 'frontblocks')}</h4>
                              <div style={colorPickerCompactStyle}>
                                 <ColorPicker
                                    color={hoverTextColor}
                                    onChangeComplete={(value) => setAttributes({ hoverTextColor: value.hex })}
                                 />
                              </div>
                           </Fragment>
                        );
                     }
                  }}
               </TabPanel>
            </PanelBody>
         </InspectorControls>

         <div {...blockProps}>
            <div style={wrapperStyle}>
               <p style={{ fontWeight: 'bold' }}>{__('Product Categories Grid', 'frontblocks')}</p>
               <p>{__('Showing', 'frontblocks')} {count === 999 ? __('All', 'frontblocks') : count} {__('categories in', 'frontblocks')} {columns} {__('columns.', 'frontblocks')}</p>
               <p style={{ color: textColor }}>{__('The background, border, and text colors are previewed here.', 'frontblocks')}</p>
            </div>
         </div>
      </Fragment>
   );
}

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
      columns: { type: 'number', default: 2 },
      bgColor: { type: 'string', default: 'rgba(255, 255, 255, 0.5)' },
      borderColor: { type: 'string', default: '#dddddd' },
      borderWidth: { type: 'number', default: 1 },
      borderRadius: { type: 'number', default: 20 },
      textColor: { type: 'string', default: 'inherit' },
      hoverBgColor: { type: 'string', default: 'rgba(255, 255, 255, 0.7)' },
      hoverBorderColor: { type: 'string', default: '#555555' },
      hoverTextColor: { type: 'string', default: 'inherit' },
   },
   edit: ProductCategoriesEdit,
   save: () => null,
});
