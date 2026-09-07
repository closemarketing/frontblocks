<?php
/**
 * Tests for FrontBlocks\Frontend\ProductCategories.
 *
 * Note: this test environment does not load WooCommerce, so
 * class_exists( 'WooCommerce' ) is always false here. That is itself a real,
 * deliberate code path in the class (the block registration and rendering
 * are both gated on WooCommerce being active) and is what these tests cover.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ProductCategories;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ProductCategoriesTest extends TestCase {

	/**
	 * @var ProductCategories
	 */
	private $product_categories;

	public function set_up() {
		parent::set_up();
		$this->product_categories = new ProductCategories();
	}

	public function test_constructor_registers_the_expected_hooks() {
		$this->assertSame( 20, has_action( 'init', array( $this->product_categories, 'register_product_categories_block' ) ) );
		$this->assertSame( 20, has_action( 'init', array( $this->product_categories, 'enable_rest_api_for_product_cat' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->product_categories, 'enqueue_block_editor_assets' ) ) );
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', array( $this->product_categories, 'enqueue_frontend_styles' ) ) );
		$this->assertNotFalse( has_action( 'rest_api_init', array( $this->product_categories, 'register_rest_fields' ) ) );
	}

	public function test_enable_rest_api_for_product_cat_configures_existing_taxonomy() {
		global $wp_taxonomies;

		$wp_taxonomies['product_cat'] = (object) array(
			'show_in_rest'          => false,
			'rest_base'             => '',
			'rest_controller_class' => '',
		);

		$this->product_categories->enable_rest_api_for_product_cat();

		$this->assertTrue( $wp_taxonomies['product_cat']->show_in_rest );
		$this->assertSame( 'product_cat', $wp_taxonomies['product_cat']->rest_base );
		$this->assertSame( 'WP_REST_Terms_Controller', $wp_taxonomies['product_cat']->rest_controller_class );

		unset( $wp_taxonomies['product_cat'] );
	}

	public function test_enable_rest_api_for_product_cat_is_noop_when_taxonomy_missing() {
		global $wp_taxonomies;
		unset( $wp_taxonomies['product_cat'] );

		// Should not raise a notice/error even though the taxonomy is absent.
		$this->product_categories->enable_rest_api_for_product_cat();

		$this->assertArrayNotHasKey( 'product_cat', $wp_taxonomies );
	}

	public function test_get_category_image_returns_null_without_thumbnail_or_woocommerce() {
		$term_id = self::factory()->term->create( array( 'taxonomy' => 'category' ) );

		$this->assertFalse( class_exists( 'WooCommerce' ), 'This test environment must not have WooCommerce active.' );
		$this->assertNull( $this->product_categories->get_category_image( array( 'id' => $term_id ) ) );
	}

	public function test_get_category_image_returns_null_when_thumbnail_points_to_missing_attachment() {
		$term_id = self::factory()->term->create( array( 'taxonomy' => 'category' ) );
		update_term_meta( $term_id, 'thumbnail_id', 999999999 );

		$this->assertNull( $this->product_categories->get_category_image( array( 'id' => $term_id ) ) );
	}

	public function test_register_product_categories_block_is_noop_without_woocommerce() {
		$this->product_categories->register_product_categories_block();

		$this->assertFalse(
			WP_Block_Type_Registry::get_instance()->is_registered( 'frontblocks/product-categories' )
		);
	}

	public function test_render_product_categories_block_returns_empty_without_woocommerce() {
		$result = $this->product_categories->render_product_categories_block( array( 'count' => 5 ) );

		$this->assertSame( '', $result );
	}
}
