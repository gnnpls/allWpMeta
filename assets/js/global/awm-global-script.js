awm_auto_fill_inputs();
awm_toggle_password();
awmShowInputs();
/**
 * this function is used to toggle the password to show text or not
 */
function awm_toggle_password() {
    document.querySelectorAll('[data-toggle="password"]').forEach(function(el) {
        el.addEventListener("click", function(e) {
            var target = document.getElementById(el.getAttribute('data-id'));
            var type = target.getAttribute('type') === 'password' ? 'text' : 'password';
            target.setAttribute('type', type);
        });
    });
}

/**
 this function is used in order to get all the inputs tha will be autofilled by others
 */
function awm_auto_fill_inputs() {
    var elems = document.querySelectorAll('input[fill-from]');
    if (elems) {
        elems.forEach(function(elem) {
            var origin = elem.getAttribute('fill-from');
            var element = document.getElementById(origin);
            if (element) {
                element.addEventListener('change', function() {
                    elem.value = element.value;
                });
            }
        });
    }
}


function awm_open_tab(evt, div) {
    var i, awm_tabcontent, awm_tablinks;
    div = div.trim()
    awm_tabcontent = document.getElementsByClassName("awm_tabcontent");
    for (i = 0; i < awm_tabcontent.length; i++) {
        awm_tabcontent[i].style.display = "none";
    }
    awm_tablinks = document.getElementsByClassName("awm_tablinks");

    for (i = 0; i < awm_tablinks.length; i++) {
        awm_tablinks[i].className = awm_tablinks[i].className.replace(" active", "");
    }
    document.getElementById(div + '_content_tab').style.display = "block";
    evt.currentTarget.className += " active";

    /*open the first*/
}

if (document.getElementsByClassName("awm_custom_image_image_uploader_field-show").length) {
    var clickables = document.getElementsByClassName("awm-tab-show");
    clickables[0].click()
}



function awm_js_ajax_call(url, js_callback) {

    var request = new XMLHttpRequest();
    request.open('GET', url, true);

    request.onload = function() {
        if (request.status >= 200 && request.status < 400) {
            var call_back = window[js_callback];
            if (typeof call_back == 'function') {
                call_back(request.responseText);
            } else {
                console.log(js_callback + ' function does not exist!');
            }
        }
    };

    request.send();
}

function awmCallbacks() {
    var elems = document.querySelectorAll('input[data-callback],select[data-callback],textarea[data-callbak]');
    if (elems) {
        elems.forEach(function(elem) {
            if (!elem.classList.contains('awm-callback-checked')) {
                awm_check_call_back(elem, false);
                elem.addEventListener("change", function() {
                    awm_check_call_back(elem, true);
                });
                elem.classList.add('awm-callback-checked')
            }

        });
    }
}

function awm_check_call_back(elem, action) {
    var call_back = window[elem.getAttribute('data-callback')];

    if (typeof call_back == 'function') {
        call_back(elem, action);

    } else {
        console.log(elem.getAttribute('data-callback') + ' function does not exist!');
    }
}

function awmInitForms() {
    var forms = document.querySelectorAll('form');
    if (forms) {
        forms.forEach(function(form) {
            if (document.getElementById('publish')) {
                document.getElementById('publish').addEventListener('click', function(e) {
                    if (!awmCheckValidation(form)) {
                        e.preventDefault();
                    }
                });
            } else {
                form.addEventListener('submit', function(e) {
                    if (!awmCheckValidation(form)) {
                        e.preventDefault();
                    }
                }, false);
            }

        });
    }
}


function awmCheckValidation(form) {
    var check = true;
    var requireds = form.querySelectorAll('.awm-needed:not(.awm_no_show)');
    var error = '';
    if (requireds) {
        requireds.forEach(function(required) {
            var type = required.getAttribute('data-type');
            var inputs = required.querySelectorAll('input,select,textarea');
            required.classList.remove("awm-form-error");
            if (check) {
                switch (type) {
                    case 'checkbox_multiple':
                        check = false;
                        inputs.forEach(function(input) {
                            if (input.type == 'checkbox' && input.checked) {
                                check = true;
                            }
                        });
                        if (!check) {
                            error = required;
                            break;
                        }
                        break;
                    default:
                        if (inputs[0].value == '' && !inputs[0].disabled) {
                            check = false;
                            error = required;
                            break;
                        }
                        break;

                }
            }
        });
    }
    if (!check) {
        error.classList.add("awm-form-error");
        var buttonElement;
        if (document.getElementById('publish')) {
            buttonElement = document.getElementById('publish');
        } else {
            buttonElement = form.querySelectorAll('input[type="submit"]')[0];
        }
        if (typeof tippy_message == 'function') {
            tippy_message(buttonElement, filoxTippyMessages.REQUIRED_FIELD + ' ' + '<strong>' + error.querySelectorAll('label > span')[0].innerHTML + '</strong>');
        }
    }
    return check;
}


