<?php
/*
Plugin Name: A FleK90 Tool Floating Field
Description: Adds a fixed-position floating field on the front-end with customizable content.
Version: 5.3
Author: FleK90
Author URI: https://flek90.aureusz.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.8
Stable tag: 5.0.1
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Initialize the plugin
class A_FleK90_Tool_Floating_Field {
    private $plugin_version = '5.3';
    private $settings_page_hook_suffix; // Hook suffix for the main settings page (now tabbed)

    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']); // Added for localization
        $this->debug_log('Plugin initialized');
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

    public function load_textdomain() { // Added for localization
        load_plugin_textdomain(
            'a-flek90-tool-floating-field',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
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
        $this->settings_page_hook_suffix = add_submenu_page(
            'flek90_main_menu_slug',                          // Parent slug
            __( 'Floating Field Settings', 'a-flek90-tool-floating-field' ), // Page title for menu
            __( 'Floating Field', 'a-flek90-tool-floating-field' ),          // Menu title for submenu item
            'manage_options',                                 // Capability
            'flek90_floating_field_settings_slug',            // Menu slug for this submenu page (and its URL)
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
        echo '<div class="flek90-admin-page">'; // Open themed div
        echo '<p>' . esc_html__( 'Welcome to the FleK90 Tools main dashboard. Please select a tool from the submenu.', 'a-flek90-tool-floating-field' ) . '</p>';
        echo '</div>'; // Close .flek90-admin-page
        echo '</div>'; // Close .wrap
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $plugin_version_display = $this->plugin_version;
        // Determine active tab, default to 'settings'
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';
        $this->debug_log('Rendering admin page for Floating Field. Active tab: ' . $active_tab);


        // Settings saving logic - only if on settings tab and form submitted
        if ($active_tab === 'settings' && isset($_POST['flek90_save_settings']) && check_admin_referer('flek90_save_settings_action', 'flek90_save_settings_nonce')) {
            $this->debug_log('Saving settings for Floating Field.');
            update_option('flek90_enable_on_desktop_v5', isset($_POST['flek90_enable_on_desktop_v5']) ? '1' : '0');
            update_option('flek90_enable_on_mobile_v5', isset($_POST['flek90_enable_on_mobile_v5']) ? '1' : '0');
            if (isset($_POST['flek90_desktop_position_v5'])) { update_option('flek90_desktop_position_v5', self::sanitize_position_setting(wp_unslash($_POST['flek90_desktop_position_v5']))); }
            if (isset($_POST['flek90_mobile_position_v5'])) { update_option('flek90_mobile_position_v5', self::sanitize_position_setting(wp_unslash($_POST['flek90_mobile_position_v5']))); }
            if (isset($_POST['flek90_background_color_v5'])) { update_option('flek90_background_color_v5', sanitize_hex_color(wp_unslash($_POST['flek90_background_color_v5'])));}
            if (isset($_POST['flek90_font_size_v5'])) { update_option('flek90_font_size_v5', absint(wp_unslash($_POST['flek90_font_size_v5'])));}
            if (isset($_POST['flek90_field_width_v5'])) { update_option('flek90_field_width_v5', absint(wp_unslash($_POST['flek90_field_width_v5'])));}
            if (isset($_POST['flek90_field_width_unit_v5'])) {
                $allowed_units = ['px', '%', 'rem', 'em', 'vw'];
                $submitted_unit = sanitize_text_field(wp_unslash($_POST['flek90_field_width_unit_v5']));
                if (in_array($submitted_unit, $allowed_units, true)) {
                    update_option('flek90_field_width_unit_v5', $submitted_unit);
                } else {
                    update_option('flek90_field_width_unit_v5', 'px'); // Default to px if invalid
                }
            }
            if (isset($_POST['flek90_custom_css_v5'])) { update_option('flek90_custom_css_v5', wp_strip_all_tags(wp_unslash($_POST['flek90_custom_css_v5']))); }

            // Old option deletion logic
            if (get_option('flek90_ff_customizer_settings') !== false) {
                delete_option('flek90_ff_customizer_settings');
                $this->debug_log('Old option flek90_ff_customizer_settings deleted.');
            }
            $old_options = ['flek90_enable_field', 'flek90_mobile_only', 'flek90_field_content', 'flek90_background_color', 'flek90_font_size', 'flek90_custom_css'];
            foreach ($old_options as $old_opt) {
                if (get_option($old_opt) !== false) { delete_option($old_opt); $this->debug_log("Old option {$old_opt} deleted.");}
            }
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'a-flek90-tool-floating-field') . '</p></div>';
        }
        ?>
        <div class="wrap"> <!-- Removed flek90-admin-page class from here -->
            <h1><?php esc_html_e('A FleK90 Tool Floating Field', 'a-flek90-tool-floating-field'); ?> - v<?php echo esc_html($plugin_version_display); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?page=flek90_floating_field_settings_slug&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Settings', 'a-flek90-tool-floating-field'); ?>
                </a>
                <a href="?page=flek90_floating_field_settings_slug&tab=about" class="nav-tab <?php echo $active_tab === 'about' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('About', 'a-flek90-tool-floating-field'); ?>
                </a>
            </h2>

            <div class="flek90-admin-page"> <!-- Added flek90-admin-page wrapper here -->
                <div class="tab-content" style="padding-top: 20px;">
                    <?php if ($active_tab === 'settings') : ?>
                        <?php
                        // Retrieve settings for the form
                    $enable_on_desktop_v5 = get_option('flek90_enable_on_desktop_v5', '1');
                    $enable_on_mobile_v5 = get_option('flek90_enable_on_mobile_v5', '1');
                    $desktop_position_v5 = get_option('flek90_desktop_position_v5', 'top-center');
                    $mobile_position_v5 = get_option('flek90_mobile_position_v5', 'top-center');
                    $background_color_v5 = get_option('flek90_background_color_v5', '#0073aa');
                    $font_size_v5 = get_option('flek90_font_size_v5', '24');
                    $field_width_v5 = get_option('flek90_field_width_v5', '280'); // Default to 280px
                    $field_width_unit_v5 = get_option('flek90_field_width_unit_v5', 'px'); // Default to 'px'
                    $custom_css_v5 = get_option('flek90_custom_css_v5', '');
                    $pos_choices = self::get_position_choices();
                    ?>
                    <form method="post" action="?page=flek90_floating_field_settings_slug&tab=settings">
                        <?php wp_nonce_field('flek90_save_settings_action', 'flek90_save_settings_nonce'); ?>
                        <table class="form-table">
                            <tr valign="top"><td colspan="2"><h3><?php esc_html_e('Display Control', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                            <tr><th scope="row"><label for="flek90_enable_on_desktop_v5"><?php esc_html_e('Enable on Desktop', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="checkbox" id="flek90_enable_on_desktop_v5" name="flek90_enable_on_desktop_v5" value="1" <?php checked($enable_on_desktop_v5, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on desktop devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                            <tr><th scope="row"><label for="flek90_enable_on_mobile_v5"><?php esc_html_e('Enable on Mobile', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="checkbox" id="flek90_enable_on_mobile_v5" name="flek90_enable_on_mobile_v5" value="1" <?php checked($enable_on_mobile_v5, '1'); ?>><p class="description"><?php esc_html_e('Show the floating field on mobile devices.', 'a-flek90-tool-floating-field'); ?></p></td></tr>

                            <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Content Settings', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                            <tr valign="top"><td colspan="2" style="padding-top: 0;">
                                <p class="description"><?php esc_html_e('Content for the floating field is managed by directly editing specific PHP files within the plugin\'s directory. This allows for flexible use of HTML, CSS, WordPress shortcodes, and basic PHP.', 'a-flek90-tool-floating-field'); ?></p>
                                <ul style="list-style: disc; margin-left: 20px;">
                                    <li><strong><?php esc_html_e('Desktop Content:', 'a-flek90-tool-floating-field'); ?></strong> <?php esc_html_e('Edit the file:', 'a-flek90-tool-floating-field'); ?> <code>content-desktop.php</code>.</li>
                                    <li><strong><?php esc_html_e('Mobile Content:', 'a-flek90-tool-floating-field'); ?></strong> <?php esc_html_e('Edit the file:', 'a-flek90-tool-floating-field'); ?> <code>content-mobile.php</code>.</li>
                                    <li><strong><?php esc_html_e('Fallback Content:', 'a-flek90-tool-floating-field'); ?></strong> <?php esc_html_e('Edit the file:', 'a-flek90-tool-floating-field'); ?> <code>floating-field-content.php</code> <?php esc_html_e('(used if the device-specific file is empty or not found).', 'a-flek90-tool-floating-field'); ?></li>
                                </ul>
                                <p class="description"><strong><?php esc_html_e('How to use these files:', 'a-flek90-tool-floating-field'); ?></strong></p>
                                <ul style="list-style: disc; margin-left: 20px;">
                                    <li><?php esc_html_e('You can include any HTML markup directly.', 'a-flek90-tool-floating-field'); ?></li>
                                    <li><?php esc_html_e('To use WordPress shortcodes, use the `do_shortcode()` PHP function. Example:', 'a-flek90-tool-floating-field'); ?> <code>&lt;?php echo do_shortcode("[your_shortcode]"); ?&gt;</code></li>
                                    <li><?php esc_html_e('You can use basic PHP, for instance, to display the current year:', 'a-flek90-tool-floating-field'); ?> <code>&lt;?php echo date('Y'); ?&gt;</code></li>
                                    <li><?php esc_html_e('For dynamic post-related information (like post title or URL), you can use WordPress functions like `get_the_title()` or `get_permalink()` within the PHP tags.', 'a-flek90-tool-floating-field'); ?></li>
                                    <li><?php esc_html_e('If you add PHP code, be careful to ensure it is correct and secure, as errors could affect your site.', 'a-flek90-tool-floating-field'); ?></li>
                                     <li><?php esc_html_e('The content from these files will be processed by `do_blocks()` and `do_shortcode()` again by the plugin before output, and then sanitized using `wp_kses_post`.', 'a-flek90-tool-floating-field'); ?></li>
                                </ul>
                                <p class="description"><em><?php esc_html_e('Example for <code>content-desktop.php</code>:', 'a-flek90-tool-floating-field'); ?></em></p>
                                <pre><code>&lt;div style="text-align: center;"&gt;
  &lt;h3&gt;Hello Desktop Users!&lt;/h3&gt;
  &lt;p&gt;Today is &lt;?php echo date('F j, Y'); ?&gt;.&lt;/p&gt;
  &lt;p&gt;&lt;?php echo do_shortcode("[my_example_shortcode]"); ?&gt;&lt;/p&gt;
&lt;/div&gt;</code></pre>
                            </td></tr>

                            <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Position Settings (Simplified)', 'a-flek90-tool-floating-field'); ?></h3><p class="description"><?php esc_html_e('Select a general position. Offsets are no longer configured on this page.', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                            <tr><th scope="row"><label for="flek90_desktop_position_v5"><?php esc_html_e('Desktop Position', 'a-flek90-tool-floating-field'); ?></label></th><td><select id="flek90_desktop_position_v5" name="flek90_desktop_position_v5"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($desktop_position_v5, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                            <tr><th scope="row"><label for="flek90_mobile_position_v5"><?php esc_html_e('Mobile Position', 'a-flek90-tool-floating-field'); ?></label></th><td><select id="flek90_mobile_position_v5" name="flek90_mobile_position_v5"><?php foreach ($pos_choices as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($mobile_position_v5, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>

                            <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Appearance Settings', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                            <tr><th scope="row"><label for="flek90_background_color_v5"><?php esc_html_e('Background Color', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="text" id="flek90_background_color_v5" name="flek90_background_color_v5" value="<?php echo esc_attr($background_color_v5); ?>" class="flek90-color-picker-field"><p class="description"><?php esc_html_e('Select background color (default: blue).', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                            <tr><th scope="row"><label for="flek90_font_size_v5"><?php esc_html_e('Font Size (px)', 'a-flek90-tool-floating-field'); ?></label></th><td><input type="number" id="flek90_font_size_v5" name="flek90_font_size_v5" value="<?php echo esc_attr($font_size_v5); ?>" min="12" max="48" step="1"><p class="description"><?php esc_html_e('Set font size (12–48px, default: 24px).', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                            <tr>
                                <th scope="row"><label for="flek90_field_width_v5"><?php esc_html_e('Field Width', 'a-flek90-tool-floating-field'); ?></label></th>
                                <td style="display: flex; align-items: center; gap: 10px;">
                                    <input type="number" id="flek90_field_width_v5" name="flek90_field_width_v5" value="<?php echo esc_attr($field_width_v5); ?>" min="10" max="1000" step="1" style="width: 80px;">
                                    <select id="flek90_field_width_unit_v5" name="flek90_field_width_unit_v5" style="flex-shrink: 0;">
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

                            <tr valign="top"><td colspan="2"><hr><h3><?php esc_html_e('Custom CSS', 'a-flek90-tool-floating-field'); ?></h3></td></tr>
                            <tr valign="top"><th scope="row"><label for="flek90_custom_css_v5"><?php esc_html_e('Custom CSS Rules', 'a-flek90-tool-floating-field'); ?></label></th><td><textarea id="flek90_custom_css_v5" name="flek90_custom_css_v5" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($custom_css_v5); ?></textarea><p class="description"><?php esc_html_e('Add your own CSS rules here. Example: #flek90-floating-container { border: 2px solid red !important; }', 'a-flek90-tool-floating-field'); ?></p></td></tr>
                        </table>
                        <p class="submit"><input type="submit" name="flek90_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'a-flek90-tool-floating-field'); ?>"></p>
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
                        <li><?php esc_html_e('Easy content customization via PHP files (content-desktop.php, content-mobile.php, floating-field-content.php).', 'a-flek90-tool-floating-field'); ?></li>
                        <li><?php esc_html_e('Display different content on desktop and mobile devices.', 'a-flek90-tool-floating-field'); ?></li>
                        <li><?php esc_html_e('Choose from multiple field positions (e.g., top-center, bottom-right).', 'a-flek90-tool-floating-field'); ?></li>
                        <li><?php esc_html_e('Customize background color (with a color picker) and font size.', 'a-flek90-tool-floating-field'); ?></li>
                        <li><?php esc_html_e('Enable or disable the floating field separately for desktop and mobile views.', 'a-flek90-tool-floating-field'); ?></li>
                        <li><?php esc_html_e('Option to add custom CSS for further styling tweaks.', 'a-flek90-tool-floating-field'); ?></li>
                        <li><?php esc_html_e('Settings link conveniently available in the plugin list.', 'a-flek90-tool-floating-field'); ?></li>
                        <li><?php esc_html_e('Modern black and gold themed admin interface for all FleK90 tools.', 'a-flek90-tool-floating-field'); ?></li>
                    </ul>

                    <h3><?php esc_html_e('Screenshots', 'a-flek90-tool-floating-field'); ?></h3>
                    <p><em><?php esc_html_e('Note: Screenshot images (screenshot-1.png, screenshot-2.png, screenshot-3.png, Floating_field_admin_menu.jpg) need to be placed in the plugin\'s assets/images/ directory.', 'a-flek90-tool-floating-field'); ?></em></p>
                    <div class="flek90-screenshots">
                        <div class="flek90-screenshot">
                            <img src="<?php echo esc_url($assets_url . 'images/screenshot-1.png'); ?>" alt="<?php esc_attr_e('Screenshot 1: Floating Field Example', 'a-flek90-tool-floating-field'); ?>">
                            <p><em><?php esc_html_e('Example: Floating field shown on a desktop view.', 'a-flek90-tool-floating-field'); ?></em></p>
                        </div>
                        <div class="flek90-screenshot">
                            <img src="<?php echo esc_url($assets_url . 'images/screenshot-2.png'); ?>" alt="<?php esc_attr_e('Screenshot 2: Mobile View Example', 'a-flek90-tool-floating-field'); ?>">
                            <p><em><?php esc_html_e('Example: Floating field shown on a mobile view.', 'a-flek90-tool-floating-field'); ?></em></p>
                        </div>
                        <div class="flek90-screenshot">
                            <img src="<?php echo esc_url($assets_url . 'images/screenshot-3.png'); ?>" alt="<?php esc_attr_e('Screenshot 3: Settings Page Example', 'a-flek90-tool-floating-field'); ?>">
                            <p><em><?php esc_html_e('The admin settings panel for configuring the Floating Field.', 'a-flek90-tool-floating-field'); ?></em></p>
                        </div>
                        <div class="flek90-screenshot">
                            <img src="<?php echo esc_url($assets_url . 'images/Floating_field_admin_menu.jpg'); ?>" alt="<?php esc_attr_e('Screenshot 4: Admin Menu Structure', 'a-flek90-tool-floating-field'); ?>">
                            <p><em><?php esc_html_e('The FleK90 Tools admin menu and Floating Field submenu.', 'a-flek90-tool-floating-field'); ?></em></p>
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
            </div> <!-- end .flek90-admin-page -->
        </div> <!-- end .wrap -->
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

        $background_color_option = get_option('flek90_background_color_v5', '#0073aa');
        // Ensure that if the color option somehow becomes empty or invalid leading to an empty string after sanitization,
        // we use 'transparent' to avoid CSS errors or unexpected inherited backgrounds.
        $css_background_color = !empty($background_color_option) ? esc_attr($background_color_option) : 'transparent';
        $font_size_v5 = get_option('flek90_font_size_v5', '24');

        // Retrieve and prepare field width CSS
        $field_width_v5 = get_option('flek90_field_width_v5', '280');
        $field_width_unit_v5 = get_option('flek90_field_width_unit_v5', 'px');

        // Validate or sanitize the unit again, just in case
        $allowed_units = ['px', '%', 'rem', 'em', 'vw'];
        if (!in_array($field_width_unit_v5, $allowed_units, true)) {
            $field_width_unit_v5 = 'px'; // Default to px if invalid stored value
        }

        $css_field_width_value = esc_attr($field_width_v5);
        $css_field_width_unit = esc_attr($field_width_unit_v5);
        $css_field_full_width = $css_field_width_value . $css_field_width_unit; // Combined value and unit

        // For mobile, max-width should not exceed original 220px OR the custom width if it's smaller than 220px, ONLY if unit is px.
        // $css_mobile_max_width = esc_attr(min((int)$field_width_v5, 220)) . 'px'; // Old logic

        $css_mobile_max_width_final = '';
        if ($css_field_width_unit === 'px') {
            $css_mobile_max_width_final = esc_attr(min((int)$css_field_width_value, 220)) . 'px';
        } elseif ($css_field_width_unit === 'rem' || $css_field_width_unit === 'em' || $css_field_width_unit === '%' || $css_field_width_unit === 'vw') {
            $css_mobile_max_width_final = $css_field_full_width; // Use the user-defined value and unit
        } else { // Fallback, should not happen
            $css_mobile_max_width_final = '220px';
        }

        $css = "
    #flek90-floating-container {
        position: fixed !important; {$desktop_pos_css} z-index: 9999; background: " . $css_background_color . "; color: #fff; padding: 1px 1px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); width: " . $css_field_full_width . "; max-width: " . $css_field_full_width . "; text-align: center; font-size: " . esc_attr($font_size_v5) . "px; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
        #flek90-floating-container { {$mobile_pos_css} padding: 2px 2px; width: auto; max-width: " . $css_mobile_max_width_final . "; font-size: " . esc_attr(max(12, (int)$font_size_v5 - 4)) . "px; }
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

    // Combined admin assets enqueuing
    public function enqueue_admin_assets($hook_suffix) {
        // $this->debug_log('enqueue_admin_assets called with hook_suffix: ' . $hook_suffix . ' | Settings page hook: ' . $this->settings_page_hook_suffix );

        $is_flek90_admin_page = false;
        // Check for the main FleK90 Tools dashboard page or the unified Settings/About page.
        if ($hook_suffix === 'toplevel_page_flek90_main_menu_slug' ||
            $hook_suffix === $this->settings_page_hook_suffix ) { // $this->details_page_hook_suffix is removed
            $is_flek90_admin_page = true;
        }

        if ($is_flek90_admin_page) {
            $this->debug_log('On a FleK90 admin page (' . $hook_suffix . '), enqueuing FleK90 admin styles.');
            wp_enqueue_style(
                'flek90-admin-styles',
                plugin_dir_url(__FILE__) . 'assets/css/flek90-admin-styles.css',
                [],
                $this->plugin_version
            );

            // Conditionally enqueue color picker scripts only on the settings page
            if ($this->settings_page_hook_suffix == $hook_suffix) {
                $this->debug_log('On Floating Field settings page (' . $hook_suffix . '), enqueuing color picker scripts.');
                wp_enqueue_style('wp-color-picker'); // Already enqueued by WordPress if needed by other plugins, but good practice.
                wp_enqueue_script(
                    'flek90-color-picker-init',
                    plugin_dir_url(__FILE__) . 'assets/js/flek90-color-picker-init.js',
                    ['wp-color-picker', 'jquery'],
                    $this->plugin_version,
                    true
                );
            }
        } else {
            // $this->debug_log('Not a FleK90 admin page (' . $hook_suffix . '), FleK90 admin assets not enqueued.');
        }
    }

    public function add_settings_link_to_plugin_list($links) {
        $settings_slug = 'flek90_floating_field_settings_slug';
        $settings_url = admin_url('admin.php?page=' . $settings_slug);
        $settings_link_html = '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'a-flek90-tool-floating-field') . '</a>';

        // Add the settings link before other links like "Deactivate" or "Edit".
        // A common way is to merge it, or add it to the beginning or specific key.
        // Using array_merge to add it to the beginning is simple.
        $new_links = ['settings' => $settings_link_html];
        $links = array_merge($new_links, $links);

        return $links;
    }
    // Removed render_plugin_details_page_html() as its content is now in render_admin_page() 'about' tab
}
try { new A_FleK90_Tool_Floating_Field(); } catch (Exception $e) {
    // Note: $this->plugin_version is not available in this static context if class instantiation fails.
    // Consider logging a generic version or fetching it differently if needed here.
    if (defined('WP_DEBUG') && WP_DEBUG) { error_log('[FleK90 Plugin Init Error] ' . $e->getMessage()); }
}
?>
