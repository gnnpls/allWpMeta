function awm_open_tab(evt, cityName) {
 var i, awm_tabcontent, awm_tablinks;
 cityName = cityName.trim()
 awm_tabcontent = document.getElementsByClassName("awm_tabcontent");
 for (i = 0; i < awm_tabcontent.length; i++) {
  awm_tabcontent[i].style.display = "none";
 }
 awm_tablinks = document.getElementsByClassName("awm_tablinks");

 for (i = 0; i < awm_tablinks.length; i++) {
  awm_tablinks[i].className = awm_tablinks[i].className.replace(" active", "");
 }
 document.getElementById(cityName + '_content_tab').style.display = "block";
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