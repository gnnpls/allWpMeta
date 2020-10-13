<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('save_post', function ($post_id, $post, $out) {
    if ((!wp_is_post_revision($post_id) && 'auto-draft' != get_post_status($post_id) && 'trash' != get_post_status($post_id))) {
        if (isset($_POST['awm_custom_meta'])) {
            awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $post_id, 'post', get_post_type($post_id));
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

add_action('edit_term', function ($term_id, $taxonomy) {
    if (isset($_POST['awm_custom_meta'])) {
        $custom_meta = awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $term_id, 'term');
    }
}, 10, 2);

add_action('create_term', function ($term_id, $taxonomy) {
    if (isset($_POST['awm_custom_meta'])) {
        $custom_meta = awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $term_id, 'term');
    }
}, 10, 2);


  /**
     * add settings page for custom options
     */
    function awm_options_callback()
    {
        ob_start();
        include  awm_path . 'includes/admin/settings.php';
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
    }