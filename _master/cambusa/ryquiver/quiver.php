<?php 
/****************************************************************************
* Name:            quiver.php                                               *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
try{
    include("_quiver.php");

    if(isset($_POST["sessionid"]))
        $sessionid=$_POST["sessionid"];
    else
        $sessionid="";

    if(isset($_POST["env"]))
        $env=$_POST["env"];
    else
        $env="";

    if(isset($_POST["bulk"]))
        $bulk=intval($_POST["bulk"]);
    else
        $bulk=0;

    if(isset($_POST["program"])){
        $statements=$_POST["program"];
    }
    else{
        if(isset($_POST["function"]))
            $statements=$_POST["function"];
        else
            $statements="";
    }
    
    if(isset($_POST["data"]))
        $bag=$_POST["data"];
    else
        $bag=array();

    print quiver_execute($sessionid, $env, $bulk, $statements, $bag);
}
catch(Exception $e){
    $jret=array();
    $jret["success"]=0;
    $jret["code"]="QVERR_UNKNOWN";
    $jret["params"]=array();
    $jret["message"]=$e->getMessage();
    $jret["SYSID"]="";
    $jret["infos"]=array();
    array_walk_recursive($jret, "maestro_escapize");
    print json_encode($jret);
}
?>