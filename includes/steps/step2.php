<?php
// File: includes/steps/step2.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
$num_posts = isset($_POST['num_posts']) ? intval($_POST['num_posts']) : 1;

// Retrieve the selected fields from the settings
$selected_fields = get_option("dummy_content_fields");
$fields_to_display = isset($selected_fields[$post_type]) ? $selected_fields[$post_type] : array();

?>

<div class="wrap">
    <h1><?php esc_html_e('Step 2: Select Fields', 'text-domain'); ?></h1>
    <p><?php echo sprintf(esc_html__('Creating %d %s', 'text-domain'), $num_posts, esc_html($post_type)); ?></p>

    <form id="step2-form" method="post" action="">
        <input type="hidden" name="step" value="3">
        <input type="hidden" name="post_type" value="<?php echo esc_attr($post_type); ?>">
        <input type="hidden" name="num_posts" value="<?php echo esc_attr($num_posts); ?>">

        <ul>
            <?php foreach ($fields_to_display as $field_type => $type_fields) : ?>
                <?php foreach ($type_fields as $field): ?>
                    <li>
                        <label>
                            <input type="checkbox" name="fields[]" value="<?php echo esc_attr($field); ?>">
                            <?php echo esc_html(get_meta_field_label($field, $field)); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e('Next', 'text-domain'); ?></button>
        </p>
    </form>
</div>
