awm_auto_fill_inputs();

/**
 this function is used in order to get all the inputs tha will be autofilled by others
 */
function awm_auto_fill_inputs()
{
  var elems = document.querySelectorAll('input[fill-from]');
  console.log(elems);
  if (elems){
  elems.forEach(function (elem) {
      var origin=elem.getAttribute('fill-from');
      var element = document.getElementById(origin);
      if (element) {
        element.addEventListener('change', function () {
        elem.value=element.value;
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

 request.onload = function () {
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
    elems.forEach(function (elem) {
      awm_check_call_back(elem, false);
      elem.addEventListener("change", function () {
        awm_check_call_back(elem, true);
      });


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
  forms.forEach(function (form) {
    if (document.getElementById('publish')) {
      document.getElementById('publish').addEventListener('click', function (e) {
        if (!awmCheckValidation(form)) {
          e.preventDefault();
        }
      });
    } else {
      form.addEventListener('submit', function (e) {
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
  var requireds = form.querySelectorAll('.awm-needed');
  var error = '';
  if (requireds) {
    requireds.forEach(function (required) {
      var type = required.getAttribute('data-type');
      var inputs = required.querySelectorAll('input,select,textarea');
      required.classList.remove("awm-form-error");
      if (check) {
        switch (type) {
          case 'checkbox_multiple':
            check = false;
            inputs.forEach(function (input) {
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
    tippy_message(buttonElement, sbp_error_messages.REQUIRED_FIELD + ' ' + '<strong>' + error.querySelectorAll('label > span')[0].innerHTML + '</strong>');
    }
  }
  return check;
}


function awmShowInputs() {
  var elems = document.querySelectorAll('div[show-when]');
  if (elems) {
    var event = new Event('change');
    elems.forEach(function (elem) {
      var parent = elem;
      var inputs = JSON.parse(elem.getAttribute('show-when').replace(/\'/g, '\"'));
      for (var p in inputs) {
        var element = document.getElementById(p);
        if (element) {
          element.addEventListener('change', function () {
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
          element.dispatchEvent(event);
        }
      }

    });
  }
}
