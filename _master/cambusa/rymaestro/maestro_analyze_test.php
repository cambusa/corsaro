<?php 
/****************************************************************************
* Name:            maestro_analyze_test.php                                 *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.00                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_upgradelib.php";

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
else
    $sessionid="";
    
if(isset($_POST["env"]))
    $env=$_POST["env"];
else
    $env=="";

// INIZIALIZZO IL DOCUMENTO IN USCITA
$json="";
    
if(ext_validatesession($sessionid, false, "maestro")){

    // SE IL DATABASE E' SQLITE, CONTROLLO CHE ESISTA E SE NON ESISTE LO CREO
    maestro_checklite($env);

    // APERTURA DATABASE
    $maestro=maestro_opendb($env);

    if($maestro->conn!==false){
        $json=MaestroAnalyze($maestro, $s, $d);
    }

    // CHIUSURA DATABASE
    maestro_closedb($maestro);
}
print $json;
?>