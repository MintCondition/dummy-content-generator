<?php
// File: includes/settings.php
// TODO: Make this pretty.
// TODO: Why isn't this saving field settings? 


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dummy_Content_Settings_Page {
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('dummy_content_settings', 'dummy_content_fields');
        register_setting('dummy_content_settings', 'dummy_content_temp_dir');

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
    }

    public function fields_callback() {
        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <div id="accordion">
            <?php foreach ($post_types as $post_type): ?>
                <h3><?php echo esc_html($post_type->label); ?></h3>
                <div>
                    <?php 
                    $fields = get_dynamic_post_fields($post_type->name); 
                    $selected_fields = get_option('dummy_content_fields', array());
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
            });
        </script>
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
            <form method="post" action="" id="dummy_content_form">
                <?php
                settings_fields('dummy_content_settings');
                do_settings_sections('dummy_content_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

new Dummy_Content_Settings_Page();
