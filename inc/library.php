<?php

if (!defined('ABSPATH')) {
    exit;
}

function awm_show_content($arrs, $id = 0, $view = 'post', $target = 'edit', $label = true, $specific = '', $sep = ', ')
{
    $msg = array();
    foreach ($arrs as $n => $a) {
        /*check if hidden val or not*/
        $required = (isset($a['required']) && $a['required']) ? 'required="true"' : false;
        $original_meta = $n;
        $ins = '';
        $label = isset($a['label']) ? $a['label'] : $n;
        if (substr($n, 0, 1) === '_') {
            $n = ltrim($n, '_');
        }
        if (($n == $specific && $specific != '') || $specific == '') {
            $show = isset($a['show']) ? $a['show'] : 1;
            $stop = 0;
            if ($show == 1) {
                $label_class = $extra_fields2 = array();
                $extraa = '';
                $class = isset($a['class']) ? implode(' ', $a['class']) : '';
                switch ($view) {
                    case 'user':
                        $val = get_user_meta($id, $original_meta, true) ?: '';
                        break;
                    case 'term':
                        $val = get_term_meta($id, $original_meta, true) ?: '';
                        break;
                    case 'post':
                        $val = get_post_meta($id, $original_meta, true) ?: '';
                        break;
                    default:
                        $val = 0;
                    break;
                }

                if (isset($a['label_class']) && !empty($a['label_class'])) {
                    $label_class = $a['label_class'];
                }

                /*check if isset attribute value*/

                if (isset($a['attributes']['value'])) {
                    $val = $a['attributes']['value'];
                    unset($a['attributes']['value']);
                }

                /*change the id*/
                $original_meta_id = $original_meta;
                if (isset($a['attributes']['id'])) {
                    $original_meta_id = $a['attributes']['id'];
                    unset($a['attributes']['id']);
                }
                $label_class[] = $a['case'];
                /*change here to array*/
                if ((isset($a['class']) && (in_array('sbp_req_for_book', $a['class']) || in_array('sbp_req_for_save', $a['class']))) || $required) {
                    $label_class[] = 'sbp_needed';
                    $required = '';
                }
                switch ($target) {
                    case 'no-value':
                        /*just display the meta*/
                        switch ($a['case']) {
                            case 'input':
                                $label_class[] = $a['case'];
                                switch ($a['type']) {
                                    case 'checkbox':
                                        $val = $val == 1 ? awm_Yes : awm_No;
                                        break;
                                    case 'hidden':
                                        if ($d == 1) {
                                            $ins .= '<input type="'.$a[2].'" name="'.$original_meta.'" id="'.$original_meta_id.'" value="'.$val.'" '.$extraa.' class="'.$class.'"/>';
                                        }
                                        break;
                                    default:
                                        break;
                                }
                                break;
                            case 'checkbox_multiple':
                            case 'select':
                                $old_val = $val;
                                $val = array();
                                if (!empty($a['options']) && !empty($old_val)) {
                                    foreach ($a['options'] as $vv => $vvv) {
                                        if (is_array($old_val)) {
                                            foreach ($old_val as $ld => $lb) {
                                                if ($vv == $lb) {
                                                    $val[] = $vvv['label'];
                                                    unset($old_val[$ld]);
                                                    break;
                                                }
                                            }
                                        } else {
                                            if ($old_val == $vv) {
                                                $val[] = $vvv['label'];
                                            }
                                        }
                                    }
                                    $val = implode($sep, $val);
                                } else {
                                    $val = '-';
                                }
                                break;
                        }

                        if ($d == 1) {
                            $label_class[] = 'sbp_no_show';

                            $ins .= '<div class="ss1">'.$a['label'].'</div><div class="ss2" id="'.$n.'">'.$val.'</div>';
                        } else {
                            $msg[$n] = array('value' => $val, 'label' => $a['label']);
                        }
                        break;
                    case 'read':
                        /*case to return the meta in array*/
                        $msg[$n] = array('value' => $val, 'attrs' => $a);
                        $stop = 1;
                        break;
                    default:
                        if (isset($a['type'])) {
                            $label_class[] = $a['type'];
                        }

                        /*display input fields*/
                        if ($a['case'] != 'checkbox_multiple' && $a['case'] != 'repeater' && $a['case'] != 'awm_tab') {
                            if ($label && $view != 'none') {
                                $ins .= '<label for="'.$original_meta_id.'" class="awm-input-label"><span>'.$label.'</span></label>';
                            }
                        }
                        if (!empty($a['attributes']) && is_array($a['attributes'])) {
                            foreach ($a['attributes'] as $k => $v) {
                                if (is_array($v)) {
                                    $v = implode(',', $v);
                                }
                                $extra_fields2[] = $k.'="'.$v.'"';
                                if ($k == 'min' && $val == 0) {
                                    $val = $v;
                                }
                            }
                        }
                        $extraa .= isset($extra_fields2) ? implode(' ', $extra_fields2) : '';
                        switch ($a['case']) {
                            case 'message':
                                if (isset($a['value']) && !empty($a['value'])) {
                                    $ins = '<div class="awm-meta-message" id="'.$original_meta_id.'"><div class="awm-meta-message-label">'.$a['label'].'</div><div class="awm-meta-message-inner">'.$a['value'].'</div></div>';
                                }
                                break;
                            case 'button':
                                $link = isset($a['link']) ? $a['link'] : '#';
                                $ins = '<a href="'.$link.'" id="'.$n.'" title="'.$a['label'].'" class="'.$class.'" '.$extraa.'>'.$a['label'].'</a>';
                                break;
                            case 'input':
                                $label_class[] = 'awm-cls-33';
                                $input_type = $a['type'];
                                $after_message = (isset($a['after_message']) && !empty($a['after_message'])) ? '<span class="awm-after-message"><label for="'.$original_meta_id.'">'.$a['after_message'].'</span></label>' : '';
                                switch ($a['type']) {
                                    case 'number':
                                        $val = (int) $val;
                                        break;
                                    case 'checkbox':
                                        if ($val == 1) {
                                            $extraa .= ' checked';
                                        }
                                        $val = 1;
                                        break;
                                    case 'hidden':
                                        $label_class[] = 'sbp_no_show';
                                        break;
                                    default:
                                        break;
                                }
                                $ins .= '<input type="'.$input_type.'" name="'.$original_meta.'" id="'.$original_meta_id.'" value="'.$val.'" '.$extraa.' class="'.$class.'" '.$required.'/>'.$after_message;

                                break;
                            case 'checkbox_multiple':
                                $ins .= '<label><span>'.$a['label'].'</span></label>';

                                foreach ($a['options'] as $dlm => $dlmm) {
                                    $chk_ex = '';
                                    if (is_array($val) && in_array($dlm, $val)) {
                                        $chk_ex = ' checked';
                                    }
                                    $ins .= '<div class="awm-multiple-checkbox"><div class="insider"><input type="checkbox" name="'.$original_meta.'[]" id="'.$original_meta_id.'_'.$dlm.'" value="'.$dlm.'" '.$extraa.$chk_ex.' class="'.$class.'"/><label for="'.$original_meta_id.'_'.$dlm.'" class="awm-input-label"><span>'.$dlmm['label'].'</span></label></div></div>';
                                }
                                $n = $n.'[]';
                                break;
                            case 'select':
                                if ($val != '' && !is_array($val)) {
                                    $val = array($val);
                                }
                                $select_name = $original_meta;
                                $label_class[] = 'awm-cls-33';
                                if (isset($a['attributes']) && array_key_exists('multiple', $a['attributes']) && $a['attributes']['multiple']) {
                                    $select_name .= '[]';
                                }

                                $ins .= '<select name="'.$select_name.'" id="'.$original_meta_id.'" class="'.$class.'" '.$extraa.' '.$required.'>';
                                if (!empty($a['options'])) {
                                    if (count($a['options']) > 1) {
                                        if (!(isset($a['removeEmpty']) && $a['removeEmpty'])) {
                                            $ins .= '<option value="">'.$a['label'].'</option>';
                                        }
                                    }
                                    foreach ($a['options'] as $vv => $vvv) {
                                        $selected = '';
                                        if (!empty($val) && in_array($vv, $val)) {
                                            $selected = 'selected';
                                        }
                                        $attrs = array();
                                        if (isset($vvv['extra'])) {
                                            foreach ($vvv['extra'] as $lp => $ld) {
                                                $attrs[] = $lp.'="'.$ld.'"';
                                            }
                                        }
                                        $ins .= '<option value="'.$vv.'" '.$selected.' '.implode(' ', $attrs).'>'.$vvv['label'].'</option>';
                                    }
                                }
                                $ins .= '</select>';

                                break;
                            case 'image':
                                $multiple = isset($a['multiple']) ? $a['multiple'] : false;
                                $ins .= awm_custom_image_image_uploader_field($original_meta, $original_meta_id, $val, $multiple, $required);
                                $label_class[] = 'awm-custom-image-meta';
                                $label_class[] = 'awm-cls-33';
                                break;
                            case 'textarea':
                                $label_class[] = 'awm-cls-100';
                                if (isset($a['wp_editor']) && $a['wp_editor']) {
                                    ob_start();
                                    wp_editor($val, $original_meta_id, array('textarea_name' => $original_meta, 'editor_class' => $class));
                                    $ins .= ob_get_clean();
                                    $label_class[] = 'awm-wp-editor';
                                } else {
                                    $ins .= '<textarea rows="5" name="'.$original_meta.'" id="'.$original_meta_id.'" class="'.$class.'" '.$required.' '.$extraa.'>'.$val.'</textarea>';
                                }

                                break;
                            case 'radio':
                                foreach ($a['options'] as $vkey => $valll) {
                                    $chk = '';
                                    if ($vkey == $val) {
                                        $chk = 'checked="checked"';
                                    }
                                    $ins .= '<label class="awm-radio-options"><input type="radio" name="'.$original_meta.'" id="'.$original_meta_id.'" value="'.$vkey.'" '.$chk.' '.$required.'/><span class="awm-radio-label">'.$valll['label'].'</span></label>';
                                }
                                break;
                            case 'section':
                                $label_class[] = 'awm-section-field';
                                $ins .= '<div class="awm-inner-section"><div class="awm-inner-section-content">';
                                foreach ($a['include'] as $key => $data) {
                                    $inputname = $original_meta_id.'['.$key.']';
                                    $data['attributes']['id'] = $original_meta_id.'_'.$key;
                                    $data['attributes']['exclude_meta'] = true;

                                    $ins .= awm_show_content(array($inputname => $data));
                                }
                                $ins .= '</div></div>';

                                break;
                            case 'awm_tab':
                                if (isset($a['awm_tabs']) && !empty($a['awm_tabs'])) {
                                    $main_tab_id = $original_meta;
                                    $tabs = '';
                                    $tab_contents = '';
                                    $ins .= '<div class="awm-tab-wrapper">';
                                    $ins .= '<div class="awm-tab-wrapper-title">'.$a['label'].'</div>';
                                    $first_visit = 0;
                                    $val = !empty($val) ? $val : array();
                                    foreach ($a['awm_tabs'] as $tab_id => $tab_intro) {
                                        ++$first_visit;
                                        $show = $first_visit == 1 ? 'awm-tab-show active' : '';
                                        $style = $first_visit == 1 ? 'style="display: block;"' : '';
                                        $tabs .= '<div id="'.$tab_id.'_tab" class="awm_tablinks '.$show.'" onclick="awm_open_tab(event,\' '.$tab_id.'\')">'.$tab_intro['label'].'</div>';
                                        $tab_contents .= '<div id="'.$tab_id.'_content_tab" class="awm_tabcontent" '.$style.'>';

                                        foreach ($tab_intro['include'] as $key => $data) {
                                            $inputname = $main_tab_id.'['.$tab_id.']['.$key.']';
                                            $data['attributes']['id'] = $main_tab_id.'_'.$tab_id.'_'.$key;
                                            if (isset($val[$tab_id][$key])) {
                                                $data['attributes']['value'] = $val[$tab_id][$key];
                                            }
                                            $data['attributes']['exclude_meta'] = true;

                                            $tab_contents .= awm_show_content(array($inputname => $data));
                                        }
                                        $tab_contents .= '</div>';
                                    }
                                    $ins .= '<div class="awm-tab">'.$tabs.'</div>'.$tab_contents;
                                    $ins .= '</div>';
                                }

                                break;

                            case 'map':
                                $label_class[] = 'awm-cls-100';
                                $lat = (isset($val['lat']) && !empty($val['lat'])) ? $val['lat'] : '';
                                $lng = (isset($val['lng']) && !empty($val['lng'])) ? $val['lng'] : '';
                                $address = (isset($val['address']) && !empty($val['address'])) ? $val['address'] : '';
                                $ins .= '<input id="awm_map'.$original_meta_id.'_search_box" class="controls" type="text" placeholder="'.$a['label'].'" value="'.$address.'" '.$required.' onkeypress="return noenter()"><div class="awm_map" id="awm_map'.$original_meta_id.'"></div>';
                                $ins .= '<input type="hidden" name="'.$original_meta.'[lat]" id="awm_map'.$original_meta_id.'_lat" value="'.$lat.'" />';
                                $ins .= '<input type="hidden" name="'.$original_meta.'[lng]" id="awm_map'.$original_meta_id.'_lng" value="'.$lng.'" />';
                                $ins .= '<input type="hidden" name="'.$original_meta.'[address]" id="awm_map'.$original_meta_id.'_address" value="'.$address.'" />';
                                break;
                            case 'repeater':
                                if (!empty($a['include'])) {
                                    $ins .= '<div class="awm-repeater" data-count="'.count($a['include']).'" data-id="'.$original_meta_id.'">';
                                    $ins .= '<div class="awm-repeater-title">'.$a['label'].'</div>';
                                    $ins .= '<div class="awm-repeater-contents">';

                                    $val = !empty($val) ? array_values($val) : array();

                                    if ((empty($val)) && isset($a['prePopulated'])) {
                                        $val = $a['prePopulated'];
                                    }

                                    $counter = !empty($val) ? count($val) : 1;
                                    for ($i = 0; $i < $counter; ++$i) {
                                        $ins .= '<div class="awm-repeater-content" data-counter="'.$i.'">';
                                        foreach ($a['include'] as $key => $data) {
                                            $inputname = $original_meta.'['.$i.']['.$key.']';
                                            if (isset($val[$i][$key])) {
                                                $data['attributes']['value'] = $val[$i][$key];
                                            }
                                            $data['attributes']['exclude_meta'] = true;

                                            $data['attributes']['id'] = $original_meta.'_'.$i.'_'.$key;

                                            $ins .= awm_show_content(array($inputname => $data));
                                        }
                                        $item = isset($a['item_name']) ? $a['item_name'] : awm_Roww;
                                        $ins .= '<div class="awm-actions"><div class="awm-repeater-remove"><span class="awm_action awm-remove">'.awm_Remove.' '.$item.'</span></div><div class="awm-repeater-add"><span class="awm_action awm-add">'.awm_Add.' '.$item.'</span></div></div>';

                                        $ins .= '</div>';
                                        /*repeater content end*/
                                    }

                                    $ins .= '</div>';
                                    $ins .= '</div>';
                                }
                                break;
                            default:
                                break;
                        }
                        if ($label && !(isset($a['attributes']['exclude_meta'])) && $view != 'none') {
                            $ins .= '<input type="hidden" name="awm_custom_meta[]" value="'.$original_meta.'"/>';
                        }

                        break;
                }

                if ($stop != 1 && isset($n)) {
                    switch ($view) {
                        case 'none':
                            /*fronted view*/
                            $msg[] = $ins;
                            break;
                        case 'term':
                            switch ($id) {
                                case 0:
                                $msg[] = '<div class="form-field term-group awm-term-meta-row">'.$ins.'</div>';
                                break;
                                default:
                                $msg[] = '<tr class="form-field term-group-wrap" data-input="'.$original_meta_id.'"><th scope="row" class="'.implode(' ', $label_class).'" data-input="'.$original_meta_id.'" data-type="'.$a['case'].'"><label for="'.$original_meta_id.'" class="awm-input-label">'.$a['label'].'</label></th><td class="awm-term-input">'.$ins.'</td></tr>';
                                break;
                            }

                        break;
                        case 'user':
                            /*user view*/
                            $msg[] = '<tr data-input="'.$original_meta_id.'"><th class="'.implode(' ', $label_class).'" data-input="'.$original_meta_id.'" data-type="'.$a['case'].'"><label for="'.$original_meta_id.'" class="awm-input-label">'.$a['label'].'</label></th>';
                            $msg[] = '<td>'.$ins.'</td></tr>';
                            break;
                        default:
                            $label_class[] = 'awm-meta-field';
                            $msg[] = '<div class="'.implode(' ', $label_class).'" data-input="'.$original_meta_id.'" data-type="'.$a['case'].'">';
                            $msg[] = $ins;
                            if (is_admin() && isset($a['information']) && !empty($a['information'])) {
                                $msg[] = '<div class="sbp-tippy-admin-message"><span class="sbp_icon sbp-icon-gps" data-message="'.$a['information'].'"></span></div>';
                            }
                            $msg[] = '</div>';
                            break;
                    }
                }
            }
        }
    }
    $msg = apply_filters('awm_show_content_filter', $msg, $id, $arrs, $view, $target, $label, $specific, $sep);
    switch ($target) {
        case 'edit':
        $msg = implode('', $msg);
        break;
        default:
        break;
    }

    return $msg;
}

