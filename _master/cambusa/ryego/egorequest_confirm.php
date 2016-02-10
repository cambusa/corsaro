<?php
/****************************************************************************
* Name:            egorequest_confirm.php                                   *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."ryego/ego_sendmail.php";

try{
    // DETERMINO USER
    if(isset($_POST["aliasid"]))
        $aliasid=ryqEscapize($_POST["aliasid"]);
    elseif(isset($_GET["aliasid"]))
        $aliasid=ryqEscapize($_GET["aliasid"]);
    else
        $aliasid="";

    // DETERMINO REQUESTID
    if(isset($_POST["reqid"]))
        $reqid=ryqEscapize($_POST["reqid"]);
    elseif(isset($_GET["reqid"]))
        $reqid=ryqEscapize($_GET["reqid"]);
    else
        $reqid="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Password reimpostata: controllare la casella di posta";
    $babel="EGO_MSG_RESETSUCCESSFUL";   // NON USO SUBITO $babelcode PERCHE' VERREBBE SOVRASCRITTO NELL'INVIO DELL'EMAIL

    if(isset($_COOKIE['_egolanguage'])){
        $global_lastlanguage=$_COOKIE['_egolanguage'];
    }

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
        $sql="SELECT EGOALIASES.USERID AS USERID, EGOALIASES.NAME AS USERNAME, EGOUSERS.ACTIVE AS ACTIVE, EGOUSERS.REQUESTTIME AS REQUESTTIME FROM EGOALIASES INNER JOIN EGOUSERS ON EGOUSERS.SYSID=EGOALIASES.USERID WHERE EGOALIASES.SYSID='$aliasid' AND EGOALIASES.EMAIL<>'' AND EGOUSERS.REQUESTID='$reqid'";
        maestro_query($maestro, $sql, $v);
        if(count($v)==1){   // Esistenza utente
            if($v[0]["ACTIVE"]=="1"){   // Stato di attivita' dell'utente
                $prevtime=str_replace(array("-", ":", "T", " ", "'", "."), "", $v[0]["REQUESTTIME"]);
                if($prevtime!=""){
                    $y=intval(substr($prevtime,0,4));
                    $m=intval(substr($prevtime,4,2));
                    $d=intval(substr($prevtime,6,2));
                    $h=intval(substr($prevtime,8,2));
                    $p=intval(substr($prevtime,10,2));
                    $s=intval(substr($prevtime,12,2));
                    $p=mktime($h,$p,$s,$m,$d,$y);
                }
                else{
                    $p=0;
                }
                if((time()-$p)<=60*60){
                    $userid=$v[0]["USERID"];
                    $user=$v[0]["USERNAME"];
                    $pwd=substr("0000".base_convert(intval(rand(0,1679615)), 10, 36),-4);
                    $object="Ego - Password reimpostata";
                    
                    $text="";
                    $text.="<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
                    $text.="<b>Ego - Password reimpostata</b><br><br>";
                    $text.="La richiesta di reimpostazione della password &egrave; stata completata.<br>";
                    $text.="Utilizzare la password provvisoria ( $pwd ) per accedere al sistema e cambiarla.<br>";
                    $text.="</body><html>";
                    
                    $m=egomail($user, $object, $text);
                    if($m["success"]==1){
                        $sql="UPDATE EGOUSERS SET PASSWORD='".sha1($pwd)."',LASTCHANGE=NULL,REQUESTID=NULL,REQUESTIP=NULL,REQUESTTIME=NULL WHERE SYSID='$userid'";
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
                    $description="La richiesta è scaduta";
                    $babel="EGO_MSG_REQUESTEXPIRED";
                }
            }
            else{
                $success=0;
                $description="Utente disattivato";
                $babel="EGO_MSG_DISABLEDUSER";
            }
        }
        else{
            $success=0;
            $description="La richiesta non può essere completata";
            $babel="EGO_MSG_REQUESTCANNOT";
        }
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $description=$maestro->errdescr;
        $babel="EGO_MSG_UNDEFINED";
    }
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    $babel="EGO_MSG_UNDEFINED";
}

$babelcode=$babel;
$description=qv_babeltranslate($description);
ryqUTF8($description);

print "<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
print $description;
print "</body><html>";
?>
