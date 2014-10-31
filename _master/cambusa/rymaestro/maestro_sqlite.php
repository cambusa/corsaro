<?php
/****************************************************************************
* Name:            maestro_sqlite.php                                       *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.00                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($sqlite3_enabled))
    $sqlite3_enabled=false;
function x_sqlite_open($strconn, $errdescr=""){
    global $sqlite3_enabled;

    $conn=false;
    if($sqlite3_enabled){
        try{
            $conn=new SQLite3($strconn); 
            $conn->busyTimeout(10000);
        }
        catch(Exception $e){
            $errdescr=$e->getMessage();
            throw new Exception( $errdescr );
        }
    }
    else{
        $conn=sqlite_open($strconn, 0666, $errdescr);
    }
    return $conn;
}
function x_sqlite_close($conn){
    global $sqlite3_enabled;

    if($sqlite3_enabled)
        return $conn->close();
    else
        return sqlite_close($conn);
}
function x_sqlite_query($conn, $sql){
    global $sqlite3_enabled;

    if($sqlite3_enabled){
        $res=$conn->query($sql);
        if(is_object($res))
            return $res;
        else
            return false;
    }
    else{
        return sqlite_query($conn, $sql, SQLITE_ASSOC);
    }
}
function x_sqlite_unbuffered_query($conn, $sql){
    global $sqlite3_enabled;

    if($sqlite3_enabled){
        $res=$conn->query($sql);
        if(is_object($res))
            return $res;
        else
            return false;
    }
    else{
        return sqlite_unbuffered_query($conn, $sql, SQLITE_ASSOC);
    }
}
function x_sqlite_fetch_array($res){
    global $sqlite3_enabled;

    if($sqlite3_enabled){
        if(is_object($res))
            return $res->fetchArray(SQLITE3_ASSOC);
        else
            return false;
    }
    else{
        return sqlite_fetch_array($res, SQLITE_ASSOC);
    }
}
function x_sqlite_array_query($conn, $sql, $type=SQLITE3_BOTH){
    global $sqlite3_enabled;

    if($sqlite3_enabled){
        $res=$conn->query($sql);
        if(is_object($res)){
            $r=array();
            while($row=$res->fetchArray($type)){
                $r[]=$row;
            }
            $res->finalize();
            return $r;
        }
        else{
            return array();
        }
    }
    else{
        switch($type){
        case SQLITE3_BOTH:$type=SQLITE_BOTH;break;
        case SQLITE3_ASSOC:$type=SQLITE_ASSOC;break;
        case SQLITE3_NUM:$type=SQLITE_NUM;break;
        }
        return sqlite_array_query($conn, $sql, $type);
    }
}
function x_sqlite_last_error($conn){
    global $sqlite3_enabled;

    if($sqlite3_enabled)
        return $conn->lastErrorCode();
    else
        return sqlite_last_error($conn);
}
function x_sqlite_error_string($conn, $coderr){
    global $sqlite3_enabled;

    if($sqlite3_enabled)
        return $conn->lastErrorMsg();
    else
        return sqlite_error_string($coderr);
}
function x_sqlite_iserror($coderr){
    global $sqlite3_enabled;

    if($sqlite3_enabled)
        return ($coderr!=0 && $coderr!=1);
    else
        return ($coderr!=0);
}
function x_sqlite_exec($conn, $sql){
    global $sqlite3_enabled;

    if($sqlite3_enabled)
        $ret=$conn->exec($sql);
    else
        $ret=sqlite_exec($conn, $sql);
    usleep(100);
    return $ret;
}
function x_sqlite_changes($conn){
    global $sqlite3_enabled;

    if($sqlite3_enabled)
        return $conn->changes();
    else
        return sqlite_changes($conn);
}
function x_sqlite_finalize(&$res){
    global $sqlite3_enabled;

    if(is_object($res)){
        if($sqlite3_enabled){
            $res->finalize();
            unset($res);
        }
        else{
            unset($res);
        }
    }
    usleep(100);
}
?>