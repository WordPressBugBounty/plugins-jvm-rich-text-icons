<?php
/**
 * Plugin Name: JVM rich text icons
 * Description: Add Font Awesome icons, or (SVG) icons from a custom icon set to the WordPress block editor.
 * Version: 1.3.2
 * Author: Joris van Montfort
 * Author URI: https://jorisvm.nl
 * Text Domain: jvm-richtext-icons
 * Domain Path: languages
 *
 * @category Gutenberg
 * @author Joris van Montfort
 * @version 1.3.2
 * @package JVM rich text icons
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

if (is_admin()) {
   require_once plugin_dir_path( __FILE__ ) . 'src/settings.php';
}

// Load the ACF field only for sites using the ACF plugin.
if (function_exists('the_field')) {
    require_once plugin_dir_path( __FILE__ ) . 'src/acf_plugin_jvm_rich_text_icons.php';
}