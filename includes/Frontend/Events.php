<?php
/**
 * Events module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Events class.
 *
 * @since 1.0.0
 */
class Events {
	/**
	 * Option key for events feature.
	 *
	 * @var string
	 */
	private $option_enable_events = 'enable_events';

	/**
	 * Option key for events type (cpt or posts).
	 *
	 * @var string
	 */
	private $option_events_type = 'events_type';

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! $this->is_events_enabled() ) {
			return;
		}

		$events_type = $this->get_events_type();

		if ( 'cpt' === $events_type ) {
			// Register CPT and taxonomy.
			add_action( 'init', array( $this, 'register_cpt_event' ) );
			add_action( 'init', array( $this, 'register_taxonomy_event_category' ) );

			// Meta boxes for CPT.
			add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
			add_action( 'save_post_event', array( $this, 'save_meta' ) );
		} else {
			// Meta boxes for posts.
			add_action( 'add_meta_boxes', array( $this, 'add_metaboxes_posts' ) );
			add_action( 'save_post', array( $this, 'save_meta_posts' ) );
		}
	}

	/**
	 * Check if events are enabled.
	 *
	 * @return bool
	 */
	private function is_events_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options[ $this->option_enable_events ] ?? false );
	}

	/**
	 * Get events type (cpt or posts).
	 *
	 * @return string
	 */
	private function get_events_type() {
		$options = get_option( 'frontblocks_settings', array() );
		return sanitize_text_field( $options[ $this->option_events_type ] ?? 'cpt' );
	}

	/**
	 * Register events custom post type.
	 *
	 * @return void
	 */
	public function register_cpt_event() {
		$labels = array(
			'name'          => __( 'Eventos', 'frontblocks' ),
			'singular_name' => __( 'Evento', 'frontblocks' ),
			'menu_name'     => __( 'Eventos', 'frontblocks' ),
			'all_items'     => __( 'Todos los eventos', 'frontblocks' ),
			'add_new'       => __( 'Añadir evento', 'frontblocks' ),
			'add_new_item'  => __( 'Añadir nuevo evento', 'frontblocks' ),
			'edit_item'     => __( 'Editar evento', 'frontblocks' ),
			'new_item'      => __( 'Nuevo evento', 'frontblocks' ),
			'view_item'     => __( 'Ver evento', 'frontblocks' ),
			'search_items'  => __( 'Buscar evento', 'frontblocks' ),
			'not_found'     => __( 'No se han encontrado eventos', 'frontblocks' ),
		);

		$args = array(
			'label'         => __( 'Eventos', 'frontblocks' ),
			'labels'        => $labels,
			'public'        => true,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'has_archive'   => true,
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-calendar-alt',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'       => array( 'slug' => 'evento' ),
			'taxonomies'    => array( 'post_tag', 'event_category' ),
		);

		register_post_type( 'event', $args );
	}

	/**
	 * Register event category taxonomy.
	 *
	 * @return void
	 */
	public function register_taxonomy_event_category() {
		$labels = array(
			'name'          => __( 'Categorías de eventos', 'frontblocks' ),
			'singular_name' => __( 'Categoría', 'frontblocks' ),
			'menu_name'     => __( 'Categorías', 'frontblocks' ),
		);

		$args = array(
			'label'             => __( 'Categorías', 'frontblocks' ),
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'categorias-eventos',
				'with_front' => false,
			),
		);

		register_taxonomy( 'event_category', array( 'event' ), $args );
	}

	/**
	 * Add meta boxes for event data (CPT).
	 *
	 * @return void
	 */
	public function add_metaboxes() {
		add_meta_box(
			'frbl_event_metabox',
			__( 'Datos del Evento', 'frontblocks' ),
			array( $this, 'render_metabox' ),
			'event',
			'normal',
			'default'
		);
	}

	/**
	 * Add meta boxes for event data (Posts).
	 *
	 * @return void
	 */
	public function add_metaboxes_posts() {
		add_meta_box(
			'frbl_event_metabox',
			__( 'Datos del Evento', 'frontblocks' ),
			array( $this, 'render_metabox' ),
			'post',
			'normal',
			'default'
		);
	}

	/**
	 * Render metabox content.
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function render_metabox( $post ) {
		$all_day          = get_post_meta( $post->ID, 'all_day', true );
		$start_date       = get_post_meta( $post->ID, 'start_date', true );
		$start_time       = get_post_meta( $post->ID, 'start_time', true );
		$end_date         = get_post_meta( $post->ID, 'end_date', true );
		$end_time         = get_post_meta( $post->ID, 'end_time', true );
		$cost             = get_post_meta( $post->ID, 'cost', true );
		$web              = get_post_meta( $post->ID, 'web', true );
		$poster_evento    = get_post_meta( $post->ID, 'poster_evento', true );
		$direccion_evento = get_post_meta( $post->ID, 'direccion_evento', true );

		wp_nonce_field( 'frbl_event_save_meta', 'frbl_event_meta_nonce' );
		?>

		<style>
			#frbl_event_metabox .frbl-grid {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 25px;
				max-width: 1000px;
			}

			#frbl_event_metabox .frbl-field {
				display: flex;
				flex-direction: column;
				margin-bottom: 15px;
			}

			#frbl_event_metabox label {
				font-weight: 600;
				margin-bottom: 6px;
			}

			#frbl_event_metabox input[type="text"],
			#frbl_event_metabox input[type="number"],
			#frbl_event_metabox input[type="url"] {
				padding: 6px 10px;
				font-size: 14px;
				width: 100%;
				max-width: 380px;
			}

			#frbl_event_metabox input[type="date"],
			#frbl_event_metabox input[type="time"] {
				padding: 4px 6px;
				font-size: 13px;
				width: 140px; 
			}

			#frbl_event_metabox .full {
				grid-column: span 2;
			}
		</style>

		<div id="frbl_event_metabox" class="frbl-grid">

			<div class="frbl-field">
				<label><?php esc_html_e( 'Evento todo el día', 'frontblocks' ); ?></label>
				<input type="checkbox" name="all_day" value="1" <?php checked( $all_day, '1' ); ?> />
			</div>

			<div class="frbl-field">
				<label><?php esc_html_e( 'Coste', 'frontblocks' ); ?></label>
				<input type="number" step="0.01" name="cost" value="<?php echo esc_attr( $cost ); ?>">
			</div>

			<div class="frbl-field">
				<label><?php esc_html_e( 'Fecha inicio', 'frontblocks' ); ?></label>
				<input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
			</div>

			<div class="frbl-field">
				<label><?php esc_html_e( 'Hora inicio', 'frontblocks' ); ?></label>
				<input type="time" name="start_time" value="<?php echo esc_attr( $start_time ); ?>">
			</div>

			<div class="frbl-field">
				<label><?php esc_html_e( 'Fecha fin', 'frontblocks' ); ?></label>
				<input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
			</div>

			<div class="frbl-field">
				<label><?php esc_html_e( 'Hora fin', 'frontblocks' ); ?></label>
				<input type="time" name="end_time" value="<?php echo esc_attr( $end_time ); ?>">
			</div>

			<div class="frbl-field">
				<label><?php esc_html_e( 'Web del evento', 'frontblocks' ); ?></label>
				<input type="url" name="web" value="<?php echo esc_attr( $web ); ?>">
			</div>

			<div class="frbl-field">
				<label><?php esc_html_e( 'Póster del evento (url)', 'frontblocks' ); ?></label>
				<input type="url" name="poster_evento" value="<?php echo esc_attr( $poster_evento ); ?>">
			</div>

			<div class="frbl-field full">
				<label><?php esc_html_e( 'Dirección', 'frontblocks' ); ?></label>
				<input type="text" name="direccion_evento" value="<?php echo esc_attr( $direccion_evento ); ?>" style="max-width:100%;">
			</div>

		</div>

		<?php
	}

	/**
	 * Save metabox data (CPT).
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['frbl_event_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['frbl_event_meta_nonce'] ) ), 'frbl_event_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$fields = array(
			'all_day'          => isset( $_POST['all_day'] ) ? '1' : '0',
			'start_date'       => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '',
			'start_time'       => isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '',
			'end_date'         => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '',
			'end_time'         => isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '',
			'cost'             => isset( $_POST['cost'] ) ? sanitize_text_field( wp_unslash( $_POST['cost'] ) ) : '',
			'web'              => isset( $_POST['web'] ) ? esc_url_raw( wp_unslash( $_POST['web'] ) ) : '',
			'poster_evento'    => isset( $_POST['poster_evento'] ) ? esc_url_raw( wp_unslash( $_POST['poster_evento'] ) ) : '',
			'direccion_evento' => isset( $_POST['direccion_evento'] ) ? sanitize_text_field( wp_unslash( $_POST['direccion_evento'] ) ) : '',
		);

		foreach ( $fields as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Save metabox data (Posts).
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta_posts( $post_id ) {
		// Only save if metabox nonce is present (meaning user is editing a post with event data).
		if ( ! isset( $_POST['frbl_event_meta_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['frbl_event_meta_nonce'] ) ), 'frbl_event_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Only save for posts.
		if ( 'post' !== get_post_type( $post_id ) ) {
			return;
		}

		$fields = array(
			'all_day'          => isset( $_POST['all_day'] ) ? '1' : '0',
			'start_date'       => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '',
			'start_time'       => isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '',
			'end_date'         => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '',
			'end_time'         => isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '',
			'cost'             => isset( $_POST['cost'] ) ? sanitize_text_field( wp_unslash( $_POST['cost'] ) ) : '',
			'web'              => isset( $_POST['web'] ) ? esc_url_raw( wp_unslash( $_POST['web'] ) ) : '',
			'poster_evento'    => isset( $_POST['poster_evento'] ) ? esc_url_raw( wp_unslash( $_POST['poster_evento'] ) ) : '',
			'direccion_evento' => isset( $_POST['direccion_evento'] ) ? sanitize_text_field( wp_unslash( $_POST['direccion_evento'] ) ) : '',
		);

		foreach ( $fields as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}
}

