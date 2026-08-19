const { test, expect } = require('@playwright/test');
const { setCookieNoticeSettings, resetVisitorState } = require('./helpers');

test.describe('Cookie Notice — basic accept/reject flow', () => {
	test.beforeEach(async ({ page }) => {
		setCookieNoticeSettings({ cookie_notice_layout: 'bar' });
		await resetVisitorState(page);
	});

	test('shows the banner to a first-time visitor', async ({ page }) => {
		await page.goto('/');

		const banner = page.locator('#frbl-cookie-notice');
		await expect(banner).toBeVisible();
		await expect(banner.getByRole('button', { name: 'Accept' })).toBeVisible();
		await expect(banner.getByRole('button', { name: 'Reject' })).toBeVisible();
	});

	test('Accept sets the consent cookie and hides the banner', async ({ page }) => {
		await page.goto('/');

		await page.locator('#frbl-cookie-notice').getByRole('button', { name: 'Accept' }).click();

		await expect(page.locator('#frbl-cookie-notice')).toBeHidden();

		const cookies = await page.context().cookies();
		const consent = cookies.find((c) => c.name === 'frbl_cookie_consent');
		expect(consent?.value).toBe('accepted');
	});

	test('Reject sets the consent cookie and hides the banner', async ({ page }) => {
		await page.goto('/');

		await page.locator('#frbl-cookie-notice').getByRole('button', { name: 'Reject' }).click();

		await expect(page.locator('#frbl-cookie-notice')).toBeHidden();

		const cookies = await page.context().cookies();
		const consent = cookies.find((c) => c.name === 'frbl_cookie_consent');
		expect(consent?.value).toBe('rejected');
	});

	test('a returning visitor with a decision cookie never sees the banner', async ({ page }) => {
		await page.goto('/');
		await page.locator('#frbl-cookie-notice').getByRole('button', { name: 'Accept' }).click();
		await expect(page.locator('#frbl-cookie-notice')).toBeHidden();

		// Reload as the same "visitor" (cookie persists) and load a second page.
		await page.reload();
		const banner = page.locator('#frbl-cookie-notice');
		// Element exists in the cache-neutral HTML but must be hidden immediately.
		await expect(banner).toBeHidden();
	});

	test('rapid repeated clicks on Accept only register one decision', async ({ page }) => {
		await page.goto('/');

		const acceptBtn = page.locator('#frbl-cookie-notice').getByRole('button', { name: 'Accept' });

		// Fire three clicks back to back, before the 300ms hide animation
		// removes the banner from the DOM.
		await Promise.all([acceptBtn.click(), acceptBtn.click(), acceptBtn.click()]);

		const consentEntries = await page.evaluate(() =>
			(window.dataLayer || []).filter((entry) => entry[0] === 'consent')
		);

		// Exactly one 'default' (page load) + one 'update' (the decision) — not
		// three updates from three processed clicks.
		expect(consentEntries.filter((e) => e[1] === 'update')).toHaveLength(1);
	});
});
