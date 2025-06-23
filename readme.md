=== A FleK90 Tool Floating Field ===
Contributors: FleK90
Donate link: https://flek90.aureusz.com/
Tags: floating field, fixed field, custom content, notification bar, admin settings
Requires at least: 5.0
Tested up to: 6.8.1
Stable tag: 6.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Highly customizable floating field with admin-page settings for content (including %page_title% tag and default examples), positioning, appearance, and separate desktop/mobile custom CSS with an override option.

**A FleK90 Tool Floating Field (Version 5.6)** is a lightweight WordPress plugin. It adds a fixed-position floating field to your website's front-end. All settings, including content (with dynamic tags and default examples), appearance, positioning, and custom CSS (separate for desktop/mobile with an override option), are managed on the plugin's admin page (**FleK90 > Floating Field Settings**).

Content for the floating field is managed via textareas in the admin settings page (**FleK90 > Floating Field > Settings > Content Settings**). These textareas support HTML, shortcodes, and a special `%page_title%` tag for displaying the current page/post title. Default example content is provided on first use. Positioning uses a 9-point system. Separate custom CSS fields for desktop and mobile allow for fine-tuned styling, and a "Prioritize Custom CSS" option lets custom styles take full precedence over plugin-generated appearance styles.

## Features
- **Customizable Floating Field Content via Admin Settings**: Content for desktop and mobile is managed via textareas on the admin settings page.
  - Supports HTML, shortcodes, and basic PHP snippets.
  - Includes a `%page_title%` dynamic tag to display the current page/post title.
  - Textareas are pre-filled with helpful example content on first use.
- **Separate Desktop & Mobile Content**: Configure distinct content for desktop and mobile views directly in the admin settings.
- **Simplified 9-Point Positioning (Admin Page)**: Control desktop and mobile positions using a selection of 9 predefined locations.
- **Separate Enable/Disable Controls**: Separate 'Enable on Desktop' and 'Enable on Mobile' controls for precise visibility.
- **Customizable Field Width with Unit Selection**: Set the field width and choose units (`px`, `%`, `rem`, `em`, `vw`) via admin settings.
- **Separate Custom CSS for Desktop & Mobile**: Dedicated textareas for adding custom CSS rules for desktop views and mobile views (applied within a `@media (max-width: 768px)` query).
- **Prioritize Custom CSS Option**: A checkbox setting allows users to make their custom CSS take full control over background, font size, and width, ignoring the plugin's settings for these properties.
- **Admin Menu Management**: All settings (enable/disable, content, appearance, positions, custom CSS, CSS override) are managed via **FleK90 > Floating Field Settings**.
- **WordPress Color Picker**: Background color selection now uses the native WordPress color picker.
- **Tabbed Admin Interface**: Settings and 'About' information are now organized in tabs on the 'Floating Field Settings' page.
- **Enhanced Admin UI**: Features a custom black and gold theme for plugin admin pages for a distinct look.
- **Quick Settings Link**: A 'Settings' link is available directly in the plugin list.
- **Content Fallback Logic**: If device-specific content in admin settings is empty, the plugin will attempt to use the content from the other device's admin settings. The `floating-field-content.php` file is no longer the primary fallback for these.
- **Mobile Support**: Displays correctly on mobile devices with responsive styling and content.
- **No Dependencies**: Uses core WordPress APIs—no additional plugins required.
- **Theme Compatibility**: Works with classic and block themes.
- **User Guidance**: Includes a welcome notice and descriptive settings.

## Installation
1. Create a plugin folder named `a-flek90-tool-floating-field` in `wp-content/plugins/`.
2. Add the following files to the folder:
   - `a-flek90-tool-floating-field.php`
   - `content-desktop.php` (legacy, content now primarily via admin settings)
   - `content-mobile.php` (legacy, content now primarily via admin settings)
   - `floating-field-content.php` (legacy, content now primarily via admin settings)
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
  - **Content Settings**:
    - **Desktop Content**: Enter HTML, shortcodes (e.g., `[your_shortcode]`), or use the `%page_title%` tag. Examples are provided in the textarea on first use.
    - **Mobile Content**: Similar to Desktop Content, but for mobile views.
  - **Position Settings**: Configure "Desktop Position" and "Mobile Position" using dropdowns (9 predefined locations).
  - **Appearance Settings**:
    - **Background Color**: Select using the WordPress color picker.
    - **Font Size**: Set font size in pixels.
    - **Field Width**: Set a numeric value and select the unit (`px`, `%`, `rem`, `em`, `vw`).
    *(Note: Background, Font Size, and Field Width settings are ignored if "Prioritize Custom CSS" is checked).*
  - **Custom CSS Settings**:
    - **Custom CSS for Desktop**: Add CSS rules specifically for desktop views.
    - **Custom CSS for Mobile**: Add CSS rules specifically for mobile views (applied within `@media (max-width: 768px)`).
    - **CSS Override**: Check "Prioritize Custom CSS for Styles" to let your custom CSS take full control over background, font size, and width, overriding the plugin's Appearance Settings for these.
  - Save changes.
