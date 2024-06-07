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

// Log the data received in step 5
error_log("Step 5 - Post Type: $post_type");
error_log("Step 5 - Number of Posts: $num_posts");
error_log("Step 5 - Fields: " . implode(', ', $fields));
error_log("Step 5 - Data Types: " . print_r($data_types, true));
error_log("Step 5 - Generators: " . print_r($generators, true));
error_log("Step 5 - Parameters: " . print_r($parameters, true));

// Load the data types
$data_types_list = load_data_types();

// Get the generated content from the transient
$generated_content = get_transient('dc_generated_content');

error_log("Step 5 - Generated Content: " . print_r($generated_content, true));

// Function to upload an image from a file to the media library
function upload_image_from_file($file_path) {
    $filetype = wp_check_filetype(basename($file_path), null);
    $wp_upload_dir = wp_upload_dir();

    $attachment = array(
        'guid'           => $wp_upload_dir['url'] . '/' . basename($file_path),
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name(basename($file_path)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    $attachment_id = wp_insert_attachment($attachment, $file_path);

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attach_data);

    return $attachment_id;
}

// Create the posts
foreach ($generated_content as $post_data) {
    $post_args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
    );

    // Assign content to appropriate fields
    if (isset($post_data['post_title'])) {
        $post_args['post_title'] = $post_data['post_title'];
        unset($post_data['post_title']);
    }
    if (isset($post_data['post_content'])) {
        $post_args['post_content'] = $post_data['post_content'];
        unset($post_data['post_content']);
    }

    // Add the rest of the fields as meta fields
    $post_args['meta_input'] = $post_data;

    // Log the post data before insertion
    error_log("Step 5 - Post Data: " . print_r($post_args, true));

    // Insert the post
    $post_id = wp_insert_post($post_args);

    // Log the created post ID
    error_log("Step 5 - Post created with ID: $post_id");

    // Check if there is a post thumbnail to upload and set
    if ($post_id && isset($post_data['_post_thumbnail_file_path'])) {
        $file_path = $post_data['_post_thumbnail_file_path'];
        $attachment_id = upload_image_from_file($file_path);
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
            error_log("Step 5 - Featured image set for post ID: $post_id with attachment ID: $attachment_id");
        } else {
            error_log("Step 5 - Error uploading image: " . $attachment_id->get_error_message());
            error_log("Step 5 - Failed to set featured image for post ID: $post_id");
        }
    }
}
?>

<div class="wrap">
    <h1>Step 5: Posts Created</h1>
    <p>The posts have been successfully created.</p>
</div>
