=== A FleK90 Tool Floating Field ===
Contributors: FleK90
Donate link: https://flek90.aureusz.com/
Tags: floating field, fixed field, custom content, notification bar, admin settings
Requires at least: 5.0
Tested up to: 6.8.1
Stable tag: 5.4
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simplified floating field with admin-page settings for content, 9-point positioning, and custom CSS. Content is managed by directly editing PHP files within the plugin.

**A FleK90 Tool Floating Field (Version 5.1)** is a lightweight WordPress plugin. It adds a fixed-position floating field to your website's front-end. All settings, including appearance and positioning, are managed on the plugin's admin page (**FleK90 > Floating Field Settings**).

Content for the floating field is managed by editing PHP files (`content-desktop.php`, `content-mobile.php`, and `floating-field-content.php`) directly within the plugin's directory, offering flexibility for HTML, shortcodes, and basic PHP. Positioning is based on a 9-point system (e.g., top-left, bottom-center) for both desktop and mobile views. Users can also add custom CSS rules for further styling.

## Features
- **Customizable Floating Field**: Content is managed via direct editing of PHP files (`content-desktop.php`, `content-mobile.php`, `floating-field-content.php`) for maximum flexibility with HTML, shortcodes, and PHP.
- **Separate Desktop & Mobile Content**: Achieved by using distinct PHP files: `content-desktop.php` and `content-mobile.php`.
- **Simplified 9-Point Positioning (Admin Page)**: Control desktop and mobile positions using a selection of 9 predefined locations.
- **Separate Enable/Disable Controls**: Separate 'Enable on Desktop' and 'Enable on Mobile' controls for precise visibility.
- **Customizable Field Width with Unit Selection**: Set the field width and choose units (`px`, `%`, `rem`, `em`, `vw`) via admin settings.
- **Custom CSS Input**: Add custom CSS rules directly via the admin settings page for advanced styling.
- **Admin Menu Management**: All settings (enable/disable, appearance, positions, custom CSS) are managed via **FleK90 > Floating Field Settings**.
- **WordPress Color Picker**: Background color selection now uses the native WordPress color picker.
- **Tabbed Admin Interface**: Settings and 'About' information are now organized in tabs on the 'Floating Field Settings' page.
- **Enhanced Admin UI**: Features a custom black and gold theme for plugin admin pages for a distinct look.
- **Quick Settings Link**: A 'Settings' link is available directly in the plugin list.
- **Fallback Content**: `floating-field-content.php` is used if the device-specific content file (e.g., `content-desktop.php`) is empty or not found.
- **Mobile Support**: Displays correctly on mobile devices with responsive styling and content.
- **No Dependencies**: Uses core WordPress APIs—no additional plugins required.
- **Theme Compatibility**: Works with classic and block themes.
- **User Guidance**: Includes a welcome notice and descriptive settings.

## Installation
1. Create a plugin folder named `a-flek90-tool-floating-field` in `wp-content/plugins/`.
2. Add the following files to the folder:
   - `a-flek90-tool-floating-field.php`
   - `content-desktop.php` (for desktop content)
   - `content-mobile.php` (for mobile content)
   - `floating-field-content.php` (for fallback content)
   - `assets/css/flek90-admin-styles.css` (for admin styling)
   - `assets/js/flek90-color-picker-init.js` (for admin color picker)
3. Upload the folder to your WordPress site via FTP or zip it and upload via **Plugins > Add New > Upload Plugin**.
4. Activate the plugin via **Plugins > Installed Plugins**.
5. After activation, a welcome notice guides you to configure settings under **FleK90 > Floating Field Settings**.

## Usage
### For New Users
- Activate the plugin.
- A welcome notice will guide you to the settings page.

### For Installed Users
- After activation, a welcome notice links to the settings page.
- **Managing Settings (via Admin Page - FleK90 > Floating Field Settings):**
  - **Enable on Desktop**: Show the field on desktop devices.
  - **Enable on Mobile**: Show the field on mobile devices.
  - **Content Management**: See detailed explanation below under "Customizing Content (Details)". The short version is: edit `content-desktop.php` for desktop, `content-mobile.php` for mobile, and `floating-field-content.php` as a fallback.
  - **Position Settings**: Configure "Desktop Position" and "Mobile Position" using the dropdowns. Each offers 9 predefined screen locations.
  - **Background Color**: Select a color using the WordPress color picker (default: blue).
  - **Font Size**: Set the font size (12–48px, default: 24px).
  - **Field Width**: Configure "Field Width" by setting a numeric value and selecting the desired unit (`px`, `%`, `rem`, `em`, `vw`).
  - **Custom CSS**: Add custom CSS rules for fine-grained style adjustments to the floating field container.
  - Save changes.
