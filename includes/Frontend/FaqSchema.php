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
		add_filter( 'render_block_core/accordion', array( $this, 'collect_details_block' ), 10, 2 );
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

		preg_match_all( '/<span[^>]+class="[^"]*wp-block-accordion-heading__toggle-title[^"]*"[^>]*>(.*?)<\/span>/is', $block_content, $questions );
		preg_match_all( '/<div[^>]+class="[^"]*wp-block-accordion-panel[^"]*"[^>]*>(.*?)<\/div>\s*(?:<\/div>|$)/is', $block_content, $answers );

		foreach ( $questions[1] as $i => $raw_question ) {
			$question = trim( wp_strip_all_tags( $raw_question ) );
			$answer   = isset( $answers[1][ $i ] ) ? trim( wp_strip_all_tags( $answers[1][ $i ] ) ) : '';

			if ( '' !== $question && '' !== $answer ) {
				$this->faq_items[] = array(
					'question' => $question,
					'answer'   => $answer,
				);
			}
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
