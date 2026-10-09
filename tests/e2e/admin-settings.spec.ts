import { test, expect } from './fixtures';
import { gotoSettingsTab, setFeatureToggle } from './helpers/settings';

test.describe('Admin settings page', () => {
	test('renders correctly', async ({ page }) => {
		await page.goto('/wp-admin/themes.php?page=frontblocks-settings');

		await expect(page.locator('.frbl-tabs[role="tablist"]')).toBeVisible();
		await expect(page.locator('.frbl-tab-btn[data-tab-target="blocks"]')).toBeVisible();
		await expect(page.locator('.frbl-tab-panel[data-tab-panel="blocks"]')).toBeVisible();
		await expect(page.locator('#frbl-settings-form')).toBeVisible();
	});

	// All four live in the "optional" tab's "Feature toggles" section
	// (frontblocks_section_features) — "Events" registers either a dedicated
	// CPT or reuses blog posts (see its own events_type field), but the
	// enable/disable toggle itself is just another entry in that same list.
	for (const id of ['enable_testimonials', 'enable_reading_progress', 'enable_back_button', 'enable_events']) {
		test(`"${id}" toggle can be enabled and disabled`, async ({ page }) => {
			await gotoSettingsTab(page, 'optional');

			await setFeatureToggle(page, id, true);
			await gotoSettingsTab(page, 'optional');
			await expect(page.locator(`#${id}`)).toBeChecked();

			await setFeatureToggle(page, id, false);
			await gotoSettingsTab(page, 'optional');
			await expect(page.locator(`#${id}`)).not.toBeChecked();
		});
	}

	test('settings persist after a page reload, not just after the save redirect', async ({ page }) => {
		await gotoSettingsTab(page, 'optional');
		await setFeatureToggle(page, 'enable_testimonials', true);

		await page.reload();
		await page.locator('.frbl-tab-btn[data-tab-target="optional"]').click();

		await expect(page.locator('#enable_testimonials')).toBeChecked();

		// Leave the setting as it was found, so this spec is safe to re-run.
		await setFeatureToggle(page, 'enable_testimonials', false);
	});
});
