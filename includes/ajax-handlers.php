<?php
// File: includes/ajax-handlers.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'utils.php'; // Include the utils file
require_once plugin_dir_path(__FILE__) . 'data-generator-interface.php'; // Include the DataGeneratorInterface


// Load data types configuration
$data_types = load_data_types();

add_action('wp_ajax_get_post_type_fields', 'handle_get_post_type_fields');
add_action('wp_ajax_nopriv_get_post_type_fields', 'handle_get_post_type_fields'); // If needed for non-logged-in users

function handle_get_post_type_fields() {
    check_ajax_referer('create_dummy_content', 'nonce');

    $post_type = sanitize_text_field($_POST['post_type']);
    $fields = array();

    if ($post_type) {
        $post_type_object = get_post_type_object($post_type);
        if ($post_type_object) {
            $fields[] = array('label' => 'Title', 'name' => 'title');
            $fields[] = array('label' => 'Content', 'name' => 'content');
        }
    }

    wp_send_json_success($fields);
}

add_action('wp_ajax_preview_dummy_content', 'handle_preview_dummy_content');

function handle_preview_dummy_content() {
    check_ajax_referer('create_dummy_content', 'nonce');

    $post_type = sanitize_text_field($_POST['post_type']);
    $post_count = intval($_POST['post_count']);
    $fields = $_POST['fields'];

    $data_types = load_data_types(); // Ensure data types are loaded here

    $preview_data = array();

    for ($i = 0; $i < $post_count; $i++) {
        $post_preview = array();

        foreach ($fields as $field) {
            $dataType = $field['dataType'];
            $generator = $field['generator'];
            $parameters = $field['parameters'];

            if (isset($data_types[$dataType])) {
                $generator_class = $data_types[$dataType]['generators'][$generator]['class'];
                $generator_path = plugin_dir_path(__FILE__) . "../generators/" . strtolower($generator_class) . ".php";
                if (file_exists($generator_path)) {
                    require_once $generator_path;
                    if (class_exists($generator_class)) {
                        $content = call_user_func(array($generator_class, 'generate'), $parameters);
                        $post_preview[$field['field']] = $content;
                    } else {
                        $post_preview[$field['field']] = 'Generator class not found. Class: ' . $generator_class;
                    }
                } else {
                    $post_preview[$field['field']] = 'Generator file not found. Path: ' . $generator_path;
                }
            } else {
                $post_preview[$field['field']] = 'Data type not found. Data type: ' . $dataType;
            }
        }

        $preview_data[] = $post_preview;
    }

    wp_send_json_success($preview_data);
}