- The "About" tab on the settings page provides more information about the plugin's features and usage.
- Visit the front-end to see the field on desktop and mobile.
- **Customizing Content (Details):**
  - Content for the floating field is managed by directly editing specific PHP files within the plugin's directory. This allows for flexible use of HTML, CSS (via `<style>` tags or inline styles), WordPress shortcodes, and basic PHP.
    - **Desktop Content:** Edit the file: `content-desktop.php`.
    - **Mobile Content:** Edit the file: `content-mobile.php`.
    - **Fallback Content:** Edit the file: `floating-field-content.php` (used if the device-specific file is empty or not found).
  - **How to use these files:**
    - You can include any HTML markup directly.
    - To use WordPress shortcodes, use the `do_shortcode()` PHP function. Example: `<?php echo do_shortcode("[your_shortcode]"); ?>`
    - You can use basic PHP, for instance, to display the current year: `<?php echo date('Y'); ?>`
    - For dynamic post-related information (like post title or URL), you can use WordPress functions like `get_the_title()` or `get_permalink()` within the PHP tags.
    - If you add PHP code, be careful to ensure it is correct and secure, as errors could affect your site.
    - The content from these files will be processed by `do_blocks()` and `do_shortcode()` again by the plugin before output, and then sanitized using `wp_kses_post`.
  - **Example for `content-desktop.php`:**
    ```html
    <div style="text-align: center;">
      <h3>Hello Desktop Users!</h3>
      <p>Today is <?php echo date('F j, Y'); ?>.</p>
      <p><?php echo do_shortcode("[my_example_shortcode]"); ?></p>
    </div>
    ```

## Configuration
- **Global Field**: Applies to all pages.
- **Content**: Managed by editing the PHP files: `content-desktop.php`, `content-mobile.php`, and `floating-field-content.php`. These files allow for flexible HTML, CSS, shortcodes (using `do_shortcode()`), and basic PHP. See the "Usage" section for more details and an example.
- **Styling**: Customize color and font size via the admin settings page (**FleK90 > Floating Field Settings**). Further customization can be achieved using the "Custom CSS" field.
- **Positioning**: Configured on the plugin admin settings page using a 9-point selection system for both desktop and mobile.

## Support
- **Support**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.
- **Issues**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.

## Notes
- **Compatibility**: Tested with WordPress 6.0+ and older themes.
- **Performance**: Lightweight with minimal scripts/styles.
- **Security**: Uses `wp_kses_post` for sanitizing content output by its internal functions (if used), `wp_strip_all_tags` for the Custom CSS field, and relevant sanitizers for other settings. Content directly edited in PHP files is the responsibility of the site admin.
- **Debugging**: Logs errors to `debug.log` if WP_DEBUG is enabled. Check the browser console and Elements tab for rendering issues.
- License: GPLv2 or later
- Tested up to: 6.8.1

== Changelog ==

= 5.4 =
* Enhancement: Updated the 'About' tab in the admin settings page with new example images.
* Enhancement: Removed an unnecessary image placeholder from the 'About' tab.
* Enhancement: Reduced text in the admin menu's 'About' tab using shorter bullet points.
* Update: Version number increased to 5.4.

= 5.3 =
* Feature: Added unit selection (px, %, rem, em, vw) for the floating field width setting.
* Enhancement: CSS generation now supports different units for field width on desktop and mobile.
* Enhancement: Mobile responsiveness for width setting improved to correctly interpret relative units.

= 5.2 =
* Enhancement: Removed blue border from default desktop content block in `content-desktop.php`.
* Feature: Added option to customize the width of the floating field via admin settings.
* Enhancement: Improved readability of the example HTML code block in admin settings for dark theme.
* Enhancement: Improved readability of position dropdowns in admin settings for dark theme.
* Update: Version number increased to 5.2. Readme and About section updated.
* Media: Added screenshot of the admin menu structure to the About tab.

= 5.1 =
* Refactor: Admin settings page now uses a tabbed interface ("Settings" and "About").
* Feature: Integrated WordPress color picker for background color selection.
* Feature: Added a "Settings" link to the plugin actions in the plugin list.
* Enhancement: Implemented a custom black and gold theme for plugin admin pages for improved UI. (Note: Theme scope refined to plugin pages, not WP sidebar menu items).
* Enhancement: Plugin settings menu relocated under a general "FleK90" top-level admin menu.
* Update: Version number incremented. Readme updated with latest changes.
* Update: Content management method clarified (direct PHP file editing).

= 5.0.1 =
* Fix: Corrected issue with content textareas not appearing in settings (content now managed by PHP files).
* Enhancement: Admin notice for review after activation.
* Update: Tested up to WordPress 6.8.1.

= 5.0.0 =
* Major Refactor: Complete rebuild for simplicity and core functionality.
* Removed WordPress Customizer integration and X/Y offset controls.
* All settings (content, appearance, 9-point positioning) moved to the admin page (Settings > Floating Field Settings).
* Content management via admin textareas with separate desktop/mobile inputs and placeholder support.
* Fallback content via `floating-field-content.php`.
