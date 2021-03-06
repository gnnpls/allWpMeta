<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('awm_show_content')) {
    /**
     * this is the function which is responsible to display the custom inputs for metaboxes/options
     * @param array $arrs the array with the inputs
     * @param array $id the post_id,term_id
     * @param array $view which source to use to load the data
     */
    function awm_show_content($arrs, $id = 0, $view = 'post', $target = 'edit', $label = true, $specific = '', $sep = ', ')
    {
        $msg = array();
        global $awm_post_id;
        $awm_post_id = $id;
        $awm_id = '';
        if (isset($arrs['awm-id'])) {
            $awm_id = $arrs['awm-id'];
            unset($arrs['awm-id']);
        }
        uasort($arrs, function ($a, $b) {
            if (isset($a['order']) && isset($b['order'])) {
                return $a['order'] - $b['order'];
            }
        });
        $meta_counter = 0;

        foreach ($arrs as $n => $a) {
            /*check if hidden val or not*/
            $required = (isset($a['required']) && $a['required']) ? 'required="true"' : false;
            $original_meta = $n;
            $display_wrapper = true;
            $ins = '';
            $label = isset($a['label']) ? $a['label'] : $n;
            if (substr($n, 0, 1) === '_') {
                $n = ltrim($n, '_');
            }

            /**exlude meta for widgets */
            if ($view == 'widget') {
                $a['exclude_meta'] = true;
            }

            if (($n == $specific && $specific != '') || $specific == '') {
                $show = isset($a['show']) ? $a['show'] : 1;
                $stop = 0;
                if ($show == 1) {
                    switch ($a['case']) {
                        case 'postType':
                            $a['case'] = isset($a['view']) ? $a['view'] : 'select';
                            $number = isset($a['number']) ? $a['number'] : '-1';
                            $args = isset($a['args']) ? $a['args'] : array();
                            $a['callback'] = 'awmPostFieldsForInput';
                            $a['callback_variables'] = array($a['post_type'], $number, $args);
                            break;
                        case 'term':
                            $a['case'] = isset($a['view']) ? $a['view'] : 'select';
                            $number = isset($a['number']) ? $a['number'] : '-1';
                            $args = isset($a['args']) ? $a['args'] : array();
                            $option_key = isset($a['option_key']) ? $a['option_key'] : 'term_id';
                            $a['callback'] = 'awmTaxonomyFieldsForInput';
                            $a['callback_variables'] = array($a['taxonomy'], $number, $option_key, $args, $awm_id);
                            break;
                        case 'user':
                            $a['case'] = isset($a['view']) ? $a['view'] : 'select';
                            $number = isset($a['number']) ? $a['number'] : '-1';
                            $args = isset($a['args']) ? $a['args'] : array();
                            $a['callback'] = 'awmUserFieldsForInput';
                            $a['callback_variables'] = array($a['roles'], $number, $args);
                            break;
                        case 'date':
                            $a['case'] = 'input';
                            $a['type'] = 'text';
                            $a['class'][] = 'awm_cl_date';
                            break;
                        case 'html':
                            if (isset($a['value']) && !empty($a['value'])) {
                                $msg[] = '<div class="awm-meta-html awm-meta-field" id="' . $n . '">' . $a['value'] . '</div>';
                                $stop = true;
                                if (isset($a['strip'])) {
                                    $msg = array($a['value']);
                                }
                            }
                            break;
                        default:
                            break;
                    }

                    /*make changes for combined inputs*/
                    $label_class = $extra_fields2 = $label_attrs = array();
                    $extraa = '';
                    $class = isset($a['class']) ? implode(' ', $a['class']) : '';
                    switch ($view) {
                        case 'widget':
                            $val = isset($id[$a['widget-key']]) ? $id[$a['widget-key']] : '';
                            break;
                        case 'user':
                            $val = get_user_meta($id, $original_meta, true) ?: '';
                            break;
                        case 'term':
                            $val = get_term_meta($id, $original_meta, true) ?: '';
                            break;
                        case 'post':
                            $val = get_post_meta($id, $original_meta, true) ?: '';
                            break;
                        case 'restrict_manage_posts':
                            $val = isset($_GET[$original_meta]) ? $_GET[$original_meta] : '';
                            break;
                        default:
                            $val = 0;
                            break;
                    }
                    $val = apply_filters('awm_show_content_value_filter', $val, $id, $original_meta, $view);


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

                    $label_class = apply_filters('awm_label_class_filter', $label_class, $a, $required);
                    if (in_array('awm-needed', $label_class)) {
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
                                        default:
                                            break;
                                    }
                                    break;
                                case 'checkbox_multiple':
                                case 'select':
                                    $old_val = $val;
                                    $val = array();
                                    if (isset($a['callback'])) {
                                        $callback_options = array();
                                        if (!empty($a['callback_variables'])) {
                                            $callback_options = call_user_func_array($a['callback'], $a['callback_variables']);
                                        }
                                        $a['options'] = empty($callback_options) ? call_user_func($a['callback']) : $callback_options;
                                    }
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
                            $hide_label = isset($a['hide-label']) ? $a['hide-label'] : false;
                            if (!$hide_label && $label && !in_array($a['case'], array('checkbox_multiple', 'repeater', 'awm_tab', 'button'))) {
                                if (($a['case'] == 'input' && isset($a['type']) && $view != 'none' && !in_array($a['type'], array('submit', 'hidden', 'button'))) || ($a['case'] == 'select' || $a['case'] == 'textarea')) {
                                    $ins .= '<label for="' . $original_meta_id . '" class="awm-input-label"><span>' . $label . '</span></label>';
                                }
                            }
                            if (!empty($a['attributes']) && is_array($a['attributes'])) {
                                foreach ($a['attributes'] as $k => $v) {
                                    if (is_array($v)) {
                                        $v = implode(',', $v);
                                    }
                                    $extra_fields2[] = $k . '="' . $v . '"';
                                    if ($k == 'min' && $val == 0) {
                                        $val = $v;
                                    }
                                }
                            }

                            if (isset($a['show-when']) && !empty($a['show-when']) && is_array($a['show-when'])) {
                                $label_attrs[] = 'show-when="' . str_replace('"', '\'', json_encode($a['show-when'])) . '"';
                            }

                            $extraa .= isset($extra_fields2) ? implode(' ', $extra_fields2) : '';
                            switch ($a['case']) {
                                case 'function':
                                    if (isset($a['callback'])  && function_exists($a['callback'])) {
                                        $ins = '<div class="awm-meta-message" id="' . $original_meta_id . '"><div class="awm-meta-message-label">' . $a['label'] . '</div><div class="awm-meta-message-inner">' . call_user_func_array($a['callback'], array($id)) . '</div></div>';
                                    }
                                    break;
                                case 'message':
                                    if (isset($a['value']) && !empty($a['value'])) {
                                        $ins = '<div class="awm-meta-message" id="' . $original_meta_id . '"><div class="awm-meta-message-label">' . $a['label'] . '</div><div class="awm-meta-message-inner">' . $a['value'] . '</div></div>';
                                    }
                                    break;
                                case 'button':
                                    $link = isset($a['link']) ? $a['link'] : '#';
                                    $ins = '<a href="' . $link . '" id="' . $n . '" title="' . $a['label'] . '" class="' . $class . '" ' . $extraa . '>' . $a['label'] . '</a>';
                                    break;
                                case 'input':
                                    $input_type = $a['type'];
                                    $after_message = (isset($a['after_message']) && !empty($a['after_message'])) ? '<span class="awm-after-message"><label for="' . $original_meta_id . '">' . $a['after_message'] . '</span></label>' : '';

                                    switch ($input_type) {
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
                                            $ins .= '<input type="' . $input_type . '" name="' . $original_meta . '" id="' . $original_meta_id . '" value="' . $val . '" ' . $extraa . ' class="' . $class . '" ' . $required . '/>';
                                            $display_wrapper = false;
                                            break;
                                        default:
                                            break;
                                    }
                                    if ($display_wrapper) {
                                        $input_html = '<input type="' . $input_type . '" name="' . $original_meta . '" id="' . $original_meta_id . '" value="' . $val . '" ' . $extraa . ' class="' . $class . '" ' . $required . '/>';

                                        $ins .= '<div class="input-wrapper">';
                                        $ins .=  $input_html . $after_message;
                                        if ($a['type'] == 'password') {
                                            $ins .= '<div class="eye" data-toggle="password" data-id="' . $original_meta_id . '"></div>';
                                        }
                                        $ins .= '</div>';
                                    }

                                    break;
                                case 'checkbox_multiple':
                                    if (isset($a['callback'])) {
                                        $callback_options = array();
                                        if (!empty($a['callback_variables'])) {
                                            $callback_options = call_user_func_array($a['callback'], $a['callback_variables']);
                                        }
                                        $a['options'] = empty($callback_options) ? call_user_func($a['callback']) : $callback_options;
                                    }
                                    $ins .= '<label><span>' . $a['label'] . '</span></label>';
                                    $checkboxOptions = array();
                                    $ins .= '<div class="awm-options-wrapper">';
                                    if (isset($a['options']) && !empty($a['options'])) {
                                        if (!isset($a['disable_apply_all'])) {
                                            $checkboxOptions['awm_apply_all'] = array('label' => __('Select All', 'all-wp-meta'), 'extra_label' => __('Deselect All', 'all-wp-meta'));
                                        }
                                        $checkboxOptions = $checkboxOptions + $a['options'];
                                        foreach ($checkboxOptions as $dlm => $dlmm) {
                                            $chk_ex = '';
                                            if (is_array($val) && in_array($dlm, $val)) {
                                                $chk_ex = ' checked';
                                            }
                                            $value_name = $dlm != 'amw_apply_all' ? $original_meta . '[]' : '';
                                            $extraLabel = ($dlm == 'awm_apply_all' && isset($dlmm['extra_label'])) ? 'data-extra="' . $dlmm['extra_label'] . '"' : '';
                                            $valueInside = $dlm != 'awm_apply_all' ? $dlm : '';
                                            $input_id = $original_meta_id . '_' . $dlm . '_' . rand(10, 100);
                                            $ins .= '<div class="awm-multiple-checkbox"><div class="insider"><label id="label_' . $input_id . '" for="' . $input_id . '" class="awm-input-label" ><input type="checkbox" name="' . $value_name . '" id="' . $input_id . '" value="' . $valueInside . '" ' . $extraa . $chk_ex . ' class="' . $class . '"' . $extraLabel . ' data-value="' . $dlm . '"/><span>' . $dlmm['label'] . '</span></label></div></div>';
                                        }
                                        $n = $n . '[]';
                                    }
                                    $ins .= '</div>';
                                    break;
                                case 'select':
                                    if ($val != '' && !is_array($val)) {
                                        $val = array($val);
                                    }
                                    if (isset($a['callback'])) {
                                        $callback_options = array();
                                        if (!empty($a['callback_variables'])) {
                                            $callback_options = call_user_func_array($a['callback'], $a['callback_variables']);
                                        }
                                        $a['options'] = empty($callback_options) ? call_user_func($a['callback']) : $callback_options;
                                    }
                                    $select_name = $original_meta;
                                    $label_class[] = 'awm-cls-33';
                                    if (isset($a['attributes']) && array_key_exists('multiple', $a['attributes']) && $a['attributes']['multiple']) {
                                        $select_name .= '[]';
                                    }

                                    $ins .= '<select name="' . $select_name . '" id="' . $original_meta_id . '" class="' . $class . '" ' . $extraa . ' ' . $required . '>';
                                    if (!empty($a['options'])) {
                                        if (count($a['options']) > 1) {
                                            if (!(isset($a['removeEmpty']) && $a['removeEmpty'])) {
                                                $ins .= '<option value="">' . $a['label'] . '</option>';
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
                                                    $attrs[] = $lp . '="' . $ld . '"';
                                                }
                                            }
                                            $option_label = isset($vvv['label']) ? $vvv['label'] : $vv;
                                            $ins .= '<option value="' . $vv . '" ' . $selected . ' ' . implode(' ', $attrs) . '>' . $vvv['label'] . '</option>';
                                        }
                                    }
                                    $ins .= '</select>';

                                    break;
                                case 'image':
                                    if (!did_action('wp_enqueue_media')) {
                                        wp_enqueue_media();
                                    }
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
                                        $ins .= '<textarea rows="5" name="' . $original_meta . '" id="' . $original_meta_id . '" class="' . $class . '" ' . $required . ' ' . $extraa . '>' . $val . '</textarea>';
                                    }

                                    break;
                                case 'radio':
                                    $optionsCounter = 0;
                                    $ins .= '<div class="awm-radio-options">';
                                    foreach ($a['options'] as $vkey => $valll) {
                                        $chk = '';
                                        $labelRequired = '';
                                        if ($vkey == $val) {
                                            $chk = 'checked="checked"';
                                        }
                                        if ($optionsCounter < 1 && $required != '') {
                                            $labelRequired = $required;
                                        }
                                        $ins .= '<div class="awm-radio-option"><input type="radio" name="' . $original_meta . '" id="' . $original_meta_id . '_' . $vkey . '" value="' . $vkey . '" ' . $chk . ' ' . $labelRequired . '/><label class="awm-radio-options" for="' . $original_meta_id . '_' . $vkey . '"><span class="awm-radio-label">' . apply_filters('awm_radio_value_label_filter', $valll['label'], $vkey, $original_meta_id) . '</span></label></div>';
                                        $optionsCounter++;
                                    }
                                    $ins .= '</div>';
                                    break;
                                case 'section':
                                    $label_class[] = 'awm-section-field';
                                    $ins .= '<div class="awm-inner-section"><div class="awm-inner-section-content">';
                                    foreach ($a['include'] as $key => $data) {

                                        $inputname = !isset($a['keep_inputs']) ? $original_meta_id . '[' . $key . ']' : $key;
                                        $data['attributes']['id'] = isset($a['keep_inputs']) ? $original_meta_id . '_' . $key : $key;
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
                                        $ins .= '<div class="awm-tab-wrapper-title">' . $a['label'] . '</div>';
                                        $first_visit = 0;
                                        $val = !empty($val) ? $val : array();
                                        foreach ($a['awm_tabs'] as $tab_id => $tab_intro) {
                                            ++$first_visit;
                                            $show = $first_visit == 1 ? 'awm-tab-show active' : '';
                                            $style = $first_visit == 1 ? 'style="display: block;"' : '';
                                            $tabs .= '<div id="' . $tab_id . '_tab" class="awm_tablinks ' . $show . '" onclick="awm_open_tab(event,\' ' . $tab_id . '\')">' . $tab_intro['label'] . '</div>';
                                            $tab_contents .= '<div id="' . $tab_id . '_content_tab" class="awm_tabcontent" ' . $style . '>';
                                            $tab_meta = array();
                                            foreach ($tab_intro['include'] as $key => $data) {
                                                $inputname = $main_tab_id . '[' . $tab_id . '][' . $key . ']';
                                                $data['attributes']['id'] = $main_tab_id . '_' . $tab_id . '_' . $key;
                                                if (isset($val[$tab_id][$key])) {
                                                    $data['attributes']['value'] = $val[$tab_id][$key];
                                                }
                                                $data['attributes']['exclude_meta'] = true;
                                                $tab_meta[$inputname] = $data;
                                            }
                                            $tab_contents .= awm_show_content($tab_meta);
                                            $tab_contents .= '</div>';
                                        }
                                        $ins .= '<div class="awm-tab">' . $tabs . '</div>' . $tab_contents;
                                        $ins .= '</div>';
                                    }

                                    break;
                                case 'map':
                                    $label_class[] = 'awm-cls-100';
                                    $lat = (isset($val['lat']) && !empty($val['lat'])) ? $val['lat'] : '';
                                    $lng = (isset($val['lng']) && !empty($val['lng'])) ? $val['lng'] : '';
                                    $address = (isset($val['address']) && !empty($val['address'])) ? $val['address'] : '';
                                    $ins .= '<input id="awm_map' . $original_meta_id . '_search_box" class="controls" type="text" placeholder="' . __('Type to search', 'awm') . '" value="' . $address . '" ' . $required . ' onkeypress="return noenter()"><div class="awm_map" id="awm_map' . $original_meta_id . '"></div>';
                                    $ins .= '<input type="hidden" name="' . $original_meta . '[lat]" id="awm_map' . $original_meta_id . '_lat" value="' . $lat . '" />';
                                    $ins .= '<input type="hidden" name="' . $original_meta . '[lng]" id="awm_map' . $original_meta_id . '_lng" value="' . $lng . '" />';
                                    $ins .= '<input type="hidden" name="' . $original_meta . '[address]" id="awm_map' . $original_meta_id . '_address" value="' . $address . '" />';
                                    break;
                                case 'repeater':
                                    if (!empty($a['include'])) {
                                        $maxrows = isset($a['maxrows']) ? absint($a['maxrows']) : '';
                                        $ins .= '<div class="awm-repeater" data-count="' . count($a['include']) . '" data-id="' . $original_meta_id . '" maxrows="' . $maxrows . '">';
                                        $ins .= '<div class="awm-repeater-title">' . $a['label'] . '</div>';
                                        $ins .= '<div class="awm-repeater-contents">';
                                        $val = !empty($val) ? array_values(maybe_unserialize($val)) : array();
                                        if ((empty($val)) && isset($a['prePopulated'])) {
                                            $val = $a['prePopulated'];
                                        }
                                        $a['include']['awm_key'] = array(
                                            'case' => 'input',
                                            'type' => 'hidden',
                                            'attributes' => array('data-unique' => true)
                                        );
                                        $counter = !empty($val) ? count($val) : 1;
                                        for ($i = 0; $i < $counter; ++$i) {
                                            $ins .= '<div id="awm-' . $original_meta . '-' . $i . '" class="awm-repeater-content" data-counter="' . $i . '" draggable="true"><div class="awm-repeater-inputs">';
                                            $new_metas = array();
                                            foreach ($a['include'] as $key => $data) {
                                                $data['attributes'] = (isset($data['attributes']) ? $data['attributes'] : array()) + (isset($a['attributes']) ? $a['attributes'] : array());
                                                $inputname = $original_meta . '[' . $i . '][' . $key . ']';
                                                if (isset($val[$i][$key])) {
                                                    $data['attributes']['value'] = $val[$i][$key];
                                                }
                                                $data['attributes']['exclude_meta'] = true;
                                                $data['attributes']['id'] = str_replace(']', '_', str_replace('[', '_', $original_meta)) . '_' . $i . '_' . $key;
                                                $data['attributes']['input-name'] = $original_meta;
                                                $new_metas[$inputname] = $data;
                                            }
                                            $ins .= awm_show_content($new_metas);
                                            $item = isset($a['item_name']) ? $a['item_name'] : awm_Roww;
                                            $ins .= '</div><div class="awm-actions"><div class="awm-repeater-remove"><span class="awm_action awm-remove">' . awm_Remove . ' ' . $item . '</span></div><div class="awm-repeater-add"><span class="awm_action awm-add">' . awm_Add . ' ' . $item . '</span></div></div>';

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
                            if ($label && !(isset($a['attributes']['exclude_meta'])) && $view != 'none' && !isset($a['attributes']['disabled']) && !isset($a['exclude_meta'])) {
                                $ins .= '<input type="hidden" name="awm_custom_meta[]" value="' . $original_meta . '"/>';
                            }

                            break;
                    }

                    if ($stop != 1 && isset($n)) {
                        $label_class[] = 'awm-meta-field';
                        $labelClass = implode(' ', $label_class);
                        $labelAttrs = implode(' ', $label_attrs);
                        if (!$display_wrapper) {
                            $msg[] = $ins;
                            continue;
                        }
                        switch ($view) {
                            case 'none':
                                /*fronted view*/
                                $msg[] = $ins;
                                break;
                            case 'term':
                                switch ($id) {
                                    case 0:
                                        $msg[] = '<div class="form-field term-group awm-term-meta-row awm-meta-term-field' . $labelClass . '" ' . $labelAttrs . '>' . $ins . '</div>';
                                        break;
                                    default:
                                        $msg[] = '<tr class="form-field term-group-wrap awm-meta-term-field" data-input="' . $original_meta_id . '"><th scope="row" class="' . implode(' ', $label_class) . '" data-input="' . $original_meta_id . '" data-type="' . $a['case'] . '"><label for="' . $original_meta_id . '" class="awm-input-label">' . $a['label'] . '</label></th><td class="awm-term-input">' . $ins . '</td></tr>';
                                        break;
                                }

                                break;
                            case 'user':
                                /*user view*/
                                $msg[] = '<tr data-input="' . $original_meta_id . '"><th class="' . implode(' ', $label_class) . '" data-input="' . $original_meta_id . '" data-type="' . $a['case'] . '"><label for="' . $original_meta_id . '" class="awm-input-label">' . $a['label'] . '</label></th>';
                                $msg[] = '<td>' . $ins . '</td></tr>';
                                break;
                            default:
                                $msg[] = '<div class="' . implode(' ', $label_class) . '" data-input="' . $original_meta_id . '" data-type="' . $a['case'] . '" ' . $labelAttrs . '>';
                                $msg[] = $ins;
                                if (is_admin() && isset($a['information']) && !empty($a['information'])) {
                                    $msg[] = '<div class="sbp-tippy-admin-message"><span class="sbp_icon sbp-icon-gps" data-message="' . $a['information'] . '"></span></div>';
                                }
                                $msg[] = '</div>';
                                break;
                        }
                        if (!in_array('awm_no_show', $label_class)) {
                            $meta_counter++;
                        }
                    }
                }
            }
        }

        $msg = apply_filters('awm_show_content_filter', $msg, $id, $arrs, $view, $target, $label, $specific, $sep);

        switch ($target) {
            case 'edit':
                $msg = '<div id="' . $awm_id . '" class="awm-show-content" count="' .  $meta_counter . '">' . implode('', $msg) . '</div>';
                break;
            default:
                break;
        }

        return $msg;
    }
}

