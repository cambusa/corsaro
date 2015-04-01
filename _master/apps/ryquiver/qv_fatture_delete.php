<?php 
/****************************************************************************
* Name:            qv_fatture_delete.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_arrows_delete.php";
include_once $path_cambusa."ryquiver/qv_quivers_remove.php";
include_once $path_cambusa."ryquiver/qv_quivers_update.php";
include_once $path_applications."ryquiver/fatture_saldo.php";
function qv_fatture_delete($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "REFARROWID,CONTOID");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_CONTOID=$pratica["CONTOID"];
        $REFARROWID=$pratica["REFARROWID"];
        
        if(!isset($data["CONTOID"])){
            $data["CONTOID"]=$REG_CONTOID;
        }

        // DETERMINAZIONE FLUSSOID
        qv_solverecord($maestro, $data, "QVARROWS", "FLUSSOID", "", $FLUSSOID);
        if($FLUSSOID==""){
            $babelcode="QVERR_FLUSSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il flusso";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO CONTOID
        $conto=qv_solverecord($maestro, $data, "QW_CONTI", "CONTOID", "", $CONTOID, "REFGENREID");
        if($CONTOID==""){
            $babelcode="QVERR_CONTOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il conto";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REFGENREID=$conto["REFGENREID"];
        
        // RIMUOVO IL FLUSSO DAL QUIVER
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$FLUSSOID;
        $jret=qv_quivers_remove($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
            
        // CANCELLO IL FLUSSO
        $datax=array();
        $datax["SYSID"]=$FLUSSOID;
        $jret=qv_arrows_delete($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // AGGIORNO IL TOTALE FATTURA
        fatture_saldo($maestro, $PRATICAID, $CONTOID, $REFGENREID, $REFARROWID, false, "", "", "", "", $TOTAL);
        
        // VARIABILI DI RITORNO
        $babelparams["TOTAL"]=$TOTAL;
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