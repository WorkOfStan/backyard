<?php
//http://stackoverflow.com/questions/3498128/guessing-users-timezone-in-php
    session_start();
    $_SESSION['time'] = $_GET['time'];