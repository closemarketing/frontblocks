// Add custom controls to the Advanced panel of GenerateBlocks Grid block
const { addFilter } = wp.hooks;
const { Fragment, useEffect, useRef } = wp.element;
const { InspectorControls, PanelColorSettings } = wp.blockEditor;
const { SelectControl, TextControl, PanelBody, ToggleControl } = wp.components;
const { __ } = wp.i18n;

/**
 * Returns the document that renders block content.
 * WordPress 6.x uses an <iframe> for the editor canvas.
 */
function getEditorDocument() {
	const iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
	return ( iframe && iframe.contentDocument && iframe.contentDocument.body )
		? iframe.contentDocument
		: document;
}

const UUID_RE = /^[0-9a-f]{8}-[0-9a-f]{4}-/;

/**
 * Walk an element's subtree to find the container whose DIRECT children
 * are actual blocks (UUID data-block).  Stops at depth 4.
 */
function findSlidesParent( el, depth ) {
	if ( depth === 0 ) return null;
	if ( Array.from( el.children ).some( ( c ) => UUID_RE.test( c.dataset.block || '' ) ) ) {
		return el;
	}
	for ( const child of el.children ) {
		const found = findSlidesParent( child, depth - 1 );
		if ( found ) return found;
	}
	return null;
}

/**
 * Find the grid DOM element for a given block.
 *
 * For generateblocks/element: [data-block][data-type] IS the gb-element.
 *   Do NOT search inside – that returns a child item.
 * For generateblocks/grid:  look for .gb-grid-wrapper inside.
 * For core/group: walk the subtree to find the element whose children
 *   are the real blocks, because WP nests them differently across versions.
 */
function findGridEl( blockEl, name ) {
	if ( name === 'generateblocks/element' ) {
		return blockEl;
	}
	if ( name === 'generateblocks/grid' ) {
		if ( blockEl.classList.contains( 'gb-grid-wrapper' ) ) return blockEl;
		const grid = blockEl.querySelector( '.gb-grid-wrapper' );
		return grid || null;
	}
	if ( name === 'core/group' ) {
		// Walk up to 4 levels deep to find where the child blocks live.
		return findSlidesParent( blockEl, 4 ) || blockEl;
	}
	if ( name === 'core/query' ) {
		return blockEl.querySelector( '.wp-block-post-template' ) || null;
	}
	return null;
}

