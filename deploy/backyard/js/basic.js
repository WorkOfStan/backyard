/*! basic utilities v.1 */
'use strict';

//for timezone
function timeZoneUpdate(){
    var tz = jstz.determine();
    if (typeof (tz) === 'undefined') {
        my_error_log('No timezone found',2);
    } else {
        $.ajax({
            type: 'GET',
            url: 'timezone.php',
            data: 'time='+ tz.name()
        }).fail(function(error){
            my_error_log('timezone.php call failed: ' + error.responseText,2);
        });
    }
}

//getInputValueByName
function gi(inputname,newvalue){
  if(newvalue === undefined){//read
    if ($('[name=' + inputname + ']').attr('type') != 'radio') {
        var result = $('[name=' + inputname + ']').val();
    } else {
        var result = $('[name=' + inputname + ']:checked').val();
    }
    return result;
  } else {//write
    if ($('[name=' + inputname + ']').attr('type') != 'radio') {
        var result = $('[name=' + inputname + ']').val(newvalue);
    } else {
        var result = $('[name=' + inputname + ']:checked').val(newvalue);//for radio newvalue must be ['radio1'] or ['radio1', 'radio2'] ; see http://api.jquery.com/val/
    }
    return true;    
  }
}

//http://stackoverflow.com/questions/3710204/how-to-check-if-a-string-is-a-valid-json-string-in-javascript-without-using-try
function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}