import { test, expect } from '@playwright/test';

test.describe( 'Reading Progress Bar', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/reading-progress.html' );
	} );

	test( 'progress bar is present in the DOM', async ( { page } ) => {
		await expect( page.locator( '.frbl-reading-progress-bar' ) ).toBeAttached();
	} );

	test( 'progress fill starts at 0%', async ( { page } ) => {
		const fill = page.locator( '.frbl-reading-progress-fill' );
		const height = await fill.evaluate( ( el ) => ( el as HTMLElement ).style.height );
		expect( height ).toBe( '0%' );
	} );

	test( 'body gets frbl-reading-progress-active class on init', async ( { page } ) => {
		await expect( page.locator( 'body' ) ).toHaveClass( /frbl-reading-progress-active/ );
	} );

	test( 'progress bar aria-valuenow starts at 0', async ( { page } ) => {
		const bar = page.locator( '.frbl-reading-progress-bar' );
		await expect( bar ).toHaveAttribute( 'aria-valuenow', '0' );
	} );

	test( 'progress increases after scrolling down', async ( { page } ) => {
		// Scroll halfway down the tall article.
		await page.evaluate( () => window.scrollTo( 0, 1500 ) );

		// Wait for rAF throttle to fire.
		await page.waitForTimeout( 100 );

		const fill = page.locator( '.frbl-reading-progress-fill' );
		const height = await fill.evaluate( ( el ) => parseFloat( ( el as HTMLElement ).style.height ) );

		expect( height ).toBeGreaterThan( 0 );
	} );

	test( 'progress aria-valuenow updates on scroll', async ( { page } ) => {
		await page.evaluate( () => window.scrollTo( 0, 1500 ) );
		await page.waitForTimeout( 100 );

		const bar = page.locator( '.frbl-reading-progress-bar' );
		const ariaValue = await bar.getAttribute( 'aria-valuenow' );
		expect( Number( ariaValue ) ).toBeGreaterThan( 0 );
	} );

	test( 'progress does not exceed 100% at page bottom', async ( { page } ) => {
		await page.evaluate( () => window.scrollTo( 0, document.body.scrollHeight ) );
		await page.waitForTimeout( 100 );

		const fill = page.locator( '.frbl-reading-progress-fill' );
		const height = await fill.evaluate( ( el ) => parseFloat( ( el as HTMLElement ).style.height ) );
		expect( height ).toBeLessThanOrEqual( 100 );
	} );
} );
