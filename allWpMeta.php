<?php
/*
Plugin Name: all WP Meta
Plugin URI: https://gnnpls.com
Description: Add html input meta for admin and frontend using php
Version: 2
Author: Giannopoulos Nikolaos
Author URI: https://gnnpls.com
Text Domain:       all-wp-meta
GitHub Plugin URI: https://github.com/Motivar/filox-cloudflare
GitHub Branch:     master
 */

if (!defined('WPINC')) {
    die;
}



if (!class_exists('all_WP_Meta'))
{
define('awm_path', plugin_dir_path(__FILE__));
define('awm_url', plugin_dir_url(__FILE__));
require 'includes/class-all-WP-Meta.php';
}