function awmShowInputs() {
    var elems = document.querySelectorAll('div[show-when]:not(.awm-initialized)');
    if (elems) {
        elems.forEach(function(elem) {
            var parent = elem;
            var inputs = JSON.parse(elem.getAttribute('show-when').replace(/\'/g, '\"'));
            for (var p in inputs) {
                var element = document.getElementById(p);
                if (element) {
                    element.addEventListener('change', function() {
                        switch (element.tagName) {
                            case 'SELECT':
                                if (this.value in inputs[p].values) {
                                    if (inputs[p].values[this.value]) {
                                        parent.classList.remove('awm_no_show');
                                        return true;
                                    }
                                }
                                break;
                            case 'INPUT':
                                switch (element.getAttribute('type')) {
                                    case 'checkbox':
                                        if (element.checked == inputs[p].values) {
                                            parent.classList.remove('awm_no_show');
                                            return true;
                                        }
                                        break;
                                }
                                break;
                        }
                        parent.classList.add('awm_no_show');
                    });
                    element.dispatchEvent(new window.Event('change', { bubbles: true }));
                }
            }
            elem.classList.add('awm-initialized')
        });
    }
}



function awm_create_calendar() {
    var values = [];
    jQuery('.awm_cl_date:not(.hasDatepicker)').each(function() {
            var idd = jQuery(this).attr('id');

            var parameters = {
                dateFormat: 'dd-mm-yy',
                changeMonth: false,
                altFormat: 'YYYY-DD-MM',
                minDate: '-18M',
                maxDate: '+18M',
            }

            if (jQuery(this).attr('max-date')) {
                parameters.maxDate = jQuery(this).attr('max-date');
            }


            if (jQuery(this).hasClass('awm-no-limit-date')) {
                parameters.minDate = null;
            }


            parameters.onSelect = function(d, i) {
                if (d !== i.lastVal) {
                    document.getElementById(idd).dispatchEvent(new Event('change'));
                }

            };

            values.push({ 'id': idd, 'value': jQuery('#' + idd).val() });

            jQuery('#' + idd).datepicker(parameters);
        }

    );



    jQuery(document).on('change', 'input.awm_cl_date.hasDatepicker', function() {
        var stop = false;
        var date = jQuery(this).datepicker('getDate');
        if (date !== null) {
            var change = jQuery(this).attr('data-change');
            if (change != '') {
                var next_date = jQuery('#' + change).datepicker('getDate');
                var add_days = jQuery('#' + change).attr('data-days') ? parseInt(jQuery('#' + change).attr('data-days')) : 1;
                if (next_date !== null) {
                    if (awm_timestamp(date) > awm_timestamp(next_date)) {
                        stop = true;
                    }
                }
                date.setDate(date.getDate() + add_days);
                jQuery('#' + change).datepicker('option', 'minDate', date);
                if (stop) {
                    jQuery('#' + change).datepicker('setDate', date);
                }
                if (sbp_admin_sbpadminminjs_sets) {
                    var newDate = jQuery(this).datepicker("getDate");
                    newDate.setDate(newDate.getDate() + parseInt(sbp_admin_sbpadminminjs_sets.calendar_days)),
                        jQuery("#" + change).datepicker("option", "maxDate", newDate)
                }
            }
        }
    });

    values.forEach(function(val) {
        if (val.value != '') {
            jQuery('#' + val.id).datepicker('setDate', val.value);
            jQuery('#' + val.id).change();
        }
    });
}




function awm_timestamp(d) {
    "use strict";
    d = new Date(d);
    d = d.setUTCHours(24, 0, 0, 0);
    return (d / 1000);
}


function awm_repeater_actions() {

    jQuery(document).on('click', '.awm-repeater-contents .awm_action', function() {
        var repeater = jQuery(this).closest('.awm-repeater').attr('data-id');
        var maxRows = jQuery(this).closest('.awm-repeater').attr('maxrows') ? parseInt(jQuery(this).closest('.awm-repeater').attr('maxrows')) : 0;

        if (jQuery(this).hasClass('awm-add')) {
            var old_counter = parseInt(jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content:last').attr('data-counter'));

            var new_counter = old_counter + 1;
            if (maxRows == 0 || new_counter < maxRows) {

                jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content:last').clone().addClass('cloned').appendTo('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-contents');
                jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content.cloned').attr('data-counter', new_counter);
                jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content.cloned').find('input,select,textarea').each(function() {
                    jQuery(this).val('');
                    jQuery(this).prop('checked', false);
                    var namee, id;
                    if (jQuery(this).attr("name")) {
                        namee = jQuery(this).attr("name").replace("[" + old_counter + "]", "[" + new_counter + "]");
                        id = namee.replace(/\[/g, '_').replace(/\]/g, '_');
                        jQuery(this).attr("name", namee).attr("id", id);

                    }

                    if (jQuery(this).closest('.awm-meta-field').hasClass('awm-custom-image-meta')) {
                        jQuery('.awm-repeater-content[data-counter="' + new_counter + '"] .awm-custom-image-meta').attr('data-input', id);
                        jQuery('.awm-repeater-content[data-counter="' + new_counter + '"] .awm-image-upload').attr('id', 'awm_image' + id);
                        jQuery('.awm-repeater-content[data-counter="' + new_counter + '"] .awm-image-upload .awm_custom_image_remove_image_button').trigger('click');
                    }
                });
                jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content.cloned').removeClass('cloned');

                jQuery('.awm-repeater-content[data-counter="' + new_counter + '"] input.hasDatepicker').removeClass('hasDatepicker');
                repeaterInit();
            }
        } else {
            jQuery(this).closest('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content').remove();
        }
        var elements = document.querySelectorAll('.awm-repeater[data-id="' + repeater + '"] input:last-child,.awm-repeater[data-id="' + repeater + '"] select:last-child,.awm-repeater[data-id="' + repeater + '"] textarea:last-child');
        var last = elements[elements.length - 1];
        last.dispatchEvent(new Event('change'));

    });


}