# FrontBlocks: Google Sign-In

**Plugin:** FrontBlocks for Gutenberg/GeneratePress
**Feature:** "Sign in / Sign up with Google" for wp-login.php and WooCommerce
**Since:** 1.6.0

---

## Overview

Google Sign-In is an optional module that lets visitors authenticate with
their Google account instead of, or alongside, a WordPress username and
password. It uses [Google Identity Services](https://developers.google.com/identity/gsi/web)
on the browser side and verifies the resulting ID token server-side —
no third-party PHP library or Google API client is required.

Enable it from **Appearance → FrontBlocks → Google Sign-In**. The button
stays hidden everywhere until a Client ID and Client Secret are set there.

## Settings

| Field | Description |
| --- | --- |
| Google Client ID / Google Client Secret | From a Google Cloud OAuth 2.0 "Web application" client. The settings screen shows the exact Authorized JavaScript origin and Authorized redirect URI to register for the current site. |
| wp-admin login screen | Shows the button on `wp-login.php`'s login form. |
| WooCommerce My Account — Login | Shows the button above the My Account login form. |
| WooCommerce My Account — Register | Shows the button above the My Account registration form. Only has an effect if WooCommerce's own "Allow customers to create an account on the My Account page" setting is also enabled — otherwise WooCommerce doesn't render a registration form at all. |
| WooCommerce Checkout (guests) | Shows the button before the checkout form, for visitors who aren't logged in yet. Does not otherwise change or block guest checkout. |

The Client Secret is only used server-side, to keep the OAuth client
associated with a "Web application" type in Google Cloud (Google requires a
secret for that client type even though this module doesn't perform the
authorization-code exchange itself).

## How it works

1. **Button rendering**: each enabled location prints a `<div
   class="frbl-google-signin-button">` placeholder. Google Identity Services
   (`https://accounts.google.com/gsi/client`, enqueued only on pages that need
   it) renders the actual button into that placeholder and, on click, returns
   a signed ID token to `assets/google-signin/frontblocks-google-signin.js`.
2. **Server-side verification**: the bridging script POSTs that token to a
   REST route (`frontblocks/v1/google-signin`), nonce-protected
   (`frbl_google_signin_action`). `TokenVerifier::verify()` checks the token's
   signature against Google's published JWKS (`RS256`, cached in a transient),
   its issuer, audience (must match the configured Client ID), expiry, and
   `email_verified` claim — nothing from the token is trusted until all of
   this passes.
3. **Account resolution** (`GoogleSignIn::find_or_create_user()`): the
   verified payload's `sub` (Google account ID) and `email` are used to find
   a WordPress user, in order —
   - an account previously linked to that Google ID (`frbl_google_id` user
     meta);
   - an existing account whose email matches, which then gets linked
     automatically for next time;
   - otherwise, if registration is allowed, a new account is created (a
     random password, since the account authenticates via Google) and linked.
4. **Registration is allowed** when `users_can_register` is on, or, if
   WooCommerce is active, when its own "Allow customers to create an account"
   setting is on — matching the same rule the rest of WordPress/WooCommerce
   already uses to decide whether new accounts can be created at all.
5. The visitor is then logged in via `wp_set_current_user()` +
   `wp_set_auth_cookie()`, and redirected to a validated target (the current
   page for My Account/Checkout, `wp-admin` — or the `redirect_to` query arg
   — for wp-login.php).

## Blocks and shortcodes

Two shortcodes and one block are always registered, independent of the
settings above — they render nothing (or an admin-only setup notice) until
Client ID and Client Secret are configured:

- `[frontblocks_google_login]` / `[frontblocks_google_register]` shortcodes,
  each accepting an optional `redirect` attribute.
- The **Google Login** block (Gutenberg inserter, FrontBlocks category), with
  a "Button type" (sign in / sign up) and a redirect URL setting.

## Security notes

- The ID token is verified entirely server-side against Google's own
  published keys — the browser can't forge a session by tampering with the
  token or replaying an old one (expiry is enforced).
- New accounts always get a random, unusable password; there is no
  Google-authenticated account that could also be logged into with a guessed
  WordPress password.
- The REST endpoint's `permission_callback` is `__return_true` (it has to be,
  for a not-yet-logged-in visitor), but every request still requires a valid
  nonce and a token that passes full verification — the endpoint itself
  never trusts client-supplied identity claims directly.

## Extension hooks

- `frbl_google_signin_user_created( int $user_id, array $payload )` — action,
  fires right after a new WordPress account is created via Google Sign-In.
- `frbl_google_signin_allow_registration( bool $allowed )` — filter, lets a
  site override whether Google Sign-In may create new accounts, independent
  of the `users_can_register` / WooCommerce rule above.

## Out of scope

- No other OAuth/OIDC provider (Facebook, Apple, etc.) — this module is
  Google-only.
- No account-linking UI for an already-logged-in user to connect/disconnect
  their Google account after the fact; linking only happens automatically the
  first time a matching email signs in with Google.
