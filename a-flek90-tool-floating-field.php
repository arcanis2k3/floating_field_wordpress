<?php
/*
Plugin Name: A FleK90 Tool Floating Field
Description: Adds a fixed-position floating field on the front-end with customizable content. Includes an admin option to display only on mobile devices or on all devices. Managed via an admin menu page (Settings > Floating Field Settings). Compatible with older themes, no dependencies.
Version: 5.0.1
Author: FleK90
Author URI: https://flek90.aureusz.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.8.1
Stable tag: 5.0.1
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Initialize the plugin
class A_FleK90_Tool_Floating_Field {
    private $plugin_version = '5.0.1';

    public function __construct() {
        $this->debug_log('Plugin initialized');
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'render_floating_field']);
        add_filter('plugin_row_meta', [$this, 'add_plugin_row_meta'], 10, 2);
        add_action('admin_notices', [$this, 'display_admin_notice']);
        add_action('admin_init', [$this, 'handle_notice_dismissal']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        // Customizer hooks removed
    }

    private function debug_log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            if (is_array($message) || is_object($message)) {
                error_log('[FleK90 Plugin V' . $this->plugin_version . '] ' . print_r($message, true));
            } else {
                error_log('[FleK90 Plugin V' . $this->plugin_version . '] ' . $message);
            }
        }
    }

    private static function get_position_choices() {
        return [
            'top-left'      => __('Top Left', 'a-flek90-tool-floating-field'),
            'top-center'    => __('Top Center', 'a-flek90-tool-floating-field'),
            'top-right'     => __('Top Right', 'a-flek90-tool-floating-field'),
            'center-left'   => __('Center Left', 'a-flek90-tool-floating-field'),
            'center-center' => __('Center Center', 'a-flek90-tool-floating-field'),
            'center-right'  => __('Center Right', 'a-flek90-tool-floating-field'),
            'bottom-left'   => __('Bottom Left', 'a-flek90-tool-floating-field'),
            'bottom-center' => __('Bottom Center', 'a-flek90-tool-floating-field'),
            'bottom-right'  => __('Bottom Right', 'a-flek90-tool-floating-field'),
        ];
    }

    public static function sanitize_position_setting($input) {
        return in_array($input, array_keys(self::get_position_choices()), true) ? $input : 'top-center';
    }

    private function generate_position_css_v5($position) {
        $css_rules = '';
        $default_offset_y = '20px';
        $default_offset_x = '20px';
        switch ($position) {
            case 'top-left': $css_rules = "top: {$default_offset_y}; left: {$default_offset_x}; right: auto; bottom: auto; transform: none;"; break;
            case 'top-center': $css_rules = "top: {$default_offset_y}; left: 50%; right: auto; bottom: auto; transform: translateX(-50%);"; break;
            case 'top-right': $css_rules = "top: {$default_offset_y}; right: {$default_offset_x}; left: auto; bottom: auto; transform: none;"; break;
            case 'center-left': $css_rules = "top: 50%; left: {$default_offset_x}; right: auto; bottom: auto; transform: translateY(-50%);"; break;
            case 'center-center': $css_rules = "top: 50%; left: 50%; right: auto; bottom: auto; transform: translate(-50%, -50%);"; break;
            case 'center-right': $css_rules = "top: 50%; right: {$default_offset_x}; left: auto; bottom: auto; transform: translateY(-50%);"; break;
            case 'bottom-left': $css_rules = "bottom: {$default_offset_y}; left: {$default_offset_x}; right: auto; top: auto; transform: none;"; break;
            case 'bottom-center': $css_rules = "bottom: {$default_offset_y}; left: 50%; right: auto; top: auto; transform: translateX(-50%);"; break;
            case 'bottom-right': $css_rules = "bottom: {$default_offset_y}; right: {$default_offset_x}; left: auto; top: auto; transform: none;"; break;
            default: $css_rules = "top: 20px; left: 50%; transform: translateX(-50%);"; break;
        }
        return $css_rules;
    }

    public function add_admin_menu() {
        $this->debug_log('Adding admin menu page');
        // add_options_page('Floating Field Settings', 'Floating Field Settings', 'manage_options', 'flek90-floating-field-settings', [$this, 'render_admin_page']);

        // Add the new top-level "FleK90" menu
        add_menu_page(
            __( 'FleK90 Tools', 'a-flek90-tool-floating-field' ), // Page title
            __( 'FleK90', 'a-flek90-tool-floating-field' ),       // Menu title
            'manage_options',                                 // Capability
            'flek90_main_menu_slug',                          // Menu slug (this will be the parent slug)
            [$this, 'flek90_main_menu_page_html_callback'],   // Callback function for the top-level page content
            'dashicons-admin-generic',                        // Icon
            75                                                // Position
        );

        // Add the "Floating Field Settings" page as a submenu
        add_submenu_page(
            'flek90_main_menu_slug',                          // Parent slug
            __( 'Floating Field Settings', 'a-flek90-tool-floating-field' ), // Page title
            __( 'Floating Field', 'a-flek90-tool-floating-field' ),          // Menu title for submenu item
            'manage_options',                                 // Capability
            'flek90_floating_field_settings_slug',            // Menu slug for this submenu page
            [$this, 'render_admin_page']                      // Existing callback function to render the settings page
        );
    }

    // Placeholder callback for the main menu page
    public function flek90_main_menu_page_html_callback() {
        echo '<div class="wrap"><h1>' . esc_html__( 'FleK90 Tools Dashboard', 'a-flek90-tool-floating-field' ) . '</h1>';
        echo '<p>' . esc_html__( 'Welcome to the FleK90 Tools main dashboard. Please select a tool from the submenu.', 'a-flek90-tool-floating-field' ) . '</p></div>';
    }

    public function render_admin_page() {
        $this->debug_log('Rendering admin page V5'); // Version will be updated by class property
        $plugin_version_display = $this->plugin_version;

        if (isset($_POST['flek90_save_settings']) && check_admin_referer('flek90_save_settings_action', 'flek90_save_settings_nonce')) {
            update_option('flek90_enable_on_desktop_v5', isset($_POST['flek90_enable_on_desktop_v5']) ? '1' : '0');
            update_option('flek90_enable_on_mobile_v5', isset($_POST['flek90_enable_on_mobile_v5']) ? '1' : '0');
            // if (isset($_POST['flek90_desktop_content_v5'])) { update_option('flek90_desktop_content_v5', wp_kses_post(wp_unslash($_POST['flek90_desktop_content_v5']))); }
            // if (isset($_POST['flek90_mobile_content_v5'])) { update_option('flek90_mobile_content_v5', wp_kses_post(wp_unslash($_POST['flek90_mobile_content_v5']))); }
            if (isset($_POST['flek90_desktop_position_v5'])) { update_option('flek90_desktop_position_v5', self::sanitize_position_setting(wp_unslash($_POST['flek90_desktop_position_v5']))); }
            if (isset($_POST['flek90_mobile_position_v5'])) { update_option('flek90_mobile_position_v5', self::sanitize_position_setting(wp_unslash($_POST['flek90_mobile_position_v5']))); }
            if (isset($_POST['flek90_background_color_v5'])) { update_option('flek90_background_color_v5', sanitize_hex_color(wp_unslash($_POST['flek90_background_color_v5'])));}
            if (isset($_POST['flek90_font_size_v5'])) { update_option('flek90_font_size_v5', absint(wp_unslash($_POST['flek90_font_size_v5'])));}
            if (isset($_POST['flek90_custom_css_v5'])) { update_option('flek90_custom_css_v5', wp_strip_all_tags(wp_unslash($_POST['flek90_custom_css_v5']))); }

            if (get_option('flek90_ff_customizer_settings') !== false) {
                delete_option('flek90_ff_customizer_settings');
                $this->debug_log('Old option flek90_ff_customizer_settings deleted.');
            }
            $old_options = ['flek90_enable_field', 'flek90_mobile_only', 'flek90_field_content', 'flek90_background_color', 'flek90_font_size', 'flek90_custom_css'];
            foreach ($old_options as $old_opt) {
                if (get_option($old_opt) !== false) { delete_option($old_opt); $this->debug_log("Old option {$old_opt} deleted.");}
            }
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }

        $enable_on_desktop_v5 = get_option('flek90_enable_on_desktop_v5', '1');
        $enable_on_mobile_v5 = get_option('flek90_enable_on_mobile_v5', '1');
        // $desktop_content_v5 = get_option('flek90_desktop_content_v5', 'Desktop Content V5: %POST_TITLE%');
        // $mobile_content_v5 = get_option('flek90_mobile_content_v5', 'Mobile Content V5: %POST_TITLE%');
        $desktop_position_v5 = get_option('flek90_desktop_position_v5', 'top-center');
        $mobile_position_v5 = get_option('flek90_mobile_position_v5', 'top-center');
        $background_color_v5 = get_option('flek90_background_color_v5', '#0073aa');
        $font_size_v5 = get_option('flek90_font_size_v5', '24');
        $custom_css_v5 = get_option('flek90_custom_css_v5', '');

        $pos_choices = self::get_position_choices();

        // Get plugin name for display, but use class property for version.
        $plugin_name = 'A FleK90 Tool Floating Field'; // Default
        if (function_exists('get_plugin_data')) {
            $plugin_data = get_plugin_data(__FILE__);
            $plugin_name = isset($plugin_data['Name']) ? $plugin_data['Name'] : $plugin_name;
        }
        ?>
        <div class="wrap"><h1><?php echo esc_html($plugin_name); ?> Settings - v<?php echo esc_html($plugin_version_display); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('flek90_save_settings_action', 'flek90_save_settings_nonce'); ?>
                <table class="form-table">
                    <tr valign="top"><td colspan="2"><h3>Display Control</h3></td></tr>
                    <tr><th scope="row"><label for="flek90_enable_on_desktop_v5">Enable on Desktop</label></th><td><input type="checkbox" id="flek90_enable_on_desktop_v5" name="flek90_enable_on_desktop_v5" value="1" <?php checked($enable_on_desktop_v5, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on desktop devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                    <tr><th scope="row"><label for="flek90_enable_on_mobile_v5">Enable on Mobile</label></th><td><input type="checkbox" id="flek90_enable_on_mobile_v5" name="flek90_enable_on_mobile_v5" value="1" <?php checked($enable_on_mobile_v5, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on mobile devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>

                    <tr valign="top"><td colspan="2"><hr><h3>Content Settings</h3></td></tr>
                    <tr valign="top">
                        <td colspan="2" style="padding-top: 0;">
                            <p class="description">
                                Content for the floating field is now managed by editing PHP files directly within the plugin's directory:
                            </p>
                            <ul style="list-style: disc; margin-left: 20px;">
                                <li><strong>Desktop Content:</strong> Edit the file <code>content-desktop.php</code>.</li>
                                <li><strong>Mobile Content:</strong> Edit the file <code>content-mobile.php</code>.</li>
                                <li><strong>Fallback Content:</strong> Edit the file <code>floating-field-content.php</code>. This file is used if the device-specific file (desktop or mobile) is not found or is empty.</li>
                            </ul>
                            <p class="description">
                                You can include any HTML, shortcodes, or plain text in these files. Remember that PHP execution is not recommended directly within these content files for security reasons, beyond simple includes or template tags if absolutely necessary and understood.
                            </p>
                        </td>
                    </tr>
                    <?php /* ?>
                    <tr><th scope="row"><label for="flek90_desktop_content_v5">Desktop Content</label></th><td><textarea id="flek90_desktop_content_v5" name="flek90_desktop_content_v5" rows="5" cols="50" class="large-text"><?php echo esc_textarea($desktop_content_v5); ?></textarea><p class="description">Enter content for desktop. If mobile content below is empty, this will also be used for mobile. Placeholders: <code>%POST_TITLE%</code>, <code>%POST_URL%</code>.</p></td></tr>
                    <tr><th scope="row"><label for="flek90_mobile_content_v5">Mobile Content</label></th><td><textarea id="flek90_mobile_content_v5" name="flek90_mobile_content_v5" rows="5" cols="50" class="large-text"><?php echo esc_textarea($mobile_content_v5); ?></textarea><p class="description">Enter content for mobile devices. If empty, desktop content will be used. Supports HTML and placeholders: <code>%POST_TITLE%</code>, <code>%POST_URL%</code>.</p></td></tr>
                    <?php */ ?>

                    <tr valign="top"><td colspan="2"><hr><h3>Position Settings (Simplified)</h3><p class="description">Select a general position. Offsets are no longer configured on this page.</p></td></tr>
                    <tr><th scope="row"><label for="flek90_desktop_position_v5">Desktop Position</label></th><td><select id="flek90_desktop_position_v5" name="flek90_desktop_position_v5"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($desktop_position_v5, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th scope="row"><label for="flek90_mobile_position_v5">Mobile Position</label></th><td><select id="flek90_mobile_position_v5" name="flek90_mobile_position_v5"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($mobile_position_v5, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>

                    <tr valign="top"><td colspan="2"><hr><h3>Appearance Settings</h3></td></tr>
                    <tr><th scope="row"><label for="flek90_background_color_v5">Background Color</label></th><td><input type="text" id="flek90_background_color_v5" name="flek90_background_color_v5" value="<?php echo esc_attr($background_color_v5); ?>" class="flek90-color-picker"><p class="description">Select background color (default: blue).</p></td></tr>
                    <tr><th scope="row"><label for="flek90_font_size_v5">Font Size (px)</label></th><td><input type="number" id="flek90_font_size_v5" name="flek90_font_size_v5" value="<?php echo esc_attr($font_size_v5); ?>" min="12" max="48" step="1"><p class="description">Set font size (12–48px, default: 24px).</p></td></tr>

                    <tr valign="top"><td colspan="2"><hr><h3>Custom CSS</h3></td></tr>
                    <tr valign="top"><th scope="row"><label for="flek90_custom_css_v5">Custom CSS Rules</label></th>
                        <td><textarea id="flek90_custom_css_v5" name="flek90_custom_css_v5" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_css_v5); ?></textarea>
                            <p class="description"><?php esc_html_e('Add your own CSS rules here to customize the floating field. These rules will be applied after the default styles. Example: #flek90-floating-container { border: 2px solid red !important; }', 'a-flek90-tool-floating-field'); ?></p>
                        </td></tr>
                </table>
                <p class="submit"><input type="submit" name="flek90_save_settings" class="button button-primary" value="Save Settings"></p>
            </form>
        </div>
        <?php
    }

    public function sanitize_content($input) {
        // Sanitize content using WordPress VIP standards for outputting HTML
        // Allows common HTML tags and attributes for formatting.
        $allowed_html = [
            'a'      => ['href' => true, 'title' => true, 'target' => true, 'rel' => true],
            'br'     => [],
            'em'     => [],
            'strong' => [],
            'p'      => ['class' => true, 'style' => true],
            'ul'     => ['class' => true, 'style' => true],
            'ol'     => ['class' => true, 'style' => true],
            'li'     => ['class' => true, 'style' => true],
            'span'   => ['class' => true, 'style' => true],
            'div'    => ['class' => true, 'style' => true],
            'img'    => ['src' => true, 'alt' => true, 'width' => true, 'height' => true, 'class' => true, 'style' => true],
            // Add other tags as needed, e.g., h1-h6, blockquote, etc.
            // Be restrictive by default.
        ];
        // For attributes like 'style', consider more specific sanitization if possible,
        // or ensure that the input source is trusted. wp_kses_post is generally safer.
        return wp_kses_post($input); // wp_kses_post is a good general sanitizer for post content.
    }

    public function enqueue_scripts() {
        $this->debug_log('Enqueuing front-end scripts for floating field - V5'); // Version will be updated by class property

        wp_register_style('flek90-floating-field-inline', false, [], $this->plugin_version);
        wp_enqueue_style('flek90-floating-field-inline');

        $desktop_position_v5 = get_option('flek90_desktop_position_v5', 'top-center');
        $mobile_position_v5 = get_option('flek90_mobile_position_v5', 'top-center');
        $this->debug_log(['V5 Position Settings Loaded for CSS' => ['desktop' => $desktop_position_v5, 'mobile' => $mobile_position_v5]]);

        $desktop_pos_css = $this->generate_position_css_v5($desktop_position_v5);
        $this->debug_log(['V5 Desktop Position CSS' => $desktop_pos_css]);

        $mobile_pos_css = $this->generate_position_css_v5($mobile_position_v5);
        $this->debug_log(['V5 Mobile Position CSS' => $mobile_pos_css]);

        $background_color_v5 = get_option('flek90_background_color_v5', '#0073aa');
        $font_size_v5 = get_option('flek90_font_size_v5', '24');
        $css = "
    #flek90-floating-container {
        position: fixed !important; {$desktop_pos_css} z-index: 9999; background: " . esc_attr($background_color_v5) . "; color: #fff; padding: 1px 1px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 280px; width: auto; text-align: center; font-size: " . esc_attr($font_size_v5) . "px; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    #flek90-floating-container * { color: inherit; line-height: 1.5; }
    #flek90-floating-container a { color: #fff; text-decoration: underline; }
    #flek90-floating-container a:hover { color: #ddd; }
    #flek90-floating-container form#searchform { display: flex; align-items: center; gap: 10px; flex-direction: row; }
    #flek90-floating-container input.search-input { background: #fff; color: #333; border: 1px solid #ccc; padding: 1px 1px; font-size: 14px; width: 200px; border-radius: 4px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); transition: border-color 0.2s ease-in-out; }
    #flek90-floating-container input.search-input:focus { border-color: #007cba; outline: none; box-shadow: 0 0 0 1px #007cba; }
    #flek90-floating-container button.search-submit { background: #fff; color: #333; border: 1px solid #ccc; padding: 0; width: 32px; height: 32px; border-radius: 4px; cursor: pointer; transition: background-color 0.2s ease-in-out; display: flex; align-items: center; justify-content: center; }
    #flek90-floating-container button.search-submit:hover { background: #f5f5f5; }
    #flek90-floating-container button.search-submit svg { width: 16px; height: 16px; stroke: #000; stroke-width: 3; }
    @media (max-width: 768px) {
        #flek90-floating-container { {$mobile_pos_css} padding: 2px 2px; max-width: 220px; font-size: " . esc_attr(max(12, (int)$font_size_v5 - 4)) . "px; }
        #flek90-floating-container form#searchform { gap: 5px; }
        #flek90-floating-container input.search-input { width: 150px; font-size: 13px; padding: 6px 10px; }
        #flek90-floating-container button.search-submit { width: 28px; height: 28px; }
        #flek90-floating-container button.search-submit svg { width: 14px; height: 14px; }
    }";

        $custom_css_v5 = get_option('flek90_custom_css_v5', '');
        $trimmed_custom_css = trim($custom_css_v5);
        if (!empty($trimmed_custom_css)) {
            $css .= "\n\n/* Custom CSS from Plugin Settings */\n" . $trimmed_custom_css;
            $this->debug_log('Appended custom CSS from settings (V5). Snippet: ' . substr($trimmed_custom_css, 0, 100));
        } else {
            $this->debug_log('No custom CSS from settings to append (V5).');
        }
        $this->debug_log("Final V5 CSS to be added: \n" . substr($css, 0, 500) . (strlen($css) > 500 ? "..." : ""));
        wp_add_inline_style('flek90-floating-field-inline', $css);
    }

