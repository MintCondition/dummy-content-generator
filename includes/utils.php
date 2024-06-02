<?php
// File: includes/utils.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function load_data_types() {
    $data_types = [];
    $data_types_dir = plugin_dir_path(__FILE__) . '../data-types';

    if (is_dir($data_types_dir)) {
        $files = glob($data_types_dir . '/*.json');
        foreach ($files as $file) {
            $json = file_get_contents($file);
            $data_type = json_decode($json, true);
            if ($data_type) {
                $data_types[basename($file, '.json')] = $data_type;
            }
        }
    }

    return $data_types;
}
