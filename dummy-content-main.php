<?php
/*
Plugin Name: Stratifi Dummy Content Generator
Description: A plugin to generate dummy content for WordPress.
Version: 0.5.0
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

// Function to print the JavaScript console log for debugging
function dc_print_console_log() {
    ?>
    <script>
        console.log("DC_PLUGIN_URL: <?php echo DC_PLUGIN_URL; ?>");
    </script>
    <?php
}
add_action('admin_footer', 'dc_print_console_log');
