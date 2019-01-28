<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('awm-admin-style');
    wp_enqueue_style('awm-global-style');
    wp_enqueue_script('awm-global-script');
    wp_enqueue_script('awm-admin-script');
}, 10, 1);

add_action('save_post', function ($post_id, $post, $out) {
    if ((!wp_is_post_revision($post_id) && 'auto-draft' != get_post_status($post_id) && 'trash' != get_post_status($post_id))) {
        if (isset($_POST['awm_custom_meta'])) {
            $tt = get_post_type($post_id);
            awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $post_id, 'post', $tt);
        }
    }
}, 10, 3);

add_action('profile_update', 'awm_profile_update', 10, 1);
add_action('user_register', 'awm_profile_update', 10, 1);
function awm_profile_update($user_id)
{
    if (isset($_POST['awm_custom_meta'])) {
        $custom_meta = awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $user_id, 'user');
    }
}

add_action('edit_terms', function ($term_id, $taxonomy) {
    if (isset($_POST['awm_custom_meta'])) {
        $custom_meta = awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $term_id, 'term');
    }
}, 10, 2);
