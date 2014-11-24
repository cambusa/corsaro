<?php 
/****************************************************************************
* Name:            flb_forum.php                                            *
* Project:         Corsaro                                                  *
* Module:          Filibuster                                               *
* Version:         1.62                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$winz_appname="corsaro";
$winz_apptitle="Corsaro";
$winz_loadmodules="library.php";
$winz_functionname="qvforum";
$winz_functionpath="qvcontenuti/";
$winz_functiontitle="Forum Filibuster";

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

include_once "../appconfig.php";
include_once "config.php";
include_once "../../cambusa/rywinz/rywembed.php";
?>
