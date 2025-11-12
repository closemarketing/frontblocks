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
 * Check if container uses GenerateBlocks global container width.
 * Only containers with var(--gb-container-width) should have edge alignment.
 *
 * @param {Object} attributes Block attributes.
 * @return {boolean} True if uses global container width.
 */
function usesGlobalMaxWidth(attributes) {
	// GenerateBlocks stores styles in an object (not array).
	// Check if maxWidth uses var(--gb-container-width) and margins are auto.
	if (!attributes.styles || typeof attributes.styles !== 'object') {
		return false;
	}
	
	// Check if maxWidth contains the global container width variable.
	if (attributes.styles.maxWidth && 
	    attributes.styles.maxWidth.includes('var(--gb-container-width)')) {
		// Also verify it's centered (marginLeft and marginRight are auto).
		if (attributes.styles.marginLeft === 'auto' && 
		    attributes.styles.marginRight === 'auto') {
			return true;
		}
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
		
		// Only show panel if container uses centered layout (margin auto).
		if (!usesGlobalMaxWidth(attributes)) {
			return <BlockEdit {...props} />;
		}

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

