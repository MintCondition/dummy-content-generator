<?php
/*
Plugin Name: Stratifi Dummy Content Generator
Description: A plugin to create dummy content for WordPress sites in Development
Version: 0.0.2
Author: Brian Wood (Stratifi Creative)
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('SITE_CONFIG_API_NAMESPACE', 'site-config/v1');
define('SITE_CONFIG_API_ROUTE', '/data');
define('SITE_CONFIG_API_TEST_ROUTE', '/test');

require_once plugin_dir_path(__FILE__) . 'includes/update-checker.php';

new GitHub_Updater(__FILE__, 'MintCondition', 'dummy-content-generator');

add_action('rest_api_init', ['Site_Config_API', 'register_routes']);
