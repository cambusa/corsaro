<?php 
/****************************************************************************
* Name:            qv_ordini_insert.php                                     *
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
function qv_ordini_insert($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "STATOID");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $STATOID=$pratica["STATOID"];

        // DESCRIPTION
        if(isset($data["DESCRIPTION"]))
            $DESCRIPTION=$data["DESCRIPTION"];
        else
            $DESCRIPTION="(nuovo trasferimento)";
        
        // TYPOLOGYID
        $TYPOLOGYID=qv_actualid($maestro, "0TRASFERIMEN");
        
        // AMOUNT
        if(isset($data["AMOUNT"]))
            $AMOUNT=$data["AMOUNT"];
        else
            $AMOUNT="0";
            
        // GENREID
        if(isset($data["GENREID"]))
            $GENREID=$data["GENREID"];
        else
            $GENREID=qv_actualid($maestro, "0STUFFJOLLY0");
        
        // MOTIVEID
        if(isset($data["MOTIVEID"]))
            $MOTIVEID=$data["MOTIVEID"];
        else
            $MOTIVEID=qv_actualid($maestro, "0MOTTRASFVEN");
            
        // BOWTIME
        if(isset($data["BOWTIME"]))
            $BOWTIME=$data["BOWTIME"];
        else
            $BOWTIME=LOWEST_DATE;
        
        // TARGETTIME
        if(isset($data["TARGETTIME"]))
            $TARGETTIME=$data["TARGETTIME"];
        else
            $TARGETTIME=LOWEST_DATE;
            
        // AUXTIME
        $AUXTIME=date("Ymd");
        
        // INSERISCO UN TRASFERIMENTO
        $datax=array();
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["TYPOLOGYID"]=$TYPOLOGYID;
        $datax["AMOUNT"]=$AMOUNT;
        $datax["GENREID"]=$GENREID;
        $datax["MOTIVEID"]=$MOTIVEID;
        $datax["BOWTIME"]=$BOWTIME;
        $datax["TARGETTIME"]=$TARGETTIME;
        $datax["AUXTIME"]=$AUXTIME;
        $datax["STATOID"]=$STATOID;
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $TRASFID=$jret["SYSID"];
        
        // AGGANCIO IL TRASFERIMENTO ALLA PRATICA
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$TRASFID;
        $jret=qv_quivers_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        $TYPOLOGYID=qv_actualid($maestro, "0FLUSSI00000");
        $GENREID=qv_actualid($maestro, "0MONEYEURO00");
        $MOTIVEID=qv_actualid($maestro, "0FLUSSMERCE0");
            
        // INSERISCO UN FLUSSO
        $datax=array();
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["TYPOLOGYID"]=$TYPOLOGYID;
        $datax["GENREID"]=$GENREID;
        $datax["MOTIVEID"]=$MOTIVEID;
        $datax["BOWTIME"]=$BOWTIME;
        $datax["TARGETTIME"]=$TARGETTIME;
        $datax["AUXTIME"]=$AUXTIME;
        $datax["REFARROWID"]=$TRASFID;
        $datax["STATOID"]=$STATOID;
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
        $babelparams["TRASFID"]=$TRASFID;
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