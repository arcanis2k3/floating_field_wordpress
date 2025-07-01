<?php
/*
Plugin Name: A FleK90 Tool Floating Field
Description: Adds a fixed-position floating field on the front-end with customizable content.
Version: 6.0
Author: FleK90
Author URI: https://flek90.aureusz.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.8
Stable tag: 6.0
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Initialize the plugin
class A_FleK90_Tool_Floating_Field {
    private $plugin_version = '6.0';
    private $settings_page_hook_suffix; // Hook suffix for the main settings page (now tabbed)
    private $new_prefix = 'aflek90tff';

    public function __construct() {
        $this->migrate_old_options(); // Add migration call
        // add_action('plugins_loaded', [$this, 'load_textdomain']); // Removed for localization on WP.org
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'render_floating_field']);
        add_filter('plugin_row_meta', [$this, 'add_plugin_row_meta'], 10, 2);
        add_action('admin_notices', [$this, 'display_admin_notice']);
        add_action('admin_init', [$this, 'handle_notice_dismissal']);

        // Consolidate admin script enqueuing
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // Add settings link to plugin list
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link_to_plugin_list']);
        // Customizer hooks removed
    }

    // public function load_textdomain() { // Removed for localization on WP.org
    //     load_plugin_textdomain(
    //         'a-flek90-tool-floating-field',
    //         false,
    //         dirname(plugin_basename(__FILE__)) . '/languages/'
    //     );
    // }

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

    /**
     * Sanitizes and validates the position setting.
     * Ensures the input is one of the allowed position keys.
     * Returns the valid input or a default value ('top-center') if invalid.
     *
     * @param string $input The position value from user input.
     * @return string The sanitized and validated position string.
     */
    public static function sanitize_position_setting($input) {
        // $input is expected to be unslashed at this point.
        // Validates against a predefined list of allowed values (keys of get_position_choices).
        // This acts as both sanitization (by restricting to known safe values) and validation.
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
        // Add the new top-level "FleK90" menu
        add_menu_page(
            __( 'FleK90 Tools', 'a-flek90-tool-floating-field' ), // Page title
            __( 'FleK90', 'a-flek90-tool-floating-field' ),       // Menu title
            'manage_options',                                 // Capability
            $this->new_prefix . '_main_menu_slug',            // Menu slug (this will be the parent slug)
            [$this, 'flek90_main_menu_page_html_callback'],   // Callback function for the top-level page content
            'dashicons-admin-generic',                        // Icon
            75                                                // Position
        );

        // Add the "Floating Field Settings" page as a submenu
        $this->settings_page_hook_suffix = add_submenu_page(
            $this->new_prefix . '_main_menu_slug',            // Parent slug
            __( 'Floating Field Settings', 'a-flek90-tool-floating-field' ), // Page title for menu
            __( 'Floating Field', 'a-flek90-tool-floating-field' ),          // Menu title for submenu item
            'manage_options',                                 // Capability
            $this->new_prefix . '_floating_field_settings_slug', // Menu slug for this submenu page (and its URL)
            [$this, 'render_admin_page']                      // Callback function for the page (now tabbed)
        );
        // Removed the separate "About" submenu page registration
    }

    // Placeholder callback for the main menu page
    public function flek90_main_menu_page_html_callback() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'a-flek90-tool-floating-field'));
        }
        // Structure changed for precise theme scoping
        echo '<div class="wrap"><h1>' . esc_html__( 'FleK90 Tools Dashboard', 'a-flek90-tool-floating-field' ) . '</h1>';
        echo '<div class="' . esc_attr($this->new_prefix) . '-admin-page">'; // Open themed div
        echo '<p>' . esc_html__( 'Welcome to the FleK90 Tools main dashboard. Please select a tool from the submenu.', 'a-flek90-tool-floating-field' ) . '</p>';
        echo '</div>'; // Close .aflek90tff-admin-page
        echo '</div>'; // Close .wrap
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $plugin_version_display = $this->plugin_version;
        // Determine active tab, default to 'settings'
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';

        // Settings saving logic - only if on settings tab and form submitted
        if ($active_tab === 'settings' && isset($_POST[$this->new_prefix . '_save_settings']) && check_admin_referer($this->new_prefix . '_save_settings_action', $this->new_prefix . '_save_settings_nonce')) {
            update_option($this->new_prefix . '_enable_on_desktop_v5', isset($_POST[$this->new_prefix . '_enable_on_desktop_v5']) ? '1' : '0');
            update_option($this->new_prefix . '_enable_on_mobile_v5', isset($_POST[$this->new_prefix . '_enable_on_mobile_v5']) ? '1' : '0');
            if (isset($_POST[$this->new_prefix . '_desktop_position_v5'])) { update_option($this->new_prefix . '_desktop_position_v5', self::sanitize_position_setting(wp_unslash($_POST[$this->new_prefix . '_desktop_position_v5']))); }
            if (isset($_POST[$this->new_prefix . '_mobile_position_v5'])) { update_option($this->new_prefix . '_mobile_position_v5', self::sanitize_position_setting(wp_unslash($_POST[$this->new_prefix . '_mobile_position_v5']))); }
            if (isset($_POST[$this->new_prefix . '_background_color_v5'])) { update_option($this->new_prefix . '_background_color_v5', sanitize_hex_color(wp_unslash($_POST[$this->new_prefix . '_background_color_v5']))); }
            if (isset($_POST[$this->new_prefix . '_font_size_v5'])) { update_option($this->new_prefix . '_font_size_v5', absint(wp_unslash($_POST[$this->new_prefix . '_font_size_v5']))); }
            if (isset($_POST[$this->new_prefix . '_field_width_v5'])) { update_option($this->new_prefix . '_field_width_v5', absint(wp_unslash($_POST[$this->new_prefix . '_field_width_v5']))); }
            if (isset($_POST[$this->new_prefix . '_field_width_unit_v5'])) {
                $allowed_units = ['px', '%', 'rem', 'em', 'vw'];
                $submitted_unit = sanitize_text_field(wp_unslash($_POST[$this->new_prefix . '_field_width_unit_v5']));
                if (in_array($submitted_unit, $allowed_units, true)) {
                    update_option($this->new_prefix . '_field_width_unit_v5', $submitted_unit);
                } else {
                    update_option($this->new_prefix . '_field_width_unit_v5', 'px'); // Default to px if invalid
                }
            }
            if (isset($_POST[$this->new_prefix . '_content_desktop_v5'])) { update_option($this->new_prefix . '_content_desktop_v5', wp_kses_post(wp_unslash($_POST[$this->new_prefix . '_content_desktop_v5']))); }
            if (isset($_POST[$this->new_prefix . '_content_mobile_v5'])) { update_option($this->new_prefix . '_content_mobile_v5', wp_kses_post(wp_unslash($_POST[$this->new_prefix . '_content_mobile_v5']))); }
            if (isset($_POST[$this->new_prefix . '_custom_css_desktop_v5'])) { update_option($this->new_prefix . '_custom_css_desktop_v5', wp_strip_all_tags(wp_unslash($_POST[$this->new_prefix . '_custom_css_desktop_v5']))); }
            if (isset($_POST[$this->new_prefix . '_custom_css_mobile_v5'])) { update_option($this->new_prefix . '_custom_css_mobile_v5', wp_strip_all_tags(wp_unslash($_POST[$this->new_prefix . '_custom_css_mobile_v5']))); }
            if (isset($_POST[$this->new_prefix . '_prioritize_custom_css_v5'])) { update_option($this->new_prefix . '_prioritize_custom_css_v5', '1'); } else { update_option($this->new_prefix . '_prioritize_custom_css_v5', '0'); }

            // Old option deletion logic
            if (get_option('flek90_ff_customizer_settings') !== false) {
                delete_option('flek90_ff_customizer_settings');
            }
            $old_options_to_delete_on_save = ['flek90_enable_field', 'flek90_mobile_only', 'flek90_field_content', 'flek90_background_color', 'flek90_font_size', 'flek90_custom_css'];
            foreach ($old_options_to_delete_on_save as $old_opt) {
                if (get_option($old_opt) !== false) { delete_option($old_opt); }
            }
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'a-flek90-tool-floating-field') . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('A FleK90 Tool Floating Field', 'a-flek90-tool-floating-field'); ?> - v<?php echo esc_html($plugin_version_display); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=<?php echo esc_attr($this->new_prefix); ?>_floating_field_settings_slug&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Settings', 'a-flek90-tool-floating-field'); ?>
                </a>
                <a href="?page=<?php echo esc_attr($this->new_prefix); ?>_floating_field_settings_slug&tab=about" class="nav-tab <?php echo $active_tab === 'about' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('About', 'a-flek90-tool-floating-field'); ?>
                </a>
            </h2>

            <div class="<?php echo esc_attr($this->new_prefix); ?>-admin-page">
                <div class="tab-content" style="padding-top: 20px;">
                    <?php if ($active_tab === 'settings') : ?>
                        <?php
                        // Retrieve settings for the form
                        $enable_on_desktop_v5 = get_option($this->new_prefix . '_enable_on_desktop_v5', '1');
                        $enable_on_mobile_v5 = get_option($this->new_prefix . '_enable_on_mobile_v5', '1');
                        $desktop_position_v5 = get_option($this->new_prefix . '_desktop_position_v5', 'top-center');
                        $mobile_position_v5 = get_option($this->new_prefix . '_mobile_position_v5', 'top-center');
                        $background_color_v5 = get_option($this->new_prefix . '_background_color_v5', '#0073aa');
                        $font_size_v5 = get_option($this->new_prefix . '_font_size_v5', '24');
                        $field_width_v5 = get_option($this->new_prefix . '_field_width_v5', '280');
                        $field_width_unit_v5 = get_option($this->new_prefix . '_field_width_unit_v5', 'px');
                        $pos_choices = self::get_position_choices();

                        // Define default content examples
                        $default_desktop_content_example = '<div style="padding: 10px; text-align: center;">' . "\n" .
                                                          '  <h2>Page: %page_title%</h2>' . "\n" .
                                                          '  <p>This is an example of desktop content.</p>' . "\n" .
                                                          '  <p>Current year via shortcode: [current_year_example_shortcode]</p>' . "\n" .
                                                          '  <p>Try another shortcode: [my_contact_form_shortcode]</p>' . "\n" .
                                                          '  <p><a href="#">Learn More</a></p>' . "\n" .
                                                          '</div>';

                        $default_mobile_content_example = '<div style="padding: 8px; text-align: center; border: 1px solid lightblue;">' . "\n" .
                                                         '  <h3>Mobile: %page_title%</h3>' . "\n" .
                                                         '  <p>Mobile content example.</p>' . "\n" .
                                                         '  <p>Shortcode: [my_contact_form_shortcode]</p>' . "\n" .
                                                         '</div>';
                        ?>
                        <form method="post" action="?page=<?php echo esc_attr($this->new_prefix); ?>_floating_field_settings_slug&tab=settings">
                            <?php wp_nonce_field($this->new_prefix . '_save_settings_action', $this->new_prefix . '_save_settings_nonce'); ?>
                            <table class="form-table">
                                <tr valign="top"><td colspan="2"><h3><?php esc_html_e('Display Control', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                                <tr><th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_enable_on_desktop_v5"><?php esc_html_e('Enable on Desktop', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="checkbox" id="<?php echo esc_attr($this->new_prefix); ?>_enable_on_desktop_v5" name="<?php echo esc_attr($this->new_prefix); ?>_enable_on_desktop_v5" value="1" <?php checked($enable_on_desktop_v5, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on desktop devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                                <tr><th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_enable_on_mobile_v5"><?php esc_html_e('Enable on Mobile', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="checkbox" id="<?php echo esc_attr($this->new_prefix); ?>_enable_on_mobile_v5" name="<?php echo esc_attr($this->new_prefix); ?>_enable_on_mobile_v5" value="1" <?php checked($enable_on_mobile_v5, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on mobile devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>

                                <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Content Settings', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr($this->new_prefix); ?>_content_desktop_v5"><?php esc_html_e('Desktop Content', 'a-flek90-tool-floating-field'); ?></label>
                                    </th>
                                    <td>
                                        <textarea id="<?php echo esc_attr($this->new_prefix); ?>_content_desktop_v5" name="<?php echo esc_attr($this->new_prefix); ?>_content_desktop_v5" rows="10" cols="50" class="large-text code"><?php echo esc_textarea(get_option($this->new_prefix . '_content_desktop_v5', $default_desktop_content_example)); ?></textarea>
                                        <p class="description"><?php esc_html_e('Enter your HTML content. Use %page_title% to display the current page/post title. For dynamic PHP-like functionality, use or create WordPress shortcodes (e.g., [your_shortcode]). The example shows basic HTML, the page title tag, and a placeholder for a shortcode. Note: You would need to define shortcodes like [current_year_example_shortcode] or [my_contact_form_shortcode] in your theme or another plugin for them to work.', 'a-flek90-tool-floating-field'); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="<?php echo esc_attr($this->new_prefix); ?>_content_mobile_v5"><?php esc_html_e('Mobile Content', 'a-flek90-tool-floating-field'); ?></label>
                                    </th>
                                    <td>
                                        <textarea id="<?php echo esc_attr($this->new_prefix); ?>_content_mobile_v5" name="<?php echo esc_attr($this->new_prefix); ?>_content_mobile_v5" rows="10" cols="50" class="large-text code"><?php echo esc_textarea(get_option($this->new_prefix . '_content_mobile_v5', $default_mobile_content_example)); ?></textarea>
                                        <p class="description"><?php esc_html_e('Enter your HTML content. Use %page_title% to display the current page/post title. For dynamic PHP-like functionality, use or create WordPress shortcodes (e.g., [your_shortcode]). The example shows basic HTML, the page title tag, and a placeholder for a shortcode. Note: You would need to define shortcodes like [my_contact_form_shortcode] in your theme or another plugin for them to work.', 'a-flek90-tool-floating-field'); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top"><td colspan="2" style="padding-top: 0;"></td></tr>

                                <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Position Settings (Simplified)', 'a-flek90-tool-floating-field'); ?></h3><p class="description"><?php esc_html_e('Select a general position. Offsets are no longer configured on this page.', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                                <tr><th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_desktop_position_v5"><?php esc_html_e('Desktop Position', 'a-flek90-tool-floating-field'); ?></label></th><td><select id="<?php echo esc_attr($this->new_prefix); ?>_desktop_position_v5" name="<?php echo esc_attr($this->new_prefix); ?>_desktop_position_v5"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($desktop_position_v5, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                                <tr><th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_mobile_position_v5"><?php esc_html_e('Mobile Position', 'a-flek90-tool-floating-field'); ?></label></th><td><select id="<?php echo esc_attr($this->new_prefix); ?>_mobile_position_v5" name="<?php echo esc_attr($this->new_prefix); ?>_mobile_position_v5"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($mobile_position_v5, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>

                                <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Appearance Settings', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                                <tr><th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_background_color_v5"><?php esc_html_e('Background Color', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="text" id="<?php echo esc_attr($this->new_prefix); ?>_background_color_v5" name="<?php echo esc_attr($this->new_prefix); ?>_background_color_v5" value="<?php echo esc_attr($background_color_v5); ?>" class="<?php echo esc_attr($this->new_prefix); ?>-color-picker-field"><p class="description"><?php esc_html_e('Select background color (default: blue).', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                                <tr><th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_font_size_v5"><?php esc_html_e('Font Size (px)', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="number" id="<?php echo esc_attr($this->new_prefix); ?>_font_size_v5" name="<?php echo esc_attr($this->new_prefix); ?>_font_size_v5" value="<?php echo esc_attr($font_size_v5); ?>" min="12" max="48" step="1"><p class="description"><?php esc_html_e('Set font size (12–48px, default: 24px).', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                                <tr>
                                    <th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_field_width_v5"><?php esc_html_e('Field Width', 'a-flek90-tool-floating-field'); ?></label></th>
                                    <td style="display: flex; align-items: center; gap: 10px;">
                                        <input type="number" id="<?php echo esc_attr($this->new_prefix); ?>_field_width_v5" name="<?php echo esc_attr($this->new_prefix); ?>_field_width_v5" value="<?php echo esc_attr($field_width_v5); ?>" min="10" max="1000" step="1" style="width: 80px;">
                                        <select id="<?php echo esc_attr($this->new_prefix); ?>_field_width_unit_v5" name="<?php echo esc_attr($this->new_prefix); ?>_field_width_unit_v5" style="flex-shrink: 0;">
                                            <?php
                                            $units = ['px', '%', 'rem', 'em', 'vw'];
                                            foreach ($units as $unit) {
                                                echo '<option value="' . esc_attr($unit) . '" ' . selected($field_width_unit_v5, $unit, false) . '>' . esc_html($unit) . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <p class="description" style="margin-left: 10px;"><?php esc_html_e('Set field width and unit (e.g., 280px, 50%, 15rem). Note: % and vw are relative to viewport width.', 'a-flek90-tool-floating-field'); ?></p>
                                    </td>
                                </tr>

                                <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Custom CSS Settings', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                                <tr valign="top">
                                    <th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_custom_css_desktop_v5"><?php esc_html_e('Custom CSS for Desktop', 'a-flek90-tool-floating-field'); ?></label></th>
                                    <td>
                                        <textarea id="<?php echo esc_attr($this->new_prefix); ?>_custom_css_desktop_v5" name="<?php echo esc_attr($this->new_prefix); ?>_custom_css_desktop_v5" rows="10" cols="50" class="large-text code"><?php echo esc_textarea(get_option($this->new_prefix . '_custom_css_desktop_v5', '')); ?></textarea>
                                        <p class="description"><?php printf(esc_html__('Add custom CSS rules to be applied for desktop views. Example: #%s-floating-container { border: 2px solid blue !important; }', 'a-flek90-tool-floating-field'), esc_attr($this->new_prefix)); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="<?php echo esc_attr($this->new_prefix); ?>_custom_css_mobile_v5"><?php esc_html_e('Custom CSS for Mobile', 'a-flek90-tool-floating-field'); ?></label></th>
                                    <td>
                                        <textarea id="<?php echo esc_attr($this->new_prefix); ?>_custom_css_mobile_v5" name="<?php echo esc_attr($this->new_prefix); ?>_custom_css_mobile_v5" rows="10" cols="50" class="large-text code"><?php echo esc_textarea(get_option($this->new_prefix . '_custom_css_mobile_v5', '')); ?></textarea>
                                        <p class="description"><?php printf(esc_html__('Add custom CSS rules to be applied for mobile views (typically within a max-width: 768px media query). Example: #%s-floating-container { font-size: 12px !important; }', 'a-flek90-tool-floating-field'), esc_attr($this->new_prefix)); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e('CSS Override', 'a-flek90-tool-floating-field'); ?></th>
                                    <td>
                                        <input type="checkbox" id="<?php echo esc_attr($this->new_prefix); ?>_prioritize_custom_css_v5" name="<?php echo esc_attr($this->new_prefix); ?>_prioritize_custom_css_v5" value="1" <?php checked(get_option($this->new_prefix . '_prioritize_custom_css_v5', '0'), '1'); ?> />
                                        <label for="<?php echo esc_attr($this->new_prefix); ?>_prioritize_custom_css_v5"><?php esc_html_e('Prioritize Custom CSS for Styles', 'a-flek90-tool-floating-field'); ?></label>
                                        <p class="description"><?php esc_html_e('If checked, your Custom CSS for Desktop/Mobile will take full control over appearance (background, font size, width). The plugin\'s specific settings for these will be ignored, allowing your CSS to prevail.', 'a-flek90-tool-floating-field'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit"><input type="submit" name="<?php echo esc_attr($this->new_prefix); ?>_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'a-flek90-tool-floating-field'); ?>"></p>
                        </form>

                    <?php elseif ($active_tab === 'about') : ?>
                        <?php
                        // Content for the "About" tab
                        $assets_url = plugin_dir_url(__FILE__) . 'assets/';
                        ?>
                        <h3><?php esc_html_e('About This Plugin', 'a-flek90-tool-floating-field'); ?></h3>
                        <p><?php esc_html_e('This plugin gives the option to create a customizable responsive floating field. Different for desktop and mobile.', 'a-flek90-tool-floating-field'); ?></p>

                        <h3><?php esc_html_e('Key Features', 'a-flek90-tool-floating-field'); ?></h3>
                        <ul>
                            <li><?php esc_html_e('Customize content via PHP files (`content-desktop.php`, `content-mobile.php`, `floating-field-content.php`).', 'a-flek90-tool-floating-field'); ?></li>
                            <li><?php esc_html_e('Separate content for desktop & mobile.', 'a-flek90-tool-floating-field'); ?></li>
                            <li><?php esc_html_e('Multiple field positions (e.g., top-center, bottom-right).', 'a-flek90-tool-floating-field'); ?></li>
                            <li><?php esc_html_e('Customizable background color & font size.', 'a-flek90-tool-floating-field'); ?></li>
                            <li><?php esc_html_e('Separate enable/disable for desktop & mobile.', 'a-flek90-tool-floating-field'); ?></li>
                            <li><?php esc_html_e('Add custom CSS for advanced styling.', 'a-flek90-tool-floating-field'); ?></li>
                            <li><?php esc_html_e('Quick settings link in plugin list.', 'a-flek90-tool-floating-field'); ?></li>
                            <li><?php esc_html_e('Themed admin interface for FleK90 tools.', 'a-flek90-tool-floating-field'); ?></li>
                        </ul>

                        <h3><?php esc_html_e('Screenshots', 'a-flek90-tool-floating-field'); ?></h3>
                        <p><em><?php esc_html_e('Note: Screenshot images (floating_field_example.jpg, floating_field_example2.jpg, Floating_field_admin_menu.jpg) need to be placed in the plugin\'s assets/images/ directory.', 'a-flek90-tool-floating-field'); ?></em></p>
                        <div class="<?php echo esc_attr($this->new_prefix); ?>-screenshots">
                            <div class="<?php echo esc_attr($this->new_prefix); ?>-screenshot">
                                <img src="<?php echo esc_url($assets_url . 'images/floating_field_example.jpg'); ?>" alt="<?php esc_attr_e('Floating Field Example 1', 'a-flek90-tool-floating-field'); ?>">
                                <p><em><?php esc_html_e('Example 1: Floating field in action.', 'a-flek90-tool-floating-field'); ?></em></p>
                            </div>
                            <div class="<?php echo esc_attr($this->new_prefix); ?>-screenshot">
                                <img src="<?php echo esc_url($assets_url . 'images/floating_field_example2.jpg'); ?>" alt="<?php esc_attr_e('Floating Field Example 2', 'a-flek90-tool-floating-field'); ?>">
                                <p><em><?php esc_html_e('Example 2: Another view of the floating field.', 'a-flek90-tool-floating-field'); ?></em></p>
                            </div>
                            <div class="<?php echo esc_attr($this->new_prefix); ?>-screenshot">
                                <img src="<?php echo esc_url($assets_url . 'images/Floating_field_admin_menu.jpg'); ?>" alt="<?php esc_attr_e('Screenshot 4: Admin Menu Structure', 'a-flek90-tool-floating-field'); ?>">
                                <p><em><?php esc_html_e('The AFTFF Tools admin menu and Floating Field submenu.', 'a-flek90-tool-floating-field'); ?></em></p>
                            </div>
                        </div>

                        <hr style="margin-top: 30px; margin-bottom: 20px;">
                        <p><?php
                            printf(
                                wp_kses(
                                    /* translators: %s: URL to plugin author FleK90. */
                                    __( 'This plugin is developed and maintained by <a href="%s" target="_blank">FleK90</a>.', 'a-flek90-tool-floating-field' ),
                                    [ 'a' => [ 'href' => true, 'target' => true ] ]
                                ),
                                esc_url('https://flek90.aureusz.com')
                            );
                        ?></p>

                    <?php endif; ?>
                </div> <!-- end .tab-content -->
            </div> <!-- end .aflek90tff-admin-page -->
        </div> <!-- end .wrap -->
        <?php
    }

    private function migrate_old_options() {
        $migration_flag = $this->new_prefix . '_options_migrated_v5_6';
        if (get_option($migration_flag)) {
            return;
        }

        $old_options_map = [
            'flek90_enable_on_desktop_v5'     => $this->new_prefix . '_enable_on_desktop_v5',
            'flek90_enable_on_mobile_v5'      => $this->new_prefix . '_enable_on_mobile_v5',
            'flek90_desktop_position_v5'      => $this->new_prefix . '_desktop_position_v5',
            'flek90_mobile_position_v5'       => $this->new_prefix . '_mobile_position_v5',
            'flek90_background_color_v5'      => $this->new_prefix . '_background_color_v5',
            'flek90_font_size_v5'             => $this->new_prefix . '_font_size_v5',
            'flek90_field_width_v5'           => $this->new_prefix . '_field_width_v5',
            'flek90_field_width_unit_v5'      => $this->new_prefix . '_field_width_unit_v5',
            'flek90_content_desktop_v5'       => $this->new_prefix . '_content_desktop_v5',
            'flek90_content_mobile_v5'        => $this->new_prefix . '_content_mobile_v5',
            'flek90_custom_css_desktop_v5'    => $this->new_prefix . '_custom_css_desktop_v5',
            'flek90_custom_css_mobile_v5'     => $this->new_prefix . '_custom_css_mobile_v5',
            'flek90_prioritize_custom_css_v5' => $this->new_prefix . '_prioritize_custom_css_v5',
        ];

        foreach ($old_options_map as $old_option_name => $new_option_name) {
            $old_value = get_option($old_option_name);
            if ($old_value !== false && get_option($new_option_name) === false) {
                update_option($new_option_name, $old_value);
            }
        }

        update_option($migration_flag, true);
    }

    public function sanitize_content($input) {
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
        ];
        return wp_kses_post($input);
    }

    public function enqueue_scripts() {
        $prioritize_custom_css = get_option($this->new_prefix . '_prioritize_custom_css_v5', '0') === '1';

        wp_register_style($this->new_prefix . '-floating-field-inline', false, [], $this->plugin_version);
        wp_enqueue_style($this->new_prefix . '-floating-field-inline');

        $desktop_position_v5 = get_option($this->new_prefix . '_desktop_position_v5', 'top-center');
        $mobile_position_v5 = get_option($this->new_prefix . '_mobile_position_v5', 'top-center');

        $desktop_pos_css = $this->generate_position_css_v5($desktop_position_v5);
        $mobile_pos_css = $this->generate_position_css_v5($mobile_position_v5);

        $dynamic_styles = "";
        $mobile_dynamic_styles = "";

        if (!$prioritize_custom_css) {
            $background_color_option = get_option($this->new_prefix . '_background_color_v5', '#0073aa');
            $css_background_color = !empty($background_color_option) ? esc_attr($background_color_option) : 'transparent';
            $font_size_v5 = get_option($this->new_prefix . '_font_size_v5', '24');
            $field_width_v5 = get_option($this->new_prefix . '_field_width_v5', '280');
            $field_width_unit_v5 = get_option($this->new_prefix . '_field_width_unit_v5', 'px');
            $allowed_units = ['px', '%', 'rem', 'em', 'vw'];
            if (!in_array($field_width_unit_v5, $allowed_units, true)) {
                $field_width_unit_v5 = 'px';
            }
            $css_field_width_value = esc_attr($field_width_v5);
            $css_field_width_unit = esc_attr($field_width_unit_v5);
            $css_field_full_width = $css_field_width_value . $css_field_width_unit;

            $dynamic_styles .= "background: " . $css_background_color . "; ";
            $dynamic_styles .= "font-size: " . esc_attr($font_size_v5) . "px; ";
            $dynamic_styles .= "width: " . $css_field_full_width . "; max-width: " . $css_field_full_width . "; ";

            $css_mobile_max_width_final = '';
            if ($css_field_width_unit === 'px') {
                $css_mobile_max_width_final = esc_attr(min((int)$css_field_width_value, 220)) . 'px';
            } elseif ($css_field_width_unit === 'rem' || $css_field_width_unit === 'em' || $css_field_width_unit === '%' || $css_field_width_unit === 'vw') {
                $css_mobile_max_width_final = $css_field_full_width;
            } else {
                $css_mobile_max_width_final = '220px';
            }
            $mobile_dynamic_styles .= "max-width: " . $css_mobile_max_width_final . "; ";
            $mobile_dynamic_styles .= "font-size: " . esc_attr(max(12, (int)$font_size_v5 - 4)) . "px; ";
        }

        $css = "
    #" . esc_attr($this->new_prefix) . "-floating-container {
        position: fixed !important; {$desktop_pos_css} z-index: 9999; {$dynamic_styles} color: #fff; padding: 1px 1px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-align: center; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    #" . esc_attr($this->new_prefix) . "-floating-container * { color: inherit; line-height: 1.5; }
    #" . esc_attr($this->new_prefix) . "-floating-container a { color: #fff; text-decoration: underline; }
    #" . esc_attr($this->new_prefix) . "-floating-container a:hover { color: #ddd; }
    #" . esc_attr($this->new_prefix) . "-floating-container form#searchform { display: flex; align-items: center; gap: 10px; flex-direction: row; }
    #" . esc_attr($this->new_prefix) . "-floating-container input.search-input { background: #fff; color: #333; border: 1px solid #ccc; padding: 1px 1px; font-size: 14px; width: 200px; border-radius: 4px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); transition: border-color 0.2s ease-in-out; }
    #" . esc_attr($this->new_prefix) . "-floating-container input.search-input:focus { border-color: #007cba; outline: none; box-shadow: 0 0 0 1px #007cba; }
    #" . esc_attr($this->new_prefix) . "-floating-container button.search-submit { background: #fff; color: #333; border: 1px solid #ccc; padding: 0; width: 32px; height: 32px; border-radius: 4px; cursor: pointer; transition: background-color 0.2s ease-in-out; display: flex; align-items: center; justify-content: center; }
    #" . esc_attr($this->new_prefix) . "-floating-container button.search-submit:hover { background: #f5f5f5; }
    #" . esc_attr($this->new_prefix) . "-floating-container button.search-submit svg { width: 16px; height: 16px; stroke: #000; stroke-width: 3; }
    @media (max-width: 768px) {
        #" . esc_attr($this->new_prefix) . "-floating-container { {$mobile_pos_css} {$mobile_dynamic_styles} padding: 2px 2px; width: auto; }
        #" . esc_attr($this->new_prefix) . "-floating-container form#searchform { gap: 5px; }
        #" . esc_attr($this->new_prefix) . "-floating-container input.search-input { width: 150px; font-size: 13px; padding: 6px 10px; }
        #" . esc_attr($this->new_prefix) . "-floating-container button.search-submit { width: 28px; height: 28px; }
        #" . esc_attr($this->new_prefix) . "-floating-container button.search-submit svg { width: 14px; height: 14px; }
    }";

        // Add Desktop Custom CSS
        $custom_css_desktop_v5 = get_option($this->new_prefix . '_custom_css_desktop_v5', '');
        $trimmed_custom_css_desktop = trim($custom_css_desktop_v5);
        if (!empty($trimmed_custom_css_desktop)) {
            $css .= "\n\n/* Custom CSS for Desktop from Plugin Settings */\n" . $trimmed_custom_css_desktop;
        }

        // Add Mobile Custom CSS (within the media query)
        $custom_css_mobile_v5 = get_option($this->new_prefix . '_custom_css_mobile_v5', '');
        $trimmed_custom_css_mobile = trim($custom_css_mobile_v5);
        if (!empty($trimmed_custom_css_mobile)) {
            $media_query_start = "@media (max-width: 768px) {";
            if (strpos($css, $media_query_start) !== false) {
                $css_parts = explode($media_query_start, $css, 2);
                if (isset($css_parts[1])) {
                    $main_css_part = $css_parts[0];
                    $media_query_content_part = $css_parts[1];
                    $last_brace_pos = strrpos($media_query_content_part, '}');
                    if ($last_brace_pos !== false) {
                        $before_last_brace = substr($media_query_content_part, 0, $last_brace_pos);
                        $after_last_brace = substr($media_query_content_part, $last_brace_pos);
                        $css = $main_css_part . $media_query_start . $before_last_brace . "\n\n/* Custom CSS for Mobile from Plugin Settings */\n" . $trimmed_custom_css_mobile . $after_last_brace;
                    } else {
                        $css .= "\n@media (max-width: 768px) {\n/* Custom CSS for Mobile from Plugin Settings (fallback append) */\n" . $trimmed_custom_css_mobile . "\n}";
                    }
                }
            } else {
                $css .= "\n@media (max-width: 768px) {\n/* Custom CSS for Mobile from Plugin Settings */\n" . $trimmed_custom_css_mobile . "\n}";
            }
        }
        wp_add_inline_style($this->new_prefix . '-floating-field-inline', $css);
    }

    public function render_floating_field() {
        $is_mobile = wp_is_mobile();
        $enabled_on_desktop = get_option($this->new_prefix . '_enable_on_desktop_v5', '1');
        $enabled_on_mobile = get_option($this->new_prefix . '_enable_on_mobile_v5', '1');

        if (($is_mobile && $enabled_on_mobile !== '1') || (!$is_mobile && $enabled_on_desktop !== '1')) {
            return;
        }

        if ($enabled_on_desktop !== '1' && $enabled_on_mobile !== '1') {
            return;
        }

        $content = '';

        if ($is_mobile) {
            $content = get_option($this->new_prefix . '_content_mobile_v5', '');
        } else {
            $content = get_option($this->new_prefix . '_content_desktop_v5', '');
        }

        // If device-specific content is empty, try to fall back to the other device's content.
        if (empty(trim($content))) {
            if ($is_mobile) {
                $content = get_option($this->new_prefix . '_content_desktop_v5', '');
            } else {
                $content = get_option($this->new_prefix . '_content_mobile_v5', '');
            }
        }

        // If still no content, display nothing
        if (empty(trim($content))) {
            return;
        }

        // Replace %page_title% placeholder
        if (strpos($content, '%page_title%') !== false) {
            $title_to_display = '';
            if (is_singular()) {
                $title_to_display = get_the_title();
            } else {
                $title_to_display = wp_get_document_title();
            }
            $content = str_replace('%page_title%', esc_html($title_to_display), $content);
        }

        $content = do_blocks($content);
        $content = do_shortcode($content);
        $content = $this->sanitize_content($content);
        ?>
        <div id="<?php echo esc_attr($this->new_prefix); ?>-floating-container">
            <div id="<?php echo esc_attr($this->new_prefix); ?>-field-content"><?php echo $content; ?></div>
        </div>
        <?php
    }

    public function add_plugin_row_meta($links, $file) { /* ... */ return $links; }
    public function display_admin_notice() { /* ... */ }
    public function handle_notice_dismissal() { /* ... */ }
    private function is_plugin_activated() { /* ... */ return true; }

    public function enqueue_admin_assets($hook_suffix) {
        $is_aflek90tff_admin_page = false;
        if ($hook_suffix === 'toplevel_page_' . $this->new_prefix . '_main_menu_slug' ||
            $hook_suffix === $this->settings_page_hook_suffix) {
            $is_aflek90tff_admin_page = true;
        }

        if ($is_aflek90tff_admin_page) {
            wp_enqueue_style(
                $this->new_prefix . '-admin-styles',
                plugin_dir_url(__FILE__) . 'assets/css/' . $this->new_prefix . '-admin-styles.css',
                [],
                $this->plugin_version
            );

            if ($this->settings_page_hook_suffix == $hook_suffix) {
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script(
                    $this->new_prefix . '-color-picker-init',
                    plugin_dir_url(__FILE__) . 'assets/js/' . $this->new_prefix . '-color-picker-init.js',
                    ['wp-color-picker', 'jquery'],
                    $this->plugin_version,
                    true
                );
            }
        }
    }

    public function add_settings_link_to_plugin_list($links) {
        $settings_slug = $this->new_prefix . '_floating_field_settings_slug';
        $settings_url = admin_url('admin.php?page=' . $settings_slug);
        $settings_link_html = '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'a-flek90-tool-floating-field') . '</a>';
        $new_links = ['settings' => $settings_link_html];
        $links = array_merge($new_links, $links);
        return $links;
    }
}

try { new A_FleK90_Tool_Floating_Field(); } catch (Exception $e) {
}
?>