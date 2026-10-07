import { test, expect } from './fixtures';
import { gotoSettingsTab, setFeatureToggle } from './helpers/settings';

// Long enough that the post is actually scrollable in a normal viewport —
// the bar only has something to measure once the article's height exceeds
// the window's.
const LONG_CONTENT = Array.from(
	{ length: 40 },
	(_, i) => `<!-- wp:paragraph --><p>Paragraph ${i + 1}. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p><!-- /wp:paragraph -->`
).join('\n');

test.describe('Reading Progress Bar', () => {
	test.beforeEach(async ({ page }) => {
		await gotoSettingsTab(page, 'optional');
		await setFeatureToggle(page, 'enable_reading_progress', true);
	});

	test.afterEach(async ({ page }) => {
		await gotoSettingsTab(page, 'optional');
		await setFeatureToggle(page, 'enable_reading_progress', false);
	});

	test('advances on scroll and reaches 100% at the bottom of the post', async ({ page, wpApi }) => {
		const post = await wpApi.createPost({
			title: 'FrontBlocks E2E — Reading Progress',
			content: LONG_CONTENT,
		});

		try {
			await page.goto(post.link);

			const bar = page.locator('.frbl-reading-progress-bar');
			await expect(bar).toBeAttached();
			await expect(bar).toHaveAttribute('aria-valuenow', '0');

			await page.mouse.wheel(0, 600);
			// The fill height updates inside a requestAnimationFrame callback.
			await expect
				.poll(async () => Number(await bar.getAttribute('aria-valuenow')))
				.toBeGreaterThan(0);

			await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
			await expect.poll(async () => Number(await bar.getAttribute('aria-valuenow'))).toBe(100);
		} finally {
			await wpApi.deleteContent('posts', post.id);
		}
	});

	test('is not rendered on a page (only singular posts)', async ({ page, wpApi }) => {
		const created = await wpApi.createPage({
			title: 'FrontBlocks E2E — Reading Progress on a Page',
			content: LONG_CONTENT,
		});

		try {
			await page.goto(created.link);
			await expect(page.locator('.frbl-reading-progress-bar')).toHaveCount(0);
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});

	test('is not rendered when disabled', async ({ page, wpApi }) => {
		await gotoSettingsTab(page, 'optional');
		await setFeatureToggle(page, 'enable_reading_progress', false);

		const post = await wpApi.createPost({
			title: 'FrontBlocks E2E — Reading Progress Disabled',
			content: LONG_CONTENT,
		});

		try {
			await page.goto(post.link);
			await expect(page.locator('.frbl-reading-progress-bar')).toHaveCount(0);
		} finally {
			await wpApi.deleteContent('posts', post.id);
		}
	});
});
