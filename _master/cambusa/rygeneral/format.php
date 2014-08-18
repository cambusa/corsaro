<?php
/****************************************************************************
* Name:            format.php                                               *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$format_trdate=array("-", ":", "T", " ", "'", ".");
function formatta_numero($VALUE, $NUMDEC){
    $VALUE=strval($VALUE);
    if(strpos($VALUE, "-")!==false){
        $SIGNUM="-";
        $VALUE=str_replace("-", "", $VALUE);
    }
    else{
        $SIGNUM="";
    }
        
    $p=strpos($VALUE, ".");
    if($p!==false){
        $INT=substr($VALUE, 0, $p);
        $DEC=substr($VALUE, $p+1);
    }
    else{
        $INT=$VALUE;
        $DEC="";
        $p=strlen($INT);
    }
    if($INT==""){
        $INT="0";
    }
    for($i=$p-3;$i>0;$i-=3){
        $INT=substr($INT, 0, $i).".".substr($INT, $i);
    }
    if($NUMDEC==0){
        $VALUE=$SIGNUM.$INT;
    }
    else{
        $DEC=substr($DEC."0000000", 0, $NUMDEC);
        $VALUE=$SIGNUM.$INT.",".$DEC;
    }
    return $VALUE;
}
function formatta_data($data){
    global $format_trdate;
    $data=str_replace($format_trdate, "", $data);
    return substr($data,6,2)."/".substr($data,4,2)."/".substr($data,0,4);
}
?>