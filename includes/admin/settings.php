<?php
if (!defined('ABSPATH')) {
    exit;
}
wp_enqueue_media();
global $awm_settings;
?>
<div class="wrap " id="<?php echo $awm_settings['id']; ?>">
        <h2><?php echo $awm_settings['title']; ?></h2>
        <form method="post" action="options.php">
        <?php
        if (isset($awm_settings['library']) && !empty($awm_settings['library']))
        {
        settings_fields( $awm_settings['id'] );
         do_settings_sections($awm_settings['id']);
         $content = '';
         $options = $awm_settings['library'];
         foreach ($options as $key => $data) {
             $value = get_option($key);
             $data['attributes']['value'] = $value;
             $content .= awm_show_content(array($key => $data));
         }
         echo $content;
         submit_button();
        }
        ?>
        </form>
</div>