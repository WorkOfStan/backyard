<?php
//backyard 2 compliant
/**
 * returns the custom length unique id; default is 10
 * http://phpgoogle.blogspot.com/2007/08/four-ways-to-generate-unique-id-by-php.html
 * @param type $random_id_length
 * @return type
 */
function backyard_randomId($random_id_length = 10) {
    //generate a random id encrypt it and store it in $rnd_id 
    $rnd_id = crypt(uniqid(rand(), 1));

    //to remove any slashes that might have come 
    $rnd_id = strip_tags(stripslashes($rnd_id));

    //Removing any . or / and reversing the string 
    $rnd_id = str_replace(".", "", $rnd_id);
    $rnd_id = strrev(str_replace("/", "", $rnd_id));

    //finally I take the first 10 characters from the $rnd_id 
    $rnd_id = substr($rnd_id, 0, $random_id_length);
    my_error_log("Random id is " . $rnd_id, 5, 16);
    return ($rnd_id);
}
