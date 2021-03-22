<?php

if (!defined('ABSPATH')) {
    exit;
}




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


