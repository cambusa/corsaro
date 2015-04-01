<?php 
/****************************************************************************
* Name:            qv_fatture_insert.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
function qv_fatture_insert($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
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

        // DESCRIPTION
        if(isset($data["DESCRIPTION"]))
            $DESCRIPTION=$data["DESCRIPTION"];
        else
            $DESCRIPTION="(nuovo flusso)";
        
        // TYPOLOGYID
        $TYPOLOGYID=qv_actualid($maestro, "0FLUSSI00000");
        
        // AMOUNT
        $AMOUNT="0";
            
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
            $data["MOTIVEID"]=qv_actualid($maestro, "0FLUSSSERVIZ");
        }
        $motive=qv_solverecord($maestro, $data, "QW_MOTIVIFLUSSO", "MOTIVEID", "", $MOTIVEID, "DIRECTION,ALIQUOTA");
        if($MOTIVEID!=""){
            $DIRECTION=$motive["DIRECTION"];
            $ALIQUOTA=floatval($motive["ALIQUOTA"]);
        }
        else{
            $babelcode="QVERR_MOTIVEID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il motivo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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

        // INSERISCO UN FLUSSO
        $datax=array();
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["TYPOLOGYID"]=$TYPOLOGYID;
        $datax["GENREID"]=$GENREID;
        $datax["MOTIVEID"]=$MOTIVEID;
        $datax["BOWID"]=$BOWID;
        $datax["BOWTIME"]=$BOWTIME;
        $datax["TARGETTIME"]=$TARGETTIME;
        $datax["TARGETID"]=$TARGETID;
        $datax["AUXTIME"]=$AUXTIME;
        $datax["STATOID"]=$STATOID;
        $datax["ALIQUOTA"]=$ALIQUOTA;
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $FLUSSOID=$jret["SYSID"];
        
        // AGGANCIO IL TRASFERIMENTO ALLA PRATICA
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$FLUSSOID;
        $jret=qv_quivers_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // VARIABILI DI RITORNO
        $babelparams["FLUSSOID"]=$FLUSSOID;
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