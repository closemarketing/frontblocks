<?php
/**
 * Tests for FrontBlocks\Frontend\TableOfContents.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\TableOfContents;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class TableOfContentsTest extends TestCase {

	/**
	 * @var TableOfContents
	 */
	private $toc;

	public function set_up() {
		parent::set_up();
		$this->toc = new TableOfContents();
	}

	/**
	 * Content without a placeholder must be returned completely untouched.
	 */
	public function test_content_without_placeholder_is_untouched() {
		$content = '<h2>Some heading</h2><p>Text.</p>';

		$this->assertSame( $content, $this->toc->inject_toc_into_content( $content ) );
	}

	/**
	 * Builds the placeholder markup for a given config, exactly as
	 * render_placeholder() would produce it, so tests exercise the same
	 * regex/JSON contract the two methods share.
	 *
	 * @param array $config Partial config; merged over sensible defaults.
	 * @return string
	 */
	private function placeholder( array $config = array() ) {
		$defaults = array(
			'title'              => 'Table of Contents',
			'listStyle'          => 'unordered',
			'accentColor'        => '',
			'collapsible'        => false,
			'collapsedByDefault' => false,
			'sticky'             => false,
			'minLevel'           => 2,
			'maxLevel'           => 4,
		);
		$config   = array_merge( $defaults, $config );

		return sprintf(
			'<div class="frbl-toc-placeholder" data-frbl-toc-id="frbl-toc-1" data-frbl-toc-config="%s"></div>',
			esc_attr( wp_json_encode( $config ) )
		);
	}

	/**
	 * The happy path: a placeholder followed by headings in range produces
	 * a <nav> with one link per heading, and the headings get real ids.
	 */
	public function test_generates_nav_with_links_and_assigns_heading_ids() {
		$content = $this->placeholder() . '<h2>First Section</h2><p>Text.</p><h3>A Subsection</h3>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringContainsString( '<nav class="frbl-toc" aria-label="Table of Contents">', $result );
		$this->assertStringContainsString( '<a class="frbl-toc__link" href="#first-section">First Section</a>', $result );
		$this->assertStringContainsString( '<a class="frbl-toc__link" href="#a-subsection">A Subsection</a>', $result );
		$this->assertStringContainsString( '<h2 id="first-section" tabindex="-1" data-frbl-toc-heading="1">First Section</h2>', $result );
		$this->assertStringContainsString( '<h3 id="a-subsection" tabindex="-1" data-frbl-toc-heading="1">A Subsection</h3>', $result );
	}

	/**
	 * An author-supplied id must be preserved untouched and used as the
	 * anchor — never overwritten with a generated one.
	 */
	public function test_preserves_author_supplied_heading_id() {
		$content = $this->placeholder() . '<h2 id="my-custom-anchor" class="wp-block-heading">First Section</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringContainsString( '<a class="frbl-toc__link" href="#my-custom-anchor">First Section</a>', $result );
		$this->assertStringContainsString( '<h2 id="my-custom-anchor" class="wp-block-heading" tabindex="-1" data-frbl-toc-heading="1">First Section</h2>', $result );
	}

	/**
	 * A heading that already has a tabindex must not get a second one.
	 */
	public function test_does_not_duplicate_an_existing_tabindex() {
		$content = $this->placeholder() . '<h2 id="foo" tabindex="0">Foo</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertSame( 1, substr_count( $result, 'tabindex=' ) );
		$this->assertStringContainsString( 'tabindex="0"', $result );
	}

	/**
	 * Two headings with identical text must get disambiguated ids so the
	 * generated anchors stay unique.
	 */
	public function test_disambiguates_duplicate_heading_text() {
		$content = $this->placeholder() . '<h2>Overview</h2><h2>Overview</h2><h2>Overview</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringContainsString( 'href="#overview"', $result );
		$this->assertStringContainsString( 'href="#overview-2"', $result );
		$this->assertStringContainsString( 'href="#overview-3"', $result );
		$this->assertStringContainsString( '<h2 id="overview"', $result );
		$this->assertStringContainsString( '<h2 id="overview-2"', $result );
		$this->assertStringContainsString( '<h2 id="overview-3"', $result );
	}

	/**
	 * A generated id must not collide with an author-supplied id that
	 * happens to match the same slug.
	 */
	public function test_generated_id_avoids_colliding_with_an_author_supplied_id() {
		$content = $this->placeholder() . '<h2 id="overview">Custom Anchor</h2><h2>Overview</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringContainsString( 'href="#overview"', $result );
		$this->assertStringContainsString( 'href="#overview-2"', $result );
	}

	/**
	 * Headings outside the configured min/max level range must be excluded
	 * from the list, though they're still assigned an id (another Table of
	 * Contents instance, or a manual link, might target them).
	 */
	public function test_excludes_headings_outside_the_configured_level_range() {
		$content = $this->placeholder( array( 'minLevel' => 2, 'maxLevel' => 2 ) )
			. '<h2>In Range</h2><h3>Too Deep</h3><h4>Also Too Deep</h4>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringContainsString( 'href="#in-range"', $result );
		$this->assertStringNotContainsString( 'href="#too-deep"', $result );
		$this->assertStringNotContainsString( 'href="#also-too-deep"', $result );
		// Still gets an id even though it's excluded from this TOC's list.
		$this->assertStringContainsString( '<h3 id="too-deep"', $result );
	}

	/**
	 * When no heading falls within range, the block must render nothing
	 * rather than an empty, useless navigation landmark.
	 */
	public function test_renders_nothing_when_no_heading_is_in_range() {
		$content = $this->placeholder( array( 'minLevel' => 5, 'maxLevel' => 6 ) ) . '<h2>Only a top heading</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringNotContainsString( 'frbl-toc-placeholder', $result );
		$this->assertStringNotContainsString( '<nav', $result );
	}

	/**
	 * Collapsible mode must render as a native <details>/<summary>, open
	 * by default unless collapsedByDefault is set.
	 */
	public function test_collapsible_renders_as_details_summary_open_by_default() {
		$content = $this->placeholder( array( 'collapsible' => true ) ) . '<h2>Section</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertMatchesRegularExpression( '/<details class="frbl-toc frbl-toc--collapsible" open>/', $result );
		$this->assertStringContainsString( '<summary class="frbl-toc__title">Table of Contents</summary>', $result );
	}

	/**
	 * collapsedByDefault must omit the `open` attribute.
	 */
	public function test_collapsible_and_collapsed_by_default_omits_open_attribute() {
		$content = $this->placeholder( array( 'collapsible' => true, 'collapsedByDefault' => true ) ) . '<h2>Section</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertMatchesRegularExpression( '/<details class="frbl-toc frbl-toc--collapsible">/', $result );
	}

	/**
	 * listStyle must control the list tag and the modifier class.
	 */
	public function test_list_style_controls_the_list_tag() {
		$orderedResult = $this->toc->inject_toc_into_content( $this->placeholder( array( 'listStyle' => 'ordered' ) ) . '<h2>A</h2>' );
		$plainResult   = $this->toc->inject_toc_into_content( $this->placeholder( array( 'listStyle' => 'plain' ) ) . '<h2>A</h2>' );

		$this->assertStringContainsString( '<ol class="frbl-toc__list frbl-toc__list--ordered">', $orderedResult );
		$this->assertStringContainsString( '<ul class="frbl-toc__list frbl-toc__list--plain">', $plainResult );
	}

	/**
	 * The sticky flag adds a modifier class to the wrapper.
	 */
	public function test_sticky_adds_a_modifier_class() {
		$result = $this->toc->inject_toc_into_content( $this->placeholder( array( 'sticky' => true ) ) . '<h2>A</h2>' );

		$this->assertStringContainsString( 'frbl-toc frbl-toc--sticky', $result );
	}

	/**
	 * An accent color is exposed as a CSS custom property on the wrapper.
	 */
	public function test_accent_color_is_applied_as_a_css_custom_property() {
		$result = $this->toc->inject_toc_into_content( $this->placeholder( array( 'accentColor' => '#ff0000' ) ) . '<h2>A</h2>' );

		$this->assertStringContainsString( 'style="--frbl-toc-accent: #ff0000;"', $result );
	}

	/**
	 * Heading discovery must not depend on the block registering the
	 * headings — a heading with arbitrary classes (as GenerateBlocks or any
	 * other block would output) must be picked up just the same.
	 */
	public function test_discovers_headings_regardless_of_their_originating_block() {
		$content = $this->placeholder()
			. '<h2 class="gb-headline gb-headline-text-abc123" data-something="x">Custom Markup Heading</h2>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringContainsString( 'href="#custom-markup-heading"', $result );
	}

	/**
	 * Two independent Table of Contents instances in the same content are
	 * each filtered by their own level range.
	 */
	public function test_two_toc_instances_are_filtered_independently() {
		$content = $this->placeholder( array( 'minLevel' => 2, 'maxLevel' => 2 ) )
			. '<h2>Top Level</h2><h3>Nested</h3>'
			. $this->placeholder( array( 'minLevel' => 3, 'maxLevel' => 3 ) );

		$result = $this->toc->inject_toc_into_content( $content );

		// First TOC: only the h2.
		$firstNav = substr( $result, 0, strpos( $result, '</nav>' ) + strlen( '</nav>' ) );
		$this->assertStringContainsString( 'href="#top-level"', $firstNav );
		$this->assertStringNotContainsString( 'href="#nested"', $firstNav );

		// Second TOC: only the h3.
		$secondNav = substr( $result, strpos( $result, '</nav>' ) + strlen( '</nav>' ) );
		$this->assertStringContainsString( 'href="#nested"', $secondNav );
	}

	/**
	 * render_placeholder() must emit exactly the markup shape that
	 * inject_toc_into_content() expects to find and replace.
	 */
	public function test_render_placeholder_output_is_understood_by_the_injector() {
		$placeholder = $this->toc->render_placeholder(
			array(
				'title'    => 'Contents',
				'minLevel' => 2,
				'maxLevel' => 3,
			)
		);

		$this->assertStringContainsString( 'frbl-toc-placeholder', $placeholder );
		$this->assertStringContainsString( 'data-frbl-toc-id="', $placeholder );

		$result = $this->toc->inject_toc_into_content( $placeholder . '<h2>Section</h2>' );

		$this->assertStringContainsString( 'aria-label="Contents"', $result );
		$this->assertStringContainsString( 'href="#section"', $result );
	}

	/**
	 * A heading with only markup and no real text (e.g. just an image)
	 * must not produce a broken, empty-text link.
	 */
	public function test_heading_with_no_text_content_is_skipped() {
		$content = $this->placeholder() . '<h2><img src="x.jpg" alt=""></h2><h3>Real Heading</h3>';

		$result = $this->toc->inject_toc_into_content( $content );

		$this->assertStringContainsString( 'href="#real-heading"', $result );
		$this->assertSame( 1, substr_count( $result, '<a class="frbl-toc__link"' ) );
	}
}
