<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Framework Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class DT_Demo_Importer extends DT_Demo_Importer_Abstract {
  /**
   *
   * option database/data name
   * @access public
   * @var string
   *
   */
  public $opt_id = '_dt_importer';
  /**
   *
   * framework option database/data name
   * @access public
   * @var string
   *
   */
  public $framework_id = '_cs_options';
  /**
   *
   * demo items
   * @access public
   * @var array
   *
   */
  public $items = array();
  /**
   *
   * instance
   * @access private
   * @var class
   *
   */
  private static $instance = null;
  // run framework construct
  public function __construct( $settings, $items ) {
    $this->settings = apply_filters( 'dt_importer_settings', $settings );
    $this->items    = apply_filters( 'dt_importer_items', $items );
    if( ! empty( $this->items ) ) {
      $this->addAction( 'admin_menu', 'admin_menu' );
      $this->addAction( 'wp_ajax_dt_demo_importer', 'import_process' );
    }
  }
  // instance
  public static function instance( $settings = array(), $items = array() ) {
    if ( is_null( self::$instance ) ) {
      self::$instance = new self( $settings, $items );
    }
    return self::$instance;
  }

  // adding option page
  public function admin_menu() {
    $defaults_menu_args = array(
      'menu_parent'     => '',
      'menu_title'      => '',
      'menu_type'       => '',
      'menu_slug'       => '',
      'menu_icon'       => '',
      'menu_capability' => 'manage_options',
      'menu_position'   => null,
    );
    $args = wp_parse_args( $this->settings, $defaults_menu_args );
    if( $args['menu_type'] == 'add_submenu_page' ) {
      call_user_func( $args['menu_type'], $args['menu_parent'], $args['menu_title'], $args['menu_title'], $args['menu_capability'], $args['menu_slug'], array( &$this, 'admin_page' ) );
    } else {
      call_user_func( $args['menu_type'], $args['menu_title'], $args['menu_title'], $args['menu_capability'], $args['menu_slug'], array( &$this, 'admin_page' ), $args['menu_icon'], $args['menu_position'] );
    }
  }
  // output demo items
  public function admin_page() {
    $nonce = wp_create_nonce('dt_importer');
  ?>
  <div class="wrap dt-importer">
    <h2><?php _e( 'Decent Themes Demo Importer', 'dt-importer' ); ?></h2>
    <div class="dt-demo-browser">
      <?php
        foreach ($this->items as $item ) :
          $opt = get_option($this->opt_id);

          $imported_class = '';
          $btn_text = '';
          $status = '';
          if (!empty($opt[$item['id']])) {
            $imported_class = 'imported';
            $btn_text .= __( 'Re-Import', 'dt-importer' );
            $status .= __( 'Imported', 'dt-importer' );
          } else {
            $btn_text .= __( 'Import', 'dt-importer' );
            $status .= __( 'No Imported', 'dt-importer' );
          }
      ?>
        <div class="dt-demo-item <?php echo esc_attr($imported_class); ?>" data-dt-importer>
          <div class="dt-demo-screenshot">
          <div class="dt-tag">
            <?php echo esc_attr($status); ?>
          </div>
            <?php
              $image_url = '';
              if (file_exists(DT_IMPORTER_CONTENT_DIR . $item['id'] . '/screenshot.png')) {
                $image_url = DT_IMPORTER_CONTENT_URI . $item['id'] . '/screenshot.png';
              } else {
                $image_url = DT_IMPORTER_URI . '/assets/img/screenshot.png';
              }
            ?>
            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr($item['title']); ?>">
          </div>
          <h2 class="dt-demo-name"><?php echo esc_attr($item['title']); ?></h2>
          <div class="dt-demo-actions">
            <a class="button button-secondary" href="#" data-import="<?php echo esc_attr($item['id']); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>"><?php echo esc_attr($btn_text); ?></a>
            <a class="button button-primary" target="_blank" href="<?php echo esc_url($item['preview_url']); ?>"><?php _e( 'Preview', 'dt-importer' ); ?></a>
          </div>
          
          <div class="dt-importer-response"><span class="dismiss" title="Dismis this messages.">X</span></div>
        </div><!-- /.dt-demo-item -->
      <?php endforeach; ?>
      <div class="clear"></div>
    </div><!-- /.dt-demo-browser -->
  </div><!-- /.wrap -->
  <?php
  }

  // Import Proccess
  public function import_process() {
    $this->import_xml_data();
    $this->import_cs_options_data();
    die();
  }
  // Insert XML Data
  public function import_xml_data() {

    if ( ! wp_verify_nonce( $_POST['nonce'], 'dt_importer' ) )
      echo die( 'Authentication Error!!!' );

    $id = $_POST['id'];
    $file = DT_IMPORTER_CONTENT_DIR . $id . '/content.xml';

    if ( !defined('WP_LOAD_IMPORTERS') ) define('WP_LOAD_IMPORTERS', true);
      require_once ABSPATH . 'wp-admin/includes/import.php';
      $importer_error = false;
      if ( !class_exists( 'WP_Importer' ) ) {
          $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
          if ( file_exists( $class_wp_importer ) ){
              require_once($class_wp_importer);
          } else {
              $importer_error = true;
          }
      }
      if ( !class_exists( 'WP_Import' ) ) {
          $class_wp_import = dirname( __FILE__ ) .'/wordpress-importer.php';
          if ( file_exists( $class_wp_import ) )
              require_once($class_wp_import);
          else
              $importer_error = true;
      }
      if($importer_error){
          die(__("Error on import", 'dt-importer'));
      } else {
        if(!is_file( $file )){
            esc_html_e("File Error!!!", 'dt-importer');
        } else {
          $wp_import = new WP_Import();
          $wp_import->fetch_attachments = true;
          $wp_import->import( $file );
          $options = get_option($this->opt_id);
          $options[$id] = true;
          update_option( $this->opt_id, $options );
      }
    }

  }

  public function import_cs_options_data() {
    $id = $_POST['id'];
    $file = DT_IMPORTER_CONTENT_DIR . $id . '/options.txt';

    if ( file_exists( $file ) ) {
      // Get file contents and decode
      $data = file_get_contents( $file );
      $decoded_data = cs_decode_string( $data );
      update_option( $this->framework_id, $decoded_data );
    }
  }

}