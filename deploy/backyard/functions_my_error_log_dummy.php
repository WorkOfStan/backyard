<?php

if (!function_exists('my_error_log')) {
    function my_error_log($message, $level = 0, $error_number = 0){
        if($level<=3){
            error_log($message);
        }
    }
}