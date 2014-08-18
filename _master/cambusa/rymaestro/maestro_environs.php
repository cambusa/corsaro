<?php 
/****************************************************************************
* Name:            maestro_environs.php                                     *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.00                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include("../sysconfig.php");
$direnvirons=$path_databases."_environs/";

if(isset($_POST["maestro"]))
    $maestro=$_POST["maestro"];
else
    $maestro=="";

$j=array();
$m=glob($direnvirons."*.php");
for($i=0;$i<count($m);$i++){
    $b=basename($m[$i]);
    $b=substr($b,0,strlen($b)-4);
    $env_maestro="";
    $env_provider="";
    include($m[$i]);
    if(strpos("|sqlite|access|mysql|oracle|sqlserver|db2odbc|", "|".$env_provider."|")!==false){
        if($maestro=="" || ($env_maestro!="" && $env_maestro==$maestro))
            $j[]=$b;
    }
}

print json_encode($j);
?>