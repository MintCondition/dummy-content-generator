<?php
// File: includes/steps/step5.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
$num_posts = isset($_POST['num_posts']) ? intval($_POST['num_posts']) : 1;
$fields = isset($_POST['fields']) ? explode(',', $_POST['fields']) : array();
$data_types = isset($_POST['data_types']) ? json_decode(stripslashes($_POST['data_types']), true) : array();
$generators = isset($_POST['generators']) ? json_decode(stripslashes($_POST['generators']), true) : array();
$parameters = isset($_POST['parameters']) ? json_decode(stripslashes($_POST['parameters']), true) : array();

// Get the temporary directory path
$temp_dir = dcg_get_temp_directory();
if (!$temp_dir) {
    echo '<div class="notice notice-error"><p>Temporary directory could not be created or accessed.</p></div>';
    return;
}

// Read the JSON files from the temp directory
$generated_content = [];
for ($i = 0; $i < $num_posts; $i++) {
    $json_filename = $temp_dir . '/post-' . ($i + 1) . '.json';
    if (file_exists($json_filename)) {
        $generated_content[] = json_decode(file_get_contents($json_filename), true);
    } else {
        echo '<div class="notice notice-error"><p>JSON file not found: ' . esc_html($json_filename) . '</p></div>';
        return;
    }
}

// Create the posts
foreach ($generated_content as $post_data) {
    $post_args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
    );

    // Assign content to appropriate fields
    if (isset($post_data['fields']['post_title']['content'])) {
        $post_args['post_title'] = $post_data['fields']['post_title']['content'];
        unset($post_data['fields']['post_title']);
    }
    if (isset($post_data['fields']['post_content']['content'])) {
        $post_args['post_content'] = $post_data['fields']['post_content']['content'];
        unset($post_data['fields']['post_content']);
    }

    // Add the rest of the fields as meta fields
    $meta_input = [];
    foreach ($post_data['fields'] as $field => $field_data) {
        if ($field_data['type'] !== 'pointer') {
            $meta_input[$field] = $field_data['content'];
        }
    }
    $post_args['meta_input'] = $meta_input;

    // Insert the post
    $post_id = wp_insert_post($post_args);

    // Set featured image if available
    if (isset($post_data['fields']['post_thumbnail']['content']['file_path'])) {
        $file_path = $post_data['fields']['post_thumbnail']['content']['file_path'];
        $image_url = $post_data['fields']['post_thumbnail']['content']['url'];

        if ($file_path) {
            $upload_dir = wp_upload_dir();
            $attachment = array(
                'guid' => $upload_dir['url'] . '/' . basename($file_path),
                'post_mime_type' => 'image/jpeg',
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_path)),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $image_id = wp_insert_attachment($attachment, $file_path, $post_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($image_id, $file_path);
            wp_update_attachment_metadata($image_id, $attach_data);

            set_post_thumbnail($post_id, $image_id);
        }
    }
}
?>

<div class="wrap">
    <h1>Step 5: Posts Created</h1>
    <p>The posts have been successfully created.</p>
</div>
