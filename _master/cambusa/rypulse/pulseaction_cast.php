<?php
/****************************************************************************
* Name:            pulseaction_cast.php                                     *
* Project:         Cambusa/ryPulse                                          *
* Version:         1.69                                                     *
* Description:     Scheduler                                                *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
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
    include_once $tocambusa."rygeneral/datetime.php";
    include_once $tocambusa."ryego/ego_sendmail.php";
    include_once $tocambusa."ryvlad/ryvlad.php";
    include_once $tocambusa."ryquiver/_quiver.php";
    include_once $tocambusa."rypaper/rypaper.php";
    include_once $tocambusa."rypulse/pulse_util.php";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Azione eseguita";

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
        
            // LEGGO L'AZIONE SU DATABASE
            $sql="SELECT * FROM ENGAGES WHERE SYSID='$sysid'";
            maestro_query($maestro_pulse, $sql, $r);
            if(count($r==1)){
                // SCRIPT DA LANCIARE
                $script=$r[0]["ENGAGE"];
                $script=str_replace("@customize/", $path_customize, $script);
                $script=str_replace("@cambusa/", "../", $script);
                $script=str_replace("@databases/", $path_databases, $script);
                
                // PARAMETRI DA PASSARE ALLO SCRIPT
                $PARAMS=array();
                $params=$r[0]["PARAMS"];
                if($params!=""){
                    if($json=json_decode($params)){
                        $PARAMS=jsonObjectToArray($json);
                    }
                    else{
                        // CHIUDO IL DATABASE
                        maestro_closedb($maestro_pulse);
                        throw new Exception( "Parametri non corretti" );
                    }
                }
                    
                // ELENCO INDIRIZZI DI NOTIFICA
                $notify=trim($r[0]["NOTIFY"]);
            
                // ADESSO
                $now=date("YmdHis");
                
                if(is_file($script)){
                    try{
                        include_once $script;
                        // ESEGUO
                        pulse_execute($maestro_pulse, $sysid, $script, $notify, $now, 0, $success, $description);
                    }
                    catch(Exception $e){
                        $success=0;
                        $description=$e->getMessage();
                        writelog("pulse_heart.php:\r\n$description");
                    }
                }
                else{
                    $success=0;
                    $description="File ".$script." doesn't exist";
                    writelog("pulse_heart.php:\r\n$description");
                }
            }
        }
        else{
            $success=0;
            $description="Sessione non valida";
            writelog("pulse_heart.php [$script]\r\n$description");
        }
    }
    else{
        $success=0;
        $description="Impossibile aprire il database";
        writelog("pulse_heart.php [$script]\r\n$description");
    }
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro_pulse);
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    writelog("pulse_heart.php [$script]\r\n$description");
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
print json_encode($j);
?>