<?php 
/****************************************************************************
* Name:            food4statistics.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tocambusa="../../cambusa/";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryquiver/_quiver.php";

$food="#";

// DETERMINAZIONE DATABASE
if(isset($_POST["env"]))
    $env=$_POST["env"];
elseif(isset($_GET["env"]))
    $env=$_GET["env"];
else
    $env="";

// DETERMINAZIONE SITO
if(isset($_POST["site"]))
    $site=ryqEscapize($_POST["site"]);
elseif(isset($_GET["site"]))
    $site=ryqEscapize($_GET["site"]);
else
    $site="";

// DETERMINAZIONE PAGINA VISITATA
if(isset($_POST["id"]))
    $PAGEID=ryqEscapize($_POST["id"]);
elseif(isset($_GET["id"]))
    $PAGEID=ryqEscapize($_GET["id"]);
else
    $PAGEID="";
    
// DETERMINAZIONE DEL FILE SCARICATO
if(isset($_POST["fileid"]))
    $FILEID=ryqEscapize($_POST["fileid"]);
elseif(isset($_GET["fileid"]))
    $FILEID=ryqEscapize($_GET["fileid"]);
else
    $FILEID="";
    
// DETERMINAZIONE IP
if(isset($_POST["ip"]))
    $IPADDR=ryqEscapize($_POST["ip"]);
elseif(isset($_GET["ip"]))
    $IPADDR=ryqEscapize($_GET["ip"]);
else
    $IPADDR=$_SERVER["REMOTE_ADDR"];

// DETERMINAZIONE UTENTE
if(isset($_POST["user"])){
    $USERID=ryqEscapize($_POST["user"]);
}
elseif(isset($_GET["user"])){
    $USERID=ryqEscapize($_GET["user"]);
}
else{
    if(isset($_COOKIE['FLBUSER']))
        $USERID=ryqEscapize($_COOKIE['FLBUSER']);
    else
        $USERID="";
}

// DETERMINAZIONE BROWSER
if(isset($_POST["browser"]))
    $browser=ryqEscapize($_POST["browser"]);
elseif(isset($_GET["browser"]))
    $browser=ryqEscapize($_GET["browser"]);
else
    $browser="unknown";

    
if($env!="" && $site!=""){
    // APRO IL DATABASE
    $maestro=maestro_opendb($env, false);

    // DETERMINO IL SITO
    maestro_query($maestro, "SELECT * FROM QW_WEBSITES WHERE [:UPPER(NAME)]='".strtoupper($site)."'", $r);
    if(count($r)==1){
        $SITEID=$r[0]["SYSID"];
        $DEFAULTID=$r[0]["DEFAULTID"];
        if($PAGEID=="" && $FILEID==""){
            $PAGEID=$DEFAULTID;
        }
        if(strlen($USERID)!=$maestro->lenid && $USERID!="@"){
            $USERID=qv_createsysid($maestro);
        }
        $STATISTICS=intval($r[0]["LOGSTATISTICS"]);
        
        if(isset($_SERVER['HTTP_USER_AGENT']))
            $AGENT=$_SERVER['HTTP_USER_AGENT'];
        else
            $AGENT=$browser;
        
        if($STATISTICS){
            $nobot=true;
            // CONTROLLI PER ESCLUDERE I BOT
            if(preg_match("/Googlebot/i", $AGENT)){
                $nobot=false;
            }
            if($nobot){
                $program=array();
                // INSERIMENTO VISITA
                $program[0]=array();
                $program[0]["function"]="objects_insert";
                $program[0]["data"]=array();
                $program[0]["data"]["DESCRIPTION"]=$site;
                $program[0]["data"]["REGISTRY"]=$AGENT;
                $program[0]["data"]["TYPOLOGYID"]=qv_actualid($maestro, "0WEBSTATIST0");
                $program[0]["data"]["AUXTIME"]=date("Ymd");
                $program[0]["data"]["REFERENCE"]=$IPADDR;
                $program[0]["data"]["SITEID"]=$SITEID;
                $program[0]["data"]["CONTENTID"]=$PAGEID;
                $program[0]["data"]["FILEID"]=$FILEID;
                $program[0]["data"]["USERID"]=$USERID;
                $program[0]["data"]["BROWSER"]=$browser;
                $json=quiver_execute($public_sessionid, $env, false, $program);
            }
        }
        $food=$USERID;
    }
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
print $food;
?>