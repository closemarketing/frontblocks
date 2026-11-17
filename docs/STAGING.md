# Staging Mode

Staging Mode applies a set of safety measures when a site is a staging or copy environment, so it cannot be indexed, cannot send real emails, cannot trigger real side effects (payments, renewals, webhooks, syncs) and is clearly labelled as staging for anyone using it.

Everything is applied at runtime through filters. FrontBlocks never changes a stored option (`blog_public`, gateway settings...), so the behaviour is reversible and is not carried over by a database copy.

The implementation is `FrontBlocks\Frontend\Staging` (`includes/Frontend/Staging.php`). Its settings live in the **Maintenance** tab of the FrontBlocks settings (Appearance → FrontBlocks), in the **Staging Mode** section below Maintenance Mode. That section also shows why staging mode is active.

## When it is active

Staging mode is active when **any** of these is true:

| Signal | Details |
|---|---|
| Domain | The host of the site URL starts with `beta.` (for example `beta.example.com`). The list of prefixes is filterable with `frontblocks_staging_host_prefixes`. |
| Manual toggle | **Enable staging mode** in the Maintenance tab. When staging is detected from the domain, the environment or the constant, the toggle shows as on and is locked. |
| Environment | `wp_get_environment_type()` returns `staging` or `development` (respects `WP_ENVIRONMENT_TYPE`). |
| Constant | `define( 'FRBL_STAGING', true );` in `wp-config.php`. |

`define( 'FRBL_STAGING', false );` is an emergency switch: it always wins, over the domain, the toggle, the environment, and the `frontblocks_is_staging` filter. A production site can never be locked into staging by a cloned option.

Only the manual toggle is stored in the database. Domain, environment and constant detection are evaluated on every request, so pushing a staging database to production does not carry staging behaviour over. The constant accepts booleans and boolean-like strings (`'true'`, `'0'`); anything else is ignored.

## Safety measures

Measures are always on while staging mode is active and cannot be switched off one by one. The Staging Mode section shows them as read-only cards. The options are the address that receives the emails, the list of hosts to block and **Restrict access**, the one measure that is optional and off by default: it stops every visitor who is not logged in, so it cannot be always on.

| Measure | What it does |
|---|---|
| Prevent indexing | Forces `noindex, nofollow` in the robots meta tag (`wp_robots`), sends `X-Robots-Tag: noindex, nofollow`, serves a closed `robots.txt` (`Disallow: /`) and makes sitemaps answer 404. Covers the core sitemaps and Rank Math (tested); Yoast SEO reads the same `sitemap` query variable. |
| Block outgoing email | `wp_mail()` never sends (`pre_wp_mail`) and the SendGrid, Mailgun, Brevo, Postmark, Amazon SES, SparkPost and Mailjet APIs are blocked at `pre_http_request`. Each blocked email is added to a log (recipient, subject, time, caller) capped at 50 entries and shown in the settings. With **Redirect emails to** set, emails are sent to that address instead (with the original recipients in the message and without Cc/Bcc). |
| Visual indicators | A `STAGING` node in the admin bar, a `[STAGING]` prefix in the browser tab title (also when an SEO plugin builds the title) and an admin notice on the dashboard and the FrontBlocks settings. |
| Frontend top bar | A "Staging site — changes here are not live" bar for logged-in users (needs `wp_body_open`). |
| WooCommerce safety | Only when WooCommerce is active: a "test site" notice on cart and checkout (classic and block), Subscriptions renewals forced to manual (`woocommerce_subscriptions_is_duplicate_site`) and webhooks paused (`woocommerce_webhook_should_deliver`). |
| Pause background tasks | Pauses WP-Cron (`pre_get_ready_cron_jobs`) and Action Scheduler (no concurrent batches). This also pauses FrontBlocks' own Image Management bulk jobs. |
| Block hosts | Outgoing requests to the hosts listed in the settings fail, subdomains included. The site's own host is never blocked. |
| Remove tracking | Removes Google Analytics and Google Tag Manager scripts (enqueued scripts and snippets printed in the page) and stops FrontConsent from loading them. |
| Restrict access (optional) | Only logged-in users can view the frontend. Visitors get a "Private staging site" page (HTTP 403) that reuses the maintenance curtain. AJAX, cron, REST and Customizer requests and `robots.txt` are never blocked (same rules as Maintenance Mode). Off by default so clients can review a `beta.` site without a login. Add-ons can let a visitor through with the `frontblocks_staging_allow_request` filter. |

### Limitations

- A physical `robots.txt` file in the web root is served by the web server before WordPress runs, so it cannot be replaced.
- Tracking removal works on the HTML of the page. Scripts whose ID starts with `frontconsent-` are kept on purpose (they only load tracking after consent, which is switched off). Tracking added by JavaScript files, or by other tools, is not detected.
- Pausing WP-Cron does not affect `wp cron event run` from WP-CLI.

## For add-ons and FrontBlocks PRO

```php
if ( function_exists( 'frontblocks_is_staging' ) && frontblocks_is_staging() ) {
	// Do not write to the live ERP/CRM from a staging site.
	return;
}
```

`frontblocks_is_staging()` returns the same value as `FrontBlocks\Frontend\Staging::is_active()`, cached for the request.

| Hook | Type | Purpose |
|---|---|---|
| `frontblocks_is_staging` | filter | Force the state: `( bool $active, array $state )`. The `FRBL_STAGING` constant is applied afterwards. |
| `frontblocks_staging_host_prefixes` | filter | Host prefixes that mark a site as staging. Default `array( 'beta.' )`. Add `staging.`, `dev.`, `test.`... |
| `frontblocks_staging_blocked_hosts` | filter | Hosts whose outgoing requests are blocked. Use it from ERP/CRM/automation connectors so staging never writes to a live system. |
| `frontblocks_staging_mail_api_hosts` | filter | Transactional email API hosts that are blocked. Host names, or regular expressions starting with `#`. |
| `frontblocks_staging_analytics_hosts` | filter | Tracking hosts whose scripts are removed. |
| `frontblocks_staging_analytics_protected_script_ids` | filter | Script ID prefixes that tracking removal never touches. Default `array( 'frontconsent-' )`. |
| `frontblocks_staging_allow_request` | filter | Whether a visitor who is not logged in may view the site while **Restrict access** is on. Default `false`. Lets an add-on grant access without a WordPress user, for example expiring client share links. |
| `frontblocks_staging_block_email` | filter | `( bool $handles, array $atts )`. Return `false` when an add-on routes the email itself, so the core measure steps aside and neither blocks nor redirects it. |

Add-ons that block or route email themselves can write to the same log with `FrontBlocks\Frontend\Staging::record_email( $type, $to, $subject )` (`blocked`, `redirected` or `api`). `Staging::EXTENSION_API` is the version of these hooks, so an add-on can check it before it relies on them, and `Staging::is_measure_enabled( $key )` tells whether a measure applies (every measure is on while staging mode is active, except Restrict access when it is off).

The detection result, with the reasons, is available from `Staging::get_state()`:

```php
$state = FrontBlocks\Frontend\Staging::get_state();
// array( 'active' => true, 'reasons' => array( 'domain' ), 'forced_off' => false, 'details' => array( 'host' => 'beta.example.com', 'prefix' => 'beta.' ) )
```

## Settings keys

Stored in `frontblocks_settings`: `enable_staging` (manual toggle), `staging_restrict_access` (optional measure), `staging_redirect_email` and `staging_blocked_hosts`. The email log is kept in the `frontblocks_staging_email_log` option, which is not autoloaded.
