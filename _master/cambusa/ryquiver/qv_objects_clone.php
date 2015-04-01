<?php 
/****************************************************************************
* Name:            qv_objects_clone.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivercln.php";
include_once "qv_objects_insert.php";
function qv_objects_clone($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // INDIVIDUAZIONE RECORD
        $record=qv_solverecord($maestro, $data, "QVOBJECTS", "SYSID", "NAME", $SYSID, "*");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // INIZIALIZZO IL VETTORE IN INGRESSO PER IL NUOVO INSERIMENTO
        $datains=array();
        
        // CAMPI STANDARD
        $datains["DESCRIPTION"]=$record["DESCRIPTION"];
        $datains["REGISTRY"]=$record["REGISTRY"];
        $datains["TYPOLOGYID"]=$record["TYPOLOGYID"];
        $datains["REFGENREID"]=$record["REFGENREID"];
        $datains["REFOBJECTID"]=$record["REFOBJECTID"];
        $datains["REFQUIVERID"]=$record["REFQUIVERID"];
        $datains["BEGINTIME"]=qv_strtime($record["BEGINTIME"]);
        $datains["ENDTIME"]=qv_strtime($record["ENDTIME"]);
        $datains["REFERENCE"]=$record["REFERENCE"];
        $datains["AUXTIME"]=qv_strtime($record["AUXTIME"]);
        $datains["AUXAMOUNT"]=$record["AUXAMOUNT"];
        $datains["MAXAMOUNT"]=$record["MAXAMOUNT"];
        $datains["BUFFERID"]=$record["BUFFERID"];
        $datains["TAG"]=$record["TAG"];
        $datains["CONSISTENCY"]=$record["CONSISTENCY"];
        $datains["SCOPE"]=$record["SCOPE"];
        $datains["UPDATING"]=$record["UPDATING"];
        $datains["DELETING"]=$record["DELETING"];
        
        // CAMPI ESTESI
        qv_cloning($maestro, "QVOBJECT", $record["TYPOLOGYID"], $SYSID, $datains);
        
        // INSERIMENTO CLONE
        $ret=qv_objects_insert($maestro, $datains);
        if($ret["success"]==1){
            // ALLEGATI
            qv_cloneattachments($maestro, $SYSID, $ret["SYSID"]);
        }
        $success=$ret["success"];
        $babelcode=$ret["code"];
        $babelparams=$ret["params"];
        $message=$ret["message"];
        $SYSID=$ret["SYSID"];
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