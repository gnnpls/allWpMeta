<?php

if (!defined('ABSPATH')) {
    exit;
}

include 'library.php';
include 'rest_api.php';
include 'admin/functions.php';

if (!function_exists('awmInputFields')) {
    function awmInputFields()
    {

        return apply_filters('awmInputFields_filter', array(
            'select' => array('label' => __('Select', 'all-wp-meta')),
            'input' => array('label' => __('Input', 'all-wp-meta')),
            'date' => array('label' => __('Date', 'all-wp-meta')),
            'email' => array('label' => __('Email', 'all-wp-meta')),
            'checkbox' => array('label' => __('Checkbox', 'all-wp-meta')),
        ));

    }

}
