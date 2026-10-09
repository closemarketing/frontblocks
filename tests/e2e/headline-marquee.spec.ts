import { test, expect } from './fixtures';

// The marquee effect applies to native headings/paragraphs carrying the
// `gb-marquee-infinite-scroll` class (Headline::apply_marquee_to_native_block).
const MARQUEE = `<!-- wp:heading {"className":"gb-marquee-infinite-scroll","frblMarqueeSpeed":"fast"} -->
<h2 class="wp-block-heading gb-marquee-infinite-scroll">Scrolling headline text</h2>
<!-- /wp:heading -->`;

test.describe('Headline Marquee', () => {
	test('wraps the text and scrolls it continuously', async ({ page, wpApi }) => {
		const created = await wpApi.createPage({ title: 'FrontBlocks E2E — Marquee', content: MARQUEE });

		try {
			await page.goto(created.link);

			const wrapper = page.locator('.gb-marquee-infinite-scroll .gb-marquee-wrapper');
			await expect(wrapper).toBeAttached();
			// The content is duplicated so the loop never shows a gap.
			expect(await page.locator('.gb-marquee-copy').count()).toBeGreaterThanOrEqual(2);

			const animationName = await wrapper.evaluate((el) => getComputedStyle(el).animationName);
			expect(animationName).not.toBe('none');
			expect(await wrapper.evaluate((el) => getComputedStyle(el).animationPlayState)).toBe('running');
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});
});
