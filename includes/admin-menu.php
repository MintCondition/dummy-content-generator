<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dummy_Content_Admin_Menu {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Dummy Content', // Page title
            'Dummy Content', // Menu title
            'manage_options', // Capability
            'dummy-content', // Menu slug
            array($this, 'dummy_content_page'), // Callback function
            'dashicons-admin-generic', // Icon URL
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
