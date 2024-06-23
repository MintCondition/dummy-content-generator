<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function dcg_set_default_settings() {
    // Set default settings for all fields
    $post_types = get_post_types(array('public' => true), 'objects');
    $default_fields = array();

    foreach ($post_types as $post_type) {
        $fields = get_dynamic_post_fields($post_type->name);
        foreach ($fields as $type => $type_fields) {
            foreach ($type_fields as $field_name => $field_label) {
                $default_fields[$post_type->name][$type][] = $field_name;
            }
        }
    }

    return $default_fields;
}

function dcg_update_option_fields($default_fields) {
    // Update the option with default fields
    update_option('dummy_content_fields', $default_fields);
}

function dcg_create_temp_directory() {
    // Create the temporary directory
    dcg_get_temp_directory();
}

function dcg_activate_plugin() {
    $default_fields = dcg_set_default_settings();
    dcg_update_option_fields($default_fields);
    dcg_create_temp_directory();
}
