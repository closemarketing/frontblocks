import { test, expect } from '@playwright/test';

test.describe( 'Marquee (infinite scroll headline)', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/marquee.html' );
		// Wait for rAF-based init to complete.
		await page.waitForTimeout( 300 );
	} );

	// ── Initialisation ────────────────────────────────────────────

	test( 'marquee element is marked as initialized', async ( { page } ) => {
		const el = page.locator( '#marquee-medium' );
		await expect( el ).toHaveAttribute( 'data-marquee-initialized', 'true' );
	} );

	test( 'marquee wrapper is created inside the element', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-medium .gb-marquee-wrapper' );
		await expect( wrapper ).toBeAttached();
	} );

	test( 'at least two content copies are created for seamless loop', async ( { page } ) => {
		const copies = page.locator( '#marquee-medium .gb-marquee-copy' );
		const count = await copies.count();
		expect( count ).toBeGreaterThanOrEqual( 2 );
	} );

	test( 'each copy contains the original text', async ( { page } ) => {
		const firstCopy = page.locator( '#marquee-medium .gb-marquee-copy' ).first();
		const text = await firstCopy.textContent();
		expect( text ).toContain( 'FrontBlocks' );
	} );

	// ── CSS animation ─────────────────────────────────────────────

	test( 'wrapper has an animation applied after init', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-medium .gb-marquee-wrapper' );
		const animationName = await wrapper.evaluate(
			( el ) => window.getComputedStyle( el ).animationName
		);
		// The script generates a unique keyframe name starting with 'marquee-scroll-'.
		expect( animationName ).toMatch( /marquee-scroll-/ );
	} );

	test( 'wrapper animation iteration count is infinite', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-medium .gb-marquee-wrapper' );
		const iterationCount = await wrapper.evaluate(
			( el ) => window.getComputedStyle( el ).animationIterationCount
		);
		expect( iterationCount ).toBe( 'infinite' );
	} );

	test( 'animation is running (not paused) by default', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-medium .gb-marquee-wrapper' );
		const playState = await wrapper.evaluate(
			( el ) => window.getComputedStyle( el ).animationPlayState
		);
		expect( playState ).toBe( 'running' );
	} );

	// ── Speed presets ─────────────────────────────────────────────

	test( 'medium speed uses 20s animation duration', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-medium .gb-marquee-wrapper' );
		const speed = await wrapper.getAttribute( 'data-marquee-speed' );
		expect( Number( speed ) ).toBe( 20 );
	} );

	test( 'fast speed uses 10s animation duration', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-fast .gb-marquee-wrapper' );
		await expect( page.locator( '#marquee-fast' ) ).toHaveAttribute( 'data-marquee-initialized', 'true' );
		const speed = await wrapper.getAttribute( 'data-marquee-speed' );
		expect( Number( speed ) ).toBe( 10 );
	} );

	test( 'slow speed uses 40s animation duration', async ( { page } ) => {
		const wrapper = page.locator( '#marquee-slow .gb-marquee-wrapper' );
		await expect( page.locator( '#marquee-slow' ) ).toHaveAttribute( 'data-marquee-initialized', 'true' );
		const speed = await wrapper.getAttribute( 'data-marquee-speed' );
		expect( Number( speed ) ).toBe( 40 );
	} );

	// ── Dynamic initialisation ────────────────────────────────────

	test( 'dynamically added marquee element is initialised via MutationObserver', async ( { page } ) => {
		await page.evaluate( () => {
			const el = document.createElement( 'div' );
			el.id = 'marquee-dynamic';
			el.className = 'gb-element gb-marquee-infinite-scroll';
			el.setAttribute( 'data-marquee-speed', 'medium' );
			el.style.overflow = 'hidden';
			el.style.width = '400px';
			el.innerHTML = '<span class="gb-headline-text">Dynamic marquee content ★</span>';
			document.body.appendChild( el );
		} );

		// Wait for MutationObserver + rAF callbacks.
		await page.waitForTimeout( 500 );

		const el = page.locator( '#marquee-dynamic' );
		await expect( el ).toHaveAttribute( 'data-marquee-initialized', 'true' );
	} );
} );

// ── WordPress frontend page – marquee integration ──────────────────
test.describe( 'Marquee on WordPress frontend page', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/wordpress-frontend-page.html' );
		await page.waitForTimeout( 300 );
	} );

	test( 'hero marquee is initialized on the frontend page', async ( { page } ) => {
		await expect( page.locator( '#marquee-hero' ) ).toHaveAttribute( 'data-marquee-initialized', 'true' );
	} );

	test( 'hero marquee has scroll copies', async ( { page } ) => {
		const copies = page.locator( '#marquee-hero .gb-marquee-copy' );
		await expect( copies ).not.toHaveCount( 0 );
	} );
} );
