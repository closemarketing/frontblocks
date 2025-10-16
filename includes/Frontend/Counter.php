<?php
/**
 * Counter module for FrontBlocks (GenerateBlocks Headline counter effect).
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Counter class.
 *
 * @since 1.0.0
 */
class Counter {
   /**
    * Constructor.
    */
   public function __construct() {
      // 1. Registro inicial de assets (usa el hook 'init' para wp_register_script)
      add_action( 'init', array( $this, 'register_assets' ) );
      
      // 2. Encola los scripts del editor (backend/Gutenberg).
      add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
      
      // 3. Filtro para añadir la clase CSS al bloque en el frontend.
      add_filter( 'generateblocks_attr_headline', array( $this, 'add_counter_class_attribute' ), 10, 2 );
      // 4. Encola el script de animación en el footer (frontend).
      add_action( 'wp_footer', array( $this, 'enqueue_counter_script' ) );
   }

   /**
    * Registra los scripts y estilos del editor.
    *
    * @return void
    */
   public function register_assets() {
        // Dependencias para el script del editor
        $dependencies = array(
            'wp-blocks',
            'wp-element',
            'wp-editor',
            'wp-components',
            'wp-compose',
            'wp-hooks',
            'wp-data',
        );

        // 1. Registra el script del editor (resultado de npm run build:counter)
        wp_register_script(
            'frontblocks-counter-editor',
            FRBL_PLUGIN_URL . 'assets/counter/frontblocks-counter.js', // El archivo compilado
            $dependencies,
            FRBL_VERSION,
            true // Encola en el footer si se usa wp_enqueue_script
        );

        // 2. Registra el CSS (para estilos específicos en el editor o la vista previa)
        wp_register_style( 
            'frontblocks-counter-styles', 
            FRBL_PLUGIN_URL . 'assets/counter/frontblocks-counter.css', 
            array(), 
            FRBL_VERSION 
        );
    }

   /**
    * Encola los scripts y estilos registrados en el hook 'enqueue_block_editor_assets'.
    *
    * @return void
    */
   public function enqueue_editor_assets() {
      wp_enqueue_script( 'frontblocks-counter-editor' );
      wp_enqueue_style( 'frontblocks-counter-styles' );
   }
   
   /**
    * Añade la clase CSS 'count-up-container' al elemento Headline
    * si la opción `isCounterActive` está habilitada en el editor.
    *
    * @param array $attributes Array de atributos actuales de la etiqueta (ej: h2).
    * @param array $context    Contexto del bloque, que incluye los atributos.
    * @return array Atributos modificados.
    */
   public function add_counter_class_attribute( $attributes, $context ) {
      $is_counter_active = isset( $context['isCounterActive'] ) && $context['isCounterActive'];

      if ( $is_counter_active ) {
         $attributes['class'] = isset( $attributes['class'] ) ? $attributes['class'] . ' count-up-container' : 'count-up-container';
      }

      return $attributes;
   }

   /**
    * Encola el script de JavaScript responsable de la animación de conteo
    * para el frontend.
    *
    * @return void
    */
   public function enqueue_counter_script() {
      $script_handle = 'frontblocks-counter-runtime';
      $script_path   = FRBL_PLUGIN_URL . 'assets/counter/frontblocks-counter-runtime.js';
      $script_deps   = array();
      $script_version = FRBL_VERSION;

      // Usamos wp_script_is para asegurar que no se encola dos veces.
      if ( ! wp_script_is( $script_handle, 'enqueued' ) ) {
         wp_enqueue_script( $script_handle, $script_path, $script_deps, $script_version, true );
      }
   }
}