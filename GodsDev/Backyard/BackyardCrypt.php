<?php
namespace GodsDev\Backyard;
//@todo SHOULDN'T IT BE GodsDev\Backyard\Crypt ?


class BackyardCrypt {
    protected $BackyardError = NULL;

    public function __construct(
    BackyardError $BackyardError) {
        //error_log("debug: " . __CLASS__ . ' ' . __METHOD__);
        $this->BackyardError = $BackyardError;
    }
    

/**
 * returns the custom length unique id; default is 10
 * http://phpgoogle.blogspot.com/2007/08/four-ways-to-generate-unique-id-by-php.html
 * @param int $random_id_length
 * @return string
 */
public function randomId($random_id_length = 10) {
    //generate a random id encrypt it and store it in $rnd_id 
    $rnd_id = crypt(uniqid(rand(), 1));

    //to remove any slashes that might have come 
    $rnd_id = strip_tags(stripslashes($rnd_id));

    //Removing any . or / and reversing the string 
    $rnd_id = strrev(str_replace("/", "", str_replace(".", "", $rnd_id)));

    if(strlen($rnd_id)<$random_id_length){
        $rnd_id = $rnd_id . $this->randomId($random_id_length-strlen($rnd_id));
    }
    
    //finally I take the first 10 characters from the $rnd_id 
    $rnd_id = substr($rnd_id, 0, $random_id_length);
    $this->BackyardError->log(5, "Random id is " . $rnd_id, array(16));
    return $rnd_id;
}
}