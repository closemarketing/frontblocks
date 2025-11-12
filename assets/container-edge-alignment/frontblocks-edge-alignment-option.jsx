// TEST: Confirmar que el script se carga
console.log('🚀 FrontBlocks Edge Alignment Script LOADED!');

const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, CheckboxControl } = wp.components;
const { __ } = wp.i18n;

/**
 * Add custom attributes to GenerateBlocks Container block.
 *
 * @param {Object} settings Block settings.
 * @param {string} name Block name.
 * @return {Object} Modified settings.
 */
function addEdgeAlignmentAttributes(settings, name) {
	// Support both old (container) and new (element) GenerateBlocks versions
	if (name !== 'generateblocks/container' && name !== 'generateblocks/element') {
		return settings;
	}

	settings.attributes = Object.assign(settings.attributes, {
		frblEdgeAlignmentLeft: {
			type: 'boolean',
			default: false,
		},
		frblEdgeAlignmentRight: {
			type: 'boolean',
			default: false,
		},
	});

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'frontblocks/edge-alignment-attributes',
	addEdgeAlignmentAttributes
);

/**
 * Check if container uses GeneratePress global max-width.
 *
 * @param {Object} attributes Block attributes.
 * @return {boolean} True if uses global max-width.
 */
function usesGlobalMaxWidth(attributes) {
	// Check if using global max width setting.
	// GenerateBlocks uses 'useGlobalMaxWidth' or checks if maxWidth is empty/not set
	// and innerContainer is enabled with global width.
	
	// Option 1: Has useGlobalMaxWidth attribute set to true.
	if (attributes.useGlobalMaxWidth === true) {
		return true;
	}
	
	// Option 2: Has isQueryLoopItem (uses global settings).
	if (attributes.isQueryLoopItem === true) {
		return true;
	}
	
	// Option 3: maxWidth is set to a CSS variable (--content-width, etc).
	if (attributes.maxWidth && typeof attributes.maxWidth === 'string') {
		if (attributes.maxWidth.includes('var(--') || 
		    attributes.maxWidth.includes('content-width') ||
		    attributes.maxWidth === 'var(--gp-site-width)' ||
		    attributes.maxWidth === 'var(--gp-page-width)') {
			return true;
		}
	}
	
	// Option 4: Check maxWidthUnit is 'global' or similar.
	if (attributes.maxWidthUnit === 'global' || attributes.maxWidthUnit === '%') {
		return true;
	}
	
	// Option 5: Inner container with inherit/global width.
	if (attributes.innerContainer === true && !attributes.maxWidth) {
		return true;
	}
	
	return false;
}

/**
 * Add edge alignment controls to GenerateBlocks Container block inspector.
 * ONLY shows if container uses GeneratePress global max-width.
 */
const withEdgeAlignmentControls = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		// Support both old (container) and new (element) GenerateBlocks versions
		if (props.name !== 'generateblocks/container' && props.name !== 'generateblocks/element') {
			return <BlockEdit {...props} />;
		}

		const { attributes, setAttributes } = props;
		const { frblEdgeAlignmentLeft, frblEdgeAlignmentRight } = attributes;
		
		// DEBUG: Ver atributos del contenedor
		console.log('=== FrontBlocks Edge Alignment DEBUG ===');
		console.log('Container attributes:', attributes);
		console.log('Uses global width?', usesGlobalMaxWidth(attributes));
		console.log('maxWidth:', attributes.maxWidth);
		console.log('maxWidthUnit:', attributes.maxWidthUnit);
		console.log('useGlobalMaxWidth:', attributes.useGlobalMaxWidth);
		console.log('innerContainer:', attributes.innerContainer);
		console.log('=======================================');
		
		// TEMPORAL: SIEMPRE MOSTRAR para debug
		// if (!usesGlobalMaxWidth(attributes)) {
		// 	return <BlockEdit {...props} />;
		// }

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody
						title={__('Edge Alignment', 'frontblocks')}
						initialOpen={false}
						className="frbl-edge-alignment-panel"
					>
						<p className="frbl-edge-alignment-description">
							{__(
								'Remove padding from one side to create an edge-to-edge effect. Perfect for asymmetric layouts where content extends to the browser edge on one side.',
								'frontblocks'
							)}
						</p>
						<CheckboxControl
							label={__('Remove Left Padding', 'frontblocks')}
							checked={frblEdgeAlignmentLeft}
							onChange={(value) =>
								setAttributes({ frblEdgeAlignmentLeft: value })
							}
							help={__(
								'Content will align to the left edge of the browser.',
								'frontblocks'
							)}
						/>
						<CheckboxControl
							label={__('Remove Right Padding', 'frontblocks')}
							checked={frblEdgeAlignmentRight}
							onChange={(value) =>
								setAttributes({ frblEdgeAlignmentRight: value })
							}
							help={__(
								'Content will align to the right edge of the browser.',
								'frontblocks'
							)}
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withEdgeAlignmentControls');

addFilter(
	'editor.BlockEdit',
	'frontblocks/edge-alignment-controls',
	withEdgeAlignmentControls
);

/**
 * Add custom classes to block in editor for visual feedback.
 */
const addEdgeAlignmentClass = createHigherOrderComponent((BlockListBlock) => {
	return (props) => {
		// Support both old (container) and new (element) GenerateBlocks versions
		if (props.name !== 'generateblocks/container' && props.name !== 'generateblocks/element') {
			return <BlockListBlock {...props} />;
		}

		const { attributes } = props;
		const { frblEdgeAlignmentLeft, frblEdgeAlignmentRight } = attributes;

		let additionalClasses = '';

		if (frblEdgeAlignmentLeft) {
			additionalClasses += ' frbl-edge-left';
		}

		if (frblEdgeAlignmentRight) {
			additionalClasses += ' frbl-edge-right';
		}

		if (additionalClasses) {
			return (
				<BlockListBlock
					{...props}
					className={props.className + additionalClasses}
				/>
			);
		}

		return <BlockListBlock {...props} />;
	};
}, 'addEdgeAlignmentClass');

addFilter(
	'editor.BlockListBlock',
	'frontblocks/edge-alignment-class',
	addEdgeAlignmentClass
);

