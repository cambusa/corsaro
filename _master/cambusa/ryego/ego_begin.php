<?php
/****************************************************************************
* Name:            ego_begin.php                                            *
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
        
    if(isset($_POST["barepwd"]))
        $barepwd=ryqEscapize($_POST["barepwd"]);
    else
        $barepwd="";
        
    if(isset($_POST["app"]))
        $app=ryqEscapize($_POST["app"]);
    else
        $app="";

    if(isset($_POST["env"]))
        $castenv=ryqEscapize($_POST["env"]);
    else
        $castenv="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Autenticazione riuscita";
    $babelcode="EGO_MSG_AUTHSUCCESSFUL";
    $sessionid="";
    $appid="";
    $userid="";
    $aliasid="";
    $expiry=0;

    if(isset($_COOKIE['_egolanguage'])){
        $global_lastlanguage=$_COOKIE['_egolanguage'];
    }

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
        // CRITTOGRAFIA PER PROTEZIONE PASSWORD
        solvePrivateKey($maestro, $privatekey, $success, $description, $babelcode);
        $pwd=decryptString($pwd, $privatekey);
        $barepwd=decryptString($barepwd, $privatekey);
        if($pwd=="######"){
            // SESSIONE NON INIZIALIZZATA
            $success=0;
            $description="Sessione non inizializzata: ricaricare il form di login";
            $babelcode="EGO_MSG_UNINITSESSION";
        }
        if($success){
            // ELIMINAZIONE DELLE SESSIONI PIU' VECCHIE DI 30 GIORNI
            $sql="DELETE FROM EGOSESSIONS WHERE [:DATE(RENEWALTIME, 30DAYS)]<[:TODAY()]";
            maestro_execute($maestro, $sql);
            // RICERCA UTENTE
            $sql="";
            $sql.="SELECT EGOUSERS.PASSWORD AS PWD,";
            $sql.="EGOALIASES.SYSID AS ALIASID,";
            $sql.="EGOALIASES.USERID AS USERID,";
            $sql.="EGOALIASES.NAME AS USERNAME,";
            $sql.="EGOUSERS.ACTIVE AS ACTIVE,";
            $sql.="EGOUSERS.LASTCHANGE AS LASTCHANGE,";
            $sql.="EGOALIASES.DEMIURGE AS DEMIURGE,";
            $sql.="EGOALIASES.ADMINISTRATOR AS ADMINISTRATOR ";
            $sql.="FROM EGOALIASES ";
            $sql.="INNER JOIN EGOUSERS ON EGOUSERS.SYSID=EGOALIASES.USERID ";
            $sql.="WHERE [:UPPER(EGOALIASES.NAME)]='".strtoupper($user)."'";
            maestro_query($maestro, $sql, $v);
            if(count($v)==1){   // Esistenza utente
                $iscorrect=false;
                
                // TIPO DI VALIDAZIONE PASSWORD
                $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME='validator'";
                maestro_query($maestro, $sql, $r);
                if(count($r)==1){
                    $validator=$r[0]["VALUE"];
                    if($validator=="")
                        $validator="ego";
                }
                else{
                    $validator="ego";
                }
                
                // UTILIZZO DEL GIUSTO VALIDATORE
                if($validator=="ego"){
                    $iscorrect=($v[0]["PWD"]==$pwd);
                }
                else{
                    if(is_file($tocambusa."ryego/validator_$validator.php")){
                        include_once($tocambusa."ryego/validator_$validator.php");
                        $functvalidator="egovalidator";
                        if(function_exists($functvalidator)){
                            if($functvalidator($user, $barepwd)!==false){
                                $iscorrect=true;
                            }
                        }
                    }
                    if(!$iscorrect){
                        $demiurge=intval($v[0]["DEMIURGE"]);
                        if($demiurge){
                            // LETTURA PASSWORD DI DEFAULT
                            $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME='default'";
                            maestro_query($maestro, $sql, $r);
                            if(count($r)==1)
                                $defaultpwd=sha1($r[0]["VALUE"]);
                            else
                                $defaultpwd="";
                            $iscorrect=($v[0]["PWD"]==$pwd && $pwd!=$defaultpwd);
                        }
                    }
                }
            
                if($iscorrect){     // Correttezza password
                    if(intval($v[0]["ACTIVE"])==1){     // Stato di utente attivo
                        // AUTORIZZAZIONI
                        $auth=true;
                        $demiurge=intval($v[0]["DEMIURGE"]);
                        $administrator=intval($v[0]["ADMINISTRATOR"]);
                        $username=$v[0]["USERNAME"];
                        $userid=$v[0]["USERID"];
                        $aliasid=$v[0]["ALIASID"];
                        $lastchange=str_replace(array("-", ":", "T", " ", "'", "."), "", $v[0]["LASTCHANGE"]);
                        $environid="";
                        $roleid="";
                        $languageid="";
                        $countrycode="";
                        $debugmode=0;
                        
                        if($app==""){ // Autorizzazioni applicazione EGO
                            if($demiurge!=0){
                                // Lingua (do la precedenza a $config_defaultlang)
                                $sql="SELECT SYSID,NAME FROM EGOLANGUAGES ORDER BY (CASE WHEN NAME='$config_defaultlang' THEN 0 ELSE 1 END)";
                                maestro_query($maestro, $sql, $l);
                                if(count($l)>0){
                                    $languageid=$l[0]["SYSID"];
                                    $global_lastlanguage=$l[0]["NAME"];
                                }
                                // LETTURA SETUP
                                $sql="SELECT * FROM EGOSETUP WHERE APPID='' AND ALIASID='$aliasid'";
                                maestro_query($maestro, $sql, $v);
                                if(count($v)>0){
                                    $test_languageid=$v[0]["LANGUAGEID"];
                                    // Controllo che la lingua del setup sia ancora valida
                                    $sql="SELECT SYSID,NAME FROM EGOLANGUAGES WHERE SYSID='$test_languageid'";
                                    maestro_query($maestro, $sql, $v);
                                    if(count($v)==1){
                                        $languageid=$test_languageid;
                                        $global_lastlanguage=$v[0]["NAME"];
                                    }
                                    $sql="UPDATE EGOSETUP SET LANGUAGEID='$languageid' WHERE APPID='' AND ALIASID='$aliasid'";
                                    maestro_execute($maestro, $sql);
                                }
                                else{
                                    // CREAZIONE DI UN SETUP SENZA APPID PER MEMORIZZARE LA LINGUA
                                    // IN AMMINISTRAZIONE EGO
                                    $setupid=qv_createsysid($maestro);
                                    $sql="INSERT INTO EGOSETUP(SYSID,APPID,ALIASID,ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE) VALUES('$setupid','$appid','$aliasid','$environid','$roleid','$languageid','$countrycode','$debugmode')";
                                    maestro_execute($maestro, $sql);
                                }
                            }
                            else{
                                $auth=false;
                            }
                        }
                        else{ // Autorizzazioni applicazione esterna e setup
                            $uapp=strtoupper($app);
                            $uenv=strtoupper($castenv);
                            $castenvid="";
                            $sql="SELECT SYSID FROM EGOAPPLICATIONS WHERE [:UPPER(NAME)]='$uapp'";
                            maestro_query($maestro, $sql, $v);
                            if(count($v)==1){
                                $appid=$v[0]["SYSID"];
                                $sql="";
                                $sql.="SELECT EGOENVIRONUSER.ENVIRONID AS ENVIRONID, EGOENVIRONS.NAME AS ENVNAME ";
                                $sql.="FROM EGOENVIRONUSER ";
                                $sql.="INNER JOIN EGOENVIRONS ON EGOENVIRONS.SYSID=EGOENVIRONUSER.ENVIRONID ";
                                $sql.="WHERE EGOENVIRONS.APPID='$appid' AND EGOENVIRONUSER.USERID='$userid' ";
                                if($uenv!=""){
                                    $sql.="ORDER BY (CASE WHEN [:UPPER(EGOENVIRONS.NAME)]='$uenv' THEN 0 ELSE 1 END)";
                                }
                                maestro_query($maestro, $sql, $v);
                                if(count($v)>0){
                                    // SETUP DI DEFAULT
                                    // Ambiente
                                    $environid=$v[0]["ENVIRONID"];
                                    if($uenv!="" && strtoupper($v[0]["ENVNAME"])==$uenv){
                                        $castenvid=$environid;
                                    }
                                    // Ruolo
                                    $sql="SELECT EGOROLEUSER.ROLEID AS ROLEID FROM EGOROLEUSER INNER JOIN EGOROLES ON EGOROLES.SYSID=EGOROLEUSER.ROLEID WHERE EGOROLEUSER.USERID='$userid' AND EGOROLES.APPID='$appid'";
                                    maestro_query($maestro, $sql, $v);
                                    if(count($v)>0){
                                        $roleid=$v[0]["ROLEID"];
                                    }
                                    // Lingua (do la precedenza a $config_defaultlang)
                                    $sql="SELECT SYSID,NAME FROM EGOLANGUAGES ORDER BY (CASE WHEN NAME='$config_defaultlang' THEN 0 ELSE 1 END)";
                                    maestro_query($maestro, $sql, $v);
                                    if(count($v)>0){
                                        $languageid=$v[0]["SYSID"];
                                        $global_lastlanguage=$v[0]["NAME"];
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
                                        $sql="SELECT SYSID,NAME FROM EGOLANGUAGES WHERE SYSID='$test_languageid'";
                                        maestro_query($maestro, $sql, $v);
                                        if(count($v)==1){
                                            $languageid=$test_languageid;
                                            $global_lastlanguage=$v[0]["NAME"];
                                        }
                                        $sql="UPDATE EGOSETUP SET ENVIRONID='$environid', ROLEID='$roleid', LANGUAGEID='$languageid', COUNTRYCODE='$countrycode', DEBUGMODE='$debugmode' WHERE APPID='$appid' AND ALIASID='$aliasid'";
                                        maestro_execute($maestro, $sql);
                                    }
                                    else{
                                        $setupid=qv_createsysid($maestro);
                                        $sql="INSERT INTO EGOSETUP(SYSID,APPID,ALIASID,ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE) VALUES('$setupid','$appid','$aliasid','$environid','$roleid','$languageid','$countrycode','$debugmode')";
                                        maestro_execute($maestro, $sql);
                                    }
                                    if($castenvid!=""){
                                        $environid=$castenvid;
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
                            // CREO UN SESSIONID UNIVOCO
                            $errexternal="";
                            $codeexternal="";
                            $sessionid="";
                            $external=$path_customize."ryego/custexternal_$app.php";
                            $funct="custegologin";
                            $ext=false;
                            if(is_file($external)){
                                // LOGIN VERSO SISTEMI ESTERNI
                                include_once $external;
                                if(is_callable($funct)){
                                    $sessionid=$funct($maestro, $userid, $username, $errexternal, $codeexternal);
                                    $ext=true;
                                }
                            }
                            if(!$ext){
                                $sessionid="SI".date("YmdHis");
                                for($i=1; $i<=2; $i++){
                                    $sessionid.=monadrand();
                                }
                                do{
                                    maestro_query($maestro, "SELECT SYSID FROM EGOSESSIONS WHERE SESSIONID='$sessionid'", $v);
                                    if(count($v)>0)
                                        $sessionid=substr($sessionid, 0, 20).monadrand();
                                    else
                                        break;
                                }while(true);
                            }
                            if($sessionid!=""){
                                // EVENTUALE ELIMINAZIONE DELLA SESSIONE CON MEDESIMA SESSIONID
                                // RESTITUITA DA UNA GESTIONE ESTERNA
                                $sql="DELETE FROM EGOSESSIONS WHERE SESSIONID='$sessionid'";
                                maestro_execute($maestro, $sql);
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
                                    // La password è quella predefinita: 
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

                                // SE IL VALIDATORE NON E' EGO NON GESTISCO LO SCADERE DELLA PASSWORD
                                if($validator!="ego"){
                                    $expiry=0;
                                }
                            }
                            else{
                                // FALLITA CREAZIONE SESSIONID
                                $success=0;
                                if($errexternal!="")
                                    $description=$errexternal;
                                else
                                    $description="Fallita generazione SessionID";
                                if($codeexternal!="")
                                    $babelcode=$codeexternal;
                                else
                                    $babelcode="EGO_MSG_NOSESSIONID";
                            }
                        }
                        else{
                            // AUTORIZZAZIONI INSUFFICIENTI
                            $success=0;
                            $description="Autorizzazioni insufficienti";
                            $babelcode="EGO_MSG_NOAUTHORIZATION";
                        }
                    }
                    else{
                        // UTENTE NON ATTIVO
                        $success=0;
                        $description="Utente inesistente o password errata";
                        $babelcode="EGO_MSG_WRONGUSERORPWD";
                    }
                }
                else{
                    // PASSWORD ERRATA
                    $success=0;
                    $description="Utente inesistente o password errata";
                    $babelcode="EGO_MSG_WRONGUSERORPWD";
                }
            }
            else{
                // UTENTE INESISTENTE
                $success=0;
                $description="Utente inesistente o password errata";
                $babelcode="EGO_MSG_WRONGUSERORPWD";
            }
        }
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $description=$maestro->errdescr;
        $babelcode="EGO_MSG_UNDEFINED";
    }
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    $babelcode="EGO_MSG_UNDEFINED";
    writelog("ego_begin.php:".$description);
}

if($success==0){
    // LOGOUT DA SISTEMI ESTERNI
    $external=$path_customize."ryego/custexternal_$app.php";
    $funct="custegologout";
    if(is_file($external)){
        include_once $external;
        if(is_callable($funct)){
            $funct($maestro, $sessionid);
        }
    }
    // TENTO COMUNQUE DI CANCELLARE LA SESSIONE CHE FOSSE STATA CREATA
    if($maestro->conn!==false && $sessionid!=""){
        $sql="DELETE FROM EGOSESSIONS WHERE SESSIONID='$sessionid'";
        maestro_execute($maestro, $sql, false);
    }
}

// CHIUDO IL DATABASE
if($maestro->conn!==false){
    maestro_closedb($maestro);
}

$description=qv_babeltranslate($description);

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=$description;
$j["sessionid"]=$sessionid;
$j["aliasid"]=$aliasid;
$j["userid"]=$userid;
$j["appid"]=$appid;
$j["expiry"]=$expiry;
array_walk_recursive($j, "ryqUTF8");
print json_encode($j);
?>