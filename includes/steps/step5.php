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
$created_posts = [];
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
    if (isset($post_data['fields']['post_date']['content'])) {
        $post_args['post_date'] = $post_data['fields']['post_date']['content'];
        unset($post_data['fields']['post_date']);
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

    // Save created post info
    if ($post_id) {
        $created_posts[] = array(
            'ID' => $post_id,
            'title' => get_the_title($post_id),
            'date' => get_the_date('', $post_id),
            'link' => get_permalink($post_id)
        );

        // Add additional meta data
        update_post_meta($post_id, 'dcg_create', true);
        update_post_meta($post_id, 'dcg_create_date', current_time('mysql'));
        update_post_meta($post_id, 'dcg_create_user', get_current_user_id());
    }

    // Set featured image if available
    if (isset($post_data['fields']['post_thumbnail']['content']['file_path'])) {
        $file_path = $post_data['fields']['post_thumbnail']['content']['file_path'];

        if ($file_path) {
            // Convert the relative path to an absolute path
            $absolute_path = $temp_dir . '/' . basename($file_path);

            // Simulate an upload of the file to the WordPress media library
            $file_array = array(
                'name' => basename($file_path),
                'tmp_name' => $absolute_path,
            );

            // Check for errors and handle the upload
            $upload = wp_handle_sideload($file_array, array('test_form' => false));

            if (!isset($upload['error']) && isset($upload['file'])) {
                $filetype = wp_check_filetype(basename($upload['file']), null);
                $attachment = array(
                    'guid' => $upload['url'],
                    'post_mime_type' => $filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $image_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($image_id, $upload['file']);
                wp_update_attachment_metadata($image_id, $attach_data);

                set_post_thumbnail($post_id, $image_id);
            }
        }
    }
}

$post_type_obj = get_post_type_object($post_type);

// Check if cleanup is enabled
$cleanup = get_option('dummy_content_cleanup', true);
$json_files_removed = 0;
$other_files_removed = 0;

if ($cleanup) {
    // Remove only the JSON files and other temp files generated during this run
    foreach ($generated_content as $index => $post_data) {
        $json_filename = $temp_dir . '/post-' . ($index + 1) . '.json';
        if (file_exists($json_filename)) {
            if (unlink($json_filename)) {
                $json_files_removed++;
            }
        }
    }

    // Optionally remove other temp files here, for now assuming other files are not generated
}
?>

<div class="wrap">
    <h1>Step 5: Posts Created</h1>
    <p><?php echo $num_posts . ' ' . esc_html($post_type_obj->labels->name) . ' created successfully.'; ?></p>
    <ul>
        <?php foreach ($created_posts as $index => $post): ?>
            <li><?php echo ($index + 1) . ' - ' . esc_html($post['title']) . ' - ' . esc_html($post['date']) . ' - <a href="' . esc_url($post['link']) . '" target="_blank">View</a>'; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($cleanup): ?>
        <p><?php echo $json_files_removed . ' JSON files were removed from the temp directory.'; ?></p>
    <?php else: ?>
        <p><?php echo 'No cleanup was performed. Left ' . $json_files_removed . ' JSON files in the temp directory ' . esc_html($temp_dir) . '. Consider cleaning these manually or in the settings page.'; ?></p>
    <?php endif; ?>
    <p>
        <a href="<?php echo esc_url(get_post_type_archive_link($post_type)); ?>" class="button" target="_blank">View Archive</a>
        <a href="<?php echo esc_url(admin_url('edit.php?post_type=' . $post_type)); ?>" class="button">View Post List</a>
    </p>
</div>
