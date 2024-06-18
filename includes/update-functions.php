<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function dcg_check_for_updates() {
    error_log('Running dcg_check_for_updates');
    if (false === get_transient('dcg_update_check')) {
        error_log('Initializing GitHub Updater for check.');
        $updater = new GitHub_Updater('MintCondition', 'dummy-content-generator');

        // Fetch repository info
        $githubAPIResult = $updater->getRepositoryInfo();

        if (is_wp_error($githubAPIResult)) {
            error_log('Error fetching repository info: ' . $githubAPIResult->get_error_message());
            return;
        }

        $latest_version = isset($githubAPIResult->tag_name) ? $githubAPIResult->tag_name : 'Unknown';
        set_transient('dcg_latest_version', $latest_version, DAY_IN_SECONDS);
        set_transient('dcg_update_check', true, DAY_IN_SECONDS);
        set_transient('dcg_last_update_check', current_time('mysql'), DAY_IN_SECONDS); // Set the last update check time

        error_log("Update check completed. Latest version: $latest_version");
    } else {
        error_log('Update check skipped as transient is already set.');
    }
}

// Function to manually flush the update transient and force an update check
function dcg_manual_update_check() {
    error_log('Running dcg_manual_update_check');
    delete_transient('dcg_update_check');
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
