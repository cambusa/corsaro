<?php
/****************************************************************************
* Name:            source_temporary.php                                     *
* Project:         Cambusa/rySource                                         *
* Version:         2.00                                                     *
* Description:     Remote file system browser                               *
*                  Copy file from environ to temporary                      *
* Copyright (C):   2017  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once("../sysconfig.php");
include_once("../rygeneral/unicode.php");
include_once("../rygeneral/writelog.php");
include_once("../ryquiver/quiversex.php");

$success=1;
$message="";
$basetarget="";
$filename="";

// CONTROLLO CHE TUTTI I PARAMETRI SIANO STATI PASSATI
//----------------------------------------------------

if(!isset($_POST['file'])){
    $success=0;
    $message="Parameter [file] not set";
}

if(!isset($_POST['envdb'])){
    $success=0;
    $message="Parameter [envdb] not set";
}

if(!isset($_POST['envfs'])){
    $success=0;
    $message="Parameter [envfs] not set";
}

if(!isset($_POST['sessionid'])){
    $success=0;
    $message="Parameter [sessionid] not set";
}

// DERMINO I PARAMETRI E LI VALIDO
//--------------------------------
    
if($success==1){
    
    // AMBIENTE DA CUI PRENDERE IL FILE
    
    $envfs=$_POST['envfs'];
    $env_provider="";
    $env_strconn="";
    
    if(is_file($path_databases."_environs/".$envfs.".php")){

        include($path_databases."_environs/".$envfs.".php");

        if($env_provider=="filesystem"){
            $basesource=realpath($env_strconn);
        }
        else{
            $success=0;
            $message="The source environ is not a folder";
        }
    }
    else{
        $success=0;
        $message="Source environ doesn't exist";
    }
}    
    
if($success==1){
    
    // SESSIONE
    $sessionid=$_POST['sessionid'];
    
    // CONNESSIONE AL DATABASE
    $envdb=$_POST['envdb'];
    
    $maestro=maestro_opendb($envdb, false);

    if($maestro->conn!==false){
        if(qv_validatesession($maestro, $sessionid)){
            $basetarget=realpath($path_customize."temporary")."/";
        }
        else{
            $success=0;
            $message="Permission denied";
        }
    }
    else{
        $success=0;
        $message="Access to database failed";
    }

    maestro_closedb($maestro);
}

if($success==1){
    
    // PERFEZIONAMENTO BASE
    if(substr($basesource,-1)!="/")
        $basesource.="/";

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
    if(strpos($file, "..")===false){
        // NORMALIZZO LA BASE
        $basesource=str_replace("\\", "/", $basesource);
        // NORMALIZZO IL FILE
        $file=str_replace("\\", "/", $file);
        if(substr($file,0,1)!="/" && substr($file,1,2)!=":/"){
            $file=$basesource.$file;
        }
        // RISOLVO E RINORMALIZZO
        $file=str_replace("\\", "/", $file);
        if(strpos(strtolower($file), strtolower($basesource))!==false){
            $path_parts=pathinfo($file);
            
            $ext=strtolower($path_parts["extension"]);
            $ext = ($ext == '') ? $ext : '.' . $ext;
            
            $filename = $path_parts['filename'];
            while (file_exists($basetarget . $filename . $ext)) {
                $filename .= rand(10, 99);
            }
            
            if(is_file($file)){
                copy($file, $basetarget.$filename.$ext);
            }
            else{
                $success=0;
                $message="File not found!";
            }
        }
        else{
            $success=0;
            $message="Access denied!";
        }
    }
    else{
        $success=0;
        $message="Access denied!";
    }
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["message"]=$message;
$j["path"]=$url_customize."temporary/".$filename.$ext;
array_walk_recursive($j, "source_escapize");
print json_encode($j);

function solve_directory($temp){
    global $path_customize,$path_cambusa,$path_databases,$path_applications;

    $temp=str_replace("@customize/", $path_customize, $temp);
    $temp=str_replace("@cambusa/", $path_cambusa, $temp);
    $temp=str_replace("@databases/", $path_databases, $temp);
    $temp=str_replace("@apps/", $path_applications, $temp);
    return realpath($temp);
}

function source_escapize(&$value){
    if($value!=""){
        if(!mb_check_encoding($value, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            $value=utf8_encode($value);
        }
    }
}

?>