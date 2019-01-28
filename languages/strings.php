<?php

if (!defined('ABSPATH')) {
    exit;
}

function awm_global_messages()
{
    do_action('awm_pre_get_all_messages');

    /*booking messages*/
    $messages = array(
        'awm_Roww' => __('Row', 'all-wp-meta'),
        'awm_Remove' => __('Remove', 'all-wp-meta'),
        'awm_Add' => __('Add', 'all-wp-meta'),
        'awm_Upload_image' => __('Upload image', 'all-wp-meta'),
        'awm_Insert_image' => __('Insert image', 'all-wp-meta'),
        'awm_Remove_images' => __('Remove images', 'all-wp-meta'),
        'awm_Yes' => __('Yes', 'all-wp-meta'),
        'awm_No' => __('No', 'all-wp-meta'),
    );

    return apply_filters('awm_global_messages_filter', $messages);
}

if (!function_exists('awm_global_messages_init')) {
    function awm_global_messages_init()
    {
        $messages = awm_global_messages();

        /*check here*/

        if (!empty($messages)) {
            foreach ($messages as $key => $value) {
                /*check if value is array*/
                if (is_array($value)) {
                    $value = serialize($value);
                }
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
    }
}

add_action('init', 'awm_global_messages_init', 10);
add_action('admin_init', 'awm_global_messages_init', 10);
