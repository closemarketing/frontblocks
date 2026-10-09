import { test, expect } from './fixtures';

/**
 * The carousel option attaches to a native core/group block with a grid
 * layout via the render_block_core/group filter (Carousel.php) — no
 * GenerateBlocks dependency needed, unlike most of its other supported
 * block types (GenerateBlocks Grid/Element, core/query).
 */
const CAROUSEL_GROUP = `<!-- wp:group {"layout":{"type":"grid"},"frblGridOption":"carousel","frblItemsToView":3,"frblAutoplay":4} -->
<div class="wp-block-group">
<!-- wp:paragraph --><p>Slide one</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Slide two</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Slide three</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->`;

const PLAIN_GRID_GROUP = `<!-- wp:group {"layout":{"type":"grid"}} -->
<div class="wp-block-group">
<!-- wp:paragraph --><p>Item one</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Item two</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->`;

test.describe('Carousel', () => {
	test('a core/group grid block with the carousel option renders carousel markup', async ({ page, wpApi }) => {
		const created = await wpApi.createPage({
			title: 'FrontBlocks E2E — Carousel',
			content: CAROUSEL_GROUP,
		});

		try {
			await page.goto(created.link);

			const carousel = page.locator('.frontblocks-carousel');
			await expect(carousel).toBeVisible();
			await expect(carousel).toHaveAttribute('data-type', 'carousel');
			await expect(carousel).toHaveAttribute('data-view', '3');
			await expect(carousel).toHaveAttribute('data-autoplay', '4000');
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});

	test('a plain grid group without the carousel option is left untouched', async ({ page, wpApi }) => {
		const created = await wpApi.createPage({
			title: 'FrontBlocks E2E — Plain Grid',
			content: PLAIN_GRID_GROUP,
		});

		try {
			await page.goto(created.link);
			await expect(page.locator('.frontblocks-carousel')).toHaveCount(0);
			await expect(page.getByText('Item one')).toBeVisible();
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});

	test('navigation arrows move between slides', async ({ page, wpApi }) => {
		// Autoplay off, a 1-item viewport, so clicking "next" deterministically
		// changes which slide is visible instead of racing a timer.
		const content = `<!-- wp:group {"layout":{"type":"grid"},"frblGridOption":"carousel","frblItemsToView":1} -->
<div class="wp-block-group">
<!-- wp:paragraph --><p>First slide text</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Second slide text</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->`;
		const created = await wpApi.createPage({ title: 'FrontBlocks E2E — Carousel Arrows', content });

		try {
			await page.goto(created.link);
			await expect(page.locator('.frontblocks-carousel')).toBeVisible();

			// Glide.js mounts its arrows/track around the block (they are the
			// carousel element's ancestors/siblings, not descendants of it).
			const nextArrow = page.locator('.glide__arrow--right').first();
			await expect(nextArrow).toBeVisible({ timeout: 10_000 });
			await nextArrow.click();

			// The carousel re-orders which slide is active; a concrete assertion
			// on glide.js's own active-slide class rather than guessing timing.
			await expect(page.locator('.glide__slide--active:not(.glide__slide--clone)')).toContainText('Second slide text');
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});
});
