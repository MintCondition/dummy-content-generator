<?php
/*
Plugin Name: Stratifi Dummy Content Generator
Plugin URI: https://github.com/MintCondition/dummy-content-generator
Description: A plugin to generate dummy content for WordPress.
Version: 0.7.0
Author: Brian Wood - Stratifi Creative
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define the plugin URL
define('DC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/clear-debug-callback.php';
require_once plugin_dir_path(__FILE__) . 'includes/plugin-activation.php';
require_once plugin_dir_path(__FILE__) . 'includes/update-checker.php';
require_once plugin_dir_path(__FILE__) . 'includes/update-functions.php'; // New file for update functions

// Hook for plugin activation
register_activation_hook(__FILE__, 'dcg_activate_plugin');

// Schedule a daily event for update checks
if (!wp_next_scheduled('dcg_daily_update_check')) {
    wp_schedule_event(time(), 'daily', 'dcg_daily_update_check');
}

add_action('dcg_daily_update_check', 'dcg_check_for_updates');

add_action('plugins_loaded', 'dcg_initialize_updater');

function dcg_initialize_updater() {
    if (class_exists('GitHub_Updater')) {
        $updater = new GitHub_Updater('MintCondition', 'dummy-content-generator');
        add_filter('pre_set_site_transient_update_plugins', array($updater, 'setTransient'));
    } else {
        error_log('GitHub_Updater class not found');
    }
}