<?php
// File: includes/steps/step4.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
$num_posts = isset($_POST['num_posts']) ? intval($_POST['num_posts']) : 1;
$fields = isset($_POST['fields']) ? explode(',', $_POST['fields']) : array();
$data_types = isset($_POST['data_types']) ? $_POST['data_types'] : array();
$generators = isset($_POST['generators']) ? $_POST['generators'] : array();
$parameters = isset($_POST['parameters']) ? $_POST['parameters'] : array();

// Log the data received in step 4
error_log("Step 4 - Post Type: $post_type");
error_log("Step 4 - Number of Posts: $num_posts");
error_log("Step 4 - Fields: " . implode(', ', $fields));
error_log("Step 4 - Data Types: " . print_r($data_types, true));
error_log("Step 4 - Generators: " . print_r($generators, true));
error_log("Step 4 - Parameters: " . print_r($parameters, true));

// Load the data types
$data_types_list = load_data_types();

// Function to generate content using the appropriate generator class
function generate_content($generator_class, $generator_file, $params) {
    $generator_file_path = plugin_dir_path(__FILE__) . '../../generators/' . $generator_file;

    if (file_exists($generator_file_path)) {
        require_once $generator_file_path;
    } else {
        error_log("Generator file not found: $generator_file_path");
        return 'Content generation failed (file not found).';
    }

    if (class_exists($generator_class)) {
        return call_user_func(array($generator_class, 'generate'), $params);
    } else {
        error_log("Generator class not found: $generator_class");
        return 'Content generation failed (class not found).';
    }
}

// Function to download an image and temporarily store it
function download_image($image_url) {
    $upload_dir = wp_upload_dir();
    $image_data = wp_remote_get($image_url);
    if (is_wp_error($image_data)) {
        return false;
    }

    $image_data = wp_remote_retrieve_body($image_data);
    $unique_key = uniqid();
    $filename = "unsplash-dc-$unique_key.jpeg";
    $file_path = $upload_dir['path'] . '/' . $filename;

    if (file_put_contents($file_path, $image_data)) {
        return array(
            'url' => $upload_dir['url'] . '/' . $filename,
            'file_path' => $file_path,
            'filename' => $filename
        );
    }

    return false;
}

// Initialize an array to store generated content
$generated_content = [];

// Generate content for each post
for ($i = 0; $i < $num_posts; $i++) {
    $post_content = [];
    foreach ($fields as $field) {
        $data_type = $data_types[$field];
        $generator_class = $generators[$field];
        $field_parameters = isset($parameters[$field]) ? $parameters[$field] : array();

        // Get the generator file from the data types list
        $generator_file = '';
        foreach ($data_types_list[$data_type]['generators'] as $generator) {
            if ($generator['class'] == $generator_class) {
                $generator_file = $generator['file'];
                break;
            }
        }

        // Generate the content
        $content = generate_content($generator_class, $generator_file, $field_parameters);

        // If the field is 'post_thumbnail', download and temporarily store the image
        if ($field == 'post_thumbnail') {
            $downloaded_image = download_image($content);
            if ($downloaded_image) {
                $content = $downloaded_image['url'];
                // Store the file path for later use in Step 5
                $post_content['_post_thumbnail_file_path'] = $downloaded_image['file_path'];
            }
        }

        $post_content[$field] = $content;
    }
    $generated_content[] = $post_content;
}

// Store the generated content in a transient
set_transient('dc_generated_content', $generated_content, 60 * 60); // 1 hour

?>

<div class="wrap">
    <h1>Step 4: Review Generated Content</h1>
    <p>This is an approval page. The next page will actually create the posts.</p>

    <form id="step4-form" method="post" action="">
        <input type="hidden" name="step" value="5">
        <input type="hidden" name="post_type" value="<?php echo esc_attr($post_type); ?>">
        <input type="hidden" name="num_posts" value="<?php echo esc_attr($num_posts); ?>">
        <input type="hidden" name="fields" value="<?php echo esc_attr(implode(',', $fields)); ?>">
        <input type="hidden" name="data_types" value="<?php echo esc_attr(json_encode($data_types)); ?>">
        <input type="hidden" name="generators" value="<?php echo esc_attr(json_encode($generators)); ?>">
        <input type="hidden" name="parameters" value="<?php echo esc_attr(json_encode($parameters)); ?>">

        <?php for ($i = 0; $i < $num_posts; $i++): ?>
            <h2>Post Number <?php echo $i + 1; ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Content</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fields as $field): ?>
                        <tr>
                            <td><?php echo esc_html($field); ?></td>
                            <td>
                                <?php if ($field == 'post_thumbnail' && isset($generated_content[$i][$field])): ?>
                                    <img src="<?php echo esc_url($generated_content[$i][$field]); ?>" alt="Generated Image" style="max-width: 100px; height: auto;">
                                <?php else: ?>
                                    <?php echo esc_html($generated_content[$i][$field]); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endfor; ?>

        <p class="submit">
            <button type="submit" class="button button-primary">Approve and Create</button>
        </p>
    </form>
</div>
