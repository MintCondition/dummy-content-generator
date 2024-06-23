<?php
// File: includes/settings.php
// TODO: Make this pretty.

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dummy_Content_Settings_Page {
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('wp_ajax_clear_temp_dir', array($this, 'clear_temp_dir'));
    }

    public function register_settings() {
        register_setting('dummy_content_settings', 'dummy_content_fields', array($this, 'sanitize_fields'));
        register_setting('dummy_content_settings', 'dummy_content_temp_dir');
        register_setting('dummy_content_settings', 'dummy_content_cleanup');
        register_setting('dummy_content_settings', 'dummy_content_clear_debug', array('default' => 0));

        add_settings_section(
            'dummy_content_settings_section',
            __('Dummy Content Settings', 'text-domain'),
            null,
            'dummy_content_settings'
        );

        add_settings_field(
            'dummy_content_fields',
            __('Post Type Fields', 'text-domain'),
            array($this, 'fields_callback'),
            'dummy_content_settings',
            'dummy_content_settings_section'
        );

        add_settings_field(
            'dummy_content_cleanup',
            __('Clean Up After Generation', 'text-domain'),
            array($this, 'cleanup_callback'),
            'dummy_content_settings',
            'dummy_content_settings_section'
        );

        add_settings_field(
            'dummy_content_temp_dir',
            __('Temporary Directory Name', 'text-domain'),
            array($this, 'temp_dir_callback'),
            'dummy_content_settings',
            'dummy_content_settings_section'
        );

        add_settings_field(
            'dummy_content_temp_dir_status',
            __('Temporary Directory Status', 'text-domain'),
            array($this, 'temp_dir_status_callback'),
            'dummy_content_settings',
            'dummy_content_settings_section'
        );

        add_settings_field(
            'dummy_content_clear_debug',
            __('Enable Clear Debug Log Button', 'text-domain'),
            array($this, 'clear_debug_callback'),
            'dummy_content_settings',
            'dummy_content_settings_section'
        );
    }

    public function sanitize_fields($fields) {
        $sanitized_fields = array();
        foreach ($fields as $post_type => $types) {
            $sanitized_fields[$post_type] = array();
            foreach ($types as $type => $type_fields) {
                $sanitized_fields[$post_type][$type] = array_map('sanitize_text_field', $type_fields);
            }
        }
        return $sanitized_fields;
    }

    public function fields_callback() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $selected_fields = get_option('dummy_content_fields', array());

        ?>
        <div id="accordion">
            <?php foreach ($post_types as $post_type): ?>
                <h3><?php echo esc_html($post_type->label); ?></h3>
                <div>
                    <?php
                    $fields = get_dynamic_post_fields($post_type->name);
                    $selected_fields_for_type = isset($selected_fields[$post_type->name]) ? $selected_fields[$post_type->name] : array();
                    foreach ($fields as $type => $type_fields) {
                        echo '<h4>' . ucfirst($type) . '</h4>';
                        echo '<ul>';
                        foreach ($type_fields as $field_name => $field_label) {
                            $is_checked = isset($selected_fields_for_type[$type]) && in_array($field_name, $selected_fields_for_type[$type]);
                            ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="dummy_content_fields[<?php echo esc_attr($post_type->name); ?>][<?php echo esc_attr($type); ?>][]" value="<?php echo esc_attr($field_name); ?>" <?php checked($is_checked, true); ?>>
                                    <?php echo esc_html($field_label) . ' (' . esc_html($field_name) . ')'; ?>
                                </label>
                            </li>
                            <?php
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $("#accordion").accordion({
                    heightStyle: "content",
                    collapsible: true
                });

                $('#dummy_content_temp_dir').on('input', function() {
                    $('#change_temp_dir').prop('disabled', false);
                });

                $('#change_temp_dir').on('click', function() {
                    var newDir = $('#dummy_content_temp_dir').val().trim();
                    if (newDir === '') {
                        alert('<?php esc_html_e('Temporary directory name cannot be empty.', 'text-domain'); ?>');
                        $('#dummy_content_temp_dir').val('<?php echo esc_js(get_option('dummy_content_temp_dir', 'dummy-content-temp')); ?>');
                        return;
                    }
                    if (confirm('<?php esc_html_e('Are you sure you want to change the temporary directory? This action will delete the current directory and its contents.', 'text-domain'); ?>')) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'change_temp_dir',
                            value: '1'
                        }).appendTo('#dummy_content_form');

                        $('<input>').attr({
                            type: 'hidden',
                            name: 'old_temp_dir',
                            value: '<?php echo esc_js(get_option('dummy_content_temp_dir', 'dummy-content-temp')); ?>'
                        }).appendTo('#dummy_content_form');

                        $('#submit').click();
                    }
                });

                $('#clear_temp_dir').on('click', function() {
                    if (confirm('<?php esc_html_e('Are you sure you want to clear all files from the temporary directory?', 'text-domain'); ?>')) {
                        $.post(ajaxurl, {
                            action: 'clear_temp_dir',
                            _ajax_nonce: '<?php echo wp_create_nonce('clear_temp_dir_nonce'); ?>'
                        }, function(response) {
                            if (response.success) {
                                alert('<?php esc_html_e('Temporary directory cleared successfully.', 'text-domain'); ?>');
                                location.reload();
                            } else {
                                alert('<?php esc_html_e('Failed to clear the temporary directory.', 'text-domain'); ?>');
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }

    public function cleanup_callback() {
        $cleanup = get_option('dummy_content_cleanup', true);
        ?>
        <label>
            <input type="checkbox" name="dummy_content_cleanup" value="1" <?php checked($cleanup, true); ?>>
            <?php esc_html_e('Remove Temp Files on successful post creation', 'text-domain'); ?>
        </label>
        <?php
    }

    public function temp_dir_callback() {
        $temp_dir = get_option('dummy_content_temp_dir', 'dummy-content-temp');
        $upload_dir = wp_upload_dir();
        $base_dir = str_replace(ABSPATH, '', $upload_dir['basedir']);
        ?>
        <div><?php echo esc_html($base_dir . '/'); ?><input type="text" id="dummy_content_temp_dir" name="dummy_content_temp_dir" value="<?php echo esc_attr($temp_dir); ?>"><button type="button" id="change_temp_dir" disabled><?php esc_html_e('Change', 'text-domain'); ?></button></div>
        <?php
    }

    public function temp_dir_status_callback() {
        $status = dcg_get_temp_dir_status();
        ?>
        <p><?php echo sprintf(__('Path: %s', 'text-domain'), esc_html($status['path'])); ?></p>
        <p><?php echo sprintf(__('Number of files: %d', 'text-domain'), esc_html($status['file_count'])); ?></p>
        <p><?php echo sprintf(__('Total size: %d bytes', 'text-domain'), esc_html($status['total_size'])); ?></p>
        <p><?php echo sprintf(__('Is writable: %s', 'text-domain'), esc_html($status['is_writable'] ? __('Yes', 'text-domain') : __('No', 'text-domain'))); ?></p>
        <button type="button" id="clear_temp_dir"><?php esc_html_e('Clear Temp Directory', 'text-domain'); ?></button>
        <?php
    }

    public function clear_debug_callback() {
        $clear_debug = get_option('dummy_content_clear_debug', false);
        ?>
        <label>
            <input type="checkbox" name="dummy_content_clear_debug" value="1" <?php checked($clear_debug, true); ?>>
            <?php esc_html_e('Enable Clear Debug Log Button', 'text-domain'); ?>
        </label>
        <?php
    }

    public function display_page() {
        if (isset($_POST['change_temp_dir']) && $_POST['change_temp_dir'] === '1') {
            $new_temp_dir = sanitize_text_field($_POST['dummy_content_temp_dir']);
            $old_temp_dir = sanitize_text_field($_POST['old_temp_dir']);

            // Check if new directory name is empty
            if (empty($new_temp_dir)) {
                add_settings_error(
                    'dummy_content_settings',
                    'dummy_content_temp_dir_empty',
                    __('Temporary directory name cannot be empty.', 'text-domain'),
                    'error'
                );
            } else {
                // Update the option
                update_option('dummy_content_temp_dir', $new_temp_dir);

                // Delete the old directory
                $upload_dir = wp_upload_dir();
                $old_dir_path = $upload_dir['basedir'] . '/' . $old_temp_dir;
                dcg_delete_directory($old_dir_path);

                // Create the new directory
                dcg_create_temp_dir($new_temp_dir);

                add_settings_error(
                    'dummy_content_settings',
                    'dummy_content_temp_dir_changed',
                    __('Temporary directory changed successfully.', 'text-domain'),
                    'updated'
                );

                // Redirect to the settings page to avoid resubmission
                wp_redirect(admin_url('admin.php?page=dummy-content-settings'));
                exit;
            }
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Dummy Content Generator Settings', 'text-domain'); ?></h1>
            <form method="post" action="options.php" id="dummy_content_form">
                <?php
                settings_fields('dummy_content_settings');
                do_settings_sections('dummy_content_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function add_settings_page() {
        add_options_page(
            __('Dummy Content Settings', 'text-domain'),
            __('Dummy Content', 'text-domain'),
            'manage_options',
            'dummy-content-settings',
            array($this, 'display_page')
        );
    }

    public function clear_temp_dir() {
        check_ajax_referer('clear_temp_dir_nonce', '_ajax_nonce');

        $temp_dir = dcg_get_temp_directory();
        $files = glob($temp_dir . '/*'); // Get all file names
        $json_files_removed = 0;
        $other_files_removed = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                if (unlink($file)) { // Delete the file
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                        $json_files_removed++;
                    } else {
                        $other_files_removed++;
                    }
                }
            }
        }

        wp_send_json_success(array(
            'json_files_removed' => $json_files_removed,
            'other_files_removed' => $other_files_removed
        ));
    }
}

new Dummy_Content_Settings_Page();
?>
