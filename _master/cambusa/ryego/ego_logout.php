<?php 
/****************************************************************************
* Name:            ego_logout.php                                           *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
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

// APRO IL DATABASE
$maestro=maestro_opendb("ryego");
if($maestro->conn!==false){
    // TERMINO LA SESSIONE
    if(strlen($sessionid)==24)
        $sql="UPDATE EGOSESSIONS SET ENDTIME=[:NOW()] WHERE SESSIONID='$sessionid'";
    else
        $sql="UPDATE EGOSESSIONS SET ENDTIME=[:NOW()] WHERE SYSID='$sessionid'";
    maestro_execute($maestro, $sql);
}

// CHIUDO IL DATABASE
maestro_closedb($maestro);

// USCITA JSON
$j=array();
$j["success"]=1;
$j["description"]="";
print json_encode($j);
?>