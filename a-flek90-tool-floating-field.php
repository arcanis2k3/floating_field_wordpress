
<?php
/*
Plugin Name: A FleK90 Tool Floating Field
Description: Adds a fixed-position floating field on the front-end with a hardcoded search form (using a bold, black SVG search icon) defined in floating-field-content.php. The form remains in one line on all screen sizes. Includes an admin option to display only on mobile devices or on all devices. Managed via an admin menu page (Settings > Floating Field Settings). Compatible with older themes, no dependencies.
Version: 2.9.1
Author: FleK90
Author URI: https://flek90.aureusz.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.8.1
Stable tag: 2.9.1
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
            update_option('flek90_mobile_only', isset($_POST['flek90_mobile_only']) ? '1' : '0');
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
        $mobile_only = get_option('flek90_mobile_only', '1'); // Default to mobile-only
        $background_color = get_option('flek90_background_color', '#0073aa');
        $font_size = get_option('flek90_font_size', '24');

        // Get plugin data for dynamic version display
        $plugin_file_path = plugin_dir_path(__FILE__) . 'a-flek90-tool-floating-field.php';
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_data = get_plugin_data($plugin_file_path);
        $plugin_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '2.9.1'; // Fallback to literal
        $plugin_name = isset($plugin_data['Name']) ? $plugin_data['Name'] : 'A FleK90 Tool Floating Field';
        $author_name = isset($plugin_data['AuthorName']) ? $plugin_data['AuthorName'] : 'FleK90';
        $author_uri = isset($plugin_data['AuthorURI']) ? $plugin_data['AuthorURI'] : 'https://flek90.aureusz.com';
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
                            <th scope="row"><label for="flek90_mobile_only">Show Only on Mobile Devices</label></th>
                            <td>
                                <input type="checkbox" id="flek90_mobile_only" name="flek90_mobile_only" value="1" <?php checked($mobile_only, '1'); ?>>
                                <p class="description">Check to display the field only on mobile devices (phones and tablets). Uncheck to display on all devices.</p>
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

                <hr>
                <h2>How to Use A FleK90 Tool Floating Field</h2>
                <p>This plugin creates an adjustable floating field that is displayed on the front-end of your website.</p>
                <p><strong>Key Features:</strong></p>
                <ul>
                    <li>Displays a customizable floating field.</li>
                    <li>Content for the field is managed directly within the <code><?php echo esc_html(plugin_dir_path(__FILE__) . 'floating-field-content.php'); ?></code> file in the plugin's directory. You can edit this file to change what appears in the floating field (e.g., text, HTML, forms).</li>
                    <li>The main settings for the plugin, including enabling/disabling the field and appearance options, are available above on this page.</li>
                </ul>
                <p><strong>Admin Menu Location:</strong></p>
                <p>You can always find these settings under <strong>Settings &gt; Floating Field Settings</strong> in your WordPress admin panel.</p>
                <p>To customize the actual content shown in the floating field, please edit the <code>floating-field-content.php</code> file directly.</p>

                <hr>
                <h2>About This Plugin</h2>
                <p><strong>Plugin Name:</strong> <?php echo esc_html($plugin_name); ?></p>
                <p><strong>Version:</strong> <?php echo esc_html($plugin_version); ?></p>
                <p><strong>Author:</strong> <?php echo esc_html($author_name); ?></p>
                <p><strong>Website:</strong> <a href="<?php echo esc_url($author_uri); ?>" target="_blank"><?php echo esc_html($author_uri); ?></a></p>
                <p><strong>Contact the Author:</strong></p>
                <ul>
                    <li>Email: <a href="mailto:flek90@aureusz.com">flek90@aureusz.com</a></li>
                    <li>Email: <a href="mailto:flek90@gmail.com">flek90@gmail.com</a></li>
                </ul>
                <p>This plugin creates an adjustable floating field. The content of this field is defined in the <code><?php echo esc_html(plugin_dir_path(__FILE__) . 'floating-field-content.php'); ?></code> file.</p>
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
        padding: 1px 1px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        max-width: 280px;
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
        padding: 1px 1px;
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
            padding: 2px 2px;
            max-width: 220px;
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

        // Check if the field should only be displayed on mobile devices
        $mobile_only = get_option('flek90_mobile_only', '1');
        if ($mobile_only && !wp_is_mobile()) {
            $this->debug_log('Mobile-only enabled, but not a mobile device, skipping render');
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
            <p><strong>Thinking about installing?</strong> Add a floating field with a hardcoded search form (using a bold, black SVG search icon) defined in <code>floating-field-content.php</code>. Configure it to display only on mobile devices or on all devices via the admin settings. The form remains in one line on all screen sizes. Manage settings via Settings > Floating Field Settings, no plugins needed. Lightweight, works with older themes, mobile-friendly.</p>
            <ul>
                <li><strong>Key Features:</strong> Hardcoded search form with SVG icon, mobile-only option, admin menu management, responsive single-line layout.</li>
                <li><strong>Compatibility:</strong> Works with any theme, including Twenty Ten.</li>
                <li><strong>No Dependencies:</strong> Pure WordPress.</li>
            </ul>
            <p><strong>Already installed?</strong> Go to <a href="' . admin_url('options-general.php?page=flek90-floating-field-settings') . '">Settings > Floating Field Settings</a> to manage settings. Enable the field and configure mobile-only display! Edit <code>floating-field-content.php</code> to customize the content.</p>
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
                <p>Manage settings via <a href="<?php echo admin_url('options-general.php?page=flek90-floating-field-settings'); ?>">Settings > Floating Field Settings</a>. The field content is hardcoded in <code>floating-field-content.php</code>. Configure it to display only on mobile devices or on all devices.</p>
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