<?php
/****************************************************************************
* Name:            rydateadd.php                                            *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."sysconfig.php";    
include_once $tocambusa."rygeneral/datetime.php";

$next="";

// DATA DI RIFERIMENTO
if(isset($_POST["begin"]))
    $begin=$_POST["begin"];
elseif(isset($_GET["begin"]))
    $begin=$_GET["begin"];
else
    $begin=date("Ymd");

if(strlen($begin)>0){
    $begin=substr($begin,0,4)."-".substr($begin,4,2)."-".substr($begin,6,2);

    // GIORNI AGGIUNTIVI
    if(isset($_POST["days"]))
        $days=intval($_POST["days"]);
    elseif(isset($_GET["days"]))
        $days=intval($_GET["days"]);
    else
        $days=1;

    // METODO SOLARE/LAVORATIVO
    if(isset($_POST["method"]))
        $method=intval($_POST["method"]);
    elseif(isset($_GET["method"]))
        $method=intval($_GET["method"]);
    else
        $method=0;
    if($method<0 || $method>1){
        $method=0;
    }

    // CALCOLO NUOVA DATA
    switch($method){
    case 0: // Solare
        $b=date_create($begin);
        $next=date_format(ry_dateadd($b, $days), "Ymd");
        break;
    case 1: // Lavorativo
        $b=date_create($begin);
        $next=date_format(ry_businessadd($b, $days), "Ymd");
        break;
    }
}
$jret=array();
$jret["success"]=1;
$jret["NEXT"]=$next;
print json_encode($jret);
?>