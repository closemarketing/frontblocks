(function() {
	'use strict';

	const { __ } = wp.i18n;
	const { addFilter } = wp.hooks;
	const { Fragment, useState } = wp.element;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl, TextareaControl, Button, Notice } = wp.components;

	/**
	 * Add custom SVG animation controls to GenerateBlocks Shape block.
	 */
	function addShapeAnimationControls(BlockEdit) {
		return function(props) {
			// Only add controls to shape blocks.
			if (props.name !== 'generateblocks/shape') {
				return wp.element.createElement(BlockEdit, props);
			}

			const { attributes, setAttributes } = props;
			const {
				frblCustomSvgAnimationEnabled = false,
				frblCustomSvgAnimationJson = ''
			} = attributes;

			const [jsonError, setJsonError] = useState('');
			const [jsonPreview, setJsonPreview] = useState(null);

			// Detect if JSON is Lottie format.
			const isLottieJson = function(parsed) {
				return parsed.v && parsed.fr && parsed.layers;
			};

			// Validate JSON.
			const validateJson = function(jsonString) {
				try {
					const parsed = JSON.parse(jsonString);
					
					// Check if it's Lottie format.
					if (isLottieJson(parsed)) {
						setJsonError('');
						setJsonPreview({
							type: 'lottie',
							name: parsed.nm || 'Lottie Animation',
							version: parsed.v,
							framerate: parsed.fr,
							duration: parsed.op ? (parsed.op / parsed.fr).toFixed(2) + 's' : 'N/A'
						});
						return true;
					}
					
					// Check if it's custom SVG + CSS format.
					if (!parsed.svg) {
						setJsonError(__('Missing "svg" property in JSON (or not valid Lottie format)', 'frontblocks'));
						return false;
					}
					
					if (!parsed.animation || !parsed.animation.keyframes || !parsed.animation.name) {
						setJsonError(__('Missing required animation properties (keyframes, name)', 'frontblocks'));
						return false;
					}
					
					setJsonError('');
					setJsonPreview({
						type: 'css',
						name: parsed.animation.name,
						trigger: parsed.animation.trigger || 'load',
						duration: parsed.animation.duration || '1s'
					});
					return true;
				} catch (e) {
					setJsonError(__('Invalid JSON format: ', 'frontblocks') + e.message);
					return false;
				}
			};

			// Handle JSON change.
			const handleJsonChange = function(value) {
				setAttributes({ frblCustomSvgAnimationJson: value });
				if (value.trim()) {
					validateJson(value);
				} else {
					setJsonError('');
					setJsonPreview(null);
				}
			};

			// Example JSON template.
			const exampleJson = JSON.stringify({
				"svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" width=\"48\" height=\"48\" fill=\"currentColor\"><path d=\"M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z\"/></svg>",
				"animation": {
					"name": "myCustomAnimation",
					"keyframes": "@keyframes myCustomAnimation { 0% { transform: rotate(0deg) scale(1); } 50% { transform: rotate(180deg) scale(1.2); } 100% { transform: rotate(360deg) scale(1); } }",
					"duration": "2s",
					"delay": "0s",
					"infinite": true,
					"trigger": "load"
				}
			}, null, 2);

			return wp.element.createElement(
				Fragment,
				{},
				wp.element.createElement(BlockEdit, props),
				wp.element.createElement(
					InspectorControls,
					{},
					wp.element.createElement(
						PanelBody,
						{
							title: __('FrontBlocks Custom SVG Animation', 'frontblocks'),
							initialOpen: false
						},
						wp.element.createElement(ToggleControl, {
							label: __('Enable Custom SVG Animation', 'frontblocks'),
							checked: frblCustomSvgAnimationEnabled,
							onChange: function(value) {
								setAttributes({ frblCustomSvgAnimationEnabled: value });
							},
							help: __('Replace SVG and add custom CSS animations via JSON', 'frontblocks')
						}),
						frblCustomSvgAnimationEnabled && wp.element.createElement(
							Fragment,
							{},
							wp.element.createElement('p', {
								style: { fontSize: '12px', marginBottom: '8px', color: '#757575' }
							}, __('Paste your JSON configuration below:', 'frontblocks')),
							wp.element.createElement(TextareaControl, {
								label: __('JSON Configuration', 'frontblocks'),
								value: frblCustomSvgAnimationJson,
								onChange: handleJsonChange,
								rows: 10,
								help: __('JSON with svg and animation properties', 'frontblocks')
							}),
							jsonError && wp.element.createElement(Notice, {
								status: 'error',
								isDismissible: false
							}, jsonError),
							jsonPreview && wp.element.createElement(Notice, {
								status: 'success',
								isDismissible: false
							}, __('✓ Valid JSON configuration', 'frontblocks')),
							wp.element.createElement('details', {
								style: { marginTop: '16px', fontSize: '12px' }
							},
								wp.element.createElement('summary', {
									style: { cursor: 'pointer', fontWeight: '600', marginBottom: '8px' }
								}, __('📋 Show example JSON', 'frontblocks')),
								wp.element.createElement('pre', {
									style: { 
										background: '#f6f7f7',
										padding: '12px',
										borderRadius: '4px',
										overflow: 'auto',
										fontSize: '11px',
										maxHeight: '300px'
									}
								}, exampleJson),
								wp.element.createElement(Button, {
									isSecondary: true,
									isSmall: true,
									onClick: function() {
										setAttributes({ frblCustomSvgAnimationJson: exampleJson });
										validateJson(exampleJson);
									},
									style: { marginTop: '8px' }
								}, __('Use this example', 'frontblocks'))
							),
							jsonPreview && wp.element.createElement('div', {
								style: { 
									marginTop: '16px', 
									padding: '12px', 
									background: jsonPreview.type === 'lottie' ? '#e8f5e9' : '#f6f7f7',
									borderRadius: '4px',
									fontSize: '12px'
								}
							},
								wp.element.createElement('strong', {}, 
									jsonPreview.type === 'lottie' ? '🎬 Lottie Animation' : '🎨 CSS Animation'
								),
								wp.element.createElement('br'),
								wp.element.createElement('span', {}, 
									__('Name:', 'frontblocks') + ' ' + (jsonPreview.name || 'N/A')
								),
								wp.element.createElement('br'),
								jsonPreview.type === 'lottie' ? (
									wp.element.createElement('span', {}, 
										__('Version:', 'frontblocks') + ' ' + jsonPreview.version + ', ' + 
										__('FPS:', 'frontblocks') + ' ' + jsonPreview.framerate
									)
								) : (
									wp.element.createElement('span', {}, 
										__('Trigger:', 'frontblocks') + ' ' + jsonPreview.trigger
									)
								),
								wp.element.createElement('br'),
								wp.element.createElement('span', {}, 
									__('Duration:', 'frontblocks') + ' ' + jsonPreview.duration
								)
							)
						)
					)
				)
			);
		};
	}

	addFilter(
		'editor.BlockEdit',
		'frontblocks/shape-custom-svg-animation-controls',
		addShapeAnimationControls
	);

	/**
	 * Add preview and handle size/color inheritance in editor.
	 */
	const { createHigherOrderComponent } = wp.compose;
	const { useEffect, useRef } = wp.element;

	const withShapeAnimationPreview = createHigherOrderComponent(function(BlockListBlock) {
		return function(props) {
			if (props.name !== 'generateblocks/shape') {
				return wp.element.createElement(BlockListBlock, props);
			}

			const { attributes } = props;
			const {
				frblCustomSvgAnimationEnabled = false,
				frblCustomSvgAnimationJson = '',
				styles = {}
			} = attributes;

			const lottieContainerRef = useRef(null);
			const lottieInstanceRef = useRef(null);

			useEffect(function() {
				if (!frblCustomSvgAnimationEnabled || !frblCustomSvgAnimationJson) {
					return;
				}

				try {
					const parsed = JSON.parse(frblCustomSvgAnimationJson);
					
					// Check if it's Lottie.
					const isLottie = parsed.v && parsed.fr && parsed.layers;
					
					if (isLottie && typeof lottie !== 'undefined') {
						// Wait for DOM to be ready.
						setTimeout(function() {
							const blockElement = document.querySelector('[data-block="' + props.clientId + '"]');
							if (!blockElement) return;

							// Find or create Lottie container in the Shape block.
							let lottieContainer = blockElement.querySelector('.frbl-lottie-preview');
							
							if (!lottieContainer) {
								// Find the gb-shape element.
								const shapeElement = blockElement.querySelector('.gb-shape');
								if (shapeElement) {
									// HIDE original SVG completely.
									const originalSvg = shapeElement.querySelector('svg');
									if (originalSvg) {
										originalSvg.style.display = 'none';
									}

								// Create Lottie preview container.
								lottieContainer = document.createElement('div');
								lottieContainer.className = 'frbl-lottie-preview';
								lottieContainer.style.cssText = 'position: relative; width: 100%; height: 100%; pointer-events: none;';
									
									// Replace content with Lottie container.
									shapeElement.appendChild(lottieContainer);
								}
							}

							if (lottieContainer) {
								// Clean up previous instance.
								if (lottieInstanceRef.current) {
									lottieInstanceRef.current.destroy();
								}

								lottieContainer.innerHTML = '';

								// Apply size from Shape block styles.
								const svgStyles = styles.svg || {};
								if (svgStyles.width) {
									lottieContainer.style.width = svgStyles.width;
								}
								if (svgStyles.height) {
									lottieContainer.style.height = svgStyles.height;
								}

								// Set color BEFORE creating animation.
								if (svgStyles.fill) {
									lottieContainer.style.setProperty('--lottie-color', svgStyles.fill);
								}

								// Create Lottie animation.
								const loop = parsed.animation && typeof parsed.animation.loop !== 'undefined' ? parsed.animation.loop : true;
								const autoplay = parsed.animation && typeof parsed.animation.autoplay !== 'undefined' ? parsed.animation.autoplay : true;
								const speed = parsed.animation && parsed.animation.speed ? parsed.animation.speed : 1;

								const animation = lottie.loadAnimation({
									container: lottieContainer,
									renderer: 'svg',
									loop: loop,
									autoplay: autoplay,
									animationData: parsed
								});

								animation.setSpeed(speed);
								lottieInstanceRef.current = animation;

								// Apply color IMMEDIATELY after creating animation.
								applyColorToLottie(lottieContainer, svgStyles.fill);

								// Apply color immediately after SVG is added to DOM.
								animation.addEventListener('DOMLoaded', function() {
									applyColorToLottie(lottieContainer, svgStyles.fill);
								});

								// Apply color on EVERY SINGLE FRAME (most aggressive approach).
								animation.addEventListener('enterFrame', function() {
									applyColorToLottie(lottieContainer, svgStyles.fill);
								});

								// Apply color multiple times immediately for instant coverage.
								setTimeout(function() { applyColorToLottie(lottieContainer, svgStyles.fill); }, 0);
								setTimeout(function() { applyColorToLottie(lottieContainer, svgStyles.fill); }, 10);
								setTimeout(function() { applyColorToLottie(lottieContainer, svgStyles.fill); }, 20);
								setTimeout(function() { applyColorToLottie(lottieContainer, svgStyles.fill); }, 50);
								setTimeout(function() { applyColorToLottie(lottieContainer, svgStyles.fill); }, 100);
							}
						}, 300);
					}
				} catch (error) {
					console.warn('FrontBlocks: Error rendering Lottie preview', error);
				}

				// Cleanup on unmount.
				return function() {
					if (lottieInstanceRef.current) {
						lottieInstanceRef.current.destroy();
						lottieInstanceRef.current = null;
					}
				};
			}, [frblCustomSvgAnimationEnabled, frblCustomSvgAnimationJson, props.clientId, styles]);

			return wp.element.createElement(BlockListBlock, props);
		};
	}, 'withShapeAnimationPreview');

	/**
	 * Apply color to Lottie animation SVG elements - AGGRESSIVELY.
	 */
	function applyColorToLottie(container, color) {
		if (!container) return;

		// If no color provided, return early.
		if (!color) return;

		// Set CSS variable.
		container.style.setProperty('--lottie-color', color);

		// Find ALL SVG elements - be very aggressive.
		const elements = container.querySelectorAll('svg *, [fill], [stroke]');
		
		elements.forEach(function(element) {
			// Get current fill and stroke.
			const currentFill = element.getAttribute('fill');
			const currentStroke = element.getAttribute('stroke');
			const computedFill = window.getComputedStyle(element).fill;
			
			// Apply to anything that has color.
			if (currentFill && currentFill !== 'none' && currentFill !== 'transparent') {
				element.setAttribute('fill', color);
				element.style.setProperty('fill', color, 'important');
			}
			
			// Also check computed style.
			if (computedFill && computedFill !== 'none' && computedFill !== 'transparent' && computedFill !== 'rgba(0, 0, 0, 0)') {
				element.style.setProperty('fill', color, 'important');
			}
			
			// Also handle stroke if it exists.
			if (currentStroke && currentStroke !== 'none' && currentStroke !== 'transparent') {
				element.setAttribute('stroke', color);
				element.style.setProperty('stroke', color, 'important');
			}
		});

		// Also try to set fill on the SVG itself.
		const svg = container.querySelector('svg');
		if (svg) {
			svg.style.setProperty('fill', color, 'important');
		}
	}

	addFilter(
		'editor.BlockListBlock',
		'frontblocks/shape-animation-preview',
		withShapeAnimationPreview
	);

})();

