# Review Notice

A dismissible admin notice inviting site owners to leave a review of FrontBlocks on WordPress.org.

## How it works

- `FrontBlocks\Admin\ReviewNotice` (`includes/Admin/ReviewNotice.php`) hooks `admin_notices` and shows the notice on the Dashboard and the FrontBlocks settings page, to users who can `manage_options`.
- The notice only appears once `self::DAYS_UNTIL_NOTICE` (14) days have passed since the plugin's first activation.
- The activation timestamp is recorded via `register_activation_hook()` in `frontblocks.php` (`frbl_set_activation_date()`), stored in the `frbl_activation_date` option — this runs reliably on first activation, unlike a hook registered from a class only instantiated on `plugins_loaded`.
- Dismissing the notice — via its "No thanks" button or WordPress's built-in notice-dismiss control — persists per user in the `frbl_review_notice_dismissed` user meta key, through a nonce-protected AJAX request (`frbl_dismiss_review_notice`).

## Debugging

Define `FRBL_DEBUG_NOTICES` as `true` (e.g. in `wp-config.php`) to bypass the screen, dismissal and 14-day checks and force the notice to render on every admin screen — useful for visually verifying markup and styling without waiting or resetting options/user meta.
