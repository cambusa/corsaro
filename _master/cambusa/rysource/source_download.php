<?php
/****************************************************************************
* Name:            source_download.php                                      *
* Project:         Cambusa/rySource                                         *
* Version:         1.69                                                     *
* Description:     Remote file system browser                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include("../sysconfig.php");
include("../rygeneral/unicode.php");
include("../rygeneral/writelog.php");

if(isset($_GET['file'])){
    $file=$_GET['file'];
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
    $ext=strtolower($path_parts["extension"]);
    if(is_file($file)){
        // Download
        if(strpos("|".$safe_extensions."|", "|".$ext."|")!==false){
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
    }
    else{
        writelog("File\r\n\r\n".$file."\r\n\r\nnot found!");
    }
}
exit(0);
?>
