<?php

if (!defined('ABSPATH')) {
    exit;
}

include 'library.php';
include 'rest_api.php';
include 'admin/functions.php';


/**
 * function to show the fields available for user to choose and create a form
 */

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


/**
 * function to get the posts of a post type
 * @param string $postType wordpres post type / custom post type
 * @param int $number the number of posts to show
 * @param array $args array for the get_posts function
 * 
 */
if (!function_exists('awmPostFieldsForInput'))
{
    function awmPostFieldsForInput($postType='',$number='-1',$args=array())
    {
        $options = array();
        $defaultArgs=array(
            'post_type' => $postType,
            'numberposts' => $number,
            'status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        );
        if (!empty($args))
        {
            foreach ($args as $argKey=>$argValue)
            {
                $defaultArgs[$argKey]=$argValue;
            }
        }
        $content = get_posts($defaultArgs);
        if (!empty($content)) {
            foreach ($content as $data) {
                $options[$data->ID] = array('label' => $data->post_title);
            }
        }
        return apply_filters('wibeeHotspots_filter', $options,$postType,$number,$defaultArgs);
    }
}