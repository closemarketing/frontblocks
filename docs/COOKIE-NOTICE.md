# Cookie Notice migration

Cookie consent has moved entirely out of FrontBlocks into the dedicated free
[FrontConsent plugin](https://wordpress.org/plugins/frontconsent/). This was a
hard cutover, not a transition: FrontBlocks no longer ships a cookie consent
banner, AJAX consent endpoints, or tracking integrations of its own. All sites
should install and configure FrontConsent directly.

## Existing FrontBlocks sites

The legacy Cookie Notice module (banner, consent-gated Google Tag Manager/GA4
and other tracking integrations) has been removed from FrontBlocks. Sites that
had it enabled will show no cookie banner at all until FrontConsent is
installed — this is intentional, to push migration forward.

**Appearance → FrontBlocks → Cookie Notice** is now a static promo panel: it
explains the move and offers a one-click install/activation link for
FrontConsent. Installing and activating FrontConsent automatically:

- Copies the existing Cookie Notice settings and acceptance statistics.
- Converts retired dedicated Google Tag Manager and GA4 IDs into its shared
  tracking integrations.

After installing, manage the banner in **Settings → FrontConsent**. See the
[FrontConsent plugin page](https://wordpress.org/plugins/frontconsent/) for
current configuration and integration documentation.
