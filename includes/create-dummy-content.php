<?php
// File: includes/create-dummy-content.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Create_Dummy_Content_Page {
    public function display_page() {
        $step = isset($_POST['step']) ? intval($_POST['step']) : 1;
        error_log("Current Step: $step");
        // error_log(print_r($_POST, true));

        if ($step == 2) {
            error_log("Loading Step 2");
            require_once plugin_dir_path(__FILE__) . 'steps/step2.php';
        } elseif ($step == 3) {
            error_log("Loading Step 3");
            require_once plugin_dir_path(__FILE__) . 'steps/step3.php';
        } elseif ($step == 4) {
            error_log("Loading Step 4");
            require_once plugin_dir_path(__FILE__) . 'steps/step4.php';
        } elseif ($step == 5) {
            error_log("Loading Step 5");
            require_once plugin_dir_path(__FILE__) . 'steps/step5.php';
        } else {
            error_log("Loading Step 1");
            require_once plugin_dir_path(__FILE__) . 'steps/step1.php';
        }
    }
}
