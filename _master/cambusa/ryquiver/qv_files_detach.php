<?php 
/****************************************************************************
* Name:            qv_files_detach.php                                      *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
function qv_files_detach($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        $FILEID="";

        // INDIVIDUAZIONE RECORD
        qv_solverecord($maestro, $data, "QVTABLEFILE", "SYSID", "", $SYSID);
        if($SYSID!=""){
            // DETERMINO FILEID
            maestro_query($maestro,"SELECT FILEID FROM QVTABLEFILE WHERE SYSID='$SYSID'",$r);
            if(count($r)==1){
                $FILEID=$r[0]["FILEID"];
            }
        }
        else{
            // DETERMINO TABLENAME, RECORDID, FILEID
            qv_tripletattach($maestro, $data, $TABLENAME, $RECORDID, $FILEID);

            // DETERMINO SYSID
            maestro_query($maestro,"SELECT SYSID FROM QVTABLEFILE WHERE [:UPPER(TABLENAME)]='".strtoupper($TABLENAME)."' AND RECORDID='$RECORDID' AND FILEID='$FILEID'",$r);
            if(count($r)==1){
                $SYSID=$r[0]["SYSID"];
            }
            else{
                $babelcode="QVERR_NOATTACH";
                $b_params=array("TABLENAME" => $TABLENAME, "RECORDID" => $RECORDID, "FILEID" => $FILEID);
                $b_pattern="Allegato non trovato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
        // CANCELLO IL LEGAME DALLA TABELLA CROSS
        $sql="DELETE FROM QVTABLEFILE WHERE SYSID='$SYSID'";
        if(maestro_execute($maestro, $sql, false)){
            $babelparams["FILEID"]=$FILEID;
        }
        else{
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
    }
    // USCITA JSON
    $j=array();
    $j["success"]=$success;
    $j["code"]=$babelcode;
    $j["params"]=$babelparams;
    $j["message"]=$message;
    $j["SYSID"]=$SYSID;
    return $j; //ritorno standard
}
?>