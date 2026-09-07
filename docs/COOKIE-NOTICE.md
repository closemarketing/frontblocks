# FrontBlocks: Cookie Notice

**Plugin:** FrontBlocks for Gutenberg/GeneratePress
**Feature:** Cookie consent banner + consent-gated Google Tag Manager / GA4
**Since:** 1.5.0

---

## Overview

Cookie Notice is an optional module that shows a lightweight, configurable cookie
consent banner on the frontend. Visitors can **Accept** or **Reject**; Google Tag
Manager and/or GA4 are only ever loaded after a visitor accepts. The module also
keeps a simple aggregate acceptance-rate stat (accepted vs. rejected counts) in
the settings page.

Enable it from **Appearance → FrontBlocks → Cookie Notice**.

## Settings

| Field | Description |
| --- | --- |
| Message | The banner copy. Falls back to a default English sentence when left empty. |
| Accept / Reject button label | Text for the two decision buttons. |
| Cookie policy page | Optional page picker (dropdown of the site's own pages), shown next to the message as a "Learn more" link resolved to that page's permalink. The banner is suppressed on that exact page so visitors can read the policy before deciding. |
| Layout | `Full-width bar` (floating bottom bar), `Boxed panel` (bottom-left/bottom-right), or `Centered popup` (modal with a dimmed backdrop). |
| Position | Only shown for the boxed panel layout — bottom-left or bottom-right. |
| Accent color | Used for the Accept button and the policy link. A contrasting text color is computed automatically so the button and link stay legible even with a very light accent. |
| Background color | The notice panel's background. A contrasting text color is computed automatically for the message and Reject button, the same way the accent color's own contrast is handled. |
| Corner rounding | `None`, `Slightly rounded`, or `Very rounded` — applies to the notice panel across all three layouts. |
| Cookie expiration (days) | How long the visitor's decision is remembered. |
| Add a tracking ID or code integration | A single add-only field for every supported tool, including Google Tag Manager and GA4: paste a bare `GTM-XXXXXXX` / `G-XXXXXXXXXX` id, GTM/gtag's own install snippet, or the full `<script>` snippet from Clientify, Brevo, or ChatGPT Ads (which also accepts a standalone Pixel ID). On save, the integration is auto-detected and only the ID/code it needs is stored; the raw pasted snippet is discarded. An unrecognized snippet is rejected with an admin notice pointing to [close.technology/contacto](https://close.technology/contacto). If empty, GTM/GA4 are never loaded. |

## How consent gating works

- The banner markup and its assets are always enqueued and rendered — this keeps the
  HTML identical for every visitor of a given URL, so a full-page cache (a very
  common setup on agency-managed GeneratePress sites) can safely cache and reuse
  the page without ever mixing up one visitor's consent state with another's.
- All consent-specific behavior happens client-side: the banner is printed already
  invisible/off-screen (a `frbl-cookie-notice--init` class, reset by a `<noscript>`
  rule for browsers without JS), and `frontblocks-cookie-notice.js` reveals it only
  after checking the `frbl_cookie_consent` cookie — an already-decided visitor's
  banner simply stays hidden and is removed from the flow, instead of flashing into
  view first and being hidden a moment later. A visitor who still needs to decide
  sees it slide/fade in instead of appearing instantly: the bar slides up from the
  bottom, the boxed panel slides in from its anchored edge, and the popup fades in
  after a short delay so it doesn't feel jarring on page load.
- **Reject**: only sets the cookie. Nothing is ever requested from Google.
- **Accept**: the cookie is set immediately, and the browser asks a small
  read-only AJAX endpoint (`frbl_get_cookie_notice_config`) for the configured
  GTM/GA4 IDs (and the detected Clientify/Brevo integration records, if any). The endpoint
  only returns them when the *browser's own* cookie says `accepted` — nothing is
  ever present in the page's HTML source before that. The frontend script then
  creates the actual `<script>` tags dynamically, so a first-time visitor sees
  tracking start without a page reload, and a returning visitor who already
  accepted gets it automatically on every page load. This endpoint is deliberately
  unauthenticated: it's read-only, never touches the aggregate counters, and only
  ever echoes back non-secret ids that are already public once the corresponding
  script loads — a nonce here would have to live in the cache-neutral HTML this
  module renders and would go stale on any page a cache keeps around longer than
  a nonce's lifetime, breaking tracking until the cache refreshes.

## Additional tracking integrations

GTM and GA4 accept a bare ID (`GTM-XXXXXXX` / `G-XXXXXXXXXX`) or their own
official install snippet. Clientify and Brevo hand out a full `<script>`
snippet to paste. ChatGPT Ads accepts either its full `oaiq` snippet or a
Pixel ID on its own. Clientify alone has two differently-shaped snippets
depending on which of its products the client is on (its current Analytics
Plus pixel, or the classic Analytics tracker.js + `ana(...)` calls). The
settings screen provides a single add-only "Add a tracking ID or code
integration" field and a list of saved integrations, covering every
supported tool including GTM/GA4. Whatever is pasted is matched against the
supported patterns in `CookieNotice::detect_tracking_snippet()`: only the
provider type (`gtm`, `ga4`, or one of the other supported types) and the one
required ID/code are stored, as a `{type, id}` record; the raw pasted markup
is always discarded. Entries can be removed individually from that list.

A snippet that doesn't match any supported pattern is rejected (an admin
notice points to [close.technology/contacto](https://close.technology/contacto)
for adding support for it) rather than silently doing nothing.

Sites upgrading from an older version that used the separate "Google Tag
Manager ID" / "GA4 Measurement ID" fields have their existing values migrated
automatically, once, into `gtm` / `ga4` records in this same list — no
re-entry needed. See `Settings::migrate_legacy_gtm_ga4_tracking_ids()`.

Companion plugins can extend this field without adding provider-specific code
to FrontBlocks: register their types with
`frbl_cookie_notice_tracking_types`, detect their snippets with
`frbl_cookie_notice_detect_tracking_snippet`, and handle their injection with
`window.frblCookieNoticeInjectIntegration`. Unhandled records are queued until
that browser callback is available. `gtm` and `ga4` records are the exception:
they're dispatched through their own dedicated response keys (consumed
directly by the built-in GTM/gtag loading code) rather than through that
generic callback.

