<?php 
/****************************************************************************
* Name:            egoaction_envuser.php                                    *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."ryque/ryq_util.php";

try{
    // DETERMINO LA SESSIONID
    if(isset($_POST["sessionid"]))
        $sessionid=ryqEscapize($_POST["sessionid"]);
    else
        $sessionid="";

    // DETERMINO L'AZIONE
    if(isset($_POST["action"]))
        $action=ryqEscapize($_POST["action"]);
    else
        $action="";

    // DETERMINO APP
    if(isset($_POST["app"]))
        $app=ryqEscapize($_POST["app"]);
    else
        $app="";
        
    // DETERMINO L'AMBIENTE
    if(isset($_POST["env"]))
        $env=ryqEscapize($_POST["env"]);
    else
        $env="";
        
    // DETERMINO L'ELENCO UTENTI
    if(isset($_POST["users"]))
        $users=ryqEscapize($_POST["users"]);
    else
        $users="";
    
    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Operazione effettuata";

    $v=explode("|",$users);
    if(count($v)>0){
        $listin="";
        for($i=0;$i<count($v);$i++){
            if($i>0)
                $listin.=",";
            $listin.="'".$v[$i]."'";
        }
        // APRO IL DATABASE
        $maestro=maestro_opendb("ryego");
        if($maestro->conn!==false){

            // CONTROLLO VALIDITA' SESSIONE
            if(ego_validatesession($maestro, $sessionid)==false){
                $success=0;
                $description="Sessione non valida";
            }
            
            // CONTROLLI DI CORRETTEZZA REPERIMENTO SYSID
            if($success){
                if($app!=""){
                    // Determino appid
                    $sql="SELECT SYSID FROM EGOAPPLICATIONS WHERE NAME='$app'";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)==1)
                        $appid=$r[0]["SYSID"];
                    else
                        $appid="";
                }
                else{
                    $success=0;
                    $description="Seleziona una applicazione";
                }
            }
            if($success){
                if($env!=""){
                    // Determino envid
                    $sql="SELECT SYSID FROM EGOENVIRONS WHERE APPID='$appid' AND NAME='$env'";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)==1)
                        $envid=$r[0]["SYSID"];
                    else
                        $envid="";
                }
                else{
                    $envid="";
                }
                if($envid==""){
                    $success=0;
                    $description=$env." Ambiente non valido";
                }
            }
            if($success){
                // BEGIN TRANSACTION
                maestro_begin($maestro);

                switch($action){
                    case "add":
                        // Determino la vera lista dei SYSID utente
                        $sql="SELECT USERID FROM EGOALIASES WHERE SYSID IN ($listin)";
                        maestro_query($maestro, $sql, $r);
                        for($i=0;$i<count($r);$i++){
                            $userid=$r[$i]["USERID"];
                            $sysid=qv_createsysid($maestro);
                            $sql="INSERT INTO EGOENVIRONUSER(SYSID,ENVIRONID,USERID) VALUES('$sysid','$envid','$userid')";
                            maestro_execute($maestro, $sql);
                        }
                        break;
                    case "remove":
                        // Determino la vera lista dei SYSID utente
                        $sql="SELECT USERID FROM EGOENVIRONUSER WHERE SYSID IN ($listin)";
                        maestro_query($maestro, $sql, $r);
                        for($i=0;$i<count($r);$i++){
                            $userid=$r[$i]["USERID"];
                            $sql="DELETE FROM EGOENVIRONUSER WHERE ENVIRONID='$envid' AND USERID='$userid'";
                            maestro_execute($maestro, $sql, false);
                        }
                        break;
                }
                if($success){
                    // COMMIT TRANSACTION
                    maestro_commit($maestro);
                }
                else{
                    // ROLLBACK TRANSACTION
                    maestro_rollback($maestro);
                }
            }
        }
        else{
            // CONNESSIONE FALLITA
            $success=0;
            $description=$maestro->errdescr;
        }

        // CHIUDO IL DATABASE
        maestro_closedb($maestro);
    }
    else{
        $success=0;
        $description="Nessun utente selezionato";
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