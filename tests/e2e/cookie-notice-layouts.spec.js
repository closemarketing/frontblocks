const { test, expect } = require('@playwright/test');
const { setCookieNoticeSettings, ensurePage, resetVisitorState } = require('./helpers');

test.describe('Cookie Notice — layouts', () => {
	test.beforeEach(async ({ page }) => {
		await resetVisitorState(page);
	});

	test('full-width bar renders at the bottom, spanning the viewport width', async ({ page }) => {
		setCookieNoticeSettings({ cookie_notice_layout: 'bar' });
		await page.goto('/');

		const banner = page.locator('#frbl-cookie-notice');
		await expect(banner).toHaveClass(/frbl-cookie-notice--bar/);

		const box = await banner.boundingBox();
		const viewport = page.viewportSize();
		expect(box.width).toBeGreaterThan(viewport.width * 0.9);
	});

	test('boxed panel renders anchored to the configured corner', async ({ page }) => {
		setCookieNoticeSettings({
			cookie_notice_layout: 'box',
			cookie_notice_position: 'bottom-right',
		});
		await page.goto('/');

		const banner = page.locator('#frbl-cookie-notice');
		await expect(banner).toHaveClass(/frbl-cookie-notice--box/);
		await expect(banner).toHaveClass(/frbl-cookie-notice--right/);

		const box = await banner.boundingBox();
		const viewport = page.viewportSize();
		// A boxed panel, unlike the bar, must NOT span the full width.
		expect(box.width).toBeLessThan(viewport.width * 0.6);
	});

	test('centered popup shows a dimmed backdrop and traps focus', async ({ page }) => {
		setCookieNoticeSettings({ cookie_notice_layout: 'popup' });
		await page.goto('/');

		const banner = page.locator('#frbl-cookie-notice');
		await expect(banner).toHaveClass(/frbl-cookie-notice--popup/);
		await expect(banner).toHaveAttribute('role', 'dialog');
		await expect(banner).toHaveAttribute('aria-modal', 'true');

		// Accept button should be auto-focused for a modal layout.
		await expect(banner.getByRole('button', { name: 'Accept' })).toBeFocused();
	});

	test('the icon badge renders in every layout', async ({ page }) => {
		for (const layout of ['bar', 'box', 'popup']) {
			setCookieNoticeSettings({ cookie_notice_layout: layout });
			await resetVisitorState(page);
			await page.goto('/');

			await expect(
				page.locator('#frbl-cookie-notice .frbl-cookie-notice__icon svg')
			).toBeVisible();
		}
	});
});

test.describe('Cookie Notice — policy page suppression', () => {
	test.beforeEach(async ({ page }) => {
		await resetVisitorState(page);
	});

	test('the banner is hidden on the configured policy page', async ({ page }) => {
		const pageId = ensurePage('E2E Cookie Policy');
		setCookieNoticeSettings({
			cookie_notice_layout: 'bar',
			cookie_notice_policy_page_id: pageId,
		});

		await page.goto(`/?page_id=${pageId}`);
		await expect(page.locator('#frbl-cookie-notice')).toHaveCount(0);
	});

	test('the "Learn more" link resolves to the policy page permalink', async ({ page }) => {
		const pageId = ensurePage('E2E Cookie Policy');
		setCookieNoticeSettings({
			cookie_notice_layout: 'bar',
			cookie_notice_policy_page_id: pageId,
		});

		await page.goto('/');

		const link = page.locator('#frbl-cookie-notice .frbl-cookie-notice__link');
		await expect(link).toBeVisible();
		const href = await link.getAttribute('href');
		expect(href).toContain(`page_id=${pageId}`);
	});

	test('the banner still shows on every other page', async ({ page }) => {
		const pageId = ensurePage('E2E Cookie Policy');
		setCookieNoticeSettings({
			cookie_notice_layout: 'bar',
			cookie_notice_policy_page_id: pageId,
		});

		await page.goto('/');
		await expect(page.locator('#frbl-cookie-notice')).toBeVisible();
	});
});
