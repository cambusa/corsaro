<?php
/****************************************************************************
* Name:            mirror_download.php                                      *
* Project:         Cambusa/rySource                                         *
* Version:         1.69                                                     *
* Description:     Remote file system browser                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include("../sysconfig.php");
include("../rygeneral/unicode.php");
include("../rygeneral/writelog.php");

if(isset($_GET['env']) && isset($_GET['file'])){
    $env=$_GET['env'];
    $env_strconn="";
    if(is_file($path_databases."_environs/".$env.".php")){
        include($path_databases."_environs/".$env.".php");
    }
    $file=$env_strconn.$_GET['file'];
    $file=utf8Decode($file);
    $file=html_entity_decode($file);
    $file=str_replace("´", "'", $file);
    $tr=Array();
    $tr["\'"]="'";
    $tr["\\\""]="\"";
    $tr["\\\\"]="\\";
    $file=strtr($file,$tr);
    
    $path_parts = pathinfo($file);
    $base=$path_parts["basename"];
    if(is_file($file)){
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: application/octet-stream");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"".$base."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($file));
        header('Connection: close');

        readfile($file);
    }
    else{
        writelog("File\r\n\r\n".$file."\r\n\r\nnot found!");
    }
}
exit(0);
?>
