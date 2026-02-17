=== JVM Gutenberg Rich Text Icons ===
Contributors: jorisvanmontfort
Donate link: https://www.paypal.com/donate/?hosted_button_id=VXZJG9GC34JJU
Tags: icon, svg, font-awesome, gutenberg, icon-block
Requires at least: 5.4
Tested up to: 6.9 * Stable tag: 1.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Playground: true

Insert icons anywhere in your content — inline in text, headings, buttons, or as a standalone block.

== Description ==

Add icons to any rich text field in the WordPress block editor. Insert icons inline in paragraphs, headings, lists, buttons, or use the dedicated single icon block.

= Features =

* **Icon picker** - Select icons from a searchable popup in the block editor toolbar.
* **Font Awesome included** - Ships with Font Awesome 4.7, 5.x and 6.x. Choose your preferred version from the settings.
* **Custom SVG icon set** - Upload your own SVG icons via a drag & drop uploader in the plugin settings. This is the recommended approach for the best performance.
* **Single icon block** - A dedicated block with font size, color, alignment and spacing options.
* **ACF integration** - Adds a "JVM Icon" field type for Advanced Custom Fields.

= How it works =

Pick an icon from the toolbar while editing any rich text field. The plugin inserts a small HTML tag that gets styled by the chosen icon set.

= Why use a custom SVG icon set? =

When you use a custom SVG icon set, the plugin defaults to **inline SVG rendering**. This is a great choice for performance! Google PageSpeed Insights will thank you.

* **Better page speed** - No render-blocking CSS or font files to download. Icons are part of the HTML itself.
* **Only loads what you use** - Unlike Font Awesome which loads CSS for hundreds of icons, inline SVG only includes the icons that are actually on the page.
* **No external requests** - Everything is served inline, so there are no extra HTTP requests for font or CSS files.
* **Inherits text color** - Icons automatically use the surrounding text color, no extra CSS needed.
* **Fully reversible** - The stored content in the database is not modified. You can switch between render technologies at any time from the plugin settings if you want to.

Alternative render technologies (CSS masks, ::before / ::after pseudo-elements) are also available for custom SVG icons if your use case requires it.

= For developers =

