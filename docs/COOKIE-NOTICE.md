# Cookie Notice migration

Cookie consent configuration has moved from FrontBlocks to the dedicated free
[FrontConsent plugin](https://wordpress.org/plugins/frontconsent/). New sites
should install and configure FrontConsent directly.

## Existing FrontBlocks sites

The legacy Cookie Notice module remains active during the transition, so an
existing banner and its consent-gated integrations keep working. Its settings
are no longer shown in **Appearance → FrontBlocks → Cookie Notice**.

Install and activate FrontConsent from that tab or from the FrontBlocks admin
notice. On its first run, FrontConsent automatically:

- Copies the existing Cookie Notice settings and acceptance statistics.
- Converts retired dedicated Google Tag Manager and GA4 IDs into its shared
  tracking integrations.
- Disables the legacy FrontBlocks banner, preventing duplicate notices.

After migration, manage the banner in **Settings → FrontConsent**. See the
[FrontConsent plugin page](https://wordpress.org/plugins/frontconsent/) for
current configuration and integration documentation.
