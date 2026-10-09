import { test, expect } from './fixtures';

// (The markup has no newline after the opening comment, like the editor's own
// serializer output: the plugin's regex anchors on the block starting with its tag.)
// Any block carrying `frblAnimation` gets Animate.css classes
// (Animations::add_animation_classes_to_blocks); the script only starts the
// animation once the element enters the viewport.
const ANIMATED = `<!-- wp:paragraph {"frblAnimation":"fadeInUp","frblAnimationDuration":1} --><p id="animated-target">I animate in.</p><!-- /wp:paragraph -->`;

test.describe('Animations', () => {
	test('elements animate in when scrolled into view', async ({ page, wpApi }) => {
		const spacer = '<!-- wp:spacer {"height":"1800px"} --><div style="height:1800px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->';
		const created = await wpApi.createPage({ title: 'FrontBlocks E2E — Animations', content: `${spacer}\n${ANIMATED}` });

		try {
			await page.goto(created.link);

			const target = page.getByText('I animate in.');
			await expect(target).toBeAttached();

			// Classes are applied server-side, but the animation stays held back
			// (not running) until the element enters the viewport.
			await expect(target).toHaveClass(/animate__animated/);
			await expect(target).toHaveClass(/animate__fadeInUp/);
			expect(await target.evaluate((el) => (el as HTMLElement).style.animationPlayState)).not.toBe('running');

			await target.scrollIntoViewIfNeeded();
			await expect
				.poll(() => target.evaluate((el) => (el as HTMLElement).style.animationPlayState), { timeout: 10_000 })
				.toBe('running');
			await expect(target).toBeVisible();
		} finally {
			await wpApi.deleteContent('pages', created.id);
		}
	});
});
