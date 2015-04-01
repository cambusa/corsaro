<?php 
/****************************************************************************
* Name:            egoaction_roleuser.php                                   *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."ryego/ego_util.php";

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
        
    // DETERMINO IL RUOLO
    if(isset($_POST["role"]))
        $role=ryqEscapize($_POST["role"]);
    else
        $role="";
        
    // DETERMINO L'ELENCO UTENTI
    if(isset($_POST["users"]))
        $users=ryqEscapize($_POST["users"]);
    else
        $users="";
    
    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Operazione effettuata";
    $babelcode="EGO_MSG_SUCCESSFUL";
    $args=array();

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){

        // CONTROLLO VALIDITA' SESSIONE
        if(ego_validatesession($maestro, $sessionid, true)==false){
            $success=0;
            $description="Sessione non valida";
            $babelcode="EGO_MSG_INVALIDSESSION";
        }
        
        $v=explode("|",$users);
        if(count($v)>0){
            $listin="";
            for($i=0;$i<count($v);$i++){
                if($i>0)
                    $listin.=",";
                $listin.="'".$v[$i]."'";
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
                    $babelcode="EGO_MSG_SELECTAPP";
                }
            }
            if($success){
                if($role!=""){
                    // Determino roleid
                    $sql="SELECT SYSID FROM EGOROLES WHERE APPID='$appid' AND NAME='$role'";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)==1)
                        $roleid=$r[0]["SYSID"];
                    else
                        $roleid="";
                }
                else{
                    $roleid="";
                }
                if($roleid==""){
                    $success=0;
                    $description="Ruolo non valido [$role]";
                    $babelcode="EGO_MSG_INVALIDROLE";
                    $args["ROLE"]=$role;
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
                            $sql="INSERT INTO EGOROLEUSER(SYSID,ROLEID,USERID) VALUES('$sysid','$roleid','$userid')";
                            maestro_execute($maestro, $sql);
                        }
                        break;
                    case "remove":
                        // Determino la vera lista dei SYSID utente
                        $sql="SELECT USERID FROM EGOROLEUSER WHERE SYSID IN ($listin)";
                        maestro_query($maestro, $sql, $r);
                        for($i=0;$i<count($r);$i++){
                            $userid=$r[$i]["USERID"];
                            $sql="DELETE FROM EGOROLEUSER WHERE ROLEID='$roleid' AND USERID='$userid'";
                            maestro_execute($maestro, $sql);
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
            $success=0;
            $description="Seleziona un utente";
            $babelcode="EGO_MSG_SELECTUSER";
        }
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $description=$maestro->errdescr;
        $babelcode="EGO_MSG_UNDEFINED";
    }

    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    $babelcode="EGO_MSG_UNDEFINED";
}

$description=qv_babeltranslate($description, $args);

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=$description;
array_walk_recursive($j, "ego_escapize");
print json_encode($j);
?>