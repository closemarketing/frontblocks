<?php
/**
 * Table of Contents block module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

use WP_Block_Type_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * TableOfContents class.
 *
 * Registers a dynamic "Table of Contents" block. Because the block usually
 * appears before the headings it needs to link to, it can't discover them
 * from within its own render_callback (sibling blocks haven't rendered
 * yet). Instead, render_callback() outputs a small placeholder carrying the
 * block's config, and a later the_content filter — running after do_blocks()
 * has rendered the whole post — scans the final HTML for headings, assigns
 * stable anchors, and replaces every placeholder with the real navigation
 * markup.
 *
 * @since 1.0.0
 */
class TableOfContents {

	const BLOCK_NAME = 'frontblocks/table-of-contents';

	/**
	 * Minimum heading level considered (h1 is reserved for the post title).
	 */
	const MIN_ALLOWED_LEVEL = 2;

	/**
	 * Maximum heading level considered.
	 */
	const MAX_ALLOWED_LEVEL = 6;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'the_content', array( $this, 'inject_toc_into_content' ), 30 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Register the Table of Contents block.
	 *
	 * @return void
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$args = array(
			'editor_script'   => 'frontblocks-toc-option',
			'render_callback' => array( $this, 'render_placeholder' ),
			'attributes'      => array(
				'title'              => array(
					'type'    => 'string',
					'default' => __( 'Table of Contents', 'frontblocks' ),
				),
				'listStyle'          => array(
					'type'    => 'string',
					'default' => 'unordered',
				),
				'accentColor'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'collapsible'        => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'collapsedByDefault' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'sticky'             => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'minLevel'           => array(
					'type'    => 'number',
					'default' => 2,
				),
				'maxLevel'           => array(
					'type'    => 'number',
					'default' => 4,
				),
			),
		);

		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( self::BLOCK_NAME ) ) {
			register_block_type( self::BLOCK_NAME, $args );
		}
	}

	/**
	 * Render a placeholder carrying this instance's config. The real
	 * navigation markup is filled in later by inject_toc_into_content(),
	 * once every heading in the post has been rendered.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_placeholder( $attributes ) {
		$config = array(
			'title'              => isset( $attributes['title'] ) ? (string) $attributes['title'] : __( 'Table of Contents', 'frontblocks' ),
			'listStyle'          => $this->sanitize_list_style( $attributes['listStyle'] ?? 'unordered' ),
			'accentColor'        => isset( $attributes['accentColor'] ) ? sanitize_text_field( $attributes['accentColor'] ) : '',
			'collapsible'        => ! empty( $attributes['collapsible'] ),
			'collapsedByDefault' => ! empty( $attributes['collapsedByDefault'] ),
			'sticky'             => ! empty( $attributes['sticky'] ),
			'minLevel'           => $this->clamp_level( $attributes['minLevel'] ?? 2 ),
			'maxLevel'           => $this->clamp_level( $attributes['maxLevel'] ?? 4 ),
		);

		if ( $config['minLevel'] > $config['maxLevel'] ) {
			list( $config['minLevel'], $config['maxLevel'] ) = array( $config['maxLevel'], $config['minLevel'] );
		}

		$id = wp_unique_id( 'frbl-toc-' );

		return sprintf(
			'<div class="frbl-toc-placeholder" data-frbl-toc-id="%1$s" data-frbl-toc-config="%2$s"></div>',
			esc_attr( $id ),
			esc_attr( wp_json_encode( $config ) )
		);
	}

	/**
	 * Replace every Table of Contents placeholder in the final rendered
	 * content with real navigation markup, after assigning stable, unique
	 * anchors to every heading that doesn't already have one.
	 *
	 * Runs at priority 30 on `the_content` — after do_blocks() (priority 9)
	 * has rendered every block, including headings that come after this
	 * one in the post.
	 *
	 * @param string $content Fully rendered post content.
	 * @return string
	 */
	public function inject_toc_into_content( $content ) {
		if ( false === strpos( $content, 'frbl-toc-placeholder' ) ) {
			return $content;
		}

		$headings = array();
		$used_ids = array();

		$content = $this->assign_heading_ids( $content, $headings, $used_ids );

		return preg_replace_callback(
			'/<div class="frbl-toc-placeholder" data-frbl-toc-id="([^"]*)" data-frbl-toc-config="([^"]*)"><\/div>/',
			function ( $matches ) use ( $headings ) {
				$config = json_decode( html_entity_decode( $matches[2], ENT_QUOTES ), true );
				if ( ! is_array( $config ) ) {
					return '';
				}

				return $this->build_toc_markup( $config, $headings );
			},
			$content
		);
	}

	/**
	 * Find every heading in the content, assign a stable unique id (and a
	 * tabindex, so keyboard navigation can focus it) to any heading that
	 * doesn't already have one, and collect them for the TOC to link to.
	 * Existing author-supplied ids are always preserved untouched.
	 *
	 * @param string $content  Rendered content.
	 * @param array  $headings Populated by reference with { level, id, text }.
	 * @param array  $used_ids Populated by reference with every id already in use.
	 * @return string Content with heading ids/tabindex added where missing.
	 */
	private function assign_heading_ids( $content, array &$headings, array &$used_ids ) {
		return preg_replace_callback(
			'/<h([1-6])((?:\s[^>]*)?)>(.*?)<\/h\1>/is',
			function ( $matches ) use ( &$headings, &$used_ids ) {
				$level      = (int) $matches[1];
				$attributes = $matches[2];
				$inner_html = $matches[3];
				$text       = trim( wp_strip_all_tags( $inner_html ) );

				if ( '' === $text ) {
					return $matches[0];
				}

				if ( preg_match( '/\bid=["\']([^"\']+)["\']/i', $attributes, $id_match ) ) {
					$id              = $id_match[1];
					$used_ids[ $id ] = true;
				} else {
					$id              = $this->generate_unique_id( $text, $used_ids );
					$used_ids[ $id ] = true;
					$attributes     .= ' id="' . esc_attr( $id ) . '"';
				}

				if ( ! preg_match( '/\btabindex=/i', $attributes ) ) {
					// data-frbl-toc-heading scopes the focus-visible style in
					// frontblocks-toc.css to only these headings, so adding
					// tabindex="-1" here doesn't affect focus styling on
					// unrelated tabindex="-1" elements elsewhere on the site.
					$attributes .= ' tabindex="-1" data-frbl-toc-heading="1"';
				}

				$headings[] = array(
					'level' => $level,
					'id'    => $id,
					'text'  => $text,
				);

				return '<h' . $level . $attributes . '>' . $inner_html . '</h' . $level . '>';
			},
			$content
		);
	}

	/**
	 * Generate a slug for a heading's text, disambiguating it from any id
	 * already in use (including author-supplied ones) with a numeric suffix.
	 *
	 * @param string $text     Heading text.
	 * @param array  $used_ids Ids already in use, keyed by id.
	 * @return string
	 */
	private function generate_unique_id( $text, array $used_ids ) {
		$base = sanitize_title( $text );
		if ( '' === $base ) {
			$base = 'section';
		}

		$id     = $base;
		$suffix = 2;
		while ( isset( $used_ids[ $id ] ) ) {
			$id = $base . '-' . $suffix;
			++$suffix;
		}

		return $id;
	}

	/**
	 * Build the final navigation markup for one Table of Contents instance.
	 *
	 * @param array $config   Decoded block config.
	 * @param array $headings All headings found in the content.
	 * @return string
	 */
	private function build_toc_markup( array $config, array $headings ) {
		$min_level = $this->clamp_level( $config['minLevel'] ?? 2 );
		$max_level = $this->clamp_level( $config['maxLevel'] ?? 4 );

		$entries = array_values(
			array_filter(
				$headings,
				function ( $heading ) use ( $min_level, $max_level ) {
					return $heading['level'] >= $min_level && $heading['level'] <= $max_level;
				}
			)
		);

		if ( empty( $entries ) ) {
			return '';
		}

		$title       = isset( $config['title'] ) && '' !== $config['title'] ? $config['title'] : __( 'Table of Contents', 'frontblocks' );
		$list_style  = $this->sanitize_list_style( $config['listStyle'] ?? 'unordered' );
		$list_tag    = 'ordered' === $list_style ? 'ol' : 'ul';
		$sticky      = ! empty( $config['sticky'] );
		$collapsible = ! empty( $config['collapsible'] );

		$list_items = '';
		foreach ( $entries as $entry ) {
			$list_items .= sprintf(
				'<li class="frbl-toc__item frbl-toc__item--level-%1$d"><a class="frbl-toc__link" href="#%2$s">%3$s</a></li>',
				$entry['level'],
				esc_attr( $entry['id'] ),
				esc_html( $entry['text'] )
			);
		}

		$list_html = sprintf(
			'<%1$s class="frbl-toc__list frbl-toc__list--%2$s">%3$s</%1$s>',
			$list_tag,
			esc_attr( $list_style ),
			$list_items
		);

		$wrapper_style = '';
		if ( ! empty( $config['accentColor'] ) ) {
			$wrapper_style = ' style="--frbl-toc-accent: ' . esc_attr( $config['accentColor'] ) . ';"';
		}

		$title_html = esc_html( $title );

		if ( $collapsible ) {
			$open = empty( $config['collapsedByDefault'] ) ? ' open' : '';

			return sprintf(
				'<details class="frbl-toc frbl-toc--collapsible%1$s"%2$s%3$s><summary class="frbl-toc__title">%4$s</summary><nav class="frbl-toc__nav" aria-label="%5$s">%6$s</nav></details>',
				$sticky ? ' frbl-toc--sticky' : '',
				$open,
				$wrapper_style,
				$title_html,
				esc_attr( $title ),
				$list_html
			);
		}

		return sprintf(
			'<nav class="frbl-toc%1$s" aria-label="%2$s"%3$s><p class="frbl-toc__title">%4$s</p>%5$s</nav>',
			$sticky ? ' frbl-toc--sticky' : '',
			esc_attr( $title ),
			$wrapper_style,
			$title_html,
			$list_html
		);
	}

	/**
	 * Sanitize the listStyle attribute to one of the supported values.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private function sanitize_list_style( $value ) {
		return in_array( $value, array( 'unordered', 'ordered', 'plain' ), true ) ? $value : 'unordered';
	}

	/**
	 * Clamp a heading level to the supported h2-h6 range.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	private function clamp_level( $value ) {
		return max( self::MIN_ALLOWED_LEVEL, min( self::MAX_ALLOWED_LEVEL, (int) $value ) );
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_style(
			'frontblocks-toc-style',
			FRBL_PLUGIN_URL . 'assets/table-of-contents/frontblocks-toc.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-toc-option',
			FRBL_PLUGIN_URL . 'assets/table-of-contents/frontblocks-toc-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);

		wp_set_script_translations( 'frontblocks-toc-option', 'frontblocks' );
	}

	/**
	 * Enqueue frontend assets, only when the block is present on the page.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		if ( ! has_block( self::BLOCK_NAME ) ) {
			return;
		}

		wp_enqueue_style(
			'frontblocks-toc-style',
			FRBL_PLUGIN_URL . 'assets/table-of-contents/frontblocks-toc.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-toc-frontend',
			FRBL_PLUGIN_URL . 'assets/table-of-contents/frontblocks-toc-frontend.js',
			array(),
			FRBL_VERSION,
			true
		);
	}
}
