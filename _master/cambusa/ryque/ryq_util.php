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
    global $global_backslash;
    if($global_backslash==2){
        $var=str_replace("\\\\", "\\", $var);
    }
    return strtr(trim($var), array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\"));
}
function ryqEscapize($var, $size=0){
    global $global_backslash;
    $var=trim($var);
    if($global_backslash==2){
        $var=str_replace("\\\\", "\\", $var);
    }
    if($size>0){
        $var=substr($var, 0, $size);
    }
    return str_replace("'", "''", strtr($var, array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\")));
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