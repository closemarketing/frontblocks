import { test, expect } from './fixtures';
import { gotoSettingsTab, setFeatureToggle } from './helpers/settings';

test.describe('Back Button', () => {
	test.beforeEach(async ({ page }) => {
		await gotoSettingsTab(page, 'optional');
		await setFeatureToggle(page, 'enable_back_button', true);
	});

	test.afterEach(async ({ page }) => {
		await gotoSettingsTab(page, 'optional');
		await setFeatureToggle(page, 'enable_back_button', false);
	});

	test('navigates to the previous page on click', async ({ page, wpApi }) => {
		const pageOne = await wpApi.createPage({
			title: 'FrontBlocks E2E — Back Button Page One',
			content: '<!-- wp:paragraph --><p>Page one.</p><!-- /wp:paragraph -->',
		});
		const pageTwo = await wpApi.createPage({
			title: 'FrontBlocks E2E — Back Button Page Two',
			content: '<!-- wp:paragraph --><p>Page two.</p><!-- /wp:paragraph -->',
		});

		try {
			// The button only appears once there is somewhere to go back to: the
			// first page of a browsing session never shows it (see
			// frontblocks-back-button.js's shouldShowButton()).
			await page.goto(pageOne.link);
			await page.goto(pageTwo.link);

			const button = page.locator('#frbl-back-button');
			await expect(button).toBeAttached();

			// It also only becomes visible once scrolled past a threshold —
			// make the page tall enough to actually scroll, then scroll it.
			await page.evaluate(() => {
				const spacer = document.createElement('div');
				spacer.style.height = '2000px';
				document.body.appendChild(spacer);
			});
			await page.mouse.wheel(0, 500);
			await expect(button).toHaveClass(/frbl-show/);

			await button.click();
			await expect(page).toHaveURL(pageOne.link);
		} finally {
			await wpApi.deleteContent('pages', pageOne.id);
			await wpApi.deleteContent('pages', pageTwo.id);
		}
	});

	test('is not rendered when disabled', async ({ page, wpApi }) => {
		await gotoSettingsTab(page, 'optional');
		await setFeatureToggle(page, 'enable_back_button', false);

		const created = await wpApi.createPage({
			title: 'FrontBlocks E2E — Back Button Disabled',
			content: '<!-- wp:paragraph --><p>Hello.</p><!-- /wp:paragraph -->',
		});

		try {
			await page.goto(created.link);
			await expect(page.locator('#frbl-back-button')).toHaveCount(0);
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});
});
