<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'class-dummy-content-list-table.php';

class Manage_Dummy_Content_Page {
    public function display_page() {
        $screen = get_current_screen();
        error_log('Current screen ID: ' . $screen->id);

        error_log('Displaying Manage Dummy Content Page...');
        $list_table = new Dummy_Content_List_Table();
        $list_table->prepare_items();
        
        echo '<div class="wrap">';
        echo '<h1>Manage Dummy Content</h1>';
        echo '<p>Welcome to the Manage Dummy Content page! Here you can view and manage all the dummy content created by the plugin.</p>';
        
        echo '<form method="post">';
        $list_table->search_box('search', 'search_id');
        // Remove custom wp_nonce_field call
        $list_table->display();
        echo '</form>';
        
        echo '</div>';
    }
}
