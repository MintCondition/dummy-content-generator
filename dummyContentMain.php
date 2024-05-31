<?php
/*
Plugin Name: Stratifi Dummy Content Generator
Description: A plugin to create dummy content for WordPress sites in Development
Version: 0.0.3
Author: Brian Wood (Stratifi Creative)
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('SITE_CONFIG_API_NAMESPACE', 'site-config/v1');
define('SITE_CONFIG_API_ROUTE', '/data');
define('SITE_CONFIG_API_TEST_ROUTE', '/test');

require_once plugin_dir_path(__FILE__) . 'includes/update-checker.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';

new GitHub_Updater(__FILE__, 'MintCondition', 'dummy-content-generator');

// Add custom logo to plugin update row
add_filter('plugin_row_meta', 'add_dummy_content_logo', 10, 2);
function add_dummy_content_logo($plugin_meta, $plugin_file) {
    // Check if this is our plugin
    if ($plugin_file == plugin_basename(__FILE__)) {
        $logo_url = plugin_dir_url(__FILE__) . 'assets/images/DummyContentGeneratorLogo.svg';
        $plugin_meta = array_merge(
            array('<img src="' . esc_url($logo_url) . '" style="width: 50px; height: auto; margin-right: 10px;">'),
            $plugin_meta
        );
    }
    return $plugin_meta;
}
