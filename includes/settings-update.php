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
    <button id="dcg_clear_update_data" class="button"><?php esc_html_e('Clear Update Data', 'text-domain'); ?></button>
    <p id="dcg_update_feedback" style="color: red;"></p>
    <?php
}

// Add this function to handle the AJAX request for clearing update data
function dcg_clear_update_data_ajax() {
    check_ajax_referer('dcg_update_nonce', 'nonce');

    $plugin_file = plugin_basename(DCG_PLUGIN_FILE);

    // Clear WordPress update cache
    wp_clean_plugins_cache(true);

    // Remove our plugin from the update_plugins transient
    $update_plugins = get_site_transient('update_plugins');
    if ($update_plugins) {
        if (isset($update_plugins->response[$plugin_file])) {
            unset($update_plugins->response[$plugin_file]);
        }
        if (isset($update_plugins->checked[$plugin_file])) {
            unset($update_plugins->checked[$plugin_file]);
        }
        set_site_transient('update_plugins', $update_plugins);
    }

    // Clear our custom transients
    delete_transient('dcg_latest_version');
    delete_transient('dcg_last_update_check');

    // Force WordPress to check for updates again
    wp_version_check();
    wp_update_plugins();

    wp_send_json_success(array('message' => __('Update data cleared successfully. Please refresh the page to see the changes.', 'text-domain')));
}
add_action('wp_ajax_dcg_clear_update_data', 'dcg_clear_update_data_ajax');
add_action('wp_ajax_dcg_clear_update_data', 'dcg_clear_update_data_ajax');

// Modify your existing JavaScript to include the new button functionality
add_action('admin_footer', 'dcg_update_script');
function dcg_update_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#dcg_check_for_updates').on('click', function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dcg_check_for_updates',
                    _ajax_nonce: '<?php echo wp_create_nonce('dcg_update_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#dcg_latest_version').text(response.data.latest_version);
                        $('#dcg_last_update_check').text(response.data.last_check);
                        $('#dcg_update_feedback').text(response.data.message).css('color', 'green');
                    } else {
                        $('#dcg_update_feedback').text(response.data.message).css('color', 'red');
                    }
                }
            });
        });

        $('#dcg_clear_update_data').on('click', function() {
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'dcg_clear_update_data',
            nonce: '<?php echo wp_create_nonce('dcg_update_nonce'); ?>'
        },
        success: function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload(); // Refresh the page
            } else {
                $('#dcg_update_feedback').text('Failed to clear update data.').css('color', 'red');
            }
        }
    });
});
    });
    </script>
    <?php
}