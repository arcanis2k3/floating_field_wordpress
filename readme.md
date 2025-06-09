=== A FleK90 Tool Floating Field ===
Contributors: FleK90
Donate link: https://flek90.aureusz.com/
Tags: floating field, fixed field, custom content, admin settings, shortcodes, page relative, placeholders, top banner, notification bar, customizer
Requires at least: 5.0
Tested up to: 6.8.1
Stable tag: 4.1.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customizable floating field with separate desktop/mobile content and positions via Admin & Customizer.

**A FleK90 Tool Floating Field** is a lightweight WordPress plugin that adds a fixed-position floating field to your website's front-end. The field's content is now customizable via a textarea in the plugin's admin settings (Settings > Floating Field Settings), with separate inputs for desktop and mobile devices. It supports plain text, HTML, and special placeholders like `%POST_TITLE%` and `%POST_URL%` to display page-relative information. Positioning (including offsets) for both desktop and mobile can be managed via the WordPress Customizer or the plugin's admin page. The `floating-field-content.php` file serves as a fallback if no custom content is set. Manage general settings like enable/disable, background color, and font size via Settings > Floating Field Settings in the admin dashboard. The field supports mobile devices and is compatible with any theme—including older ones like Twenty Ten—without extra plugins.

## Features
- **Customizable Floating Field**: The field content is managed via textareas in the admin settings, supporting HTML and placeholders.
- **Separate Desktop & Mobile Content**: Define different content for desktop and mobile views via the plugin's admin settings page.
- **Positioning via Customizer & Admin Page**: Control desktop and mobile positions (9 options like top-left, bottom-center) and X/Y offsets through Appearance > Customize > Floating Field Display (with live preview) or directly on the plugin's admin settings page.
- **Admin Menu Management**: Manage settings (enable/disable, content, background color, font size, positions) via Settings > Floating Field Settings.
- **Placeholder Support**: Use `%POST_TITLE%` and `%POST_URL%` in the content textarea for dynamic, page-specific information.
- **Fallback Content**: `floating-field-content.php` can be used to define fallback content if the admin textarea is empty.
- **Example Usage**: Can be used to display announcements, important links, a simple search form, or other custom HTML.
- **Mobile Support**: Displays correctly on mobile devices with responsive styling and content.
- **No Dependencies**: Uses core WordPress APIs—no additional plugins required.
- **Theme Compatibility**: Works with classic and block themes, including Twenty Ten.
- **User Guidance**: Includes a welcome notice and descriptive settings.

## Installation
1. Create a plugin folder named `a-flek90-tool-floating-field` in `wp-content/plugins/`.
2. Add the following files to the folder:
   - `a-flek90-tool-floating-field.php`
   - `assets/js/customize-preview.js`
   - `floating-field-content.php` (optional, for fallback content)
3. Upload the folder to your WordPress site via FTP or zip it and upload via **Plugins > Add New > Upload Plugin**.
4. Activate the plugin via **Plugins > Installed Plugins**.
5. After activation, a welcome notice guides you to configure settings:
   - Go to **Settings > Floating Field Settings** to enable/disable the field, set content, customize styling, and set positions.
   - Alternatively, go to **Appearance > Customize > Floating Field Display** to set field positions and offsets with a live preview.

## Usage
### For New Users
- Activate the plugin.
- A welcome notice will guide you to the settings page.

### For Installed Users
- After activation, a welcome notice links to the settings page.
- **Managing Settings (via Admin Page - Settings > Floating Field Settings):**
  - **Enable Floating Field**: Check to display the field on the front-end.
  - **Show Only on Mobile Devices**: Option to restrict display to mobile/tablets or show on all devices.
  - **Desktop Content**: Enter your desired content for desktop displays (text, HTML). Use `%POST_TITLE%` for the current post/page title and `%POST_URL%` for its URL. This content is also used for mobile if "Mobile Content" is empty.
  - **Mobile Content**: Enter your desired content specifically for mobile displays. If left empty, "Desktop Content" will be used.
  - **Position Settings**: Configure Desktop Position, Desktop Offset X/Y, Mobile Position, and Mobile Offset X/Y directly on this page. These settings are synchronized with the Customizer.
  - **Background Color**: Select a color (default: blue).
  - **Font Size**: Set the font size (12–48px, default: 24px).
  - Save changes.
- **Positioning Settings (Alternative - via Customizer):**
  - Positioning of the floating field can be adjusted via the WordPress Customizer (**Appearance > Customize > Floating Field Display**) which offers a live preview, or directly on the plugin's admin settings page (**Settings > Floating Field Settings**).
  - In the Customizer:
    - **Desktop Position**: Choose from 9 screen positions (e.g., Top Center, Bottom Left).
    - **Desktop Offset X/Y**: Fine-tune the position with X and Y offsets (e.g., `10px`, `5%`).
    - **Mobile Position**: Choose from 9 screen positions for mobile views.
    - **Mobile Offset X/Y**: Fine-tune the mobile position with X and Y offsets.
  - Changes in the Customizer are previewed live and saved by publishing.
- Further "How to Use" and "About" information can be found on the plugin's admin settings page.
- Visit the front-end to see the field on desktop and mobile.
- **Customizing Content (Details):**
  - The primary way to set the field content is via the **Desktop Content** and **Mobile Content** textareas found under **Settings > Floating Field Settings**. This allows for dynamic text, HTML, and page-specific placeholders.
  - The `floating-field-content.php` file can still be edited to change the fallback content if both the admin settings for "Desktop Content" and "Mobile Content" (for the respective view) are left empty.

## Configuration
- **Global Field**: Applies to all pages.
- **Content**: Managed via "Desktop Content" and "Mobile Content" textareas in **Settings > Floating Field Settings**. Supports HTML and page-specific placeholders (`%POST_TITLE%`, `%POST_URL%`). The `floating-field-content.php` file is used as a final fallback if both relevant custom content fields are empty.
- **Styling**: Customize color and font size via the admin settings page.
- **Positioning**: Configured via the WordPress Customizer under **Appearance > Customize > Floating Field Display** (recommended for live preview) or on the main plugin admin settings page. Allows separate position and offset settings for desktop and mobile views. For advanced styling, modify the inline CSS within `a-flek90-tool-floating-field.php`.

## Support
- **Support**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.
- **Issues**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.

## Notes
- **Compatibility**: Tested with WordPress 6.0+ and older themes.
- **Performance**: Lightweight with minimal scripts/styles.
- **Security**: Uses `wp_kses_post` for sanitizing custom content from admin settings, `sanitize_key` and custom sanitizers for Customizer settings, and `sanitize_content` method for fallback content.
- **Debugging**: Logs errors to `debug.log` if WP_DEBUG is enabled. Check the browser console and Elements tab for rendering issues.
- License: GPLv2 or later
- Tested up to: 6.8.1
