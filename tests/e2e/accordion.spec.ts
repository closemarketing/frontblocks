import path from 'path';
import { test, expect } from './fixtures';

/**
 * assets/accordion/frontblocks-accordion.js patches GenerateBlocks accordion
 * markup (`.gb-accordion__*`). The free plugin never enqueues that file
 * itself (nothing in includes/ references it), so this spec injects the
 * shipped script into a page that carries the same markup via a core/html
 * block — which tests the real file's open/close behavior without needing
 * GenerateBlocks installed.
 */
const ACCORDION_HTML = `<!-- wp:html -->
<div class="gb-accordion">
  <div class="gb-accordion__item" id="acc-item-1">
    <button class="gb-accordion__toggle" aria-expanded="false">First question</button>
    <div class="gb-accordion__content"><p>First answer</p></div>
  </div>
  <div class="gb-accordion__item gb-accordion__item-open" id="acc-item-2">
    <button class="gb-accordion__toggle" aria-expanded="true">Second question</button>
    <div class="gb-accordion__content"><p>Second answer</p></div>
  </div>
</div>
<!-- /wp:html -->`;

test.describe('Accordion', () => {
	test('opens and closes panels, starting from the markup\'s own open/closed state', async ({ page, wpApi }) => {
		const created = await wpApi.createPage({ title: 'FrontBlocks E2E — Accordion', content: ACCORDION_HTML });

		try {
			await page.goto(created.link);
			await page.addScriptTag({ path: path.resolve(process.cwd(), 'assets/accordion/frontblocks-accordion.js') });

			const first = page.locator('#acc-item-1');
			const second = page.locator('#acc-item-2');

			// Initial state comes from the markup: the first item is closed, the second open.
			await expect(first.getByText('First answer')).toBeHidden();
			await expect(second.getByText('Second answer')).toBeVisible();

			await first.locator('.gb-accordion__toggle').click();
			await expect(first.getByText('First answer')).toBeVisible();
			await expect(first.locator('.gb-accordion__toggle')).toHaveAttribute('aria-expanded', 'true');

			await first.locator('.gb-accordion__toggle').click();
			await expect(first.getByText('First answer')).toBeHidden();
			await expect(first.locator('.gb-accordion__toggle')).toHaveAttribute('aria-expanded', 'false');

			await second.locator('.gb-accordion__toggle').click();
			await expect(second.getByText('Second answer')).toBeHidden();
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});
});