function awm_save_custom_meta($data, $dataa, $id, $view = 'post', $tt = '')
{
    if (isset($data) && !empty($data)) {
        $arr = awm_custom_meta_update_vars($data, $dataa, $id, $view);
        do_action('awm_custom_meta_update_action', $data, $dataa, $id, $view, $tt);

        return $arr;
    }
}

function awm_custom_meta_update_vars($meta, $metaa, $id, $view)
{
    foreach ($meta as $k) {
        $chk = '';

        if (strpos($k, '[') !== false) {
            $keys = explode('[', $k);
            $ref = &$metaa;
            $ref2 = &$arr;
            $count = 0;
            while ($key = array_shift($keys)) {
                if ($count == 0) {
                    $k = $key;
                }
                $key = str_replace(']', '', $key);
                $ref = &$ref[$key];
                $ref2 = &$ref2[$key];
                ++$count;
            }
            $ref2 = $ref;
            $val = $arr[$k];
        } else {
            if (isset($metaa[$k])) {
                $chk = $metaa[$k];
            }
            $val = isset($chk) ? $chk : '';
            $arr[$k] = $val;
        }
        switch ($view) {
            case 'user':
                /*update user meta*/
                if (!empty($val)) {
                    update_user_meta($id, $k, $val);
                } else {
                    delete_user_meta($id, $k);
                }
                break;
            case 'term':
                /*update user meta*/
                if (!empty($val)) {
                    update_term_meta($id, $k, $val);
                } else {
                    delete_term_meta($id, $k);
                }
                break;
            default:
                /* update post type*/
                if (!empty($val)) {
                    update_post_meta($id, $k, $val);
                } else {
                    delete_post_meta($id, $k);
                }
                break;
        }
    }

    return $arr;
}

