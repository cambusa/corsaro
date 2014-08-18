<?php 
/****************************************************************************
* Name:            ego_infosession.php                                      *
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
        
    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Operazione effettuata";
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
    $dateformat=0;

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){

        $sql="SELECT SYSID,ALIASID,ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE,CLIENTIP FROM EGOSESSIONS WHERE SESSIONID='$sessionid' AND ENDTIME IS NULL AND [:DATE(RENEWALTIME, 1DAYS)]>[:TODAY()]";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $ip=$r[0]["CLIENTIP"];
            $remoteip=get_ip_address();
            if($ip==$remoteip){
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
                    $appid=$r[0]["APPID"];
                    $environ=$r[0]["NAME"];
                    $envdescr=$r[0]["DESCRIPTION"];
                }
                else{
                    $appid="";
                    $environ="";
                }

                // Ruolo
                if($roleid!=""){
                    $sql="SELECT NAME,DESCRIPTION FROM EGOROLES WHERE SYSID='$roleid'";
                    maestro_query($maestro, $sql, $r);
                    $role=$r[0]["NAME"];
                    $roledescr=$r[0]["DESCRIPTION"];
                }
                else{
                    $role="";
                }
                
                // Lingua
                if($langid!=""){
                    $sql="SELECT NAME FROM EGOLANGUAGES WHERE SYSID='$langid'";
                    maestro_query($maestro, $sql, $r);
                    $language=$r[0]["NAME"];
                }
                else{
                    $language="";
                }

                // Alias
                $sql="SELECT ALIASNAME,USERID,USERNAME,ADMINISTRATOR,EMAIL FROM EGOVIEWUSERS WHERE SYSID='$aliasid'";
                maestro_query($maestro, $sql, $r);
                $alias=$r[0]["ALIASNAME"];
                $userid=$r[0]["USERID"];
                $user=$r[0]["USERNAME"];
                $admin=intval($r[0]["ADMINISTRATOR"]);
                $email=$r[0]["EMAIL"];
                
                // Applicazione
                if($langid!=""){
                    $sql="SELECT NAME,DESCRIPTION FROM EGOAPPLICATIONS WHERE SYSID='$appid'";
                    maestro_query($maestro, $sql, $r);
                    $app=$r[0]["NAME"];
                    $appdescr=$r[0]["DESCRIPTION"];
                }
                else{
                    $app="";
                    $appdescr="";
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
                }
            }
            else{
                $success=0;
                $sessionid="";
                $description="L'IP del richiedente � diverso da quello di sessione";
            }
        }
        else{
            $success=0;
            $sessionid="";
            $description="Sessione non valida";
        }
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $sessionid="";
        $description=$maestro->errdescr;
    }
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
catch(Exception $e){
    $success=0;
    $sessionid="";
    $description=$e->getMessage();
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
    $dateformat=0;
}

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
$j["dateformat"]=$dateformat;
if($padding=="")
    print json_encode($j);
else        // Gestione JSONP (JSON con padding) per le richieste "cross domain"
    print $padding."(".json_encode($j).");";
?>