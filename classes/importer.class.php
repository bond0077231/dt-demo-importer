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
        foreach ($this->items as $item => $value ) :
          $opt = get_option($this->opt_id);

          $imported_class = '';
          $btn_text = '';
          $status = '';
          if (!empty($opt[$item])) {
            $imported_class = 'imported';
            $btn_text .= __( 'Re-Import', 'dt-importer' );
            $status .= __( 'Imported', 'dt-importer' );
          } else {
            $btn_text .= __( 'Import', 'dt-importer' );
            $status .= __( 'Not Imported', 'dt-importer' );
          }
      ?>
        <div class="dt-demo-item <?php echo esc_attr($imported_class); ?>" data-dt-importer>
          <div class="dt-demo-screenshot">
          <div class="dt-tag">
            <?php echo esc_attr($status); ?>
          </div>
            <?php
              $image_url = '';
              if (file_exists(DT_IMPORTER_CONTENT_DIR . $item . '/screenshot.png')) {
                $image_url = DT_IMPORTER_CONTENT_URI . $item . '/screenshot.png';
              } else {
                $image_url = DT_IMPORTER_URI . '/assets/img/screenshot.png';
              }
            ?>
            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr($value['title']); ?>">
          </div>
          <h2 class="dt-demo-name"><?php echo esc_attr($value['title']); ?></h2>
          <div class="dt-demo-actions">
            <a class="button button-secondary" href="#" data-import="<?php echo esc_attr($item); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>"><?php echo esc_attr($btn_text); ?></a>
            <a class="button button-primary" target="_blank" href="<?php echo esc_url($value['preview_url']); ?>"><?php _e( 'Preview', 'dt-importer' ); ?></a>
          </div>
          
          <div class="dt-importer-response"><span class="dismiss" title="Dismis this messages.">X</span></div>
        </div><!-- /.dt-demo-item -->
      <?php endforeach; ?>
      <div class="clear"></div>
      <div class="dt-importer-footer">
        <?php _e( 'One-Click Installer Brought you by <a href="https://github.com/AminulBD/dt-demo-importer">Aminul Islam</a>', 'dt-importer' ); ?>
      </div>
    </div><!-- /.dt-demo-browser -->
  </div><!-- /.wrap -->
  <?php
  }

  /**
   * Import Proccess
   */
  public function import_process() {
    $id = $_POST['id'];

    // Import XML Data
    $this->import_xml_data();

    // Setup Option Data from Codestar
    $this->import_cs_options_data();

    // Setup Reading
    $this->set_pages_for_reading();

    // Setup Menu
    if (isset($this->items[$id]['menus'])) {
      $this->set_menu();
    }
    die();
  }


  /**
   * Import XML data by WordPress Importer
   */
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

  /**
   * Update Codestar Framework Options Data
   */
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

  /**
   * Set Homepage and Front page
   */
  public function set_pages_for_reading() {
    $id = $_POST['id'];

    // Set Home
    if (isset($this->items[$id]['front_page'])) {
      $page = get_page_by_title($this->items[$id]['front_page']);

      if ( isset( $page->ID ) ) {
        update_option( 'page_on_front', $page->ID );
        update_option( 'show_on_front', 'page' );
      }
    }

    // Set Blog
    if (isset($this->items[$id]['blog_page'])) {
      $page = get_page_by_title($this->items[$id]['blog_page']);

      if ( isset( $page->ID ) ) {
        update_option( 'page_for_posts', $page->ID );
        update_option( 'show_on_front', 'page' );
      }
    }
  }

  /**
   * Setup Menu
   */
  public function set_menu() {
    $id = $_POST['id'];
    
    // Store All Menu
    $menu_locations = array();

    foreach ($this->items[$id]['menus'] as $key => $value) {
      $menu = get_term_by( 'name', $value, 'nav_menu' );
      if (isset($menu->term_id)) {
        $menu_locations[$key] = $menu->term_id;
      }
    }

    // Set Menu If has
    if (isset($menu_locations)) {
      set_theme_mod( 'nav_menu_locations', $menu_locations );
    }
  }

}