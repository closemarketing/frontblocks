<?php
/**
 * Tests for CookieNotice's Clientify/Brevo tracking snippet auto-detection.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\CookieNotice;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class CookieNoticeTrackingSnippetTest extends TestCase {

	/**
	 * The Clientify Analytics Plus snippet is just a single pixel <script>
	 * tag — the id is the last path segment of its src URL.
	 */
	public function test_detects_clientify_analytics_plus_pixel() {
		$snippet = <<<'HTML'
<!--Clientify Tracking Begins-->
<script defer src="https://analyticsplusdev.clientify.net/analytics_plus/pixel/TestPixelId123"></script>
<!--Clientify Tracking Ends-->
HTML;

		$detected = CookieNotice::detect_tracking_snippet( $snippet );

		$this->assertSame( 'clientify_analytics_plus', $detected['type'] );
		$this->assertSame( 'TestPixelId123', $detected['id'] );
	}

	/**
	 * The classic Clientify Analytics snippet loads a different domain
	 * (tracker.js, not the Analytics Plus pixel host) and carries its code
	 * inside a setTrackingCode(...) call rather than in a URL.
	 */
	public function test_detects_clientify_analytics_classic_tracking_code() {
		$snippet = <<<'HTML'
<!--Clientify Tracking Begins-->
<script type="text/javascript">
if (typeof trackerCode ==='undefined'){
    (function (d, w, u, o) {
        w[o] = w[o] || function () {
            (w[o].q = w[o].q || []).push(arguments)
        };
        a = d.createElement('script'),
            m = d.getElementsByTagName('script')[0];
        a.async = 1; a.src = u;
        m.parentNode.insertBefore(a, m)
    })(document, window, 'https://analytics.clientify.net/tracker.js', 'ana');
    ana('setTrackerUrl', 'https://analytics.clientify.net');
    ana('setTrackingCode', 'CF-00000-00000-TEST');
    ana('trackPageview');
}</script>
<!--Clientify Tracking Ends-->
HTML;

		$detected = CookieNotice::detect_tracking_snippet( $snippet );

		$this->assertSame( 'clientify_analytics_classic', $detected['type'] );
		$this->assertSame( 'CF-00000-00000-TEST', $detected['id'] );
	}

	/**
	 * Brevo's SDK loader + init() snippet carries its id as a client_key.
	 */
	public function test_detects_brevo_client_key() {
		$snippet = <<<'HTML'
<script src="https://cdn.brevo.com/js/sdk-loader.js" async></script>
<script>
    window.Brevo = window.Brevo || [];
    Brevo.push([
        "init",
        {
        client_key: "testclientkey1234567890",
        }
    ]);
</script>
HTML;

		$detected = CookieNotice::detect_tracking_snippet( $snippet );

		$this->assertSame( 'brevo', $detected['type'] );
		$this->assertSame( 'testclientkey1234567890', $detected['id'] );
	}

	/**
	 * A snippet from an unsupported tool (or plain garbage) must not be
	 * mistaken for one of the three supported patterns.
	 */
	public function test_unrecognized_snippet_returns_null() {
		$this->assertNull( CookieNotice::detect_tracking_snippet( '<script src="https://example.com/some-other-tracker.js"></script>' ) );
	}

	/**
	 * An empty/blank field is "no snippet configured", not an invalid one.
	 */
	public function test_blank_snippet_returns_null() {
		$this->assertNull( CookieNotice::detect_tracking_snippet( '' ) );
		$this->assertNull( CookieNotice::detect_tracking_snippet( "   \n\t" ) );
	}

	/**
	 * build_tracking_snippet() must round-trip: what it generates for a
	 * detected type/id must itself detect back to the same type/id, so the
	 * admin settings field can safely prefill from stored values.
	 */
	public function test_build_tracking_snippet_round_trips_through_detection() {
		foreach (
			array(
				array( 'clientify_analytics_plus', 'RoundTripPixel1' ),
				array( 'clientify_analytics_classic', 'CF-11111-22222-RTID' ),
				array( 'brevo', 'roundtripclientkey000001' ),
			) as $case
		) {
			list( $type, $id ) = $case;

			$snippet  = CookieNotice::build_tracking_snippet( $type, $id );
			$detected = CookieNotice::detect_tracking_snippet( $snippet );

			$this->assertSame( $type, $detected['type'], "Round trip failed for type: {$type}" );
			$this->assertSame( $id, $detected['id'], "Round trip failed for id: {$id}" );
		}
	}

	/**
	 * An empty type/id, or an unknown type, must not produce a snippet at all.
	 */
	public function test_build_tracking_snippet_returns_empty_for_invalid_input() {
		$this->assertSame( '', CookieNotice::build_tracking_snippet( '', '' ) );
		$this->assertSame( '', CookieNotice::build_tracking_snippet( 'brevo', '' ) );
		$this->assertSame( '', CookieNotice::build_tracking_snippet( 'not-a-real-type', 'some-id' ) );
	}

	/**
	 * GTM/GA4 default to the "analytics" category, while every Clientify/Brevo
	 * variant defaults to "marketing" — this is what FrontBlocks PRO's
	 * per-category consent gating keys off of.
	 */
	public function test_integration_default_categories() {
		$this->assertSame( 'analytics', CookieNotice::get_integration_default_category( 'gtm' ) );
		$this->assertSame( 'analytics', CookieNotice::get_integration_default_category( 'ga4' ) );
		$this->assertSame( 'marketing', CookieNotice::get_integration_default_category( 'clientify_analytics_plus' ) );
		$this->assertSame( 'marketing', CookieNotice::get_integration_default_category( 'clientify_analytics_classic' ) );
		$this->assertSame( 'marketing', CookieNotice::get_integration_default_category( 'brevo' ) );
	}

	/**
	 * An unknown integration type must still resolve to a sane default
	 * ("marketing") instead of an empty/invalid category.
	 */
	public function test_unknown_integration_type_defaults_to_marketing_category() {
		$this->assertSame( 'marketing', CookieNotice::get_integration_default_category( 'some-future-tool' ) );
	}

	/**
	 * The category default must be filterable, so FrontBlocks PRO (or any
	 * add-on) can override it without forking this method.
	 */
	public function test_integration_default_category_is_filterable() {
		$callback = static function ( $category, $type ) {
			return 'brevo' === $type ? 'custom-category' : $category;
		};

		add_filter( 'frbl_cookie_notice_integration_category', $callback, 10, 2 );

		$this->assertSame( 'custom-category', CookieNotice::get_integration_default_category( 'brevo' ) );
		$this->assertSame( 'analytics', CookieNotice::get_integration_default_category( 'ga4' ) );

		remove_filter( 'frbl_cookie_notice_integration_category', $callback, 10 );
	}
}