function awm_custom_image_image_uploader_field($name, $id, $value = '', $multiple = false, $required = '')
{
    $image = ' button">'.sbp_Upload_image;
    $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
    $display = 'none'; // display state ot the "Remove image" button

    if ($image_attributes = wp_get_attachment_image_src($value, $image_size)) {
        $image = '"><img src="'.$image_attributes[0].'"/>';
        $display = 'inline-block';
    }

    return '<div class="awm-image-upload" id="awm_image'.$id.'"data-multiple="'.$multiple.'" data-add_label="'.sbp_Insert_image.'" data-remove_label="'.sbp_Remove_images.'">
		<a href="#" class="awm_custom_image_upload_image_button'.$image.'</a>
		<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$required.'/>
		<a href="#" class="awm_custom_image_remove_image_button" style="display:inline-block;display:'.$display.'">Remove image</a>
	</div>';
}

function awm_add_meta_boxes($postType, $post)
{
    $metaBoxes = apply_filters('awm_add_meta_boxes_filter', array());
    if (!empty($metaBoxes)) {
        wp_enqueue_media();
        foreach ($metaBoxes as $metaBoxKey => $metaBoxData) {
            if (isset($metaBoxData['library']) && !empty($metaBoxData['library'])) {
                $metaBoxData['id'] = $metaBoxKey;
                add_meta_box($metaBoxKey,
            $metaBoxData['title'], // $title
            function ($post) use ($metaBoxData) {
                echo apply_filters('awm_add_meta_boxes_filter_content', awm_show_content($metaBoxData['library'], $post->ID), $metaBoxData['id']);
            },
            $metaBoxData['postTypes'], // $page
            $metaBoxData['context'], // $context
            $metaBoxData['priority']
            ); // $priority
            }
        }
    }
}

