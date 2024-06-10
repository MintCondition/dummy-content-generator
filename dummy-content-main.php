<?php
/*
Plugin Name: Stratifi Dummy Content Generator
Plugin URI: https://github.com/MintCondition/dummy-content-generator
Description: A plugin to generate dummy content for WordPress.
Version: 0.6.1
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

// Hook for plugin activation
register_activation_hook(__FILE__, 'dcg_activate_plugin');

function dcg_activate_plugin() {
    // Set default settings for all fields
    $post_types = get_post_types(array('public' => true), 'objects');
    $default_fields = array();

    foreach ($post_types as $post_type) {
        $fields = get_dynamic_post_fields($post_type->name);
        foreach ($fields as $type => $type_fields) {
            foreach ($type_fields as $field_name => $field_label) {
                $default_fields[$post_type->name][$type][] = $field_name;
            }
        }
    }

    // Update the option with default fields
    update_option('dummy_content_fields', $default_fields);

    // Create the temporary directory
    dcg_get_temp_directory();
}


// Function to print the JavaScript console log for debugging
function dc_print_console_log() {
    ?>
    <script>
        console.log("DC_PLUGIN_URL: <?php echo DC_PLUGIN_URL; ?>");
    </script>
    <?php
}
add_action('admin_footer', 'dc_print_console_log');
