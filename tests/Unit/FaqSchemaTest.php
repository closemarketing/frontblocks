<?php
/**
 * Tests for FrontBlocks\Frontend\FaqSchema.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\FaqSchema;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class FaqSchemaTest extends TestCase {

	/**
	 * @var FaqSchema
	 */
	private $faq_schema;

	public function set_up() {
		parent::set_up();
		$this->faq_schema = new FaqSchema();
	}

	/**
	 * Read the private $schema_groups property.
	 *
	 * @return array
	 */
	private function get_schema_groups() {
		$property = new ReflectionProperty( FaqSchema::class, 'schema_groups' );
		$property->setAccessible( true );

		return $property->getValue( $this->faq_schema );
	}

	public function test_constructor_registers_wp_footer_action() {
		$this->assertNotFalse(
			has_action( 'wp_footer', array( $this->faq_schema, 'output_json_ld' ) )
		);
	}

	public function test_collect_details_block_ignores_block_without_faq_schema_flag() {
		$content = '<div><span class="wp-block-accordion-heading__toggle-title">Q1</span><div class="wp-block-accordion-panel"><p>A1</p></div></div>';
		$block   = array( 'attrs' => array() );

		$output = $this->faq_schema->collect_details_block( $content, $block );

		$this->assertSame( $content, $output );
		$this->assertSame( array(), $this->get_schema_groups() );
	}

	public function test_collect_details_block_extracts_question_and_answer_pairs() {
		$content = '<div class="wp-block-accordion">'
			. '<h3><button><span class="wp-block-accordion-heading__toggle-title">What is FrontBlocks?</span></button></h3>'
			. '<div class="wp-block-accordion-panel"><p>A WordPress plugin.</p></div>'
			. '</div>';
		$block = array( 'attrs' => array( 'frblFaqSchema' => true ) );

		$output = $this->faq_schema->collect_details_block( $content, $block );

		$this->assertSame( $content, $output, 'The rendered block markup must be returned unmodified.' );

		$groups = $this->get_schema_groups();
		$this->assertArrayHasKey( 'FAQPage', $groups );
		$this->assertSame( 'What is FrontBlocks?', $groups['FAQPage'][0]['question'] );
		$this->assertSame( 'A WordPress plugin.', $groups['FAQPage'][0]['answer'] );
	}

	public function test_collect_details_block_uses_custom_schema_type() {
		$content = '<div class="wp-block-accordion">'
			. '<span class="wp-block-accordion-heading__toggle-title">Step one</span>'
			. '<div class="wp-block-accordion-panel"><p>Do this first.</p></div>'
			. '</div>';
		$block = array(
			'attrs' => array(
				'frblFaqSchema'  => true,
				'frblSchemaType' => 'HowTo',
			),
		);

		$this->faq_schema->collect_details_block( $content, $block );

		$groups = $this->get_schema_groups();
		$this->assertArrayHasKey( 'HowTo', $groups );
		$this->assertArrayNotHasKey( 'FAQPage', $groups );
	}

	public function test_collect_details_block_skips_incomplete_pairs() {
		// A question with no matching answer panel must not be collected.
		$content = '<span class="wp-block-accordion-heading__toggle-title">Orphan question</span>';
		$block   = array( 'attrs' => array( 'frblFaqSchema' => true ) );

		$this->faq_schema->collect_details_block( $content, $block );

		$this->assertSame( array(), $this->get_schema_groups() );
	}

	public function test_collect_gb_accordion_block_ignores_non_accordion_variant() {
		$content = '<div class="gb-accordion__toggle"><span class="gb-button-text">Q1</span></div><div id="gb-accordion-content-1">A1</div>';
		$block   = array(
			'attrs' => array(
				'frblFaqSchema' => true,
				'variantRole'   => 'tabs',
			),
		);

		$this->faq_schema->collect_gb_accordion_block( $content, $block );

		$this->assertSame( array(), $this->get_schema_groups() );
	}

	public function test_collect_gb_accordion_block_ignores_block_without_faq_schema_flag() {
		$content = '<div class="gb-accordion__toggle"><span class="gb-button-text">Q1</span></div><div id="gb-accordion-content-1">A1</div>';
		$block   = array(
			'attrs' => array( 'variantRole' => 'accordion' ),
		);

		$this->faq_schema->collect_gb_accordion_block( $content, $block );

		$this->assertSame( array(), $this->get_schema_groups() );
	}

	public function test_collect_gb_accordion_block_extracts_question_and_answer() {
		$content = '<div class="gb-container">'
			. '<div class="gb-accordion__toggle"><span class="gb-button-text">How does it work?</span></div>'
			. '<div id="gb-accordion-content-1">It just works.</div>'
			. '</div>';
		$block = array(
			'attrs' => array(
				'frblFaqSchema' => true,
				'variantRole'   => 'accordion',
			),
		);

		$output = $this->faq_schema->collect_gb_accordion_block( $content, $block );

		$this->assertSame( $content, $output );

		$groups = $this->get_schema_groups();
		$this->assertArrayHasKey( 'FAQPage', $groups );
		$this->assertSame( 'How does it work?', $groups['FAQPage'][0]['question'] );
		$this->assertSame( 'It just works.', $groups['FAQPage'][0]['answer'] );
	}

	public function test_build_faq_page_produces_expected_schema_shape() {
		$method = new ReflectionMethod( FaqSchema::class, 'build_faq_page' );
		$method->setAccessible( true );

		$schema = $method->invoke(
			$this->faq_schema,
			array( array( 'question' => 'Q1', 'answer' => 'A1' ) )
		);

		$this->assertSame( 'https://schema.org', $schema['@context'] );
		$this->assertSame( 'FAQPage', $schema['@type'] );
		$this->assertSame( 'Question', $schema['mainEntity'][0]['@type'] );
		$this->assertSame( 'Q1', $schema['mainEntity'][0]['name'] );
		$this->assertSame( 'Answer', $schema['mainEntity'][0]['acceptedAnswer']['@type'] );
		$this->assertSame( 'A1', $schema['mainEntity'][0]['acceptedAnswer']['text'] );
	}

	public function test_build_how_to_produces_expected_schema_shape() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'How to test FrontBlocks' ) );
		go_to( get_permalink( $post_id ) );

		$method = new ReflectionMethod( FaqSchema::class, 'build_how_to' );
		$method->setAccessible( true );

		$schema = $method->invoke(
			$this->faq_schema,
			array( array( 'question' => 'Step 1', 'answer' => 'Do the thing.' ) )
		);

		$this->assertSame( 'https://schema.org', $schema['@context'] );
		$this->assertSame( 'HowTo', $schema['@type'] );
		$this->assertSame( 'How to test FrontBlocks', $schema['name'] );
		$this->assertSame( 'HowToStep', $schema['step'][0]['@type'] );
		$this->assertSame( 'Step 1', $schema['step'][0]['name'] );
		$this->assertSame( 'Do the thing.', $schema['step'][0]['text'] );
	}

	public function test_output_json_ld_prints_nothing_when_no_schema_collected() {
		ob_start();
		$this->faq_schema->output_json_ld();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_output_json_ld_prints_script_tag_with_collected_faq_data() {
		$content = '<span class="wp-block-accordion-heading__toggle-title">Q1</span><div class="wp-block-accordion-panel"><p>A1</p></div>';
		$block   = array( 'attrs' => array( 'frblFaqSchema' => true ) );
		$this->faq_schema->collect_details_block( $content, $block );

		ob_start();
		$this->faq_schema->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<script type="application/ld+json">', $output );
		$this->assertStringContainsString( '"@type":"FAQPage"', $output );
		$this->assertStringContainsString( '"name":"Q1"', $output );
		$this->assertStringContainsString( '"text":"A1"', $output );
	}
}
