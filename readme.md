=== A FleK90 Tool Floating Field ===
Contributors: FleK90
Donate link: https://flek90.aureusz.com/
Tags: floating field, fixed field, custom content, admin settings, shortcodes, page relative, placeholders, top banner, notification bar, custom css
Requires at least: 5.0
Tested up to: 6.8.1
Stable tag: 5.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simplified floating field with admin-page settings for content, 9-point positioning, and custom CSS.

**A FleK90 Tool Floating Field (Version 5.0.0)** is a lightweight WordPress plugin, rebuilt for simplicity and core functionality. It adds a fixed-position floating field to your website's front-end. All settings, including content, appearance, and positioning, are now exclusively managed on the plugin's admin page (**Settings > Floating Field Settings**).

The field's content is customizable via textareas, with separate inputs for desktop and mobile devices. It supports plain text, HTML, and special placeholders like `%POST_TITLE%` and `%POST_URL%` to display page-relative information. Positioning is based on a 9-point system (e.g., top-left, bottom-center) for both desktop and mobile views. Users can also add custom CSS rules for further styling. The `floating-field-content.php` file serves as a fallback if no custom content is set.

This version removes the WordPress Customizer integration and X/Y offset controls to streamline configuration.

## Features
- **Customizable Floating Field**: The field content is managed via textareas in the admin settings, supporting HTML and placeholders.
- **Separate Desktop & Mobile Content**: Define different content for desktop and mobile views via the plugin's admin settings page.
- **Simplified 9-Point Positioning (Admin Page)**: Control desktop and mobile positions using a selection of 9 predefined locations.
- **Separate Enable/Disable Controls**: Separate 'Enable on Desktop' and 'Enable on Mobile' controls for precise visibility.
- **Custom CSS Input**: Add custom CSS rules directly via the admin settings page for advanced styling.
- **Admin Menu Management**: All settings (enable/disable, content, background color, font size, positions, custom CSS) are managed via Settings > Floating Field Settings.
- **Placeholder Support**: Use `%POST_TITLE%` and `%POST_URL%` in the content textarea for dynamic, page-specific information.
- **Fallback Content**: `floating-field-content.php` can be used to define fallback content if the admin textarea is empty.
- **Mobile Support**: Displays correctly on mobile devices with responsive styling and content.
- **No Dependencies**: Uses core WordPress APIs—no additional plugins required.
- **Theme Compatibility**: Works with classic and block themes.
- **User Guidance**: Includes a welcome notice and descriptive settings.

## Installation
1. Create a plugin folder named `a-flek90-tool-floating-field` in `wp-content/plugins/`.
2. Add the following files to the folder:
   - `a-flek90-tool-floating-field.php`
   - `floating-field-content.php` (optional, for fallback content)
3. Upload the folder to your WordPress site via FTP or zip it and upload via **Plugins > Add New > Upload Plugin**.
4. Activate the plugin via **Plugins > Installed Plugins**.
5. After activation, a welcome notice guides you to configure settings under **Settings > Floating Field Settings**.

## Usage
### For New Users
- Activate the plugin.
- A welcome notice will guide you to the settings page.

### For Installed Users
- After activation, a welcome notice links to the settings page.
- **Managing Settings (via Admin Page - Settings > Floating Field Settings):**
  - **Enable on Desktop**: Show the field on desktop devices.
  - **Enable on Mobile**: Show the field on mobile devices.
  - **Desktop Content**: Enter your desired content for desktop displays (text, HTML). Use `%POST_TITLE%` for the current post/page title and `%POST_URL%` for its URL. This content is also used for mobile if "Mobile Content" is empty.
  - **Mobile Content**: Enter your desired content specifically for mobile displays. If left empty, "Desktop Content" will be used.
  - **Position Settings**: Configure "Desktop Position" and "Mobile Position" using the dropdowns. Each offers 9 predefined screen locations. Offsets are no longer manually configurable.
  - **Background Color**: Select a color (default: blue).
  - **Font Size**: Set the font size (12–48px, default: 24px).
  - **Custom CSS**: Add custom CSS rules for fine-grained style adjustments to the floating field container.
  - Save changes.
- Further "How to Use" and "About" information can be found on the plugin's admin settings page.
- Visit the front-end to see the field on desktop and mobile.
- **Customizing Content (Details):**
  - The primary way to set the field content is via the **Desktop Content** and **Mobile Content** textareas found under **Settings > Floating Field Settings**. This allows for dynamic text, HTML, and page-specific placeholders.
  - The `floating-field-content.php` file can still be edited to change the fallback content if both the admin settings for "Desktop Content" and "Mobile Content" (for the respective view) are left empty.

## Configuration
- **Global Field**: Applies to all pages.
- **Content**: Managed via "Desktop Content" and "Mobile Content" textareas in **Settings > Floating Field Settings**. Supports HTML and page-specific placeholders (`%POST_TITLE%`, `%POST_URL%`). The `floating-field-content.php` file is used as a final fallback if both relevant custom content fields are empty.
- **Styling**: Customize color and font size via the admin settings page. Further customization can be achieved using the "Custom CSS" field.
- **Positioning**: Configured on the main plugin admin settings page using a 9-point selection system for both desktop and mobile. X/Y offsets are no longer user-configurable.

## Support
- **Support**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.
- **Issues**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.

## Notes
- **Compatibility**: Tested with WordPress 6.0+ and older themes.
- **Performance**: Lightweight with minimal scripts/styles.
- **Security**: Uses `wp_kses_post` for sanitizing custom content from admin settings, `wp_strip_all_tags` for the Custom CSS field, and relevant sanitizers for other settings.
- **Debugging**: Logs errors to `debug.log` if WP_DEBUG is enabled. Check the browser console and Elements tab for rendering issues.
- License: GPLv2 or later
- Tested up to: 6.8.1
