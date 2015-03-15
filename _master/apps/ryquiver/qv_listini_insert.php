<?php 
/****************************************************************************
* Name:            qv_listini_insert.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_equivalences_add.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
function qv_listini_insert($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO LISTINOID
        qv_solverecord($maestro, $data, "QW_LISTINI", "LISTINOID", "", $LISTINOID);
        if($LISTINOID==""){
            $babelcode="QVERR_LISTINOID";
            $b_params=array();
            $b_pattern="Listino non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO REFTYPOLOGYID (TIPO FRECCIA DI RIFERIMENTO)
        qv_solverecord($maestro, $data, "QVARROWTYPES", "REFTYPOLOGYID", "", $REFTYPOLOGYID);
        if($REFTYPOLOGYID==""){
            $babelcode="QVERR_REFTYPOLOGYID";
            $b_params=array();
            $b_pattern="Tipo freccia di riferimento non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO REFGENREID (GENERE DI RIFERIMENTO)
        $refgenre=qv_solverecord($maestro, $data, "QVGENRES", "REFGENREID", "", $REFGENREID, "*");
        if($REFGENREID==""){
            $babelcode="QVERR_REFGENREID";
            $b_params=array();
            $b_pattern="Genere di riferimento non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $DESCRIPTION=$refgenre["DESCRIPTION"];

        // DETERMINO REFMOTIVEID (MOTIVO DI RIFERIMENTO)
        qv_solverecord($maestro, $data, "QVMOTIVES", "REFMOTIVEID", "", $REFMOTIVEID);
        if($REFMOTIVEID==""){
            $babelcode="QVERR_REFMOTIVEID";
            $b_params=array();
            $b_pattern="Motivo di riferimento non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO EQTYPOLOGYID (TIPO FRECCIA EQUIVALENTE)
        qv_solverecord($maestro, $data, "QVARROWTYPES", "EQTYPOLOGYID", "", $EQTYPOLOGYID);
        if($EQTYPOLOGYID==""){
            $babelcode="QVERR_EQTYPOLOGYID";
            $b_params=array();
            $b_pattern="Tipo freccia equivalente non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO EQGENREID (GENERE EQUIVALENTE)
        qv_solverecord($maestro, $data, "QVGENRES", "EQGENREID", "", $EQGENREID);
        if($EQGENREID==""){
            $babelcode="QVERR_EQGENREID";
            $b_params=array();
            $b_pattern="Genere equivalente non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO EQMOTIVEID (MOTIVO EQUIVALENTE)
        qv_solverecord($maestro, $data, "QVMOTIVES", "EQMOTIVEID", "", $EQMOTIVEID);
        if($EQMOTIVEID==""){
            $babelcode="QVERR_EQMOTIVEID";
            $b_params=array();
            $b_pattern="Motivo equivalente non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO LA DATA VALIDITÀ
        if(isset($data["AUXTIME"]))
            $AUXTIME=$data["AUXTIME"];
        else
            $AUXTIME=date("Ymd");

        // INSERISCO LA FRECCIA DI RIFERIMENTO
        $datax["TYPOLOGYID"]=$REFTYPOLOGYID;
        $datax["GENREID"]=$REFGENREID;
        $datax["AMOUNT"]="1";
        $datax["MOTIVEID"]=$REFMOTIVEID;
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["AUXTIME"]=$AUXTIME;
        $datax["CONSISTENCY"]="3";
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $REFERENCEID=$jret["SYSID"];
        
        // AGGANCIO LA FRECCIA DI RIFERIMENTO AL LISTINO
        $datax=array();
        $datax["QUIVERID"]=$LISTINOID;
        $datax["ARROWID"]=$REFERENCEID;
        $jret=qv_quivers_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // INSERISCO LA FRECCIA EQUIVALENTE
        $datax["TYPOLOGYID"]=$EQTYPOLOGYID;
        $datax["GENREID"]=$EQGENREID;
        $datax["AMOUNT"]="0";
        $datax["MOTIVEID"]=$EQMOTIVEID;
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["AUXTIME"]=$AUXTIME;
        $datax["CONSISTENCY"]="3";
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $EQUIVALENTID=$jret["SYSID"];
        
        // CREO L'EQUIVALENZA
        $datax=array();
        $datax["REFERENCEID"]=$REFERENCEID;
        $datax["EQUIVALENTID"]=$EQUIVALENTID;
        $jret=qv_equivalences_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $SYSID=$jret["SYSID"];
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