import { defineConfig, devices } from '@playwright/test';
import path from 'path';

/**
 * Playwright E2E configuration for FrontBlocks plugin.
 *
 * Tests run against standalone HTML fixtures that load the plugin's actual
 * JavaScript files directly — no WordPress install required.
 */
export default defineConfig({
	testDir: './tests/e2e',
	fullyParallel: true,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: process.env.CI ? 2 : undefined,
	reporter: [
		['html', { outputFolder: 'tests/e2e/playwright-report', open: 'never' }],
		['list'],
	],

	use: {
		// Serve fixtures from the repo root so tests can reference assets with
		// relative paths like /assets/accordion/frontblocks-accordion.js
		baseURL: 'http://localhost:3737',
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},

	// Static file server — serves the whole repo (fixtures + assets).
	webServer: {
		command: 'npx serve . --listen 3737 --no-clipboard',
		url: 'http://localhost:3737',
		reuseExistingServer: !process.env.CI,
		timeout: 30_000,
	},

	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
		{
			name: 'firefox',
			use: { ...devices['Desktop Firefox'] },
		},
	],
});
