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

// Load the data types
$data_types_list = load_data_types();

// Function to generate content using the appropriate generator class
function generate_content($generator_class, $generator_file, $params) {
    $generator_file_path = plugin_dir_path(__FILE__) . '../../generators/' . $generator_file;

    if (file_exists($generator_file_path)) {
        require_once $generator_file_path;
    } else {
        return 'Content generation failed (file not found).';
    }

    if (class_exists($generator_class)) {
        return call_user_func(array($generator_class, 'generate'), $params);
    } else {
        return 'Content generation failed (class not found).';
    }
}

// Get the temporary directory path
$temp_dir = dcg_get_temp_directory();
if (!$temp_dir) {
    echo '<div class="notice notice-error"><p>Temporary directory could not be created or accessed.</p></div>';
    return;
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
        $generated_field = generate_content($generator_class, $generator_file, $field_parameters);
        
        if (!is_array($generated_field) || !isset($generated_field['type']) || !isset($generated_field['data_type']) || !isset($generated_field['content'])) {
            echo '<div class="notice notice-error"><p>Invalid Generator Response: '. $generated_field .'</p></div>';
            // error_log('Invalid Generator Response format: ' . print_r($generated_field, true));
            return;
        }

        $post_content[$field] = $generated_field;
    }
    
    $generated_content[] = [
        'meta_data' => [
            'generated_by' => wp_get_current_user()->user_login,
            'generated_at' => current_time('mysql'),
            'post_type' => $post_type
        ],
        'fields' => $post_content
    ];

    // Save JSON to temp directory
    $json_filename = $temp_dir . '/post-' . ($i + 1) . '.json';
    file_put_contents($json_filename, json_encode($generated_content[$i]));
}

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
            <?php 
                $json_filename = $temp_dir . '/post-' . ($i + 1) . '.json';
                $json_content = json_decode(file_get_contents($json_filename), true);
            ?>
            <h2>Post Number <?php echo $i + 1; ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Content</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($json_content['fields'] as $field => $field_data): ?>
                        <tr>
                            <td><?php echo esc_html($field); ?></td>
                            <td>
                                <?php
                                switch ($field_data['data_type']) {
                                    case 'plaintext':
                                    case 'html':
                                        echo wp_kses_post($field_data['content']);
                                        break;
                                    case 'image':
                                        if ($field_data['type'] === 'pointer') {
                                            echo '<img src="' . esc_url($field_data['content']['url']) . '" alt="Generated Image">';
                                        }
                                        break;
                                    default:
                                        echo esc_html(print_r($field_data['content'], true));
                                        break;
                                }
                                ?>
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
