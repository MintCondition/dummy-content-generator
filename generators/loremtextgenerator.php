<?php
// File: generators/lorem-text-generator.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Ensure the DataGeneratorInterface is included
require_once plugin_dir_path(__FILE__) . '../includes/data-generator-interface.php';

class LoremTextGenerator implements DataGeneratorInterface {
    public static function getParameters() {
        return [
            'length' => [
                'type' => 'select',
                'label' => 'Text Length',
                'class' => 'text-length',
                'instructions' => 'Select the length of the text.',
                'options' => ['short', 'medium', 'long']
            ],
            'prefix' => [
                'type' => 'text',
                'label' => 'Prefix',
                'class' => 'text-prefix',
                'instructions' => 'Optional text to add at the beginning.'
            ],
            'suffix' => [
                'type' => 'text',
                'label' => 'Suffix',
                'class' => 'text-suffix',
                'instructions' => 'Optional text to add at the end.'
            ],
            'paragraph_count' => [
                'type' => 'number',
                'label' => 'Paragraph Count',
                'class' => 'paragraph-count',
                'instructions' => 'Number of paragraphs to generate.'
            ]
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
