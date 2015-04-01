<?php 
/****************************************************************************
* Name:            maestro_analyze_test.php                                 *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.69                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
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