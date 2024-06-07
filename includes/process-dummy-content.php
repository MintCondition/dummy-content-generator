<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function handle_create_dummy_content() {
    if (!isset($_POST['create_dummy_content_nonce']) || !wp_verify_nonce($_POST['create_dummy_content_nonce'], 'create_dummy_content_nonce')) {
        wp_die('Invalid nonce');
    }

    $post_type = sanitize_text_field($_POST['dummy_content_post_type']);
    $post_count = intval($_POST['dummy_content_post_count']);

    if (!$post_type || $post_count < 1 || $post_count > 20) {
        wp_die('Invalid input');
    }

    // Store initial data in transient
    $transient_key = 'dummy_content_' . get_current_user_id();
    set_transient($transient_key, [
        'post_type' => $post_type,
        'post_count' => $post_count,
        'fields' => []
    ], 30 * MINUTE_IN_SECONDS);

    // Redirect to step 2
    wp_redirect(add_query_arg(['page' => 'create-dummy-content', 'step' => 2], admin_url('admin.php')));
    exit;
}
add_action('admin_post_create_dummy_content', 'handle_create_dummy_content');
?>
