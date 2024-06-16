<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'class-dummy-content-list-table.php';

class Manage_Dummy_Content_Page {
    public function display_page() {
        $screen = get_current_screen();

        // Get all public post types
        $post_types = get_post_types(['public' => true], 'objects');

        echo '<div class="wrap">';
        echo '<h1>Manage Dummy Content</h1>';
        echo '<p>Welcome to the Manage Dummy Content page! Here you can view and manage all the dummy content created by the plugin.</p>';

        // Include the JavaScript and CSS for the accordion
        $this->enqueue_accordion_scripts();

        foreach ($post_types as $post_type) {
            // Create a Dummy_Content_List_Table instance
            $list_table = new Dummy_Content_List_Table($post_type->name);
            $dummy_post_count = $list_table->get_dummy_content_count();

            // Only display section if there are dummy posts
            if ($dummy_post_count > 0) {
                // Accordion header
                echo '<button class="accordion">' . $post_type->labels->name . ' - ' . $dummy_post_count . ' Dummy Posts</button>';
                echo '<div class="panel">';
                $list_table->prepare_items();
                echo '<form method="post">';
                $list_table->search_box('search', 'search_id');
                $list_table->display();
                echo '</form>';
                echo '</div>';
            }
        }

        echo '</div>';
    }

    private function enqueue_accordion_scripts() {
        ?>
        <style>
            .accordion {
                background-color: #f9f9f9;
                color: #444;
                cursor: pointer;
                padding: 18px;
                width: 100%;
                border: none;
                text-align: left;
                outline: none;
                font-size: 15px;
                transition: 0.4s;
            }

            .active, .accordion:hover {
                background-color: #ccc;
            }

            .panel {
                padding: 0 18px;
                background-color: white;
                display: none;
                overflow: hidden;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var acc = document.getElementsByClassName('accordion');
                for (var i = 0; i < acc.length; i++) {
                    acc[i].addEventListener('click', function() {
                        this.classList.toggle('active');
                        var panel = this.nextElementSibling;
                        if (panel.style.display === 'block') {
                            panel.style.display = 'none';
                        } else {
                            panel.style.display = 'block';
                        }
                    });
                }
            });
        </script>
        <?php
    }
}
