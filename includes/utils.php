<?php
// File: includes/utils.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function load_data_types() {
    $data_types = [];
    $data_types_dir = plugin_dir_path(__FILE__) . '../data-types';

    if (is_dir($data_types_dir)) {
        $files = glob($data_types_dir . '/*.json');
        foreach ($files as $file) {
            $json = file_get_contents($file);
            $data_type = json_decode($json, true);
            if ($data_type) {
                $data_types[basename($file, '.json')] = $data_type;
            }
        }
    }

    return $data_types;
}

function get_dynamic_post_fields($post_type) {
    $fields = array(
        'standard' => array(),
        'meta' => array(),
        'acf' => array(),
    );

    // Core fields that every post must have
    $core_fields = array(
        'post_title' => __('Title', 'text-domain'),
        'post_content' => __('Content', 'text-domain'),
        'post_excerpt' => __('Excerpt', 'text-domain'),
        'post_author' => __('Author', 'text-domain'),
        'post_date' => __('Date', 'text-domain'),
        'post_status' => __('Status', 'text-domain'),
        'post_thumbnail' => __('Featured Image', 'text-domain'),
    );

    // Include core fields in the list
    $fields['standard'] = $core_fields;

    // Get custom fields for the post type
    $custom_fields = get_post_meta_fields($post_type);
    foreach ($custom_fields as $meta_key => $meta_label) {
        $fields['meta'][$meta_key] = get_meta_field_label($meta_key, $meta_label);
    }

    // Get ACF fields for the post type
    if (function_exists('acf_get_field_groups') && function_exists('acf_get_fields')) {
        $field_groups = acf_get_field_groups(array('post_type' => $post_type));
        foreach ($field_groups as $group) {
            $acf_fields = acf_get_fields($group['ID']);
            foreach ($acf_fields as $acf_field) {
                $fields['acf'][$acf_field['name']] = $acf_field['label'];
            }
        }
    }

    return $fields;
}

/**
 * Function to get meta fields for a post type
 */
function get_post_meta_fields($post_type) {
    global $wpdb;
    $meta_keys = $wpdb->get_col($wpdb->prepare(
        "
        SELECT DISTINCT meta_key 
        FROM {$wpdb->postmeta}
        WHERE post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)
        ", 
        $post_type
    ));

    $fields = array();
    foreach ($meta_keys as $meta_key) {
        $fields[$meta_key] = $meta_key; // Initially set the meta_key as the label
    }

    return $fields;
}

/**
 * Function to generate a user-friendly label for a meta field
 */
function get_meta_field_label($meta_key, $default_label) {
    // Check if the meta field has a registered description or label
    $meta = get_registered_meta_key($meta_key);
    if ($meta && isset($meta['description'])) {
        return $meta['description'];
    }

    // If no label is found, create a more readable label from the meta key
    return ucwords(str_replace('_', ' ', $default_label));
}

/**
 * Function to get registered meta key information (if available)
 */
function get_registered_meta_key($meta_key) {
    global $wp_meta_keys;

    foreach ($wp_meta_keys as $object_type => $type_keys) {
        if (isset($type_keys['post'][$meta_key])) {
            return $type_keys['post'][$meta_key];
        }
    }

    return null;
}

/**
 * Function to get ACF fields for a post type
 */
function get_acf_fields($post_type) {
    $acf_fields = array();

    // Get ACF field groups associated with the post type
    $field_groups = acf_get_field_groups(array('post_type' => $post_type));

    // Iterate over each field group and get the fields
    foreach ($field_groups as $group) {
        $fields = acf_get_fields($group['key']);
        if ($fields) {
            foreach ($fields as $field) {
                $acf_fields[$field['name']] = $field['label'];
            }
        }
    }

    return $acf_fields;
}

function dcg_create_temp_dir($dir_name) {
    $upload_dir = wp_upload_dir();
    $dir_path = $upload_dir['basedir'] . '/' . $dir_name;
    if (!file_exists($dir_path)) {
        wp_mkdir_p($dir_path);
    }
}

function dcg_delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!dcg_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

function dcg_get_temp_dir_status() {
    $temp_dir = get_option('dummy_content_temp_dir', 'dummy-content-temp');
    $upload_dir = wp_upload_dir();
    $temp_dir_path = $upload_dir['basedir'] . '/' . $temp_dir;

    $status = array(
        'path' => $temp_dir_path,
        'file_count' => 0,
        'total_size' => 0,
        'is_writable' => is_writable($temp_dir_path),
    );

    if (is_dir($temp_dir_path)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp_dir_path, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            $status['file_count']++;
            $status['total_size'] += $file->getSize();
        }
    }

    return $status;
}


function dcg_get_temp_directory() {
    $temp_dir_name = get_option('dummy_content_temp_dir', 'dummy-content-temp'); // Get the temp directory name from settings
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/' . $temp_dir_name;

    if (!file_exists($temp_dir)) {
        wp_mkdir_p($temp_dir);
    }

    return $temp_dir;
}

