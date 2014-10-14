<?php 
/****************************************************************************
* Name:            fatture_saldo.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function fatture_saldo($maestro, $PRATICAID, $CONTOID, $GENREID, $REFARROWID, $MOREDATA=false, $DESCRIPTION="", $DATASCADENZA="", $AUXTIME="", $STATUS="", &$TOTAL=0){
    // INIZIALIZZO IL TOTALE
    $TOTAL=0;

    // SCANDISCO I FLUSSI
    $lordo=0;
    $totimposta=0;
    $sql="SELECT * FROM QW_FLUSSI WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')";
    maestro_query($maestro, $sql, $f);
    for($i=0; $i<count($f); $i++){
        $BOWID=$f[$i]["BOWID"];
        if($CONTOID!="" && $BOWID==$CONTOID)
            $segno=1;
        else
            $segno=-1;

        $importo=$segno*round(floatval($f[$i]["AMOUNT"]), 2);
        $aliquota=floatval($f[$i]["ALIQUOTA"]);
        
        $lordo+=$importo;
        
        if($aliquota>0){
            $imposta=round($importo*$aliquota/100, 2);
            $totimposta+=$imposta;
        }
    }
    $lordo=round($lordo, 2);
    $totimposta=round($totimposta, 2);
    $TOTAL=round($lordo+$totimposta, 2);
    $ABS=abs($TOTAL);

    // CONTROLLO L'ESISTENZA DEL MOVIMENTO FATTURA
    if($REFARROWID==""){
        $datax=array();
        if($DESCRIPTION!="")
            $datax["DESCRIPTION"]=$DESCRIPTION;
        else
            $datax["DESCRIPTION"]="Fattura $PRATICAID";
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0MOVIMENTI00");
        $datax["AMOUNT"]=$ABS;
        $datax["GENREID"]=$GENREID;
        $datax["MOTIVEID"]=qv_actualid($maestro, "0CAUSEQUIV00");
        $datax["BOWID"]=$CONTOID;
        if($DATASCADENZA!=""){
            $datax["BOWTIME"]=$DATASCADENZA;
        }
        if($AUXTIME!=""){
            $datax["TARGETTIME"]=$AUXTIME;
            $datax["AUXTIME"]=$AUXTIME;
        }
        $datax["CONSISTENCY"]="1";
        if($STATUS!=""){
            $datax["STATUS"]=$STATUS;
        }
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $REFARROWID=$jret["SYSID"];
    }
    else{
        // AGGIORNO IL MOVIMENTO DI FATTURA
        $datax=array();
        $datax["SYSID"]=$REFARROWID;
        if($DESCRIPTION!=""){
            $datax["DESCRIPTION"]=$DESCRIPTION;
        }
        $datax["AMOUNT"]=$ABS;
        $datax["BOWID"]=$CONTOID;
        if($DATASCADENZA!=""){
            $datax["BOWTIME"]=$DATASCADENZA;
        }
        if($AUXTIME!=""){
            $datax["TARGETTIME"]=$AUXTIME;
            $datax["AUXTIME"]=$AUXTIME;
        }
        if($STATUS!=""){
            $datax["STATUS"]=$STATUS;
        }
        $jret=qv_arrows_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
    }

    // AGGIORNO LA PRATICA
    $datax=array();
    $datax["SYSID"]=$PRATICAID;
    $datax["CONTOID"]=$CONTOID;
    $datax["REFARROWID"]=$REFARROWID;
    $datax["AUXAMOUNT"]=$ABS;
    if($MOREDATA!==false){
        $datax["MOREDATA"]=$MOREDATA;
    }
    $jret=qv_quivers_update($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
}
?>