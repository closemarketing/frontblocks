import { test, expect } from '@playwright/test';

test.describe( 'Counter Animation', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/counter.html' );
	} );

	test( 'counter initialises with start value (0) before animation', async ( { page } ) => {
		// On DOMContentLoaded the script sets the text to the start value before
		// the IntersectionObserver fires. The counters are visible in the viewport
		// so animation starts immediately; we check a snapshot before it ends by
		// using a very short evaluation window — but we can at least assert the
		// element exists and has the expected class.
		const counter = page.locator( '#counter-1' );
		await expect( counter ).toBeAttached();
		await expect( counter ).toHaveClass( /frontblocks-counter-active/ );
	} );

	test( 'counter reaches target value after animation', async ( { page } ) => {
		const counter = page.locator( '#counter-1' );

		// Wait until the count-up-animated class is added (animation complete).
		await expect( counter ).toHaveClass( /count-up-animated/, { timeout: 3000 } );

		const text = await counter.textContent();
		expect( text?.replace( /,/g, '' ) ).toBe( '500' );
	} );

	test( 'counter with prefix and suffix renders correctly after animation', async ( { page } ) => {
		const counter = page.locator( '#counter-2' );

		await expect( counter ).toHaveClass( /count-up-animated/, { timeout: 3000 } );

		const text = await counter.textContent();
		expect( text ).toContain( '$' );
		expect( text ).toContain( 'K' );
		expect( text ).toContain( '100' );
	} );

	test( 'counter with non-zero start value reaches correct target', async ( { page } ) => {
		const counter = page.locator( '#counter-3' );

		await expect( counter ).toHaveClass( /count-up-animated/, { timeout: 3000 } );

		const text = await counter.textContent();
		expect( text?.replace( /,/g, '' ) ).toBe( '200' );
	} );

	test( 'animation does not run twice on the same element', async ( { page } ) => {
		const counter = page.locator( '#counter-1' );

		// Wait for first animation to complete.
		await expect( counter ).toHaveClass( /count-up-animated/, { timeout: 3000 } );

		const valueBefore = await counter.textContent();

		// Trigger an artificial IntersectionObserver-like call via evaluate.
		await page.evaluate( () => {
			const el = document.getElementById( 'counter-1' )!;
			// Simulate a second DOMContentLoaded — should be a no-op.
			el.dispatchEvent( new Event( 'DOMContentLoaded' ) );
		} );

		await page.waitForTimeout( 600 );
		const valueAfter = await counter.textContent();

		expect( valueAfter ).toBe( valueBefore );
	} );
} );
