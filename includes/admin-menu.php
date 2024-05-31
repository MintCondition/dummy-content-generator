<?php
// File: includes/admin-menu.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dummy_Content_Admin_Menu {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Dummy Content', // Page title
            'Dummy Content', // Menu title
            'manage_options', // Capability
            'dummy-content', // Menu slug
            array($this, 'dummy_content_page'), // Callback function
            'data:image/svg+xml;base64,' . base64_encode($this->get_svg_icon()), // Icon URL
            6 // Position
        );

        add_submenu_page(
            'dummy-content',
            'Create Dummy Content',
            'Create Dummy Content',
            'manage_options',
            'create-dummy-content',
            array($this, 'create_dummy_content_page')
        );

        add_submenu_page(
            'dummy-content',
            'Manage Dummy Content',
            'Manage Dummy Content',
            'manage_options',
            'manage-dummy-content',
            array($this, 'manage_dummy_content_page')
        );

        add_submenu_page(
            'dummy-content',
            'Settings',
            'Settings',
            'manage_options',
            'dummy-content-settings',
            array($this, 'dummy_content_settings_page')
        );
    }

    private function get_svg_icon() {
        $svg_path = plugin_dir_path(__FILE__) . '../assets/images/dummy-content-icon.svg';
        if (file_exists($svg_path)) {
            return file_get_contents($svg_path);
        }
        return '';
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style('dummy-content-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
    }

    public function dummy_content_page() {
        echo '<h1>Dummy Content</h1>';
        echo '<p>Welcome to the Dummy Content Generator plugin!</p>';
    }

    public function create_dummy_content_page() {
        echo '<h1>Create Dummy Content</h1>';
        echo '<p>Here you can create dummy content.</p>';
    }

    public function manage_dummy_content_page() {
        echo '<h1>Manage Dummy Content</h1>';
        echo '<p>Here you can manage dummy content.</p>';
    }

    public function dummy_content_settings_page() {
        echo '<h1>Settings</h1>';
        echo '<p>Here you can adjust the settings for the Dummy Content Generator plugin.</p>';
    }
}

new Dummy_Content_Admin_Menu();
