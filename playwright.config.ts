import { defineConfig, devices } from '@playwright/test';

/**
 * E2E configuration for FrontBlocks.
 *
 * The WordPress environment is a local WordPress Playground server (no
 * Docker required): `wp-playground-cli server --auto-mount=<this plugin>`
 * mounts this checkout as wp-content/plugins/frontblocks, auto-activates it,
 * and `--login` auto-authenticates the very first request any browser
 * context makes as an administrator (see tests/e2e/helpers/wp.ts for how
 * specs create content via the REST API using that same session).
 *
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
	testDir: './tests/e2e',
	fullyParallel: true,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	// A single worker locally: every spec shares one WordPress Playground
	// server and can create/trash posts, which isn't safe to parallelize
	// against the same site. CI gets the same default via PLAYWRIGHT_WORKERS
	// below, kept at 1 for the same reason.
	workers: 1,
	reporter: process.env.CI ? [['html', { open: 'never' }], ['github']] : 'list',
	use: {
		baseURL: process.env.FRBL_E2E_BASE_URL || 'http://127.0.0.1:9413',
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],
	// Boots the WordPress Playground server itself, unless FRBL_E2E_BASE_URL
	// points at an already-running one (e.g. reusing a server you started by
	// hand while iterating on a spec).
	webServer: process.env.FRBL_E2E_BASE_URL
		? undefined
		: {
				command: `${process.execPath} ./node_modules/.bin/wp-playground-cli server --auto-mount=${process.cwd()} --login --port=9413 --wordpress-install-mode=download-and-install`,
				// Not wp-login.php: the --login flag makes every unauthenticated
				// request to it 302-redirect (it auto-logs the visitor in), and
				// Playwright's webServer health check only accepts a 2xx response.
				url: 'http://127.0.0.1:9413/wp-includes/js/wp-emoji-release.min.js',
				reuseExistingServer: !process.env.CI,
				timeout: 180_000,
				stdout: 'pipe',
				stderr: 'pipe',
			},
});
