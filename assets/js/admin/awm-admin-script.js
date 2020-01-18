/*inits*/

awm_add_map();
awmCallbacks();
awmShowInputs();
awmSelectrBoxes();
awmInitForms();
awmMultipleCheckBox();
awm_create_calendar();


jQuery(document).on('click', '.awm-repeater-contents .awm_action', function () {

  var repeater = jQuery(this).closest('.awm-repeater').attr('data-id');
   var maxRows = jQuery(this).closest('.awm-repeater').attr('maxrows') ? parseInt(jQuery(this).closest('.awm-repeater').attr('maxrows')) : 0;

  if (jQuery(this).hasClass('awm-add')) {
    var old_counter = parseInt(jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content:last').attr('data-counter'));

    var new_counter = old_counter + 1;
    if (maxRows == 0 || new_counter < maxRows) {
          jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content:last').clone().addClass('cloned').appendTo('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-contents');
      jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content.cloned').attr('data-counter', new_counter);
      jQuery('.awm-repeater[data-id="' + repeater + '"] .awm-repeater-content.cloned').find('input,select,textarea').each(function () {
        jQuery(this).val('');
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

});





/*
 * Select/Upload image(s) event
 */
jQuery(document).on('click', '.awm_custom_image_upload_image_button', function (e) {
  e.preventDefault();
  var id = jQuery(this).closest('.awm-image-upload').attr('id');
  var button = $(this),
    custom_uploader = wp.media({
      title: jQuery('#' + id).attr('data-add_label'),
      library: {
        // uncomment the next line if you want to attach image to the current post
        // uploadedTo : wp.media.view.settings.post.id, 
        type: 'image'
      },
      button: {
        text: 'Use this image' // button label text
      },
      multiple: jQuery('#' + id).attr('data-multiple') // for multiple image selection set to true
    }).on('select', function () { // it also has "open" and "close" events 
      var attachment = custom_uploader.state().get('selection').first().toJSON();
      $(button).removeClass('button').html('<img class="true_pre_image" src="' + attachment.url + '" style="max-width:95%;display:block;" />').next().val(attachment.id).next().show();
      /* if you sen multiple to true, here is some code for getting the image IDs*/
      if (jQuery('#' + id).attr('data-multiple')) {
        var attachments = frame.state().get('selection'),
          attachment_ids = new Array(),
          i = 0;
        attachments.each(function (attachment) {
          attachment_ids[i] = attachment['id'];
          i++;
        });
      }

    })
    .open();
});

/*
 * Remove image event
 */
jQuery(document).on('click', '.awm_custom_image_remove_image_button', function () {
  jQuery(this).hide().prev().val('').prev().addClass('button').html('Upload image');
  return false;
});
/*awm settings*/


/*awm_map*/
var markers = [];
var awm_map_options = [];

function awm_add_map() {
  var map = document.getElementsByClassName("awm_map");

  if (typeof (map) != 'undefined' && map != null && typeof (awmGlobals) != 'undefined' && awmGlobals != null) {
    awm_js_ajax_call(awmGlobals.url + '/wp-json/all-wp-meta/awm-map-options/', 'awm_call_maps_api');
  }
}


function awm_call_maps_api(data) {
  awm_map_options = JSON.parse(data);
  var src = "//maps.googleapis.com/maps/api/js?libraries=places&callback=awmInitMap";
  if (awm_map_options.key !== null) {
    src += '&key=' + awm_map_options.key;
  }
  var a = document.createElement("script");
  a.type = "text/javascript";
  a.src = src;
  a.async = !0;
  a.defer = !0
  document.body.appendChild(a)
}

function awmInitMap() {

  var map = document.getElementsByClassName("awm_map");

  for (i = 0; i < map.length; i++) {
    var map_id = map[i].id;
    var myLatlng = {
      lat: parseFloat(document.getElementById(map_id + '_lat').value !== '' ? document.getElementById(map_id + '_lat').value : awm_map_options['lat']),
      lng: parseFloat(document.getElementById(map_id + '_lat').value !== '' ? document.getElementById(map_id + '_lng').value : awm_map_options['lng'])
    };

    var map = new google.maps.Map(document.getElementById(map_id), {
      zoom: 10,
      center: myLatlng,
    });
    var marker = new google.maps.Marker({
      position: myLatlng,
      map: map
    });
    markers.push(marker);


    google.maps.event.addListener(map, 'click', function (event) {
      placeMarker(map, event.latLng, map_id);
    });
    /*search box*/
    var input = document.getElementById(map_id + '_search_box');
    var searchBox = new google.maps.places.SearchBox(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function () {
      searchBox.setBounds(map.getBounds());
    });
    searchBox.addListener('places_changed', function () {
      var places = searchBox.getPlaces();

      if (places.length == 0) {
        return;
      }

      // For each place, get the icon, name and location.
      bounds = new google.maps.LatLngBounds();
      places.forEach(function (place) {
        if (!place.geometry) {
          console.log("Returned place contains no geometry");
          return;
        }

        placeMarker(map, place.geometry.location, map_id);
        map.fitBounds(bounds);

      });


    });

  }
}

function removeMarkers() {
  for (i = 0; i < markers.length; i++) {
    markers[i].setMap(null);
  }
}

function placeMarker(map, location, map_id) {
  removeMarkers();
  /*publish inputs to the hidden fields*/
  document.getElementById(map_id + '_lat').value = location.lat();
  document.getElementById(map_id + '_lng').value = location.lng();
  var geocoder = new google.maps.Geocoder();
  geocoder.geocode({
    'latLng': location,
  }, function (results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      if (results[0]) {
        document.getElementById(map_id + '_address').value = results[0].formatted_address;
        document.getElementById(map_id + '_search_box').value = results[0].formatted_address;
      } else {
        document.getElementById(map_id + '_address').value = '-';
        document.getElementById(map_id + '_search_box').value = '-';
      }
    }
  });


  /*puublish the marker*/
  var marker = new google.maps.Marker({
    position: location,
    map: map
  });

  markers.push(marker);
  map.panTo(marker.getPosition());
  map.fitBounds();
}

function noenter() {
  return !(window.event && window.event.keyCode == 13);
}




function awm_create_calendar() {
  jQuery('.awm_cl_date:not(.hasDatepicker)').each(function () {
    var idd = jQuery(this).attr('id');
    var parameters = {
      dateFormat: 'dd-mm-yy',
      changeMonth: true
    }
    if (!jQuery('.awm-no-limit-date').length) {
      parameters.minDate = 0;
    }
    jQuery('#' + idd).datepicker(parameters);
  });
}

function awmSelectrBoxes()
{
  var elems = document.querySelectorAll('.awm-meta-field select');
  if (elems)
  {
    elems.forEach(function(elem){
      var slim = new SlimSelect({
        select: '#'+elem.id,
        showSearch: true
      })
    });
  }
}

function repeaterInit(){
  awm_create_calendar();
  awmSelectrBoxes();
  awmInitForms();
}


function awmMultipleCheckBox() {
  var elems = document.querySelectorAll('.checkbox_multiple.awm-meta-field');
  if (elems) {
    elems.forEach(function (elem) {
      inputs = elem.querySelectorAll('input[type="checkbox"]');

      if (inputs) {
        inputs.forEach(function (input) {
          var dataValue = input.getAttribute('data-value');
          if (dataValue == 'awm_apply_all') {
            input.addEventListener('change', function (e) {
              var checked = input.checked;
              var text = input.getAttribute('data-extra');

              elem.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                if (checkbox.value != '') {
                  checkbox.checked = checked;
                }
              });
              input.setAttribute('data-extra', document.getElementById('label_' + input.id).innerText);
              document.getElementById('label_' + input.id).innerText = text;
            });
          }
        });
      }
    });
  }
}