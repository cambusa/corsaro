<?php 
/****************************************************************************
* Name:            ryzero.php                                               *
* Project:         Cambusa/ryQue                                            *
* Version:         1.69                                                     *
* Description:     Subset Sum Problem Remedy                                *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
if(is_file($tocambusa."ryque/ryq_gauge.php"))
    include_once $tocambusa."ryque/ryq_gauge.php";
elseif(is_file($tocambusa."ryque/ryq_gauge.phar"))
    include_once "phar://".$tocambusa."ryque/ryq_gauge.phar/ryq_gauge.php";
else
    include_once $tocambusa."ryque/ryq_gaugeminus.php";

include_once $tocambusa."sysconfig.php";
include_once $tocambusa."rygeneral/writelog.php";

$reqid=$_POST['reqid'];

if(isset($_POST['gauge'])){
    $gauge=$_POST['gauge'];
    $values=$_POST['values'];
    $refs=$_POST['refs'];
    $s=zerosearch($reqid, array("gauge" => $gauge, "exhaustive" => 2), $values, $refs);
}
else{
    if(is_file("requests/".$reqid.".sts")){
        $s=zerosearch($reqid);
    }
    else{
        $s=array();
    }
}

sort($s);
print json_encode($s);
?>