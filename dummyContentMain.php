<?php
/*
Plugin Name: Stratifi Dummy Content Generator
Description: A plugin to create dummy content for WordPress sites in Development
Version: 0.0.3b  
Author: Brian Wood (Stratifi Creative)
Author URI: https://stratificreative.com
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/update-checker.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/create-dummy-content.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils.php';

new GitHub_Updater(__FILE__, 'MintCondition', 'dummy-content-generator');

add_action('admin_enqueue_scripts', 'enqueue_dummy_content_scripts');
function enqueue_dummy_content_scripts() {
    wp_enqueue_script('dummy-content-script', plugin_dir_url(__FILE__) . 'js/dummy-content-admin.js', ['jquery'], '1.0', true);

    // Ensure load_data_types() is available and returns the data types
    $data_types = load_data_types();
    wp_localize_script('dummy-content-script', 'dummyContent', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('create_dummy_content'),
        'data_types' => $data_types
    ]);
}
?>
