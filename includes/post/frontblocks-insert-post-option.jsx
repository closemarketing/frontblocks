// Insert Post Block Component
const { addFilter } = wp.hooks;
const { Fragment, useState, useEffect, useRef } = wp.element;
const { InspectorControls, useBlockProps, RichText } = wp.blockEditor;
const { 
	SelectControl, 
	TextControl, 
	PanelBody, 
	Button, 
	Spinner,
	Notice,
	Card,
	CardBody,
	CardHeader,
	ToggleControl
} = wp.components;
const { __ } = wp.i18n;
const { apiFetch } = wp;

// Custom Insert Post Block
function InsertPostBlock(props) {
	const { attributes, setAttributes } = props;
	const {
		selectedPostId = 0,
		selectedPostType = 'post',
		selectedPostTitle = '',
		selectedPostContent = '',
		className = ''
	} = attributes;

	const [searchTerm, setSearchTerm] = useState('');
	const [isSearching, setIsSearching] = useState(false);
	const [searchError, setSearchError] = useState('');
	const [showSearch, setShowSearch] = useState(false);
	const searchInputRef = useRef(null);

	// Get available post types
	const [postTypes, setPostTypes] = useState([
		{ label: __('Posts', 'frontblocks'), value: 'post' },
		{ label: __('Pages', 'frontblocks'), value: 'page' }
	]);

	useEffect(() => {
		// Get custom post types
		apiFetch({ path: '/wp/v2/types' }).then(types => {
			const customTypes = Object.keys(types)
				.filter(type => type !== 'post' && type !== 'page')
				.map(type => ({
					label: types[type].name,
					value: type
				}));
			
			setPostTypes(prev => [...prev, ...customTypes]);
		}).catch(() => {
			// If API fails, continue with default post types
		});
	}, []);

	// Initialize jQuery autocomplete when component mounts or search is shown
	useEffect(() => {
		if (showSearch && searchInputRef.current && typeof jQuery !== 'undefined') {
			const $input = jQuery(searchInputRef.current);
			
			// Check if autocomplete is already initialized before destroying
			if ($input.hasClass('ui-autocomplete-input')) {
				try {
					$input.autocomplete('destroy');
				} catch (e) {
					// If destroy fails, remove the autocomplete class manually
					$input.removeClass('ui-autocomplete-input');
				}
			}
			
			// Initialize autocomplete
			$input.autocomplete({
				source: function(request, response) {
					if (request.term.length < 2) {
						response([]);
						return;
					}

					setIsSearching(true);
					setSearchError('');

					const formData = new FormData();
					formData.append('action', 'frbl_search_posts');
					formData.append('nonce', frblInsertPost.nonce);
					formData.append('search', request.term);
					formData.append('post_type', selectedPostType);

					fetch(frblInsertPost.ajaxUrl, {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						setIsSearching(false);
						if (data.success) {
							const results = data.data.map(post => ({
								label: `${post.title} (${post.type})`,
								value: post.title,
								post: post
							}));
							response(results);
						} else {
							setSearchError(data.data || __('Search failed', 'frontblocks'));
							response([]);
						}
					})
					.catch(error => {
						setIsSearching(false);
						setSearchError(__('Search failed. Please try again.', 'frontblocks'));
						response([]);
					});
				},
				minLength: 2,
				delay: 300,
				select: function(event, ui) {
					selectPost(ui.item.post);
					return false;
				},
				open: function() {
					jQuery(this).autocomplete('widget').css('z-index', 999999);
				}
			});

			// Cleanup function
			return () => {
				try {
					if ($input.hasClass('ui-autocomplete-input')) {
						$input.autocomplete('destroy');
					}
				} catch (e) {
					// If destroy fails, just remove the class
					$input.removeClass('ui-autocomplete-input');
				}
			};
		}
	}, [showSearch, selectedPostType]);

	const selectPost = (post) => {
		setAttributes({
			selectedPostId: post.id,
			selectedPostTitle: post.title,
			selectedPostType: post.type
		});
		setShowSearch(false);
		setSearchTerm('');
		setSearchError('');
	};

	const clearSelection = () => {
		setAttributes({
			selectedPostId: 0,
			selectedPostTitle: '',
			selectedPostContent: ''
		});
		setShowSearch(false);
		setSearchTerm('');
		setSearchError('');
	};

	const blockProps = useBlockProps({
		className: `frbl-insert-post-block ${className}`
	});

	return (
		<Fragment>
			<div {...blockProps}>
				{selectedPostId ? (
					<div className="frbl-insert-post-preview">
						<h2 className="frbl-insert-post-title">
							{selectedPostTitle}
						</h2>
						<div className="frbl-insert-post-content">
							{selectedPostContent || __('Content will be loaded on the frontend', 'frontblocks')}
						</div>
						<div className="frbl-insert-post-actions">
							<Button 
								isSecondary 
								onClick={() => setShowSearch(true)}
							>
								{__('Change Post', 'frontblocks')}
							</Button>
							<Button 
								isDestructive 
								onClick={clearSelection}
							>
								{__('Clear Selection', 'frontblocks')}
							</Button>
						</div>
					</div>
				) : (
					<div className="frbl-insert-post-empty">
						<p>{__('No post selected. Use the sidebar to search and select a post.', 'frontblocks')}</p>
						<Button 
							isPrimary 
							onClick={() => setShowSearch(true)}
						>
							{__('Select Post', 'frontblocks')}
						</Button>
					</div>
				)}
			</div>

			<InspectorControls>
				<PanelBody
					title={__('Insert Post Settings', 'frontblocks')}
					initialOpen={true}
				>
					{(!selectedPostId || showSearch) && (
						<>
							<SelectControl
								label={__('Post Type', 'frontblocks')}
								value={selectedPostType}
								options={postTypes}
								onChange={(value) => setAttributes({ selectedPostType: value })}
							/>
							
							<TextControl
								ref={searchInputRef}
								label={__('Search Posts', 'frontblocks')}
								value={searchTerm}
								onChange={setSearchTerm}
								placeholder={__('Start typing to search posts...', 'frontblocks')}
								help={__('Type at least 2 characters to search', 'frontblocks')}
							/>

							{isSearching && (
								<div style={{ marginTop: '10px' }}>
									<Spinner />
									<span style={{ marginLeft: '8px' }}>{__('Searching...', 'frontblocks')}</span>
								</div>
							)}

							{searchError && (
								<Notice status="error" isDismissible={false}>
									{searchError}
								</Notice>
							)}
						</>
					)}

					{selectedPostId && !showSearch && (
						<div className="frbl-selected-post-info">
							<h4>{__('Selected Post', 'frontblocks')}</h4>
							<Card>
								<CardBody>
									<p><strong>{__('Title:', 'frontblocks')}</strong> {selectedPostTitle}</p>
									<p><strong>{__('Type:', 'frontblocks')}</strong> {selectedPostType}</p>
									<p><strong>{__('ID:', 'frontblocks')}</strong> {selectedPostId}</p>
								</CardBody>
							</Card>
							<Button 
								isSecondary 
								onClick={() => setShowSearch(true)}
								style={{ marginTop: '10px' }}
							>
								{__('Change Post', 'frontblocks')}
							</Button>
						</div>
					)}
				</PanelBody>
			</InspectorControls>
		</Fragment>
	);
}

