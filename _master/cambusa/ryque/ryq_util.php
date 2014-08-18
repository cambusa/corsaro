<?php 
/****************************************************************************
* Name:            ryq_util.php                                             *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function ryqNormalize($var){
    return strtr(trim($var), array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\"));
}
function ryqEscapize($var, $size=0){
    $var=trim($var);
    if($size>0){
        $var=substr($var, 0, $size);
    }
    return str_replace("'", "''", strtr($var, array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\")));
}
?>