The plugin provides several filter hooks to customize its behavior. You can load your own icon set, CSS file, or change the icon class prefix. The icon config file can also be in Fontello format (see <https://fontello.com>).

**Load a custom icon set file**

`
add_filter( 'jvm_richtext_icons_iconset_file', function($file) {
    return get_stylesheet_directory() . '/path_to_my/icons.json';
});
`

**Load a custom CSS file**

`
add_filter( 'jvm_richtext_icons_css_file', function($cssfile) {
    return get_stylesheet_directory_uri() . '/path_to_my/cssfile.css';
});
`

To disable the default CSS file entirely:

`
add_filter( 'jvm_richtext_icons_css_file', '__return_false');
`

**Change the icon class prefix**

`
add_filter( 'jvm_richtext_icons_base_class', function() {
    return 'my-custom-css-class-name';
});
`

**Disable the settings screen**

`
add_filter( 'jvm_richtext_icons_show_settings', '__return_false');
`

Please note that if you are loading a custom icon set with the plugin hooks, you should keep the plugin settings set to "Font Awesome 4.7" (default).

== Changelog ==

= 1.6.0 =
* Renamed the "Custom SVG icon set" to "My SVG uploads" for clarity.
* Improved layout forSVG uploads grid on the settings page: CSS grid layout with icon class name labels.
* Added Dutch and German translations.
* Added `load_plugin_textdomain()` for translation support.
* Fixed Text Domain header mismatch (was `jvm-richtext-icons`, now matches code: `jvm-rich-text-icons`).
* Centralized CSS renderer for custom SVG icons. Replaces three separate view files with a single renderer class that supports all render technologies (inline-svg, html-css, html-css-before, html-css-after).
* Icons JSON parser supports a new pro/extended format alongside the existing flat array and Fontello formats.
* More preparation for (Pro) future features.

= 1.5.1 =
* Added extensibility hooks for pro plugin support: `jvm_richtext_icons_svg_directories`, `jvm_richtext_icons_use_inline_svg`, `jvm_richtext_icons_available_icon_sets`, `jvm_richtext_icons_valid_icon_sets`, `jvm_richtext_icons_load_default_css`.
* Added action hooks on the settings page: `jvm_richtext_icons_settings_after_form` and `jvm_richtext_icons_settings_after_icons`.
* The icon set dropdown is now dynamically generated and filterable instead of hardcoded.
* Fixed "Font Awsome" typo to "Font Awesome" in the settings dropdown.

= 1.5.0 =
* New visual icon picker for the single icon block. Replaced the dropdown with a searchable icon grid with tooltips. The same style as the rich text toolbar picker. Both pickers now share a single reusable IconPicker component.
* Added padding and margin controls to the single icon block via native WordPress block supports.
* Migrated the single icon block to useBlockProps for better compatibility with WordPress block features.
* Fixed the icon search bar in the rich text toolbar popover scrolling out of view. The search bar now stays fixed at the top while the icon grid scrolls.

= 1.4.1 =
* Added a friendly review notice that appears after 14 days of use. Can be permanently dismissed.
* Updated plugin description and tagline.

= 1.4.0 =
* New render technology: Inline SVG. When using a custom SVG icon set you can now choose "Inline SVG" as render technology. This replaces the `<i>` tags with inline `<svg>` elements on the frontend using output buffering. Benefits: no CSS overhead for unused icons, only icons that are actually on the page are included, and icons inherit the current text color via `fill: currentColor`. The block editor continues to use CSS-based rendering so icons remain visible while editing. The stored HTML in the database is not modified, so you can switch back to any CSS-based technology at any time.
* Migrated build tooling from the deprecated cgb-scripts (Create Guten Block) to @wordpress/scripts. JavaScript source files now use proper `@wordpress/*` package imports, and the build generates a `blocks.asset.php` file for automatic dependency management.
* Fixed "acf is not defined" error on admin screens where Advanced Custom Fields is not active.
* Code quality review of the entire codebase: added settings sanitization with whitelisted values, added capability checks in AJAX handlers, improved error handling for file operations and JSON parsing, fixed a text domain typo, and replaced deprecated PHP syntax.

= 1.3.7 =
Tested on WordPress 6.9

= 1.3.6 =
Font awesome 5.x and 6.x have been updated and are now loaded from a source within the plugin. This also fixes a bug where Font Awesome icons did not display in the block pattern / full site editor.

= 1.3.4 =
Php deprecation warning fixed.

= 1.3.3 =
Added a fix for CSS in the full site editor and block editor when using custom icons.

= 1.3.2 =
Added a font-size (pixels) option for single icon block.

= 1.3.1 =
Added color options for single icon block.

= 1.3.0 =
Added alignment and spacing options for single icon block.

= 1.2.9 =
Added an option for rendering technology for custom icons sets. You can now also choose rendering with a ::before or ::after pseudo element instead of the regular HTML / CSS. This allows for more CSS flexibility. For example adding backgrounds or hover effects.

= 1.2.8 =
Update to re-enable icons in ACF select2 field. Icons apeared as literal HTML after an update of the ACF plugin.

= 1.2.7 =
Security update. File name now sanitize in delete icon ajax call.

= 1.2.6 =
Security update. Fixed a vulnerabilities in plugin settings upload and delete icon options.

= 1.2.3 =
Fixed the thick border around the toolbar button by using the correct toolbar button markup.

= 1.2.2 =
Bugfix WordPress 6.2 site editor rich text blocks not editable.

= 1.2.1 =
Bugfix for the single icon block using incomplete css classes.

= 1.2.0 =
Added a dedicated single icon block for Gutenberg.

= 1.1.9 =
Fixed some deprecation errors to get this plugin compatible with the site editor and future WordPress versions. Some work is still needed on this.

= 1.1.8 =
Got rid of position relative for custom icon sets.

= 1.1.7 =
Fixed editor dialog position on smaller screens.

= 1.1.5 =
Font Awesome 4.7 webfont URL's fixed.

= 1.1.4 =
Now also load in the site editor. Not all block however.

= 1.1.3 =
Fixed a deprecated warning in php 8.1.

= 1.1.2 =
Added Font Awesome Free 5.15.4 and Font Awesome Free 6.2.0 to the settings. The CSS for these verions are loaded from a CDN. Font Awesome version 4.7 is still the default.

= 1.1.1 =
Added a notice on the settings screen if a custom icon set is loaded and the SVG icon set is selected. These options won't work together.

= 1.1.0 =
Added a hook to disable the plugin settings page altogether for those who like a clean WordPress admin.

Use this in your functions.php to disable the settings screen that was added in 1.0.9:
`add_filter('jvm_richtext_icons_show_settings', '__return_false');`

= 1.0.9 =
Added a plugin settings screen and a nice interface to upload and create a custom SVG file based icon set. If you like this feature please consinder donating: https://www.paypal.com/donate/?hosted_button_id=VXZJG9GC34JJU

= 1.0.8 =
Fixed some WordPress coding convenstions and tested and fixed some minor issues for WordPress 6.0.

= 1.0.7 =
Fixed the styling of the editor pop-over. It was to large since WordPress 5.9.

= 1.0.6 =
The addon is now also loaded in the widget screen (widget.php)

= 1.0.5 =
Added a hook for modifying the editor javascript file loaded for advanced users.
Example usage:

`
function add_my_js_file($file) {
    $file = '/path_to_my/js_file.js';
    return $file;
}

add_filter( 'jvm_richtext_icons_editor_js_file', 'add_my_js_file');
`

= 1.0.4 =
Bug fix: Replaced the deprecated block_editor_settings hook by the new block_editor_settings_all hook. This fixes a deprecated notice.

= 1.0.3 =
New feature: ACF field for the JVM icon set loaded.
New feature: Font icon config file can now also ben in fontello format

= 1.0.2 =
Bugfix: Changed backend asset loading to load only on new posts and edit post pages. In version 1.0.1 scripts for this plugin loaded on all backend pages and kept breaking the widget text editor.

= 1.0.1 =
Php error fix for some php versions on plugin activation.

= 1.0.0 =
Initial release
