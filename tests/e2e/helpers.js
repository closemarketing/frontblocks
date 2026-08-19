const { execSync } = require('child_process');

/**
 * Run a wp-cli command inside the wp-env "tests" environment.
 *
 * @param {string} command wp-cli subcommand, without the leading "wp".
 * @return {string} Trimmed stdout.
 */
function wpCli(command) {
	return execSync(`npx wp-env run tests-cli wp ${command}`, {
		encoding: 'utf8',
	}).trim();
}

/**
 * Replace the frontblocks_settings option with exactly the given values
 * (merged over the module's own defaults), so each test starts from a known,
 * explicit configuration instead of whatever a previous test left behind.
 *
 * @param {Record<string, unknown>} settings
 */
function setCookieNoticeSettings(settings) {
	const payload = JSON.stringify({
		enable_cookie_notice: true,
		...settings,
	});

	wpCli(
		`option update frontblocks_settings '${payload.replace(/'/g, "'\\''")}' --format=json`
	);
}

/**
 * Look up a page's ID by title, creating it first if it doesn't exist yet.
 * Used to get a real page ID for the "cookie policy page" picker tests.
 *
 * @param {string} title
 * @return {number}
 */
function ensurePage(title) {
	const existing = wpCli(
		`post list --post_type=page --title=${JSON.stringify(title)} --field=ID --format=csv`
	);

	if (existing) {
		return parseInt(existing.split('\n')[0], 10);
	}

	const id = wpCli(
		`post create --post_type=page --post_title=${JSON.stringify(title)} --post_status=publish --porcelain`
	);

	return parseInt(id, 10);
}

/**
 * Clear every cookie/localStorage/dataLayer state a previous test may have
 * left behind, so each test starts as a fresh, undecided visitor.
 *
 * @param {import('@playwright/test').Page} page
 */
async function resetVisitorState(page) {
	await page.context().clearCookies();
}

module.exports = {
	wpCli,
	setCookieNoticeSettings,
	ensurePage,
	resetVisitorState,
};
