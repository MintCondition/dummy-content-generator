<?php
// File: generators/unsplash-image-generator.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../includes/data-generator-interface.php';

class UnsplashImageGenerator implements DataGeneratorInterface {
    public static function getParameters() {
        return [
            'choice' => [
                'type' => 'select',
                'label' => 'Choice',
                'class' => 'image-choice',
                'instructions' => 'Select Random or Search-based image.',
                'options' => ['Random', 'Search']
            ],
            'size' => [
                'type' => 'select',
                'label' => 'Image Size',
                'class' => 'image-size',
                'instructions' => 'Select the size of the image.',
                'options' => ['Small (250x250)', 'Medium (800x600)', 'Large (1920x1080)', 'Custom']
            ],
            'custom_width' => [
                'type' => 'number',
                'label' => 'Custom Width',
                'class' => 'custom-width',
                'instructions' => 'Enter custom width if Custom size is selected.',
                'conditional' => ['size' => 'Custom']
            ],
            'custom_height' => [
                'type' => 'number',
                'label' => 'Custom Height',
                'class' => 'custom-height',
                'instructions' => 'Enter custom height if Custom size is selected.',
                'conditional' => ['size' => 'Custom']
            ],
            'search_term' => [
                'type' => 'text',
                'label' => 'Search Term',
                'class' => 'search-term',
                'instructions' => 'Enter search term if Search is selected.',
                'conditional' => ['choice' => 'Search']
            ],
        ];
    }

    public static function generate($params) {
        $choice = isset($params['choice']) ? $params['choice'] : 'Random';
        $size = isset($params['size']) ? $params['size'] : 'Medium';
        $custom_width = isset($params['custom_width']) ? intval($params['custom_width']) : 800;
        $custom_height = isset($params['custom_height']) ? intval($params['custom_height']) : 600;
        $search_term = isset($params['search_term']) ? urlencode($params['search_term']) : '';

        $size_mapping = [
            'Small (250x250)' => '250x250',
            'Medium (800x600)' => '800x600',
            'Large (1920x1080)' => '1920x1080'
        ];

        if ($size == 'Custom') {
            $size_str = "{$custom_width}x{$custom_height}";
        } else {
            $size_str = $size_mapping[$size];
        }

        if ($choice == 'Search' && !empty($search_term)) {
            $url = "https://source.unsplash.com/featured/?{$search_term}/{$size_str}";
        } else {
            $url = "https://source.unsplash.com/random/{$size_str}";
        }

        return $url;
    }
}
