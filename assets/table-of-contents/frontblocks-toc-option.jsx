const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps, PanelColorSettings } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, ToggleControl, RangeControl, Notice } = wp.components;
const { useSelect } = wp.data;
const { __ } = wp.i18n;

/**
 * Recursively collect every core/heading block in the current post, in
 * document order, so the editor can show a best-effort live preview.
 *
 * This only sees core Heading blocks: GenerateBlocks and other third-party
 * heading output isn't a distinct, reliably identifiable block type in the
 * editor's block tree, so it can't be previewed here. The actual published
 * page is unaffected by this limitation — the frontend discovers every
 * <h1>-<h6> tag in the final rendered HTML regardless of its source block.
 *
 * @param {Array} blocks Blocks to scan (recurses into innerBlocks).
 * @return {Array} { level, text }
 */
function collectHeadings( blocks ) {
	const headings = [];

	blocks.forEach( ( block ) => {
		if ( 'core/heading' === block.name ) {
			const text = ( block.attributes.content || '' ).replace( /<[^>]*>/g, '' ).trim();
			if ( text ) {
				headings.push( { level: block.attributes.level || 2, text } );
			}
		}

		if ( block.innerBlocks && block.innerBlocks.length ) {
			headings.push( ...collectHeadings( block.innerBlocks ) );
		}
	} );

	return headings;
}

/**
 * Edit component for the Table of Contents block.
 */
function TableOfContentsEdit( props ) {
	const { attributes, setAttributes } = props;
	const {
		title,
		listStyle,
		accentColor,
		collapsible,
		collapsedByDefault,
		sticky,
		minLevel,
		maxLevel,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'frbl-toc-editor-preview' + ( sticky ? ' frbl-toc-editor-preview--sticky' : '' ),
	} );

	const headings = useSelect(
		( select ) => collectHeadings( select( 'core/block-editor' ).getBlocks() ),
		[]
	).filter( ( heading ) => heading.level >= minLevel && heading.level <= maxLevel );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Table of Contents', 'frontblocks' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Title', 'frontblocks' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
					/>
					<SelectControl
						label={ __( 'List style', 'frontblocks' ) }
						value={ listStyle }
						options={ [
							{ label: __( 'Bulleted', 'frontblocks' ), value: 'unordered' },
							{ label: __( 'Numbered', 'frontblocks' ), value: 'ordered' },
							{ label: __( 'Plain (no bullets)', 'frontblocks' ), value: 'plain' },
						] }
						onChange={ ( value ) => setAttributes( { listStyle: value } ) }
					/>
					<RangeControl
						label={ __( 'From heading level', 'frontblocks' ) }
						value={ minLevel }
						min={ 2 }
						max={ 6 }
						onChange={ ( value ) => setAttributes( { minLevel: value } ) }
					/>
					<RangeControl
						label={ __( 'To heading level', 'frontblocks' ) }
						value={ maxLevel }
						min={ 2 }
						max={ 6 }
						onChange={ ( value ) => setAttributes( { maxLevel: value } ) }
					/>
					<ToggleControl
						label={ __( 'Collapsible', 'frontblocks' ) }
						checked={ collapsible }
						onChange={ ( value ) => setAttributes( { collapsible: value } ) }
						help={ __( 'Renders as a native, keyboard-accessible expand/collapse control.', 'frontblocks' ) }
					/>
					{ collapsible && (
						<ToggleControl
							label={ __( 'Collapsed by default', 'frontblocks' ) }
							checked={ collapsedByDefault }
							onChange={ ( value ) => setAttributes( { collapsedByDefault: value } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Sticky', 'frontblocks' ) }
						checked={ sticky }
						onChange={ ( value ) => setAttributes( { sticky: value } ) }
						help={ __( 'Keeps the Table of Contents in view while visitors scroll.', 'frontblocks' ) }
					/>
				</PanelBody>
				<PanelColorSettings
					title={ __( 'Color', 'frontblocks' ) }
					colorSettings={ [
						{
							value: accentColor,
							onChange: ( color ) => setAttributes( { accentColor: color || '' } ),
							label: __( 'Accent color', 'frontblocks' ),
						},
					] }
				/>
			</InspectorControls>
			<nav { ...blockProps } style={ accentColor ? { '--frbl-toc-accent': accentColor } : undefined }>
				<p className="frbl-toc__title">{ title }</p>
				{ headings.length > 0 ? (
					<Notice status="info" isDismissible={ false }>
						{ __( 'Preview shows core Heading blocks only. GenerateBlocks headings and final anchors are generated when the page is published.', 'frontblocks' ) }
					</Notice>
				) : (
					<Notice status="info" isDismissible={ false }>
						{ __( 'No headings found yet in this range. Add some Heading blocks to this post.', 'frontblocks' ) }
					</Notice>
				) }
				{ headings.length > 0 && (
					<ul className={ 'frbl-toc__list frbl-toc__list--' + listStyle }>
						{ headings.map( ( heading, index ) => (
							<li key={ index } className={ 'frbl-toc__item frbl-toc__item--level-' + heading.level }>
								{ heading.text }
							</li>
						) ) }
					</ul>
				) }
			</nav>
		</>
	);
}

registerBlockType( 'frontblocks/table-of-contents', {
	title: __( 'Table of Contents', 'frontblocks' ),
	description: __( 'An accessible table of contents generated from the headings in this post.', 'frontblocks' ),
	category: 'widgets',
	icon: 'list-view',
	supports: {
		html: false,
	},
	attributes: {
		title: {
			type: 'string',
			default: __( 'Table of Contents', 'frontblocks' ),
		},
		listStyle: {
			type: 'string',
			default: 'unordered',
		},
		accentColor: {
			type: 'string',
			default: '',
		},
		collapsible: {
			type: 'boolean',
			default: false,
		},
		collapsedByDefault: {
			type: 'boolean',
			default: false,
		},
		sticky: {
			type: 'boolean',
			default: false,
		},
		minLevel: {
			type: 'number',
			default: 2,
		},
		maxLevel: {
			type: 'number',
			default: 4,
		},
	},
	edit: TableOfContentsEdit,
	save: () => null,
} );
