<?php
// File: includes/settings.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dummy_Content_Settings_Page {
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('dummy_content_settings', 'dummy_content_fields');

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
            });
        </script>
        <?php
    }

    public function display_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Dummy Content Generator Settings', 'text-domain'); ?></h1>
            <form method="post" action="options.php">
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
?>
