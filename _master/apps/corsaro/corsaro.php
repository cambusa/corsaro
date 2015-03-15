<?php 
/****************************************************************************
* Name:            corsaro.php                                              *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$winz_appname="corsaro";
$winz_apptitle="Corsaro";
$winz_appdescr="Web-based Enterprise Resource Planning";
$winz_appversion="1.00";
$copyright_name="Rodolfo Calzetti";
$copyright_year="2014";
$copyright_dealer="";

// FORZATURA AMBIENTE
if(isset($_GET["env"])){
    $winz_appenviron=$_GET["env"];
}

include_once "../appconfig.php";
include_once "config.php";
include_once $url_cambusa."rywinz/rywinz.php";
?>
