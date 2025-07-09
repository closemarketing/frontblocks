<?php
/**
 * Block registration for Content Link block.
 *
 * @author Closetechnology
 * @package FrontBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 */
function create_block_content_link_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_content_link_block_init' );
