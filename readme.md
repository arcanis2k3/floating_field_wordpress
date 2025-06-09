# A FleK90 Tool Floating Field

**A FleK90 Tool Floating Field** is a lightweight WordPress plugin that adds a fixed-position floating field to your website's front-end. The field content is hardcoded in a separate file (`floating-field-content.php`) and includes a search form that submits to `https://portal.bestlinks.fun/`. The form features a text input and a submit button with a bold, black SVG search icon, centered in the button, and displayed in a single line on all screen sizes. Manage settings like enable/disable, background color, and font size via Settings > Floating Field Settings in the admin dashboard. The field supports mobile devices and is compatible with any theme—including older ones like Twenty Ten—without extra plugins.

## Features
- **Floating Field with Hardcoded Content**: The field content is defined in `floating-field-content.php`, featuring a search form.
- **Admin Menu Management**: Manage settings (enable/disable, background color, font size) via Settings > Floating Field Settings.
- **Search Form**: Includes a search form with a text input and a submit button using a bold, black SVG search icon, centered in the button, always displayed in one line.
- **Mobile Support**: Displays correctly on mobile devices with responsive styling.
- **No Dependencies**: Uses core WordPress APIs—no additional plugins required.
- **Theme Compatibility**: Works with classic and block themes, including Twenty Ten.
- **User Guidance**: Includes a "View Details" section and a welcome notice.

## Installation
1. Create a plugin folder named `a-flek90-tool-floating-field` in `wp-content/plugins/`.
2. Add the following files to the folder:
   - `a-flek90-tool-floating-field.php`
   - `floating-field-content.php`
3. Upload the folder to your WordPress site via FTP or zip it and upload via **Plugins > Add New > Upload Plugin**.
4. Activate the plugin via **Plugins > Installed Plugins**.
5. After activation, a welcome notice guides you to configure settings:
   - Go to **Settings > Floating Field Settings** to enable/disable the field and customize styling.

## Usage
### For New Users
- On the **Plugins** page, find **A FleK90 Tool Floating Field** and click **View Details** (next to **Activate**).
- Learn about the plugin: a fixed-position field with a hardcoded search form, managed via an admin menu page, compatible with any theme.
- Activate to start—no extra setup needed.

### For Installed Users
- After activation, a welcome notice links to the settings page.
- **Managing Settings**:
  - Go to **Settings > Floating Field Settings**.
  - **Enable Floating Field**: Check to display the field on the front-end.
  - **Background Color**: Select a color (default: blue).
  - **Font Size**: Set the font size (12–48px, default: 24px).
  - Save changes.
- Visit the front-end to see the field on desktop and mobile.
- **Customizing Content**:
  - The field content is hardcoded in `floating-field-content.php`. Edit this file to change the content (e.g., modify the search form, add HTML).

## Configuration
- **Global Field**: Applies to all pages. For page-specific fields, contact support.
- **Content**: Hardcoded in `floating-field-content.php`. Includes a search form submitting to `https://portal.bestlinks.fun/`. Edit the file to customize.
- **Styling**: Customize color and font size via the admin settings page. The search form is styled to match the block editor aesthetic, with a bold, black SVG search icon. For advanced styling, modify the inline CSS (see code).
- **Positioning**: Fixed position at the top center of the page.

## Support
- **Documentation**: [Placeholder - Add your docs link]
- **Support**: [Placeholder - Add your support link]
- **Issues**: Report bugs or request features via [Placeholder - Add your contact method].

## Notes
- **Compatibility**: Tested with WordPress 6.0+ and older themes.
- **Performance**: Lightweight with minimal scripts/styles.
- **Security**: Sanitizes HTML in the hardcoded file.
- **Debugging**: Logs errors to `debug.log` if WP_DEBUG is enabled. Check the browser console and Elements tab for rendering issues.

## Complete Plugin Code
Below are the files for the plugin.

### `a-flek90-tool-floating-field.php`
The main plugin file.

