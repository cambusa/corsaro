<?php 
/****************************************************************************
* Name:            qv_ordini_delete.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_delete.php";
include_once $path_cambusa."ryquiver/qv_quivers_remove.php";
function qv_ordini_delete($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID);
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINAZIONE TRASFID
        qv_solverecord($maestro, $data, "QVARROWS", "TRASFID", "", $TRASFID);
        if($TRASFID==""){
            $babelcode="QVERR_TRASFID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // CERCO L'EVENTUALE FLUSSO COLLEGATO
        $REFARROWID="";
        $sql="SELECT SYSID FROM QW_FLUSSI WHERE REFARROWID='$TRASFID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $REFARROWID=$r[0]["SYSID"];
        }
        
        if($REFARROWID!=""){
            // RIMUOVO IL FLUSSO DAL QUIVER
            $datax=array();
            $datax["QUIVERID"]=$PRATICAID;
            $datax["ARROWID"]=$REFARROWID;
            $jret=qv_quivers_remove($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            
            // CANCELLO IL FLUSSO
            $datax=array();
            $datax["SYSID"]=$REFARROWID;
            $jret=qv_arrows_delete($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }

        // RIMUOVO IL TRASFERIMENTO DAL QUIVER
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$TRASFID;
        $jret=qv_quivers_remove($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // CANCELLO IL TRASFERIMENTO
        $datax=array();
        $datax["SYSID"]=$TRASFID;
        $jret=qv_arrows_delete($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
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