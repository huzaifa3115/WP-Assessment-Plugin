<?php

/**
 * @package Akismet
 */
/*
Plugin Name: WP Assessment
Plugin URI: http://localhost/WP-Assessment
Description: Custom plugin.
Version: 0.0.1
Author: Muhammad Huzaifa
Author URI: https://codexloopers.com/
Text Domain: wp-assessment
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

//define('WP_ASSESSMENT_VERSION', '0.0.1');
define('WP_ASSESSMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_ASSESSMENT_ASSETS', plugins_url('/assets', __FILE__));
define('WP_ASSESSMENT_FRONT_IMAGES', plugins_url('/assets/images/front', __FILE__));

const WP_ASSESSMENT_PLUGIN_ADMIN_VIEW_DIR = WP_ASSESSMENT_PLUGIN_DIR . '/views/admin';
const WP_ASSESSMENT_PLUGIN_FRONT_VIEW_DIR = WP_ASSESSMENT_PLUGIN_DIR . '/views/front';
// define views
const MODERATOR_LIST_ADMIN_SELECT = WP_ASSESSMENT_PLUGIN_ADMIN_VIEW_DIR . '/moderator-list.php';
const MODERATOR_LIST_ADMIN_QUESTIONAIRE_FIELDS = WP_ASSESSMENT_PLUGIN_ADMIN_VIEW_DIR . '/questionaire.php';
const ADMIN_SUBMISSION_VIEW = WP_ASSESSMENT_PLUGIN_ADMIN_VIEW_DIR . '/submission-view.php';
const QUIZ_TEMPLATE_VIEW = WP_ASSESSMENT_PLUGIN_FRONT_VIEW_DIR . '/quiz.php';

require_once(WP_ASSESSMENT_PLUGIN_DIR . '/includes/custom-post-type.php');
require_once(WP_ASSESSMENT_PLUGIN_DIR . '/includes/custom-fields.php');
require_once(WP_ASSESSMENT_PLUGIN_DIR . '/includes/function.php');
require_once(WP_ASSESSMENT_PLUGIN_DIR . '/includes/question-form.php');
