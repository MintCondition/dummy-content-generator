<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function dcg_check_for_updates() {
    error_log('Running dcg_check_for_updates');
    $transient_key = 'dcg_update_check';
    if (false === get_transient($transient_key)) {
        error_log('Initializing GitHub Updater for check.');
        $updater = new GitHub_Updater('MintCondition', 'dummy-content-generator');

        // Force the transient update to check for new versions
        $transient = get_site_transient('update_plugins');
        $transient = $updater->setTransient($transient);
        set_site_transient('update_plugins', $transient);

        // Fetch repository info
        $githubAPIResult = $updater->getRepositoryInfo();

        if (is_wp_error($githubAPIResult)) {
            error_log('Error fetching repository info: ' . $githubAPIResult->get_error_message());
            return;
        }

        $latest_version = isset($githubAPIResult->tag_name) ? $githubAPIResult->tag_name : 'Unknown';
        set_transient('dcg_latest_version', $latest_version, DAY_IN_SECONDS);
        set_transient($transient_key, true, DAY_IN_SECONDS);
        set_transient('dcg_last_update_check', current_time('mysql'), DAY_IN_SECONDS); // Set the last update check time

        error_log("Update check completed. Latest version: $latest_version");
    } else {
        error_log('Update check skipped as transient is already set.');
    }
}

// Function to manually flush the update transient and force an update check
function dcg_manual_update_check() {
    error_log('Running dcg_manual_update_check');
    $transient_key = 'dcg_update_check';
    delete_transient($transient_key);
    dcg_check_for_updates();
}

function dcg_check_for_updates_ajax() {
    error_log('Running dcg_check_for_updates_ajax');
    check_ajax_referer('dcg_check_for_updates_nonce', '_ajax_nonce');

    // Force the update check by clearing the transient
    dcg_manual_update_check();

    // Send success response
    wp_send_json_success(array('message' => __('Update check completed.', 'text-domain')));
}

// Register the AJAX action for checking updates
add_action('wp_ajax_dcg_check_for_updates', 'dcg_check_for_updates_ajax');

// Add action to check for updates on admin_init
add_action('admin_init', 'dcg_check_for_updates');

// Add the transient data for debug purposes on the plugin update page
add_filter('pre_set_site_transient_update_plugins', function($transient) {
    error_log('pre_set_site_transient_update_plugins: ' . print_r($transient, true));
    return $transient;
});

// Debug log for checking update_plugins transient when the update page is loaded
add_action('load-update-core.php', function() {
    $transient = get_site_transient('update_plugins');
    error_log('Loaded update_plugins transient: ' . print_r($transient, true));
});

function dcg_clear_update_cache() {
    error_log('Clearing update cache');
    delete_site_transient('update_plugins');
}
add_action('admin_init', 'dcg_clear_update_cache');
