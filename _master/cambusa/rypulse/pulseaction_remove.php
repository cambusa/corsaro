<?php
/****************************************************************************
* Name:            pulseaction_remove.php                                   *
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
    include_once $tocambusa."ryquiver/quiversex.php";
    include_once $tocambusa."rygeneral/writelog.php";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Eliminazione effettuata";

    // DETERMINO LA SESSIONID
    if(isset($_POST["sessionid"]))
        $sessionid=ryqEscapize($_POST["sessionid"]);
    else
        $sessionid="";

    // DETERMINO IL SYSID
    if(isset($_POST["SYSID"]))
        $sysid=ryqEscapize($_POST["SYSID"]);
    else
        $sysid="";

    // APRO IL DATABASE
    $maestro_pulse=maestro_opendb("rypulse", false);

    if($maestro_pulse->conn!==false){
        
        // CONTROLLO VALIDITA' SESSIONE
        if(qv_validatesession($maestro_pulse, $sessionid, "pulse")){
        
            $sql="DELETE FROM ENGAGES WHERE SYSID='$sysid'";
            if(!maestro_execute($maestro_pulse, $sql, false)){
                $success=0;
                $description=$maestro_pulse->errdescr;
            }
        }
        else{
            $success=0;
            $description="Sessione non valida";
        }
    }
    else{
        $success=0;
        $description="Impossibile aprire il database";
    }
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro_pulse);
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