//backyard 1
/*! debug functions v1 */
"use strict";

//http://stackoverflow.com/questions/7585351/testing-for-console-log-statements-in-ie
if (typeof debugging === "undefined") {
  var debugging = true; //or false; // or true
}
function consoleMsg() {
  if (debugging) {
    alert(message);
  }
}
try {
  console.log();
} catch (ex) {
  /*var*/ console = {
    log: function () {
      consoleMsg();
    },
  };
}

/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Source: http://binnyva.blogspot.com/2005/10/dump-function-javascript-equivalent-of.html
 */
function dump(arr, level) {
  var dumped_text = "";
  if (!level) {
    level = 0;
  }

  //The padding given at the beginning of the line.
  var level_padding = "";
  for (var j = 0; j < level + 1; j++) level_padding += "    ";

  if (typeof arr == "object") {
    //Array/Hashes/Objects
    for (var item in arr) {
      var value = arr[item];

      if (typeof value == "object") {
        //If it is an array,
        dumped_text += level_padding + "'" + item + "' ...\n"; //@TODO 3: Use single quote instead of double quotes.
        dumped_text += dump(value, level + 1);
      } else {
        dumped_text += level_padding + "'" + item + "' => \"" + value + '"\n'; //@TODO 3: Use single quote instead of double quotes.
      }
    }
  } else {
    //Stings/Chars/Numbers etc.
    dumped_text = "===>" + arr + "<===(" + typeof arr + ")";
  }
  return dumped_text;
}

var my_error_log_event_session_counter = 0;

/**
 * Requires jQuery
 * Requires include_once (__BACKYARDROOT__."/my_error_log_js.php"); or may call api
 *
 * @param {string} message
 * @param {int} level
 * @param {string} apiUrl
 * @returns {Boolean}
 */
function my_error_log(message, level, apiUrl) {
  if (apiUrl == null) {
    apiUrl = ""; //this page
  }
  // http://free.t-mobile.cz/check13stage/api/v1/error_log/
  var nameOfThisApp = "tobeadapted"; //@TODO 4 - adapt na volající skript. Anebo nechat univerzální a adaptovat při každém konkrétním volání? Možná zbytečné, protože v logu je stejně uvedeno jméno volaného skriptu, tedy to, co je v parametru url
  var jqxhr = $.ajax({
    url: apiUrl,
    type: "POST",
    data: {
      my_error_log_message:
        nameOfThisApp +
        " " +
        message +
        " (" +
        ++my_error_log_event_session_counter +
        ")",
      my_error_log_level: level,
    },
    dataType: "json",
  }); //@TODO 2 - přidat info o error auth.
  if (debugging) {
    console.log(message);
  }
  return true;
}
