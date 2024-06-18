<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function dcg_clear_debug_log() {
    // Ensure the user has the appropriate capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You do not have permission to clear the debug log.', 'text-domain'));
        return;
    }

    // Verify the nonce for security
    check_ajax_referer('clear_debug_log_nonce', 'security');

    // Path to the debug log file
    $log_file = plugin_dir_path(__FILE__) . '../debug.log';

    // Clear the debug log file
    if (file_exists($log_file)) {
        $handle = fopen($log_file, 'w');
        if ($handle) {
            fclose($handle);
            wp_send_json_success(__('Debug log cleared successfully.', 'text-domain'));
        } else {
            wp_send_json_error(__('Unable to clear the debug log.', 'text-domain'));
        }
    } else {
        wp_send_json_error(__('Debug log file does not exist.', 'text-domain'));
    }
}

// Register the AJAX action for clearing the debug log
add_action('wp_ajax_dcg_clear_debug_log', 'dcg_clear_debug_log');
