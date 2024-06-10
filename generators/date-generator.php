<?php
// File: generators/date-generator.php
// Class: DateGenerator

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../includes/data-generator-interface.php';

class DateGenerator implements DataGeneratorInterface {
    public static function getParameters() {
        return [
            'start_date' => [
                'type' => 'date',
                'label' => 'Start Date',
                'instructions' => 'Select the start date of the range.',
            ],
            'end_date' => [
                'type' => 'date',
                'label' => 'End Date',
                'instructions' => 'Select the end date of the range.',
            ],
        ];
    }

    public static function generate($params) {
        $startDate = isset($params['start_date']) ? strtotime($params['start_date']) : strtotime('-3 months');
        $endDate = isset($params['end_date']) ? strtotime($params['end_date']) : time();

        if ($startDate > $endDate) {
            throw new InvalidArgumentException('Start date must be before end date.');
        }

        $randomTimestamp = rand($startDate, $endDate);

        return [
            'type' => 'inline',
            'data_type' => 'datetime',
            'content' => date('Y-m-d H:i:s', $randomTimestamp)
        ];
    }
}