if (is_admin()) {
    add_action('add_meta_boxes', 'awm_add_meta_boxes', 10, 2);
    add_action('admin_init', 'awm_admin_post_columns');
}

function awm_admin_post_columns()
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

            $metaBoxes = apply_filters('awm_add_meta_boxes_filter', array());
            if (!empty($metaBoxes)) {
                foreach ($metaBoxes as $metaBoxKey => $metaBoxData) {
                    if (isset($metaBoxData['library']) && !empty($metaBoxData['library'])) {
                        foreach ($metaBoxData['library'] as $meta => $data) {
                            if (isset($data['admin_list']) && $data['admin_list']) {
                                $data['key'] = $meta;
                                foreach ($metaBoxData['postTypes'] as $postType) {
                                    if (isset($_GET['post_type']) && $_GET['post_type'] == $postType) {
                                        add_filter('manage_'.$postType.'_posts_columns', function ($columns) use ($data) {
                                            $columns[$data['key']] = $data['label'];

                                            return $columns;
                                        }, 10, 1);
                                        /*add_filter('manage_edit-'.$postType.'_sortable_columns', function ($columns) use ($data) {
                                            $columns['_fm_views_total'] = '_fm_views_total';
                                            $columns['_fm_arch_impr_total'] = '_fm_arch_impr_total';
                                            if (isset($p['sbp_book'])) {
                                                $columns['_fm_form_impr_total'] = '_fm_form_impr_total';
                                            }

                                            return $columns;
                                        }, 10, 1);*/

                                        add_action('manage_'.$postType.'_posts_custom_column', function ($column) use ($data) {
                                            global $post;
                                            if ($data['key'] == $column) {
                                                echo awm_display_meta_value($data['key'], $data, $post->ID);
                                            }
                                        }, 10, 1);
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

function awm_display_meta_value($meta, $data, $postId)
{
    $value = get_post_meta($postId, $meta, 'true');
    if (!empty($value)) {
        switch ($data['case']) {
        case 'select':
        case 'checkbox_mutliple':
        if (array_key_exists($value, $data['options'])) {
            $value = $data['options'][$value]['label'];
        }
        break;
    }
    }

    return $value;
}
