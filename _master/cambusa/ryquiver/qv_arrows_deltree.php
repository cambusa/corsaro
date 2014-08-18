<?php 
/****************************************************************************
* Name:            qv_arrows_deltree.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "qv_arrows_delete.php";
function qv_arrows_deltree($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        qv_solverecord($maestro, $data, "QVARROWS", "SYSID", "NAME", $SYSID);
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $r=qv_arrowdelrecursive($maestro, $SYSID);
        $success=$r["success"];
        $babelcode=$r["code"];
        $babelparams=$r["params"];
        $message=$r["message"];
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
function qv_arrowdelrecursive($maestro, $SYSID){
    $s=true;
    maestro_query($maestro,"SELECT SYSID FROM QVARROWS WHERE REFARROWID='$SYSID'",$r);
    for($i=0; $i<count($r); $i++){
        $j=qv_arrowdelrecursive($maestro, $r[$i]["SYSID"]);
        if($j["success"]==0){
            $s=false;
            break;
        }
    }
    if($s){
        $subdata=array();
        $subdata["SYSID"]=$SYSID;
        $j=qv_arrows_delete($maestro, $subdata);
    }
    return $j;
}
?>