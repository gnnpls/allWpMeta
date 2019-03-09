<?php
/*
Plugin Name: all WP Meta
Plugin URI: https://gnnpls.com
Description: Add html input meta for admin and frontend using php
Version: 1
Author: Giannopoulos Nikolaos
Author URI: https://gnnpls.com
Text Domain:       all-wp-meta
GitHub Plugin URI: https://github.com/Motivar/filox-cloudflare
GitHub Branch:     master
 */

if (!defined('WPINC')) {
    die;
}

define('awm_path', plugin_dir_path(__FILE__));
define('awm_url', plugin_dir_url(__FILE__));

add_action('plugins_loaded', 'all_wp_meta_load_textdomain');
function all_wp_meta_load_textdomain()
{
    load_plugin_textdomain('all-wp-meta', false, dirname(plugin_basename(__FILE__)).'/languages/');
}

require_once 'languages/strings.php';
require_once 'inc/main.php';

add_action('wp_loaded', function () {
    wp_register_style('awm-global-style', awm_url.'assets/css/global/awm-global-style.min.css', false, '1.0.0');
    wp_register_style('awm-admin-style', awm_url.'assets/css/admin/awm-admin-style.min.css', false, '1.0.0');
    wp_register_script('awm-global-script', awm_url.'assets/js/global/awm-global-script.js', array(), false, true);
    wp_localize_script('awm-global-script', 'awmGlobals', array('url' => esc_url(site_url())));
    wp_register_script('awm-admin-script', awm_url.'assets/js/admin/awm-admin-script.js', array(), false, true);
}, 10, 1);

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('awm-global-style');
    wp_enqueue_script('awm-global-script');
}, 10, 1);

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('awm-admin-style');
    wp_enqueue_style('awm-global-style');
    wp_enqueue_script('awm-global-script');
    wp_enqueue_script('awm-admin-script');
}, 10, 1);
