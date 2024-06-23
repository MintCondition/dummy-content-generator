<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function dcg_display_update_info() {
    $plugin_file_path = plugin_dir_path(__FILE__) . '../dummy-content-main.php';
    
    if (file_exists($plugin_file_path)) {
        $plugin_data = get_plugin_data($plugin_file_path);
        $installed_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : 'Unknown';
    } else {
        $installed_version = 'Unknown';
    }

    $latest_version = get_transient('dcg_latest_version') ?: 'Unknown';
    $last_update_check = get_transient('dcg_last_update_check') ?: 'Never';
    ?>
    <h2><?php esc_html_e('DCG Updates', 'text-domain'); ?></h2>
    <p><?php esc_html_e('Installed Version: ', 'text-domain'); ?><strong><?php echo esc_html($installed_version); ?></strong></p>
    <p><?php esc_html_e('Latest Version: ', 'text-domain'); ?><strong id="dcg_latest_version"><?php echo esc_html($latest_version); ?></strong></p>
    <p><?php esc_html_e('Last Update Check: ', 'text-domain'); ?><strong id="dcg_last_update_check"><?php echo esc_html($last_update_check); ?></strong></p>
    <button id="dcg_check_for_updates" class="button"><?php esc_html_e('Check for Updates', 'text-domain'); ?></button>
    <p id="dcg_update_feedback" style="color: red;"></p>
    <?php
}