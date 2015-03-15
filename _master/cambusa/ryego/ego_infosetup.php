<?php 
/****************************************************************************
* Name:            ego_infosetup.php                                        *
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
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."ryque/ryq_util.php";

try{
    // DETERMINO LA SESSIONID
    if(isset($_POST["sessionid"]))
        $sessionid=ryqEscapize($_POST["sessionid"]);
    else
        $sessionid="";

    // DETERMINO APPID
    if(isset($_POST["appid"]))
        $appid=ryqEscapize($_POST["appid"]);
    else
        $appid="";

    // DETERMINO ALISID
    if(isset($_POST["aliasid"]))
        $aliasid=ryqEscapize($_POST["aliasid"]);
    else
        $aliasid="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Operazione effettuata";
    $babelcode="EGO_MSG_SUCCESSFUL";

    $userid="";

    $lastenvironid="";
    $lastroleid="";
    $lastlanguageid="";
    $lastcountrycode="";
    $lastdebugmode="";
    $email="";
    $lstenviron=array();
    $lstrole=array();
    $lstlanguage=array();
    $lstcc=array();
    
    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){

        // CONTROLLO VALIDITA' SESSIONE
        if(ego_validatesession($maestro, $sessionid, true, "")==false){
            $success=0;
            $description="Sessione non valida";
            $babelcode="EGO_MSG_INVALIDSESSION";
        }
        
        if($success){
            // Reperisco USERID
            $sql="SELECT USERID FROM EGOALIASES WHERE SYSID='$aliasid'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $userid=$r[0]["USERID"];
            }

            // Reperisco ambiente, ruolo e lingua di setup
            $sql="SELECT ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE FROM EGOSETUP WHERE APPID='$appid' AND ALIASID='$aliasid'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $lastenvironid=$r[0]["ENVIRONID"];
                $lastroleid=$r[0]["ROLEID"];
                $lastlanguageid=$r[0]["LANGUAGEID"];
                $lastcountrycode=$r[0]["COUNTRYCODE"];
                $lastdebugmode=$r[0]["DEBUGMODE"];
            }
            
            // Reperisco la lista ambienti
            $sql="SELECT DESCRIPTION,ENVIRONID FROM EGOVIEWENVIRONUSER WHERE APPID='$appid' AND USERID='$userid'";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $lstenviron[$i]=array("caption" => $r[$i]["DESCRIPTION"], "key" => $r[$i]["ENVIRONID"]);
            }
            
            // Reperisco la lista ruoli
            $sql="SELECT DESCRIPTION,ROLEID FROM EGOVIEWROLEUSER WHERE APPID='$appid' AND USERID='$userid'";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $lstrole[$i]=array("caption" => $r[$i]["DESCRIPTION"], "key" => $r[$i]["ROLEID"]);
            }
            
            // Reperisco la lista lingue
            $sql="SELECT * FROM EGOLANGUAGES";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $lstlanguage[$i]=array("caption" => $r[$i]["DESCRIPTION"], "key" => $r[$i]["SYSID"], "tag" => $r[$i]["NAME"]);
            }
            
            // Reperisco l'email
            $sql="SELECT EMAIL FROM EGOALIASES WHERE USERID='$userid'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $email=$r[0]["EMAIL"];
            }
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
    
    $tr=array("'" => "&acute;");
    // APERTURA DATABASE GEOGRAPHY
    $maestro=maestro_opendb("rygeography");

    if($maestro->conn!==false){
        maestro_query($maestro,"SELECT DESCRIPTION,ALFATRE FROM GEONAZIONI ORDER BY DESCRIPTION", $r);
        for($i=0; $i<count($r); $i++){
            $lstcc[$i]=array("caption" => strtr( $r[$i]["DESCRIPTION"], $tr), "key" => $r[$i]["ALFATRE"]);
        }
    }

    // CHIUSURA DATABASE
    maestro_closedb($maestro);
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    $babelcode="EGO_MSG_UNDEFINED";
}

$description=qv_babeltranslate($description);

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);

$j["userid"]=$userid;
$j["lastenvironid"]=$lastenvironid;
$j["lastroleid"]=$lastroleid;
$j["lastlanguageid"]=$lastlanguageid;
$j["lastcountrycode"]=$lastcountrycode;
$j["lastdebugmode"]=$lastdebugmode;
$j["email"]=$email;
$j["lstenviron"]=$lstenviron;
$j["lstrole"]=$lstrole;
$j["lstlanguage"]=$lstlanguage;
$j["lstcc"]=$lstcc;

print json_encode($j);
?>