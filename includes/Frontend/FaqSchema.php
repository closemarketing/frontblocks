<?php
/**
 * FAQ Schema (JSON-LD) module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * FaqSchema class.
 *
 * Collects Q&A pairs from core/details and generateblocks/accordion blocks
 * that have frblFaqSchema enabled, then outputs a FAQPage JSON-LD in the footer.
 *
 * @since 1.0.0
 */
class FaqSchema {

	/**
	 * Collected FAQ entries: array of { question: string, answer: string }.
	 *
	 * @var array
	 */
	private array $faq_items = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'render_block_core/details', array( $this, 'collect_details_block' ), 10, 2 );
		add_filter( 'render_block_generateblocks/accordion', array( $this, 'collect_accordion_block' ), 10, 2 );
		add_action( 'wp_footer', array( $this, 'output_json_ld' ), 99 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Collect FAQ entry from a core/details block when frblFaqSchema is enabled.
	 *
	 * @param string $block_content Rendered HTML.
	 * @param array  $block         Block data.
	 * @return string Unchanged block HTML.
	 */
	public function collect_details_block( string $block_content, array $block ): string {
		if ( empty( $block['attrs']['frblFaqSchema'] ) ) {
			return $block_content;
		}

		$question = $block['attrs']['summary'] ?? '';

		// Fall back to parsing <summary> from rendered HTML if attribute is empty.
		if ( '' === $question ) {
			if ( preg_match( '/<summary[^>]*>(.*?)<\/summary>/is', $block_content, $matches ) ) {
				$question = wp_strip_all_tags( $matches[1] );
			}
		}

		// Extract answer: everything inside <details> excluding the <summary>.
		$answer = '';
		if ( preg_match( '/<details[^>]*>(.*?)<\/details>/is', $block_content, $outer ) ) {
			$inner = preg_replace( '/<summary[^>]*>.*?<\/summary>/is', '', $outer[1] );
			$answer = wp_strip_all_tags( $inner );
		}

		$question = trim( $question );
		$answer   = trim( $answer );

		if ( '' !== $question && '' !== $answer ) {
			$this->faq_items[] = array(
				'question' => $question,
				'answer'   => $answer,
			);
		}

		return $block_content;
	}

	/**
	 * Collect FAQ entry from a generateblocks/accordion block when frblFaqSchema is enabled.
	 *
	 * @param string $block_content Rendered HTML.
	 * @param array  $block         Block data.
	 * @return string Unchanged block HTML.
	 */
	public function collect_accordion_block( string $block_content, array $block ): string {
		if ( empty( $block['attrs']['frblFaqSchema'] ) ) {
			return $block_content;
		}

		$question = '';
		$answer   = '';

		// GenerateBlocks accordion toggle element (.gb-accordion__toggle).
		if ( preg_match( '/<[^>]+class="[^"]*gb-accordion__toggle[^"]*"[^>]*>(.*?)<\/[^>]+>/is', $block_content, $q ) ) {
			$question = wp_strip_all_tags( $q[1] );
		}

		// GenerateBlocks accordion content element (.gb-accordion__content).
		if ( preg_match( '/<[^>]+class="[^"]*gb-accordion__content[^"]*"[^>]*>(.*?)<\/[^>]+>/is', $block_content, $a ) ) {
			$answer = wp_strip_all_tags( $a[1] );
		}

		$question = trim( $question );
		$answer   = trim( $answer );

		if ( '' !== $question && '' !== $answer ) {
			$this->faq_items[] = array(
				'question' => $question,
				'answer'   => $answer,
			);
		}

		return $block_content;
	}

	/**
	 * Output the FAQPage JSON-LD script in the footer.
	 *
	 * @return void
	 */
	public function output_json_ld(): void {
		if ( empty( $this->faq_items ) ) {
			return;
		}

		$entities = array();
		foreach ( $this->faq_items as $item ) {
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['answer'],
				),
			);
		}

		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}

	/**
	 * Enqueue editor assets for the FAQ Schema inspector toggle.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		$asset_file = FRBL_PLUGIN_PATH . 'assets/faq-schema/frontblocks-faq-schema.js';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		wp_enqueue_script(
			'frontblocks-faq-schema',
			FRBL_PLUGIN_URL . 'assets/faq-schema/frontblocks-faq-schema.js',
			array( 'wp-hooks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-compose' ),
			FRBL_VERSION,
			true
		);
	}
}
