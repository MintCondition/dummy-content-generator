<?php
// File: generators/html-generator.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
require_once plugin_dir_path(__FILE__) . '../includes/data-generator-interface.php'; // Include the DataGeneratorInterface

class HtmlGenerator implements DataGeneratorInterface {
    public static function getParameters() {
        return [
            'length' => [
                'type' => 'select',
                'label' => 'HTML Length',
                'class' => 'html-length',
                'instructions' => 'Select the length of the HTML snippet.',
                'options' => ['short', 'medium', 'long']
            ],
            'tags' => [
                'type' => 'checkbox',
                'label' => 'HTML Tags',
                'class' => 'html-tags',
                'instructions' => 'Select the HTML tags to include.',
                'options' => ['<p>', '<div>', '<span>', '<a>', '<ul>', '<ol>', '<li>']
            ],
            'custom_css' => [
                'type' => 'textarea',
                'label' => 'Custom CSS',
                'class' => 'custom-css',
                'instructions' => 'Optional custom CSS to include.',
                'placeholder' => 'Enter custom CSS here...'
            ]
        ];
    }

    public static function generate($params) {
        $length = isset($params['length']) ? $params['length'] : 'medium';
        $tags = isset($params['tags']) ? $params['tags'] : [];
        $customCss = isset($params['custom_css']) ? $params['custom_css'] : '';

        $htmlContent = self::generateHtmlContent($length, $tags);
        if ($customCss) {
            $htmlContent .= "<style>{$customCss}</style>";
        }

        return $htmlContent;
    }

    private static function generateHtmlContent($length, $tags) {
        $content = '';

        // Define some example content based on the length parameter
        switch ($length) {
            case 'short':
                $content = 'This is a short HTML snippet.';
                break;
            case 'medium':
                $content = 'This is a medium HTML snippet with more content and some formatting.';
                break;
            case 'long':
                $content = 'This is a long HTML snippet. It contains a lot of content and multiple HTML elements to showcase different tags and formatting options.';
                break;
        }

        // Wrap the content with the selected tags
        foreach ($tags as $tag) {
            $content = htmlspecialchars($tag) . "{$content}" . htmlspecialchars(str_replace('<', '</', $tag));
        }

        return $content;
    }
}
