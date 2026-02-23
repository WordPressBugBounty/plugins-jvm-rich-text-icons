<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class JVM_Richtext_icons {
    /**
     * This plugin's instance.
     *
     * @var JVM_Richttext_icons
     */
    private static $instance;

    /**
     * Registers the plugin.
     */
    public static function register() {
        if ( null === self::$instance ) {
            self::$instance = new JVM_Richtext_icons();
        }
    }

    /**
     * The Constructor.
     */
    private function __construct() {
        add_filter( 'block_editor_settings_all', array( $this, 'block_editor_settings' ), 10, 2 );
        add_action( 'init', array( $this, 'load_css') );
        add_action( 'enqueue_block_assets', array( $this, 'load_editor_icon_css') );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets') );
        add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
        add_action( 'template_redirect', array( $this, 'maybe_start_inline_svg_buffer' ) );
        add_action( 'admin_notices', array( $this, 'maybe_show_review_notice' ) );
        add_action( 'wp_ajax_jvm_richtext_dismiss_review', array( $this, 'dismiss_review_notice' ) );


        /**
         * Register Gutenberg block on server-side from block.json.
         *
         * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/
         * @since 1.16.0
         */
        register_block_type( plugin_dir_path( __DIR__ ) . 'src', array(
            'editor_script' => 'jvm-rich-text-icons-js',
            'editor_style'  => 'jvm-rich-text-icons-editor-css',
        ) );
    }

    /**
     * Add settings as link from the plugin screen
     */
    public function plugin_action_links($links, $plugin_file) {

        if ($plugin_file == 'jvm-rich-text-icons/plugin.php' && apply_filters('jvm_richtext_icons_show_settings', true)) {
            $setting_link = array(
                '<a href="' . admin_url( 'options-general.php?page=jvm-rich-text-icons' ) . '">' . esc_html__( 'Settings' ) . '</a>',
            );
            return array_merge( $links, $setting_link );
        }

        return $links;
    }

    /**
     * Add settings as link from the plugin screen
     */
    public function plugin_row_meta($links, $plugin_file) {
        if ($plugin_file == 'jvm-rich-text-icons/plugin.php') {
            $donate_link = array(
                '<a href="https://www.paypal.com/donate/?hosted_button_id=VXZJG9GC34JJU" target="_blank">' . esc_html__( 'Donate to Support', 'jvm-rich-text-icons' ) . '</a>',
            );
            return array_merge( $links, $donate_link );
        }
        return $links;
    }

    /**
     * Filters the settings to pass to the block editor.
     *
     * @param array  $editor_settings The editor settings.
     * @param object $post The post being edited.
     *
     * @return array Returns updated editors settings.
     */
    public function block_editor_settings( $editor_settings, $post ) {
        if ( ! isset( $editor_settings['jvm_richtext_icons'] ) ) {

            $editor_settings['jvm_richtext_icons'] = [
                'formats'    => array(
                    'name'  => 'formats',
                    'label' => __( 'Formats', 'block-options' ),
                    'items' => array(
                        'icons'        => array(
                            'name'  => 'icon',
                            'label' => __( 'Insert icon', 'jvm-rich-text-icons' ),
                            'value' => true,
                        )
                    )
                )
            ];

        }

        return $editor_settings;
    }


    /**
     * Enqueue Gutenberg block assets for both admin backend.
     */
    public function load_admin_assets($hook_suffix) {
        if( 'post.php' == $hook_suffix
            || 'post-new.php' == $hook_suffix
            || 'widgets.php' == $hook_suffix
            || 'site-editor.php' == $hook_suffix) {

            // Register block editor script for backend.
            $asset_file = include plugin_dir_path( __DIR__ ) . 'dist/blocks.asset.php';
            wp_enqueue_script(
                'jvm-rich-text-icons-js',
                plugins_url( '/dist/blocks.js', dirname( __FILE__ ) ),
                $asset_file['dependencies'],
                $asset_file['version'],
                true
            );

            // Register block editor styles for backend.
            wp_enqueue_style(
                'jvm-rich-text-icons-editor-css', // Handle.
                plugins_url( 'dist/editor.css', dirname( __FILE__ ) ), // Block editor CSS.
                array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
                filemtime( plugin_dir_path( __DIR__ ) . 'dist/editor.css' ) // Version: File modification time.
            );

            $icons = JVM_Richtext_icons::get_icons();
            $base_class = JVM_Richtext_icons::get_class_prefix();
            wp_localize_script(
                'jvm-rich-text-icons-js',
                'jvm_richtext_icon_settings', // Array containing dynamic data for a JS Global.
                [
                    'iconset' => $icons,
                    'base_class' => $base_class,
                    'text' => [
                        'delete_icon' => __('Delete Icon', 'jvm-rich-text-icons')
                    ]
                ]
            );
        }
    }

    /**
     * Enqueue icon CSS in the block editor iframe.
     * Only runs in admin context to avoid double-loading on the frontend.
     */
    public function load_editor_icon_css() {
        if ( is_admin() ) {
            self::load_css();
        }
    }

    /**
     * Enqueue Gutenberg block assets for both frontend + backend.
     */
    public static function load_css() {
        $settings = self::get_settings();
        $technology = isset($settings['technology']) ? $settings['technology'] : 'html-css';

        // Base CSS — always loaded regardless of pro override or technology
        if (!is_admin()) {
            wp_register_style('jvm-rich-text-icons-svg', false);
            wp_enqueue_style('jvm-rich-text-icons-svg');
            $base_css = '.wp-block-jvm-single-icon{line-height:1}';
            if ($technology === 'inline-svg') {
                $base_css = 'svg.icon{width:1em;height:1em;display:inline-block;vertical-align:-0.125em}' . $base_css;
            }
            wp_add_inline_style('jvm-rich-text-icons-svg', $base_css);
        }

        $load_default_css = apply_filters('jvm_richtext_icons_load_default_css', true, $settings);
        if (!$load_default_css) {
            return;
        }

        $icons = [];
        $icon_set = 'default';
        if (isset($settings['icon_set'])) {
            $icon_set = $settings['icon_set'];
        }

        if ($icon_set == 'custom-svg') {
            if ($technology !== 'inline-svg' || is_admin()) {
                wp_register_style('jvm-rich-text-icons-svg', false);
                wp_enqueue_style('jvm-rich-text-icons-svg');
                // In admin/editor always load mask-based CSS so <i> tags render correctly
                wp_add_inline_style('jvm-rich-text-icons-svg', JVM_Richtext_icons::parse_dynamic_css());
            }
        }else {

            $folder = $icon_set;
            if ($icon_set == 'default') {
                $fontCssFile = plugins_url( 'dist/fa-4.7/font-awesome.min.css', dirname( __FILE__ ));
            }else if ($icon_set == 'fa-5') {
                $fontCssFile = plugins_url( 'dist/fa-5/css/all.min.css', dirname( __FILE__ ));
            }else if ($icon_set == 'fa-6') {
                $fontCssFile = plugins_url( 'dist/fa-6/css/all.min.css', dirname( __FILE__ ));
            }

            // Icon set CSS (font awesome 4.7 is shipped by default).
            $fontCssFile = apply_filters('jvm_richtext_icons_css_file', $fontCssFile);

            if (!empty($fontCssFile)) {
                wp_enqueue_style(
                    'jvm-rich-text-icons-icon-font-css', // Handle.
                    $fontCssFile
                );
            }
        }
    }

    /**
     * Start output buffering for inline SVG replacement on the frontend.
     */
    public function maybe_start_inline_svg_buffer() {
        $settings = self::get_settings();
        $use_inline_svg = ($settings['icon_set'] == 'custom-svg' && $settings['technology'] == 'inline-svg');
        $use_inline_svg = apply_filters('jvm_richtext_icons_use_inline_svg', $use_inline_svg, $settings);

        if ($use_inline_svg) {
            ob_start(array($this, 'replace_icons_with_inline_svg'));
        }
    }

    /**
     * Replace <i class="icon icon-name"> tags with inline SVG elements.
     * @param string $html
     * @return string
     */
    public function replace_icons_with_inline_svg($html) {
        $prefix = self::get_class_prefix();
        $svg_dirs = apply_filters('jvm_richtext_icons_svg_directories', [
            'default' => self::get_svg_directory()
        ]);
        static $svg_cache = [];

        $html = preg_replace_callback(
            '/<i\b([^>]*)\bclass="' . preg_quote($prefix, '/') . ' ([^"]*)"([^>]*)>\s*<\/i>/',
            function ($matches) use ($prefix, $svg_dirs, &$svg_cache) {
                $classes = $matches[2];
                $extra_attrs = trim($matches[1] . ' ' . $matches[3]);
                // Remove aria-hidden from extra attrs since we add it ourselves
                $extra_attrs = preg_replace('/\s*aria-hidden="[^"]*"/', '', $extra_attrs);
                $extra_attrs = trim($extra_attrs);

                // The last class is the icon name (e.g. "fas fa-location-dot" -> "fa-location-dot")
                $class_parts = explode(' ', $classes);
                $icon_name = end($class_parts);

                if (!isset($svg_cache[$icon_name])) {
                    $svg_cache[$icon_name] = false;
                    foreach ($svg_dirs as $dir) {
                        $file = $dir . $icon_name . '.svg';
                        if (file_exists($file)) {
                            $svg_cache[$icon_name] = file_get_contents($file);
                            break;
                        }
                    }
                }

                if ($svg_cache[$icon_name] === false) {
                    return $matches[0];
                }

                $svg = $svg_cache[$icon_name];
                // Add class, aria-hidden and any extra attributes (e.g. style) to the SVG element
                $attrs = 'class="' . esc_attr($prefix . ' ' . $classes) . '" aria-hidden="true"';
                if (!empty($extra_attrs)) {
                    $attrs .= ' ' . $extra_attrs;
                }
                $svg = preg_replace('/<svg\b/', '<svg ' . $attrs, $svg, 1);

                return $svg;
            },
            $html
        );

        return $html;
    }

    /**
     * Show a review notice after 14 days of use.
     */
    public function maybe_show_review_notice() {
        if (get_option('jvm_richtext_icons_review_dismissed')) {
            return;
        }

        $activated = get_option('jvm_richtext_icons_activated');
        if (!$activated) {
            // Existing installs that never triggered the activation hook
            update_option('jvm_richtext_icons_activated', time());
            return;
        }
        if ((time() - $activated) < 14 * DAY_IN_SECONDS) {
            return;
        }

        echo '<div class="notice notice-info is-dismissible jvm-richtext-review-notice">';
        echo '<p>';
        echo sprintf(
            __('Enjoying %1$s? Please consider %2$sleaving a review%3$s — it helps other WordPress users find this plugin.', 'jvm-rich-text-icons'),
            '<strong>JVM Rich Text Icons</strong>',
            '<a href="https://wordpress.org/support/plugin/jvm-rich-text-icons/reviews/#new-post" target="_blank">',
            '</a>'
        );
        echo '</p>';
        echo '</div>';
        echo '<script>jQuery(document).on("click",".jvm-richtext-review-notice .notice-dismiss",function(){jQuery.post(ajaxurl,{action:"jvm_richtext_dismiss_review"})});</script>';
    }

    /**
     * AJAX handler to permanently dismiss the review notice.
     */
    public function dismiss_review_notice() {
        update_option('jvm_richtext_icons_review_dismissed', true);
        wp_send_json_success();
    }

    /**
     * Get the class prefix for the css
     * @return [string]
     */
    public static function get_class_prefix() {
        return apply_filters('jvm_richtext_icons_base_class', 'icon');
    }

    /**
     * Get the icon config
     * @return [array]
     */
    public static function get_icons() {
        $settings = self::get_settings();
        $icons = [];
        $icon_set = 'default';
        if (isset($settings['icon_set'])) {
            $icon_set = $settings['icon_set'];
        }


        if ($icon_set == 'custom-svg') {
                $svg_files = self::get_svg_file_list();
                foreach ($svg_files as $file) {
                    $pi = pathinfo($file);
                    if ($pi['extension'] == 'svg') {
                        $icon_class = sanitize_title($pi['filename']);
                        $icons[] = $icon_class;
                    }
                }
        }else {
            $folder = $icon_set;
            if ($icon_set == 'default') {
                $folder = 'fa-4.7';
            }

            // WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
            $iconFile = plugin_dir_path( __DIR__ ).'dist/'.$folder.'/icons.json';
            $iconFile = apply_filters('jvm_richtext_icons_iconset_file', $iconFile);

            if (file_exists($iconFile)) {
                $iconData = file_get_contents($iconFile);
                $data = json_decode($iconData);
                if ($data === null) {
                    return $icons;
                }
                $icons = [];
                // Check if data is fontello format
                if (isset($data->glyphs)) {
                    foreach($data->glyphs as $g) {
                        $icons[] = $data->css_prefix_text.$g->css;
                    }
                // Pro/extended format: object with icons array of {name, tags, categories, styles}
                } elseif (is_object($data) && isset($data->icons) && is_array($data->icons)) {
                    foreach ($data->icons as $icon) {
                        if (is_object($icon) && isset($icon->name)) {
                            $icons[] = $icon->name;
                        } elseif (is_string($icon)) {
                            $icons[] = $icon;
                        }
                    }
                // Legacy format: flat array of class name strings
                } else {
                    $icons = $data;
                }

                $icons = apply_filters('jvm_richtext_icons_iconset', $icons);
            }
        }

        return $icons;
    }

    /**
     * Get the plugin settings
     * @return [array] [options]
     */
    public static function get_settings() {
        $settings = get_option('jvm-rich-text-icons');

        // Array if no options
        if (false == $settings) {
            $settings = [];
        }

        if (!isset($settings['icon_set'])) {
            $settings['icon_set'] = 'fa-6';
        }

        if (!isset($settings['technology'])) {
            $settings['technology'] = 'inline-svg';
        }

        return $settings;
    }

    /**
     * Render a view file
     * @param  [string] $fileName
     * @param  array  $dataForView
     * @return [string] rendered view
     */
    public static function render_view($fileName, $dataForView=array()) {

        if (!file_exists(plugin_dir_path( __DIR__ ).'views/'.$fileName)) {
            return plugin_dir_path( __DIR__ ).'views/'.$fileName. ' not found.';
        }else {

            // Extract vars to local namespace
            extract($dataForView, EXTR_SKIP);
            ob_start();

            include plugin_dir_path( __DIR__ ).'views/'.$fileName;

            $out = ob_get_clean();

            return $out;
        }
    }

    /**
     * Get the css for custom svg settings
     * @return [string] [css styling]
     */
    public static function parse_dynamic_css() {
        $settings = self::get_settings();
        $technology = isset($settings['technology']) ? $settings['technology'] : 'html-css';
        $files = self::get_svg_file_list();
        $prefix_class = self::get_class_prefix();

        $icons = [];
        foreach ($files as $file) {
            $pi = pathinfo($file);
            $icon_class = sanitize_title($pi['filename']);
            $file_content = file_get_contents($file);
            if ($file_content === false) { continue; }

            $ratio = 1;
            $dom = new DOMDocument();
            @$dom->load($file);
            $svg = $dom->getElementsByTagName('svg');
            if ($svg && $svg->length > 0) {
                $viewBox = $svg[0]->getAttribute('viewBox');
                if ($viewBox) {
                    list($x, $y, $width, $height) = explode(' ', $viewBox);
                } else {
                    $width = str_replace('px', '', $svg[0]->getAttribute('width'));
                    $height = str_replace('px', '', $svg[0]->getAttribute('height'));
                }
                if (!empty($width) && !empty($height)) {
                    $ratio = $width / $height;
                }
            }

            $icons[] = ['class' => $icon_class, 'svg' => $file_content, 'ratio' => $ratio];
        }

        return JVM_RTI_Renderer::generate_css($icons, $technology);
    }

    /**
     * Get a list of uploaded custom SVG icons
     * @return [array] [icons]
     */
    public static function get_svg_file_list() {
        $base = self::get_svg_directory();
        $files = scandir($base);
        $files_out = [];
        foreach ($files as $file) {
            $pi = pathinfo($base.$file);

            if ($pi['extension'] == 'svg') {
                $files_out[] = $base.$file;
            }
        }

        return $files_out;
    }

    /**
     * Get the icon upload base directory
     * @return [string]
     */
    public static function get_svg_directory() {
        $upload = wp_upload_dir();
        $base = $upload['basedir'].'/jvm-rich-text-icons/';
        if (!is_dir($base)) {
            mkdir($base);
        }
        return $base;
    }
}

JVM_Richtext_icons::register();