public function render_floating_field() {
    $this->debug_log('Rendering floating field (V5.1 - Device Specific Content)');

    $is_mobile = wp_is_mobile();
    $enabled_on_desktop = get_option('flek90_enable_on_desktop_v5', '1');
    $enabled_on_mobile = get_option('flek90_enable_on_mobile_v5', '1');

    if (($is_mobile && $enabled_on_mobile !== '1') || (!$is_mobile && $enabled_on_desktop !== '1')) {
        $this->debug_log('Floating field display check: Condition not met for current device. Mobile: ' . ($is_mobile ? 'Yes' : 'No') . ', Desktop Enabled: ' . $enabled_on_desktop . ', Mobile Enabled: ' . $enabled_on_mobile . '. Field will NOT render.');
        return;
    }

    if ($enabled_on_desktop !== '1' && $enabled_on_mobile !== '1') {
         $this->debug_log('Floating field display check: Globally disabled (both desktop and mobile are OFF). Field will NOT render.');
         return;
    }

    $this->debug_log('Floating field display check: Conditions met. Proceeding to render.');

    $content = '';
    $loaded_file_path = '';

    if ($is_mobile) {
        $specific_file_path = plugin_dir_path(__FILE__) . 'content-mobile.php';
        if (file_exists($specific_file_path)) {
            $this->debug_log('Attempting to load mobile content from: ' . $specific_file_path);
            ob_start();
            include $specific_file_path;
            $content = ob_get_clean();
            $loaded_file_path = 'content-mobile.php';
            if (empty(trim($content))) {
                $this->debug_log('Mobile content file (' . $loaded_file_path . ') is empty. Clearing content to trigger fallback.');
                $content = ''; // Ensure fallback if file is empty
            }
        } else {
            $this->debug_log('Mobile content file not found: ' . $specific_file_path);
        }
    } else {
        $specific_file_path = plugin_dir_path(__FILE__) . 'content-desktop.php';
        if (file_exists($specific_file_path)) {
            $this->debug_log('Attempting to load desktop content from: ' . $specific_file_path);
            ob_start();
            include $specific_file_path;
            $content = ob_get_clean();
            $loaded_file_path = 'content-desktop.php';
            if (empty(trim($content))) {
                $this->debug_log('Desktop content file (' . $loaded_file_path . ') is empty. Clearing content to trigger fallback.');
                $content = ''; // Ensure fallback if file is empty
            }
        } else {
            $this->debug_log('Desktop content file not found: ' . $specific_file_path);
        }
    }

    // Fallback to floating-field-content.php if specific content is empty or file not found
    if (empty(trim($content))) {
        $this->debug_log('Specific content not loaded or empty. Attempting fallback to floating-field-content.php.');
        $fallback_file_path = plugin_dir_path(__FILE__) . 'floating-field-content.php';
        if (file_exists($fallback_file_path)) {
            ob_start();
            include $fallback_file_path;
            $content = ob_get_clean();
            $loaded_file_path = 'floating-field-content.php (fallback)';
            if (empty(trim($content))) {
                $this->debug_log('Fallback content file (floating-field-content.php) is also empty.');
                $content = ''; // Still empty
            }
        } else {
            $this->debug_log('Fallback content file (floating-field-content.php) not found.');
        }
    }

    if (!empty(trim($content))) {
         $this->debug_log('Captured content from ' . $loaded_file_path . '. Raw length: ' . strlen($content));
    } else {
        $this->debug_log('All content sources (specific, fallback) are empty or not found. Using default error/empty message.');
        // Check if the initial specific file was supposed to exist but was empty, or just not found
        $main_content_file_to_check = $is_mobile ? 'content-mobile.php' : 'content-desktop.php';
        if (!file_exists(plugin_dir_path(__FILE__) . $main_content_file_to_check) && !file_exists(plugin_dir_path(__FILE__) . 'floating-field-content.php')) {
            echo '<div id="flek90-floating-container" style="display:block !important; visibility:visible !important; opacity:1 !important; position:fixed !important; top: 10px; left: 10px; background:red !important; color:white !important; z-index: 100000 !important; padding: 10px !important;"><p>Error: Content files (e.g., ' . esc_html($main_content_file_to_check) . ' or floating-field-content.php) not found.</p></div>';
            return;
        } else {
            $content = '<p style="margin:0; padding:5px;">Floating field content is empty. Please edit the relevant content file (e.g., ' . esc_html($main_content_file_to_check) . ' or floating-field-content.php) to add your desired HTML.</p>';
             $this->debug_log('Using placeholder message for empty content.');
        }
    }

    $content = do_blocks($content);
    $content = do_shortcode($content);
    $content = $this->sanitize_content($content);

    $this->debug_log('Processed and sanitized content. Final length: ' . strlen($content));

    ?>
    <div id="flek90-floating-container">
        <div id="flek90-field-content"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is sanitized by $this->sanitize_content ?></div>
    </div>
    <?php
}
    public function add_plugin_row_meta($links, $file) { /* ... */ return $links;}
    public function display_admin_notice() { /* ... */ }
    public function handle_notice_dismissal() { /* ... */ }
    private function is_plugin_activated() { /* ... */ return true;}
    public function enqueue_admin_scripts($hook) { /* ... */ }
}
try { new A_FleK90_Tool_Floating_Field(); } catch (Exception $e) { if (defined('WP_DEBUG') && WP_DEBUG) { error_log('[FleK90 Plugin V' . $this->plugin_version . '] ' . $e->getMessage()); }} // Updated to use class property
?>
