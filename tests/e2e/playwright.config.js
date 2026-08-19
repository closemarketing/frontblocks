const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright config for the Cookie Notice e2e suite.
 *
 * Runs against a wp-env instance (see .wp-env.json, env.tests.port). Start it
 * with `npm run wp-env start` before `npm run test:e2e`, or let the
 * global-setup.js script do it automatically when WP_ENV_AUTOSTART=1 (as CI does).
 */
module.exports = defineConfig({
	testDir: __dirname,
	fullyParallel: false, // Tests share one wp-env instance and mutate the same option.
	workers: 1,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI ? [['list'], ['github']] : 'list',
	globalSetup: require.resolve('./global-setup.js'),
	use: {
		baseURL: 'http://localhost:8890',
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],
});
