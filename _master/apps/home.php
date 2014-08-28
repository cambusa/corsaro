<?php
    $language="english";
    if(isset($_COOKIE['_egolanguage'])){
        $language=$_COOKIE['_egolanguage'];
    }
    if($language=="english")
        include_once "home_eng.php";
    else
        include_once "home_ita.php";
?>