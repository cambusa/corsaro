<?php
/****************************************************************************
* Name:            source_deletetemp.php                                    *
* Project:         Cambusa/rySource                                         *
* Version:         2.00                                                     *
* Description:     Remote file system browser                               *
*                  Delete a file in temporary folder                        *
* Copyright (C):   2017  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once("../sysconfig.php");
include_once("../rygeneral/unicode.php");

$success=1;
$message="";
$filename="";

// CONTROLLO CHE TUTTI I PARAMETRI SIANO STATI PASSATI
//----------------------------------------------------

if(!isset($_POST['file'])){
    $success=0;
    $message="Parameter [file] not set";
}

if($success==1){
    
    $basetarget=$path_customize."temporary/";
    
    // DETERMINAZIONE FILE
    $file=$_POST['file'];
    $file=utf8Decode($file);
    $file=html_entity_decode($file);
    $file=str_replace("´", "'", $file);
    $tr=Array();
    $tr["\'"]="'";
    $tr["\\\""]="\"";
    $tr["\\\\"]="\\";
    $file=strtr($file,$tr);
    $file=str_replace("\\", "/", $file);
    
    $filename = basename($file);
    if(is_file($basetarget.$filename)){
        @unlink($basetarget.$filename);
    }
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["message"]=$message;
array_walk_recursive($j, "source_escapize");
print json_encode($j);

function source_escapize(&$value){
    if($value!=""){
        if(!mb_check_encoding($value, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            $value=utf8_encode($value);
        }
    }
}

?>