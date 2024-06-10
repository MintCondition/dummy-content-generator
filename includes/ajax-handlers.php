<?php
// File: includes/ajax-handlers.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('wp_ajax_get_generators', 'get_generators');
add_action('wp_ajax_get_generator_parameters', 'get_generator_parameters');

function get_generators() {
    if (!check_ajax_referer('create_dummy_content', '_ajax_nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    $data_type = sanitize_text_field($_POST['data_type']);
    $field = sanitize_text_field($_POST['field']);

    $data_types = load_data_types();
    if (!isset($data_types[$data_type])) {
        wp_send_json_error('Data type not found');
        return;
    }

    $generators = isset($data_types[$data_type]['generators']) ? $data_types[$data_type]['generators'] : array();

    wp_send_json_success(array('generators' => $generators));
}

function get_generator_parameters() {
    if (!check_ajax_referer('create_dummy_content', '_ajax_nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    $generator = sanitize_text_field($_POST['generator']);
    $field = sanitize_text_field($_POST['field']);

    $data_types = load_data_types();
    $parameters_html = '';

    foreach ($data_types as $data_type) {
        foreach ($data_type['generators'] as $gen) {
            if ($gen['class'] == $generator) {
                foreach ($gen['parameters'] as $paramKey => $param) {
                    $input = '';
                    switch ($param['type']) {
                        case 'select':
                            $input = '<select name="parameters[' . $field . '][' . $paramKey . ']" class="' . $param['class'] . '">';
                            foreach ($param['options'] as $option) {
                                $input .= '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                            }
                            $input .= '</select>';
                            break;
                        case 'text':
                        case 'number':
                        case 'date':
                            $input = '<input type="' . $param['type'] . '" name="parameters[' . $field . '][' . $paramKey . ']" class="' . $param['class'] . '">';
                            break;
                    }
                    $parameters_html .= '<label>' . esc_html($param['label']) . ': </label>' . $input . '<br>';
                }
            }
        }
    }

    wp_send_json_success(array('parameters_html' => $parameters_html));
}
