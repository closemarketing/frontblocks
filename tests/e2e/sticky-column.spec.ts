import { test, expect } from '@playwright/test';

test.describe( 'Sticky Column', () => {
	test.beforeEach( async ( { page } ) => {
		// Use a small viewport so we can scroll into the wrapper area.
		await page.setViewportSize( { width: 1024, height: 600 } );
		await page.goto( '/tests/e2e/fixtures/sticky-column.html' );
	} );

	test( 'sticky column wrapper is present in DOM', async ( { page } ) => {
		await expect( page.locator( '#sticky-wrapper' ) ).toBeAttached();
	} );

	test( 'sticky column does not have sticky-active class on page load', async ( { page } ) => {
		const col = page.locator( '#sticky-col' );
		await expect( col ).not.toHaveClass( /sticky-active/ );
	} );

	test( 'sticky column gets sticky-active class after scrolling into view', async ( { page } ) => {
		// Scroll past the wrapper top (it starts at y=800px).
		await page.evaluate( () => window.scrollTo( 0, 900 ) );
		await page.waitForTimeout( 100 );

		const col = page.locator( '#sticky-col' );
		await expect( col ).toHaveClass( /sticky-active/ );
	} );

	test( 'sticky column loses sticky-active class after scrolling back to top', async ( { page } ) => {
		// Scroll down to activate sticky.
		await page.evaluate( () => window.scrollTo( 0, 900 ) );
		await page.waitForTimeout( 100 );

		// Scroll back up above the wrapper.
		await page.evaluate( () => window.scrollTo( 0, 0 ) );
		await page.waitForTimeout( 100 );

		const col = page.locator( '#sticky-col' );
		await expect( col ).not.toHaveClass( /sticky-active/ );
	} );

	test( 'sticky column applies top offset to inner container', async ( { page } ) => {
		await page.evaluate( () => window.scrollTo( 0, 900 ) );
		await page.waitForTimeout( 100 );

		const container = page.locator( '#sticky-content' );
		const topStyle = await container.evaluate( ( el ) => ( el as HTMLElement ).style.top );

		// data-sticky-offset="20" → top should be "20px".
		expect( topStyle ).toBe( '20px' );
	} );
} );