function awm_save_custom_meta($data, $dataa, $id, $view = 'post', $postType = '')
{
    if (isset($data) && !empty($data)) {
        awm_custom_meta_update_vars($data, $dataa, $id, $view);
        /*check for translation */
        do_action('awm_custom_meta_update_action', $data, $dataa, $id, $view, $postType);
        awm_auto_translate($data, $dataa, $id, $view);
    }
}

function awm_auto_translate($data, $dataa, $id, $view)
{
    /*wpml check*/
    $autoTranslate = array();
    if (isset($dataa['awm_metabox']) && !empty($dataa['awm_metabox'])) {

        foreach ($dataa['awm_metabox'] as $metabox) {
            $metaboxData = awm_get_metabox_info($metabox);
            if (isset($metaboxData['auto-translate']) && $metaboxData['auto-translate']) {
                $autoTranslate[$metabox] = $metaboxData['library'];
            }
        }
        if (!empty($autoTranslate)) {
            if (function_exists('icl_object_id')) {
                global $sitepress;
                foreach ($autoTranslate as $library_key => $library_data) {
                    $ids = awm_translated_ids($id);
                    if (!empty($ids) && isset($ids['original'])) {
                        if (!empty($ids) && isset($ids['original'])) {
                            $original = $ids['original'];
                            unset($ids['original']);
                            $fields = apply_filters('awm_auto_translate_fields', $library_data, $library_key);
                            $copy_metas = array();
                            foreach ($fields as $key => $data) {
                                $meta = get_post_meta($original, $key, 'true') ?: '';
                                foreach ($ids as $id) {
                                    if ($meta != '') {
                                        $copy_meta['no_empty'][$key] = $meta;
                                        update_post_meta($id, $key, $meta);
                                    } else {
                                        delete_post_meta($id, $key);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return;
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
    $image = ' button">' .  __('Insert media', 'all-wp-meta');
    $image_size = 'large'; // it would be better to use thumbnail size here (150x150 or so)
    $display = 'none'; // display state ot the "Remove image" button
    if ($value) {
        $image_attributes = wp_get_attachment_image_src($value, $image_size);
        $image = '"><img src="' . $image_attributes[0] . '"/>';
        $display = 'inline-block';
    }

    return '<div class="awm-image-upload" id="awm_image' . $id . '"data-multiple="' . $multiple . '" data-add_label="' . __('Insert media', 'all-wp-meta') . '" data-remove_label="' . __('Remove media', 'all-wp-meta') . '">
		<a href="#" class="awm_custom_image_upload_image_button' . $image . '</a>
		<input type="hidden" name="' . $name . '" id="' . $id . '" value="' . $value . '" ' . $required . '/>
		<a href="#" class="awm_custom_image_remove_image_button" style="display:inline-block;display:' . $display . '">' . __('Remove media', 'all-wp-meta') . '</a>
	</div>';
}

function awm_get_metabox_info($id)
{
    if ($id) {
        $metaBoxes = apply_filters('awm_add_meta_boxes_filter', array());
        return isset($metaBoxes[$id]) ? $metaBoxes[$id] : array();
    }
    return array();
}



if (!function_exists('awm_translated_ids')) {
    function awm_translated_ids($post_id)
    {
        global $sitepress;
        $default = $sitepress->get_default_language();
        $ids = array();
        if ($default != ICL_LANGUAGE_CODE) {
            $original_id = (int) icl_object_id($post_id, get_post_type(), true, $default);
            /*get custom fields*/
            $ids[] = $post_id;
        } else {
            $original_id = $post_id;
            $trid = $sitepress->get_element_trid($post_id);
            $translations = $sitepress->get_element_translations($trid);
            if (!empty($translations)) {
                unset($translations[$default]);
                foreach ($translations as $lan => $tran) {
                    $ids[] = $tran->element_id;
                }
            }
        }
        $ids['original'] = $original_id;

        return apply_filters('awm_translated_ids_filter', $ids);
    }
}

function awm_display_meta_value($meta, $data, $postId)
{
    global $awm_post_id;
    $awm_post_id = $postId;
    $value = get_post_meta($postId, $meta, true) ?: false;
    $original_value = $value;
    switch ($data['case']) {
        case 'input':
            switch ($data['type']) {
                case 'checkbox':
                    $value = $value ? __('Yes', 'all-wp-meta') : __('No', 'all-wp-meta');
                    break;
                case 'url':
                    $value = $value != '' ? '<a href="' . $value . '" target="_blank">' . $value . '</a>' : '';
                    break;
                default:
                    break;
            }

            break;
        case 'postType':
            $value = $value != '' ? '<a href="' . get_edit_post_link($value) . '" target="_blank">' . get_the_title($value) . '</a>' : '-';
            break;
        case 'message':
        case 'html':
            $value = isset($data['value']) ? $data['value'] : '';
            if (isset($data['strip'])) {
                return $value;
            }
            break;
        case 'function':
            if (isset($data['callback']) && function_exists($data['callback'])) {
                $value = call_user_func_array($data['callback'], array($postId));
            }
            break;
        case 'select':
        case 'checkbox_multiple':
            $values = is_array($value) ? $value : array($value);
            $finalShow = array();
            foreach ($values as $value) {
                if (!empty($value) && array_key_exists($value, $data['options'])) {
                    $finalShow[] = $data['options'][$value]['label'];
                }
            }
            $value = implode('<br>', $finalShow);
            break;
    }

    return apply_filters('awm_display_meta_value_filter', $value, $meta, $original_value, $data, $postId);
}



/**
 * this funciton is used to creat a form for the fields we add
 * @param array $data all the data needed
 */
function awm_create_form($options)
{

    $defaults = array(
        'library' => '',
        'id' => '',
        'method' => 'post',
        'action' => '',
        'submit' => true,
        'submit_label' => __('Register', 'awm'),
        'nonce' => true
    );

    $settings = array_merge($defaults, $options);
    $library = $settings['library'];

    ob_start();
?>
    <form id="<?php echo $settings['id']; ?>" action="<?php echo $settings['action']; ?>" method="<?php echo $post; ?>">
        <?php
        if ($settings['nonce']) {
            wp_nonce_field($settings['id'], 'awm_form_nonce_field');
        }
        ?>
        <?php echo awm_show_content($library); ?>
        <?php if ($settings['submit']) {
        ?>
            <input type="submit" id="awm-submit-<?php echo $settings['id'] ?>" value="<?php echo $settings['submit_label']; ?>" />
        <?php
        }
        ?>
    </form>
<?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}


if (!function_exists('awm_callback_library')) {
    /**
     * return options library
     * @param array $library the library either for the metas or the options
     */
    function awm_callback_library($library)
    {
        if (isset($library['library'])) {
            return $library['library'];
        }
        if (isset($library['options']) && !empty($library['options'])) {
            return $library['options'];
        }

        if (!isset($library['library'])) {
            if (isset($library['callback'])) {
                $callback_options = array();
                if (!empty($library['callback_variables'])) {
                    $callback_options = call_user_func_array($library['callback'], $library['callback_variables']);
                }
                $library = empty($callback_options) ? call_user_func($library['callback']) : $callback_options;
                return $library;
            }
        }
        return '';
    }
}
