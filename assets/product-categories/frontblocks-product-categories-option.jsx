const { registerBlockType } = wp.blocks;
const { Fragment, useState, useEffect } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const {
   PanelBody,
   RangeControl,
   SelectControl,
   ToggleControl,
   TextControl,
   ColorPicker,
   ColorIndicator,
   Dropdown,
   Button,
   TabPanel,
   Spinner,
} = wp.components;
const { __ } = wp.i18n;
const apiFetch = wp.apiFetch;

function CompactColorPicker( { label, color, onChangeComplete, enableAlpha } ) {
   return (
      <div style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '12px' } }>
         <span style={ { fontSize: '12px' } }>{ label }</span>
         <Dropdown
            renderToggle={ ( { isOpen, onToggle } ) => (
               <Button
                  onClick={ onToggle }
                  aria-expanded={ isOpen }
                  style={ { padding: 0, minWidth: 0, background: 'none', border: 'none', boxShadow: 'none' } }
               >
                  <ColorIndicator colorValue={ color || 'transparent' } />
               </Button>
            ) }
            renderContent={ () => (
               <div style={ { padding: '8px' } }>
                  <ColorPicker
                     color={ color }
                     onChangeComplete={ onChangeComplete }
                     disableAlpha={ ! enableAlpha }
                  />
               </div>
            ) }
         />
      </div>
   );
}

