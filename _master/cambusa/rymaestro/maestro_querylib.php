<?php 
/****************************************************************************
* Name:            maestro_querylib.php                                     *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.69                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function maestro_unbuffered($maestro, $sql, $raise=true){
    try{
        $maestro->errdescr="";
        // SOSTITUZIONE DELLE MACRO
        $sql=maestro_macro($maestro, $sql);
        switch($maestro->provider){
        case "sqlite":
            $res=false;
            $res=@x_sqlite_unbuffered_query($maestro->conn, $sql);
            if($res===true){
                $res=false;
            }
            elseif($res==false){
                $coderr=x_sqlite_last_error($maestro->conn);
                if($coderr!=0){
                    $maestro->errdescr=x_sqlite_error_string($maestro->conn, $coderr);
                    log_write($sql.";\r\n--->" . $maestro->errdescr);
                }
            }
            break;
        case "mysql":
            $res=@mysqli_query($maestro->conn, $sql, MYSQLI_USE_RESULT);
            if($res===false){
                $maestro->errdescr=mysqli_error($maestro->conn);
                log_write($sql.";\r\n--->" . $maestro->errdescr);
            }
            break;
        case "oracle":
            if($maestro->transon)
                $mode=OCI_NO_AUTO_COMMIT;
            else
                $mode=OCI_COMMIT_ON_SUCCESS;
            $res=oci_parse($maestro->conn,$sql);
            $ret=@oci_execute($res, $mode);
            if($ret===false){
                $me=oci_error($res);
                oci_free_statement($res);
                $res=false;
                $maestro->errdescr=$me["message"];
                log_write($sql.";\r\n--->" . $maestro->errdescr);
            }
            break;
        default:
            $res=@odbc_exec($maestro->conn, $sql);
            if($res!==false){
                odbc_longreadlen($res, 100000000);
            }
            else{
                $maestro->errdescr=odbc_errormsg($maestro->conn);
                log_write($sql.";\r\n--->" . $maestro->errdescr);
            }
        }
    }
    catch(Exception $e){
        $res=false;
        $maestro->errdescr=$e->getMessage();
        log_write($maestro->errdescr);
    }
    if(!$res && $raise)
        throw new Exception( $maestro->errdescr );
    return $res;
}
function maestro_fetch($maestro, &$res){
    try{
        $row=false;
        if($res){
            switch($maestro->provider){
            case "sqlite":
                $row=x_sqlite_fetch_array($res);
                break;
            case "mysql":
                if($row=mysqli_fetch_assoc($res)){
                    // RISOLVO I NULL
                    foreach($row as $k => $v){
                        if($v===null)
                            $row[$k]="";
                    }
                }
                break;
            case "oracle":
                if($row=oci_fetch_array($res, OCI_ASSOC+OCI_RETURN_NULLS)){
                    // RISOLVO I CLOB E I NULL
                    foreach($row as $k => $v){
                        if(is_object($v))
                            $row[$k]=$v->load();
                        elseif($v===null)
                            $row[$k]="";
                    }
                }
                break;
            case "db2odbc":
                if($row=odbc_fetch_array($res)){
                    // SOSTITUISCO LA VIRGOLA DEI NUMERI E RISOLVO I NULL
                    foreach($row as $k => $v){
                        if($v===null)
                            $row[$k]="";
                        elseif(preg_match("/^\d*,\d+$/", $v))
                            $row[$k]=str_replace(",", ".", $v);
                    }
                }
                break;
            default:
                if($row=odbc_fetch_array($res)){
                    // RISOLVO I NULL
                    foreach($row as $k => $v){
                        if($v===null)
                            $row[$k]="";
                    }
                }
            }
            if(!is_array($row)){    
                $row=false;
                maestro_free($maestro, $res);
            }
        }
    }
    catch(Exception $e){}
    return $row;
}

function maestro_free($maestro, &$res){
    try{
        if($res){
            switch($maestro->provider){
            case "sqlite":
                x_sqlite_finalize($res);
                break;
            case "mysql":
                mysqli_free_result($res);
                break;
            case "oracle":
                oci_free_statement($res);
                break;
            default:
                odbc_free_result($res);
            }
        }
        $res=false;
    }
    catch(Exception $e){}
}
?>