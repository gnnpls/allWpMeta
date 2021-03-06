<?php
if (!defined('ABSPATH')) {
    exit;
}

class all_WP_Meta
{

    public function init()
    {
        define('AWM_JQUERY_LOAD', apply_filters('awm_jquery_load_filter', true));
        require_once awm_path . '/languages/strings.php';
        require_once awm_path . '/includes/main.php';
        require_once awm_path . '/includes/gallery-meta-box/gallery-meta-box.php';
        add_action('plugins_loaded', function () {
            load_plugin_textdomain('all-wp-meta', false, awm_path . '/languages/');
        });
        add_action('init', array($this, 'awm_init'), 100);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_script'), 100);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles_scripts'), 100);
        add_action('add_meta_boxes', array($this, 'awm_add_post_meta_boxes'), 10, 2);
        add_action('admin_init', array($this, 'awm_admin_post_columns'), 100);
        add_action('admin_init', array($this, 'awm_add_term_meta_boxes'), 100);
        add_action('admin_menu', array($this, 'awm_add_options_page'), 100);
        add_action('admin_init', array($this, 'awm_register_option_settings'), 100);
        add_action('restrict_manage_posts', array($this, 'awm_add_restrict_posts_form'), 100);
        add_filter('pre_get_posts', array($this, 'awm_pre_get_posts'), 100);
        add_action(
            'save_post',
            function ($post_id) {
                if ((!wp_is_post_revision($post_id) && 'auto-draft' != get_post_status($post_id) && 'trash' != get_post_status($post_id))) {
                    if (isset($_POST['awm_custom_meta'])) {
                        awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $post_id, 'post', get_post_type($post_id));
                    }
                }
            },
            100
        );

        add_action('profile_update', 'awm_profile_update', 10, 100);
        add_action('user_register', 'awm_profile_update', 10, 100);
        function awm_profile_update($user_id)
        {
            if (isset($_POST['awm_custom_meta'])) {
                awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $user_id, 'user');
            }
        }

        add_action(
            'edit_term',
            function ($term_id, $taxonomy) {
                if (isset($_POST['awm_custom_meta'])) {
                    awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $term_id, 'term');
                }
            },
            100,
            2
        );

        add_action(
            'create_term',
            function ($term_id, $taxonomy) {
                if (isset($_POST['awm_custom_meta'])) {
                    awm_save_custom_meta($_POST['awm_custom_meta'], $_POST, $term_id, 'term');
                }
            },
            100,
            2
        );
    }


    public function awm_admin_post_columns()
    {
        global $pagenow;

        switch ($pagenow) {
                /*case 'edit-tags.php':
        foreach ($posts as $p) {
        if (isset($_GET['post_type']) && isset($_GET['taxonomy']) && $_GET['post_type'] == $postType && isset($p['tax_types']) && array_key_exists($_GET['taxonomy'], $p['tax_types'])) {
        add_filter('manage_edit-'.$_GET['taxonomy'].'_columns', function ($columns) use ($p) {
        $columns['fx_metrics'] = __('Total Views', 'filox-metrics');

        return $columns;
        }, 10, 1);
        add_filter('manage_edit-'.$_GET['taxonomy'].'_sortable_columns', function ($columns) use ($p) {
        $columns['fx_metrics'] = '_fm_views_total';

        return $columns;
        }, 10, 1);

        add_action('manage_'.$_GET['taxonomy'].'_custom_column', function ($content, $column, $term_id) {
        if ($column == 'fx_metrics') {
        echo get_term_meta($term_id, '_fm_views_total', true) ?: 0;
        }
        }, 10, 3);
        break;
        }
        }

        break;*/
            case 'edit.php':
                $metaBoxes = $this->meta_boxes();
                if (!empty($metaBoxes)) {
                    foreach ($metaBoxes as $metaBoxKey => $metaBoxData) {
                        if (isset($metaBoxData['library']) && !empty($metaBoxData['library'])) {
                            foreach ($metaBoxData['library'] as $meta => $data) {
                                if (isset($data['admin_list']) && $data['admin_list']) {
                                    $data['key'] = $meta;
                                    foreach ($metaBoxData['postTypes'] as $postType) {
                                        if (isset($_GET['post_type']) && $_GET['post_type'] == $postType) {
                                            /*add post columns*/
                                            add_filter('manage_' . $postType . '_posts_columns', function ($columns) use ($data) {
                                                $columns[$data['key']] = $data['label'];
                                                return $columns;
                                            }, 10, 1);
                                            /*add the value of the post columns*/
                                            add_action('manage_' . $postType . '_posts_custom_column', function ($column) use ($data) {
                                                global $post;
                                                if ($data['key'] == $column) {
                                                    echo awm_display_meta_value($data['key'], $data, $post->ID);
                                                }
                                            }, 10, 1);
                                            /*add the sortables*/
                                            if (isset($data['sortable']) && $data['sortable']) {
                                                add_filter('manage_edit-' . $postType . '_sortable_columns', function ($columns) use ($data) {
                                                    $columns[$data['key']] = $data['key'] . '_awm_sort_by_' . $data['sortable'];
                                                    return $columns;
                                                }, 10, 1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            default:
                break;
        }
    }





    /**
     * admin enqueue scripts and styles
     */
    public function admin_enqueue_styles_scripts()
    {
        wp_enqueue_style('awm-slim-lib-style');
        wp_enqueue_style('awm-admin-style');
        wp_enqueue_style('awm-global-style');
        wp_enqueue_script('awm-slim-lib-script');
        wp_enqueue_script('awm-global-script');
        wp_enqueue_script('awm-admin-script');
    }

    /**
     * enquee scripts and styles
     */
    public function enqueue_styles_script()
    {
        if (AWM_JQUERY_LOAD) {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-awm');
        }
        wp_enqueue_style('awm-global-style');
        wp_enqueue_script('awm-global-script');
        wp_enqueue_script('awm-public-script');
    }
    /**
     * init function
     */
    public function awm_init()
    {
        $this->register_script_styles();
        $this->add_nonce_action();
    }

    /**
     * private function add awm save action
     */
    private function add_nonce_action()
    {
        if (isset($_REQUEST['awm_form_nonce_field'])) {
            do_action('awm_form_action');
        }
    }

    /**
     * register scripts and styles
     */
    private function register_script_styles()
    {
        wp_register_style('awm-slim-lib-style', 'https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.0/slimselect.min.css', false, '1.0.0');
        wp_register_style('awm-global-style', awm_url . 'assets/css/global/awm-global-style.min.css', false, '1.0.0');
        wp_register_style('awm-admin-style', awm_url . 'assets/css/admin/awm-admin-style.min.css', false, '1.0.0');
        wp_register_script('awm-global-script', awm_url . 'assets/js/global/awm-global-script.js', array(), false, true);
        wp_register_script('awm-public-script', awm_url . 'assets/js/public/awm-public-script.js', array(), false, true);
        wp_localize_script('awm-global-script', 'awmGlobals', array('url' => esc_url(site_url())));
        wp_register_script('awm-admin-script', awm_url . 'assets/js/admin/awm-admin-script.js', array(), false, true);
        wp_register_script('awm-slim-lib-script', 'https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.0/slimselect.min.js', array(), false, true);
        wp_register_style('jquery-ui-awm', apply_filters('jquery_ui_awm_filter', 'https://code.jquery.com/ui/1.12.1/themes/pepper-grinder/jquery-ui.css'));
    }


    /**
     * this function is responsible for the admin pre get posts based on the awm restrict manage posts
     */
    public function awm_pre_get_posts($query)
    {
        global $pagenow;

        if (!is_admin()) {
            return;
        }

        if (isset($_REQUEST['awm_restict_post_list']) && !empty($_REQUEST['awm_restict_post_list'])) {
            $lists = $_REQUEST['awm_restict_post_list'];
            $registered = $this->restrict_post_forms();
            if ($query->is_main_query() && is_admin() && $pagenow == 'edit.php') {
                foreach ($lists as $list) {
                    if (isset($registered[$list])) {
                        if (isset($registered[$list]['callback']) && function_exists($registered[$list]['callback'])) {
                            $query = call_user_func_array($registered[$list]['callback'], array($query));
                        }
                    }
                }
            }
        }


        /*check order by*/
        $orderby = $query->get('orderby') ?: '';

        if (strpos($orderby, '_awm_sort_by_') !== false) {
            $awm_info = explode('_awm_sort_by_', $orderby);
            $meta = $awm_info[0];
            $type = $awm_info[1];
            $query->set('meta_key', $meta);
            $query->set('orderby', $type);
        }

        return $query;
    }

    /**
     * 
     */


    /**
     * get all the registered options pages
     *
     * @return array
     */
    public function options_boxes()
    {
        $optionsPages = apply_filters('awm_add_options_boxes_filter', array());
        /**
         * sort settings by order
         */
        if (!empty($optionsPages)) {
            uasort($optionsPages, function ($a, $b) {
                $first = isset($a['order']) ? $a['order'] : 100;
                $second = isset($b['order']) ? $b['order'] : 100;
                return $first - $second;
            });
        }

        return $optionsPages;
    }


    /**
     * Get post types for this meta box.
     *
     * @return array
     */
    public function meta_boxes()
    {

        $metaBoxes = apply_filters('awm_add_meta_boxes_filter', array(), 1);
        /**
         * sort settings by order
         */
        uasort($metaBoxes, function ($a, $b) {
            $first = isset($a['order']) ? $a['order'] : 100;
            $second = isset($b['order']) ? $b['order'] : 100;
            return $first - $second;
        });
        return $metaBoxes;
    }

    /**
     * Get post types for this meta box.
     *
     * @return array
     */
    protected function term_meta_boxes()
    {
        return apply_filters('awm_add_term_meta_boxes_filter', array());
    }

    /**
     * get all the restict post forms
     */
    protected function restrict_post_forms()
    {
        $restrict_forms = apply_filters('awm_restrict_post_boxes_filter', array());
        /**
         * sort settings by order
         */

        uasort($restrict_forms, function ($a, $b) {
            $first = isset($a['order']) ? $a['order'] : 100;
            $second = isset($b['order']) ? $b['order'] : 100;
            return $first - $second;
        });

        return $restrict_forms;
    }


    /**
     * get all the forms added to certain post types via awm
     */
    public function awm_add_restrict_posts_form()
    {
        $restrict_post_forms = $this->restrict_post_forms();
        if (!empty($restrict_post_forms)) {

            $post_type = $_GET['post_type'] ? $_GET['post_type'] : 'post';

            foreach ($restrict_post_forms as $optionKey => $optionData) {
                if (in_array($post_type, $optionData['postTypes']) && !empty($optionData['library'])) {
                    $library = array();
                    foreach ($optionData['library'] as $key => $data) {
                        $library[$key] = $data;
                        $library[$key]['exclude_meta'] = true;
                    }
                    $library['awm-id'] = $optionKey;
                    $library['awm_restict_post_list[]'] = array('case' => 'input', 'type' => 'hidden', 'exclude_meta' => true, 'attributes' => array('value' => $optionKey));
                    echo awm_show_content($library, 0, 'restrict_manage_posts');
                }
            }
        }
    }

    /**
     * register settings for the options
     */
    public function awm_register_option_settings()
    {
        $optionsPages = $this->options_boxes();
        if (!empty($optionsPages)) {


            foreach ($optionsPages as $optionKey => $optionData) {
                if ((isset($optionData['library']) && !empty($optionData['library'])) || (isset($optionData['callback']) && !empty($optionData['callback']))) {
                    $args = array();
                    $options = awm_callback_library($optionData);
                    foreach ($options as $key => $data) {
                        register_setting($optionKey, $key, $args);
                    }
                    add_filter('option_page_capability_' . $optionKey, function () {
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

        if (!empty($optionsPages)) {
            foreach ($optionsPages as $optionKey => $optionData) {
                $optionData['id'] = $optionKey;
                $parent = isset($optionData['parent']) ? $optionData['parent'] : 'options-general.php';
                $cap = isset($optionData['cap']) ? $optionData['cap'] : 'manage_options';
                $callback = isset($optionData['ext_callback']) ? $optionData['ext_callback'] : 'awm_options_callback';
                global $awm_settings;
                $awm_settings = $optionData;
                if ($parent) {
                    add_submenu_page($parent, $optionData['title'], $optionData['title'], $cap, $optionKey, $callback);
                    continue;
                }
                add_menu_page(
                    ucwords($optionData['title']),
                    ucwords($optionData['title']),
                    $cap,
                    $optionKey,
                    $callback
                );
            }
        }
    }




    /**
     * add term meta boxes to taxonomies
     */
    public function awm_add_term_meta_boxes()
    {
        global $pagenow;
        if (in_array($pagenow, array('edit-tags.php', 'term.php'))) {
            $metaBoxes = $this->term_meta_boxes();
            if (!empty($metaBoxes)) {
                /**
                 * sort settings by order
                 */
                uasort($metaBoxes, function ($a, $b) {
                    $first = isset($a['order']) ? $a['order'] : 100;
                    $second = isset($b['order']) ? $b['order'] : 100;
                    return $first - $second;
                });
                foreach ($metaBoxes as $metaBoxKey => $metaBoxData) {
                    if (isset($metaBoxData['library']) && !empty($metaBoxData['library']) && isset($metaBoxData['taxonomies'])) {
                        $metaBoxData['id'] = $metaBoxKey;
                        foreach ($metaBoxData['taxonomies'] as $taxonomy) {
                            if (isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy'] == $taxonomy) {
                                add_action($taxonomy . '_add_form_fields', function ($term) use ($metaBoxData) {
                                    echo '<input type="hidden" name="awm_metabox[]" value="' . $metaBoxData['id'] . '"/>';
                                    echo awm_show_content($metaBoxData['library'], 0, 'term');
                                });
                                add_action($taxonomy . '_edit_form_fields', function ($term) use ($metaBoxData) {
                                    echo '<input type="hidden" name="awm_metabox[]" value="' . $metaBoxData['id'] . '"/>';
                                    echo awm_show_content($metaBoxData['library'], $term->term_id, 'term');
                                });
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * add metaboxes to the admin
     * @param array $postType all the post type sto show the post box
     * @param object $post the post object
     */
    public function awm_add_post_meta_boxes($postType, $post)
    {

        $metaBoxes = $this->meta_boxes();

        if (!empty($metaBoxes)) {
            foreach ($metaBoxes as $metaBoxKey => $metaBoxData) {
                if (in_array($postType, $metaBoxData['postTypes'])) {

                    $metaBoxData['library'] = awm_callback_library($metaBoxData);
                    if (!empty($metaBoxData['library'])) {
                        $metaBoxData['post'] = $post;
                        $metaBoxData['id'] = $metaBoxKey;
                        add_meta_box(
                            $metaBoxKey,
                            $metaBoxData['title'], // $title
                            function () use ($metaBoxData) {
                                $view = isset($metaBoxData['view']) ? $metaBoxData['view'] : 'post';
                                $metaBoxData['library']['awm-id'] = $metaBoxData['id'];
                                echo apply_filters('awm_add_meta_boxes_filter_content', awm_show_content($metaBoxData['library'], $metaBoxData['post']->ID, $view), $metaBoxData['id']);
                                echo '<input type="hidden" name="awm_metabox[]" value="' . $metaBoxData['id'] . '"/>';
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
}






$metas = new all_WP_Meta();
$metas->init();
