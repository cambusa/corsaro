<?php 
/****************************************************************************
* Name:            qv_files_attach.php                                      *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
function qv_files_attach($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);
        
        // DETERMINO TABLENAME, RECORDID, FILEID
        qv_tripletattach($maestro, $data, $TABLENAME, $RECORDID, $FILEID);
        
        // CONTROLLO CHE LA TERNA TABLENAME, RECORDID, FILEID NON SIA GIA' PRESENTE
        maestro_query($maestro,"SELECT SYSID FROM QVTABLEFILE WHERE [:UPPER(TABLENAME)]='".strtoupper($TABLENAME)."' AND RECORDID='$RECORDID' AND FILEID='$FILEID'",$r);
        if(count($r)>0){
            $babelcode="QVERR_ALREADYATT";
            $b_params=array("TABLENAME" => $TABLENAME, "RECORDID" => $RECORDID, "FILEID" => $FILEID);
            $b_pattern="Impossibile allegare due volte lo stesso file";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,TABLENAME,RECORDID,FILEID,SORTER";
        $values="'$SYSID','$TABLENAME','$RECORDID','$FILEID',0";
        $sql="INSERT INTO QVTABLEFILE($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false)){
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