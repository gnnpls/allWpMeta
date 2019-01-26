<?php

if (!defined('ABSPATH')) {
    exit;
}

function awm_wp_admin_style()
{
    wp_register_style('awm-admin-style', awm_url.'/assets/css/admin/awm-style.min.css', false, '1.0.0');
    wp_enqueue_style('awm-admin-style');
}
add_action('admin_enqueue_scripts', 'awm_wp_admin_style');

add_action('save_post', function ($post_id, $post, $out) {
    if ((!wp_is_post_revision($post_id) && 'auto-draft' != get_post_status($post_id) && 'trash' != get_post_status($post_id))) {
        if (isset($_POST['awm_custom_meta'])) {
            awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $post_id, 0, $tt);
        }
    }
}, 10, 3);

add_action('profile_update', 'awm_profile_update', 10, 1);
add_action('user_register', 'awm_profile_update', 10, 1);
function awm_profile_update($user_id)
{
    if (isset($_POST['awm_custom_meta'])) {
        $custom_meta = awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $user_id, 2);
    }
}
