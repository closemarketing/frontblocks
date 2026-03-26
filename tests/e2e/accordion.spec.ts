import { test, expect } from '@playwright/test';

test.describe( 'Accordion', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/tests/e2e/fixtures/accordion.html' );
	} );

	test( 'closed item starts hidden', async ( { page } ) => {
		const content = page.locator( '#item-1 .gb-accordion__content' );
		await expect( content ).not.toBeVisible();
	} );

	test( 'open item starts visible', async ( { page } ) => {
		const content = page.locator( '#item-2 .gb-accordion__content' );
		await expect( content ).toBeVisible();
	} );

	test( 'clicking toggle opens a closed item', async ( { page } ) => {
		const toggle = page.locator( '#item-1 .gb-accordion__toggle' );
		const content = page.locator( '#item-1 .gb-accordion__content' );

		await toggle.click();
		await expect( content ).toBeVisible();
	} );

	test( 'clicking toggle closes an open item', async ( { page } ) => {
		const toggle = page.locator( '#item-2 .gb-accordion__toggle' );
		const content = page.locator( '#item-2 .gb-accordion__content' );

		await toggle.click();
		await expect( content ).not.toBeVisible();
	} );

	test( 'toggle sets aria-expanded correctly when opening', async ( { page } ) => {
		const toggle = page.locator( '#item-1 .gb-accordion__toggle' );

		await toggle.click();
		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'true' );
	} );

	test( 'toggle sets aria-expanded correctly when closing', async ( { page } ) => {
		const toggle = page.locator( '#item-2 .gb-accordion__toggle' );

		await toggle.click();
		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'false' );
	} );

	test( 'toggle can open and close the same item multiple times', async ( { page } ) => {
		const toggle = page.locator( '#item-3 .gb-accordion__toggle' );
		const content = page.locator( '#item-3 .gb-accordion__content' );

		// Open.
		await toggle.click();
		await expect( content ).toBeVisible();

		// Close.
		await toggle.click();
		await expect( content ).not.toBeVisible();

		// Open again.
		await toggle.click();
		await expect( content ).toBeVisible();
	} );

	test( 'multiple items can be open simultaneously', async ( { page } ) => {
		const toggle1 = page.locator( '#item-1 .gb-accordion__toggle' );
		const toggle3 = page.locator( '#item-3 .gb-accordion__toggle' );

		await toggle1.click();
		await toggle3.click();

		await expect( page.locator( '#item-1 .gb-accordion__content' ) ).toBeVisible();
		await expect( page.locator( '#item-2 .gb-accordion__content' ) ).toBeVisible();
		await expect( page.locator( '#item-3 .gb-accordion__content' ) ).toBeVisible();
	} );
} );
