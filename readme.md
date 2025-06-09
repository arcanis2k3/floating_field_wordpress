# A FleK90 Tool Floating Field

**A FleK90 Tool Floating Field** is a lightweight WordPress plugin that adds a fixed-position floating field to your website's front-end. The field content is hardcoded in a separate file (`floating-field-content.php`) and includes a search form that submits to `https://portal.bestlinks.fun/`. The form features a text input and a submit button with a bold, black SVG search icon, centered in the button, and displayed in a single line on all screen sizes. Manage settings like enable/disable, background color, and font size via Settings > Floating Field Settings in the admin dashboard. The field supports mobile devices and is compatible with any theme—including older ones like Twenty Ten—without extra plugins.

## Features
- **Floating Field with Hardcoded Content**: The field content is defined in `floating-field-content.php`, featuring a search form.
- **Admin Menu Management**: Manage settings (enable/disable, background color, font size) via Settings > Floating Field Settings.
- **Search Form**: Includes a search form with a text input and a submit button using a bold, black SVG search icon, centered in the button, always displayed in one line.
- **Mobile Support**: Displays correctly on mobile devices with responsive styling.
- **No Dependencies**: Uses core WordPress APIs—no additional plugins required.
- **Theme Compatibility**: Works with classic and block themes, including Twenty Ten.
- **User Guidance**: Includes a "View Details" section and a welcome notice.

## Installation
1. Create a plugin folder named `a-flek90-tool-floating-field` in `wp-content/plugins/`.
2. Add the following files to the folder:
   - `a-flek90-tool-floating-field.php`
   - `floating-field-content.php`
3. Upload the folder to your WordPress site via FTP or zip it and upload via **Plugins > Add New > Upload Plugin**.
4. Activate the plugin via **Plugins > Installed Plugins**.
5. After activation, a welcome notice guides you to configure settings:
   - Go to **Settings > Floating Field Settings** to enable/disable the field and customize styling.

## Usage
### For New Users
- On the **Plugins** page, find **A FleK90 Tool Floating Field** and click **View Details** (next to **Activate**).
- Learn about the plugin: a fixed-position field with a hardcoded search form, managed via an admin menu page, compatible with any theme.
- Activate to start—no extra setup needed.

### For Installed Users
- After activation, a welcome notice links to the settings page.
- **Managing Settings**:
  - Go to **Settings > Floating Field Settings**.
  - **Enable Floating Field**: Check to display the field on the front-end.
  - **Show Only on Mobile Devices**: Option to restrict display to mobile/tablets or show on all devices.
  - **Background Color**: Select a color (default: blue).
  - **Font Size**: Set the font size (12–48px, default: 24px).
  - Save changes.
- Further "How to Use" and "About" information can be found on the same page, below these settings.
- Visit the front-end to see the field on desktop and mobile.
- **Customizing Content**:
  - The field content is hardcoded in `floating-field-content.php`. Edit this file to change the content (e.g., modify the search form, add HTML).

## Configuration
- **Global Field**: Applies to all pages. For page-specific fields, contact support.
- **Content**: Hardcoded in `floating-field-content.php`. Includes a search form submitting to `https://portal.bestlinks.fun/`. Edit the file to customize.
- **Styling**: Customize color and font size via the admin settings page. The search form is styled to match the block editor aesthetic, with a bold, black SVG search icon. For advanced styling, modify the inline CSS (see code).
- **Positioning**: Fixed position at the top center of the page.

## Support
- **Support**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.
- **Issues**: Report bugs or request features via email to flek90@aureusz.com or flek90@gmail.com.

## Notes
- **Compatibility**: Tested with WordPress 6.0+ and older themes.
- **Performance**: Lightweight with minimal scripts/styles.
- **Security**: Sanitizes HTML in the hardcoded file.
- **Debugging**: Logs errors to `debug.log` if WP_DEBUG is enabled. Check the browser console and Elements tab for rendering issues.
