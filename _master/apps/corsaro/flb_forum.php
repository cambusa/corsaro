<?php 
/****************************************************************************
* Name:            flb_forum.php                                            *
* Project:         Corsaro                                                  *
* Module:          Filibuster                                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

/*******************************
| INIZIALIZZAZIONE GESTORE MDI |
*******************************/

include_once "../../cambusa/rywinz/rywinclude.php";

/******************************
| CONFIGURAZIONE APPLICAZIONE |
******************************/

$winz_appname="corsaro";
$winz_apptitle="Corsaro";
$winz_loadmodules="library.php";
$winz_functionname="qvforum";
$winz_functionpath="qvcontenuti/";
$winz_functiontitle="Forum Filibuster";
$winz_applogout=true;

if(isset($_GET["environ"])){
    $winz_appenviron=$_GET["environ"];
    setcookie("flb_environ", $winz_appenviron, time()+365*24*60*60);
}
elseif(isset($_COOKIE["flb_environ"])){
    $winz_appenviron=$_COOKIE["flb_environ"];
}

if(isset($_GET["sitename"])){
    $sitename=$_GET["sitename"];
    setcookie("flb_sitename", $sitename, time()+365*24*60*60);
}
elseif(isset($_COOKIE["flb_sitename"])){
    $sitename=$_COOKIE["flb_sitename"];
}
else{
    $sitename="";
}

if(isset($_GET["pageid"])){
    $pageid=$_GET["pageid"];
    setcookie("flb_pageid", $pageid, time()+365*24*60*60);
}
elseif(isset($_COOKIE["flb_pageid"])){
    $pageid=$_COOKIE["flb_pageid"];
}
else{
    $pageid="";
}

$winz_moremodules=<<<MOREMODULES
<script>
var _filibustersitename="{$sitename}";
</script>
MOREMODULES;

/**************
| GESTORE MDI |
**************/

include_once $path_cambusa."rywinz/rywembed.php";
?>
