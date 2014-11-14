<?php
/****************************************************************************
* Name:            ods2array.php                                            *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
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