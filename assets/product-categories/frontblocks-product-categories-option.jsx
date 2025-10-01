const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { 
    InspectorControls, 
    useBlockProps,
} = wp.blockEditor;
const { 
   PanelBody, 
   RangeControl, 
   SelectControl, 
   ToggleControl,
   ColorPicker, 
   TabPanel 
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
      textColor,
      hoverBgColor,
      hoverBorderColor,
      hoverTextColor,
      className 
   } = attributes;

   const blockProps = useBlockProps({
      className: `frbl-product-categories-block ${className}`
   });
   
   const wrapperStyle = {
      padding: '30px', 
      border: `${borderWidth}px solid ${borderColor}`,
      backgroundColor: bgColor,
      textAlign: 'center',
      color: textColor,
      borderRadius: '20px',
      lineHeight: '1.5em'
   };
   
   return (
      <Fragment>
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
                  max={999}
                  help={__('Set the slider to 999 to show all available categories.', 'frontblocks')}
                  renderTooltipContent={(value) => (value === 999 ? __('All', 'frontblocks') : value)}
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
                              <div style={{ maxWidth: '250px', marginTop: '15px' }}>
                                 <h4>{__('Background Color', 'frontblocks')}</h4>
                                 <ColorPicker
                                    color={bgColor}
                                    onChangeComplete={(value) => setAttributes({ bgColor: value.rgb ? `rgba(${value.rgb.r}, ${value.rgb.g}, ${value.rgb.b}, ${value.rgb.a})` : value.hex })}
                                    disableAlpha={false}
                                 />
                              </div>

                              <div style={{ maxWidth: '250px', marginTop: '15px' }}>
                                 <h4>{__('Border Color', 'frontblocks')}</h4>
                                 <ColorPicker
                                    color={borderColor}
                                    onChangeComplete={(value) => setAttributes({ borderColor: value.hex })}
                                 />
                              </div>

                              <div style={{ maxWidth: '250px', marginTop: '15px' }}>
                                 <h4>{__('Text Color', 'frontblocks')}</h4>
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
                              <div style={{ maxWidth: '250px', marginTop: '15px' }}>
                                 <h4>{__('Hover Background Color', 'frontblocks')}</h4>
                                 <ColorPicker
                                    color={hoverBgColor}
                                    onChangeComplete={(value) => setAttributes({ hoverBgColor: value.rgb ? `rgba(${value.rgb.r}, ${value.rgb.g}, ${value.rgb.b}, ${value.rgb.a})` : value.hex })}
                                    disableAlpha={false}
                                 />
                              </div>

                              <div style={{ maxWidth: '250px', marginTop: '15px' }}>
                                 <h4>{__('Hover Border Color', 'frontblocks')}</h4>
                                 <ColorPicker
                                    color={hoverBorderColor}
                                    onChangeComplete={(value) => setAttributes({ hoverBorderColor: value.hex })}
                                 />
                              </div>

                              <div style={{ maxWidth: '250px', marginTop: '15px' }}>
                                 <h4>{__('Hover Text Color', 'frontblocks')}</h4>
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
      textColor: { type: 'string', default: 'inherit' },
      hoverBgColor: { type: 'string', default: 'rgba(255, 255, 255, 0.7)' },
      hoverBorderColor: { type: 'string', default: '#555555' },
      hoverTextColor: { type: 'string', default: 'inherit' },
   },
   edit: ProductCategoriesEdit,
   save: () => null,
});