```php
<?php
/*
Plugin Name: A FleK90 Tool Floating Field
Description: Adds a fixed-position floating field on the front-end with a hardcoded search form (using a bold, black SVG search icon) defined in floating-field-content.php. The form remains in one line on all screen sizes. Managed via an admin menu page (Settings > Floating Field Settings). Compatible with older themes, no dependencies.
Version: 2.6
Author: Your Name
License: GPL-2.0+
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Initialize the plugin
class A_FleK90_Tool_Floating_Field {
    public function __construct() {
        // Debug logging
        $this->debug_log('Plugin initialized');
        
        // Add admin menu page
        add_action('admin_menu', [$this, 'add_admin_menu']);
        // Enqueue front-end scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        // Output the floating field
        add_action('wp_footer', [$this, 'render_floating_field']);
        // Add plugin row meta
        add_filter('plugin_row_meta', [$this, 'add_plugin_row_meta'], 10, 2);
        // Add admin notice
        add_action('admin_notices', [$this, 'display_admin_notice']);
        // Handle notice dismissal
        add_action('admin_init', [$this, 'handle_notice_dismissal']);
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    // Debug logging helper
    private function debug_log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[FleK90 Plugin] ' . $message);
        }
    }

    // Add admin menu page
    public function add_admin_menu() {
        $this->debug_log('Adding admin menu page');
        add_options_page(
            'Floating Field Settings',
            'Floating Field Settings',
            'manage_options',
            'flek90-floating-field-settings',
            [$this, 'render_admin_page']
        );
    }

    // Render the admin page
    public function render_admin_page() {
        $this->debug_log('Rendering admin page');

        // Handle form submission
        if (isset($_POST['flek90_save_settings']) && check_admin_referer('flek90_save_settings_action', 'flek90_save_settings_nonce')) {
            update_option('flek90_enable_field', isset($_POST['flek90_enable_field']) ? '1' : '0');
            update_option('flek90_background_color', sanitize_hex_color($_POST['flek90_background_color']));
            update_option('flek90_font_size', absint($_POST['flek90_font_size']));
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Settings saved successfully!</p>
            </div>
            <?php
        }

        // Get current settings
        $enable_field = get_option('flek90_enable_field', '1');
        $background_color = get_option('flek90_background_color', '#0073aa');
        $font_size = get_option('flek90_font_size', '24');
        ?>
        <div class="wrap">
            <h1>Floating Field Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('flek90_save_settings_action', 'flek90_save_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="flek90_enable_field">Enable Floating Field</label></th>
                        <td>
                            <input type="checkbox" id="flek90_enable_field" name="flek90_enable_field" value="1" <?php checked($enable_field, '1'); ?>>
                            <p class="description">Check to display the floating field on the front-end.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Field Content</th>
                        <td>
                            <p>The field content is hardcoded in <code>floating-field-content.php</code>. Edit this file to customize the content (e.g., modify the search form, add HTML).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="flek90_background_color">Background Color</label></th>
                        <td>
                            <input type="text" id="flek90_background_color" name="flek90_background_color" value="<?php echo esc_attr($background_color); ?>" class="flek90-color-picker">
                            <p class="description">Select the background color for the floating field (default: blue).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="flek90_font_size">Font Size (px)</label></th>
                        <td>
                            <input type="number" id="flek90_font_size" name="flek90_font_size" value="<?php echo esc_attr($font_size); ?>" min="12" max="48" step="1">
                            <p class="description">Set the font size for the floating field (12–48px, default: 24px).</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="flek90_save_settings" class="button button-primary" value="Save Settings">
                </p>
            </form>
        </div>
        <?php
    }

    // Sanitize content (for hardcoded HTML processing)
    public function sanitize_content($input) {
        $this->debug_log('Sanitizing content: ' . substr($input, 0, 100) . '...');
        $allowed_tags = wp_kses_allowed_html('post');
        $allowed_tags['input'] = [
            'type' => true,
            'name' => true,
            'class' => true,
            'placeholder' => true,
            'autocomplete' => true,
            'value' => true,
        ];
        $allowed_tags['button'] = [
            'type' => true,
            'class' => true,
        ];
        $allowed_tags['svg'] = [
            'width' => true,
            'height' => true,
            'viewbox' => true,
            'fill' => true,
            'stroke' => true,
            'stroke-width' => true,
        ];
        $allowed_tags['circle'] = [
            'cx' => true,
            'cy' => true,
            'r' => true,
        ];
        $allowed_tags['line'] = [
            'x1' => true,
            'y1' => true,
            'x2' => true,
            'y2' => true,
        ];
        $allowed_tags['form'] = [
            'id' => true,
            'method' => true,
            'action' => true,
        ];
        $sanitized = wp_kses($input, $allowed_tags);
        $this->debug_log('Sanitized content: ' . substr($sanitized, 0, 100) . '...');
        return $sanitized;
    }

    // Enqueue scripts and styles
    public function enqueue_scripts() {
        $this->debug_log('Enqueuing front-end scripts');

        // Inline CSS
        $background_color = get_option('flek90_background_color', '#0073aa');
        $font_size = get_option('flek90_font_size', '24');
        $css = '
            #flek90-floating-container {
                position: fixed !important;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                background: ' . esc_attr($background_color) . ';
                color: #fff;
                padding: 15px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                max-width: 90%;
                width: auto;
                text-align: center;
                font-size: ' . esc_attr($font_size) . 'px;
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }
            #flek90-floating-container * {
                color: inherit;
                line-height: 1.5;
            }
            #flek90-floating-container a {
                color: #fff;
                text-decoration: underline;
            }
            #flek90-floating-container a:hover {
                color: #ddd;
            }
            #flek90-floating-container form#searchform {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-direction: row;
            }
            #flek90-floating-container input.search-input {
                background: #fff;
                color: #333;
                border: 1px solid #ccc;
                padding: 8px 12px;
                font-size: 14px;
                width: 200px;
                border-radius: 4px;
                box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
                transition: border-color 0.2s ease-in-out;
            }
            #flek90-floating-container input.search-input:focus {
                border-color: #007cba;
                outline: none;
                box-shadow: 0 0 0 1px #007cba;
            }
            #flek90-floating-container button.search-submit {
                background: #fff;
                color: #333;
                border: 1px solid #ccc;
                padding: 0;
                width: 32px;
                height: 32px;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.2s ease-in-out;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            #flek90-floating-container button.search-submit:hover {
                background: #f5f5f5;
            }
            #flek90-floating-container button.search-submit svg {
                width: 16px;
                height: 16px;
                stroke: #000;
                stroke-width: 3;
            }
            @media (max-width: 768px) {
                #flek90-floating-container {
                    top: 10px;
                    padding: 10px 15px;
                    max-width: 95%;
                    font-size: ' . esc_attr(max(12, $font_size - 4)) . 'px;
                }
                #flek90-floating-container form#searchform {
                    gap: 5px;
                }
                #flek90-floating-container input.search-input {
                    width: 150px;
                    font-size: 13px;
                    padding: 6px 10px;
                }
                #flek90-floating-container button.search-submit {
                    width: 28px;
                    height: 28px;
                }
                #flek90-floating-container button.search-submit svg {
                    width: 14px;
                    height: 14px;
                }
            }
        ';
        wp_add_inline_style('wp-block-library', $css);
    }

    // Render the floating field
    public function render_floating_field() {
        $this->debug_log('Rendering floating field');
        if (!get_option('flek90_enable_field', '1')) {
            $this->debug_log('Floating field disabled');
            return;
        }

        // Load the hardcoded content from the separate file
        $file_path = plugin_dir_path(__FILE__) . 'floating-field-content.php';
        if (!file_exists($file_path)) {
            $this->debug_log('floating-field-content.php not found');
            echo '<div id="flek90-floating-container"><p>Error: floating-field-content.php not found.</p></div>';
            return;
        }

        // Capture the content
        ob_start();
        include $file_path;
        $content = ob_get_clean();

        $this->debug_log('Captured content: ' . substr($content, 0, 100) . '...');

        // Process shortcodes and blocks
        $content = do_blocks($content);
        $content = do_shortcode($content);
        $content = $this->sanitize_content($content);

        $this->debug_log('Processed content: ' . substr($content, 0, 100) . '...');

        // Output the content
        ?>
        <div id="flek90-floating-container">
            <div id="flek90-field-content"><?php echo $content; ?></div>
        </div>
        <?php
    }

    // Add plugin row meta
    public function add_plugin_row_meta($links, $file) {
        $this->debug_log('Adding plugin row meta');
        if (plugin_basename(__FILE__) !== $file) {
            return $links;
        }

        $details = [
            '<a href="#!" class="flek90-details-link" data-details="flek90-details">View Details</a>',
            '<a href="https://example.com/support" target="_blank">Support</a>',
        ];

        $details_content = '
        <div id="flek90-details" style="display:none; margin-top:10px; padding:10px; background:#f9f9f9; border:1px solid #ddd;">
            <h3>A FleK90 Tool Floating Field - Plugin Details</h3>
            <p><strong>Thinking about installing?</strong> Add a floating field with a hardcoded search form (using a bold, black SVG search icon) defined in <code>floating-field-content.php</code>. The form remains in one line on all screen sizes. Manage settings via Settings > Floating Field Settings, no plugins needed. Lightweight, works with older themes, mobile-friendly.</p>
            <ul>
                <li><strong>Key Features:</strong> Hardcoded search form with SVG icon, admin menu management, responsive single-line layout.</li>
                <li><strong>Compatibility:</strong> Works with any theme, including Twenty Ten.</li>
                <li><strong>No Dependencies:</strong> Pure WordPress.</li>
            </ul>
            <p><strong>Already installed?</strong> Go to <a href="' . admin_url('options-general.php?page=flek90-floating-field-settings') . '">Settings > Floating Field Settings</a> to manage settings. Enable the field to show it! Edit <code>floating-field-content.php</code> to customize the content.</p>
            <p><a href="https://example.com/docs" target="_blank">Documentation</a></p>
        </div>';

        return array_merge($links, $details);
    }

    // Display admin notice
    public function display_admin_notice() {
        $this->debug_log('Displaying admin notice');
        if (!get_option('flek90_notice_dismissed') && $this->is_plugin_activated()) {
            ?>
            <div class="notice notice-info is-dismissible flek90-notice">
                <p><strong>Welcome to A FleK90 Tool Floating Field!</strong></p>
                <p>Manage settings via <a href="<?php echo admin_url('options-general.php?page=flek90-floating-field-settings'); ?>">Settings > Floating Field Settings</a>. The field content is hardcoded in <code>floating-field-content.php</code>.</p>
                <p><a href="<?php echo admin_url('?flek90_dismiss_notice=1'); ?>" class="button">Got it, dismiss</a></p>
            </div>
            <?php
        }
    }

    // Handle notice dismissal
    public function handle_notice_dismissal() {
        $this->debug_log('Handling notice dismissal');
        if (isset($_GET['flek90_dismiss_notice']) && $_GET['flek90_dismiss_notice'] == '1') {
            update_option('flek90_notice_dismissed', 1);
            wp_safe_redirect(remove_query_arg('flek90_dismiss_notice'));
            exit;
        }
    }

    // Check if plugin is activated
    private function is_plugin_activated() {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        return is_plugin_active(plugin_basename(__FILE__));
    }

    // Enqueue admin scripts
    public function enqueue_admin_scripts($hook) {
        $this->debug_log('Enqueuing admin scripts for hook: ' . $hook);
        if ($hook === 'settings_page_flek90-floating-field-settings') {
            // Enqueue color picker
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            $admin_js = '
                jQuery(document).ready(function($) {
                    $(".flek90-color-picker").wpColorPicker();
                });
            ';
            wp_add_inline_script('wp-color-picker', $admin_js);
        }
        if ($hook === 'plugins.php') {
            $admin_js = '
                jQuery(document).ready(function($) {
                    $(".flek90-details-link").on("click", function(e) {
                        e.preventDefault();
                        var $details = $("#flek90-details");
                        $details.slideToggle();
                    });
                });
            ';
            wp_add_inline_script('jquery', $admin_js);
        }
    }
}

try {
    new A_FleK90_Tool_Floating_Field();
} catch (Exception $e) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[FleK90 Plugin Error] ' . $e->getMessage());
    }
}
?>