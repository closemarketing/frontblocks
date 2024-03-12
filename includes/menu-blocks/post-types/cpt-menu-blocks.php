<?php
/**
 * CPT Blocks Menu
 *
 * @package    WordPress
 * @author     Manuel Muñoz Rodríguez <manolo@close.marketing>
 * @copyright  2021 Closemarketing
 * @version    1.0
 */

add_action( 'init', 'mebl_cpt_menu_block' );
/**
 * Register Post Type POST Blocks Menu
 *
 * @return void
 **/
function mebl_cpt_menu_block() {
	$labels = array(
		'name'               => __( 'Blocks Menu', 'menu-blocks' ),
		'singular_name'      => __( 'Block Menu', 'menu-blocks' ),
		'add_new'            => __( 'Add new Block Menu', 'menu-blocks' ),
		'add_new_item'       => __( 'Add new Block Menu', 'menu-blocks' ),
		'edit_item'          => __( 'Edit Block Menu', 'menu-blocks' ),
		'new_item'           => __( 'New Block Menu', 'menu-blocks' ),
		'view_item'          => __( 'View Block Menu', 'menu-blocks' ),
		'search_items'       => __( 'Search Blocks Menu', 'menu-blocks' ),
		'not_found'          => __( 'Not found Blocks Menu', 'menu-blocks' ),
		'not_found_in_trash' => __( 'Not found Blocks Menu in trash', 'menu-blocks' ),
	);
	$args   = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'rewrite'            => false,
		'has_archive'        => false,
		'capability_type'    => 'page',
		'hierarchical'       => false,
		'menu_position'      => 15,
		'menu_icon'          => 'dashicons-menu-alt3',
		'supports'           => array( 'title', 'editor', 'revisions', 'page-attributes' ),
	);
	register_post_type( 'menu_block', $args );
}


// Register Meta box for post type menu_block.
add_action( 'add_meta_boxes', 'mebl_metabox_menu_block' );
add_action( 'save_post', 'mebl_save_metaboxes_menu_block' );

/**
 * Adds metabox
 *
 * @return void
 */
function mebl_metabox_menu_block() {
	add_meta_box(
		'menu_block',
		__( 'Options of Menu Block', 'menu-blocks' ),
		'mebl_metabox_show_menu_block',
		'menu_block',
		'side'
	);
}

/**
 * Metabox inputs for post type.
 *
 * @param object $post Post object.
 * @return void
 */
function mebl_metabox_show_menu_block( $post ) {
	$menu_block_url_parent = get_post_meta( $post->ID, 'menu_block_url_parent', true );
	$menu_block_css_class = get_post_meta( $post->ID, 'menu_block_css_class', true );
	?>
	<table>
		<tr><!-- URL web -->
			<td>
				<label for="menu_block_url_parent"><?php esc_html_e( 'Web', 'textdomain' ); ?></label>
			</td>
			<td>
				<input type="url" name="menu_block_url_parent" pattern="https://.*" size="20" value="<?php echo esc_attr( $menu_block_url_parent ); ?>">
			</td>
		</tr>
		<tr><!-- CSS class -->
			<td>
				<label for="menu_block_css_class"><?php esc_html_e( 'CSS Class', 'textdomain' ); ?></label>
			</td>
			<td>
				<input type="text" name="menu_block_css_class" value="<?php echo esc_attr( $menu_block_css_class ); ?>">
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save metaboxes
 *
 * @param int $post_id Post id.
 * @return void
 */
function mebl_save_metaboxes_menu_block( $post_id ) {
	if ( isset( $_POST['menu_block_url_parent'] ) ) {
		update_post_meta( $post_id, 'menu_block_url_parent', sanitize_text_field( $_POST['menu_block_url_parent'] ) );
	}

	if ( isset( $_POST['menu_block_css_class'] ) ) {
		update_post_meta( $post_id, 'menu_block_css_class', sanitize_text_field( $_POST['menu_block_css_class'] ) );
	}
}

add_filter( 'manage_edit-menu_block_columns', 'add_new_menu_block_columns' );
/**
 * Adds columns to post type menu_block
 *
 * @param array $menu_block_columns  Header of admin post type list.
 * @return array $menu_block_columns New elements for header.
 */
function add_new_menu_block_columns( $menu_block_columns ) {
	$new_columns['cb']    = '<input type="checkbox" />';
	$new_columns['title'] = __( 'Title', 'menu_blocks' );
	$new_columns['info']  = __( 'Info', 'menu_blocks' );

	return $new_columns;
}

add_action( 'manage_menu_block_posts_custom_column', 'manage_menu_block_columns', 10, 2 );
/**
 * Add columns content
 *
 * @param array $column_name Column name of actual.
 * @param array $id Post ID.
 * @return void
 */
function manage_menu_block_columns( $column_name, $id ) {
	$menu_block_url_parent = get_post_meta( $id, 'menu_block_url_parent', true );
	$menu_block_order      = get_post_field( 'menu_order', $id );

	switch ( $column_name ) {
		case 'info':
			echo '<p><strong>' . esc_html__( 'Order', 'menu-blocks' ) . ':</strong> ' . (int) $menu_block_order . '</p>';
			echo '<p><strong>' . esc_html__( 'URL', 'menu-blocks' ) . ':</strong>';
			echo ' <a href="' . esc_html( $menu_block_url_parent ) . '" target="_blank">' . esc_html( $menu_block_url_parent ) . '</a></p>';
			break;

		default:
			break;
	} // end switch
}