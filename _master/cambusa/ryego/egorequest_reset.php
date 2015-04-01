<?php
/****************************************************************************
* Name:            egorequest_reset.php                                     *
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
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."rymonad/monad_lib.php";
include_once $tocambusa."ryego/ego_validate.php";
include_once $tocambusa."ryego/ego_sendmail.php";

try{
    // DETERMINO USER
    if(isset($_POST["user"]))
        $user=ryqEscapize($_POST["user"]);
    else
        $user="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Richiesta inoltrata: controllare la casella di posta";
    $babel="EGO_MSG_REQSUCCESSFUL"; // NON USO SUBITO $babelcode PERCHE' VERREBBE SOVRASCRITTO NELL'INVIO DELL'EMAIL
    
    if(isset($_COOKIE['_egolanguage'])){
        $global_lastlanguage=$_COOKIE['_egolanguage'];
    }

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
        if($user!=""){
            $userupper=strtoupper(ryqEscapize($user));
            $sql="SELECT EGOALIASES.SYSID AS ALIASID, EGOALIASES.USERID AS USERID, EGOUSERS.ACTIVE AS ACTIVE, EGOUSERS.REQUESTTIME AS REQUESTTIME FROM EGOALIASES INNER JOIN EGOUSERS ON EGOUSERS.SYSID=EGOALIASES.USERID WHERE [:UPPER(EGOALIASES.NAME)]='$userupper' AND EGOALIASES.EMAIL<>''";
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
                    if((time()-$p)>60*60){
                        $userid=$v[0]["USERID"];
                        $aliasid=$v[0]["ALIASID"];
                        $requestid=monadcall(16,2);
                        $reqip=get_ip_address();
                        $object="Ego - Richiesta di reimpostazione password";
                        
                        $text="";
                        $text.="<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
                        $text.="<b>Ego - Richiesta di reimpostazione password</b><br><br>";
                        $text.="Una richiesta di reimpostazione della password &egrave; stata inoltrata.<br>";
                        $text.="Confermando l'autenticit&agrave; della richiesta verr&agrave; generata una nuova password provvisoria e inviata per email.<br>";
                        $text.="Al primo accesso tale password dovr&agrave; essere cambiata.<br>";
                        $text.="Confermi la <a href='".$url_cambusa."ryego/egorequest_confirm.php?aliasid=".$aliasid."&reqid=". $requestid."' target='_blank'>reimpostazione</a> della password?<br>";
                        $text.="</body><html>";
                        
                        $m=egomail($user, $object, $text);
                        if($m["success"]==1){
                            $sql="UPDATE EGOUSERS SET REQUESTID='$requestid',REQUESTIP='$reqip',REQUESTTIME=[:NOW()] WHERE SYSID='$userid'";
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
                        $description="Una richiesta è già stata inoltrata";
                        $babel="EGO_MSG_ALREADYSENT";
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
                $description="Utente/email inesistente";
                $babel="EGO_MSG_NOUSEROREMAIL";
            }
        }
        else{
            $success=0;
            $description="Specificare un nome utente o alias";
            $babel="EGO_MSG_MANDATORYUSERALIAS";
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

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
print json_encode($j);
?>