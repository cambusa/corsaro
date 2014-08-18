<?php 
/****************************************************************************
* Name:            egoaction_apps.php                                       *
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
        
    // DETERMINO NUOVA APP
    if(isset($_POST["appnew"]))
        $appnew=ryqEscapize($_POST["appnew"]);
    else
        $appnew="";
    
    $app=strtolower($app);
    $appnew=strtolower($appnew);

    // DETERMINO DESCRIPTION
    if(isset($_POST["descr"]))
        $descr=ryqEscapize($_POST["descr"]);
    else
        $descr="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Operazione effettuata";

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
                if($action=="insert")
                    $description="Applicazione obbligatoria";
                else
                    $description="Seleziona una applicazione";
            }
        }
        if($success){
            if($action=="insert" || $action=="update"){
                if($descr==""){
                    $success=0;
                    $description="Descrizione obbligatoria";
                }
            }
        }
        if($success){
            if($action=="update"){
                if($appnew!=""){
                    if($app!=$appnew){
                        // Determino appid del nuovo valore di app per vedere se esiste già
                        $sql="SELECT SYSID FROM EGOAPPLICATIONS WHERE NAME='$appnew'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1)
                            $appnewid=$r[0]["SYSID"];
                        else
                            $appnewid="";
                    }
                    else{
                        $appnewid="";
                    }
                }
            }
        }
        if($success){
            // BEGIN TRANSACTION
            maestro_begin($maestro);
            
            switch($action){
                case "insert":
                    if($appid==""){
                        $appid=qv_createsysid($maestro);
                        $sql="INSERT INTO EGOAPPLICATIONS(SYSID,NAME,DESCRIPTION) VALUES('$appid','$app','$descr')";
                        maestro_execute($maestro, $sql);
                    }
                    else{
                        $success=0;
                        $description="Nome già in uso";
                    }
                    break;
                case "update":
                    if($appnewid==""){
                        if($appid!=""){
                            $sql="UPDATE EGOAPPLICATIONS SET NAME='$appnew',DESCRIPTION='$descr' WHERE SYSID='$appid'";
                            maestro_execute($maestro, $sql);
                        }
                        else{
                            $success=0;
                            $description="Nome non valido";
                        }
                    }
                    else{
                        $success=0;
                        $description="Nome già in uso";
                    }
                    break;
                case "delete":
                    $sql="SELECT SYSID FROM EGOENVIRONS WHERE APPID='$appid'";
                    maestro_query($maestro, $sql, $a);
                    for($i=0;$i<count($a);$i++){
                        $envid=$a[$i]["SYSID"];
                        maestro_execute($maestro, "DELETE FROM EGOENVIRONS WHERE SYSID='$envid'", false);
                        maestro_execute($maestro, "DELETE FROM EGOENVIRONUSER WHERE ENVIRONID='$envid'", false);
                        maestro_execute($maestro, "DELETE FROM EGOSESSIONS WHERE ENVIRONID='$envid'", false);
                    }
                    maestro_execute($maestro, "DELETE FROM EGOAPPLICATIONS WHERE SYSID='$appid'", false);
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