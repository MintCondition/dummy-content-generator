<?php
// File: includes/admin-menu.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'utils.php';
require_once plugin_dir_path(__FILE__) . 'create-dummy-content.php';
require_once plugin_dir_path(__FILE__) . 'settings.php';
require_once plugin_dir_path(__FILE__) . 'testing.php';
require_once plugin_dir_path(__FILE__) . 'manage-dummy-content.php';

class Dummy_Content_Admin_Menu {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Dummy Content',
            'Dummy Content',
            'manage_options',
            'dummy-content',
            array($this, 'dummy_content_page'),
            'data:image/svg+xml;base64,' . base64_encode($this->get_svg_icon()),
            6
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

        add_submenu_page(
            'dummy-content',
            'Testing',
            'Testing',
            'manage_options',
            'dummy-content-testing',
            array($this, 'dummy_content_testing_page')
        );

        add_submenu_page(
            null,
            'Create Dummy Content',
            'Create Dummy Content',
            'manage_options',
            'create-dummy-content',
            array($this, 'create_dummy_content_page')
        );
    }

    private function get_svg_icon() {
        $svg_path = plugin_dir_path(__FILE__) . '../assets/images/dummy-content-icon.svg';
        if (file_exists($svg_path)) {
            return file_get_contents($svg_path);
        }
        return '';
    }

    public function enqueue_admin_scripts($hook_suffix) {
        if (strpos($hook_suffix, 'dummy-content') !== false) {
            wp_enqueue_style('dc-admin-style', DC_PLUGIN_URL . 'assets/css/admin-style.css');
            wp_enqueue_script('dc-admin-script', DC_PLUGIN_URL . 'js/dummy-content-admin.js', array('jquery', 'jquery-ui-accordion'), null, true);
            wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
            wp_localize_script('dc-admin-script', 'dummyContent', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('create_dummy_content'),
                'base_url' => DC_PLUGIN_URL,
                'data_types' => load_data_types()
            ));
        }
    }

    public function dummy_content_page() {
        echo '<h1>Dummy Content</h1>';
        echo '<p>Welcome to the Dummy Content Generator plugin!</p>';
        echo '<a href="' . admin_url('admin.php?page=create-dummy-content') . '" class="button button-primary">Create Dummy Content</a>';
    }

    public function create_dummy_content_page() {
        $page = new Create_Dummy_Content_Page();
        $page->display_page();
    }

    public function manage_dummy_content_page() {
        $manage_page = new Manage_Dummy_Content_Page();
        $manage_page->display_page();
    }

    public function dummy_content_settings_page() {
        $settings_page = new Dummy_Content_Settings_Page();
        $settings_page->display_page();
    }

    public function dummy_content_testing_page() {
        $testing_page = new Dummy_Content_Testing_Page();
        $testing_page->display_page();
    }
}

new Dummy_Content_Admin_Menu();
