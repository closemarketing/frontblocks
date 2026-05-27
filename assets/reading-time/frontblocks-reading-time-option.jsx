const { registerBlockType } = wp.blocks;
const { Fragment, useState, useEffect } = wp.element;
const { InspectorControls, useBlockProps, ColorPalette } = wp.blockEditor;
const { PanelBody, ToggleControl, TextControl, RangeControl, SelectControl } = wp.components;
const { __ } = wp.i18n;
const { useSelect } = wp.data;

/**
 * Calculate reading time for content.
 *
 * @param {string} content - Post content.
 * @return {number} Reading time in minutes.
 */
function calculateReadingTime(content) {
	// Ensure content is a string.
	if (!content || typeof content !== 'string') {
		return 5;
	}

	// Strip HTML tags.
	const textContent = content.replace(/<[^>]*>/g, '');
	
	// Count words.
	const wordCount = textContent.split(/\s+/).filter(word => word.length > 0).length;
	
	// Average reading speed: 225 words per minute.
	const readingTime = Math.ceil(wordCount / 225);
	
	// Minimum 1 minute.
	return Math.max(1, readingTime);
}

/**
 * Edit component for Reading Time block.
 *
 * @param {Object} props - Block properties.
 * @return {JSX.Element} Block edit component.
 */
function ReadingTimeEdit(props) {
	const { attributes, setAttributes } = props;
	const {
		postId,
		showIcon,
		prefix,
		suffix,
		textColor,
		backgroundColor,
		fontSize,
		iconColor,
		alignment,
		padding,
		borderRadius,
	} = attributes;

	const [readingTime, setReadingTime] = useState(5);

	const blockProps = useBlockProps();

	// Get current post content and calculate reading time.
	useEffect(() => {
		try {
			// Try to get the post content from the editor.
			const postContent = wp.data.select('core/editor')?.getEditedPostContent?.();
			
			if (postContent && typeof postContent === 'string') {
				const calculatedTime = calculateReadingTime(postContent);
				setReadingTime(calculatedTime);
			} else {
				setReadingTime(5);
			}
		} catch (error) {
			// If there's any error, just use default value.
			setReadingTime(5);
		}
	}, [postId]);

	const styleVars = {
		'--frbl-text-color': textColor,
		'--frbl-bg-color': backgroundColor,
		'--frbl-font-size': `${fontSize}px`,
		'--frbl-icon-color': iconColor,
		'--frbl-padding': `${padding}px`,
		'--frbl-border-radius': `${borderRadius}px`,
	};

	const iconSvg = (
		<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
			<circle cx="12" cy="12" r="10"></circle>
			<polyline points="12 6 12 12 16 14"></polyline>
		</svg>
	);

	const wrapperClass = `frbl-reading-time-wrapper align-${alignment}`;

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={__('General Settings', 'frontblocks')} initialOpen={true}>
					<ToggleControl
						label={__('Show Icon', 'frontblocks')}
						checked={showIcon}
						onChange={(value) => setAttributes({ showIcon: value })}
					/>
					
					<TextControl
						label={__('Prefix', 'frontblocks')}
						value={prefix}
						onChange={(value) => setAttributes({ prefix: value })}
						help={__('Text to display before the reading time number. Example: "This blog takes"', 'frontblocks')}
						placeholder={__('Example: This blog takes', 'frontblocks')}
					/>
					
					<TextControl
						label={__('Suffix', 'frontblocks')}
						value={suffix}
						onChange={(value) => setAttributes({ suffix: value })}
						help={__('Text to display after the reading time number. Example: "minutes to read"', 'frontblocks')}
						placeholder={__('Example: minutes to read', 'frontblocks')}
					/>

					<SelectControl
						label={__('Alignment', 'frontblocks')}
						value={alignment}
						options={[
							{ label: __('Left', 'frontblocks'), value: 'left' },
							{ label: __('Center', 'frontblocks'), value: 'center' },
							{ label: __('Right', 'frontblocks'), value: 'right' },
						]}
						onChange={(value) => setAttributes({ alignment: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Style Settings', 'frontblocks')} initialOpen={false}>
					<RangeControl
						label={__('Font Size', 'frontblocks')}
						value={fontSize}
						onChange={(value) => setAttributes({ fontSize: value })}
						min={10}
						max={48}
					/>

					<p style={{ marginBottom: '8px', fontWeight: '500' }}>
						{__('Text Color', 'frontblocks')}
					</p>
					<ColorPalette
						value={textColor}
						onChange={(value) => setAttributes({ textColor: value || 'inherit' })}
					/>

					<p style={{ marginBottom: '8px', fontWeight: '500' }}>
						{__('Background Color', 'frontblocks')}
					</p>
					<ColorPalette
						value={backgroundColor}
						onChange={(value) => setAttributes({ backgroundColor: value || 'transparent' })}
					/>

					<p style={{ marginBottom: '8px', fontWeight: '500' }}>
						{__('Icon Color', 'frontblocks')}
					</p>
					<ColorPalette
						value={iconColor}
						onChange={(value) => setAttributes({ iconColor: value || 'currentColor' })}
					/>

					<RangeControl
						label={__('Padding', 'frontblocks')}
						value={padding}
						onChange={(value) => setAttributes({ padding: value })}
						min={0}
						max={50}
					/>

					<RangeControl
						label={__('Border Radius', 'frontblocks')}
						value={borderRadius}
						onChange={(value) => setAttributes({ borderRadius: value })}
						min={0}
						max={50}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className={wrapperClass}>
					<div className="frbl-reading-time" style={styleVars}>
						{showIcon && (
							<span className="frbl-reading-time-icon">{iconSvg}</span>
						)}
						<span className="frbl-reading-time-text">
							{prefix && `${prefix} `}
							{readingTime}
							{suffix && ` ${suffix}`}
						</span>
					</div>
				</div>
			</div>
		</Fragment>
	);
}

// Register the block.
registerBlockType('frontblocks/reading-time', {
	title: __('FrontBlocks - Reading Time', 'frontblocks'),
	description: __('Display estimated reading time for the current post or page.', 'frontblocks'),
	category: 'common',
	icon: 'clock',
	keywords: [
		__('reading', 'frontblocks'),
		__('time', 'frontblocks'),
		__('duration', 'frontblocks'),
		__('blog', 'frontblocks'),
	],
	attributes: {
		postId: {
			type: 'number',
			default: 0,
		},
		showIcon: {
			type: 'boolean',
			default: true,
		},
		prefix: {
			type: 'string',
			default: '',
		},
		suffix: {
			type: 'string',
			default: 'min',
		},
		className: {
			type: 'string',
			default: '',
		},
		textColor: {
			type: 'string',
			default: 'inherit',
		},
		backgroundColor: {
			type: 'string',
			default: 'transparent',
		},
		fontSize: {
			type: 'number',
			default: 16,
		},
		iconColor: {
			type: 'string',
			default: 'currentColor',
		},
		alignment: {
			type: 'string',
			default: 'left',
		},
		padding: {
			type: 'number',
			default: 10,
		},
		borderRadius: {
			type: 'number',
			default: 5,
		},
	},
	edit: ReadingTimeEdit,
	save: function () {
		return null; // Dynamic block, render on server side.
	},
});