function ProductCategoriesEdit(props) {
   const { attributes, setAttributes } = props;
   const {
      count,
      orderby,
      order,
      hideEmpty,
      showCount,
      columns,
      bgColor,
      borderColor,
      borderWidth,
      borderRadius,
      textColor,
      hoverBgColor,
      hoverBorderColor,
      hoverTextColor,
      showButton,
      buttonText,
      btnBgColor,
      btnTextColor,
      btnBorderColor,
      btnBorderWidth,
      btnBorderRadius,
      btnFontSize,
      btnPaddingV,
      btnPaddingH,
      btnHoverBgColor,
      btnHoverTextColor,
      btnHoverBorderColor,
      className,
   } = attributes;

   const [categories, setCategories] = useState([]);
   const [isLoading, setIsLoading] = useState(true);

   const blockProps = useBlockProps({
      className: `frbl-product-categories-block ${className}`
   });

   const countHelpText = count === 999 ? 
      __('Currently set to show ALL categories.', 'frontblocks') : 
      __('Number of categories shown. Set to 999 to show all.', 'frontblocks');

   const countLabel = count === 999 ? 
      __('Number of Categories (All)', 'frontblocks') : 
      __('Number of Categories', 'frontblocks');

   // Load categories from API.
   useEffect(() => {
      setIsLoading(true);
      
      const queryLimit = count === 999 ? 100 : count;
      const orderParam = order.toLowerCase();
      
      // Build the API path.
      const apiPath = `/wp/v2/product_cat?per_page=${queryLimit}&orderby=${orderby}&order=${orderParam}&hide_empty=${hideEmpty}&_fields=id,name,slug,count,category_image`;
      
      apiFetch({
         path: apiPath,
      })
      .then((data) => {
         setCategories(data);
         setIsLoading(false);
      })
      .catch((error) => {
         console.error('FrontBlocks: Error loading categories:', error);
         setCategories([]);
         setIsLoading(false);
      });
   }, [count, orderby, order, hideEmpty]);

   const styleVars = {
      '--frbl-grid-columns': columns,
      '--frbl-bg-color': bgColor,
      '--frbl-border-color': borderColor,
      '--frbl-border-width': `${borderWidth}px`,
      '--frbl-text-color': textColor,
      '--frbl-hover-bg-color': hoverBgColor,
      '--frbl-hover-border-color': hoverBorderColor,
      '--frbl-hover-text-color': hoverTextColor,
      '--frbl-border-radius': `${borderRadius}px`,
      '--frbl-btn-bg': btnBgColor || 'transparent',
      '--frbl-btn-color': btnTextColor || 'currentColor',
      '--frbl-btn-border-color': btnBorderColor || 'currentColor',
      '--frbl-btn-border-width': `${btnBorderWidth}px`,
      '--frbl-btn-border-radius': `${btnBorderRadius}px`,
      '--frbl-btn-font-size': `${btnFontSize}px`,
      '--frbl-btn-padding-v': `${btnPaddingV}px`,
      '--frbl-btn-padding-h': `${btnPaddingH}px`,
      '--frbl-btn-hover-bg': btnHoverBgColor || 'transparent',
      '--frbl-btn-hover-color': btnHoverTextColor || 'currentColor',
      '--frbl-btn-hover-border-color': btnHoverBorderColor || 'currentColor',
   };

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

               <ToggleControl
                  label={__('Show Product Count', 'frontblocks')}
                  checked={showCount}
                  onChange={(newShowCount) => setAttributes({ showCount: newShowCount })}
                  help={__('Display the number of products in each category.', 'frontblocks')}
               />

               <ToggleControl
                  label={__('Show Button', 'frontblocks')}
                  checked={showButton}
                  onChange={(val) => setAttributes({ showButton: val })}
                  help={__('Display a button at the bottom of each category card.', 'frontblocks')}
               />

               { showButton && (
                  <TextControl
                     label={__('Button Text', 'frontblocks')}
                     value={buttonText}
                     onChange={(val) => setAttributes({ buttonText: val })}
                     placeholder={__('Shop now', 'frontblocks')}
                  />
               ) }
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
                              <CompactColorPicker
                                 label={ __( 'Background Color', 'frontblocks' ) }
                                 color={ bgColor }
                                 enableAlpha={ true }
                                 onChangeComplete={ (value) => setAttributes({ bgColor: value.rgb ? `rgba(${value.rgb.r}, ${value.rgb.g}, ${value.rgb.b}, ${value.rgb.a})` : value.hex }) }
                              />
                              <CompactColorPicker
                                 label={ __( 'Border Color', 'frontblocks' ) }
                                 color={ borderColor }
                                 onChangeComplete={ (value) => setAttributes({ borderColor: value.hex }) }
                              />
                              <CompactColorPicker
                                 label={ __( 'Text Color', 'frontblocks' ) }
                                 color={ textColor }
                                 onChangeComplete={ (value) => setAttributes({ textColor: value.hex }) }
                              />
                           </Fragment>
                        );
                     }
                     if (tab.name === 'hover') {
                        return (
                           <Fragment>
                              <CompactColorPicker
                                 label={ __( 'Hover Background Color', 'frontblocks' ) }
                                 color={ hoverBgColor }
                                 enableAlpha={ true }
                                 onChangeComplete={ (value) => setAttributes({ hoverBgColor: value.rgb ? `rgba(${value.rgb.r}, ${value.rgb.g}, ${value.rgb.b}, ${value.rgb.a})` : value.hex }) }
                              />
                              <CompactColorPicker
                                 label={ __( 'Hover Border Color', 'frontblocks' ) }
                                 color={ hoverBorderColor }
                                 onChangeComplete={ (value) => setAttributes({ hoverBorderColor: value.hex }) }
                              />
                              <CompactColorPicker
                                 label={ __( 'Hover Text Color', 'frontblocks' ) }
                                 color={ hoverTextColor }
                                 onChangeComplete={ (value) => setAttributes({ hoverTextColor: value.hex }) }
                              />
                           </Fragment>
                        );
                     }
                  }}
               </TabPanel>
            </PanelBody>

            { showButton && (
               <PanelBody title={ __( 'Button Style', 'frontblocks' ) } initialOpen={ false }>
                  <RangeControl
                     label={ __( 'Font Size (px)', 'frontblocks' ) }
                     value={ btnFontSize }
                     onChange={ (v) => setAttributes({ btnFontSize: v }) }
                     min={ 10 } max={ 40 }
                  />
                  <RangeControl
                     label={ __( 'Padding Vertical (px)', 'frontblocks' ) }
                     value={ btnPaddingV }
                     onChange={ (v) => setAttributes({ btnPaddingV: v }) }
                     min={ 0 } max={ 60 }
                  />
                  <RangeControl
                     label={ __( 'Padding Horizontal (px)', 'frontblocks' ) }
                     value={ btnPaddingH }
                     onChange={ (v) => setAttributes({ btnPaddingH: v }) }
                     min={ 0 } max={ 100 }
                  />
                  <RangeControl
                     label={ __( 'Border Width (px)', 'frontblocks' ) }
                     value={ btnBorderWidth }
                     onChange={ (v) => setAttributes({ btnBorderWidth: v }) }
                     min={ 0 } max={ 10 }
                  />
                  <RangeControl
                     label={ __( 'Border Radius (px)', 'frontblocks' ) }
                     value={ btnBorderRadius }
                     onChange={ (v) => setAttributes({ btnBorderRadius: v }) }
                     min={ 0 } max={ 50 }
                  />

                  <TabPanel
                     className="frbl-style-tabs"
                     tabs={ [
                        { name: 'normal', title: __( 'Normal', 'frontblocks' ), className: 'tab-normal' },
                        { name: 'hover',  title: __( 'Hover',  'frontblocks' ), className: 'tab-hover'  },
                     ] }
                  >
                     { (tab) => {
                        if ( tab.name === 'normal' ) {
                           return (
                              <Fragment>
                                 <CompactColorPicker
                                    label={ __( 'Background', 'frontblocks' ) }
                                    color={ btnBgColor }
                                    enableAlpha={ true }
                                    onChangeComplete={ (v) => setAttributes({ btnBgColor: v.rgb ? `rgba(${v.rgb.r},${v.rgb.g},${v.rgb.b},${v.rgb.a})` : v.hex }) }
                                 />
                                 <CompactColorPicker
                                    label={ __( 'Text Color', 'frontblocks' ) }
                                    color={ btnTextColor }
                                    onChangeComplete={ (v) => setAttributes({ btnTextColor: v.hex }) }
                                 />
                                 <CompactColorPicker
                                    label={ __( 'Border Color', 'frontblocks' ) }
                                    color={ btnBorderColor }
                                    onChangeComplete={ (v) => setAttributes({ btnBorderColor: v.hex }) }
                                 />
                              </Fragment>
                           );
                        }
                        if ( tab.name === 'hover' ) {
                           return (
                              <Fragment>
                                 <CompactColorPicker
                                    label={ __( 'Background', 'frontblocks' ) }
                                    color={ btnHoverBgColor }
                                    enableAlpha={ true }
                                    onChangeComplete={ (v) => setAttributes({ btnHoverBgColor: v.rgb ? `rgba(${v.rgb.r},${v.rgb.g},${v.rgb.b},${v.rgb.a})` : v.hex }) }
                                 />
                                 <CompactColorPicker
                                    label={ __( 'Text Color', 'frontblocks' ) }
                                    color={ btnHoverTextColor }
                                    onChangeComplete={ (v) => setAttributes({ btnHoverTextColor: v.hex }) }
                                 />
                                 <CompactColorPicker
                                    label={ __( 'Border Color', 'frontblocks' ) }
                                    color={ btnHoverBorderColor }
                                    onChangeComplete={ (v) => setAttributes({ btnHoverBorderColor: v.hex }) }
                                 />
                              </Fragment>
                           );
                        }
                     } }
                  </TabPanel>
               </PanelBody>
            ) }
         </InspectorControls>

         <div {...blockProps}>
            {isLoading ? (
               <div style={{ textAlign: 'center', padding: '40px' }}>
                  <Spinner />
                  <p>{__('Loading categories...', 'frontblocks')}</p>
               </div>
            ) : categories.length === 0 ? (
               <div style={{ padding: '20px', border: '2px dashed #ccc', borderRadius: '8px', textAlign: 'center', color: '#666' }}>
                  <p>{__('No product categories found.', 'frontblocks')}</p>
                  <p style={{ fontSize: '14px' }}>{__('Make sure WooCommerce is active and you have product categories.', 'frontblocks')}</p>
               </div>
            ) : (
               <div className="frbl-product-categories-grid" style={styleVars}>
                  {categories.map((category) => {
                     const imageUrl = category.category_image && category.category_image.src 
                        ? category.category_image.src 
                        : 'https://placehold.co/600x400/eeeeee/333333?text=Product+Category';
                     
                     return (
                        <div key={category.id} className={`frbl-category-item frbl-category-${category.slug}`}>
                           <div className="frbl-category-link">
                              <div className="frbl-category-image-wrap">
                                 <img
                                    src={imageUrl}
                                    alt={category.name}
                                    className="frbl-category-image"
                                 />
                              </div>
                              <h3 className="frbl-category-name">
                                 {category.name}{showCount && ` (${category.count})`}
                              </h3>
                              { showButton && (
                                 <span className="frbl-category-button">
                                    { buttonText || __( 'Shop now', 'frontblocks' ) }
                                 </span>
                              ) }
                           </div>
                        </div>
                     );
                  })}
               </div>
            )}
         </div>
      </Fragment>
   );
}

registerBlockType('frontblocks/product-categories', {
   title: __('Product Categories', 'frontblocks'),
   description: __('Display a list of WooCommerce Product Categories.', 'frontblocks'),
   category: 'woocommerce', 
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
      hideEmpty: { type: 'boolean', default: false },
      showCount: { type: 'boolean', default: true },
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
      showButton:         { type: 'boolean', default: false },
      buttonText:         { type: 'string',  default: '' },
      btnBgColor:         { type: 'string',  default: 'transparent' },
      btnTextColor:       { type: 'string',  default: '' },
      btnBorderColor:     { type: 'string',  default: '' },
      btnBorderWidth:     { type: 'number',  default: 2 },
      btnBorderRadius:    { type: 'number',  default: 4 },
      btnFontSize:        { type: 'number',  default: 14 },
      btnPaddingV:        { type: 'number',  default: 10 },
      btnPaddingH:        { type: 'number',  default: 20 },
      btnHoverBgColor:    { type: 'string',  default: '' },
      btnHoverTextColor:  { type: 'string',  default: '' },
      btnHoverBorderColor:{ type: 'string',  default: '' },
   },
   edit: ProductCategoriesEdit,
   save: () => null,
});
