"use strict";
//backyard 1
var geocoder;

//  geoInitialize();//mela by byt az po body onload
//Get the latitude and the longitude;
function successFunction(position) {
  var lat = position.coords.latitude;
  var lng = position.coords.longitude;
  codeLatLng(lat, lng);
}
function errorFunction(error) {
  alert("Geocoder failed");
  //alert("Geocoder failed: " + error.message); //@TODO - zapsat ajaxem do logu //1210052318
}
function geoInitialize() {
  //var city;
  //var geocoder;
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(successFunction, errorFunction);
  }
  geocoder = new google.maps.Geocoder();
  //alert ("geocoder "+dump(geocoder));
  //alert ("nav.geol "+dump(navigator.geolocation));
  //alert ("city "+dump(city));
  //alert ("lat "+dump(lat));
  //  var now = new Date();myDate=now.getFullYear()+'-'+(now.getMonth()+1)+'-'+now.getDate();
  //  document.getElementById('form_project_name').value = city.short_name+' '+myDate;
}
function codeLatLng(lat, lng) {
  var city;
  var latlng = new google.maps.LatLng(lat, lng);
  geocoder.geocode({ latLng: latlng }, function (results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      console.log(results);
      if (results[1]) {
        //formatted address
        //alert(results[0].formatted_address)
        //find country name
        for (var i = 0; i < results[0].address_components.length; i++) {
          for (
            var b = 0;
            b < results[0].address_components[i].types.length;
            b++
          ) {
            //there are different types that might hold a city admin_area_lvl_1 usually does in come cases looking for sublocality type will be more appropriate
            if (
              results[0].address_components[i].types[b] ==
              "administrative_area_level_1"
            ) {
              //this is the object you are looking for
              city = results[0].address_components[i];
              break;
            }
          }
        }
        //city data
        //alert(city.short_name + " " + city.long_name);
        var now = new Date();
        myDate =
          now.getFullYear() + "-" + (now.getMonth() + 1) + "-" + now.getDate();
        var weekday = new Array(7);
        weekday[0] = "Neděle"; //"Sunday";
        weekday[1] = "Pondělí"; //"Monday";
        weekday[2] = "Úterý"; //"Tuesday";
        weekday[3] = "Středa"; //"Wednesday";
        weekday[4] = "Čtvrtek"; //"Thursday";
        weekday[5] = "Pátek"; //"Friday";
        weekday[6] = "Sobota"; //"Saturday";

        //var x = document.getElementById("demo");
        //x.innerHTML=weekday[d.getDay()]
        document.getElementById("form_project_name").value =
          weekday[now.getDay()] + " " + city.short_name; //myDate;
      } else {
        //alert("No results found");//@TODO - do logu
      }
    } else {
      //alert("Geocoder failed due to: " + status);//@TODO - do logu
    }
  });
  return city;
}

// my-own
//rychly pristup k ID
function ge(id) {
  return document.getElementById(id);
}

//http://binnyva.blogspot.com/2005/10/dump-function-javascript-equivalent-of.html
/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 *
 * @param {type} arr
 * @param {type} level
 * @returns {String|window.dump.dumped_text}
 */
function dump(arr, level) {
  var dumped_text = "";
  if (!level) level = 0;

  //The padding given at the beginning of the line.
  var level_padding = "";
  for (var j = 0; j < level + 1; j++) level_padding += "    ";

  if (typeof arr === "object") {
    //Array/Hashes/Objects
    for (var item in arr) {
      var value = arr[item];

      if (typeof value === "object") {
        //If it is an array,
        dumped_text += level_padding + "'" + item + "' ...\n";
        dumped_text += dump(value, level + 1);
      } else {
        dumped_text += level_padding + "'" + item + "' => \"" + value + '"\n';
      }
    }
  } else {
    //Stings/Chars/Numbers etc.
    dumped_text = "===>" + arr + "<===(" + typeof arr + ")";
  }
  return dumped_text;
}
