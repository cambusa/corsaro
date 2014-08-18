<?php 
/****************************************************************************
* Name:            qv_motives_clone.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivercln.php";
include_once "qv_motives_insert.php";
function qv_motives_clone($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        $record=qv_solverecord($maestro, $data, "QVMOTIVES", "SYSID", "NAME", $SYSID, "*");
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
        $datains["DIRECTION"]=$record["DIRECTION"];
        $datains["REFERENCEID"]=$record["REFERENCEID"];
        $datains["COUNTERPARTID"]=$record["COUNTERPARTID"];
        $datains["CONSISTENCY"]=$record["CONSISTENCY"];
        $datains["SCOPE"]=$record["SCOPE"];
        $datains["UPDATING"]=$record["UPDATING"];
        $datains["DELETING"]=$record["DELETING"];
        $datains["STATUS"]=$record["STATUS"];
        $datains["DISCHARGE"]=$record["DISCHARGE"];
        $datains["TAG"]=$record["TAG"];
        
        // CAMPI ESTESI
        qv_cloning($maestro, "QVMOTIVE", $record["TYPOLOGYID"], $SYSID, $datains);
        
        // INSERIMENTO CLONE
        $ret=qv_motives_insert($maestro, $datains);
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