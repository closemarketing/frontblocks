/**
 * Reading Time Block - Editor Options (Compiled JS)
 *
 * @package FrontBlocks
 */

(function () {
	'use strict';

	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;
	var TextControl = wp.components.TextControl;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var ColorPalette = wp.blockEditor.ColorPalette;
	var useSelect = wp.data.useSelect;
	var createElement = wp.element.createElement;

	/**
	 * Calculate reading time for content.
	 *
	 * @param {string} content - Post content.
	 * @return {number} Reading time in minutes.
	 */
	function calculateReadingTime(content) {
		if (!content) {
			return 1;
		}

		// Strip HTML tags.
		var textContent = content.replace(/<[^>]*>/g, '');
		
		// Count words.
		var wordCount = textContent.split(/\s+/).filter(function (word) {
			return word.length > 0;
		}).length;
		
		// Average reading speed: 225 words per minute.
		var readingTime = Math.ceil(wordCount / 225);
		
		// Minimum 1 minute.
		return Math.max(1, readingTime);
	}

	/**
	 * Edit component for Reading Time block.
	 *
	 * @param {Object} props - Block properties.
	 * @return {Object} Block edit component.
	 */
	function ReadingTimeEdit(props) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var postId = attributes.postId;
		var showIcon = attributes.showIcon;
		var prefix = attributes.prefix;
		var suffix = attributes.suffix;
		var textColor = attributes.textColor;
		var backgroundColor = attributes.backgroundColor;
		var fontSize = attributes.fontSize;
		var iconColor = attributes.iconColor;
		var alignment = attributes.alignment;
		var padding = attributes.padding;
		var borderRadius = attributes.borderRadius;

		var blockProps = useBlockProps();

		// Get current post content.
		var postContent = useSelect(function (select) {
			var currentPostId = postId || select('core/editor').getCurrentPostId();
			var post = select('core').getEditedEntityRecord('postType', 'post', currentPostId);
			return post ? post.content : '';
		}, [postId]);

		var readingTime = calculateReadingTime(postContent);

		var styleVars = {
			'--frbl-text-color': textColor,
			'--frbl-bg-color': backgroundColor,
			'--frbl-font-size': fontSize + 'px',
			'--frbl-icon-color': iconColor,
			'--frbl-alignment': alignment,
			'--frbl-padding': padding + 'px',
			'--frbl-border-radius': borderRadius + 'px'
		};

		var iconSvg = createElement(
			'svg',
			{
				xmlns: 'http://www.w3.org/2000/svg',
				width: '1em',
				height: '1em',
				viewBox: '0 0 24 24',
				fill: 'none',
				stroke: 'currentColor',
				strokeWidth: '2',
				strokeLinecap: 'round',
				strokeLinejoin: 'round'
			},
			createElement('circle', { cx: '12', cy: '12', r: '10' }),
			createElement('polyline', { points: '12 6 12 12 16 14' })
		);

		return createElement(
			Fragment,
			null,
			createElement(
				InspectorControls,
				null,
				createElement(
					PanelBody,
					{ title: __('General Settings', 'frontblocks'), initialOpen: true },
					createElement(ToggleControl, {
						label: __('Show Icon', 'frontblocks'),
						checked: showIcon,
						onChange: function (value) {
							return setAttributes({ showIcon: value });
						}
					}),
					createElement(TextControl, {
						label: __('Prefix', 'frontblocks'),
						value: prefix,
						onChange: function (value) {
							return setAttributes({ prefix: value });
						},
						help: __('Text to display before the reading time number. Example: "This blog takes"', 'frontblocks'),
						placeholder: __('Example: This blog takes', 'frontblocks')
					}),
					createElement(TextControl, {
						label: __('Suffix', 'frontblocks'),
						value: suffix,
						onChange: function (value) {
							return setAttributes({ suffix: value });
						},
						help: __('Text to display after the reading time number. Example: "minutes to read"', 'frontblocks'),
						placeholder: __('Example: minutes to read', 'frontblocks')
					}),
					createElement(SelectControl, {
						label: __('Alignment', 'frontblocks'),
						value: alignment,
						options: [
							{ label: __('Left', 'frontblocks'), value: 'left' },
							{ label: __('Center', 'frontblocks'), value: 'center' },
							{ label: __('Right', 'frontblocks'), value: 'right' }
						],
						onChange: function (value) {
							return setAttributes({ alignment: value });
						}
					})
				),
				createElement(
					PanelBody,
					{ title: __('Style Settings', 'frontblocks'), initialOpen: false },
					createElement(RangeControl, {
						label: __('Font Size', 'frontblocks'),
						value: fontSize,
						onChange: function (value) {
							return setAttributes({ fontSize: value });
						},
						min: 10,
						max: 48
					}),
					createElement('p', { style: { marginBottom: '8px', fontWeight: '500' } }, __('Text Color', 'frontblocks')),
					createElement(ColorPalette, {
						value: textColor,
						onChange: function (value) {
							return setAttributes({ textColor: value || 'inherit' });
						}
					}),
					createElement('p', { style: { marginBottom: '8px', fontWeight: '500' } }, __('Background Color', 'frontblocks')),
					createElement(ColorPalette, {
						value: backgroundColor,
						onChange: function (value) {
							return setAttributes({ backgroundColor: value || 'transparent' });
						}
					}),
					createElement('p', { style: { marginBottom: '8px', fontWeight: '500' } }, __('Icon Color', 'frontblocks')),
					createElement(ColorPalette, {
						value: iconColor,
						onChange: function (value) {
							return setAttributes({ iconColor: value || 'currentColor' });
						}
					}),
					createElement(RangeControl, {
						label: __('Padding', 'frontblocks'),
						value: padding,
						onChange: function (value) {
							return setAttributes({ padding: value });
						},
						min: 0,
						max: 50
					}),
					createElement(RangeControl, {
						label: __('Border Radius', 'frontblocks'),
						value: borderRadius,
						onChange: function (value) {
							return setAttributes({ borderRadius: value });
						},
						min: 0,
						max: 50
					})
				)
			),
			createElement(
				'div',
				blockProps,
				createElement(
					'div',
					{ className: 'frbl-reading-time', style: styleVars },
					showIcon && createElement('span', { className: 'frbl-reading-time-icon' }, iconSvg),
					createElement(
						'span',
						{ className: 'frbl-reading-time-text' },
						prefix && prefix + ' ',
						readingTime,
						suffix && ' ' + suffix
					)
				)
			)
		);
	}

	// Register the block.
	registerBlockType('frontblocks/reading-time', {
		title: __('Reading Time', 'frontblocks'),
		description: __('Display estimated reading time for the current post or page.', 'frontblocks'),
		category: 'common',
		icon: 'clock',
		keywords: [
			__('reading', 'frontblocks'),
			__('time', 'frontblocks'),
			__('duration', 'frontblocks'),
			__('blog', 'frontblocks')
		],
		attributes: {
			postId: {
				type: 'number',
				default: 0
			},
			showIcon: {
				type: 'boolean',
				default: true
			},
			prefix: {
				type: 'string',
				default: ''
			},
			suffix: {
				type: 'string',
				default: 'min'
			},
			className: {
				type: 'string',
				default: ''
			},
			textColor: {
				type: 'string',
				default: 'inherit'
			},
			backgroundColor: {
				type: 'string',
				default: 'transparent'
			},
			fontSize: {
				type: 'number',
				default: 16
			},
			iconColor: {
				type: 'string',
				default: 'currentColor'
			},
			alignment: {
				type: 'string',
				default: 'left'
			},
			padding: {
				type: 'number',
				default: 10
			},
			borderRadius: {
				type: 'number',
				default: 5
			}
		},
		edit: ReadingTimeEdit,
		save: function () {
			return null; // Dynamic block, render on server side.
		}
	});
})();