// Register the custom block
const { registerBlockType } = wp.blocks;

registerBlockType('frontblocks/insert-post', {
	title: __('Insert Post', 'frontblocks'),
	description: __('Display content from another post or page', 'frontblocks'),
	category: 'generateblocks',
	icon: 'admin-post',
	keywords: [
		__('post', 'frontblocks'),
		__('content', 'frontblocks'),
		__('insert', 'frontblocks'),
		__('display', 'frontblocks')
	],
	supports: {
		html: false,
		align: ['wide', 'full']
	},
	attributes: {
		selectedPostId: {
			type: 'number',
			default: 0
		},
		selectedPostType: {
			type: 'string',
			default: 'post'
		},
		selectedPostTitle: {
			type: 'string',
			default: ''
		},
		selectedPostContent: {
			type: 'string',
			default: ''
		},
		className: {
			type: 'string',
			default: ''
		}
	},
	edit: InsertPostBlock,
	save: () => null // Using PHP render callback
});

// Add custom panel to existing GenerateBlocks Grid block
function addInsertPostPanel(BlockEdit) {
	return (props) => {
		if (props.name !== 'generateblocks/grid') {
			return <BlockEdit {...props} />;
		}

		const { frblInsertPostEnabled = false } = props.attributes;

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody
						title={__('Insert Post Integration', 'frontblocks')}
						initialOpen={false}
					>
						<ToggleControl
							label={__('Enable Insert Post Grid', 'frontblocks')}
							checked={frblInsertPostEnabled}
							onChange={(value) => props.setAttributes({ frblInsertPostEnabled: value })}
							help={__('Enable insert post functionality for this grid', 'frontblocks')}
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}

addFilter(
	'editor.BlockEdit',
	'frontblocks/insert-post-grid-panel',
	addInsertPostPanel
); 