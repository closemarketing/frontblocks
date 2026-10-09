import { test, expect } from './fixtures';

/**
 * Sticky Column attaches to a native core/columns block (see
 * StickyColumn::add_sticky_attributes_to_columns_block) — no GenerateBlocks needed.
 */
const filler = Array.from({ length: 30 }, (_, i) => `<!-- wp:paragraph --><p>Filler paragraph ${i + 1} to make the second column much taller than the first.</p><!-- /wp:paragraph -->`).join('\n');

const STICKY_COLUMNS = `<!-- wp:columns {"frblStickyEnabled":true,"frblStickyOffset":20,"frblStickyColumnIndex":0} -->
<div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph --><p id="sticky-target-text">Sticky side column</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column">${filler}</div><!-- /wp:column -->
</div>
<!-- /wp:columns -->`;

test.describe('Sticky Column', () => {
	test('the sticky column stays in view while the page scrolls', async ({ page, wpApi }) => {
		const created = await wpApi.createPage({ title: 'FrontBlocks E2E — Sticky Column', content: STICKY_COLUMNS });

		try {
			await page.goto(created.link);

			const wrapper = page.locator('.frontblocks-sticky-wrapper[data-sticky-enabled="true"]');
			await expect(wrapper).toHaveAttribute('data-sticky-offset', '20');

			const target = page.locator('#sticky-target-text');
			await expect(target).toBeInViewport();

			await page.evaluate(() => window.scrollTo(0, 800));
			await page.waitForTimeout(300);

			// Scrolled well past its natural position, yet still on screen.
			expect(await page.evaluate(() => window.scrollY)).toBeGreaterThan(300);
			await expect(target).toBeInViewport();
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});

	test('columns without the sticky option are untouched', async ({ page, wpApi }) => {
		const created = await wpApi.createPage({
			title: 'FrontBlocks E2E — Plain Columns',
			content: `<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph --><p>A</p><!-- /wp:paragraph --></div><!-- /wp:column --></div><!-- /wp:columns -->`,
		});

		try {
			await page.goto(created.link);
			await expect(page.locator('.frontblocks-sticky-wrapper')).toHaveCount(0);
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});
});
