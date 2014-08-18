<?php 
/****************************************************************************
* Name:            qv_quivers_deepdelete.php                                *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverinf.php";
include_once "quiverdel.php";
include_once "quiverval.php";
include_once "quiverext.php";
include_once "qv_quivers_delete.php";
include_once "qv_arrows_delete.php";
function qv_quivers_deepdelete($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        $record=qv_solverecord($maestro, $data, "QVQUIVERS", "SYSID", "NAME", $SYSID, "TYPOLOGYID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TYPOLOGYID=$record["TYPOLOGYID"];
        
        // DETERMINO LE FRECCE INCLUSE
        $arrows=array();
        maestro_query($maestro,"SELECT QVQUIVERARROW.ARROWID AS ARROWID FROM QVQUIVERARROW INNER JOIN QVARROWS ON QVARROWS.SYSID=QVQUIVERARROW.ARROWID WHERE QVQUIVERARROW.QUIVERID='$SYSID' ORDER BY QVARROWS.REFARROWID DESC",$r);
        for($i=0; $i<count($r); $i++){
            $arrows[$i]=$r[$i]["ARROWID"];
        }
        // CANCELLO IL QUIVER
        $subdata=array();
        $subdata["SYSID"]=$SYSID;
        $d=qv_quivers_delete($maestro, $subdata);
        if(!$d["success"]){
            return $d;
        }
        // CANCELLO LE FRECCE INCLUSE
        for($i=0; $i<count($arrows); $i++){
            $subdata=array();
            $subdata["SYSID"]=$arrows[$i];
            $d=qv_arrows_delete($maestro, $subdata);
            if(!$d["success"]){
                return $d;
            }
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