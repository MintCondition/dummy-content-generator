<?php
/*
Plugin Name: Stratifi Dummy Content Generator
Description: A plugin to create dummy content for WordPress sites in Development
Version: 0.0.3b  
Author: Brian Wood (Stratifi Creative)
Author URI: https://stratificreative.com
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/update-checker.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/create-dummy-content.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';

new GitHub_Updater(__FILE__, 'MintCondition', 'dummy-content-generator');
