const { test, expect } = require('@playwright/test');
const { setCookieNoticeSettings, resetVisitorState } = require('./helpers');

/**
 * Google Consent Mode v2 signals — see includes/Frontend/CookieNotice.php
 * render_consent_mode_default() and the JS updateConsentMode() function.
 * This is what actually holds back third-party analytics/ads scripts (Google
 * Site Kit, a manually pasted GTM container, etc.) that read window.dataLayer,
 * independent of this plugin's own GTM/GA4 fields.
 */
test.describe('Cookie Notice — Google Consent Mode v2', () => {
	test.beforeEach(async ({ page }) => {
		setCookieNoticeSettings({ cookie_notice_layout: 'bar' });
		await resetVisitorState(page);
	});

	test('defaults every signal to denied for an undecided visitor', async ({ page }) => {
		await page.goto('/');

		const consentDefault = await page.evaluate(() =>
			(window.dataLayer || []).find((entry) => entry[0] === 'consent' && entry[1] === 'default')
		);

		expect(consentDefault).toBeTruthy();
		expect(consentDefault[2]).toMatchObject({
			ad_storage: 'denied',
			ad_user_data: 'denied',
			ad_personalization: 'denied',
			analytics_storage: 'denied',
		});
	});

	test('the default signal is queued before any other inline script tag', async ({ page }) => {
		await page.goto('/');

		const html = await page.content();
		const consentIndex = html.indexOf("gtag( 'consent', 'default'");
		const headCloseIndex = html.indexOf('</head>');

		expect(consentIndex).toBeGreaterThan(-1);
		expect(consentIndex).toBeLessThan(headCloseIndex);

		// It must also come before any other <script> tag with real content —
		// this is what actually blocks third-party tags reading Consent Mode.
		const firstScriptIndex = html.indexOf('<script');
		expect(consentIndex - firstScriptIndex).toBeLessThanOrEqual(0);
	});

	test('Accept pushes an update with every signal granted', async ({ page }) => {
		await page.goto('/');
		await page.locator('#frbl-cookie-notice').getByRole('button', { name: 'Accept' }).click();

		const consentUpdate = await page.evaluate(() =>
			(window.dataLayer || []).find((entry) => entry[0] === 'consent' && entry[1] === 'update')
		);

		expect(consentUpdate[2]).toMatchObject({
			ad_storage: 'granted',
			ad_user_data: 'granted',
			ad_personalization: 'granted',
			analytics_storage: 'granted',
		});
	});

	test('Reject pushes an update with every signal denied', async ({ page }) => {
		await page.goto('/');
		await page.locator('#frbl-cookie-notice').getByRole('button', { name: 'Reject' }).click();

		const consentUpdate = await page.evaluate(() =>
			(window.dataLayer || []).find((entry) => entry[0] === 'consent' && entry[1] === 'update')
		);

		expect(consentUpdate[2]).toMatchObject({
			ad_storage: 'denied',
			ad_user_data: 'denied',
			ad_personalization: 'denied',
			analytics_storage: 'denied',
		});
	});

	test('a returning accepted visitor gets a granted default on the next page load', async ({
		page,
	}) => {
		await page.goto('/');
		await page.locator('#frbl-cookie-notice').getByRole('button', { name: 'Accept' }).click();

		await page.goto('/'); // Fresh navigation, same cookie jar.

		const consentDefault = await page.evaluate(() =>
			(window.dataLayer || []).find((entry) => entry[0] === 'consent' && entry[1] === 'default')
		);

		expect(consentDefault[2].analytics_storage).toBe('granted');
	});
});
