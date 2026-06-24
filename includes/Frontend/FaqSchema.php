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
 * that have frblFaqSchema enabled, then outputs JSON-LD in the footer.
 * Supports FAQPage and HowTo schema types.
 *
 * @since 1.0.0
 */
class FaqSchema {

	/**
	 * Collected schema items grouped by type.
	 * Shape: array<string, array<array{question: string, answer: string}>>
	 *
	 * @var array
	 */
	private array $schema_groups = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'render_block_core/accordion', array( $this, 'collect_details_block' ), 10, 2 );
		add_filter( 'render_block_generateblocks/container', array( $this, 'collect_gb_accordion_block' ), 10, 2 );
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

		$schema_type = $block['attrs']['frblSchemaType'] ?? 'FAQPage';

		preg_match_all( '/<span[^>]+class="[^"]*wp-block-accordion-heading__toggle-title[^"]*"[^>]*>(.*?)<\/span>/is', $block_content, $questions );
		preg_match_all( '/<div[^>]+class="[^"]*wp-block-accordion-panel[^"]*"[^>]*>(.*?)<\/div>\s*(?:<\/div>|$)/is', $block_content, $answers );

		foreach ( $questions[1] as $i => $raw_question ) {
			$question = trim( html_entity_decode( wp_strip_all_tags( $raw_question ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
			$answer   = isset( $answers[1][ $i ] ) ? trim( html_entity_decode( wp_strip_all_tags( $answers[1][ $i ] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : '';

			if ( '' !== $question && '' !== $answer ) {
				$this->schema_groups[ $schema_type ][] = array(
					'question' => $question,
					'answer'   => $answer,
				);
			}
		}

		return $block_content;
	}

	/**
	 * Collect FAQ entries from a GenerateBlocks accordion container when frblFaqSchema is enabled.
	 *
	 * @param string $block_content Rendered HTML.
	 * @param array  $block         Block data.
	 * @return string Unchanged block HTML.
	 */
	public function collect_gb_accordion_block( string $block_content, array $block ): string {
		if ( empty( $block['attrs']['frblFaqSchema'] ) ) {
			return $block_content;
		}
		if ( ( $block['attrs']['variantRole'] ?? '' ) !== 'accordion' ) {
			return $block_content;
		}

		$schema_type = $block['attrs']['frblSchemaType'] ?? 'FAQPage';

		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?><meta charset="utf-8">' . $block_content );
		libxml_clear_errors();
		$xpath = new \DOMXPath( $dom );

		$toggle_texts  = $xpath->query( '//*[contains(@class,"gb-accordion__toggle")]//span[contains(@class,"gb-button-text")]' );
		$content_nodes = $xpath->query( '//*[starts-with(@id,"gb-accordion-content-")]' );

		$questions = array();
		foreach ( $toggle_texts as $node ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$questions[] = trim( html_entity_decode( $node->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		}

		$answers = array();
		foreach ( $content_nodes as $node ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$answers[] = trim( html_entity_decode( $node->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		}

		foreach ( $questions as $i => $question ) {
			$answer = $answers[ $i ] ?? '';
			if ( '' !== $question && '' !== $answer ) {
				$this->schema_groups[ $schema_type ][] = array(
					'question' => $question,
					'answer'   => $answer,
				);
			}
		}

		return $block_content;
	}

	/**
	 * Build a FAQPage JSON-LD array.
	 *
	 * @param array $items Q&A pairs.
	 * @return array Schema array.
	 */
	private function build_faq_page( array $items ): array {
		$entities = array();
		foreach ( $items as $item ) {
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['answer'],
				),
			);
		}

		return array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);
	}

	/**
	 * Build a HowTo JSON-LD array.
	 *
	 * @param array $items Q&A pairs used as steps.
	 * @return array Schema array.
	 */
	private function build_how_to( array $items ): array {
		$steps = array();
		foreach ( $items as $item ) {
			$steps[] = array(
				'@type' => 'HowToStep',
				'name'  => $item['question'],
				'text'  => $item['answer'],
			);
		}

		return array(
			'@context' => 'https://schema.org',
			'@type'    => 'HowTo',
			'name'     => get_the_title(),
			'step'     => $steps,
		);
	}

	/**
	 * Output JSON-LD scripts in the footer, one per schema type.
	 *
	 * @return void
	 */
	public function output_json_ld(): void {
		if ( empty( $this->schema_groups ) ) {
			return;
		}

		foreach ( $this->schema_groups as $type => $items ) {
			if ( 'HowTo' === $type ) {
				$schema = $this->build_how_to( $items );
			} else {
				$schema = $this->build_faq_page( $items );
			}

			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}
	}

	/**
	 * Enqueue editor assets for the Schema inspector controls.
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
