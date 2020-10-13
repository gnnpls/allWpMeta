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
if (!function_exists('awmPostFieldsForInput')) {
    function awmPostFieldsForInput($postType = '', $number = '-1', $args = array())
    {
        $options = array();
        if (!empty($postType))
            {
            if (!is_array($postType))
            {
                $postType=array($postType);
            }    
            foreach ($postType as $currentPostType)
            {
                $defaultArgs = array(
                    'post_type' => $currentPostType,
                    'numberposts' => $number,
                    'status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC',
                );
                if (!empty($args)) {
                    foreach ($args as $argKey => $argValue) {
                        $defaultArgs[$argKey] = $argValue;
                    }
                }
                $content = get_posts($defaultArgs);
                if (!empty($content)) {
                    foreach ($content as $data) {
                        $options[$data->ID] = array('label' => $data->post_title);
                    }
                }
            }
        }
        return apply_filters('awmPostFieldsForInput_filter', $options, $postType, $number, $defaultArgs);
    }
}


/**
 * function to get the posts of a post type
 * @param string $taxonomy wordpres taxonomy name
 * @param int $number the number of posts to show
 * @param string $option_key which key to bring back to the option value
 * @param array $args array for the get_posts function
 * 
 */
if (!function_exists('awmTaxonomyFieldsForInput'))
{
    function awmTaxonomyFieldsForInput($taxonomy='',$number='-1',$option_key='term_id',$args=array())
    {
        $options = array();
        $defaultArgs = array(
            'taxonomy'      => $taxonomy, // taxonomy name
            'orderby'       => 'id', 
            'order'         => 'ASC',
            'hide_empty'    => false,
            'fields'        => 'all',
             'suppress_filter' => false,
        );
        if (!empty($args))
        {
            foreach ($args as $argKey=>$argValue)
            {
                $defaultArgs[$argKey]=$argValue;
            }
        }
        $content = get_terms($defaultArgs);
        if (!empty($content)) {
            foreach ($content as $data) {
                $options[$data->{$option_key}] = array('label' => $data->name);
            }
        }
        return apply_filters('awmTaxonomyFieldsForInput_filter', $options,$taxonomy,$number,$defaultArgs);
    }
}


/**
 * function to get the posts of a post type
 * @param string $roles wordpres user roles
 * @param int $number the number of users to show
 * @param array $args array for the get_posts function
 * 
 */
if (!function_exists('awmUserFieldsForInput'))
{
    function awmUserFieldsForInput($roles=array(),$number='-1',$args=array())
    {
        $options = array();
        $defaultArgs = array(
        'role__in' => $roles,
        'orderby' => 'display_name'
        );
        
        if (!empty($args))
        {
            foreach ($args as $argKey=>$argValue)
            {
                $defaultArgs[$argKey]=$argValue;
            }
        }
        $content = get_users($defaultArgs);
        if (!empty($content)) {
            foreach ($content as $data) {
                $options[$data->ID] = array('label' => $data->display_name);
            }
        }
        return apply_filters('awmUserFieldsForInput_filter', $options,$roles,$number,$defaultArgs);
    }
}