<?php 
/****************************************************************************
* Name:            egoaction_last.php                                       *
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
include_once $tocambusa."ryego/ego_util.php";

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

    // DETERMINO L'AMBIENTE
    if(isset($_POST["environid"]))
        $environid=ryqEscapize($_POST["environid"]);
    else
        $environid="";

    // DETERMINO IL RUOLO
    if(isset($_POST["roleid"]))
        $roleid=ryqEscapize($_POST["roleid"]);
    else
        $roleid="";

    // DETERMINO LA LINGUA
    if(isset($_POST["languageid"]))
        $languageid=ryqEscapize($_POST["languageid"]);
    else
        $languageid="";

    // DETERMINO IL PAESE
    if(isset($_POST["countrycode"]))
        $countrycode=ryqEscapize($_POST["countrycode"]);
    else
        $countrycode="";

    // DETERMINO LO STATO DI DEBUGGING
    if(isset($_POST["debugmode"]))
        $debugmode=intval($_POST["debugmode"]);
    else
        $debugmode=-1;

    // DETERMINO L'EMAIL
    if(isset($_POST["email"]))
        $email=$_POST["email"];
    else
        $email="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Le nuove impostazioni sono state registrate";
    $babelcode="EGO_MSG_SETSUCCESSFUL";
    
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
            if($environid!=""){
                // AGGIORNO EGOSETUP CON L'AMBIENTE
                $sql="UPDATE EGOSETUP SET ENVIRONID='".$environid."' WHERE APPID='".$appid."' AND ALIASID='".$aliasid."'";
                maestro_execute($maestro, $sql);
                
                // AGGIORNO LA SESSIONE
                $sql="UPDATE EGOSESSIONS SET ENVIRONID='".$environid."' WHERE SESSIONID='".$sessionid."'";
                maestro_execute($maestro, $sql);
            }
            if($roleid!=""){
                // AGGIORNO EGOSETUP CON IL RUOLO
                $sql="UPDATE EGOSETUP SET ROLEID='".$roleid."' WHERE APPID='".$appid."' AND ALIASID='".$aliasid."'";
                maestro_execute($maestro, $sql);
                
                // AGGIORNO LA SESSIONE
                $sql="UPDATE EGOSESSIONS SET ROLEID='".$roleid."' WHERE SESSIONID='".$sessionid."'";
                maestro_execute($maestro, $sql);
            }
            if($languageid!=""){
                $sql="SELECT NAME FROM EGOLANGUAGES WHERE SYSID='$languageid'";
                maestro_query($maestro, $sql, $r);
                if(count($r)>0){
                    $global_lastlanguage=$r[0]["NAME"];
            
                    // AGGIORNO EGOSETUP CON LA LINGUA
                    $sql="UPDATE EGOSETUP SET LANGUAGEID='".$languageid."' WHERE APPID='".$appid."' AND ALIASID='".$aliasid."'";
                    maestro_execute($maestro, $sql);
                    
                    // AGGIORNO LA SESSIONE (SE NON E' UNA SESSIONE EGO)
                    $sql="UPDATE EGOSESSIONS SET LANGUAGEID='".$languageid."' WHERE SESSIONID='".$sessionid."'";
                    maestro_execute($maestro, $sql);
                    
                    // MEMORIZZO LA SCELTA ANCHE IN UN COOKIE
                    setcookie("_egolanguage", $global_lastlanguage, time()+4000000);
                }
            }
            if($countrycode!=""){
                // AGGIORNO EGOSETUP CON IL PAESE
                $sql="UPDATE EGOSETUP SET COUNTRYCODE='".$countrycode."' WHERE APPID='".$appid."' AND ALIASID='".$aliasid."'";
                maestro_execute($maestro, $sql);
                
                // AGGIORNO LA SESSIONE
                $sql="UPDATE EGOSESSIONS SET COUNTRYCODE='".$countrycode."' WHERE SESSIONID='".$sessionid."'";
                maestro_execute($maestro, $sql);
            }
            if($debugmode!=-1){
                // AGGIORNO EGOSETUP CON LO STATO DI DEBUGGING
                $sql="UPDATE EGOSETUP SET DEBUGMODE='".$debugmode."' WHERE APPID='".$appid."' AND ALIASID='".$aliasid."'";
                maestro_execute($maestro, $sql);
                
                // AGGIORNO LA SESSIONE
                $sql="UPDATE EGOSESSIONS SET DEBUGMODE='".$debugmode."' WHERE SESSIONID='".$sessionid."'";
                maestro_execute($maestro, $sql);
            }
            if($email!=""){
                $sql="UPDATE EGOALIASES SET EMAIL='$email' WHERE SYSID='$aliasid'";
                maestro_execute($maestro, $sql);
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
$j["description"]=$description;
array_walk_recursive($j, "ego_escapize");
print json_encode($j);
?>