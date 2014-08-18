<?php
/****************************************************************************
* Name:            ego_begin.php                                            *
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
include_once $tocambusa."ryego/ego_crypt.php";
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."phpseclib/Math/BigInteger.php";
include_once $tocambusa."phpseclib/Crypt/RSA.php";
try{
    if(isset($_POST["user"])){
        $usercookie=ryqNormalize($_POST["user"]);
        $user=ryqEscapize($_POST["user"]);
    }
    else{
        $usercookie="";
        $user="";
    }

    if(isset($_POST["pwd"]))
        $pwd=ryqEscapize($_POST["pwd"]);
    else
        $pwd=sha1("");

    if(isset($_POST["app"]))
        $app=ryqEscapize($_POST["app"]);
    else
        $app="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Autenticazione riuscita";
    $sessionid="";
    $appid="";
    $userid="";
    $aliasid="";
    $expiry=0;

    // PERMUTAZIONE PER PROTEZIONE PASSWORD
    session_start();
    if (isset($_SESSION["ego_privatekey"])){
        $privatekey=$_SESSION["ego_privatekey"];
    }
    else{
        // SESSIONE NON INIZIALIZZATA
        $success=0;
        $description="Sessione non inizializzata: ricaricare il form di login";
    }
    
    if($success){
        // APRO IL DATABASE
        $maestro=maestro_opendb("ryego");
        if($maestro->conn!==false){
            // RICERCA UTENTE
            $sql="";
            $sql.="SELECT EGOUSERS.PASSWORD AS PWD,";
            $sql.="EGOALIASES.SYSID AS ALIASID,";
            $sql.="EGOALIASES.USERID AS USERID,";
            $sql.="EGOUSERS.ACTIVE AS ACTIVE,";
            $sql.="EGOUSERS.LASTCHANGE AS LASTCHANGE,";
            $sql.="EGOALIASES.DEMIURGE AS DEMIURGE,";
            $sql.="EGOALIASES.ADMINISTRATOR AS ADMINISTRATOR ";
            $sql.="FROM EGOALIASES ";
            $sql.="INNER JOIN EGOUSERS ON EGOUSERS.SYSID=EGOALIASES.USERID ";
            $sql.="WHERE [:UPPER(EGOALIASES.NAME)]='".strtoupper($user)."'";
            maestro_query($maestro, $sql, $v);
            if(count($v)==1){   // Esistenza utente
                $pwd=decryptString($pwd, $privatekey);
                if($v[0]["PWD"]==$pwd){     // Correttezza password
                    if(intval($v[0]["ACTIVE"])==1){     // Stato di utente attivo
                        // AUTORIZZAZIONI
                        $auth=true;
                        $demiurge=intval($v[0]["DEMIURGE"]);
                        $administrator=intval($v[0]["ADMINISTRATOR"]);
                        $userid=$v[0]["USERID"];
                        $aliasid=$v[0]["ALIASID"];
                        $lastchange=str_replace(array("-", ":", "T", " ", "'", "."), "", $v[0]["LASTCHANGE"]);
                        $environid="";
                        $roleid="";
                        $languageid="";
                        $countrycode="";
                        $debugmode=0;
                        
                        if($app==""){ // Autorizzazioni applicazione EGO
                            if($demiurge!=1)
                                $auth=false;
                        }
                        else{ // Autorizzazioni applicazione esterna e setup
                            $sql="SELECT SYSID FROM EGOAPPLICATIONS WHERE NAME='$app'";
                            maestro_query($maestro, $sql, $v);
                            if(count($v)==1){
                                $appid=$v[0]["SYSID"];
                                $sql="";
                                $sql.="SELECT ENVIRONID ";
                                $sql.="FROM EGOENVIRONUSER ";
                                $sql.="INNER JOIN EGOENVIRONS ON EGOENVIRONS.SYSID=EGOENVIRONUSER.ENVIRONID ";
                                $sql.="WHERE EGOENVIRONS.APPID='$appid' AND EGOENVIRONUSER.USERID='$userid'";
                                maestro_query($maestro, $sql, $v);
                                if(count($v)>0){
                                    // SETUP DI DEFAULT
                                    // Ambiente
                                    $environid=$v[0]["ENVIRONID"];
                                    // Ruolo
                                    $sql="SELECT EGOROLEUSER.ROLEID AS ROLEID FROM EGOROLEUSER INNER JOIN EGOROLES ON EGOROLES.SYSID=EGOROLEUSER.ROLEID WHERE EGOROLEUSER.USERID='$userid' AND EGOROLES.APPID='$appid'";
                                    maestro_query($maestro, $sql, $v);
                                    if(count($v)>0){
                                        $roleid=$v[0]["ROLEID"];
                                    }
                                    // Lingua
                                    $sql="SELECT SYSID FROM EGOLANGUAGES";
                                    maestro_query($maestro, $sql, $v);
                                    if(count($v)>0){
                                        $languageid=$v[0]["SYSID"];
                                    }
                                    // Paese
                                    $countrycode="ITA";

                                    // Debugging
                                    $debugmode=0;

                                    // LETTURA SETUP
                                    $sql="SELECT ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE FROM EGOSETUP WHERE APPID='$appid' AND ALIASID='$aliasid'";
                                    maestro_query($maestro, $sql, $v);
                                    if(count($v)==1){
                                        $test_environid=$v[0]["ENVIRONID"];
                                        $test_roleid=$v[0]["ROLEID"];
                                        $test_languageid=$v[0]["LANGUAGEID"];
                                        $countrycode=$v[0]["COUNTRYCODE"];
                                        $debugmode=intval($v[0]["DEBUGMODE"]);
                                        
                                        // Controllo che l'ambiente del setup sia ancora valido
                                        $sql="SELECT SYSID FROM EGOENVIRONUSER WHERE ENVIRONID='$test_environid' AND USERID='$userid'";
                                        maestro_query($maestro, $sql, $v);
                                        if(count($v)==1){
                                            $environid=$test_environid;
                                        }
                                        // Controllo che il ruolo del setup sia ancora valido
                                        $sql="SELECT SYSID FROM EGOROLEUSER WHERE ROLEID='$test_roleid' AND USERID='$userid'";
                                        maestro_query($maestro, $sql, $v);
                                        if(count($v)==1){
                                            $roleid=$test_roleid;
                                        }
                                        // Controllo che la lingua del setup sia ancora valida
                                        $sql="SELECT SYSID FROM EGOLANGUAGES WHERE SYSID='$test_languageid'";
                                        maestro_query($maestro, $sql, $v);
                                        if(count($v)==1){
                                            $languageid=$test_languageid;
                                        }
                                        $sql="UPDATE EGOSETUP SET ENVIRONID='$environid', ROLEID='$roleid', LANGUAGEID='$languageid', COUNTRYCODE='$countrycode', DEBUGMODE='$debugmode' WHERE APPID='$appid' AND ALIASID='$aliasid'";
                                        maestro_execute($maestro, $sql);
                                    }
                                    else{
                                        $setupid=qv_createsysid($maestro);
                                        $sql="INSERT INTO EGOSETUP(SYSID,APPID,ALIASID,ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE) VALUES('$setupid','$appid','$aliasid','$environid','$roleid','$languageid','$countrycode','$debugmode')";
                                        maestro_execute($maestro, $sql);
                                    }
                                }
                                else{
                                    $auth=false;
                                }
                            }
                            else{
                                $auth=false;
                            }
                        }
                        if($auth){
                            // MI FACCIO RESTITUIRE UN SYSTEM ID UNIVOCO
                            $sessionid=monadcall(16,2);
                            
                            // INSERISCO LA SESSIONE
                            $ip=get_ip_address();
                            $sysid=qv_createsysid($maestro);
                            $sql="";
                            $sql.="INSERT INTO EGOSESSIONS(SYSID,SESSIONID,DEMIURGE,ADMINISTRATOR,ALIASID,ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE,CLIENTIP,BEGINTIME,RENEWALTIME,ENDTIME) ";
                            $sql.="VALUES('$sysid',";
                            $sql.="'$sessionid',";
                            $sql.="$demiurge,";
                            $sql.="$administrator,";
                            $sql.="'$aliasid',";
                            $sql.="'$environid',";
                            $sql.="'$roleid',";
                            $sql.="'$languageid',";
                            $sql.="'$countrycode',";
                            $sql.="'$debugmode',";
                            $sql.="'$ip',";
                            $sql.="[:NOW()],";
                            $sql.="[:NOW()],";
                            $sql.="NULL)";
                            maestro_execute($maestro, $sql);
                            
                            // GESTIONE SCADENZA PASSWORD
                            if($lastchange!=""){
                                $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME IN ('duration','warning') ORDER BY NAME";
                                maestro_query($maestro, $sql, $r);
                                $duration=intval($r[0]["VALUE"]);
                                $warning=intval($r[1]["VALUE"]);
                                if($duration>0){
                                    $ly=intval(substr($lastchange,0,4));
                                    $lm=intval(substr($lastchange,4,2));
                                    $ld=intval(substr($lastchange,6,2));
                                    $ty=intval(date("Y"));
                                    $tm=intval(date("m"));
                                    $td=intval(date("d"));
                                    $today_date="D".date("Ymd", mktime(0,0,0,$tm, $td, $ty));
                                    $expiry_date="D".date("Ymd", mktime(0,0,0,$lm, $ld+$duration, $ly));
                                    $warning_date="D".date("Ymd", mktime(0,0,0,$lm, $ld+$duration-$warning, $ly));
                                    if($today_date>=$expiry_date)
                                        $expiry=2; // password scaduta
                                    elseif($today_date>=$warning_date)
                                        $expiry=1; // password scadente
                                }
                            }
                            else{
                                // La password � quella predefinita: 
                                // la tratto come se fosse scaduta
                                $expiry=2;
                            }

                            // GESTIONE PROPOSTA UTENTE
                            $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME='saveuser'";
                            maestro_query($maestro, $sql, $r);
                            $saveuser=intval($r[0]["VALUE"]);
                            if($saveuser==1){
                                setcookie("_egouser", $usercookie, time()+4000000);
                            }
                        }
                        else{
                            // AUTORIZZAZIONI INSUFFICIENTI
                            $success=0;
                            $description="Autorizzazioni insufficienti";
                        }
                    }
                    else{
                        // UTENTE NON ATTIVO
                        $success=0;
                        $description="Utente inesistente o password errata";
                    }
                }
                else{
                    // PASSWORD ERRATA
                    $success=0;
                    $description="Utente inesistente o password errata";
                }
            }
            else{
                // UTENTE INESISTENTE
                $success=0;
                $description="Utente inesistente o password errata";
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
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    writelog("ego_begin.php:".$description);
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
$j["sessionid"]=$sessionid;
$j["aliasid"]=$aliasid;
$j["userid"]=$userid;
$j["appid"]=$appid;
$j["expiry"]=$expiry;
print json_encode($j);
?>