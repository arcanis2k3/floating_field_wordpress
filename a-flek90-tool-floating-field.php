<?php
/*
Plugin Name: A FleK90 Tool Floating Field
Description: Adds a fixed-position floating field on the front-end with customizable content. Includes an admin option to display only on mobile devices or on all devices. Managed via an admin menu page (Settings > Floating Field Settings). Compatible with older themes, no dependencies.
Version: 4.2.0
Author: FleK90
Author URI: https://flek90.aureusz.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.8.1
Stable tag: 4.2.0
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Initialize the plugin
class A_FleK90_Tool_Floating_Field {
    public function __construct() {
        $this->debug_log('Plugin initialized');
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'render_floating_field']);
        add_filter('plugin_row_meta', [$this, 'add_plugin_row_meta'], 10, 2);
        add_action('admin_notices', [$this, 'display_admin_notice']);
        add_action('admin_init', [$this, 'handle_notice_dismissal']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('customize_register', [$this, 'setup_customizer']);
        add_action('customize_preview_init', [$this, 'enqueue_customizer_preview_scripts']);
    }

    private function debug_log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            if (is_array($message) || is_object($message)) {
                error_log('[FleK90 Plugin V4.2.0] ' . print_r($message, true));
            } else {
                error_log('[FleK90 Plugin V4.2.0] ' . $message);
            }
        }
    }

    public function enqueue_customizer_preview_scripts() {
        $this->debug_log('Enqueuing Customizer preview scripts.');
        $plugin_version = '4.2.0';
        wp_enqueue_script('flek90-ff-customize-preview', plugin_dir_url(__FILE__) . 'assets/js/customize-preview.js', ['jquery', 'customize-preview'], $plugin_version, true);
    }

    private function get_customizer_defaults() {
        return [
            'desktop_position' => 'top-center', 'desktop_offset_x' => '0px', 'desktop_offset_y' => '20px',
            'mobile_position'  => 'top-center', 'mobile_offset_x'  => '0px', 'mobile_offset_y'  => '10px',
        ];
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

    public static function sanitize_offset_setting($input) {
        return sanitize_text_field($input);
    }

    private function generate_position_css($position, $offset_x, $offset_y) {
        $css_rules = '';
        $offset_x = preg_match('/^(\-?\d+)(px|%|em|rem|vw|vh|auto)$/', $offset_x) ? $offset_x : '0px';
        $offset_y = preg_match('/^(\-?\d+)(px|%|em|rem|vw|vh|auto)$/', $offset_y) ? $offset_y : '0px';
        switch ($position) {
            case 'top-left': $css_rules = "top: {$offset_y}; left: {$offset_x}; right: auto; bottom: auto; transform: none;"; break;
            case 'top-center': $css_rules = "top: {$offset_y}; left: calc(50% + {$offset_x}); right: auto; bottom: auto; transform: translateX(-50%);"; break;
            case 'top-right': $css_rules = "top: {$offset_y}; right: {$offset_x}; left: auto; bottom: auto; transform: none;"; break;
            case 'center-left': $css_rules = "top: calc(50% + {$offset_y}); left: {$offset_x}; right: auto; bottom: auto; transform: translateY(-50%);"; break;
            case 'center-center': $css_rules = "top: calc(50% + {$offset_y}); left: calc(50% + {$offset_x}); right: auto; bottom: auto; transform: translate(-50%, -50%);"; break;
            case 'center-right': $css_rules = "top: calc(50% + {$offset_y}); right: {$offset_x}; left: auto; bottom: auto; transform: translateY(-50%);"; break;
            case 'bottom-left': $css_rules = "bottom: {$offset_y}; left: {$offset_x}; right: auto; top: auto; transform: none;"; break;
            case 'bottom-center': $css_rules = "bottom: {$offset_y}; left: calc(50% + {$offset_x}); right: auto; top: auto; transform: translateX(-50%);"; break;
            case 'bottom-right': $css_rules = "bottom: {$offset_y}; right: {$offset_x}; left: auto; top: auto; transform: none;"; break;
            default: $css_rules = "top: 20px; left: calc(50% + 0px); right: auto; bottom: auto; transform: translateX(-50%);"; break;
        }
        return $css_rules;
    }

    public function setup_customizer($wp_customize) { /* ... same as before ... */
        $this->debug_log('Setting up Customizer');
        $defaults = $this->get_customizer_defaults();
        $option_name = 'flek90_ff_customizer_settings';
        $wp_customize->add_section('flek90_ff_customizer_section', ['title'=>__('Floating Field Display','a-flek90-tool-floating-field'),'priority'=>160,'capability'=>'edit_theme_options']);
        $position_choices = self::get_position_choices();
        $settings_config = [
            'desktop_position' => ['label' => __('Desktop Position', 'a-flek90-tool-floating-field'), 'type' => 'select', 'choices' => $position_choices, 'sanitize' => 'sanitize_position_setting'],
            'desktop_offset_x' => ['label' => __('Desktop Offset X (e.g., 10px, 5%, auto)', 'a-flek90-tool-floating-field'), 'type' => 'text', 'sanitize' => 'sanitize_offset_setting'],
            'desktop_offset_y' => ['label' => __('Desktop Offset Y (e.g., 20px, 2%, auto)', 'a-flek90-tool-floating-field'), 'type' => 'text', 'sanitize' => 'sanitize_offset_setting'],
            'mobile_position'  => ['label' => __('Mobile Position', 'a-flek90-tool-floating-field'), 'type' => 'select', 'choices' => $position_choices, 'sanitize' => 'sanitize_position_setting'],
            'mobile_offset_x'  => ['label' => __('Mobile Offset X (e.g., 0px, 5%, auto)', 'a-flek90-tool-floating-field'), 'type' => 'text', 'sanitize' => 'sanitize_offset_setting'],
            'mobile_offset_y'  => ['label' => __('Mobile Offset Y (e.g., 10px, 2%, auto)', 'a-flek90-tool-floating-field'), 'type' => 'text', 'sanitize' => 'sanitize_offset_setting']
        ];
        foreach ($settings_config as $key => $props) {
            $wp_customize->add_setting($option_name . '[' . $key . ']', ['default' => $defaults[$key], 'type' => 'option', 'capability' => 'edit_theme_options', 'transport' => 'postMessage', 'sanitize_callback' => [__CLASS__, $props['sanitize']]]);
            $control_args = ['label' => $props['label'], 'section' => 'flek90_ff_customizer_section', 'type' => $props['type']];
            if ($props['type'] === 'select') $control_args['choices'] = $props['choices'];
            if ($props['type'] === 'text') $control_args['input_attrs'] = ['placeholder' => $defaults[$key]];
            $wp_customize->add_control($option_name . '[' . $key . ']', $control_args);
        }
    }
    public function add_admin_menu() { /* ... same as before ... */
        $this->debug_log('Adding admin menu page');
        add_options_page('Floating Field Settings', 'Floating Field Settings', 'manage_options', 'flek90-floating-field-settings', [$this, 'render_admin_page']);
    }
    public function render_admin_page() { /* ... same as before ... */
        $this->debug_log('Rendering admin page');
        $option_name_customizer = 'flek90_ff_customizer_settings';

        if (isset($_POST['flek90_save_settings']) && check_admin_referer('flek90_save_settings_action', 'flek90_save_settings_nonce')) {
            update_option('flek90_enable_on_desktop', isset($_POST['flek90_enable_on_desktop']) ? '1' : '0');
            update_option('flek90_enable_on_mobile', isset($_POST['flek90_enable_on_mobile']) ? '1' : '0');

            if (isset($_POST['flek90_field_content'])) { update_option('flek90_field_content', wp_kses_post(wp_unslash($_POST['flek90_field_content']))); }
            if (isset($_POST['flek90_field_content_mobile'])) { update_option('flek90_field_content_mobile', wp_kses_post(wp_unslash($_POST['flek90_field_content_mobile']))); }
            if (isset($_POST['flek90_background_color'])) { update_option('flek90_background_color', sanitize_hex_color(wp_unslash($_POST['flek90_background_color'])));}
            if (isset($_POST['flek90_font_size'])) { update_option('flek90_font_size', absint(wp_unslash($_POST['flek90_font_size'])));}

            $customizer_defaults = $this->get_customizer_defaults();
            $current_pos_settings = get_option($option_name_customizer, $customizer_defaults);
            $current_pos_settings = wp_parse_args($current_pos_settings, $customizer_defaults);
            $pos_keys_to_save = ['desktop_position', 'desktop_offset_x', 'desktop_offset_y', 'mobile_position', 'mobile_offset_x', 'mobile_offset_y'];
            foreach($pos_keys_to_save as $key) {
                if (isset($_POST[$key])) {
                    if (strpos($key, 'position') !== false) {
                        $current_pos_settings[$key] = self::sanitize_position_setting(wp_unslash($_POST[$key]));
                    } else {
                        $current_pos_settings[$key] = self::sanitize_offset_setting(wp_unslash($_POST[$key]));
                    }
                }
            }
            update_option($option_name_customizer, $current_pos_settings);

            if (isset($_POST['flek90_custom_css'])) {
                update_option('flek90_custom_css', wp_strip_all_tags(wp_unslash($_POST['flek90_custom_css'])));
            }
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }

        $field_content = get_option('flek90_field_content', 'Default: %POST_TITLE% - %POST_URL%');
        $field_content_mobile = get_option('flek90_field_content_mobile', '');
        $enable_on_desktop = get_option('flek90_enable_on_desktop', '1');
        $enable_on_mobile = get_option('flek90_enable_on_mobile', '1');
        $background_color = get_option('flek90_background_color', '#0073aa');
        $font_size = get_option('flek90_font_size', '24');
        $custom_css = get_option('flek90_custom_css', '');

        $customizer_defaults_for_display = $this->get_customizer_defaults();
        $position_settings = get_option($option_name_customizer, $customizer_defaults_for_display);
        $position_settings = wp_parse_args($position_settings, $customizer_defaults_for_display);
        $pos_choices = self::get_position_choices();

        $plugin_file_path = __FILE__;
        if (!function_exists('get_plugin_data')) { require_once(ABSPATH . 'wp-admin/includes/plugin.php'); }
        $plugin_data = get_plugin_data($plugin_file_path);
        $plugin_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '4.2.0';
        $plugin_name = isset($plugin_data['Name']) ? $plugin_data['Name'] : 'A FleK90 Tool Floating Field';
        ?>
        <div class="wrap"><h1>Floating Field Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('flek90_save_settings_action', 'flek90_save_settings_nonce'); ?>
                <table class="form-table">
                    <tr><th scope="row"><label for="flek90_enable_on_desktop">Enable on Desktop</label></th><td><input type="checkbox" id="flek90_enable_on_desktop" name="flek90_enable_on_desktop" value="1" <?php checked($enable_on_desktop, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on desktop devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                    <tr><th scope="row"><label for="flek90_enable_on_mobile">Enable on Mobile</label></th><td><input type="checkbox" id="flek90_enable_on_mobile" name="flek90_enable_on_mobile" value="1" <?php checked($enable_on_mobile, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on mobile devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>

                    <tr><th scope="row"><label for="flek90_field_content">Desktop Content</label></th><td><textarea id="flek90_field_content" name="flek90_field_content" rows="5" cols="50" class="large-text"><?php echo esc_textarea($field_content); ?></textarea><p class="description">Enter content for desktop. If mobile content below is empty, this will also be used for mobile. Placeholders: <code>%POST_TITLE%</code>, <code>%POST_URL%</code>.</p></td></tr>
                    <tr><th scope="row"><label for="flek90_field_content_mobile">Mobile Content</label></th><td><textarea id="flek90_field_content_mobile" name="flek90_field_content_mobile" rows="5" cols="50" class="large-text"><?php echo esc_textarea($field_content_mobile); ?></textarea><p class="description">Enter content for mobile devices. If empty, desktop content will be used. Supports HTML and placeholders: <code>%POST_TITLE%</code>, <code>%POST_URL%</code>.</p></td></tr>
                    <tr><th scope="row">Old Field Content Method</th><td><p class="description">Previously, content was hardcoded. It is now managed via the content textareas above. The <code>floating-field-content.php</code> file may be used as a fallback if both content fields are empty.</p></td></tr>

                    <tr valign="top"><td colspan="2"><hr><h3>Position Settings</h3><p class="description">These settings control the field's position and can also be managed (with live preview) via Appearance > Customize > Floating Field Display.</p></td></tr>
                    <tr><th scope="row"><label for="desktop_position">Desktop Position</label></th><td><select id="desktop_position" name="desktop_position"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($position_settings['desktop_position'], $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th scope="row"><label for="desktop_offset_x">Desktop Offset X</label></th><td><input type="text" id="desktop_offset_x" name="desktop_offset_x" value="<?php echo esc_attr($position_settings['desktop_offset_x']); ?>" placeholder="e.g., 0px, 10%"><p class="description">Horizontal offset (e.g., 10px, -5%, auto).</p></td></tr>
                    <tr><th scope="row"><label for="desktop_offset_y">Desktop Offset Y</label></th><td><input type="text" id="desktop_offset_y" name="desktop_offset_y" value="<?php echo esc_attr($position_settings['desktop_offset_y']); ?>" placeholder="e.g., 20px, 2%"><p class="description">Vertical offset (e.g., 20px, -2%, auto).</p></td></tr>
                    <tr><th scope="row"><label for="mobile_position">Mobile Position</label></th><td><select id="mobile_position" name="mobile_position"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($position_settings['mobile_position'], $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th scope="row"><label for="mobile_offset_x">Mobile Offset X</label></th><td><input type="text" id="mobile_offset_x" name="mobile_offset_x" value="<?php echo esc_attr($position_settings['mobile_offset_x']); ?>" placeholder="e.g., 0px, 5%"><p class="description">Horizontal offset for mobile (e.g., 0px, 5%, auto).</p></td></tr>
                    <tr><th scope="row"><label for="mobile_offset_y">Mobile Offset Y</label></th><td><input type="text" id="mobile_offset_y" name="mobile_offset_y" value="<?php echo esc_attr($position_settings['mobile_offset_y']); ?>" placeholder="e.g., 10px, 2%"><p class="description">Vertical offset for mobile (e.g., 10px, 2%, auto).</p></td></tr>

                    <tr><td colspan="2"><hr><h3>Appearance Settings</h3></td></tr>
                    <tr><th scope="row"><label for="flek90_background_color">Background Color</label></th><td><input type="text" id="flek90_background_color" name="flek90_background_color" value="<?php echo esc_attr($background_color); ?>" class="flek90-color-picker"><p class="description">Select background color (default: blue).</p></td></tr>
                    <tr><th scope="row"><label for="flek90_font_size">Font Size (px)</label></th><td><input type="number" id="flek90_font_size" name="flek90_font_size" value="<?php echo esc_attr($font_size); ?>" min="12" max="48" step="1"><p class="description">Set font size (12–48px, default: 24px).</p></td></tr>

                    <tr valign="top"><td colspan="2"><hr><h3>Custom CSS</h3></td></tr>
                    <tr valign="top"><th scope="row"><label for="flek90_custom_css">Custom CSS Rules</label></th>
                        <td><textarea id="flek90_custom_css" name="flek90_custom_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
                            <p class="description"><?php esc_html_e('Add your own CSS rules here to customize the floating field. These rules will be applied after the default styles. Example: #flek90-floating-container { border: 2px solid red !important; }', 'a-flek90-tool-floating-field'); ?></p>
                        </td></tr>
                </table>
                <p class="submit"><input type="submit" name="flek90_save_settings" class="button button-primary" value="Save Settings"></p>
            </form>
            <!-- ... How to Use and About sections ... -->
        </div>
        <?php
    }

    public function sanitize_content($input) { /* ... */ return $input; }
    public function enqueue_scripts() {
        $this->debug_log('Enqueuing front-end scripts for floating field');

        wp_register_style('flek90-floating-field-inline', false);
        wp_enqueue_style('flek90-floating-field-inline');

        $customizer_defaults = $this->get_customizer_defaults();
        $customizer_settings = get_option('flek90_ff_customizer_settings', $customizer_defaults);
        $customizer_settings = wp_parse_args($customizer_settings, $customizer_defaults);
        $this->debug_log(['Customizer Settings Loaded' => $customizer_settings]);

        $desktop_pos_css = $this->generate_position_css($customizer_settings['desktop_position'],$customizer_settings['desktop_offset_x'],$customizer_settings['desktop_offset_y']);
        $this->debug_log(['Desktop Position CSS' => $desktop_pos_css]);
        $mobile_pos_css = $this->generate_position_css($customizer_settings['mobile_position'],$customizer_settings['mobile_offset_x'],$customizer_settings['mobile_offset_y']);
        $this->debug_log(['Mobile Position CSS' => $mobile_pos_css]);

        $background_color = get_option('flek90_background_color', '#0073aa');
        $font_size = get_option('flek90_font_size', '24');
        $css = "
    #flek90-floating-container {
        position: fixed !important; {$desktop_pos_css} z-index: 9999; background: " . esc_attr($background_color) . "; color: #fff; padding: 1px 1px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 280px; width: auto; text-align: center; font-size: " . esc_attr($font_size) . "px; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
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
        #flek90-floating-container { {$mobile_pos_css} padding: 2px 2px; max-width: 220px; font-size: " . esc_attr(max(12, $font_size - 4)) . "px; }
        #flek90-floating-container form#searchform { gap: 5px; }
        #flek90-floating-container input.search-input { width: 150px; font-size: 13px; padding: 6px 10px; }
        #flek90-floating-container button.search-submit { width: 28px; height: 28px; }
        #flek90-floating-container button.search-submit svg { width: 14px; height: 14px; }
    }";

        $custom_css_option = get_option('flek90_custom_css', '');
        $trimmed_custom_css = trim($custom_css_option);
        if (!empty($trimmed_custom_css)) {
            $css .= "\n\n/* Custom CSS from Plugin Settings */\n" . $trimmed_custom_css; // Line re-enabled
            $this->debug_log('Appended custom CSS from settings. Snippet: ' . substr($trimmed_custom_css, 0, 100));
        } else {
            $this->debug_log('No custom CSS from settings to append.');
        }
        $this->debug_log("Final CSS to be added: \n" . substr($css, 0, 500) . (strlen($css) > 500 ? "..." : ""));
        wp_add_inline_style('flek90-floating-field-inline', $css);
    }

    public function render_floating_field() { /* ... same as before ... */ }
    public function add_plugin_row_meta($links, $file) { /* ... */ return $links;}
    public function display_admin_notice() { /* ... */ }
    public function handle_notice_dismissal() { /* ... */ }
    private function is_plugin_activated() { /* ... */ return true;}
    public function enqueue_admin_scripts($hook) { /* ... */ }
}
try { new A_FleK90_Tool_Floating_Field(); } catch (Exception $e) { if (defined('WP_DEBUG') && WP_DEBUG) { error_log('[FleK90 Plugin V4.2.0] ' . $e->getMessage()); }}
?>
