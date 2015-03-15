<?php
/****************************************************************************
* Name:            egorequest_reg.php                                       *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.61                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."ryego/ego_sendmail.php";

try{
    // DETERMINO REQUESTID
    if(isset($_POST["reqid"]))
        $reqid=ryqEscapize($_POST["reqid"]);
    elseif(isset($_GET["reqid"]))
        $reqid=ryqEscapize($_GET["reqid"]);
    else
        $reqid="";

    $userid="";
    $appid="";
    $appname="";
    $envid="";
    $envname="";
    $roleid="";
    $rolename="";
    
    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Account registrato: controllare la casella di posta";
    $babel="EGO_MSG_REGSUCCESSFUL";   // NON USO SUBITO $babelcode PERCHE' VERREBBE SOVRASCRITTO NELL'INVIO DELL'EMAIL

    if(isset($_COOKIE['_egolanguage'])){
        $global_lastlanguage=$_COOKIE['_egolanguage'];
    }

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
    
        // CANCELLO LE RICHIESTE SCADUTE
        $sql="DELETE FROM EGOREGISTRATIONS WHERE [:TIME(REQUESTTIME,1HOURS)]<[:NOW()]";
        maestro_execute($maestro, $sql, false);
        
        // LEGGO LA RICHIESTA DI REGISTRAZIONE
        $sql="SELECT * FROM EGOREGISTRATIONS WHERE REQUESTID='$reqid'";
        maestro_query($maestro, $sql, $v);
        if(count($v)>0){
            $email=$v[0]["EMAIL"];
            $appid=$v[0]["APPID"];
            $envid=$v[0]["ENVID"];
            $roleid=$v[0]["ROLEID"];
            $custom=$v[0]["REGISTRY"];
            if(substr($custom,0,1)=="{"){
                $custom=json_decode($custom, true);
            }
        }
        else{
            $success=0;
            $description="Richiesta di registrazione inesistente o scaduta";
            $babel="EGO_MSG_NOREQID";
            throw new Exception( $description );
        }

        // VERIFICO L'ESISTENZA DELL'APPLICAZIONE
        $sql="SELECT * FROM EGOAPPLICATIONS WHERE SYSID='$appid'";
        maestro_query($maestro, $sql, $v);
        if(count($v)==1){
            $appname=$v[0]["NAME"];
        }
        else{
            $success=0;
            $description="Applicazione inesistente";
            $babel="EGO_MSG_NOAPPID";
            throw new Exception( $description );
        }

        // VERIFICO L'ESISTENZA DELL'AMBIENTE
        $sql="SELECT * FROM EGOENVIRONS WHERE SYSID='$envid' AND APPID='$appid'";
        maestro_query($maestro, $sql, $v);
        if(count($v)==1){
            $envname=$v[0]["NAME"];
        }
        else{
            $success=0;
            $description="Ambiente inesistente";
            $babel="EGO_MSG_NOENVID";
            throw new Exception( $description );
        }

        // VERIFICO L'ESISTENZA DEL RUOLO
        $sql="SELECT * FROM EGOROLES WHERE SYSID='$roleid' AND APPID='$appid'";
        maestro_query($maestro, $sql, $v);
        if(count($v)==1){
            $rolename=$v[0]["NAME"];
        }
        else{
            $success=0;
            $description="Ruolo inesistente";
            $babel="EGO_MSG_NOROLEID";
            throw new Exception( $description );
        }

        // DETERMINO LA LUNGHEZZA MINIMA DELLA PASSWORD
        $minlen=4;
        $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME='minlen'";
        maestro_query($maestro, $sql, $v);
        if(count($v)>0){
            $minlen=intval($v[0]["VALUE"]);
        }

        // GENERO UN USERID
        $userid=qv_createsysid($maestro);
        
        // CHIAMO L'EVENTUALE TRIGGER DI REGISTRAZIONE 
        $trigger_reg=$path_customize."ryego/custtriggerreg.php";
        if(is_file($trigger_reg)){
            include_once $trigger_reg;
            $funct="ego_triggerreg";
            if(function_exists($funct)){
                if(!$funct($userid, $email, $appname, $envname, $rolename, $custom, $b, $f)){
                    $success=0;
                    $description=$f;
                    $babel=$b;
                    throw new Exception( $description );
                }
            }
        }
        
        // CHIUDO E RIAPRO IL DATABASE (SICURAMENTE INDISPENSABILE CON SQLITE)
        maestro_closedb($maestro);
        $maestro=maestro_opendb("ryego");
        
        // BEGIN TRANSACTION
        maestro_begin($maestro);
    
        // GENERO UNA PASSWORD
        $pwd="";
        while(strlen($pwd)<$minlen){
            $pwd.=substr("0000".base_convert(intval(rand(0,1679615)), 10, 36),-4);
        }  
        $pwd=substr($pwd, 0, $minlen);
        $sha1pwd=sha1($pwd);

        // INSERISCO IN EGOUSERS
        $sql="INSERT INTO EGOUSERS(SYSID,PASSWORD,ACTIVE,LASTCHANGE) VALUES('$userid', '$sha1pwd', '1', [:NOW()])";
        if(!maestro_execute($maestro, $sql, false)){
            $success=0;
            $description=$maestro->errdescr;
            $babel="EGO_MSG_UNDEFINED";
            throw new Exception( $description );
        }
        
        // INSERISCO IN EGOALIASES
        $aliasid=qv_createsysid($maestro);
        $sql="INSERT INTO EGOALIASES(SYSID,USERID,NAME,EMAIL,MAIN,DEMIURGE,ADMINISTRATOR) VALUES('$aliasid', '$userid', '$email', '$email', 1, 0, 0)";
        if(!maestro_execute($maestro, $sql, false)){
            $success=0;
            $description=$maestro->errdescr;
            $babel="EGO_MSG_UNDEFINED";
            throw new Exception( $description );
        }
        
        // INSERISCO IN EGOENVIRONUSER
        $usenid=qv_createsysid($maestro);
        $sql="INSERT INTO EGOENVIRONUSER(SYSID,ENVIRONID,USERID) VALUES('$usenid', '$envid', '$userid')";
        if(!maestro_execute($maestro, $sql, false)){
            $success=0;
            $description=$maestro->errdescr;
            $babel="EGO_MSG_UNDEFINED";
            throw new Exception( $description );
        }
        
        // INSERISCO IN EGOROLEUSER
        $usroid=qv_createsysid($maestro);
        $sql="INSERT INTO EGOROLEUSER(SYSID,ROLEID,USERID) VALUES('$usroid', '$roleid', '$userid')";
        if(!maestro_execute($maestro, $sql, false)){
            $success=0;
            $description=$maestro->errdescr;
            $babel="EGO_MSG_UNDEFINED";
            throw new Exception( $description );
        }
        
        // CANCELLO LA RICHIESTA
        $sql="DELETE FROM EGOREGISTRATIONS WHERE REQUESTID='$reqid'";
        maestro_execute($maestro, $sql, false);

        $object="Ego - Registrazione account";
        
        $text="";
        $text.="<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
        $text.="<b>Ego - Nuovo account registrato</b><br><br>";
        $text.="La richiesta di registrazione del nuovo account <b>$email</b> &egrave; stata completata.<br>";
        $text.="Utilizzare la password provvisoria ( <b>$pwd</b> ) per accedere al sistema e cambiarla.<br>";
        $text.="</body><html>";
        
        $m=egomail($email, $object, $text, false);
        if($m["success"]==0){
            $success=0;
            $description="Sending email failed:".$m["description"];
            $babel=$babelcode;
            throw new Exception( $description );
        }
        
        // COMMIT TRANSACTION
        maestro_commit($maestro);
        
        // CHIUDO IL DATABASE
        maestro_closedb($maestro);
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $description=$maestro->errdescr;
        $babel="EGO_MSG_UNDEFINED";
    }
}
catch(Exception $e){
    // ROLLBACK TRANSACTION
    maestro_rollback($maestro);
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);

    $success=0;
    $description=$e->getMessage();
    if($babel=="EGO_MSG_REGSUCCESSFUL"){
        $babel="EGO_MSG_UNDEFINED";
    }
}

$babelcode=$babel;
$description=qv_babeltranslate($description);

print "<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
print $description;
print "</body><html>";
?>
