<?php 
/****************************************************************************
* Name:            quiver.php                                               *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
try{
    include_once "_quiver.php";

    $params=array();
    
    $rtype=1;
    if(isset($_POST["xml"])){
        $rtype=2;
        include_once "quiverxml.php";
        _qv_loadxml($_POST["xml"]);
    }
    $params["return"]=$rtype;

    if(isset($_POST["sessionid"]))
        $params["sessionid"]=$_POST["sessionid"];

    if(isset($_POST["env"]))
        $params["environ"]=$_POST["env"];

    if(isset($_POST["progressid"]))
        $params["progressid"]=$_POST["progressid"];

    if(isset($_POST["bulk"]))
        $params["bulk"]=intval($_POST["bulk"]);

    if(isset($_POST["program"]))
        $params["program"]=$_POST["program"];
        
    if(isset($_POST["function"]))
        $params["function"]=$_POST["function"];
    
    if(isset($_POST["data"]))
        $params["data"]=$_POST["data"];

    if(isset($_POST["space"]))
        $params["spacename"]=$_POST["space"];
    
    print quiver_execute($params);
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