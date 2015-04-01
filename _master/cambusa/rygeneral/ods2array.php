<?php
/****************************************************************************
* Name:            ods2array.php                                            *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once "$tocambusa/rygeneral/ods2array_lib.php";

if(isset($_GET["ods"]))
    $ods=$_GET["ods"];
elseif(isset($_POST["ods"]))
    $ods=$_POST["ods"];
else
    $ods="";

ods2array($arr, $ods);

array_walk_recursive($arr, "ods_escapize");
print json_encode($arr);

function ods_escapize(&$sql){
    $sql=utf8_encode(utf8_decode($sql));
}
?>