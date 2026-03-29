/**
 * End-to-end tests for the realistic WordPress frontend page fixture.
 *
 * The fixture at tests/e2e/fixtures/wordpress-frontend-page.html combines all
 * FrontBlocks components on a single page, mirroring what a GeneratePress site
 * with FrontBlocks active would render on the frontend.
 */

import { test, expect } from '@playwright/test';

test.describe( 'WordPress frontend page – full integration', () => {
	test.beforeEach( async ( { page } ) => {
		await page.setViewportSize( { width: 1280, height: 800 } );
		await page.goto( '/tests/e2e/fixtures/wordpress-frontend-page.html' );
		await page.waitForLoadState( 'load' );
		// Allow rAF-based initialisations (marquee, carousel) to settle.
		await page.waitForTimeout( 400 );
	} );

	// ── Page structure ────────────────────────────────────────────

	test( 'page title is correct', async ( { page } ) => {
		await expect( page ).toHaveTitle( /Sample Page/ );
	} );

	test( 'site header is visible', async ( { page } ) => {
		await expect( page.locator( '.site-header' ) ).toBeVisible();
	} );

	test( 'article content is present', async ( { page } ) => {
		await expect( page.locator( '#post-1' ) ).toBeAttached();
	} );

	// ── Reading progress bar ──────────────────────────────────────

	test( 'reading progress bar is present', async ( { page } ) => {
		await expect( page.locator( '.frbl-reading-progress-bar' ) ).toBeAttached();
	} );

	test( 'reading progress body class is added', async ( { page } ) => {
		await expect( page.locator( 'body' ) ).toHaveClass( /frbl-reading-progress-active/ );
	} );

	test( 'reading progress updates on scroll', async ( { page } ) => {
		await page.evaluate( () => window.scrollTo( 0, 800 ) );
		await page.waitForTimeout( 150 );

		const fill = page.locator( '.frbl-reading-progress-fill' );
		const height = await fill.evaluate( ( el ) => parseFloat( ( el as HTMLElement ).style.height ) );
		expect( height ).toBeGreaterThan( 0 );
	} );

	// ── Animated counters ─────────────────────────────────────────

	test( 'all three counters are present', async ( { page } ) => {
		await expect( page.locator( '#counter-clients' ) ).toBeAttached();
		await expect( page.locator( '#counter-projects' ) ).toBeAttached();
		await expect( page.locator( '#counter-years' ) ).toBeAttached();
	} );

	test( 'counters reach their target values', async ( { page } ) => {
		await expect( page.locator( '#counter-clients' ) ).toHaveClass( /count-up-animated/, { timeout: 4000 } );
		await expect( page.locator( '#counter-projects' ) ).toHaveClass( /count-up-animated/, { timeout: 4000 } );
		await expect( page.locator( '#counter-years' ) ).toHaveClass( /count-up-animated/, { timeout: 4000 } );
	} );

	test( 'counter-clients ends with "+" suffix', async ( { page } ) => {
		await expect( page.locator( '#counter-clients' ) ).toHaveClass( /count-up-animated/, { timeout: 4000 } );
		const text = await page.locator( '#counter-clients' ).textContent();
		expect( text ).toContain( '+' );
		expect( text?.replace( /,|\+/g, '' ) ).toBe( '1200' );
	} );

	test( 'counter-years ends with " yrs" suffix', async ( { page } ) => {
		await expect( page.locator( '#counter-years' ) ).toHaveClass( /count-up-animated/, { timeout: 4000 } );
		const text = await page.locator( '#counter-years' ).textContent();
		expect( text ).toContain( 'yrs' );
	} );

	// ── Carousel ──────────────────────────────────────────────────

	test( 'featured articles carousel is mounted', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-featured' ).locator( 'xpath=../..' );
		await expect( wrapper ).toHaveClass( /glide/ );
	} );

	test( 'featured articles carousel has 5 slides', async ( { page } ) => {
		await expect( page.locator( '#carousel-featured .glide__slide' ) ).toHaveCount( 5 );
	} );

	// ── Sticky column ─────────────────────────────────────────────

	test( 'sticky layout wrapper is present', async ( { page } ) => {
		await expect( page.locator( '#sticky-layout' ) ).toBeAttached();
	} );

	test( 'sidebar becomes sticky after scrolling to it', async ( { page } ) => {
		// Scroll to the sticky section (it starts after a long counters + carousel section).
		await page.locator( '#section-sticky' ).scrollIntoViewIfNeeded();
		await page.evaluate( () => window.scrollBy( 0, 200 ) );
		await page.waitForTimeout( 150 );

		const sidebar = page.locator( '#sidebar-col' );
		await expect( sidebar ).toHaveClass( /sticky-active/ );
	} );

	// ── Accordion (FAQ) ───────────────────────────────────────────

	test( 'closed FAQ items start hidden', async ( { page } ) => {
		await expect( page.locator( '#faq-1 .gb-accordion__content' ) ).not.toBeVisible();
		await expect( page.locator( '#faq-2 .gb-accordion__content' ) ).not.toBeVisible();
	} );

	test( 'pre-opened FAQ item is visible', async ( { page } ) => {
		await expect( page.locator( '#faq-3 .gb-accordion__content' ) ).toBeVisible();
	} );

	test( 'clicking FAQ toggle opens it', async ( { page } ) => {
		await page.locator( '#faq-1 .gb-accordion__toggle' ).click();
		await expect( page.locator( '#faq-1 .gb-accordion__content' ) ).toBeVisible();
	} );

	test( 'clicking FAQ toggle sets aria-expanded to true', async ( { page } ) => {
		await page.locator( '#faq-2 .gb-accordion__toggle' ).click();
		await expect( page.locator( '#faq-2 .gb-accordion__toggle' ) ).toHaveAttribute( 'aria-expanded', 'true' );
	} );

	// ── Marquee ───────────────────────────────────────────────────

	test( 'hero marquee is initialised', async ( { page } ) => {
		await expect( page.locator( '#marquee-hero' ) ).toHaveAttribute( 'data-marquee-initialized', 'true' );
	} );

	test( 'hero marquee has animation running', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-hero .gb-marquee-wrapper' );
		const playState = await wrapper.evaluate(
			( el ) => window.getComputedStyle( el ).animationPlayState
		);
		expect( playState ).toBe( 'running' );
	} );

	// ── Back button ───────────────────────────────────────────────

	test( 'back button is present in DOM', async ( { page } ) => {
		await expect( page.locator( '#frbl-back-button' ) ).toBeAttached();
	} );

	test( 'back button has correct aria-label', async ( { page } ) => {
		await expect( page.locator( '#frbl-back-button' ) ).toHaveAttribute( 'aria-label', 'Go back to previous page' );
	} );

	test( 'back button is hidden on first visit (no prior navigation)', async ( { page } ) => {
		await expect( page.locator( '#frbl-back-button' ) ).not.toHaveClass( /frbl-show/ );
	} );

	// ── Accessibility ─────────────────────────────────────────────

	test( 'reading progress bar has proper ARIA role', async ( { page } ) => {
		await expect( page.locator( '.frbl-reading-progress-bar' ) ).toHaveAttribute( 'role', 'progressbar' );
	} );

	test( 'reading progress bar has accessible aria-label', async ( { page } ) => {
		await expect( page.locator( '.frbl-reading-progress-bar' ) ).toHaveAttribute( 'aria-label', 'Reading progress' );
	} );

	test( 'carousel arrows have aria-labels', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-featured' ).locator( 'xpath=../..' );
		await expect( wrapper.locator( '.glide__arrow--left' ) ).toHaveAttribute( 'aria-label', 'Previous slide' );
		await expect( wrapper.locator( '.glide__arrow--right' ) ).toHaveAttribute( 'aria-label', 'Next slide' );
	} );
} );
