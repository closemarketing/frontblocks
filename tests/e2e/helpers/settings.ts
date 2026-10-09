import type { Page } from '@playwright/test';

/**
 * Opens the FrontBlocks settings page and switches to the given tab.
 *
 * @param page    Playwright page.
 * @param tabId   Value of a tab button's `data-tab-target` attribute, e.g. 'optional' or 'cpt'.
 */
export async function gotoSettingsTab(page: Page, tabId: string): Promise<void> {
	await page.goto('/wp-admin/themes.php?page=frontblocks-settings');
	await page.locator(`.frbl-tab-btn[data-tab-target="${tabId}"]`).click();
}

/**
 * Sets a settings-page checkbox (by its `id`, e.g. 'enable_back_button') to the
 * given state and saves the form, then reloads to confirm the value actually
 * persisted — the real round trip through `options.php`, not just the DOM.
 *
 * Assumes the matching tab is already open (see gotoSettingsTab()): the
 * checkbox lives inside a `hidden` sibling panel otherwise and Playwright's
 * actionability checks will time out waiting for it to become visible.
 *
 * @param page    Playwright page, on the settings screen with the right tab open.
 * @param id      The checkbox's `id` attribute.
 * @param enabled Desired checked state.
 */
export async function setFeatureToggle(page: Page, id: string, enabled: boolean): Promise<void> {
	const checkbox = page.locator(`#${id}`);

	if ((await checkbox.isChecked()) === enabled) {
		return;
	}

	// The visual switch is a sibling <span> painted via CSS on the label (see
	// field_enable_*() in Settings.php) — the <input> itself is visually
	// hidden, so click its label instead of relying on Playwright's visibility
	// check for the checkbox element.
	await page.locator(`label.frbl-toggle:has(#${id})`).click();
	await page.locator('#frbl-settings-form button[type="submit"]').click();
	await page.waitForLoadState('load');
}
