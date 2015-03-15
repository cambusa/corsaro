<?php
/****************************************************************************
* Name:            egorequest_new.php                                       *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."rymonad/monad_lib.php";
include_once $tocambusa."ryego/ego_validate.php";
include_once $tocambusa."ryego/ego_sendmail.php";

try{
    // DETERMINO EMAIL
    if(isset($_POST["email"]))
        $email=ryqEscapize($_POST["email"]);
    else
        $email="";
        
    // DETERMINO APPNAME
    if(isset($_POST["appname"]))
        $appname=ryqEscapize($_POST["appname"]);
    else
        $appname="";

    // DETERMINO ENVNAME
    if(isset($_POST["envname"]))
        $envname=ryqEscapize($_POST["envname"]);
    else
        $envname="";

    // DETERMINO ROLENAME
    if(isset($_POST["rolename"]))
        $rolename=ryqEscapize($_POST["rolename"]);
    else
        $rolename="";

    // CAMPI CUSTOM
    if(isset($_POST["custom"]))
        $custom=ryqEscapize(json_encode($_POST["custom"]));
    else
        $custom="";
        
    $appid="";
    $appdescr="";
    $envid="";
    $roleid="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Richiesta inoltrata: controllare la casella di posta";
    $babel="EGO_MSG_REQSUCCESSFUL"; // NON USO SUBITO $babelcode PERCHE' VERREBBE SOVRASCRITTO NELL'INVIO DELL'EMAIL
    $bpar=array();
    
    if(isset($_COOKIE['_egolanguage'])){
        $global_lastlanguage=$_COOKIE['_egolanguage'];
    }

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
        if($email!=""){
            $emailupper=strtoupper(ryqEscapize($email));
            $appnameupper=strtoupper(ryqEscapize($appname));
            $envnameupper=strtoupper(ryqEscapize($envname));
            $rolenameupper=strtoupper(ryqEscapize($rolename));
            
            // VERIFICO CHE UN UTENTE CON LA STESSA EMAIL NON SIA GIA' PRESENTE NEL SISTEMA
            $sql="SELECT SYSID FROM EGOALIASES WHERE [:UPPER(NAME)]='$emailupper' OR [:UPPER(EMAIL)]='$emailupper'";
            maestro_query($maestro, $sql, $v);
            if(count($v)>0){   // Esistenza email
                $success=0;
                $description="Utente con la stessa email presente nel sistema";
                $babel="EGO_MSG_ALREADYEMAIL";
                throw new Exception( $description );
            }

            // CANCELLO LE RICHIESTE SCADUTE
            $sql="DELETE FROM EGOREGISTRATIONS WHERE [:TIME(REQUESTTIME,1HOURS)]<[:NOW()]";
            maestro_execute($maestro, $sql, false);

            // VERIFICO CHE UNA RICHIESTA NON SIA GIA' PENDENTE
            $sql="SELECT * FROM EGOREGISTRATIONS WHERE [:UPPER(EMAIL)]='$emailupper'";
            maestro_query($maestro, $sql, $v);
            if(count($v)==1){   // Esistenza richiesta
                $success=0;
                $description="Una richiesta è già stata inoltrata";
                $babel="EGO_MSG_ALREADYSENT";
                throw new Exception( $description );
            }
            // VERIFICO L'ESISTENZA DELL'APPLICAZIONE
            $sql="SELECT * FROM EGOAPPLICATIONS WHERE [:UPPER(NAME)]='$appnameupper'";
            maestro_query($maestro, $sql, $v);
            if(count($v)==1){
                $appid=$v[0]["SYSID"];
                $appdescr=$v[0]["DESCRIPTION"];
            }
            else{
                $success=0;
                $description="Applicazione inesistente";
                $babel="EGO_MSG_NOAPPNAME";
                throw new Exception( $description );
            }

            // VERIFICO L'ESISTENZA DELL'AMBIENTE
            $sql="SELECT * FROM EGOENVIRONS WHERE [:UPPER(NAME)]='$envnameupper' AND APPID='$appid'";
            maestro_query($maestro, $sql, $v);
            if(count($v)==1){
                $envid=$v[0]["SYSID"];
            }
            else{
                $success=0;
                $bpar["ENVNAME"]=$envname;
                $description="Ambiente '{1}' inesistente";
                $babel="EGO_MSG_NOENVNAME";
                throw new Exception( $description );
            }

            // VERIFICO L'ESISTENZA DEL RUOLO
            $sql="SELECT * FROM EGOROLES WHERE [:UPPER(NAME)]='$rolenameupper' AND APPID='$appid'";
            maestro_query($maestro, $sql, $v);
            if(count($v)==1){
                $roleid=$v[0]["SYSID"];
            }
            else{
                $success=0;
                $description="Ruolo inesistente";
                $babel="EGO_MSG_NOROLENAME";
                throw new Exception( $description );
            }

            $requestid=monadcall(16,2);
            $reqip=get_ip_address();
            $object="Ego - Richiesta di registrazione nuovo account";
            
            $text="";
            $text.="<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
            $text.="<b>Ego - Richiesta di registrazione nuovo account</b><br><br>";
            $text.="Una richiesta di registrazione &egrave; stata inoltrata per $envnameupper.<br>";
            $text.="Confermando l'autenticit&agrave; della richiesta verranno generati un nuovo account e una password provvisoria la quale sar&agrave; inviata per email.<br>";
            $text.="Al primo accesso tale password dovr&agrave; essere cambiata.<br>";
            $text.="Confermi la <a href='".$url_cambusa."ryego/egorequest_reg.php?reqid=". $requestid."' target='_blank'>registrazione</a> del nuovo account?<br>";
            $text.="</body><html>";
            
            $m=egomail($email, $object, $text, false);
            if($m["success"]==1){
                $SYSID=qv_createsysid($maestro);
                $sql="INSERT INTO EGOREGISTRATIONS (SYSID,EMAIL,APPID,ENVID,ROLEID,REGISTRY,REQUESTID,REQUESTIP,REQUESTTIME) VALUES('$SYSID','$email','$appid','$envid','$roleid','$custom','$requestid','$reqip',[:NOW()])";
                if(!maestro_execute($maestro, $sql, false)){
                    $success=0;
                    $description=$maestro->errdescr;
                    $babel="EGO_MSG_UNDEFINED";
                }
            }
            else{
                $success=0;
                $description="Sending email failed:".$m["description"];
                $babel=$babelcode;
            }
        }
        else{
            $success=0;
            $description="Indirizzo email non impostato";
            $babel="EGO_MSG_NOEMAIL";
        }
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $description=$maestro->errdescr;
        $babel="EGO_MSG_UNDEFINED";
    }
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    if($babel=="EGO_MSG_REQSUCCESSFUL"){
        $babel="EGO_MSG_UNDEFINED";
    }
}

// CHIUDO IL DATABASE
maestro_closedb($maestro);

$babelcode=$babel;
$description=qv_babeltranslate($description, $bpar);

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
print json_encode($j);
?>