<?php 
/****************************************************************************
* Name:            ego_infosession.php                                      *
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

try{
    // DETERMINO LA SESSIONID
    if(isset($_POST["sessionid"]))
        $sessionid=ryqEscapize($_POST["sessionid"]);
    elseif(isset($_GET["sessionid"]))
        $sessionid=ryqEscapize($_GET["sessionid"]);
    else
        $sessionid="";

    // DETERMINO APPLICAZIONE
    if(isset($_POST["app"]))
        $appname=ryqEscapize($_POST["app"]);
    elseif(isset($_GET["app"]))
        $appname=ryqEscapize($_GET["app"]);
    else
        $appname="";
        
    // DETERMINO IL PADDING PER USCITA JSONP
    if(isset($_POST["padding"]))
        $padding=ryqEscapize($_POST["padding"]);
    elseif(isset($_GET["padding"]))
        $padding=ryqEscapize($_GET["padding"]);
    else
        $padding="";
        
    // GESTIONE BACKSLASH
    if(isset($_POST["backslash"]))
        $backslash=strlen($_POST["backslash"]);
    elseif(isset($_GET["backslash"]))
        $backslash=strlen($_GET["backslash"]);
    else
        $backslash=0;
    @file_put_contents($path_databases."_configs/backslash.par", $backslash);
        
    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Operazione effettuata";
    $babelcode="EGO_MSG_SUCCESSFUL";
    $appid="";
    $app="";
    $appdescr="";
    $envid="";
    $environ="";
    $envdescr="";
    $roleid="";
    $role="";
    $roledescr="";
    $language="";
    $countrycode="";
    $debugmode=0;
    $aliasid="";
    $alias="";
    $userid="";
    $user="";
    $admin=0;
    $email="";
    $registry="";
    $dateformat=0;
    
    if($sqlite3_enabled)
        $sqlite="3";
    else
        $sqlite="2";

    if(isset($_COOKIE['_egolanguage'])){
        $global_lastlanguage=$_COOKIE['_egolanguage'];
    }

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
    
        $sql="SELECT SYSID,ALIASID,ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE,CLIENTIP FROM EGOSESSIONS WHERE SESSIONID='$sessionid' AND ENDTIME IS NULL AND [:DATE(RENEWALTIME, 1DAYS)]>[:TODAY()]";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $ip=$r[0]["CLIENTIP"];
            $remoteip=get_ip_address();
            if($ip==$remoteip || $check_sessionip==false){
                $sysid=$r[0]["SYSID"];
                $aliasid=$r[0]["ALIASID"];
                $envid=$r[0]["ENVIRONID"];
                $roleid=$r[0]["ROLEID"];
                $langid=$r[0]["LANGUAGEID"];
                $countrycode=$r[0]["COUNTRYCODE"];
                $debugmode=intval($r[0]["DEBUGMODE"]);
                
                // Ambiente
                if($envid!=""){
                    $sql="SELECT APPID,NAME,DESCRIPTION FROM EGOENVIRONS WHERE SYSID='$envid'";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)>0){
                        $appid=$r[0]["APPID"];
                        $environ=$r[0]["NAME"];
                        $envdescr=$r[0]["DESCRIPTION"];
                    }
                }

                // Ruolo
                if($roleid!=""){
                    $sql="SELECT NAME,DESCRIPTION FROM EGOROLES WHERE SYSID='$roleid'";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)>0){
                        $role=$r[0]["NAME"];
                        $roledescr=$r[0]["DESCRIPTION"];
                    }
                }
                
                // Lingua
                if($langid!=""){
                    $sql="SELECT NAME FROM EGOLANGUAGES WHERE SYSID='$langid'";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)>0){
                        $language=$r[0]["NAME"];
                    }
                }

                // Alias
                $sql="SELECT ALIASNAME,USERID,USERNAME,ADMINISTRATOR,EMAIL,REGISTRY FROM EGOVIEWUSERS WHERE SYSID='$aliasid'";
                maestro_query($maestro, $sql, $r);
                if(count($r)>0){
                    $alias=$r[0]["ALIASNAME"];
                    $userid=$r[0]["USERID"];
                    $user=$r[0]["USERNAME"];
                    $admin=intval($r[0]["ADMINISTRATOR"]);
                    $email=$r[0]["EMAIL"];
                    $registry=$r[0]["REGISTRY"];
                }
                
                // Applicazione
                if($appid!=""){
                    $sql="SELECT NAME,DESCRIPTION FROM EGOAPPLICATIONS WHERE SYSID='$appid'";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)>0){
                        $app=$r[0]["NAME"];
                        $appdescr=$r[0]["DESCRIPTION"];
                    }
                }
                
                // Impostazioni internazionali
                $locale=$tocambusa."/ryego/locale/".strtolower($countrycode).".php";
                if(is_file($locale)){
                    include_once $locale;
                }

                // Controllo che l'applicazione che richiede le info sia quella che ha fatto logon
                if($app!=$appname){
                    $success=0;
                    $sessionid="";
                    $description="Autorizzazioni insufficienti";
                    $babelcode="EGO_MSG_NOAUTHORIZATION";
                }
            }
            else{
                $success=0;
                $sessionid="";
                $description="L'IP del richiedente è diverso da quello di sessione";
                $babelcode="EGO_MSG_MISMATCHIP";
            }
        }
        else{
            $success=0;
            $sessionid="";
            $description="Sessione non valida";
            $babelcode="EGO_MSG_INVALIDSESSION";
        }
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $sessionid="";
        $description=$maestro->errdescr;
        $babelcode="EGO_MSG_UNDEFINED";
    }
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
catch(Exception $e){
    $success=0;
    $sessionid="";
    $description=$e->getMessage();
    $babelcode="EGO_MSG_UNDEFINED";
}

if($success==0){
    $appid="";
    $app="";
    $appdescr="";
    $envid="";
    $environ="";
    $envdescr="";
    $roleid="";
    $role="";
    $roledescr="";
    $language="";
    $countrycode="";
    $debugmode=0;
    $aliasid="";
    $alias="";
    $userid="";
    $user="";
    $admin=0;
    $email="";
    $registry="";
    $dateformat=0;
    $sqlite="";
}

$description=qv_babeltranslate($description);

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["sessionid"]=$sessionid;
$j["description"]=htmlentities($description);
$j["appid"]=$appid;
$j["app"]=htmlentities($app);
$j["appdescr"]=htmlentities($appdescr);
$j["envid"]=htmlentities($envid);
$j["environ"]=htmlentities($environ);
$j["envdescr"]=htmlentities($envdescr);
$j["roleid"]=htmlentities($roleid);
$j["role"]=htmlentities($role);
$j["roledescr"]=htmlentities($roledescr);
$j["language"]=htmlentities($language);
$j["countrycode"]=htmlentities($countrycode);
$j["debugmode"]=$debugmode;
$j["aliasid"]=$aliasid;
$j["alias"]=htmlentities($alias);
$j["userid"]=htmlentities($userid);
$j["user"]=htmlentities($user);
$j["admin"]=$admin;
$j["email"]=htmlentities($email);
$j["registry"]=htmlentities($registry);
$j["dateformat"]=$dateformat;
$j["sqlite"]=$sqlite;
if($padding=="")
    print json_encode($j);
else        // Gestione JSONP (JSON con padding) per le richieste "cross domain"
    print $padding."(".json_encode($j).");";
?>