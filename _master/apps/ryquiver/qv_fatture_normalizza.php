<?php 
/****************************************************************************
* Name:            qv_fatture_normalizza.php                                *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_quivers_update.php";
include_once $path_applications."ryquiver/qv_attivita_update.php";
include_once $path_applications."ryquiver/fatture_saldo.php";
function qv_fatture_normalizza($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "DESCRIPTION,STATOID,CONTOID,REFARROWID");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $DESCRIPTION=$pratica["DESCRIPTION"];
        $STATOID=$pratica["STATOID"];
        $REG_CONTOID=$pratica["CONTOID"];
        $REFARROWID=$pratica["REFARROWID"];
        
        if(!isset($data["CONTOID"])){
            $data["CONTOID"]=$REG_CONTOID;
        }

        // DETERMINO L'ATTIVITA' FATTURAID
        $fattura=qv_solverecord($maestro, $data, "QW_ATTIVITA", "FATTURAID", "", $FATTURAID, "AUXTIME,STATUS");
        if($FATTURAID==""){
            $babelcode="QVERR_FATTURAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la fattura";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $AUXTIME=qv_strtime($fattura["AUXTIME"]);
        $STATUS=$fattura["STATUS"];
        
        // DETERMINO L'EVENTUALE NUOVO STATUS DELLA FATTURA
        if(isset($data["STATUS"])){
            $STATUS=$data["STATUS"];
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
        
        // DETERMINO DATA SCADENZA
        if(isset($data["DATASCADENZA"]))
            $DATASCADENZA=$data["DATASCADENZA"];
        else
            $DATASCADENZA=$AUXTIME;
        
        // AGGIORNO L'ATTIVITA' "FATTURA"
        $datax=array();
        $datax["SYSID"]=$FATTURAID;
        $datax["PRATICAID"]=$PRATICAID;
        $datax["CONSISTENCY"]="0";
        $datax["STATUS"]=$STATUS;
        $jret=qv_attivita_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $PROTSERIE=$jret["params"]["PROTSERIE"];
        $PROTPROGR=$jret["params"]["PROTPROGR"];
        
        // DETERMINO LA DIVISA
        if($REFGENREID!="")
            $GENREID=$REFGENREID;
        else
            $GENREID=qv_actualid($maestro, "0MONEYEURO00");

        // ASSEGNO IL CONTO A TUTTI I FLUSSI DI TRASFERIMENTO
        $FLUSSOTYPE=qv_actualid($maestro, "0FLUSSI00000");
        $sql="SELECT QVARROWS.SYSID AS SYSID, QVMOTIVES.DIRECTION AS DIRECTION FROM QVARROWS INNER JOIN QVMOTIVES ON QVMOTIVES.SYSID=QVARROWS.MOTIVEID WHERE QVARROWS.TYPOLOGYID='$FLUSSOTYPE' AND QVARROWS.GENREID='$GENREID' AND QVARROWS.SYSID IN (SELECT QVQUIVERARROW.ARROWID AS ARROWID FROM QVQUIVERARROW WHERE QVQUIVERARROW.QUIVERID='$PRATICAID')";
        maestro_query($maestro, $sql, $r);
        for($i=0; $i<count($r); $i++){
            $ARROWID=$r[$i]["SYSID"];
            $DIRECTION=intval($r[$i]["DIRECTION"]);
            if($DIRECTION==0)
                $sql="UPDATE QVARROWS SET BOWID='$CONTOID' WHERE SYSID='$ARROWID'";
            else
                $sql="UPDATE QVARROWS SET TARGETID='$CONTOID' WHERE SYSID='$ARROWID'";
            maestro_execute($maestro, $sql);
        }

        // AGGIORNO IL TOTALE FATTURA
        fatture_saldo($maestro, $PRATICAID, $CONTOID, $GENREID, $REFARROWID, false, $DESCRIPTION, $DATASCADENZA, $AUXTIME, $STATUS, $TOTAL);
        
        // VARIABILI DI RITORNO
        $babelparams["PROTSERIE"]=$PROTSERIE;
        $babelparams["PROTPROGR"]=$PROTPROGR;
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