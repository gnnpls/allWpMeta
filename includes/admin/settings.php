<?php
if (!defined('ABSPATH')) {
    exit;
}

wp_enqueue_media();
global $pagenow;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
$allWpMeta = new all_WP_Meta();
$options = $allWpMeta->options_boxes();
if (!empty($page) && ($pagenow == 'admin.php' || $pagenow== 'options-general.php')) {
    $awm_settings = $options[$page];
    $awm_settings['id'] = $page;
?>
    <div class="wrap awm-settings-form" id="<?php echo $awm_settings['id']; ?>">
        <h2><?php echo $awm_settings['title']; ?></h2>
        <form method="post" action="options.php" id="awm-form-<?php echo $awm_settings['id']; ?>" class="awm-form">
            <?php
            if (isset($awm_settings['library']) && !empty($awm_settings['library'])) {
                settings_fields($awm_settings['id']);
                do_settings_sections($awm_settings['id']);
                $content = '';
                $options = $awm_settings['library'];
                foreach ($options as $key => $data) {
                    $data['id']=$key;
                    $value = get_option($key);
                    $data['attributes']['value'] = apply_filters('awm_settings_page_value',$value,$data,$awm_settings);
                    $content .= awm_show_content(array($key => $data));
                }
                echo $content;
                ?>
                <div class="awm-form-submit-area">
                <?php submit_button();?>
                </div>
                <?php
            }
            ?>
        </form>
    </div>
<?php
}
?>