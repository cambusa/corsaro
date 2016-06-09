<?php 
/****************************************************************************
* Name:            ryq_util.php                                             *
* Project:         Cambusa/ryQue                                            *
* Version:         1.69                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$global_backslash=0;
function ryqNormalize($var){
    global $path_databases,$global_backslash;
    if($global_backslash==0){
        $global_backslash=intval(@file_get_contents($path_databases."_configs/backslash.par"));
    }
    if($global_backslash==2){
        $var=strtr($var, array("\\'" => "'", "\\\"" => "\"", "\\\\" => "\\"));
    }
    return trim($var);
}
function ryqEscapize($var, $size=0){
    global $path_databases,$global_backslash;
    if($global_backslash==0){
        $global_backslash=intval(@file_get_contents($path_databases."_configs/backslash.par"));
    }
    if($global_backslash==2){
        $var=strtr($var, array("\\'" => "'", "\\\"" => "\"", "\\\\" => "\\"));
    }
    if($size>0){
        $var=substr($var, 0, $size);
    }
    return str_replace("'", "''", trim($var));
}
function ryqUTF8(&$value){
    if($value!=""){
        if(!mb_check_encoding($value, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            $value=utf8_encode($value);
        }
    }
}
?>