<?php 
/****************************************************************************
* Name:            ego_export.php                                           *
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
    if(isset($_POST["appid"]))
        $appid=ryqEscapize($_POST["appid"]);
    elseif(isset($_GET["appid"]))
        $appid=ryqEscapize($_GET["appid"]);
    else
        $appid="";
        
    // DETERMINO L'AMBIENTE
    if(isset($_POST["envid"]))
        $envid=ryqEscapize($_POST["envid"]);
    elseif(isset($_GET["envid"]))
        $envid=ryqEscapize($_GET["envid"]);
    else
        $envid="";
        
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
    
    $infos=array( "USERS" => array(), "ROLES" => array());

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
        // CONTROLLO VALIDITA' SESSIONE
        if(ego_validatesession($maestro, $sessionid, false, "export")==false){
            $success=0;
            $description="Sessione non valida";
        }

        if($success){
            $sql="SELECT EGOENVIRONUSER.USERID AS SYSID, EGOALIASES.NAME AS NAME, EGOALIASES.ADMINISTRATOR AS ADMINISTRATOR, EGOALIASES.EMAIL AS EMAIL FROM EGOENVIRONUSER INNER JOIN EGOALIASES ON EGOALIASES.USERID=EGOENVIRONUSER.USERID AND EGOALIASES.MAIN=1 WHERE EGOENVIRONUSER.ENVIRONID='$envid'";
            maestro_query($maestro, $sql, $r);
            for($i=0;$i<count($r);$i++){
                $infos["USERS"][$i]=array( 
                    "SYSID" => $r[$i]["SYSID"], 
                    "NAME" => htmlentities($r[$i]["NAME"]),
                    "ADMINISTRATOR" => $r[$i]["ADMINISTRATOR"],
                    "EMAIL" => htmlentities($r[$i]["EMAIL"])
                );
            }
            $sql="SELECT SYSID,DESCRIPTION FROM EGOROLES WHERE APPID='$appid'";
            maestro_query($maestro, $sql, $r);
            for($i=0;$i<count($r);$i++){
                $infos["ROLES"][$i]=array( "SYSID" => $r[$i]["SYSID"], "NAME" => htmlentities($r[$i]["DESCRIPTION"]) );
            }
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
catch(Exception $e){
    $success=0;
    $sessionid="";
    $description=$e->getMessage();
}

if($success==0){
    $infos=array( "USERS" => array(), "ROLES" => array());
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
$j["infos"]=$infos;
if($padding=="")
    print json_encode($j);
else        // Gestione JSONP (JSON con padding) per le richieste "cross domain"
    print $padding."(".json_encode($j).");";
?>