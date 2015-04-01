<?php
/****************************************************************************
* Name:            pulseaction_engage.php                                   *
* Project:         Cambusa/ryPulse                                          *
* Version:         1.69                                                     *
* Description:     Scheduler                                                *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
try{
    // CARICO LE LIBRERIE
    if(!isset($tocambusa))
        $tocambusa="../";
    include_once $tocambusa."rymaestro/maestro_execlib.php";
    include_once $tocambusa."rygeneral/writelog.php";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Logout effettuato";

    // DETERMINO LA SESSIONID
    if(isset($_POST["sessionid"]))
        $sessionid=ryqEscapize($_POST["sessionid"]);
    else
        $sessionid="";
        
    if($sessionid!=""){
        // APRO IL DATABASE
        $maestro_pulse=maestro_opendb("rypulse", false);
    
        if($maestro_pulse->conn!==false){
            // CANCELLO LA SESSIONE DALLA CACHE
            $sql="DELETE FROM QVSESSIONS WHERE SESSIONID='$sessionid'";
            maestro_execute($maestro_pulse, $sql, false);
        }

        // CHIUDO IL DATABASE
        maestro_closedb($maestro_pulse);
    }
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
print json_encode($j);
?>