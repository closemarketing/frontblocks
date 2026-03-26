import { test, expect } from '@playwright/test';

test.describe( 'Back Button', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/back-button.html' );
	} );

	test( 'back button element is present in DOM', async ( { page } ) => {
		await expect( page.locator( '#frbl-back-button' ) ).toBeAttached();
	} );

	test( 'back button is hidden on first page visit', async ( { page } ) => {
		// navCount is 1 on first visit → shouldShowButton() returns false.
		const button = page.locator( '#frbl-back-button' );
		await expect( button ).not.toHaveClass( /frbl-show/ );
	} );

	test( 'back button has no frbl-show class before scrolling (first visit)', async ( { page } ) => {
		const button = page.locator( '#frbl-back-button' );
		await page.evaluate( () => window.scrollTo( 0, 200 ) );
		await page.waitForTimeout( 200 );

		// Still hidden because it is the first page visited (navCount ≤ 1).
		await expect( button ).not.toHaveClass( /frbl-show/ );
	} );

	test( 'back button shows frbl-show class after navigation and scroll', async ( { page } ) => {
		// Simulate a second navigation by manually setting sessionStorage so
		// the script thinks the user already visited another page.
		await page.evaluate( () => {
			sessionStorage.setItem( 'frbl_nav_count', '2' );
			sessionStorage.setItem( 'frbl_entry_url', 'http://localhost:3737/other-page.html' );
		} );

		// Reload so the script reads the updated sessionStorage.
		await page.reload();

		// Scroll past the 100px threshold.
		await page.evaluate( () => window.scrollTo( 0, 200 ) );
		await page.waitForTimeout( 600 ); // wait for the 500ms show delay + rAF.

		const button = page.locator( '#frbl-back-button' );
		await expect( button ).toHaveClass( /frbl-show/ );
	} );

	test( 'back button hides when scrolling back to top', async ( { page } ) => {
		// Simulate second visit.
		await page.evaluate( () => {
			sessionStorage.setItem( 'frbl_nav_count', '2' );
			sessionStorage.setItem( 'frbl_entry_url', 'http://localhost:3737/other-page.html' );
		} );
		await page.reload();

		await page.evaluate( () => window.scrollTo( 0, 200 ) );
		await page.waitForTimeout( 600 );

		// Now scroll back to top.
		await page.evaluate( () => window.scrollTo( 0, 0 ) );
		await page.waitForTimeout( 200 );

		const button = page.locator( '#frbl-back-button' );
		await expect( button ).not.toHaveClass( /frbl-show/ );
	} );
} );
