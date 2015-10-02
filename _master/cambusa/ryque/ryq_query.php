<?php 
/****************************************************************************
* Name:            ryq_query.php                                            *
* Project:         Cambusa/ryQue                                            *
* Version:         1.69                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."rymaestro/maestro_macro.php";

$reqid=$_POST["reqid"];
$sql=ryqNormalize($_POST['sql']);
$args=$_POST['args'];
$lenkey=12;
$env_quiver=false;

if(is_array($args)){
    foreach($args as $key => $value)
       $sql=str_replace("[=$key]", ryqEscapize($value), $sql);
}

$env_name=file_get_contents("requests/".$reqid.".req");
include("../sysconfig.php");
include($path_databases."_environs/".$env_name.".php");

if(isset($env_lenid))
    $lenkey=$env_lenid;

// SOSTITUZIONE DELLE MACRO
$maestro=new Maestro();
$maestro->provider=$env_provider;
$maestro->lenid=$lenkey;
$sql=maestro_macro($maestro, $sql);
unset($maestro);

// CONTROLLO CHE NON SIA UNA QUERY DI AGGIORNAMENTO
$sql=preg_replace("/^[ \n\r\t]*SELECT[ \n\r\t]/i", "SELECT ", $sql, 1);
if(substr($sql, 0, 7)!="SELECT "){
    $sql="SELECT SYSID FROM QVSYSTEM WHERE 0=1";
}

// VALIDAZIONE DELLE QUERY PER DATABASE QUIVER
if($env_quiver){
    $inc=$path_applications."ryque/appvalidatequery.php";
    if(is_file($inc)){
        include_once $inc;
        $funct="appvalidatequery";
        if(function_exists($funct)){
            if(!$funct($sql)){
                $sql="SELECT SYSID FROM QVSYSTEM WHERE 0=1";
            }
        }
    }
}

$r=array();

switch($env_provider){
case "sqlite":
    $conn=x_sqlite_open($env_strconn);
    $res=x_sqlite_query($conn, $sql);
    if(!is_bool($res)){
        while($row=x_sqlite_fetch_array($res)){
            // RISOLVO I NULL
            foreach($row as $k => $v){
                if($v===null)
                    $row[$k]="";
            }
            $r[]=$row;
        }
        x_sqlite_finalize($res);
    }
    x_sqlite_close($conn);
    break;
case "mysql":
    $conn=mysqli_connect($env_host, $env_user, $env_password, $env_strconn);
    $res=mysqli_query($conn, $sql);
    while($riga=mysqli_fetch_assoc($res)){
        // RISOLVO I NULL
        foreach($riga as $k => $v){
            if($v===null)
                $riga[$k]="";
        }
        $r[]=$riga;
    }
    mysqli_free_result($res);
    mysqli_close($conn);
    break;
case "oracle":
    $conn=oci_connect($env_user, $env_password, $env_strconn);
    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS\".000Z\"'"));
    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS.FF3\"Z\"'"));
    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'"));
    $res=oci_parse($conn,$sql);
    oci_execute($res);
    while ($riga=oci_fetch_array($res, OCI_ASSOC+OCI_RETURN_NULLS)){
        // RISOLVO I CLOB E I NULL
        foreach($riga as $k => $v){
            if(is_object($v))
                $riga[$k]=$v->load();
            elseif($v===null)
                $riga[$k]="";
        }
        $r[]=$riga;
    }
    oci_free_statement($res);
    oci_close($conn);
    break;
case "db2odbc":
    $conn=odbc_connect($env_strconn, $env_user, $env_password, SQL_CUR_USE_DRIVER);
    if(($res=odbc_exec($conn, $sql))){
        odbc_longreadlen($res, 100000000);
        while($rows=odbc_fetch_array($res)){
            // SOSTITUISCO LA VIRGOLA DEI NUMERI E RISOLVO I NULL
            foreach($rows as $k => $v){
                if($v===null)
                    $rows[$k]="";
                elseif(preg_match("/^\d*,\d+$/", $v))
                    $rows[$k]=str_replace(",", ".", $v);
            }
            $r[]=$rows;
        }
    }
    odbc_free_result($res);
    odbc_close($conn);
    break;
default:
    $conn=odbc_connect($env_strconn, $env_user, $env_password, SQL_CUR_USE_DRIVER);
    if (($res=odbc_exec($conn, $sql))){
        odbc_longreadlen($res, 100000000);
        while($rows=odbc_fetch_array($res)){
            // RISOLVO I NULL
            foreach($rows as $k => $v){
                if($v===null)
                    $rows[$k]="";
            }
            $r[]=$rows;
        }
    }
    odbc_free_result($res);
    odbc_close($conn);
}
array_walk_recursive($r, "escapize");
print json_encode($r);

function escapize(&$value){
    //$value=utf8_decode(utf8_encode($value));
    if($value!=""){
        if(!mb_check_encoding($value, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            $value=utf8_encode($value);
        }
    }
}
?>