<?php
// File: generators/picsum-image-generator.php
// Class: PicsumImageGenerator

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../includes/data-generator-interface.php';

class PicsumImageGenerator implements DataGeneratorInterface {
    public static function getParameters() {
        return [
            'choice' => [
                'type' => 'select',
                'label' => 'Image Choice',
                'class' => 'image-choice',
                'instructions' => 'Select between random or specific images.',
                'options' => ['Random', 'Grayscale', 'Blur', 'Seed']
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
                'instructions' => 'Specify custom width if Custom size is selected.'
            ],
            'custom_height' => [
                'type' => 'number',
                'label' => 'Custom Height',
                'class' => 'custom-height',
                'instructions' => 'Specify custom height if Custom size is selected.'
            ],
            'seed' => [
                'type' => 'text',
                'label' => 'Seed',
                'class' => 'seed',
                'instructions' => 'Optional seed for specific images.'
            ],
            'blur' => [
                'type' => 'number',
                'label' => 'Blur Level',
                'class' => 'blur',
                'instructions' => 'Optional blur level (1-10).'
            ],
            'grayscale' => [
                'type' => 'select',
                'label' => 'Grayscale',
                'class' => 'grayscale',
                'instructions' => 'Apply grayscale filter.',
                'options' => ['No', 'Yes']
            ]
        ];
    }

    public static function generate($params) {
        $choice = isset($params['choice']) ? $params['choice'] : 'Random';
        $size = isset($params['size']) ? $params['size'] : 'Medium (800x600)';
        $customWidth = isset($params['custom_width']) ? $params['custom_width'] : null;
        $customHeight = isset($params['custom_height']) ? $params['custom_height'] : null;
        $seed = isset($params['seed']) ? $params['seed'] : null;
        $blur = isset($params['blur']) ? $params['blur'] : null;
        $grayscale = isset($params['grayscale']) && $params['grayscale'] === 'Yes' ? true : false;

        $url = 'https://picsum.photos/';
        $sizeMapping = [
            'Small (250x250)' => '250/250',
            'Medium (800x600)' => '800/600',
            'Large (1920x1080)' => '1920/1080'
        ];

        if ($size === 'Custom' && $customWidth && $customHeight) {
            $url .= "{$customWidth}/{$customHeight}";
        } else {
            $url .= $sizeMapping[$size];
        }

        if ($choice === 'Seed' && $seed) {
            $url .= '?seed=' . urlencode($seed);
        }

        if ($grayscale) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'grayscale';
        }

        if ($blur) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'blur=' . $blur;
        }

        // Log the generated URL
        error_log("Generated Picsum URL: $url");

        // Save the image to the temp directory
        $image_data = self::fetch_and_save_image($url);

        return $image_data;
    }

    private static function fetch_and_save_image($url) {
        // Fetch the image
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            error_log("Failed to fetch image from Picsum: " . $response->get_error_message());
            return [
                'type' => 'pointer',
                'data_type' => 'image',
                'content' => [
                    'file_path' => '',
                    'url' => '',
                    'error' => 'Failed to fetch image from Picsum: ' . $response->get_error_message()
                ]
            ];
        }

        $body = wp_remote_retrieve_body($response);

        // Check if the response body is not empty
        if (empty($body)) {
            error_log("Empty response body from Picsum");
            return [
                'type' => 'pointer',
                'data_type' => 'image',
                'content' => [
                    'file_path' => '',
                    'url' => '',
                    'error' => 'Empty response body from Picsum'
                ]
            ];
        }

        // Create a temporary file in the temp directory
        $temp_dir = dcg_get_temp_directory();
        $file_path = $temp_dir . '/picsum-dc-' . uniqid() . '.jpg';

        // Save the file
        $result = file_put_contents($file_path, $body);
        if ($result === false) {
            error_log("Failed to save Picsum image to: $file_path");
            return [
                'type' => 'pointer',
                'data_type' => 'image',
                'content' => [
                    'file_path' => '',
                    'url' => '',
                    'error' => 'Failed to save Picsum image to: ' . $file_path
                ]
            ];
        }

        // Log the file path
        error_log("Saved Picsum image to: $file_path");

        // Return the file path and URL
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['basedir'], '', $file_path);
        return [
            'type' => 'pointer',
            'data_type' => 'image',
            'content' => [
                'file_path' => $relative_path,
                'url' => $upload_dir['baseurl'] . $relative_path
            ]
        ];
    }
}
