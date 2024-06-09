<?php
// File: generators/lorem-text-generator.php
// Class: LoremTextGenerator

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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
            ],
            'min_words' => [
                'type' => 'number',
                'label' => 'Minimum Words',
                'class' => 'min-words',
                'instructions' => 'Minimum number of words.'
            ],
            'max_words' => [
                'type' => 'number',
                'label' => 'Maximum Words',
                'class' => 'max-words',
                'instructions' => 'Maximum number of words.'
            ],
            'format' => [
                'type' => 'select',
                'label' => 'Format',
                'class' => 'text-format',
                'instructions' => 'Select the format of the text.',
                'options' => ['plaintext', 'html']
            ]
        ];
    }

    public static function generate($params) {
        $prefix = isset($params['prefix']) ? $params['prefix'] : '';
        $suffix = isset($params['suffix']) ? $params['suffix'] : '';
        $paragraphCount = isset($params['paragraph_count']) ? (int)$params['paragraph_count'] : null;
        $minWords = isset($params['min_words']) ? (int)$params['min_words'] : null;
        $maxWords = isset($params['max_words']) ? (int)$params['max_words'] : null;
        $format = isset($params['format']) ? $params['format'] : 'html';
        $length = isset($params['length']) ? $params['length'] : null;

        $content = '';
        $type = 'inline';
        $data_type = $format;

        // Determine which method to use based on provided parameters
        if ($minWords && $maxWords && !$length && !$paragraphCount) {
            $content = self::fetchLoremTextWords($minWords, $maxWords, $format);
        } else {
            $paragraphCount = $paragraphCount ?? 1;
            $length = $length ?? 'medium';
            $content = self::fetchLoremText($paragraphCount, $length, $format);
        }

        return [
            'type' => $type,
            'data_type' => $data_type,
            'content' => $prefix . $content . $suffix
        ];
    }

    private static function fetchLoremText($paragraphCount, $length, $format) {
        $formatParameter = $format === 'plaintext' ? 'plaintext' : '';
        $url = "https://loripsum.net/api/{$paragraphCount}/{$formatParameter}/{$length}";

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        }

        $text = wp_remote_retrieve_body($response);
        return $format === 'plaintext' ? strip_tags($text) : $text;
    }

    private static function fetchLoremTextWords($minWords, $maxWords, $format) {
        $wordCount = rand($minWords, $maxWords);
        $formatParameter = $format === 'plaintext' ? 'plaintext' : '';
        $url = "https://loripsum.net/api/1/{$formatParameter}/short";

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return 'Lorem ipsum dolor sit amet.';
        }

        $text = wp_remote_retrieve_body($response);

        // Remove the initial "Lorem ipsum dolor sit amet, consectetur adipiscing elit." phrase
        $text = preg_replace('/^Lorem ipsum dolor sit amet, consectetur adipiscing elit\.\s*/', '', strip_tags($text));

        return self::truncateText($text, $wordCount);
    }

    private static function truncateText($text, $wordCount) {
        $words = explode(' ', $text);
        if (count($words) > $wordCount) {
            $words = array_slice($words, 0, $wordCount);
        }
        return implode(' ', $words);
    }
}
