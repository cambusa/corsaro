<?php
/****************************************************************************
* Name:            pulseaction_engage.php                                   *
* Project:         Cambusa/ryPulse                                          *
* Version:         1.00                                                     *
* Description:     Scheduler                                                *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
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
    $description="Registrazione effettuata";

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

    // DETERMINO NAME
    if(isset($_POST["NAME"]))
        $name=ryqEscapize($_POST["NAME"]);
    else
        $name="";

    // DETERMINO DESCRIPTION
    if(isset($_POST["DESCRIPTION"]))
        $descr=ryqEscapize($_POST["DESCRIPTION"]);
    else
        $descr="";
        
    if($name!="" && $descr!=""){
    
        // APRO IL DATABASE
        $maestro_pulse=maestro_opendb("rypulse", false);
    
        if($maestro_pulse->conn!==false){
        
            // CONTROLLO VALIDITA' SESSIONE
            if(qv_validatesession($maestro_pulse, $sessionid, "pulse")){
            
                // BEGIN TRANSACTION
                maestro_begin($maestro_pulse);
                
                $fields=array("NAME","DESCRIPTION","NOTIFY","PARAMS","TOLERANCE","LATENCY","MINUTES","HOURS","DAYS","WEEK","MONTHS","BUSINESSDAY","ENGAGE","ENABLED","UNATANTUM");
                if($sysid==""){
                    // INSERIMENTO
                    $sysid=qv_createsysid($maestro_pulse);
                    $sql="INSERT INTO ENGAGES(SYSID,RUNNING,NEXTENGAGE";
                    foreach($fields as $key => $value){
                        $sql.=",".$value;
                    }
                    $sql.=") VALUES('$sysid','0',NULL";
                    foreach($fields as $key => $value){
                        if(isset($_POST[$value])){
                            $sql.=",'".ryqEscapize($_POST[$value])."'";
                        }
                    }
                    $sql.=")";
                }
                else{
                    // MODIFICA
                    $sql="UPDATE ENGAGES SET RUNNING='0',NEXTENGAGE=NULL";
                    foreach($fields as $key => $value){
                        if(isset($_POST[$value])){
                            $sql.=",$value='".ryqEscapize($_POST[$value])."'";
                        }
                    }
                    $sql.=" WHERE SYSID='$sysid'";
                }
                if(!maestro_execute($maestro_pulse, $sql, false)){
                    $success=0;
                    $description=$maestro_pulse->errdescr;
                }
                if($success){
                    // COMMIT TRANSACTION
                    maestro_commit($maestro_pulse);
                }
                else{
                    // ROLLBACK TRANSACTION
                    maestro_rollback($maestro_pulse);
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
    else{
        $success=0;
        $description="Nome e descrizione obbligatori";
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