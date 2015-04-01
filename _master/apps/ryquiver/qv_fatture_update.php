<?php 
/****************************************************************************
* Name:            qv_fatture_update.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."ryquiver/qv_quivers_update.php";
include_once $path_applications."ryquiver/fatture_saldo.php";
function qv_fatture_update($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $WARNING="";

        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "*");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $STATOID=$pratica["STATOID"];
        $DATAINIZIO=qv_strtime($pratica["DATAINIZIO"]);
        $DATAFINE=qv_strtime($pratica["DATAFINE"]);
        $AUXTIME=qv_strtime($pratica["AUXTIME"]);
        $REG_CONTOID=$pratica["CONTOID"];
        $REFARROWID=$pratica["REFARROWID"];
        $PARAMETRI=json_decode($pratica["MOREDATA"], true);
        
        if(!isset($data["CONTOID"])){
            $data["CONTOID"]=$REG_CONTOID;
        }

        if(!isset($data["CONTROID"])){
            if(isset($PARAMETRI["_CONTROID"])){
                $data["CONTROID"]=$PARAMETRI["_CONTROID"];
            }
        }

        if(!isset($data["GENREID"])){
            if(isset($PARAMETRI["_GENREID"])){
                $data["GENREID"]=$PARAMETRI["_GENREID"];
            }
        }

        // DETERMINAZIONE FLUSSOID
        $record=qv_solverecord($maestro, $data, "QVARROWS", "FLUSSOID", "", $FLUSSOID, "*");
        if($FLUSSOID==""){
            $babelcode="QVERR_FLUSSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il flusso";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_DESCRIPTION=$record["DESCRIPTION"];
        $REG_BOWID=$record["BOWID"];
        $REG_TARGETID=$record["TARGETID"];
        $REG_AMOUNT=$record["AMOUNT"];
        $REG_GENREID=$record["GENREID"];
        $REG_MOTIVEID=$record["MOTIVEID"];

        if(isset($data["DESCRIPTION"]))
            $DESCRIPTION=$data["DESCRIPTION"];
        else
            $DESCRIPTION=$REG_DESCRIPTION;
            
        if(isset($data["AMOUNT"]))
            $AMOUNT=$data["AMOUNT"];
        else
            $AMOUNT=$REG_AMOUNT;

        // DETERMINO CONTOID
        $conto=qv_solverecord($maestro, $data, "QW_CONTI", "CONTOID", "", $CONTOID, "REFGENREID");
        if($CONTOID==""){
            $babelcode="QVERR_CONTOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il conto";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REFGENREID=$conto["REFGENREID"];
        
        // CONTROID
        if(isset($data["CONTROID"]))
            $CONTROID=$data["CONTROID"];
        else
            $CONTROID="";
        
        // GENREID
        if(isset($data["GENREID"]))
            $GENREID=$data["GENREID"];
        elseif($REFGENREID!="")
            $GENREID=$REFGENREID;
        else
            $GENREID=qv_actualid($maestro, "0MONEYEURO00");
        
        // MOTIVEID
        if(!isset($data["MOTIVEID"])){
            $data["MOTIVEID"]=$REG_MOTIVEID;
        }
        $motive=qv_solverecord($maestro, $data, "QW_MOTIVIFLUSSO", "MOTIVEID", "", $MOTIVEID, "DIRECTION,ALIQUOTA");
        if($MOTIVEID!=""){
            $DIRECTION=intval($motive["DIRECTION"]);
            $ALIQUOTA=floatval($motive["ALIQUOTA"]);
        }
        else{
            $DIRECTION=0;
            $ALIQUOTA=0;
        }
        
        if($DIRECTION==0){
            $BOWID=$CONTOID;
            $BOWTIME=$DATAFINE;
            $TARGETID=$CONTROID;
            $TARGETTIME=$DATAINIZIO;
        }
        else{
            $BOWID=$CONTROID;
            $BOWTIME=$DATAINIZIO;
            $TARGETID=$CONTOID;
            $TARGETTIME=$DATAFINE;
        }
        
        // ALIQUOTA
        if(isset($data["ALIQUOTA"])){
            $ALIQUOTA=floatval($data["ALIQUOTA"]);
        }

        // AGGIORNO IL FLUSSO
        $datax=array();
        $datax["SYSID"]=$FLUSSOID;
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["BOWID"]=$BOWID;
        $datax["BOWTIME"]=$BOWTIME;
        $datax["TARGETID"]=$TARGETID;
        $datax["TARGETTIME"]=$TARGETTIME;
        $datax["AMOUNT"]=$AMOUNT;
        $datax["GENREID"]=$GENREID;
        $datax["MOTIVEID"]=$MOTIVEID;
        $datax["ALIQUOTA"]=$ALIQUOTA;
        $jret=qv_arrows_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // AGGIORNO IL TOTALE FATTURA
        $PARAMETRI["_CONTROID"]=$CONTROID;
        $PARAMETRI["_GENREID"]=$GENREID;
        $MOREDATA=json_encode($PARAMETRI);
        fatture_saldo($maestro, $PRATICAID, $CONTOID, $GENREID, $REFARROWID, $MOREDATA, "", "", "", "", $TOTAL);
        
        // VARIABILI DI RITORNO
        $babelparams["TOTAL"]=$TOTAL;
        $babelparams["CONTROID"]=$CONTROID;
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