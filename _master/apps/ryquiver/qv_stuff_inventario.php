<?php 
/****************************************************************************
* Name:            qv_stuff_inventario.php                                  *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
function qv_stuff_inventario($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        /*************************************************************
        Da collocazione, istante, ammontare da inventario
        si deve registarre un trasferimento per cui
        la giacenza in quell'istante coincida con l'ammontare stesso
        *************************************************************/
        
        // DETERMINO COLLID
        $collocazione=qv_solverecord($maestro, $data, "QVOBJECTS", "COLLID", "", $COLLID, "*");
        if($COLLID==""){
            $babelcode="QVERR_COLLID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la collocazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $GENREID=$collocazione["REFGENREID"];
        
        // DETERMINO ROUNDING
        if($GENREID!=""){
            maestro_query($maestro, "SELECT ROUNDING FROM QVGENRES WHERE SYSID='$GENREID'", $r);
            if(count($r)>0)
                $ROUNDING=intval($r[0]["ROUNDING"]);
            else
                $ROUNDING=0;
        }
        else{
            $babelcode="QVERR_GENREID";
            $b_params=array();
            $b_pattern="Genere non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO MOMENTO
        if(isset($data["MOMENTO"]))
            $MOMENTO=qv_strtime($data["MOMENTO"]);
        else
            $MOMENTO=date("YmdHis");
        
        // DETERMINO AMOUNT
        if(isset($data["AMOUNT"]))
            $AMOUNT=round(floatval($data["AMOUNT"]), $ROUNDING);
        else
            $AMOUNT=0;
            
        // DETERMINO LA GIACENZA
        maestro_query($maestro, "SELECT * FROM QWCBALANCES WHERE SYSID='$COLLID' AND EVENTTIME<=[:TIME($MOMENTO)] ORDER BY EVENTTIME DESC", $r);
        if(count($r)>0)
            $GIACENZA=floatval($r[0]["BALANCE"]);
        else
            $GIACENZA=0;
            
        // DETERMINO UN MOTIVO DI INVENTARIO
        maestro_query($maestro, "SELECT SYSID FROM QW_MOTIVITRASF WHERE INVENTARIO=1", $r);
        if(count($r)>0){
            $MOTIVEID=$r[0]["SYSID"];
        }
        else{
            $babelcode="QVERR_NOINVENTARIO";
            $b_params=array();
            $b_pattern="Non esistono motivi di inventario";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // AMMONTARE DELLA FRECCIA DI INVENTARIO
        $DELTA=round($AMOUNT-$GIACENZA, $ROUNDING);
        
        if($DELTA!=0){
            if($DELTA>0){
                // DEVO AGGIUNGERE MERCE
                $BOWID="";
                $TARGETID=$COLLID;
            }
            else{
                // DEVO TOGLIERE MERCE
                $BOWID=$COLLID;
                $TARGETID="";
            }
            
            // ISTRUZIONE DI INSERIMENTO FRECCIA
            $datax=array();
            $datax["TYPOLOGYID"]=qv_actualid($maestro, "0TRASFERIMEN");
            $datax["GENREID"]=$GENREID;
            $datax["MOTIVEID"]=$MOTIVEID;
            $datax["BOWID"]=$BOWID;
            $datax["BOWTIME"]=$MOMENTO;
            $datax["TARGETID"]=$TARGETID;
            $datax["TARGETTIME"]=$MOMENTO;
            $datax["AMOUNT"]=abs($DELTA);
            $datax["STATUS"]=2;
            $jret=qv_arrows_insert($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        // VARIABILI DI RITORNO
        $babelparams["DELTA"]=$DELTA;
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