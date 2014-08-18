<?php 
/****************************************************************************
* Name:            ryq_request.php                                          *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."sysconfig.php";
include_once $tocambusa."ryquiver/quiversex.php";

// INIZIALIZZO LE VARIABILI IN USCITA
$success=1;
$description="Operazione effettuata";
$reqid="";
$env_provider="";
$env_lenid="";

$r=array();

if(isset($_POST["env"]) && isset($_POST["sessionid"])){

    // RISOLVO I VALORI IN INGRESSO
    $env_name=strtolower($_POST["env"]);
    $sessionid=$_POST["sessionid"];

    // APRO IL DATABASE
    $maestro=maestro_opendb($env_name, false);

    // VERIFICO IL BUON ESITO DELL'APERTURA
    if($maestro->conn!==false){

        // DETERMINO PROVIDER E LUNGHEZZA SYSID
        $env_provider=$maestro->provider;
        $env_lenid=$maestro->lenid;
        
        // COMUNICO DI FARE UNA VALIDAZIONE DI SICUREZZA
        if($maestro->quiver)
            $context="quiver";
        else
            $context="";
            
        // GESTIONE SESSIONE SPECIALE IN SOLA LETTURA
        if($sessionid==$ryque_sessionid){
            $context="ryque";
        }

        // VALIDAZIONE CODICE DI SESSIONE
        if(qv_validatesession($maestro, $sessionid, $context)){
            if($maestro->monad){
                // MI FACCIO RESTITUIRE UN SYSID UNIVOCO
                $reqid=qv_createsysid($maestro);
                for($i=1; $i<=2; $i++){
                    $reqid.=strtoupper(substr("0000".base_convert(intval(rand(0,1679615)), 10, 36),-4));
                }
            }
            else{
                // MI FACCIO RESTITUIRE UN SYSID UNIVOCO
                $reqid=monadcall(16, 2);
            }
            $buff=$env_name;
            $fn="requests/".$reqid.".req";
            $fp=fopen($fn, "w");
            fwrite($fp, $buff);
            fclose($fp);
        }
        else{
            $success=0;
            $description="Sessione non valida";
        }
    }
    else{
        $success=0;
        $description="Connessione non valida";
    }
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
// Uscita JSON
$r["success"]=$success;
$r["description"]=$description;
$r["reqid"]=$reqid;
$r["provider"]=$env_provider;
$r["lenid"]=$env_lenid;
print json_encode($r);
?>