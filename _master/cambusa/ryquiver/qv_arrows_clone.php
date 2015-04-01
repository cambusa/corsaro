<?php 
/****************************************************************************
* Name:            qv_arrows_clone.php                                      *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivercln.php";
include_once "qv_arrows_insert.php";
function qv_arrows_clone($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // INDIVIDUAZIONE RECORD
        $record=qv_solverecord($maestro, $data, "QVARROWS", "SYSID", "NAME", $SYSID, "*");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // INIZIALIZZO IL VETTORE IN INGRESSO PER IL NUOVO INSERIMENTO
        $datains=array();
        
        // CAMPI STANDARD
        $datains["DESCRIPTION"]=$record["DESCRIPTION"];
        $datains["REGISTRY"]=$record["REGISTRY"];
        $datains["BOWID"]=$record["BOWID"];
        $datains["BOWTIME"]=qv_strtime($record["BOWTIME"]);
        $datains["TARGETID"]=$record["TARGETID"];
        $datains["TARGETTIME"]=qv_strtime($record["TARGETTIME"]);
        $datains["AUXTIME"]=qv_strtime($record["AUXTIME"]);
        $datains["STATUSTIME"]=qv_strtime($record["STATUSTIME"]);
        $datains["TYPOLOGYID"]=$record["TYPOLOGYID"];
        $datains["MOTIVEID"]=$record["MOTIVEID"];
        $datains["GENREID"]=$record["GENREID"];
        $datains["AMOUNT"]=$record["AMOUNT"];
        $datains["REFERENCE"]=$record["REFERENCE"];
        $datains["TAG"]=$record["TAG"];
        $datains["CONSISTENCY"]=$record["CONSISTENCY"];
        $datains["AVAILABILITY"]=$record["AVAILABILITY"];
        $datains["SCOPE"]=$record["SCOPE"];
        $datains["UPDATING"]=$record["UPDATING"];
        $datains["DELETING"]=$record["DELETING"];
        $datains["STATUS"]=$record["STATUS"];
        $datains["STATUSRISK"]=$record["STATUSRISK"];
        $datains["PHASE"]=$record["PHASE"];
        $datains["PHASENOTE"]=$record["PHASENOTE"];
        $datains["PROVIDER"]=$record["PROVIDER"];
        $datains["PARCEL"]=$record["PARCEL"];
        
        // CAMBIO CONSISTENCY
        if(isset($data["CONSISTENCY"])){
            $CONSISTENCY=intval($data["CONSISTENCY"]);
            if($CONSISTENCY>=0 && $CONSISTENCY<=3 )
                $datains["CONSISTENCY"]=$CONSISTENCY;
        }

        // CAMBIO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS>=0 && $STATUS<=3 )
                $datains["STATUS"]=$STATUS;
        }
        
        // CAMBIO AUXTIME
        if(isset($data["AUXTIME"])){
            $datains["AUXTIME"]=qv_strtime($data["AUXTIME"]);
        }
        
        // CAMPI ESTESI
        qv_cloning($maestro, "QVARROW", $record["TYPOLOGYID"], $SYSID, $datains);
        
        // INSERIMENTO CLONE
        $ret=qv_arrows_insert($maestro, $datains);
        if($ret["success"]==1){
            // ALLEGATI
            qv_cloneattachments($maestro, $SYSID, $ret["SYSID"]);
        }
        $success=$ret["success"];
        $babelcode=$ret["code"];
        $babelparams=$ret["params"];
        $message=$ret["message"];
        $SYSID=$ret["SYSID"];
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