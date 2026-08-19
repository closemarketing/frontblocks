const { execSync } = require('child_process');

/**
 * One-time setup for the whole e2e run: starts wp-env (if not already
 * running) and activates the plugin. Per-test option changes (layout, accent
 * color, policy page, etc.) live in each spec's own beforeEach instead, via
 * the wpCli() helper in helpers.js — this only handles what every test needs.
 */
module.exports = async function globalSetup() {
	if (process.env.WP_ENV_AUTOSTART === '1') {
		execSync('npx wp-env start', { stdio: 'inherit' });
	}

	execSync('npx wp-env run tests-cli wp plugin activate frontblocks', {
		stdio: 'inherit',
	});

	// Pin plain permalinks so every spec can rely on the exact same
	// "?page_id=N" URL shape instead of guessing at pretty-permalink slugs.
	execSync('npx wp-env run tests-cli wp rewrite structure ""', {
		stdio: 'inherit',
	});
};
