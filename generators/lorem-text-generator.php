<?php
// File: generators/lorem-text-generator.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class LoremTextGenerator implements DataGeneratorInterface {
    public static function getParameters() {
        return [
            'length' => ['short', 'medium', 'long'],
            'prefix' => '',
            'suffix' => '',
            'paragraph_count' => 1 // Default to 1 paragraph if not specified
        ];
    }

    public static function generate($params) {
        $length = isset($params['length']) ? $params['length'] : 'medium';
        $prefix = isset($params['prefix']) ? $params['prefix'] : '';
        $suffix = isset($params['suffix']) ? $params['suffix'] : '';
        $paragraphCount = isset($params['paragraph_count']) ? (int)$params['paragraph_count'] : 1;

        // Fetch Lorem Ipsum text from the API
        $loremText = self::fetchLoremText($paragraphCount, $length);

        return $prefix . $loremText . $suffix;
    }

    private static function fetchLoremText($paragraphCount, $length) {
        $url = "https://loripsum.net/api/{$paragraphCount}/short,medium,long/{$length}";

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        }

        return wp_remote_retrieve_body($response);
    }
}
