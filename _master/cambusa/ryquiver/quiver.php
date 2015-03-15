<?php 
/****************************************************************************
* Name:            quiver.php                                               *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
try{
    include_once "_quiver.php";

    $rtype=1;
    if(isset($_POST["xml"])){
        $rtype=2;
        include_once "quiverxml.php";
        _qv_loadxml($_POST["xml"]);
    }

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

    print quiver_execute($sessionid, $env, $bulk, $statements, $bag, $rtype);
}
catch(Exception $e){
    $jret=array();
    $jret["success"]=0;
    $jret["code"]="QVERR_UNKNOWN";
    $jret["params"]=array();
    $jret["message"]=$e->getMessage();
    $jret["SYSID"]="";
    $jret["infos"]=array();
    switch($rtype){
        case 1:
            array_walk_recursive($jret, "quiver_escapize");
            print json_encode($jret);
            break;
        case 2:
            print _qv_savexml($jret);
            break;
        default:
            print serialize($jret);
    }
}
?>