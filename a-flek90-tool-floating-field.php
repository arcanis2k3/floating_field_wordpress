<?php
/*
Plugin Name: A FleK90 Tool Floating Field
Description: Adds a fixed-position floating field on the front-end with customizable content. Includes an admin option to display only on mobile devices or on all devices. Managed via an admin menu page (Settings > Floating Field Settings). Compatible with older themes, no dependencies.
Version: 4.1.0
Author: FleK90
Author URI: https://flek90.aureusz.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.8.1
Stable tag: 4.1.0
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
            error_log('[FleK90 Plugin V4.1.0] ' . $message);
        }
    }

    public function enqueue_customizer_preview_scripts() {
        $this->debug_log('Enqueuing Customizer preview scripts.');
        $plugin_version = '4.1.0';
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

    public function setup_customizer($wp_customize) {
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

    public function add_admin_menu() {
        $this->debug_log('Adding admin menu page');
        add_options_page('Floating Field Settings', 'Floating Field Settings', 'manage_options', 'flek90-floating-field-settings', [$this, 'render_admin_page']);
    }

    public function render_admin_page() {
        $this->debug_log('Rendering admin page');
        $option_name_customizer = 'flek90_ff_customizer_settings';

        if (isset($_POST['flek90_save_settings']) && check_admin_referer('flek90_save_settings_action', 'flek90_save_settings_nonce')) {
            update_option('flek90_enable_field', isset($_POST['flek90_enable_field']) ? '1' : '0');
            update_option('flek90_mobile_only', isset($_POST['flek90_mobile_only']) ? '1' : '0');
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
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }

        $field_content = get_option('flek90_field_content', 'Default: %POST_TITLE% - %POST_URL%');
        $field_content_mobile = get_option('flek90_field_content_mobile', '');
        $enable_field = get_option('flek90_enable_field', '1');
        $mobile_only = get_option('flek90_mobile_only', '1');
        $background_color = get_option('flek90_background_color', '#0073aa');
        $font_size = get_option('flek90_font_size', '24');

        $customizer_defaults_for_display = $this->get_customizer_defaults();
        $position_settings = get_option($option_name_customizer, $customizer_defaults_for_display);
        $position_settings = wp_parse_args($position_settings, $customizer_defaults_for_display);
        $pos_choices = self::get_position_choices();

        $plugin_file_path = __FILE__;
        if (!function_exists('get_plugin_data')) { require_once(ABSPATH . 'wp-admin/includes/plugin.php'); }
        $plugin_data = get_plugin_data($plugin_file_path);
        $plugin_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '4.1.0';
        $plugin_name = isset($plugin_data['Name']) ? $plugin_data['Name'] : 'A FleK90 Tool Floating Field';
        ?>
        <div class="wrap"><h1>Floating Field Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('flek90_save_settings_action', 'flek90_save_settings_nonce'); ?>
                <table class="form-table">
                    <tr><th scope="row"><label for="flek90_enable_field">Enable Floating Field</label></th><td><input type="checkbox" id="flek90_enable_field" name="flek90_enable_field" value="1" <?php checked($enable_field, '1'); ?>><p class="description">...</p></td></tr>
                    <tr><th scope="row"><label for="flek90_mobile_only">Show Only on Mobile Devices</label></th><td><input type="checkbox" id="flek90_mobile_only" name="flek90_mobile_only" value="1" <?php checked($mobile_only, '1'); ?>><p class="description">...</p></td></tr>
                    <tr><th scope="row"><label for="flek90_field_content">Desktop Content</label></th><td><textarea id="flek90_field_content" name="flek90_field_content" rows="5" cols="50" class="large-text"><?php echo esc_textarea($field_content); ?></textarea><p class="description">...</p></td></tr>
                    <tr><th scope="row"><label for="flek90_field_content_mobile">Mobile Content</label></th><td><textarea id="flek90_field_content_mobile" name="flek90_field_content_mobile" rows="5" cols="50" class="large-text"><?php echo esc_textarea($field_content_mobile); ?></textarea><p class="description">...</p></td></tr>
                    <tr><th scope="row">Old Field Content Method</th><td><p class="description">...</p></td></tr>
                    <tr valign="top"><td colspan="2"><hr><h3>Position Settings</h3><p class="description">These settings control the field's position and can also be managed (with live preview) via Appearance > Customize > Floating Field Display.</p></td></tr>
                    <tr><th scope="row"><label for="desktop_position">Desktop Position</label></th><td><select id="desktop_position" name="desktop_position"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($position_settings['desktop_position'], $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th scope="row"><label for="desktop_offset_x">Desktop Offset X</label></th><td><input type="text" id="desktop_offset_x" name="desktop_offset_x" value="<?php echo esc_attr($position_settings['desktop_offset_x']); ?>" placeholder="e.g., 0px, 10%"><p class="description">Horizontal offset (e.g., 10px, -5%, auto).</p></td></tr>
                    <tr><th scope="row"><label for="desktop_offset_y">Desktop Offset Y</label></th><td><input type="text" id="desktop_offset_y" name="desktop_offset_y" value="<?php echo esc_attr($position_settings['desktop_offset_y']); ?>" placeholder="e.g., 20px, 2%"><p class="description">Vertical offset (e.g., 20px, -2%, auto).</p></td></tr>
                    <tr><th scope="row"><label for="mobile_position">Mobile Position</label></th><td><select id="mobile_position" name="mobile_position"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($position_settings['mobile_position'], $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th scope="row"><label for="mobile_offset_x">Mobile Offset X</label></th><td><input type="text" id="mobile_offset_x" name="mobile_offset_x" value="<?php echo esc_attr($position_settings['mobile_offset_x']); ?>" placeholder="e.g., 0px, 5%"><p class="description">Horizontal offset for mobile (e.g., 0px, 5%, auto).</p></td></tr>
                    <tr><th scope="row"><label for="mobile_offset_y">Mobile Offset Y</label></th><td><input type="text" id="mobile_offset_y" name="mobile_offset_y" value="<?php echo esc_attr($position_settings['mobile_offset_y']); ?>" placeholder="e.g., 10px, 2%"><p class="description">Vertical offset for mobile (e.g., 10px, 2%, auto).</p></td></tr>
                    <tr><td colspan="2"><hr><h3>Appearance Settings</h3></td></tr>
                    <tr><th scope="row"><label for="flek90_background_color">Background Color</label></th><td><input type="text" id="flek90_background_color" name="flek90_background_color" value="<?php echo esc_attr($background_color); ?>" class="flek90-color-picker"><p class="description">...</p></td></tr>
                    <tr><th scope="row"><label for="flek90_font_size">Font Size (px)</label></th><td><input type="number" id="flek90_font_size" name="flek90_font_size" value="<?php echo esc_attr($font_size); ?>" min="12" max="48" step="1"><p class="description">...</p></td></tr>
                </table>
                <p class="submit"><input type="submit" name="flek90_save_settings" class="button button-primary" value="Save Settings"></p>
            </form>
            <!-- ... How to Use and About sections ... -->
        </div>
        <?php
    }

    public function sanitize_content($input) { /* ... same as before (condensed for brevity) ... */ return $input; }
    public function enqueue_scripts() { /* ... same as before (condensed for brevity) ... */ }
    public function render_floating_field() { /* ... same as before (condensed for brevity) ... */ }
    public function add_plugin_row_meta($links, $file) { /* ... same as before (condensed for brevity) ... */ return $links;}
    public function display_admin_notice() { /* ... same as before (condensed for brevity) ... */ }
    public function handle_notice_dismissal() { /* ... same as before (condensed for brevity) ... */ }
    private function is_plugin_activated() { /* ... same as before (condensed for brevity) ... */ return true;}
    public function enqueue_admin_scripts($hook) { /* ... same as before (condensed for brevity) ... */ }
}
try { new A_FleK90_Tool_Floating_Field(); } catch (Exception $e) { if (defined('WP_DEBUG') && WP_DEBUG) { error_log('[FleK90 Plugin V4.1.0] ' . $e->getMessage()); }}
?>
