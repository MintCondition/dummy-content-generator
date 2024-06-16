<?php
// File: generators/fake-email-generator.php
// Class: FakeEmailGenerator

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../includes/data-generator-interface.php';

class FakeEmailGenerator implements DataGeneratorInterface {
    public static function getParameters() {
        return [
            'gender' => [
                'type' => 'select',
                'label' => 'Gender',
                'class' => 'gender',
                'instructions' => 'Select the gender of the generated name.',
                'options' => ['Any', 'Male', 'Female']
            ],
            'nationality' => [
                'type' => 'select',
                'label' => 'Nationality',
                'class' => 'nationality',
                'instructions' => 'Select the nationality of the generated name.',
                'options' => ['Any', 'US', 'GB', 'FR', 'DE', 'ES', 'NL']
            ],
            'order' => [
                'type' => 'select',
                'label' => 'Name Order',
                'class' => 'order',
                'instructions' => 'Choose the order of the generated name.',
                'options' => ['First Last', 'Last, First']
            ]
        ];
    }

    public static function generate($params) {
        $gender = isset($params['gender']) && $params['gender'] !== 'Any' ? strtolower($params['gender']) : '';
        $nat = isset($params['nationality']) && $params['nationality'] !== 'Any' ? strtolower($params['nationality']) : '';
        $order = isset($params['order']) ? $params['order'] : 'First Last';

        $url = 'https://randomuser.me/api/?inc=name';
        if ($gender) {
            $url .= '&gender=' . $gender;
        }
        if ($nat) {
            $url .= '&nat=' . $nat;
        }

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            error_log("Failed to fetch name from Random User API: " . $response->get_error_message());
            return [
                'type' => 'text',
                'data_type' => 'plaintext',
                'content' => 'Failed to fetch name from Random User API: ' . $response->get_error_message()
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['results'])) {
            error_log("Empty response from Random User API");
            return [
                'type' => 'text',
                'data_type' => 'plaintext',
                'content' => 'Empty response from Random User API'
            ];
        }

        $name = $data['results'][0]['name'];
        $first_name = $name['first'];
        $last_name = $name['last'];

        // Define common email domains
        $domains = ['example.com', 'test.com', 'mailinator.com', 'fakemail.com', 'tempmail.com', 'mail.com', 'yopmail.com'];
        $random_domain = $domains[array_rand($domains)];

        // Create the email address based on the order
        if ($order === 'Last, First') {
            $email = strtolower($last_name) . '.' . strtolower($first_name) . '@' . $random_domain;
        } else {
            $email = strtolower($first_name) . '.' . strtolower($last_name) . '@' . $random_domain;
        }
        
        return [
            'type' => 'text',
            'data_type' => 'plaintext',
            'content' => $email
        ];
    }
}
