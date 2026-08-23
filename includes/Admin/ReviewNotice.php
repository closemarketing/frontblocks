<?php
/**
 * Admin Review Notice
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Shows a dismissible admin notice asking users to review the plugin,
 * a set number of days after it was first activated.
 */
class ReviewNotice {

	/**
	 * Days to wait after activation before showing the notice.
	 *
	 * @var int
	 */
	const DAYS_UNTIL_NOTICE = 14;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'review_notice' ) );
		add_action( 'wp_ajax_frbl_dismiss_review_notice', array( $this, 'dismiss_review_notice' ) );
	}

	/**
	 * Display admin notice to remind users to leave a review on WordPress.org.
	 *
	 * @return void
	 */
	public function review_notice() {
		$debug = defined( 'FRBL_DEBUG_NOTICES' ) && FRBL_DEBUG_NOTICES;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( ! $debug ) {
			$allowed_screens = array( 'appearance_page_frontblocks-settings', 'dashboard' );
			if ( ! in_array( $screen->id, $allowed_screens, true ) && false === strpos( $screen->id, 'frontblocks' ) ) {
				return;
			}
		}

		$dismissed = get_user_meta( get_current_user_id(), 'frbl_review_notice_dismissed', true );
		if ( ! $debug && $dismissed ) {
			return;
		}

		$activation_date = get_option( 'frbl_activation_date' );
		if ( ! $activation_date ) {
			update_option( 'frbl_activation_date', time(), false );
			if ( ! $debug ) {
				return;
			}
		}

		if ( ! $debug ) {
			$days_active = ( time() - (int) $activation_date ) / DAY_IN_SECONDS;
			if ( self::DAYS_UNTIL_NOTICE > $days_active ) {
				return;
			}
		}

		$review_url = 'https://wordpress.org/support/plugin/frontblocks/reviews/#new-post';

		$notice_title = esc_html__( 'Enjoying FrontBlocks?', 'frontblocks' );

		$notice_message = sprintf(
			/* translators: %s is the review URL. */
			__( 'Thank you for using FrontBlocks! If you find it helpful, please take a moment to <a href="%s" target="_blank" rel="noopener noreferrer">leave a review on WordPress.org</a>. It really helps the plugin grow!', 'frontblocks' ),
			esc_url( $review_url )
		);

		echo '<div id="frbl-review-notice" class="notice notice-info is-dismissible">';
		echo '<p><strong>' . esc_html( $notice_title ) . '</strong></p>';
		$allowed_tags = array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
			),
		);
		echo '<p>' . wp_kses( $notice_message, $allowed_tags ) . '</p>';
		echo '<p>';
		echo '<a href="' . esc_url( $review_url ) . '" target="_blank" rel="noopener noreferrer" class="button button-primary">' . esc_html__( 'Leave a Review', 'frontblocks' ) . '</a>';
		echo '&nbsp;&nbsp;';
		echo '<a href="#" id="frbl-dismiss-review" class="button button-secondary">' . esc_html__( 'No thanks', 'frontblocks' ) . '</a>';
		echo '</p>';
		echo '</div>';

		$this->enqueue_script();
	}

	/**
	 * Enqueue the JS needed to handle dismissal.
	 *
	 * @return void
	 */
	private function enqueue_script() {
		wp_enqueue_script(
			'frbl-review-notice',
			FRBL_PLUGIN_URL . 'assets/admin/review-notice.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frbl-review-notice',
			'frblReviewNotice',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'frbl_dismiss_review' ),
			)
		);
	}

	/**
	 * AJAX handler to persist the notice dismissal for the current user.
	 *
	 * @return void
	 */
	public function dismiss_review_notice() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'frbl_dismiss_review' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'frontblocks' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'frontblocks' ), 403 );
		}

		update_user_meta( get_current_user_id(), 'frbl_review_notice_dismissed', true );
		wp_die();
	}
}