- The "About" tab on the settings page provides more information about the plugin's features and usage.
- Visit the front-end to see the field and test responsiveness.
- **Customizing Content (Details):**
  - Content for the floating field is managed via the "Desktop Content" and "Mobile Content" textareas in **FleK90 > Floating Field > Settings > Content Settings**.
  - **Default Examples**: The textareas come pre-filled with helpful examples on first use, demonstrating HTML structure, the `%page_title%` tag, and placeholder shortcodes (e.g., `[current_year_example_shortcode]`).
  - **Dynamic Page Title**: Use the `%page_title%` tag in your content. This tag will be automatically replaced with the current page or post title on the front-end. For archive pages, it displays a relevant archive title.
  - **HTML, Shortcodes, PHP**:
    - You can include any HTML markup directly.
    - WordPress shortcodes (e.g., `[your_gallery]`, `[contact-form-7 id="123" title="Contact form 1"]`) can be used as you would in the WordPress editor.
    - Basic PHP snippets can be used (e.g., `<?php echo date('Y'); ?>`), but ensure they are correct and secure.
    - The content from these textareas is processed by `do_blocks()` and `do_shortcode()` before output and then sanitized using `wp_kses_post`.
  - **Legacy File-Based Content:** Files like `content-desktop.php` are no longer the primary method for content input and are overridden by admin settings.

## Configuration
- **Global Field**: Applies to all pages by default.
- **Content**: Managed via "Desktop Content" and "Mobile Content" textareas in admin settings. Supports HTML, shortcodes, and the `%page_title%` dynamic tag. Default examples are provided.
- **Styling**:
    - Basic appearance (background color, font size, field width) is set via **FleK90 > Floating Field Settings > Appearance Settings**.
    - Advanced styling is achieved using the "Custom CSS for Desktop" and "Custom CSS for Mobile" fields.
    - The "Prioritize Custom CSS for Styles" option allows the custom CSS fields to completely override the plugin's basic appearance settings for background, font size, and width.
- **Positioning**: Configured in admin settings using a 9-point selection system for desktop and mobile.

## Support
- **Support**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.
- **Issues**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.

## Notes
- **Compatibility**: Tested with WordPress 6.0+ and older themes.
- **Performance**: Lightweight with minimal scripts/styles.
- **Security**: Uses `wp_kses_post` for sanitizing content from the admin textareas, `wp_strip_all_tags` for the Custom CSS field, and relevant sanitizers for other settings. If PHP is used in content textareas, its security is the responsibility of the site admin.
- **Debugging**: Logs errors to `debug.log` if WP_DEBUG is enabled. Check the browser console and Elements tab for rendering issues.
- License: GPLv2 or later
- Tested up to: 6.8.1

== Changelog ==

= 6.0 =
* Security & Standards: Addressed WordPress Plugin Review feedback to enhance security and coding standards.
* Prefixing: Implemented a unique prefix `aflek90tff_` for all plugin options, CSS IDs/classes, admin page slugs, and nonce names to prevent conflicts with other plugins and themes.
* Data Migration: Added a one-time migration routine to seamlessly transfer existing user settings from old option names to the new prefixed names.
* Text Domain: Removed the `load_plugin_textdomain()` call, now relying on WordPress.org's standard mechanism for loading translations (for WordPress 4.6+).
* Sanitization & Escaping: Reviewed and ensured all user inputs are properly sanitized upon saving and all outputs are correctly escaped, particularly for frontend content display and inline CSS generation, adhering to the "escape late" principle.
* Code Refinements: Updated asset file names (CSS, JS) and their enqueue calls to align with the new prefix. Minor code adjustments for clarity and adherence to best practices.

= 5.6 =
* Feature: Content textareas now pre-fill with comprehensive examples on first use, including HTML structure, a %page_title% tag, and placeholder shortcodes.
* Feature: Implemented dynamic tag replacement for %page_title% to display the current page/post title.
* Feature: Added separate Custom CSS textarea fields for Desktop and Mobile views, replacing the single Custom CSS field.
* Feature: Added a "Prioritize Custom CSS for Styles" option. If checked, plugin-generated styles for background, font size, and width are not applied, allowing custom CSS to take full control.
* Enhancement: Updated descriptions in admin settings for content fields and new CSS options.
* Update: Version number increased to 5.6. Readme updated to reflect new features.

= 5.5 =
* Feature: Content for desktop and mobile fields is now managed via textareas in the admin settings (FleK90 > Floating Field > Settings).
* Enhancement: Removed direct reliance on `content-desktop.php` and `content-mobile.php` for content; settings now stored in database options.
* Enhancement: Simplified content fallback logic. If device-specific content in admin is empty, it will check the other device's admin content. The `floating-field-content.php` file is no longer the primary fallback for these.
* Update: Version number increased to 5.5. Readme updated to reflect new content management.

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
