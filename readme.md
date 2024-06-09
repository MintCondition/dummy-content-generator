=== Site Config JSON API ===
Contributors: Brian Wood
Tags: WordPress, plugin, dummy content
Requires at least: 5.0
Tested up to: 6.5.4
Stable tag: 0.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
This plugin will (when it's ready) easily generate dummy content for your WordPress site. Eventually
it will allow you to create n posts of any defined type, including titles, content, images, and even
meta-data.

== Installation ==
If you can't figure out how to install a WP Plugin, you should NOT use this one.

== Changelog ==
0.5.1 - 2024-06-09

### Enhancements:

- Settings now sets all fields to available on activation

### Fixed:

- Settings now saves available fields correctly.

  0.5.0 - Version 0.5.0 - 2024-06-09

Approaching MVP. Currently only tested with posts and 3 fields: Title, Content, and Feat Image.

### New Features:

- Added Picsum Image Generator using Picsum.photos API for random, grayscale, blur, and seed-based images with custom sizes.
- Standardized generator output format including type, data type, and content.
- Introduced settings for specifying a temporary directory for image storage.
- Added settings for choosing which fields are available for which post types

### Enhancements:

- Improved settings page with collapsible sections and temporary directory settings.
- Added functions for temporary directory management: create, delete, and get status.
- Enhanced error handling for image fetching and saving in generators.

### Fixes:

- Resolved open_basedir restriction issues by adjusting paths.
- Fixed JavaScript issues on the settings page.
- Ensured old temporary directories are deleted when a new one is specified.

### Miscellaneous:

- Added logging for debugging purposes.
- Refactored code for better readability and maintainability.

### Known Issues

- Settings is not saving field options.

  0.0.2 - Adding Admin Pages
  0.0.1 - Initial commit with updater functionality.