<?php 
/****************************************************************************
* Name:            qv_ordini_update.php                                     *
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
function qv_ordini_update($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $WARNING="";

        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "STATOID,MAGAZZINOID");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $STATOID=$pratica["STATOID"];
        $REG_MAGAZZINOID=$pratica["MAGAZZINOID"];

        if(!isset($data["MAGAZZINOID"])){
            $data["MAGAZZINOID"]=$REG_MAGAZZINOID;
        }
        
        // DETERMINAZIONE TRASFID
        $record=qv_solverecord($maestro, $data, "QW_TRASFERIMENTI", "TRASFID", "", $TRASFID, "*");
        if($TRASFID==""){
            $babelcode="QVERR_TRASFID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_DESCRIPTION=$record["DESCRIPTION"];
        $REG_BOWID=$record["BOWID"];
        $REG_TARGETID=$record["TARGETID"];
        $REG_BOWTIME=qv_strtime($record["BOWTIME"]);
        $REG_TARGETTIME=qv_strtime($record["TARGETTIME"]);
        $REG_AUXTIME=qv_strtime($record["AUXTIME"]);
        $REG_AMOUNT=$record["AMOUNT"];
        $REG_GENREID=$record["GENREID"];
        $REG_SERVIZIOID=$record["SERVIZIOID"];
        $REG_MOTIVEID=$record["MOTIVEID"];

        if(isset($data["DESCRIPTION"]))
            $DESCRIPTION=$data["DESCRIPTION"];
        else
            $DESCRIPTION=$REG_DESCRIPTION;
            
        if(isset($data["BOWID"]))
            $BOWID=$data["BOWID"];
        else
            $BOWID=$REG_BOWID;

        if(isset($data["TARGETID"]))
            $TARGETID=$data["TARGETID"];
        else
            $TARGETID=$REG_TARGETID;

        if(isset($data["BOWTIME"]))
            $BOWTIME=$data["BOWTIME"];
        else
            $BOWTIME=$REG_BOWTIME;
            
        if(isset($data["TARGETTIME"]))
            $TARGETTIME=$data["TARGETTIME"];
        else
            $TARGETTIME=$REG_TARGETTIME;
            
        if(isset($data["AUXTIME"]))
            $AUXTIME=$data["AUXTIME"];
        else
            $AUXTIME=$REG_AUXTIME;
            
        if(isset($data["GENREID"]))
            $GENREID=$data["GENREID"];
        else
            $GENREID=$REG_GENREID;

        if(isset($data["SERVIZIOID"]))
            $SERVIZIOID=$data["SERVIZIOID"];
        else
            $SERVIZIOID=$REG_SERVIZIOID;

        if(isset($data["AMOUNT"]))
            $AMOUNT=$data["AMOUNT"];
        else
            $AMOUNT=$REG_AMOUNT;

        if(isset($data["MOTIVEID"]))
            $MOTIVEID=$data["MOTIVEID"];
        else
            $MOTIVEID=$REG_MOTIVEID;
            
        if(isset($data["MAGAZZINOID"])){
            $MAGAZZINOID=$data["MAGAZZINOID"];
            // CERCO LA COLLOCAZIONE NEL MAGAZZINO
            $sql="SELECT SYSID FROM QW_COLLOCAZIONI WHERE REFGENREID='$GENREID' AND MAGAZZINOID='$MAGAZZINOID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $BOWID=$r[0]["SYSID"];
            }
        }
        else{
            $MAGAZZINOID="";
        }

        // AGGIORNO LA FRECCIA DI RIFERIMENTO
        $datax=array();
        $datax["SYSID"]=$TRASFID;
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["BOWID"]=$BOWID;
        $datax["TARGETID"]=$TARGETID;
        $datax["BOWTIME"]=$BOWTIME;
        $datax["TARGETTIME"]=$TARGETTIME;
        $datax["AMOUNT"]=$AMOUNT;
        $datax["GENREID"]=$GENREID;
        $datax["SERVIZIOID"]=$SERVIZIOID;
        $datax["MOTIVEID"]=$MOTIVEID;
        $jret=qv_arrows_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        $TRASFTYPE=qv_actualid($maestro, "0TRASFERIMEN");
        $MOVTYPE=qv_actualid($maestro, "0FLUSSI00000");
        
        // DETERMINAZIONE PREZZO
        $PREZZO=0;
        $sql="SELECT EQAMOUNT FROM QW_LISTINIJOIN WHERE REFGENREID='$GENREID' AND REFAMOUNT<=$AMOUNT AND EQTYPOLOGYID='$MOVTYPE' AND AUXTIME<=[:DATE($BOWTIME)] ORDER BY AUXTIME,REFAMOUNT";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $PREZZO=floatval($r[0]["EQAMOUNT"])*floatval($AMOUNT);
        }
        
        // GESTIONE DEL FLUSSO
        $TYPOLOGYID=qv_actualid($maestro, "0FLUSSI00000");
        $GENREID=qv_actualid($maestro, "0MONEYEURO00");
        $MOTIVEID=qv_actualid($maestro, "0FLUSSMERCE0");
        
        $ALIQUOTA=22;
        $sql="SELECT ALIQUOTA FROM QW_MOTIVIFLUSSO WHERE SYSID='$MOTIVEID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $ALIQUOTA=floatval($r[0]["ALIQUOTA"]);
        }    
        
        $sql="SELECT * FROM QW_FLUSSI WHERE REFARROWID='$TRASFID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)==0){
            // INSERISCO IL FLUSSO MANCANTE
            $datax=array();
            $datax["DESCRIPTION"]=$DESCRIPTION;
            $datax["TYPOLOGYID"]=$TYPOLOGYID;
            $datax["AMOUNT"]=$PREZZO;
            $datax["GENREID"]=$GENREID;
            $datax["MOTIVEID"]=$MOTIVEID;
            $datax["BOWTIME"]=$BOWTIME;
            $datax["TARGETTIME"]=$TARGETTIME;
            $datax["AUXTIME"]=$AUXTIME;
            $datax["REFARROWID"]=$TRASFID;
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
        }
        else{
            $FLUSSOID=$r[0]["SYSID"];

            // AGGIORNO I DATI DEL FLUSSO
            $datax=array();
            $datax["SYSID"]=$FLUSSOID;
            $datax["DESCRIPTION"]=$DESCRIPTION;
            $datax["AMOUNT"]=$PREZZO;
            $datax["GENREID"]=$GENREID;
            $datax["MOTIVEID"]=$MOTIVEID;
            $datax["BOWTIME"]=$BOWTIME;
            $datax["TARGETTIME"]=$TARGETTIME;
            $datax["AUXTIME"]=$AUXTIME;
            $datax["ALIQUOTA"]=$ALIQUOTA;
            $jret=qv_arrows_update($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }

        // AGGIORNO LA PRATICA
        $datax=array();
        $datax["SYSID"]=$PRATICAID;
        $datax["MAGAZZINOID"]=$MAGAZZINOID;
        $jret=qv_quivers_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // VARIABILI DI RITORNO
        $babelparams["WARNING"]=$WARNING;
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