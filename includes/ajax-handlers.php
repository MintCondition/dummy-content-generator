<?php
// File: includes/ajax-handlers.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('wp_ajax_get_generators', 'get_generators');
add_action('wp_ajax_get_generator_parameters', 'get_generator_parameters');

function get_generators() {
    check_ajax_referer('create_dummy_content', '_ajax_nonce');

    $data_type = sanitize_text_field($_POST['data_type']);
    $field = sanitize_text_field($_POST['field']);

    // Fetch generators for the selected data type
    $generators = array(); // Replace with the actual generators fetching logic

    wp_send_json_success(array('generators' => $generators));
}

function get_generator_parameters() {
    check_ajax_referer('create_dummy_content', '_ajax_nonce');

    $generator = sanitize_text_field($_POST['generator']);
    $field = sanitize_text_field($_POST['field']);

    // Fetch parameters for the selected generator
    $parameters_html = ''; // Replace with the actual parameters fetching logic

    wp_send_json_success(array('parameters_html' => $parameters_html));
}