function addCustomCarouselPanel( BlockEdit ) {
	return ( props ) => {
		if (
			props.name !== 'generateblocks/grid' &&
			props.name !== 'generateblocks/element' &&
			props.name !== 'core/group' &&
			props.name !== 'core/query'
		) {
			return <BlockEdit { ...props } />;
		}

		if ( props.name === 'generateblocks/element' ) {
			const styles = props.attributes.styles || {};
			if ( styles.display !== 'grid' ) {
				return <BlockEdit { ...props } />;
			}
		}

		if ( props.name === 'core/group' ) {
			const layout = props.attributes.layout || {};
			if ( layout.type !== 'grid' ) {
				return <BlockEdit { ...props } />;
			}
		}

		const {
			frblGridOption      = 'none',
			frblItemsToView     = '4',
			frblLaptopToView    = '3',
			frblTabletToView    = '2',
			frblResponsiveToView = '1',
			frblAutoplay        = '',
			frblGap             = '20',
			frblButtons         = 'arrows',
			frblRewind          = true,
			frblButtonColor,
			frblButtonBgColor,
			frblButtonsPosition = 'side',
			frblDisableOnDesktop = false,
		} = props.attributes;

		// ── Editor carousel preview ──────────────────────────────────────────
		const stateRef = useRef( null );

		useEffect( () => {
			let mounted = true;
			let timer   = null;

		function cleanup() {
			if ( ! stateRef.current ) return;
			const { gridEl, slides, spacer, prevBtn, nextBtn, resizeObs, scrollFn } = stateRef.current;

			// Disconnect observer and scroll listener.
			if ( resizeObs ) resizeObs.disconnect();
			const editorDoc = getEditorDocument();
			const editorScrollEl = editorDoc.documentElement || editorDoc.body;
			if ( scrollFn ) editorScrollEl.removeEventListener( 'scroll', scrollFn, true );

			// Remove arrows from outer window body (completely outside React).
			[ prevBtn, nextBtn ].forEach( ( btn ) => {
				if ( btn && btn.parentNode ) btn.parentNode.removeChild( btn );
			} );

			// Remove right spacer element.
			if ( spacer && spacer.parentNode ) spacer.parentNode.removeChild( spacer );

			// Restore gridEl styles.
			try {
				gridEl.classList.remove( 'frbl-carousel-noscrollbar' );
				[
					'display', 'flex-wrap', 'gap',
					'overflow-x', 'scroll-behavior',
					'padding-left', 'padding-right',
				].forEach( ( p ) => gridEl.style.removeProperty( p ) );

					slides.forEach( ( slide ) => {
						[ 'flex-shrink', 'width', 'min-width',
						  'margin-left', 'margin-right', 'grid-column',
						].forEach( ( p ) => slide.style.removeProperty( p ) );
					} );
				} catch ( e ) {}

				stateRef.current = null;
			}

			function init() {
				if ( ! mounted ) return;
				cleanup();

				const editorDoc = getEditorDocument();

				// GB 2.x wraps blocks in a root element that shares the same data-block UUID.
				// Use data-type to select the correct inner element; fall back for native blocks.
				let blockEl = editorDoc.querySelector(
					`[data-block="${ props.clientId }"][data-type="${ props.name }"]`
				);
				if ( ! blockEl ) {
					blockEl = editorDoc.querySelector( `[data-block="${ props.clientId }"]` );
				}
				if ( ! blockEl ) return;

				const gridEl = findGridEl( blockEl, props.name );
				if ( ! gridEl ) return;

				// Slides = direct children that are real blocks (UUID data-block).
				const slides = Array.from( gridEl.children ).filter(
					( el ) => UUID_RE.test( el.dataset.block || '' )
				);
				if ( slides.length === 0 ) return;

				const perView    = Math.max( 1, parseInt( frblItemsToView ) || 4 );
				const gap        = Math.max( 0, parseInt( frblGap ) || 20 );
				const btnColor   = frblButtonColor   || '#fff';
				const btnBg      = frblButtonBgColor || 'rgba(0,0,0,0.45)';

				// Measure content-box width before any style changes.
				const editorWin  = editorDoc.defaultView || window;
			const ARROW_W    = 40; // px reserved on each side for arrow buttons.
			const cs         = editorWin.getComputedStyle( gridEl );
			const innerW     = gridEl.getBoundingClientRect().width
				- ( parseFloat( cs.paddingLeft )  || 0 )
				- ( parseFloat( cs.paddingRight ) || 0 );
			const totalWidth = Math.round( innerW ) || 600;
			// Content area after reserving space for both arrow buttons.
			const contentW   = totalWidth - ARROW_W * 2;
			const slideWidth = Math.round( ( contentW - gap * ( perView - 1 ) ) / perView );
			const step       = slideWidth + gap;

			// ── Apply scroll-based layout directly to gridEl ─────────────
			// No DOM restructuring → no React reconciliation conflicts.
			gridEl.classList.add( 'frbl-carousel-noscrollbar' );
			gridEl.style.display        = 'flex';
			gridEl.style.flexWrap       = 'nowrap';
			gridEl.style.gap            = gap + 'px';
			gridEl.style.overflowX      = 'scroll';
			gridEl.style.scrollBehavior = 'smooth';
			// paddingLeft creates left space for the prev arrow.
			// paddingRight is NOT used because Chrome ignores it in scroll extent;
			// a spacer flex child is appended instead to guarantee right-side space.
			gridEl.style.paddingLeft    = ARROW_W + 'px';
			gridEl.style.paddingRight   = '0';

			slides.forEach( ( slide ) => {
				slide.style.flexShrink  = '0';
				slide.style.width       = slideWidth + 'px';
				slide.style.minWidth    = slideWidth + 'px';
				slide.style.marginLeft  = '0';
				slide.style.marginRight = '0';
				slide.style.gridColumn  = 'unset';
			} );

			// Right-side spacer: Chrome does not include padding-right in horizontal
			// scroll extent, so we use a real flex child to guarantee right space.
			const appender = gridEl.querySelector( '.block-list-appender' );
			const spacer   = editorDoc.createElement( 'span' );
			spacer.setAttribute( 'data-frbl-spacer', '1' );
			spacer.style.cssText = `display:block;flex-shrink:0;width:${ ARROW_W }px;min-width:${ ARROW_W }px;`;
			gridEl.insertBefore( spacer, appender || null );

			// ── Navigation (scrollLeft, infinite loop) ───────────────────
				let idx = 0;

				function scrollTo( n ) {
					const total = slides.length;
					if ( n >= total ) {
						// Forward past last: instant snap to 0 then continue.
						gridEl.style.scrollBehavior = 'auto';
						gridEl.scrollLeft = 0;
						void gridEl.offsetWidth;
						gridEl.style.scrollBehavior = 'smooth';
						idx = 0;
						return;
					}
					if ( n < 0 ) {
						// Back past first: instant snap to last.
						gridEl.style.scrollBehavior = 'auto';
						gridEl.scrollLeft = Math.round( ( total - 1 ) * step );
						void gridEl.offsetWidth;
						gridEl.style.scrollBehavior = 'smooth';
						idx = total - 1;
						return;
					}
					idx = n;
					gridEl.scrollLeft = Math.round( idx * step );
				}

				// ── Arrows in OUTER document body ────────────────────────────
				// Placing them outside the iframe means React never touches them.
				const iframe = document.querySelector( 'iframe[name="editor-canvas"]' );

				function updateArrowPos() {
					if ( ! stateRef.current ) return;
					const ifrRect = iframe ? iframe.getBoundingClientRect() : { top: 0, left: 0 };
					const rect    = gridEl.getBoundingClientRect(); // coords in iframe viewport
					const midY    = Math.round( ifrRect.top + rect.top + rect.height / 2 - 16 );

					prevBtn.style.top   = midY + 'px';
					prevBtn.style.left  = Math.round( ifrRect.left + rect.left + 8 ) + 'px';
					nextBtn.style.top   = midY + 'px';
					nextBtn.style.right = Math.round( window.innerWidth - ( ifrRect.left + rect.right ) + 8 ) + 'px';
				}

				function makeArrow( dir ) {
					const btn = document.createElement( 'button' );
					btn.type  = 'button';
					btn.className = `frbl-editor-arrow frbl-editor-arrow-${ dir }`;
					btn.setAttribute( 'aria-label', dir === 'prev' ? 'Previous slide' : 'Next slide' );
					btn.style.cssText = `position:fixed;z-index:99999;background-color:${ btnBg };`;
					const d = dir === 'prev' ? 'M6 1L1 6L6 11' : 'M1 11L6 6L1 1';
					btn.innerHTML = `<svg width="7" height="12" viewBox="0 0 7 12" fill="none"><path d="${ d }" stroke="${ btnColor }" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
					btn.addEventListener( 'click', ( e ) => {
						e.stopPropagation();
						scrollTo( dir === 'prev' ? idx - 1 : idx + 1 );
					} );
					document.body.appendChild( btn );
					return btn;
				}

				const prevBtn = makeArrow( 'prev' );
				const nextBtn = makeArrow( 'next' );
				updateArrowPos();

				// Keep arrows positioned correctly when the editor scrolls or resizes.
				const resizeObs = new ResizeObserver( updateArrowPos );
				resizeObs.observe( gridEl );

				const editorScrollEl = editorDoc.documentElement || editorDoc.body;
				const scrollFn       = updateArrowPos;
				editorScrollEl.addEventListener( 'scroll', scrollFn, { passive: true, capture: true } );

				stateRef.current = { gridEl, slides, spacer, prevBtn, nextBtn, resizeObs, scrollFn };
			}

			if ( frblGridOption !== 'none' ) {
				timer = setTimeout( init, 300 );
			} else {
				cleanup();
			}

			return () => {
				mounted = false;
				if ( timer ) clearTimeout( timer );
				cleanup();
			};
		}, [
			frblGridOption, frblItemsToView, frblGap,
			frblButtonColor, frblButtonBgColor, props.clientId,
		] );
		// ── Inspector panel ──────────────────────────────────────────────────

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Carousel Settings', 'frontblocks' ) }
						initialOpen={ true }
					>
						<SelectControl
							label={ __( 'FrontBlocks Grid Option', 'frontblocks' ) }
							value={ frblGridOption }
							options={ [
								{ label: __( 'None',     'frontblocks' ), value: 'none'     },
								{ label: __( 'Carousel', 'frontblocks' ), value: 'carousel' },
								{ label: __( 'Slider',   'frontblocks' ), value: 'slider'   },
							] }
							onChange={ ( value ) => props.setAttributes( { frblGridOption: value } ) }
							help={ __( 'This option gives the option to make carousel in your grid block.', 'frontblocks' ) }
						/>
						{ frblGridOption !== 'none' && (
							<>
								<TextControl
									label={ __( 'Items to view (Desktop)', 'frontblocks' ) }
									value={ frblItemsToView }
									onChange={ ( value ) => props.setAttributes( { frblItemsToView: value } ) }
									help={ __( 'Number of items to show on desktop (>1200px)', 'frontblocks' ) }
								/>
								<TextControl
									label={ __( 'Items to view (Laptop)', 'frontblocks' ) }
									value={ frblLaptopToView }
									onChange={ ( value ) => props.setAttributes( { frblLaptopToView: value } ) }
									help={ __( 'Number of items to show on laptop (992px-1199px)', 'frontblocks' ) }
								/>
								<TextControl
									label={ __( 'Items to view (Tablet)', 'frontblocks' ) }
									value={ frblTabletToView }
									onChange={ ( value ) => props.setAttributes( { frblTabletToView: value } ) }
									help={ __( 'Number of items to show on tablet (768px-991px)', 'frontblocks' ) }
								/>
								<TextControl
									label={ __( 'Items to view (Mobile)', 'frontblocks' ) }
									value={ frblResponsiveToView }
									onChange={ ( value ) => props.setAttributes( { frblResponsiveToView: value } ) }
									help={ __( 'Number of items to show on mobile (<768px)', 'frontblocks' ) }
								/>
								<TextControl
									label={ __( 'Autoplay (seconds)', 'frontblocks' ) }
									value={ frblAutoplay }
									onChange={ ( value ) => props.setAttributes( { frblAutoplay: value } ) }
								/>
								<TextControl
									label={ __( 'Gap (px)', 'frontblocks' ) }
									value={ frblGap }
									onChange={ ( value ) => props.setAttributes( { frblGap: value } ) }
									help={ __( 'Space between slides in pixels. Leave empty for 20.', 'frontblocks' ) }
								/>
								{ frblGridOption === 'slider' && (
									<ToggleControl
										label={ __( 'Rewind', 'frontblocks' ) }
										checked={ frblRewind }
										onChange={ ( value ) => props.setAttributes( { frblRewind: value } ) }
									/>
								) }
								<SelectControl
									label={ __( 'Buttons', 'frontblocks' ) }
									value={ frblButtons }
									options={ [
										{ label: __( 'None',    'frontblocks' ), value: 'none'    },
										{ label: __( 'Bullets', 'frontblocks' ), value: 'bullets' },
										{ label: __( 'Arrows',  'frontblocks' ), value: 'arrows'  },
									] }
									onChange={ ( value ) => props.setAttributes( { frblButtons: value } ) }
								/>
								{ frblButtons === 'arrows' && (
									<SelectControl
										label={ __( 'Buttons Position', 'frontblocks' ) }
										value={ frblButtonsPosition }
										options={ [
											{ label: __( 'Side',   'frontblocks' ), value: 'side'   },
											{ label: __( 'Bottom', 'frontblocks' ), value: 'bottom' },
										] }
										onChange={ ( value ) => props.setAttributes( { frblButtonsPosition: value } ) }
									/>
								) }
								<PanelColorSettings
									title={ __( 'Button Colors', 'frontblocks' ) }
									colorSettings={ [
										{
											value:    frblButtonColor,
											onChange: ( color ) => props.setAttributes( { frblButtonColor: color } ),
											label:    __( 'Color button', 'frontblocks' ),
										},
										{
											value:    frblButtonBgColor,
											onChange: ( color ) => props.setAttributes( { frblButtonBgColor: color } ),
											label:    __( 'Color background button', 'frontblocks' ),
										},
									] }
								/>
								<ToggleControl
									label={ __( 'Disable on Desktop', 'frontblocks' ) }
									checked={ frblDisableOnDesktop }
									onChange={ ( value ) => props.setAttributes( { frblDisableOnDesktop: value } ) }
									help={ __( 'If enabled, carousel/slider will only work on mobile devices.', 'frontblocks' ) }
								/>
							</>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}

addFilter(
	'editor.BlockEdit',
	'frontblocks/gb-grid-carousel-panel',
	addCustomCarouselPanel
);
