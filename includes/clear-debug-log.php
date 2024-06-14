<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to clear the debug.log file
function dcg_clear_debug_log() {
    check_ajax_referer('clear_debug_log_nonce', '_ajax_nonce');

    $log_file = WP_CONTENT_DIR . '/debug.log';

    if (file_exists($log_file)) {
        $cleared = file_put_contents($log_file, '');

        if ($cleared !== false) {
            wp_send_json_success(__('Debug log cleared successfully.', 'text-domain'));
        } else {
            wp_send_json_error(__('Failed to clear the debug log.', 'text-domain'));
        }
    } else {
        wp_send_json_error(__('Debug log file does not exist.', 'text-domain'));
    }
}
add_action('wp_ajax_dcg_clear_debug_log', 'dcg_clear_debug_log');
