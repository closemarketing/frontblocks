const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;
const { __ } = wp.i18n;

const GB_BLOCKS    = [ 'generateblocks/container', 'generateblocks/element' ];
const NATIVE_BLOCKS = [ 'core/group', 'core/columns' ];
const ALL_SUPPORTED_BLOCKS = [ ...GB_BLOCKS, ...NATIVE_BLOCKS ];

/**
 * Add custom attributes to supported blocks.
 */
function addEdgeAlignmentAttributes( settings, name ) {
	if ( ! ALL_SUPPORTED_BLOCKS.includes( name ) ) {
		return settings;
	}

	settings.attributes = Object.assign( settings.attributes, {
		frblEdgeAlignment: {
			type: 'string',
			default: '',
		},
	} );

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'frontblocks/edge-alignment-attributes',
	addEdgeAlignmentAttributes
);

/**
 * Check if a GenerateBlocks container uses the global container width.
 * Only containers with var(--gb-container-width) should have edge alignment.
 */
function usesGlobalMaxWidth( attributes ) {
	if ( ! attributes.styles || typeof attributes.styles !== 'object' ) {
		return false;
	}

	if (
		attributes.styles.maxWidth &&
		attributes.styles.maxWidth.includes( 'var(--gb-container-width)' )
	) {
		if (
			attributes.styles.marginLeft === 'auto' &&
			attributes.styles.marginRight === 'auto'
		) {
			return true;
		}
	}

	return false;
}

/**
 * Check if a native block uses a constrained (centered, max-width) layout.
 * Matches core/group and core/columns with constrained layout or inherited layout.
 */
function usesConstrainedLayout( attributes ) {
	// No explicit layout — inherits from theme, which is typically constrained.
	if ( ! attributes.layout ) {
		return true;
	}
	return attributes.layout.type === 'constrained';
}

/**
 * Add edge alignment controls to supported block inspector panels.
 */
const withEdgeAlignmentControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( ! ALL_SUPPORTED_BLOCKS.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const { frblEdgeAlignment } = attributes;
		const isGBBlock = GB_BLOCKS.includes( props.name );

		// Guard: only show panel when the block uses a centered/constrained layout.
		const shouldShowPanel = isGBBlock
			? usesGlobalMaxWidth( attributes )
			: usesConstrainedLayout( attributes );

		if ( ! shouldShowPanel ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'FrontBlocks Edge Alignment', 'frontblocks' ) }
						initialOpen={ false }
						className="frbl-edge-alignment-panel"
					>
						<p className="frbl-edge-alignment-description">
							{ __(
								'Remove padding from one side to create an edge-to-edge effect. Perfect for asymmetric layouts where content extends to the browser edge on one side.',
								'frontblocks'
							) }
						</p>
						<SelectControl
							label={ __( 'Align to Edge', 'frontblocks' ) }
							value={ frblEdgeAlignment }
							options={ [
								{
									label: __( 'None', 'frontblocks' ),
									value: '',
								},
								{
									label: __( 'Remove Left Padding', 'frontblocks' ),
									value: 'left',
								},
								{
									label: __( 'Remove Right Padding', 'frontblocks' ),
									value: 'right',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { frblEdgeAlignment: value } )
							}
							help={ __(
								'Choose which side should extend to the browser edge.',
								'frontblocks'
							) }
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withEdgeAlignmentControls' );

addFilter(
	'editor.BlockEdit',
	'frontblocks/edge-alignment-controls',
	withEdgeAlignmentControls
);

/**
 * Add visual feedback classes in the editor.
 */
const addEdgeAlignmentClass = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		if ( ! ALL_SUPPORTED_BLOCKS.includes( props.name ) ) {
			return <BlockListBlock { ...props } />;
		}

		const { attributes } = props;
		const { frblEdgeAlignment } = attributes;

		let additionalClasses = '';

		if ( frblEdgeAlignment === 'left' ) {
			additionalClasses = ' frbl-edge-left';
		} else if ( frblEdgeAlignment === 'right' ) {
			additionalClasses = ' frbl-edge-right';
		}

		if ( additionalClasses ) {
			return (
				<BlockListBlock
					{ ...props }
					className={ props.className + additionalClasses }
				/>
			);
		}

		return <BlockListBlock { ...props } />;
	};
}, 'addEdgeAlignmentClass' );

addFilter(
	'editor.BlockListBlock',
	'frontblocks/edge-alignment-class',
	addEdgeAlignmentClass
);
