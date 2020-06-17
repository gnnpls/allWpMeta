<?php
if ( ! defined( 'ABSPATH' ) ) {
 exit;
}

class all_WP_Meta
{

    public function __construct()
    {
            
            require_once awm_path.'/includes/gallery-meta-box/gallery-meta-box.php';
            require_once awm_path.'/languages/strings.php';
            require_once awm_path.'/includes/main.php';

            add_action('plugins_loaded', function(){
                load_plugin_textdomain('all-wp-meta', false, awm_path . '/languages/');
                
                add_action('wp_loaded', function () {
                wp_register_style('awm-slim-lib-style', 'https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.18.10/slimselect.min.css', false, '1.0.0');
                wp_register_style('awm-global-style', awm_url . 'assets/css/global/awm-global-style.min.css', false, '1.0.0');
                wp_register_style('awm-admin-style', awm_url . 'assets/css/admin/awm-admin-style.min.css', false, '1.0.0');
                wp_register_script('awm-global-script', awm_url . 'assets/js/global/awm-global-script.js', array(), false, true);
                wp_localize_script('awm-global-script', 'awmGlobals', array('url' => esc_url(site_url())));
                wp_register_script('awm-admin-script', awm_url . 'assets/js/admin/awm-admin-script.js', array(), false, true);
                wp_register_script('awm-slim-lib-script', 'https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.18.10/slimselect.min.js', array(), false, true);
            }, 10, 1);

            add_action('wp_enqueue_scripts', function () {
                wp_enqueue_style('awm-global-style');
                wp_enqueue_script('awm-global-script');
            }, 100);

            add_action('admin_enqueue_scripts', function () {
                wp_enqueue_style('awm-slim-lib-style');
                wp_enqueue_style('awm-admin-style');
                wp_enqueue_style('awm-global-style');
                wp_enqueue_script('awm-slim-lib-script');
                wp_enqueue_script('awm-global-script');
                wp_enqueue_script('awm-admin-script');
            }, 100);

            
            });
            if (is_admin()){
            add_action('add_meta_boxes', array($this,'awm_add_post_meta_boxes'),10,2);
            add_action('admin_init', 'awm_admin_post_columns');
            add_action('admin_init',array($this,'awm_add_term_meta_boxes'),100);
            add_action('admin_menu',array($this,'awm_add_options_page'),100);
            add_action('admin_init',array($this,'awm_register_option_settings'),100);
            }            
    }

    /**
     * get all the registered options pages
     *
     * @return array
     */
    protected function options_boxes()
    {
        return apply_filters('awm_add_options_boxes_filter',array());
    }


    /**
     * Get post types for this meta box.
     *
     * @return array
     */
    protected function meta_boxes()
    {
        return apply_filters('awm_add_meta_boxes_filter',array());
    }

    /**
     * Get post types for this meta box.
     *
     * @return array
     */
    protected function term_meta_boxes()
    {
        return apply_filters('awm_add_term_meta_boxes_filter',array());
    }

    /**
     * register settings for the options
     */
    public function awm_register_option_settings()
    {   
        $optionsPages = $this->options_boxes();
        if (!empty($optionsPages))
        {
            foreach ($optionsPages as $optionKey=>$optionData)
            {
                if (isset($optionData['library']) && !empty($optionData['library']))
                {
                    $args = array();
                    $options = $optionData['library'];
                    foreach ($options as $key => $data) {
                        register_setting($optionKey, $key, $args);
                    }
                    add_filter('option_page_capability_'.$optionKey, function () {
                        return 'edit_posts';
                    });
                }
            }
        }
    }

    /**
     * add options pages
     */
    public function awm_add_options_page()
    {
        global $pagenow;
        $optionsPages = $this->options_boxes();
        if (!empty($optionsPages))
        {
            foreach ($optionsPages as $optionKey=>$optionData)
            {
                $optionData['id']=$optionKey;
                $parent=isset($optionData['parent']) ? $optionData['parent'] : 'options-general.php';
                $cap=isset($optionData['cap']) ? $optionData['cap'] : 'manage_options';
                $callback=isset($optionData['callback']) ? $optionData['callback'] : 'awm_options_callback';
                global $awm_settings;
                $awm_settings=$optionData;
                add_submenu_page( $parent, $optionData['title'], $optionData['title'], $cap, $optionKey,$callback); 
            }
        }
    }

  


    /**
     * add term meta boxes to taxonomies
     */
    public function awm_add_term_meta_boxes()
    {
        global $pagenow;
        if (in_array($pagenow,array('edit-tags.php','term.php'))) {
        $metaBoxes = $this->term_meta_boxes();
        if (!empty($metaBoxes)) {
            foreach ($metaBoxes as $metaBoxKey => $metaBoxData) {              
                if (isset($metaBoxData['library']) && !empty($metaBoxData['library']) && isset($metaBoxData['taxonomies'])) {
                    $metaBoxData['id']=$metaBoxKey;
                    foreach ($metaBoxData['taxonomies'] as $taxonomy) {
                        if (isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy']==$taxonomy) {                            
                                add_action($taxonomy.'_add_form_fields', function ($term) use ($metaBoxData) {
                                    echo '<input type="hidden" name="awm_metabox[]" value="'.$metaBoxData['id'].'"/>';
                                    echo awm_show_content($metaBoxData['library'], 0, 'term');
                                    });
                                add_action($taxonomy.'_edit_form_fields', function ($term) use ($metaBoxData) {
                                    echo '<input type="hidden" name="awm_metabox[]" value="'.$metaBoxData['id'].'"/>';
                                    echo awm_show_content($metaBoxData['library'], $term->term_id, 'term');
                                });
                            }    
                            
                        }
                    }
                }
            }
        }
    }


    public function awm_add_post_meta_boxes($postType,$post)
    {
        
    $metaBoxes = $this->meta_boxes();
    
    if (!empty($metaBoxes)) {
        wp_enqueue_media();
        foreach ($metaBoxes as $metaBoxKey => $metaBoxData) {
            if (isset($metaBoxData['library']) && !empty($metaBoxData['library']) && in_array($postType, $metaBoxData['postTypes'])) {
                $metaBoxData['id'] = $metaBoxKey;
                add_meta_box($metaBoxKey,
                    $metaBoxData['title'], // $title
                    function ($post) use ($metaBoxData) {
                        $view=isset($metaBoxData['view']) ? $metaBoxData['view'] : 'post';
                        echo apply_filters('awm_add_meta_boxes_filter_content', awm_show_content($metaBoxData['library'], $post->ID,$view), $metaBoxData['id']);
                        echo '<input type="hidden" name="awm_metabox[]" value="'.$metaBoxData['id'].'"/>';
                    },
                    $metaBoxData['postTypes'], // $page
                    $metaBoxData['context'], // $context
                    $metaBoxData['priority'] // $priority
                ); 
            }
        }
    }
    }

}
new all_WP_Meta();

