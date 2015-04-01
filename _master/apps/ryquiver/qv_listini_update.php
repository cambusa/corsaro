<?php 
/****************************************************************************
* Name:            qv_listini_update.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
function qv_listini_update($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO EQUIVALENCEID
        $equivalence=qv_solverecord($maestro, $data, "QVEQUIVALENCES", "EQUIVALENCEID", "", $EQUIVALENCEID, "*");
        if($EQUIVALENCEID==""){
            $babelcode="QVERR_EQUIVALENCEID";
            $b_params=array();
            $b_pattern="Equivalenza non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REFERENCEID=$equivalence["REFERENCEID"];
        $EQUIVALENTID=$equivalence["EQUIVALENTID"];

        // AGGIORNO LA FRECCIA DI RIFERIMENTO
        $datax=array();
        $datax["SYSID"]=$REFERENCEID;
        if(isset($data["DESCRIPTION"])){
            $datax["DESCRIPTION"]=$data["DESCRIPTION"];
        }
        if(isset($data["AUXTIME"])){
            $datax["AUXTIME"]=$data["AUXTIME"];
        }
        if(isset($data["REFAMOUNT"])){
            $datax["AMOUNT"]=$data["REFAMOUNT"];
        }
        $jret=qv_arrows_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // AGGIORNO LA FRECCIA EQUIVALENTE
        $datax=array();
        $datax["SYSID"]=$EQUIVALENTID;
        if(isset($data["DESCRIPTION"])){
            $datax["DESCRIPTION"]=$data["DESCRIPTION"];
        }
        if(isset($data["AUXTIME"])){
            $datax["AUXTIME"]=$data["AUXTIME"];
        }
        if(isset($data["EQAMOUNT"])){
            $datax["AMOUNT"]=$data["EQAMOUNT"];
        }
        if(isset($data["EQGENREID"])){
            $datax["GENREID"]=$data["EQGENREID"];
        }
        $jret=qv_arrows_update($maestro, $datax);
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