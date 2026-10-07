import { test, expect } from './fixtures';

// Counter works on any block whose rendered text holds the target number
// (Counter::render_block_counter reads `isCounterActive`/`finalNumber`).
const COUNTER = `<!-- wp:paragraph {"className":"stat","isCounterActive":true,"finalNumber":"250","animationDuration":800} -->
<p id="counter-target" class="stat">250</p>
<!-- /wp:paragraph -->`;

test.describe('Counter', () => {
	test('counts up from 0 to the target value when scrolled into view', async ({ page, wpApi }) => {
		const spacer = '<!-- wp:spacer {"height":"1500px"} --><div style="height:1500px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->';
		const created = await wpApi.createPage({ title: 'FrontBlocks E2E — Counter', content: `${spacer}\n${COUNTER}` });

		try {
			await page.goto(created.link);

			const counter = page.locator('#counter-target');
			await expect(counter).toHaveAttribute('data-counter-target', '250');

			// Below the fold: the animation must not have run yet.
			await expect(counter).not.toHaveClass(/count-up-animated/);

			await counter.scrollIntoViewIfNeeded();
			await expect(counter).toHaveClass(/count-up-animated/, { timeout: 10_000 });
			await expect(counter).toHaveText('250');
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});
});
