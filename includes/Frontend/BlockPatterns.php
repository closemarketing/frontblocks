<?php
/**
 * Block Patterns Registration
 *
 * Registers custom block patterns for FrontBlocks.
 *
 * @package FrontBlocks
 */

namespace FrontBlocks\Frontend;

/**
 * Class BlockPatterns
 *
 * Handles registration of custom block patterns.
 */
class BlockPatterns {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_pattern_categories' ) );
		add_action( 'init', array( $this, 'register_patterns' ) );
	}

	/**
	 * Register custom pattern categories
	 *
	 * @return void
	 */
	public function register_pattern_categories() {
		// Register FrontBlocks category.
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category(
				'frontblocks',
				array(
					'label' => __( 'FrontBlocks', 'frontblocks' ),
				)
			);
		}
	}

	/**
	 * Register block patterns
	 *
	 * @return void
	 */
	public function register_patterns() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		// Register Carousel Hero Pattern.
		$this->register_carousel_hero_pattern();
	}

	/**
	 * Register Carousel Hero Pattern
	 *
	 * Full-width hero carousel with gradient backgrounds, headings, text and CTA buttons.
	 *
	 * @return void
	 */
	private function register_carousel_hero_pattern() {
		$pattern_content = '<!-- wp:group {"layout":{"type":"grid","columnCount":1,"minimumColumnWidth":null},"frblGridOption":"carousel","frblItemsToView":"1","frblLaptopToView":"1","frblTabletToView":"1","frblCarouselButtons":"arrows","frblCarouselButtonsPosition":"side"} -->
<div class="wp-block-group"><!-- wp:cover {"dimRatio":40,"isUserOverlayColor":true,"customGradient":"linear-gradient(135deg,rgb(224,15,15) 0%,rgb(225,15,15) 100%)","isDark":false,"sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"120px","bottom":"120px"}}}} -->
<div class="wp-block-cover alignfull is-light" style="padding-top:120px;padding-bottom:120px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim has-background-gradient" style="background:linear-gradient(135deg,rgb(224,15,15) 0%,rgb(225,15,15) 100%)"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading {"textAlign":"left","level":1,"style":{"typography":{"fontSize":"52px","fontWeight":"700","lineHeight":"1.2"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-left has-white-color has-text-color" style="font-size:52px;font-weight:700;line-height:1.2">Discover Amazing Content</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"left","style":{"typography":{"fontSize":"18px"},"spacing":{"margin":{"top":"20px"}}},"textColor":"white"} -->
<p class="has-text-align-left has-white-color has-text-color" style="margin-top:20px;font-size:18px">Experience the best features and benefits we have to offer. Join thousands of satisfied customers today.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"40px"}}}} -->
<div class="wp-block-buttons" style="margin-top:40px"><!-- wp:button {"backgroundColor":"primary","className":"is-style-fill","style":{"border":{"radius":"25px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"15px","bottom":"15px"}}}} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:25px;padding-top:15px;padding-right:35px;padding-bottom:15px;padding-left:35px">Get Started</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->

<!-- wp:cover {"dimRatio":40,"customOverlayColor":"#006f49","isUserOverlayColor":true,"isDark":false,"sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"120px","bottom":"120px"}}}} -->
<div class="wp-block-cover alignfull is-light" style="padding-top:120px;padding-bottom:120px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#006f49"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading {"textAlign":"left","level":1,"style":{"typography":{"fontSize":"52px","fontWeight":"700","lineHeight":"1.2"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-left has-white-color has-text-color" style="font-size:52px;font-weight:700;line-height:1.2">Transform Your Business</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"left","style":{"typography":{"fontSize":"18px"},"spacing":{"margin":{"top":"20px"}}},"textColor":"white"} -->
<p class="has-text-align-left has-white-color has-text-color" style="margin-top:20px;font-size:18px">Unlock powerful tools and resources designed to help you grow and succeed in your industry.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"40px"}}}} -->
<div class="wp-block-buttons" style="margin-top:40px"><!-- wp:button {"backgroundColor":"primary","className":"is-style-fill","style":{"border":{"radius":"25px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"15px","bottom":"15px"}}}} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:25px;padding-top:15px;padding-right:35px;padding-bottom:15px;padding-left:35px">Learn More</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->

<!-- wp:cover {"dimRatio":40,"customOverlayColor":"#1a237e","isUserOverlayColor":true,"isDark":false,"sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"120px","bottom":"120px"}}}} -->
<div class="wp-block-cover alignfull is-light" style="padding-top:120px;padding-bottom:120px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#1a237e"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading {"textAlign":"left","level":1,"style":{"typography":{"fontSize":"52px","fontWeight":"700","lineHeight":"1.2"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-left has-white-color has-text-color" style="font-size:52px;font-weight:700;line-height:1.2">Join Our Community</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"left","style":{"typography":{"fontSize":"18px"},"spacing":{"margin":{"top":"20px"}}},"textColor":"white"} -->
<p class="has-text-align-left has-white-color has-text-color" style="margin-top:20px;font-size:18px">Connect with like-minded professionals and access exclusive resources, events, and opportunities.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"40px"}}}} -->
<div class="wp-block-buttons" style="margin-top:40px"><!-- wp:button {"backgroundColor":"primary","className":"is-style-fill","style":{"border":{"radius":"25px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"15px","bottom":"15px"}}}} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" style="border-radius:25px;padding-top:15px;padding-right:35px;padding-bottom:15px;padding-left:35px">Join Now</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->';

		register_block_pattern(
			'frontblocks/carousel-hero',
			array(
				'title'       => __( 'Hero Carousel', 'frontblocks' ),
				'description' => __( 'Full-width hero carousel with smooth transitions, featuring gradient backgrounds, headings, and call-to-action buttons. Perfect for landing pages and promotional content.', 'frontblocks' ),
				'content'     => $pattern_content,
				'categories'  => array( 'frontblocks', 'featured', 'header' ),
				'keywords'    => array( 'carousel', 'hero', 'slider', 'cover', 'cta', 'gradient', 'header', 'banner' ),
				'viewportWidth' => 1440,
			)
		);
	}
}
