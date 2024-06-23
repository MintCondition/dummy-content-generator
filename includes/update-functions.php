<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function dcg_check_for_updates() {
    $updater = new GitHub_Updater('MintCondition', 'dummy-content-generator');
    $repoInfo = $updater->getRepositoryInfo();

    if ($repoInfo) {
        $latest_version = $repoInfo->tag_name;
        set_transient('dcg_latest_version', $latest_version, DAY_IN_SECONDS);
        set_transient('dcg_last_update_check', current_time('mysql'), DAY_IN_SECONDS);
    }

    return $repoInfo;
}

function dcg_manual_update_check() {
    delete_transient('dcg_latest_version');
    delete_transient('dcg_last_update_check');
    delete_site_transient('update_plugins');
    
    return dcg_check_for_updates();
}

function dcg_check_for_updates_ajax() {
    check_ajax_referer('dcg_check_for_updates_nonce', '_ajax_nonce');

    $result = dcg_manual_update_check();

    if ($result) {
        wp_send_json_success(array(
            'message' => __('Update check completed.', 'text-domain'),
            'latest_version' => get_transient('dcg_latest_version'),
            'last_check' => get_transient('dcg_last_update_check')
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to check for updates.', 'text-domain')));
    }
}

add_action('wp_ajax_dcg_check_for_updates', 'dcg_check_for_updates_ajax');

if (!wp_next_scheduled('dcg_daily_update_check')) {
    wp_schedule_event(time(), 'daily', 'dcg_daily_update_check');
}

add_action('dcg_daily_update_check', 'dcg_check_for_updates');

add_action('admin_init', 'dcg_debug_transient');

function dcg_debug_transient() {
    $transient = get_site_transient('update_plugins');
    error_log('Current update_plugins transient: ' . print_r($transient, true));
}