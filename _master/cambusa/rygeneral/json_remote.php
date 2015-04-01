<?php
/****************************************************************************
* Name:            json_remote.php                                          *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include("json_loader.php");

if(isset($_POST["base"]))
    $base=$_POST["base"];
elseif(isset($_GET["base"]))
    $base=$_GET["base"];
else
    $base=="";

if(isset($_POST["json"]))
    $json=$_POST["json"];
elseif(isset($_GET["json"]))
    $json=$_GET["json"];
else
    $json=="";

$infobase=json_load($base, $json);
print json_encode($infobase);
?>