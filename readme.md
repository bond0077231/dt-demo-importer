# DT Demo Importer for WordPress themes
A One Click Demo Installer Class for WordPress Themes. This class developed for creating a quick installer for wordpress.


# Quick Usage

**Place this code in functions.php**
````
/**
 * Load DT Importer
 */
require_once get_template_directory() . '/dt_importer/init.php';


/**
 * Initialize DT Importer
 */
$settings      = array(
  'menu_parent' => 'tools.php',
  'menu_title'  => __('Demo Importer', 'dt-importer'),
  'menu_type'   => 'add_submenu_page',
  'menu_slug'   => 'dt_demo_importer',
);
$options        = array(
    array(
      'id'            => 'demo-1', //folder name
      'title'         => __('Demo 1', 'dt-importer'),
      'preview_url'   => 'https://www.google.com/',
    ),
    array(
      'id'            => 'demo-2', folder name
      'title'         => __('Demo 2', 'dt-importer'),
      'preview_url'   => 'https://www.yahoo.com/',
    ),
    array(
      'id'            => 'demo-3',
      'title'         => __('Demo 3', 'dt-importer'),
      'preview_url'   => 'https://www.google.com/',
    ),
);
DT_Demo_Importer::instance( $settings, $options );
````

**Create Folder by id in ````dt_importer/demos/```` by same id**
````
demos
  - demo-1
    - content.xml // WP Exported Data
    - options.txt // Codestart Exported Opt
    - screenshot.php // Preview Image
  - demo-2
    - content.xml // WP Exported Data
    - options.txt // Codestart Exported Opt
    - screenshot.php // Preview Image
  - demo-3
    - content.xml // WP Exported Data
    - options.txt // Codestart Exported Opt
    - screenshot.php // Preview Image
````

# Few screenshots
![Dashboard](http://i.imgur.com/u8oknuS.jpg)

# Credits
* [Codestar](https://github.com/Codestar/codestar-framework)
* [FrankM1](https://github.com/FrankM1/radium-one-click-demo-install)

# Licence
GPL V2+