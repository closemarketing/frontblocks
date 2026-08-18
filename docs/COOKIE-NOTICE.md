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
| Cookie policy URL | Optional link shown next to the message ("Learn more"). When this URL points to a page on the same site, the banner is suppressed on that exact page so visitors can read the policy before deciding. |
| Layout | `Full-width bar` (bottom bar), `Boxed panel` (bottom-left/bottom-right), or `Centered popup` (modal with a dimmed backdrop). |
| Position | Only shown for the boxed panel layout — bottom-left or bottom-right. |
| Accent color | Used for the Accept button and the policy link. A contrasting text color is computed automatically so the button and link stay legible even with a very light accent. |
| Cookie expiration (days) | How long the visitor's decision is remembered. |
| Google Tag Manager ID | `GTM-XXXXXXX`. Left empty, GTM is never loaded. |
| GA4 Measurement ID | `G-XXXXXXXXXX`. Left empty, GA4 is never loaded. |

## How consent gating works

- The banner markup and its assets are always enqueued and rendered — this keeps the
  HTML identical for every visitor of a given URL, so a full-page cache (a very
  common setup on agency-managed GeneratePress sites) can safely cache and reuse
  the page without ever mixing up one visitor's consent state with another's.
- All consent-specific behavior happens client-side: a small inline script printed
  right after the banner checks the `frbl_cookie_consent` cookie and immediately
  hides the banner if the visitor already decided, before the page finishes loading.
- **Reject**: only sets the cookie. Nothing is ever requested from Google.
- **Accept**: the cookie is set immediately, and the browser asks a small
  read-only AJAX endpoint (`frbl_get_cookie_notice_config`) for the configured
  GTM/GA4 IDs. The endpoint only returns them when the *browser's own* cookie
  says `accepted` — the IDs are never present in the page's HTML source before
  that. The frontend script then creates the actual `<script>` tags dynamically,
  so a first-time visitor sees tracking start without a page reload, and a
  returning visitor who already accepted gets it automatically on every page load.
  This endpoint is deliberately unauthenticated: it's read-only, never touches the
  aggregate counters, and only ever echoes back non-secret IDs that are already
  public once GTM/GA4 loads — a nonce here would have to live in the cache-neutral
  HTML this module renders and would go stale on any page a cache keeps around
  longer than a nonce's lifetime, breaking tracking until the cache refreshes.

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

## Out of scope

- Category-based consent (analytics/marketing toggles as separate switches).
- Google Consent Mode v2 signals (`gtag('consent', ...)`).
- Per-visitor logging, timestamps, or a dashboard of responses over time.
- Auto-scanning the site's existing scripts/cookies.
