<?php
// File: steps/step1.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Fetch all public post types
$post_types = get_post_types(array('public' => true), 'objects');

// Fetch custom data types
$data_types = load_data_types();

?>

<div class="wrap">
    <h1>Step 1: Select Post Type</h1>
    <form id="step1-form" method="post" action="">
        <input type="hidden" name="step" value="2">

        <table class="form-table">
            <tr>
                <th scope="row"><label for="post_type">Post Type</label></th>
                <td>
                    <select name="post_type" id="post_type" required>
                        <?php foreach ($post_types as $post_type): ?>
                            <option value="<?php echo esc_attr($post_type->name); ?>"><?php echo esc_html($post_type->label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="num_posts">Number of Posts</label></th>
                <td>
                    <input type="number" name="num_posts" id="num_posts" value="1" min="1" max="10" required>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">Next</button>
        </p>
    </form>
</div>
