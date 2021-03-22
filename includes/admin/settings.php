<?php
if (!defined('ABSPATH')) {
    exit;
}
global $pagenow;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
$allWpMeta = new all_WP_Meta();
$options = $allWpMeta->options_boxes();
if (!empty($page)) {
    $awm_settings = $options[$page];
    $awm_settings['id'] = $page;
    $custom_link = '';
    if (isset($awm_settings['parent']) && strpos($awm_settings['parent'], $pagenow) !== false) {
        $custom_link = $pagenow;
        if (isset($_REQUEST['post_type'])) {
            $custom_link .= '?post_type=' . $_REQUEST['post_type'];
        }
    }
    $awm_settings['library'] = awm_callback_library($awm_settings);
    if ($pagenow == 'admin.php' || $pagenow == 'options-general.php' || $custom_link == $awm_settings['parent']) { ?>
        <div class="wrap awm-settings-form" id="<?php echo $awm_settings['id']; ?>">
            <h2><?php echo $awm_settings['title']; ?></h2>
            <form method="post" action="options.php" id="awm-form-<?php echo $awm_settings['id']; ?>" class="awm-form">
                <?php
                if (isset($awm_settings['library']) && !empty($awm_settings['library'])) {
                    settings_fields($awm_settings['id']);
                    do_settings_sections($awm_settings['id']);
                    $options = $awm_settings['library'];
                    $settings = array('awm-id' => $awm_settings['id']);
                    foreach ($options as $key => $data) {
                        $data['id'] = $key;
                        if (!isset($data['attributes']['value'] ))
                        {
                        $value = get_option($key);
                        $data['attributes']['value'] = apply_filters('awm_settings_page_value', $value, $data, $awm_settings);
                        }
                        $settings[$key] = $data;
                    }
                    echo awm_show_content($settings);
                    echo '<input type="hidden" name="awm_metabox[]" value="' . $awm_settings['id'] . '"/>';
                ?>
                    <div class="awm-form-submit-area">
                        <?php submit_button(); ?>
                    </div>
                <?php
                }
                ?>
            </form>
        </div>
<?php
    }
}
?>