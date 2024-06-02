<?php
// File: includes/ajax-handlers.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Handle AJAX request to get post type fields
add_action('wp_ajax_get_post_type_fields', 'handle_get_post_type_fields');
add_action('wp_ajax_nopriv_get_post_type_fields', 'handle_get_post_type_fields'); // If needed for non-logged-in users

function handle_get_post_type_fields() {
    check_ajax_referer('create_dummy_content', 'nonce');

    $post_type = sanitize_text_field($_POST['post_type']);
    $fields = array();

    if ($post_type) {
        $post_type_object = get_post_type_object($post_type);
        if ($post_type_object) {
            // Get basic fields (simplified for example purposes)
            $fields[] = array('label' => 'Title', 'name' => 'title');
            $fields[] = array('label' => 'Content', 'name' => 'content');
            // Add more fields as needed
        }
    }

    wp_send_json_success($fields);
}

// Handle AJAX request to get data type parameters
add_action('wp_ajax_get_data_type_parameters', 'handle_get_data_type_parameters');
add_action('wp_ajax_nopriv_get_data_type_parameters', 'handle_get_data_type_parameters'); // If needed for non-logged-in users

function handle_get_data_type_parameters() {
    check_ajax_referer('create_dummy_content', 'nonce');

    $data_type = sanitize_text_field($_POST['data_type']);
    $field = sanitize_text_field($_POST['field']);
    $parameters = '';

    if ($data_type) {
        switch ($data_type) {
            case 'lorem-ipsum':
                $parameters = '<input type="text" name="length" placeholder="Length">';
                $parameters .= '<input type="text" name="prefix" placeholder="Prefix">';
                $parameters .= '<input type="text" name="suffix" placeholder="Suffix">';
                break;
            // Add cases for other data types and their parameters
        }
    }

    wp_send_json_success(['parameters' => $parameters]);
}
