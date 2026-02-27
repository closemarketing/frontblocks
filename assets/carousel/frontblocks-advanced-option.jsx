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
	return null;
}

function addCustomCarouselPanel( BlockEdit ) {
	return ( props ) => {
		if (
			props.name !== 'generateblocks/grid' &&
			props.name !== 'generateblocks/element' &&
			props.name !== 'core/group'
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
				const { originalParent, wrapper, gridEl, clones } = stateRef.current;
				try {
					// Remove clones first
					( clones || [] ).forEach( ( c ) => c.parentNode && c.parentNode.removeChild( c ) );

					if ( wrapper.parentNode ) {
						originalParent.replaceChild( gridEl, wrapper );
					}
					// Remove ALL inline styles we added
					[
						'display', 'flex-wrap', 'gap',
						'transition', 'will-change', 'transform',
					].forEach( ( p ) => gridEl.style.removeProperty( p ) );

					// Remove slide sizing from children
					Array.from( gridEl.children )
						.filter( ( el ) => UUID_RE.test( el.dataset.block || '' ) )
						.forEach( ( slide ) => {
							slide.style.removeProperty( 'flex-shrink' );
							slide.style.removeProperty( 'width' );
							slide.style.removeProperty( 'min-width' );
						} );
				} catch ( e ) {}
				stateRef.current = null;
			}

			function init() {
				if ( ! mounted ) return;
				cleanup();

				const editorDoc = getEditorDocument();

				// GB 2.x wraps blocks in a root element that shares the same data-block UUID.
				// Using data-type ensures we pick the inner element that actually IS the block.
				// For native blocks (core/group) there is no double-wrapper, so the fallback
				// to plain [data-block] is safe.
				let blockEl = editorDoc.querySelector(
					`[data-block="${ props.clientId }"][data-type="${ props.name }"]`
				);
				if ( ! blockEl ) {
					blockEl = editorDoc.querySelector( `[data-block="${ props.clientId }"]` );
				}
				if ( ! blockEl ) return;

				const gridEl = findGridEl( blockEl, props.name );
				if ( ! gridEl || gridEl.closest( '.frbl-editor-carousel' ) ) return;

				// Slides = direct children that are real blocks (UUID data-block).
				const slides = Array.from( gridEl.children ).filter(
					( el ) => UUID_RE.test( el.dataset.block || '' )
				);
				if ( slides.length === 0 ) return;

				const perView = Math.max( 1, parseInt( frblItemsToView ) || 4 );
				const gap     = Math.max( 0, parseInt( frblGap ) || 20 );

				// ── Create the overflow:hidden wrapper ───────────────────────
				const originalParent = gridEl.parentNode;
				const wrapper        = editorDoc.createElement( 'div' );
				wrapper.className    = 'frbl-editor-carousel';
				originalParent.replaceChild( wrapper, gridEl );
				wrapper.appendChild( gridEl );

				// ── Apply flex layout to the grid element itself ─────────────
				gridEl.style.display    = 'flex';
				gridEl.style.flexWrap   = 'nowrap';
				gridEl.style.gap        = gap + 'px';
				gridEl.style.transition = 'transform 400ms ease';
				gridEl.style.willChange = 'transform';

				// ── Size each slide ──────────────────────────────────────────
				const totalWidth = wrapper.offsetWidth || 600;
				const slideWidth = Math.floor(
					( totalWidth - gap * ( perView - 1 ) ) / perView
				);

				slides.forEach( ( slide ) => {
					slide.style.flexShrink = '0';
					slide.style.width      = slideWidth + 'px';
					slide.style.minWidth   = slideWidth + 'px';
				} );

				// ── Trailing loop clones ─────────────────────────────────────
				// Add perView clones from the start after the last real slide so
				// the next-arrow transition looks smooth before snapping back.
				const clones = [];
				const directAppender = Array.from( gridEl.children ).find(
					( c ) => c.classList.contains( 'block-list-appender' )
				);
				for ( let i = 0; i < perView; i++ ) {
					const clone = slides[ i % slides.length ].cloneNode( true );
					clone.removeAttribute( 'data-block' );
					clone.setAttribute( 'aria-hidden', 'true' );
					clone.classList.add( 'frbl-slide-clone' );
					clone.style.flexShrink    = '0';
					clone.style.width         = slideWidth + 'px';
					clone.style.minWidth      = slideWidth + 'px';
					clone.style.pointerEvents = 'none';
					gridEl.insertBefore( clone, directAppender || null );
					clones.push( clone );
				}

				// ── Slide navigation (infinite loop) ─────────────────────────
				let idx     = 0;
				let looping = false;

				function slideTo( n ) {
					if ( looping ) return;

					if ( n < 0 ) {
						// Backwards wrap: jump to end of real slides silently, then animate back one.
						gridEl.style.transition = 'none';
						idx = slides.length;
						gridEl.style.transform = `translateX(-${ idx * ( slideWidth + gap ) }px)`;
						void gridEl.offsetWidth; // force reflow
						gridEl.style.transition = 'transform 400ms ease';
						idx = slides.length - 1;
						gridEl.style.transform = `translateX(-${ idx * ( slideWidth + gap ) }px)`;
						return;
					}

					idx = n;
					gridEl.style.transform = `translateX(-${ idx * ( slideWidth + gap ) }px)`;

					// After animating into the trailing clones zone, silently snap back to real position.
					if ( idx >= slides.length ) {
						looping = true;
						setTimeout( () => {
							gridEl.style.transition = 'none';
							idx = idx % slides.length;
							gridEl.style.transform = `translateX(-${ idx * ( slideWidth + gap ) }px)`;
							void gridEl.offsetWidth;
							gridEl.style.transition = 'transform 400ms ease';
							looping = false;
						}, 420 );
					}
				}


				// ── Arrows ───────────────────────────────────────────────────
				const btnColor = frblButtonColor   || '#fff';
				const btnBg    = frblButtonBgColor || 'rgba(0,0,0,0.45)';

				[ 'prev', 'next' ].forEach( ( dir ) => {
					const btn   = editorDoc.createElement( 'button' );
					btn.type    = 'button';
					btn.className = `frbl-editor-arrow frbl-editor-arrow-${ dir }`;
					btn.setAttribute( 'aria-label', dir === 'prev' ? 'Previous slide' : 'Next slide' );
					btn.style.backgroundColor = btnBg;
					const d = dir === 'prev' ? 'M6 1L1 6L6 11' : 'M1 11L6 6L1 1';
					btn.innerHTML = `<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="${ d }" stroke="${ btnColor }" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
					btn.addEventListener( 'click', ( e ) => {
						e.stopPropagation();
						slideTo( dir === 'prev' ? idx - 1 : idx + 1 );
					} );
					wrapper.appendChild( btn );
				} );

				stateRef.current = { originalParent, wrapper, gridEl, clones };
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
