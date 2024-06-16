<?php
// File: generators/unsplash-image-generator.php
// Class: UnsplashImageGenerator

// This class is deprecated and only included for historical purposes. Unsplash API v1 has been deprecated and replaced with the Unsplash API v2. The new API requires authentication and is not suitable for this plugin at this time. We will continue to monitor the situation and update the plugin if the new API becomes suitable.

// if (!defined('ABSPATH')) {
//     exit; // Exit if accessed directly
// }

// require_once plugin_dir_path(__FILE__) . '../includes/data-generator-interface.php';

// class UnsplashImageGenerator implements DataGeneratorInterface {
//     public static function getParameters() {
//         return [
//             'choice' => [
//                 'type' => 'select',
//                 'label' => 'Image Choice',
//                 'class' => 'image-choice',
//                 'instructions' => 'Select between random or search images.',
//                 'options' => ['Random', 'Search']
//             ],
//             'size' => [
//                 'type' => 'select',
//                 'label' => 'Image Size',
//                 'class' => 'image-size',
//                 'instructions' => 'Select the size of the image.',
//                 'options' => ['Small (250x250)', 'Medium (800x600)', 'Large (1920x1080)', 'Custom']
//             ],
//             'custom_width' => [
//                 'type' => 'number',
//                 'label' => 'Custom Width',
//                 'class' => 'custom-width',
//                 'instructions' => 'Specify custom width if Custom size is selected.'
//             ],
//             'custom_height' => [
//                 'type' => 'number',
//                 'label' => 'Custom Height',
//                 'class' => 'custom-height',
//                 'instructions' => 'Specify custom height if Custom size is selected.'
//             ],
//             'query' => [
//                 'type' => 'text',
//                 'label' => 'Search Query',
//                 'class' => 'search-query',
//                 'instructions' => 'Optional search term for images.'
//             ]
//         ];
//     }

//     public static function generate($params) {
//         $choice = isset($params['choice']) ? $params['choice'] : 'Random';
//         $size = isset($params['size']) ? $params['size'] : 'Medium (800x600)';
//         $customWidth = isset($params['custom_width']) ? $params['custom_width'] : null;
//         $customHeight = isset($params['custom_height']) ? $params['custom_height'] : null;
//         $query = isset($params['query']) ? $params['query'] : '';

//         $url = 'https://source.unsplash.com/';
//         $sizeMapping = [
//             'Small (250x250)' => '250x250',
//             'Medium (800x600)' => '800x600',
//             'Large (1920x1080)' => '1920x1080'
//         ];

//         if ($size === 'Custom' && $customWidth && $customHeight) {
//             $url .= "{$customWidth}x{$customHeight}";
//         } else {
//             $url .= $sizeMapping[$size];
//         }

//         if ($choice === 'Search' && !empty($query)) {
//             $url .= '/?' . urlencode($query);
//         } else {
//             $url .= '/random';
//         }

//         // Log the generated URL
//         error_log("Generated Unsplash URL: $url");

//         // Save the image to the media library
//         $image_data = self::fetch_and_save_image($url);

//         return $image_data;
//     }

//     private static function fetch_and_save_image($url) {
//         // Fetch the image
//         $response = wp_remote_get($url);

//         if (is_wp_error($response)) {
//             error_log("Failed to fetch image from Unsplash: " . $response->get_error_message());
//             return [
//                 'type' => 'pointer',
//                 'data_type' => 'image',
//                 'content' => [
//                     'file_path' => '',
//                     'url' => '',
//                     'error' => 'Failed to fetch image from Unsplash: ' . $response->get_error_message()
//                 ]
//             ];
//         }

//         $body = wp_remote_retrieve_body($response);

//         // Check if the response body is not empty
//         if (empty($body)) {
//             error_log("Empty response body from Unsplash");
//             return [
//                 'type' => 'pointer',
//                 'data_type' => 'image',
//                 'content' => [
//                     'file_path' => '',
//                     'url' => '',
//                     'error' => 'Empty response body from Unsplash'
//                 ]
//             ];
//         }

//         // Create a temporary file in the temp directory
//         $temp_dir = dcg_get_temp_directory();
//         $file_path = $temp_dir . '/unsplash-dc-' . uniqid() . '.jpg';

//         // Save the file
//         $result = file_put_contents($file_path, $body);
//         if ($result === false) {
//             error_log("Failed to save Unsplash image to: $file_path");
//             return [
//                 'type' => 'pointer',
//                 'data_type' => 'image',
//                 'content' => [
//                     'file_path' => '',
//                     'url' => '',
//                     'error' => 'Failed to save Unsplash image to: ' . $file_path
//                 ]
//             ];
//         }

//         // Log the file path
//         error_log("Saved Unsplash image to: $file_path");

//         // Return the file path and URL
//         return [
//             'type' => 'pointer',
//             'data_type' => 'image',
//             'content' => [
//                 'file_path' => str_replace(WP_CONTENT_DIR, '', $file_path),
//                 'url' => content_url(basename($temp_dir) . '/' . basename($file_path))
//             ]
//         ];
//     }
// }
