<?php 
/****************************************************************************
* Name:            ego_logout.php                                           *
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

// DETERMINO LA SESSIONID
if(isset($_POST["sessionid"]))
    $sessionid=ryqEscapize($_POST["sessionid"]);
elseif(isset($_GET["sessionid"]))
    $sessionid=ryqEscapize($_GET["sessionid"]);
else
    $sessionid="";

$appname="";
    
try{    
    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){
        // REPERISCO L'APPLICAZIONE PER UN EVENTUALE LOGOUT ESTERNO
        $sql="";
        $sql.="SELECT EGOAPPLICATIONS.NAME AS APPNAME ";
        $sql.="FROM EGOSESSIONS ";
        $sql.="INNER JOIN EGOENVIRONS ON EGOENVIRONS.SYSID=EGOSESSIONS.ENVIRONID ";
        $sql.="INNER JOIN EGOAPPLICATIONS ON EGOAPPLICATIONS.SYSID=EGOENVIRONS.APPID ";
        $sql.="WHERE EGOSESSIONS.SESSIONID='$sessionid'";
        maestro_query($maestro, $sql, $v);
        if(count($v)>0){
            $appname=$v[0]["APPNAME"];
        }
        // TERMINO LA SESSIONE
        if(strlen($sessionid)==$maestro->lenid)
            $sql="UPDATE EGOSESSIONS SET ENDTIME=[:NOW()] WHERE SYSID='$sessionid'";
        else
            $sql="UPDATE EGOSESSIONS SET ENDTIME=[:NOW()] WHERE SESSIONID='$sessionid'";
        maestro_execute($maestro, $sql);
    }

    // LOGOUT DA SISTEMI ESTERNI
    $external=$path_customize."ryego/custexternal_$appname.php";
    $funct="custegologout";
    if(is_file($external)){
        include_once $external;
        if(is_callable($funct)){
            $funct($maestro, $sessionid);
        }
    }

    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
catch(Exception $e){

}

// USCITA JSON
$j=array();
$j["success"]=1;
$j["description"]="";
print json_encode($j);
?>