<?php 
/****************************************************************************
* Name:            ryq_window.php                                           *
* Project:         Cambusa/ryQue                                            *
* Version:         1.69                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_macro.php";

$reqid=$_POST['reqid'];
$offset=$_POST['offset'];
$length=$_POST['length'];
$clause=$_POST['clause'];
$lenkey=12;
$more="";

clearstatcache();

$filereq="requests/".$reqid.".req";
if(is_file($filereq)){
    // TOUCH PER PROLUNGARE LA SCADENZA DELLA RICHIESTA
    if(time()-@filemtime($filereq)>60*60){
        @touch($filereq);
    }
    $env_name=file_get_contents("requests/".$reqid.".req");
    $select=file_get_contents("requests/".$reqid.".slt");
    $from=file_get_contents("requests/".$reqid.".tbl");
    
    include($path_databases."_environs/".$env_name.".php");

    if(isset($env_lenid))
        $lenkey=$env_lenid;

    $fp=fopen("requests/".$reqid.".ndx","r");
    fseek($fp, ($lenkey+1)*($offset-1));
    $buff=fread($fp,($lenkey+1)*$length-1);
    fclose($fp);

    $elenco=explode("|",$buff);
    
    if(count($elenco)>0){
        $elencoin="'".implode("','", $elenco)."'";
        $r=array_fill(0, count($elenco), array());
    }
    else{
        $elencoin="''";
        $r=array();
    }
    if($select!="")
        $select.=",";
    $select.="SYSID AS RYQUEWINID";
    
    // SOSTITUZIONE DELLE MACRO
    $maestro=new Maestro();
    $maestro->provider=$env_provider;
    $maestro->lenid=$lenkey;
    $select=maestro_macro($maestro,$select);
    unset($maestro);

    if(is_array($clause)){
        if($env_provider!="oracle"){
            foreach($clause as $key => $value)
                $more.=" AND $key='$value'";
        }
        else{
            foreach($clause as $key => $value)
                $more.=" AND $key=:$key";
        }
    }
    
    $winsql="SELECT $select FROM $from WHERE SYSID IN ($elencoin) $more";

    switch($env_provider){
    case "sqlite":
        $conn=x_sqlite_open($env_strconn);
        $res=x_sqlite_query($conn, $winsql);
        if(!is_bool($res)){
            while($row=x_sqlite_fetch_array($res)){
                $i=array_search($row["RYQUEWINID"], $elenco);
                if($i!==false){
                    $r[$i]=$row;
                }
            }
            x_sqlite_finalize($res);
        }
        x_sqlite_close($conn);
        break;
    case "mysql":
        $conn=mysqli_connect($env_host, $env_user, $env_password, $env_strconn);
        if($res=mysqli_query($conn, $winsql)){
            while($row=mysqli_fetch_assoc($res)){
                $i=array_search($row["RYQUEWINID"], $elenco);
                if($i!==false){
                    $r[$i]=$row;
                }
            }
            mysqli_free_result($res);
        }
        mysqli_close($conn);
        break;
    case "oracle":
        $conn = oci_connect($env_user, $env_password, $env_strconn);
        oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS\".000Z\"'"));
        oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS.FF3\"Z\"'"));
        oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'"));
        $res=oci_parse($conn, $winsql);
        if(is_array($clause)){
            foreach($clause as $key => $value)
                oci_bind_by_name($res, ":$key", $value);
        }
        if(oci_execute($res)){
            while($row=oci_fetch_array($res, OCI_ASSOC+OCI_RETURN_NULLS)){
                // RISOLVO I CLOB E I NULL
                foreach($row as $k => $v){
                    if(is_object($v))
                        $row[$k]=$v->load();
                    elseif($v===null)
                        $row[$k]="";
                }
                // TRAVASO
                $i=array_search($row["RYQUEWINID"], $elenco);
                if($i!==false){
                    $r[$i]=$row;
                }
            }
        }
        oci_free_statement($res);
        oci_close($conn);
        break;
    case "db2odbc":
        $conn=odbc_connect($env_strconn, $env_user, $env_password, 1);
        if($res=odbc_exec($conn, $winsql)){
            odbc_longreadlen($res, 100000000);
            while($row=odbc_fetch_array($res)){
                // SOSTITUISCO LA VIRGOLA DEI NUMERI
                foreach($row as $k => $v){
                    if(preg_match("/^\d*,\d+$/", $v))
                        $row[$k]=str_replace(",", ".", $v);
                }
                $i=array_search($row["RYQUEWINID"], $elenco);
                if($i!==false){
                    $r[$i]=$row;
                }
            }
        }
        odbc_free_result($res);
        odbc_close($conn);
        break;
    default:
        $conn=odbc_connect($env_strconn, $env_user, $env_password, 1);
        if($res=odbc_exec($conn, $winsql)){
            odbc_longreadlen($res, 100000000);
            while($row=odbc_fetch_array($res)){
                $i=array_search($row["RYQUEWINID"], $elenco);
                if($i!==false){
                    $r[$i]=$row;
                }
            }
        }
        odbc_free_result($res);
        odbc_close($conn);
    }
}
else{
    $r=array();
}
array_walk_recursive($r, "escapize");
print json_encode($r);

function escapize(&$sql){
    $sql=utf8_decode(utf8_encode($sql));
}
?>