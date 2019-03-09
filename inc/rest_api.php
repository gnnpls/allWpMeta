<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('all-wp-meta', '/awm-map-options', array(
            'methods' => 'GET',
            'callback' => 'awm_map_options_func',
        ));
}, 10, 1);

if (!function_exists('awm_map_options_func')) {
    function awm_map_options_func()
    {
        $options = array();

        $options['key'] = '';
        $options['lat'] = '39.0742';
        $options['lng'] = '21.8243';
        $options['map_options'] = array(
            'zoom' => 12,
        );

        return apply_filters('awm_map_options_func_filter', $options);
    }
}
