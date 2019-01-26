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

add_action('plugins_loaded', 'all_wp_meta');
function filox_advanced_search_plugin_load_textdomain()
{
    load_plugin_textdomain('all-wp-meta', false, dirname(plugin_basename(__FILE__)).'/languages/');
}
