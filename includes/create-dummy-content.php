<?php
// File: admin/create-dummy-content.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Create_Dummy_Content_Page {
    public function display_page() {
        ?>
        <div class="wrap">
            <!-- Working Indicator -->
            <div id="working-indicator" style="display: none;">
                <div class="dc-overlay"></div>    
                <div class="dc-center-box"><p>Working...</p></div>
            </div>    
            <h1>Create Dummy Content</h1>
            <div id="step-1">
                <h2>Step 1: Select Post Type</h2>
                <select id="dummy_content_post_type">
                    <option value="">--Select Post Type--</option>
                    <?php
                    $post_types = get_post_types(array('public' => true), 'objects');
                    foreach ($post_types as $post_type) {
                        echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                    }
                    ?>
                </select>
                <h2>Select Number of Posts</h2>
                <input type="number" id="dummy_content_post_count" min="1" max="20" value="1">
                <button id="next-step" class="button button-primary">Next</button>
            </div>
            <div id="step-2" style="display:none;">
                <h2>Step 2: Configure Fields</h2>
                <table id="dummy-content-fields-table" class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="column-primary">Field</th>
                            <th>Data Type & Generator</th>
                            <th>Parameters</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically here -->
                    </tbody>
                </table>
                <button id="final-step" class="button button-primary">Next</button>
            </div>
            <div id="step-3" style="display:none;">
                <h2>Step 3: Review and Generate</h2>
                <div id="post-review-content"></div>
                <button id="generate-dummy-content" class="button button-primary">Create Dummy Content</button>
                <button id="cancel-generation" class="button">Cancel</button>
            </div>
        </div>
        <script>
            var dummyContent = {
                ajax_url: "<?php echo admin_url('admin-ajax.php'); ?>",
                nonce: "<?php echo wp_create_nonce('create_dummy_content'); ?>",
                data_types: <?php echo json_encode(load_data_types()); ?>,
                plugin_url: "<?php echo plugin_dir_url(__FILE__) . '../'; ?>"
            };
        </script>
        <script src="<?php echo plugin_dir_url(__FILE__) . '../js/dummy-content-admin.js'; ?>"></script>
        <?php
    }
}
?>