## Compatibility with other analytics/ads plugins (Google Consent Mode)

GTM/GA4 loading (see above) only gates what *this module* loads. It has no
effect on tracking scripts injected by other plugins — most notably
[Google Site Kit](https://wordpress.org/plugins/google-site-kit/), which
enqueues its own `gtag.js` independently and would otherwise start collecting
data before the visitor has made a choice.

To close that gap, the module implements
[Google Consent Mode v2](https://developers.google.com/tag-platform/security/guides/consent):

- On every page, before any other script runs (`wp_head`, priority `1`), it
  pushes a `consent` → `default` command to `window.dataLayer` — `denied` for
  `ad_storage`, `ad_user_data`, `ad_personalization`, and `analytics_storage`,
  or `granted` if the visitor already has a `frbl_cookie_consent=accepted`
  cookie from a previous visit.
- When a visitor accepts or rejects, the frontend script pushes a matching
  `consent` → `update` command.

Because Consent Mode works through `window.dataLayer` rather than through
whichever plugin's script happens to process it, this holds back **any**
Consent Mode-aware tag — Site Kit's own gtag snippet, a GTM container pasted
manually into the theme, another plugin's analytics script — as long as this
module's default fires first, which the priority-`1` hook guarantees for
anything hooked at the normal `wp_head` priority (`10`) or later.

No extra plugin (e.g. WP Consent API) or Site Kit configuration is required —
this is plain Consent Mode, read directly off `window.dataLayer`. When Site Kit
is configured to place a tag, FrontBlocks hides the matching `gtm`/`ga4` entry
from the tracking-integrations list and does not load its saved ID, preventing
duplicate Google tags. Site Kit can remain active without affecting FrontBlocks
when the matching module is unconfigured or its snippet placement is disabled.

## GTM4WP and cache compatibility

When [Google Tag Manager for WordPress (GTM4WP)](https://wordpress.org/plugins/duracelltomi-google-tag-manager/)
is active and configured with the same container ID, FrontBlocks shows an admin
warning if GTM4WP is still outputting its container code. Disable GTM4WP's
container-code injection and leave its data layer enabled; FrontBlocks then
loads the shared container only after the visitor accepts.

Cookie Notice settings are part of the cached frontend output. When they change,
FrontBlocks clears the WP Rocket cache automatically if WP Rocket is active. For
other full-page cache plugins, purge the cache after saving or use the action
below to add a cache integration.

## Acceptance-rate stat

Every decision also POSTs to a nonce-protected logging endpoint
(`frbl_log_cookie_consent`) that increments one of two WordPress options
(`frontblocks_cookie_notice_accepted_count` / `..._rejected_count`) directly in
the database with an atomic `UPDATE ... SET value = value + 1`, so concurrent
visitors deciding at the same time don't lose each other's count. This is a
best-effort, lightweight aggregate stat rather than precise per-visitor
metering — the module can't embed a per-visitor deduplication token in its
cache-neutral HTML without reintroducing the same caching problem the module is
designed to avoid, so a replayed request could in principle nudge the count.
Logged-in users with the `manage_options` capability (site admins) are excluded,
so testing the banner while logged in doesn't skew the stats. The resulting
acceptance rate is shown directly in the Cookie Notice settings section.

## `frblCookieConsent` event

A `frblCookieConsent` custom event is dispatched on `document` whenever a visitor
accepts or rejects, so other scripts can react without touching this module:

```js
document.addEventListener('frblCookieConsent', function (event) {
	console.log(event.detail.consent); // 'accepted' or 'rejected'
});
```

## Extension hooks

This module fires a few hooks so an add-on can extend the banner without
forking it — used by FrontBlocks PRO's Advanced Cookie Management to add a
"Customize" (per-category) option:

- `frbl_cookie_notice_before_actions( array $options )` — action, fires inside
  the actions row, right before the Reject/Accept buttons.
- `frbl_cookie_notice_after_banner( array $options )` — action, fires right
  after the banner markup, still inside the same `wp_footer` output.
- `frbl_cookie_notice_default_accept_label( string $default )` /
  `frbl_cookie_notice_default_reject_label( string $default )` — filters,
  used only when the admin left the corresponding label field empty.
- `frbl_cookie_notice_settings_updated( array $old_options, array $new_options )`
  — action, fires after Cookie Notice settings that affect frontend output change.
  Cache integrations can use it to purge their cached pages.
- `window.frblCookieNoticeConsentModeState()` — client-side JS, not a PHP
  hook: if defined, `render_consent_mode_default()` calls it and uses its
  return value (an object with the four Consent Mode keys) instead of the
  binary accept/reject default, so an add-on can send granular per-category
  signals. Must return `null` when it has no valid decision yet (falls back
  to the binary default), and must be defined *before* this method's own
  script runs (an earlier `wp_head` priority). Deliberately client-side, not
  a PHP filter reading a cookie server-side: this method's printed HTML is
  identical for every visitor of a URL, which a PHP-side per-visitor value
  would break under a full-page cache.
- `frbl_cookie_notice_integration_category( string $category, string $type )`
  — filter, lets an add-on override which consent category
  (`CookieNotice::get_integration_default_category()`) an integration falls
  under by default: `'analytics'` for `gtm`/`ga4`, `'marketing'` for every
  Clientify/Brevo variant. This plugin's own gating stays a plain
  accept/reject binary regardless of category — it's FrontBlocks PRO's
  Advanced Cookie Management that reads this to decide which category gate
  an integration needs when the visitor granted only some categories.
- `frbl_cookie_notice_has_tracking_consent( bool $has_tracking_consent )`
  — filter, lets an add-on with category-based consent make the read-only
  configuration endpoint available after at least one tracking category is
  accepted. The add-on must pass its allowed categories to
  `window.frblCookieNoticeInject()`.
- `frbl_cookie_notice_allowed_tracking_categories()` — filter, lets that
  add-on return its allowed category map for the configuration response. The
  map is passed to both automatic injection paths so a partial choice cannot
  load integrations from a withheld category.

## Out of scope

- Per-visitor logging, timestamps, or a dashboard of responses over time.
- Auto-scanning the site's existing scripts/cookies.

Category-based consent (Analytics/Marketing toggles as separate switches) is
no longer out of scope for the plugin as a whole — see FrontBlocks PRO's
Advanced Cookie Management, built on top of the hooks above.
