import { test as base, expect, type Page } from '@playwright/test';

/**
 * Shared E2E fixtures.
 *
 * The WordPress Playground server started by playwright.config.ts (or
 * pointed at via FRBL_E2E_BASE_URL) is launched with `--login`, which
 * authenticates the very first HTTP request any browser context makes as
 * an administrator (it responds with the WordPress auth cookies on that
 * first request, exactly like a real login would). Each Playwright test
 * gets a fresh browser context, so each test gets its own fresh
 * auto-login — no explicit sign-in step is needed in specs.
 */

export interface WpApi {
	/** The site's current REST API nonce, valid for this test's session. */
	nonce: string;
	/** Creates a published page with the given block markup and returns its id/link. */
	createPage: (options: { title: string; content: string }) => Promise<{ id: number; link: string }>;
	/** Creates a published post with the given block markup and returns its id/link. */
	createPost: (options: { title: string; content: string }) => Promise<{ id: number; link: string }>;
	/** Permanently deletes a page or post created with createPage()/createPost(). */
	deleteContent: (type: 'pages' | 'posts', id: number) => Promise<void>;
}

async function getRestNonce(page: Page): Promise<string> {
	// post-new.php is a reliable place to find the REST nonce: it always loads
	// wp-api-fetch (the block editor needs it), unlike most other admin screens.
	await page.goto('/wp-admin/post-new.php?post_type=page');
	const html = await page.content();
	const match = html.match(/createNonceMiddleware\(\s*"([a-f0-9]+)"\s*\)/);

	if (!match) {
		throw new Error(
			'Could not find the REST API nonce on the block editor page — is the admin session logged in? ' +
				'Check that the WordPress Playground server was started with --login.'
		);
	}

	return match[1];
}

export const test = base.extend<{ wpApi: WpApi }>({
	wpApi: async ({ page }, use) => {
		const nonce = await getRestNonce(page);
		const headers = { 'X-WP-Nonce': nonce };

		const createContent = async (type: 'pages' | 'posts', { title, content }: { title: string; content: string }) => {
			const response = await page.request.post(`/wp-json/wp/v2/${type}`, {
				headers,
				data: { title, content, status: 'publish' },
			});

			if (!response.ok()) {
				throw new Error(`Failed to create ${type.slice(0, -1)} "${title}" (${response.status()}): ${await response.text()}`);
			}

			const body = await response.json();
			return { id: body.id as number, link: body.link as string };
		};

		const wpApi: WpApi = {
			nonce,
			createPage: (options) => createContent('pages', options),
			createPost: (options) => createContent('posts', options),
			deleteContent: async (type, id) => {
				await page.request.delete(`/wp-json/wp/v2/${type}/${id}`, {
					headers,
					data: { force: true },
				});
			},
		};

		await use(wpApi);
	},
});

export { expect };
