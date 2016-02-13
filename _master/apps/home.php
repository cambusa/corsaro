<?php
    $language="english";
    if(isset($_GET["lang"])){
        $language=$_GET["lang"];
        setcookie("_egolanguage", $language, time()+4000000);
    }
    elseif(isset($_COOKIE['_egolanguage'])){
        $language=$_COOKIE['_egolanguage'];
    }
    if($language=="english")
        include_once "home_eng.php";
    else
        include_once "home_ita.php";
?>