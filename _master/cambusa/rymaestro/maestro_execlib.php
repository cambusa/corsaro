<?php 
/****************************************************************************
* Name:            maestro_execlib.php                                      *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.69                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."sysconfig.php";    
include_once $tocambusa."rymaestro/maestro_sqlite.php";
include_once $tocambusa."rymaestro/maestro_macro.php";
include_once $tocambusa."rygeneral/writelog.php";
include_once $tocambusa."rygeneral/unicode.php";
include_once $tocambusa."rygeneral/json_loader.php";

class Maestro{
    public $conn;
    public $provider;
    public $environ;
    public $lenid;
    public $master;
    public $monad;
    public $ego;
    public $quiver;
    public $transon;
    public $errdescr;
    public $rows;
    public $infobase;
    public $logonly;
    public $strconn;
    public $user;
    function Maestro(){
        $this->conn=false;
        $this->provider="";
        $this->environ="";
        $this->lenid=12;
        $this->master="";
        $this->monad=false;
        $this->ego=false;
        $this->quiver=false;
        $this->transon=false;
        $this->errdescr="";
        $this->rows=0;
        $this->infobase=false;
        $this->logonly=false;
        $this->strconn="";
        $this->user="";
    }
    public function loadinfo(){
        global $path_databases;
        try{
            if($this->infobase===false){
                if($this->master!=""){
                    if(substr($this->master,0,1)=="/" || substr($this->master,1,1)==":"){
                        $base=dirname($this->master)."/";
                        $json=basename($this->master);
                    }
                    else{
                        $base=$path_databases."_maestro/";
                        $json=$this->master;
                    }
                    // LETTURA DOCUMENTO JSON
                    $this->infobase=json_load($base, $json);
                }
            }
        }
        catch(Exception $e){
            $this->infobase=false;
            log_write( $e->getMessage() );
        }
    }
}

function maestro_opendb($env, $raise=true){
    global $path_databases;
    try{
        $env_maestro="";
        $env_monad=false;
        $env_ego=false;
        $env_quiver=false;
        $env_provider="";
        $env_lenid=12;
        $env_strconn="";
        $env_user="";
        $conn=false;
        $errdescr="";
        if(is_file($path_databases."_environs/".$env.".php")){
            include($path_databases."_environs/".$env.".php");
            switch($env_provider){
            case "sqlite":
                if(is_file($env_strconn)){
                    if(!$conn=@x_sqlite_open($env_strconn, $errdescr)){
                        $conn=false;
                        log_write("Connection\r\n--->".$errdescr);
                    }
                }
                else{
                    $conn=false;
                    $errdescr="Database doesn't exist";
                    log_write("Connection\r\n--->".$errdescr);
                }
                break;
            case "mysql":
                $conn=@mysqli_connect($env_host, $env_user, $env_password, $env_strconn);
                if(mysqli_connect_errno()){
                    $conn=false;
                    $errdescr=mysqli_connect_error();
                    log_write("Connection\r\n--->".$errdescr);
                }
                break;
            case "oracle":
                if($conn=@oci_connect($env_user, $env_password, $env_strconn)){
                    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS\".000Z\"'"));
                    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS.FF3\"Z\"'"));
                    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'"));
                }
                else{
                    $conn=false;
                    $me=oci_error();
                    $errdescr=$me["message"];
                    log_write("Connection\r\n--->".$errdescr);
                }
                break;
            default:
                if($conn=@odbc_connect($env_strconn, $env_user, $env_password, 1)){
                    if($env_provider=="sqlserver"){
                        odbc_exec($conn, "SET TEXTSIZE 2147483647");
                    }
                }
                else{
                    $conn=false;
                    $errdescr=odbc_errormsg();
                    log_write("Connection\r\n--->".$errdescr);
                }
            }
        }
        else{
            $errdescr="Incorrect database name";
        }
        $maestro=new Maestro();
        $maestro->conn=$conn;
        $maestro->provider=$env_provider;
        $maestro->environ=$env;
        $maestro->lenid=$env_lenid;
        $maestro->master=$env_maestro;
        $maestro->monad=$env_monad;
        $maestro->ego=$env_ego;
        $maestro->quiver=$env_quiver;
        $maestro->strconn=$env_strconn;
        $maestro->user=$env_user;
        $maestro->errdescr=$errdescr;
    }
    catch(Exception $e){
        $maestro=new Maestro();
        $maestro->errdescr=$e->getMessage();
        log_write($maestro->errdescr);
    }
    if($maestro->conn===false && $raise)
        throw new Exception( $maestro->errdescr );
    return $maestro;
}
function maestro_closedb(&$maestro){
    try{
        if($maestro->conn!==false){
            switch($maestro->provider){
            case "sqlite":
                @x_sqlite_close($maestro->conn);
                break;
            case "mysql":
                @mysqli_close($maestro->conn);
                break;
            case "oracle":
                @oci_close($maestro->conn);
                break;
            default:
                @odbc_close($maestro->conn);
            }
        }
    }
    catch(Exception $e){}
    unset($maestro);
}
function maestro_querytype($maestro, $sql){
    try{
        if(strtoupper(substr(trim($sql), 0, 7))=="SELECT ")
            return true;
        else
            return false;
    }
    catch(Exception $e){
        return false;
    }
}
function maestro_query($maestro, $sql, &$r, $raise=true){
    try{
        $r=array();
        $ret=true;
        $maestro->errdescr="";
        // SOSTITUZIONE DELLE MACRO
        $sql=maestro_macro($maestro, $sql);
        // CONTROLLO CHE NON SIA UNA QUERY DI AGGIORNAMENTO
        $sql=preg_replace("/^[ \n\r\t]*SELECT[ \n\r\t]/i", "SELECT ", $sql, 1);
        if(substr($sql, 0, 7)!="SELECT "){
            $ret=false;
            $maestro->errdescr="SQL is not a SELECT query";
            log_write($sql.";\r\n--->" . $maestro->errdescr);
        }
        if($ret){
            switch($maestro->provider){
            case "sqlite":
                $res=false;
                $res=@x_sqlite_query($maestro->conn, $sql);
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
                elseif($res==false){
                    $ret=false;
                    $maestro->errdescr=x_sqlite_error_string($maestro->conn, x_sqlite_last_error($maestro->conn));
                    log_write($sql.";\r\n--->" . $maestro->errdescr);
                }
                break;
            case "mysql":
                if(@$resloc=mysqli_query($maestro->conn, $sql)){
                    while($rows=mysqli_fetch_assoc($resloc)){
                        // RISOLVO I NULL
                        foreach($rows as $k => $v){
                            if($v===null)
                                $rows[$k]="";
                        }
                        // TRAVASO
                        $r[]=$rows;
                    }
                    mysqli_free_result($resloc);
                }
                else{
                    $ret=false;
                    $maestro->errdescr=mysqli_error($maestro->conn);
                    log_write($sql.";\r\n--->" . $maestro->errdescr);
                }
                break;
            case "oracle":
                if($maestro->transon)
                    $mode=OCI_NO_AUTO_COMMIT;
                else
                    $mode=OCI_COMMIT_ON_SUCCESS;
                $resloc=oci_parse($maestro->conn,$sql);
                if(@oci_execute($resloc, $mode)){
                    while($rows=oci_fetch_array($resloc, OCI_ASSOC+OCI_RETURN_NULLS)){
                        // RISOLVO I CLOB E I NULL
                        foreach($rows as $k => $v){
                            if(is_object($v))
                                $rows[$k]=$v->load();
                            elseif($v===null)
                                $rows[$k]="";
                        }
                        // TRAVASO
                        $r[]=$rows;
                    }
                }
                else{
                    $ret=false;
                    $me=oci_error($resloc);
                    $maestro->errdescr=$me["message"];
                    log_write($sql.";\r\n--->" . $maestro->errdescr);
                }
                @oci_free_statement($resloc);
                break;
            case "db2odbc":
                if($resloc=@odbc_exec($maestro->conn, $sql)){
                    odbc_longreadlen($resloc, 100000000);
                    while($rows=odbc_fetch_array($resloc)){
                        // SOSTITUISCO LA VIRGOLA DEI NUMERI E RISOLVO I NULL
                        foreach($rows as $k => $v){
                            if($v===null)
                                $rows[$k]="";
                            elseif(preg_match("/^\d*,\d+$/", $v))
                                $rows[$k]=str_replace(",", ".", $v);
                        }
                        // TRAVASO
                        $r[]=$rows;
                    }
                    odbc_free_result($resloc);
                }
                else{
                    $ret=false;
                    $maestro->errdescr=odbc_errormsg($maestro->conn);
                    log_write($sql.";\r\n--->" . $maestro->errdescr);
                }
                break;
            default:
                if($resloc=@odbc_exec($maestro->conn, $sql)){
                    odbc_longreadlen($resloc, 100000000);
                    while($rows=odbc_fetch_array($resloc)){
                        // RISOLVO I NULL
                        foreach($rows as $k => $v){
                            if($v===null)
                                $rows[$k]="";
                        }
                        // TRAVASO
                        $r[]=$rows;
                    }
                    odbc_free_result($resloc);
                }
                else{
                    $ret=false;
                    $maestro->errdescr=odbc_errormsg($maestro->conn);
                    log_write($sql.";\r\n--->" . $maestro->errdescr);
                }
            }
        }
    }
    catch(Exception $e){
        $ret=false;
        $maestro->errdescr=$e->getMessage();
        log_write($sql.";\r\n--->" . $maestro->errdescr);
    }
    if(!$ret && $raise)
        throw new Exception( $maestro->errdescr . " ===> " . $sql );
    return $ret;
}
function maestro_execute($maestro, $sql, $raise=true, $clobs=false){
    try{
        $ret=true;
        $maestro->rows=0;
        $maestro->errdescr="";
        // SOSTITUZIONE DELLE MACRO
        $sql=maestro_macro($maestro,$sql);
        switch($maestro->provider){
        case "sqlite":
            if(@x_sqlite_exec($maestro->conn, $sql)){
                $maestro->rows=x_sqlite_changes($maestro->conn);
            }
            else{
                $ret=false;
                $maestro->errdescr=x_sqlite_error_string($maestro->conn, x_sqlite_last_error($maestro->conn));
                log_write($sql.";\r\n--->" . $maestro->errdescr);
            }
            break;
        case "mysql":
            if(mysqli_query($maestro->conn, $sql)){
                $maestro->rows=mysqli_affected_rows($maestro->conn);
            }
            else{
                $ret=false;
                $maestro->errdescr=mysqli_error($maestro->conn);
                log_write($sql.";\r\n--->" . $maestro->errdescr);
            }
            break;
        case "oracle":
            if($maestro->transon)
                $mode=OCI_NO_AUTO_COMMIT;
            else
                $mode=OCI_COMMIT_ON_SUCCESS;
            $resloc=oci_parse($maestro->conn, $sql);
            
            // GESTIONE CLOB
            if($clobs){
                foreach($clobs as $id => $clob){
                    oci_bind_by_name($resloc, ":$id", $clob);
                }
            }

            if(@oci_execute($resloc, $mode)){
                $maestro->rows=oci_num_rows($resloc);
            }
            else{
                $ret=false;
                $me=oci_error($resloc);
                $maestro->errdescr=$me["message"];
                log_write($sql.";\r\n--->" . $maestro->errdescr);
            }
            @oci_free_statement($resloc);
            break;
        default:
            if($resloc=@odbc_prepare($maestro->conn, $sql)){
                if($clobs)
                    $retex=@odbc_execute($resloc, $clobs);
                else
                    $retex=@odbc_execute($resloc);
                if($retex){
                    $maestro->rows=odbc_num_rows($resloc);
                }
                else{
                    $ret=false;
                    $maestro->errdescr=odbc_errormsg($maestro->conn);
                    log_write($sql.";\r\n--->" . $maestro->errdescr);
                }
            }
            else{
                $ret=false;
                $maestro->errdescr=odbc_errormsg($maestro->conn);
                log_write($sql.";\r\n--->" . $maestro->errdescr);
            }
        }
    }
    catch(Exception $e){
        $ret=false;
        $maestro->errdescr=$e->getMessage();
        log_write($sql.";\r\n--->" . $maestro->errdescr);
    }
    if(!$ret && $raise)
        throw new Exception( $maestro->errdescr . " ===> " . $sql );
    return $ret;
}
function maestro_begin($maestro, $raise=true){
    try{
        $ret=true;
        $sql="BEGIN";
        switch($maestro->provider){
        case "sqlite":
            $ret=@x_sqlite_exec($maestro->conn, "BEGIN;");
            if(!$ret){
                $ret=false;
                $d=x_sqlite_error_string($maestro->conn, x_sqlite_last_error($maestro->conn));
                $maestro->errdescr=$d;
                log_write($sql.";\r\n--->".$d);
            }
            break;
        case "mysql":
            $ret=@mysqli_autocommit($maestro->conn, false);
            if(!$ret){
                $ret=false;
                $d=mysqli_error($maestro->conn);
                $maestro->errdescr=$d;
                log_write($sql.";\r\n--->".$d);
            }
            break;
        case "oracle":
            break;
        default:
            $ret=@odbc_autocommit($maestro->conn, false);
            if(!$ret){
                $ret=false;
                $d=odbc_errormsg($maestro->conn);
                $maestro->errdescr=$d;
                log_write($sql.";\r\n--->".$d);
            }
        }
    }
    catch(Exception $e){
        $ret=false;
        $d=$e->getMessage();
        $this->errdescr=$d;
        log_write($d);
    }
    if($ret)
        $maestro->transon=true;
    if(!$ret && $raise)
        throw new Exception( $maestro->errdescr );
    return $ret;
}
function maestro_commit($maestro, $raise=true){
    try{
        $ret=true;
        $sql="COMMIT";
        if($maestro->transon){
            switch($maestro->provider){
            case "sqlite":
                $ret=@x_sqlite_exec($maestro->conn, "COMMIT;");
                if(!$ret){
                    $ret=false;
                    $d=x_sqlite_error_string($maestro->conn, x_sqlite_last_error($maestro->conn));
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }
                break;
            case "mysql":
                $ret=@mysqli_commit($maestro->conn);
                if(!$ret){
                    $ret=false;
                    $d=mysqli_error($maestro->conn);
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }
                break;
            case "oracle":
                $ret=@oci_commit($maestro->conn);
                if(!$ret){
                    $ret=false;
                    $me=oci_error($resloc);
                    $d=$me["message"];
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }            
                break;
            default:
                $ret=@odbc_commit($maestro->conn);
                if(!$ret){
                    $ret=false;
                    $d=odbc_errormsg($maestro->conn);
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }
            }
        }
    }
    catch(Exception $e){
        $ret=false;
        $d=$e->getMessage();
        $maestro->errdescr=$d;
        log_write($d);
    }
    $maestro->transon=false;
    if(!$ret && $raise)
        throw new Exception( $maestro->errdescr );
    return $ret;
}
function maestro_rollback($maestro, $raise=true){
    try{
        $ret=true;
        $sql="ROLLBACK";
        if($maestro->transon){
            switch($maestro->provider){
            case "sqlite":
                $ret=@x_sqlite_exec($maestro->conn, "ROLLBACK;");
                if(!$ret){
                    $ret=false;
                    $d=x_sqlite_error_string($maestro->conn, x_sqlite_last_error($maestro->conn));
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }
                break;
            case "mysql":
                $ret=@mysqli_rollback($maestro->conn);
                if(!$ret){
                    $ret=false;
                    $d=mysqli_error($maestro->conn);
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }
                break;
            case "oracle":
                $ret=@oci_rollback($maestro->conn);
                if(!$ret){
                    $ret=false;
                    $me=oci_error($resloc);
                    $d=$me["message"];
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }            
                break;
            default:
                $ret=@odbc_rollback($maestro->conn);
                if(!$ret){
                    $ret=false;
                    $d=odbc_errormsg($maestro->conn);
                    $maestro->errdescr=$d;
                    log_write($sql.";\r\n--->".$d);
                }
            }
        }
    }
    catch(Exception $e){
        $ret=false;
        $d=$e->getMessage();
        $maestro->errdescr=$d;
        log_write($d);
    }
    $maestro->transon=false;
    if(!$ret && $raise)
        throw new Exception( $maestro->errdescr );
    return $ret;
}
function maestro_solvetype($maestro, $tp, $sz, $ky, $nn, $uq){
    switch(strtoupper($tp)){
        case "SYSID":
            if($ky){
                $dbtype="CHAR(".$maestro->lenid.")";
            }
            else{
                if($maestro->provider=="oracle")
                    $dbtype="VARCHAR2(".$maestro->lenid.")";
                else
                    $dbtype="VARCHAR(".$maestro->lenid.")";
            }
            break;
        case "CHAR":
            if($sz==0){
                $sz=50;
            }
            $dbtype="CHAR(".$sz.")";
            break;
        case "VARCHAR":
            if($sz==0){
                $sz=50;
            }
            if($maestro->provider=="oracle")
                $dbtype="VARCHAR2(".$sz.")";
            else
                $dbtype="VARCHAR(".$sz.")";
            break;
        case "JSON":
            if($sz>0){
                if($maestro->provider=="oracle")
                    $dbtype="VARCHAR2(".$sz.")";
                else
                    $dbtype="VARCHAR(".$sz.")";
            }
            else{
                switch($maestro->provider){
                case "oracle":
                case "sqlite":
                case "db2odbc":
                    $dbtype="CLOB";
                    break;
                case "mysql":
                    $dbtype="LONGTEXT";
                    break;
                case "access":
                    $dbtype="LONGCHAR";
                    break;
                default:
                    $dbtype="TEXT";
                }
            }
            break;
        case "TEXT":
            switch($maestro->provider){
            case "oracle":
            case "sqlite":
            case "db2odbc":
                $dbtype="CLOB";
                break;
            case "mysql":
                $dbtype="LONGTEXT";
                break;
            case "access":
                $dbtype="LONGCHAR";
                break;
            default:
                $dbtype="TEXT";
            }
            break;
        case "BOOLEAN":
            switch($maestro->provider){
            case "sqlite":
                $dbtype="BOOLEAN";
                break;
            case "oracle":
                $dbtype="NUMBER(1,0)";
                break;
            case "access":
                $dbtype="BYTE";
                break;
            default:
                $dbtype="DECIMAL(1,0)";
            }
            break;
        case "INTEGER":
            switch($maestro->provider){
            case "oracle":
                $dbtype="NUMBER(19,0)";
                break;
            case "access":
                $dbtype="LONG";
                break;
            default:
                $dbtype="DECIMAL(19,0)";
            }
            break;
        case "RATIONAL":
            switch($maestro->provider){
            case "oracle":
                $dbtype="NUMBER(28,7)";
                break;
            case "access":
                $dbtype="CURRENCY";
                break;
            default:
                $dbtype="DECIMAL(28,7)";
            }
            break;
        case "DATE":
            $dbtype="DATE";
            break;
        case "TIMESTAMP":
            switch($maestro->provider){
            case "oracle":
            case "db2odbc":
                $dbtype="TIMESTAMP";
                break;
            default:
                $dbtype="DATETIME";
            }
            break;
        default:
            $dbtype=$tp;
    }
    if($ky){
        $dbtype.=" PRIMARY KEY NOT NULL";
        $uq=0;
    }
    if($uq){
        $dbtype.=" UNIQUE";
        $nn=1;
    }
    if($nn){
        $dbtype.=" NOT NULL";
    }
    return $dbtype;
}
function maestro_abstract($field, $actual, $dbsize, $dbprec, $dbscale, &$abstract, &$size){
    // CARICO I VALORI DI DEFAULT
    $size=0;
    $scale=0;
    if($dbsize>=0)
        $size=$dbsize;
    if($dbprec>=0)
        $size=$dbprec;
    if($dbscale>=0)
        $scale=$dbscale;
        
    if($field=="SYSID"){
        $abstract="SYSID";
        $size=0;
    }
    else{
        if(preg_match("/([^()]+)(\((\d+),?(\d+)?\))?/i",$actual,$m)){
            $actual="VARCHAR";
            switch(count($m)){
                case 5:
                    $scale=intval($m[4]);
                case 4:
                    $size=intval($m[3]);
                default:
                    $actual=$m[1];
            }
            switch(strtoupper($actual)){
            case "CHAR":
                $abstract="CHAR";
                break;

            case "VARCHAR":
            case "VARCHAR2":
            case "NVARCHAR":
                $abstract="VARCHAR";
                break;

            case "TEXT":
            case "NTEXT":
            case "LONGTEXT":
            case "BLOB":
            case "CLOB":
            case "LONGCHAR":
                $abstract="TEXT";
                $size=0;
                break;

            case "BOOLEAN":
            case "BYTE":
                $abstract="BOOLEAN";
                $size=0;
                break;
                
            case "INTEGER":
            case "LONG":
            case "INT":
            case "SMALLINT":
            case "BIGINT":
                $abstract="INTEGER";
                $size=0;
                break;

            case "NUMBER":
            case "DECIMAL":
            case "NUMERIC":
                if($scale>0){
                    $abstract="RATIONAL";
                }
                else{
                    if($size==1)
                        $abstract="BOOLEAN";
                    else
                        $abstract="INTEGER";
                }
                $size=0;
                break;

            case "CURRENCY":
            case "FLOAT":
            case "REAL":
            case "MONEY":
                $abstract="RATIONAL";
                $size=0;
                break;

            case "DATE":
                $abstract="DATE";
                $size=0;
                break;

            case "DATETIME":
            case "TIMESTAMP":
                $abstract="TIMESTAMP";
                $size=0;
                break;

            default:
                $abstract="VARCHAR";
                $size=50;
            }
        }
        else{
            $abstract="VARCHAR";
            $size=50;
        }
    }
}
function maestro_istable($maestro, $tabname){
    global $freezelog;
    $ret=false;
    $freezelog=true;
    
    if($maestro->provider=="sqlite"){
        $sql="SELECT * FROM sqlite_master WHERE name='$tabname'";
        $r=x_sqlite_array_query($maestro->conn, $sql, SQLITE3_ASSOC);
        if(count($r)>0){
            $ret=true;
        }
    }
    else{
        $sql="SELECT * FROM ".$tabname." WHERE 0=1";
        if(maestro_query($maestro, $sql, $r, false)){
            $ret=true;
        }
    }
    
    $freezelog=false;
    unset($r);
    return $ret;
}
function maestro_escapize(&$sql){
    $sql=htmlentities(utf8Decode($sql));
}
?>