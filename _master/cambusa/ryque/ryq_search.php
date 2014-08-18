<?php 
/****************************************************************************
* Name:            ryq_search.php                                           *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";

$tr=Array();
$tr["\'"]="'";
$tr["\\\""]="\"";
$tr["\\\\"]="\\";

$reqid=$_POST['reqid'];
if(isset($_POST['criteria']))
    $criteria=$_POST['criteria'];
else
    $criteria=array();

// REPERIMENTO AMBIENTE
$env_name=file_get_contents("requests/".$reqid.".req");

// REPERIMENTO FILE INDICE
$indexes=file_get_contents("requests/".$reqid.".ndx");

// REPERIMENTO TABELLA
$from=file_get_contents("requests/".$reqid.".tbl");

// APERTURA DATABASE
$maestro=maestro_opendb($env_name, false);
$lenid=$maestro->lenid+1;

$where="";
$in="'" . str_replace("|", "','", $indexes) . "'";
$indexes="|".$indexes."|";

if(isset($criteria["where"])){
    $where="(" . strtr($criteria["where"], $tr) . ")";
    $where.=" AND SYSID IN (" . $in . ")";
}

if(isset($criteria["gauge"])){
    $gauge=floatval($criteria["gauge"]);
    $name=$criteria["name"];
    if(is_file($tocambusa."ryque/ryq_gauge.php"))
        include_once $tocambusa."ryque/ryq_gauge.php";
    elseif(is_file($tocambusa."ryque/ryq_gauge.phar"))
        include_once "phar://".$tocambusa."ryque/ryq_gauge.phar/ryq_gauge.php";
    else
        include_once $tocambusa."ryque/ryq_gaugeminus.php";
    
    $values=array();
    $refs=array();
    
    if($where==""){
        $where="SYSID IN (" . $in . ")";
    }

    maestro_query($maestro, "SELECT SYSID, $name AS _NAME FROM $from WHERE $where", $r);
    for($i=0; $i<count($r); $i++){
        $SYSID=$r[$i]["SYSID"];
        $p=strpos($indexes, "|".$SYSID."|");
        $refs[]=($p/$lenid)+1;
        $values[]=floatval($r[$i]["_NAME"]);
    }
    unset($r);
    
    $s=zerosearch($reqid, array("gauge" => $gauge), $values, $refs);
}
elseif(count($criteria)==0){
    if(is_file("requests/".$reqid.".sts")){
        if(is_file($tocambusa."ryque/ryq_gauge.php"))
            include_once $tocambusa."ryque/ryq_gauge.php";
        elseif(is_file($tocambusa."ryque/ryq_gauge.phar"))
            include_once "phar://".$tocambusa."ryque/ryq_gauge.phar/ryq_gauge.php";
        else
            include_once $tocambusa."ryque/ryq_gaugeminus.php";
        
        $s=zerosearch($reqid);
    }
    else{
        $s=array();
    }
}
else{
    if($where==""){
        $where="SYSID IN (" . $in . ")";
    }

    $s=array();

    // QUERY FINALE DI REPERIMENTO SYSID
    maestro_query($maestro, "SELECT SYSID FROM $from WHERE $where", $r);

    for($i=0; $i<count($r); $i++){
        $SYSID=$r[$i]["SYSID"];
        $p=strpos($indexes, "|".$SYSID."|");
        $s[]=($p/$lenid)+1;
    }
}

// CHIUSURA DATABASE
maestro_closedb($maestro);

sort($s);
print json_encode($s);
?>