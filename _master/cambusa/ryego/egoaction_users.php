<?php 
/****************************************************************************
* Name:            egoaction_users.php                                      *
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

    // DETERMINO USER
    if(isset($_POST["user"]))
        $user=ryqEscapize($_POST["user"]);
    else
        $user="";

    // DETERMINO ALIAS
    if(isset($_POST["alias"]))
        $alias=ryqEscapize($_POST["alias"]);
    else
        $alias="";

    // DETERMINO NUOVO ALIAS
    if(isset($_POST["aliasnew"]))
        $aliasnew=ryqEscapize($_POST["aliasnew"]);
    else
        $aliasnew="";

    // DETERMINO EMAIL
    if(isset($_POST["email"]))
        $email=ryqEscapize($_POST["email"]);
    else
        $email="";

    // DETERMINO DEMIURGE
    if(isset($_POST["demiurge"]))
        $demiurge=ryqEscapize($_POST["demiurge"]);
    else
        $demiurge="0";

    // DETERMINO ADMINISTRATOR
    if(isset($_POST["admin"]))
        $admin=ryqEscapize($_POST["admin"]);
    else
        $admin="0";

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
        
        if($success){
            // CONTROLLI DI CORRETTEZZA REPERIMENTO SYSID
            switch($action){
                case "newuser":
                case "reset":
                    // Controllo che user sia passato
                    if($user!=""){
                        // Determino userid
                        $sql="SELECT USERID FROM EGOALIASES WHERE [:UPPER(NAME)]='".strtoupper($user)."'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1)
                            $userid=$r[0]["USERID"];
                        else
                            $userid="";
                    }
                    else{
                        $success=0;
                        $description="Alias obbligatorio";
                    }
                    break;  
                case "newalias":
                    // Controllo che user sia passato
                    if($user!=""){
                        // Determino userid
                        $sql="SELECT USERID FROM EGOALIASES WHERE [:UPPER(NAME)]='".strtoupper($user)."'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1)
                            $userid=$r[0]["USERID"];
                        else
                            $userid="";
                    }
                    else{
                        $success=0;
                        $description="Nessun utente selezionato";
                    }
                    // Controllo che alias sia passato
                    if($alias!=""){
                        // Determino aliasid
                        $sql="SELECT SYSID FROM EGOALIASES WHERE [:UPPER(NAME)]='".strtoupper($alias)."'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1)
                            $aliasid=$r[0]["SYSID"];
                        else
                            $aliasid="";
                    }
                    else{
                        $success=0;
                        $description="Alias obbligatorio";
                    }
                    break;
                case "update":
                    // Controllo che alias sia passato
                    if($alias!=""){
                        // Determino aliasid
                        $sql="SELECT SYSID FROM EGOALIASES WHERE [:UPPER(NAME)]='".strtoupper($alias)."'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1)
                            $aliasid=$r[0]["SYSID"];
                        else
                            $aliasid="";
                    }
                    else{
                        $success=0;
                        $description="Alias obbligatorio";
                    }
                    if($aliasnew!=""){
                        if(strtolower($alias)!=strtolower($aliasnew)){
                            // Determino aliasid del nuovo valore di alias per vedere se esiste gi�
                            $sql="SELECT SYSID FROM EGOALIASES WHERE [:UPPER(NAME)]='".strtoupper($aliasnew)."'";
                            maestro_query($maestro, $sql, $r);
                            if(count($r)==1)
                                $aliasnewid=$r[0]["SYSID"];
                            else
                                $aliasnewid="";
                        }
                        else{
                            $aliasnewid="";
                        }
                    }
                    else{
                        $success=0;
                        $description="Alias obbligatorio";
                    }
                    break;
                case "activate":
                    // Controllo che user sia passato
                    if($user!=""){
                        // Determino userid
                        $sql="SELECT EGOALIASES.USERID AS USERID,EGOUSERS.ACTIVE AS ACTIVE FROM EGOALIASES INNER JOIN EGOUSERS ON EGOUSERS.SYSID=EGOALIASES.USERID WHERE [:UPPER(EGOALIASES.NAME)]='".strtoupper($user)."'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1){
                            $userid=$r[0]["USERID"];
                            $active=$r[0]["ACTIVE"];
                            $active=1-$active;
                            if($active==0){
                                // Controllo che la disattivazione non coinvolga tutti i demiurghi
                                $sql="SELECT COUNT(1) AS DEMIURGECOUNT FROM EGOALIASES INNER JOIN EGOUSERS ON EGOUSERS.SYSID=EGOALIASES.USERID WHERE EGOALIASES.USERID<>'$userid' AND EGOALIASES.DEMIURGE=1 AND EGOUSERS.ACTIVE=1";
                                maestro_query($maestro, $sql, $r);
                                if($r[0]["DEMIURGECOUNT"]==0){
                                    $success=0;
                                    $description="Impossibile eliminare l'ultimo demiurgo";
                                }
                            }
                        }
                        else{
                            $userid="";
                        }
                    }
                    else{
                        $success=0;
                        $description="Alias obbligatorio";
                    }
                    break;  
                case "delete":
                    // Controllo che alias sia passato
                    if($alias!=""){
                        // Determino aliasid
                        $sql="SELECT SYSID,MAIN FROM EGOALIASES WHERE [:UPPER(NAME)]='".strtoupper($alias)."'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1){
                            $aliasid=$r[0]["SYSID"];
                            if($r[0]["MAIN"]==1){
                                $success=0;
                                $description="Non si pu� eliminare l'alias principale";
                            }
                        }
                        else{
                            $aliasid="";
                        }
                    }
                    else{
                        $success=0;
                        $description="Alias obbligatorio";
                    }
                    break;
                case "deleteall":
                    break;
                default:
                    $success=0;
                    $description="Nessuna azione riconosciuta";
            }
        }
        if($success){
            // CONTROLLI DI CORRETTEZZA DEL FLAG Demiurge
            if($demiurge!="0" && $demiurge!="1"){
                $success=0;
                $description="Valore di 'demiurge' non previsto";
            }
        }
        if($success){
            // CONTROLLI DI CORRETTEZZA DEL FLAG Admin
            if($admin!="0" && $admin!="1"){
                $success=0;
                $description="Valore di 'admin' non previsto";
            }
        }
        if($success){
            // BEGIN TRANSACTION
            maestro_begin($maestro);

            switch($action){
                case "newuser":
                    if($userid==""){
                        // Reperisco la password predefinita
                        $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME='default'";
                        maestro_query($maestro, $sql, $r);
                        $pwd=sha1($r[0]["VALUE"]);
                        $userid=qv_createsysid($maestro);
                        $sql="INSERT INTO EGOUSERS(SYSID,PASSWORD,ACTIVE) VALUES('$userid','$pwd','1')";
                        maestro_execute($maestro, $sql);
                        if($success){
                            $aliasid=qv_createsysid($maestro);
                            $sql="INSERT INTO EGOALIASES(SYSID,USERID,NAME,EMAIL,MAIN,DEMIURGE,ADMINISTRATOR) VALUES('$aliasid','$userid','$user','$email','1','$demiurge','$admin')";
                            maestro_execute($maestro, $sql);
                        }
                    }
                    else{
                        $success=0;
                        $description="Alias gi� in uso";
                    }
                    break;
                case "newalias":
                    if($aliasid==""){
                        if($userid!=""){
                            $aliasid=qv_createsysid($maestro);
                            $sql="INSERT INTO EGOALIASES(SYSID,USERID,NAME,EMAIL,MAIN,DEMIURGE,ADMINISTRATOR) VALUES('$aliasid','$userid','$alias','$email','0','$demiurge','$admin')";
                            maestro_execute($maestro, $sql);
                        }
                        else{
                            $success=0;
                            $description="Utente non valido";
                        }
                    }
                    else{
                        $success=0;
                        $description="Alias gi� in uso";
                    }
                    break;
                case "update":
                    if($aliasnewid==""){
                        if($aliasid!=""){
                            $sql="UPDATE EGOALIASES SET NAME='$aliasnew',EMAIL='$email',DEMIURGE=$demiurge,ADMINISTRATOR=$admin WHERE SYSID='$aliasid'";
                            maestro_execute($maestro, $sql);
                        }
                        else{
                            $success=0;
                            $description="Alias non valido";
                        }
                    }
                    else{
                        $success=0;
                        $description="Alias gi� in uso";
                    }
                    break;
                case "reset":
                    // Reperisco la password predefinita
                    $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME='default'";
                    maestro_query($maestro, $sql, $r);
                    $pwd=sha1($r[0]["VALUE"]);
                    $sql="UPDATE EGOUSERS SET PASSWORD='$pwd',LASTCHANGE=NULL WHERE SYSID='$userid'";
                    maestro_execute($maestro, $sql);
                    break;
                case "activate":
                    $sql="UPDATE EGOUSERS SET ACTIVE=$active WHERE SYSID='$userid'";
                    maestro_execute($maestro, $sql);
                    break;
                case "delete":
                    $sql="DELETE FROM EGOALIASES WHERE SYSID='$aliasid'";
                    maestro_execute($maestro, $sql);
                    break;
                case "deleteall":
                    $sql="SELECT SYSID FROM EGOUSERS WHERE ACTIVE=0";
                    maestro_query($maestro, $sql, $u);
                    for($i=0;$i<count($u);$i++){
                        $userid=$u[$i]["SYSID"];
                        $sql="SELECT SYSID FROM EGOALIASES WHERE USERID='$userid'";
                        maestro_query($maestro, $sql, $a);
                        for($j=0;$j<count($a);$j++){
                            $aliasid=$a[$j]["SYSID"];
                            maestro_execute($maestro, "DELETE FROM EGOSESSIONS WHERE ALIASID='$aliasid'");
                            maestro_execute($maestro, "DELETE FROM EGOALIASES WHERE SYSID='$aliasid'");
                        }
                        maestro_execute($maestro, "DELETE FROM EGOENVIRONUSER WHERE USERID='$userid'");
                        maestro_execute($maestro, "DELETE FROM EGOUSERS WHERE SYSID='$userid'");
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