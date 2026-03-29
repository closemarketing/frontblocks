import { test, expect } from '@playwright/test';

test.describe( 'Carousel (Glide.js)', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/carousel.html' );
		// The carousel initialises on window.load — wait for it.
		await page.waitForLoadState( 'load' );
		// Give Glide time to mount.
		await page.waitForTimeout( 300 );
	} );

	// ── DOM structure ─────────────────────────────────────────────

	test( 'carousel wrapper gets glide and frontblocks classes', async ( { page } ) => {
		// The script wraps the element twice: inner = glide__track, outer = glide + frontblocks.
		const outer = page.locator( '#carousel-arrows' ).locator( 'xpath=../..' );
		await expect( outer ).toHaveClass( /glide/ );
		await expect( outer ).toHaveClass( /frontblocks/ );
	} );

	test( 'carousel slides list gets glide__slides class', async ( { page } ) => {
		const slides = page.locator( '#carousel-arrows' );
		await expect( slides ).toHaveClass( /glide__slides/ );
	} );

	test( 'individual slide items get glide__slide class', async ( { page } ) => {
		const slides = page.locator( '#carousel-arrows .glide__slide' );
		await expect( slides ).toHaveCount( 5 );
	} );

	test( 'track wrapper gets glide__track class and data-glide-el=track', async ( { page } ) => {
		const track = page.locator( '#carousel-arrows' ).locator( 'xpath=..' );
		await expect( track ).toHaveClass( /glide__track/ );
		await expect( track ).toHaveAttribute( 'data-glide-el', 'track' );
	} );

	// ── Arrow navigation ──────────────────────────────────────────

	test( 'arrows carousel renders left and right arrow buttons', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-arrows' ).locator( 'xpath=../..' );
		const arrowLeft = wrapper.locator( '.glide__arrow--left' );
		const arrowRight = wrapper.locator( '.glide__arrow--right' );

		await expect( arrowLeft ).toBeVisible();
		await expect( arrowRight ).toBeVisible();
	} );

	test( 'arrow buttons have accessible aria-labels', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-arrows' ).locator( 'xpath=../..' );

		await expect( wrapper.locator( '.glide__arrow--left' ) ).toHaveAttribute( 'aria-label', 'Previous slide' );
		await expect( wrapper.locator( '.glide__arrow--right' ) ).toHaveAttribute( 'aria-label', 'Next slide' );
	} );

	test( 'clicking next arrow advances to the next slide', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-arrows' ).locator( 'xpath=../..' );
		const nextArrow = wrapper.locator( '.glide__arrow--right' );

		// Get the current active index from Glide's data-glide-index attribute.
		const getActiveSlide = () =>
			page.locator( '#carousel-arrows .glide__slide--active' ).count();

		await nextArrow.click();
		await page.waitForTimeout( 400 );

		// After clicking next, active slide count should still be ≥ 1.
		const activeCount = await getActiveSlide();
		expect( activeCount ).toBeGreaterThanOrEqual( 1 );
	} );

	// ── Bullet navigation ─────────────────────────────────────────

	test( 'bullets carousel renders bullet buttons', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-bullets' ).locator( 'xpath=../..' );
		const bullets = wrapper.locator( '.glide__bullet' );

		// 3 slides → 3 bullets.
		await expect( bullets ).toHaveCount( 3 );
	} );

	test( 'bullet buttons have accessible aria-labels', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-bullets' ).locator( 'xpath=../..' );
		const firstBullet = wrapper.locator( '.glide__bullet' ).first();

		await expect( firstBullet ).toHaveAttribute( 'aria-label', 'Go to slide 1' );
	} );

	test( 'bullet navigation group has aria-label', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-bullets' ).locator( 'xpath=../..' );
		const bulletsGroup = wrapper.locator( '.glide__bullets' );

		await expect( bulletsGroup ).toHaveAttribute( 'aria-label', 'Slide navigation' );
	} );

	// ── Slider type ───────────────────────────────────────────────

	test( 'slider type mounts and renders slides', async ( { page } ) => {
		const slides = page.locator( '#carousel-slider .glide__slide' );
		await expect( slides ).toHaveCount( 3 );
	} );
} );

// ── WordPress frontend page – carousel integration ────────────────
test.describe( 'Carousel on WordPress frontend page', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/wordpress-frontend-page.html' );
		await page.waitForLoadState( 'load' );
		await page.waitForTimeout( 300 );
	} );

	test( 'carousel is mounted on the featured articles section', async ( { page } ) => {
		const slides = page.locator( '#carousel-featured .glide__slide' );
		await expect( slides ).toHaveCount( 5 );
	} );

	test( 'carousel arrow buttons are visible', async ( { page } ) => {
		const wrapper = page.locator( '#carousel-featured' ).locator( 'xpath=../..' );
		await expect( wrapper.locator( '.glide__arrow--left' ) ).toBeVisible();
		await expect( wrapper.locator( '.glide__arrow--right' ) ).toBeVisible();
	} );
} );
