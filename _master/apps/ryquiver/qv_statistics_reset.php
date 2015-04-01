<?php 
/****************************************************************************
* Name:            qv_statistics_reset.php                                  *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_statistics_reset($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // LEGGO IL SITO
        qv_solverecord($maestro, $data, "QW_WEBSITES", "SITEID", "", $SITEID);
        if($SITEID==""){
            $babelcode="QVERR_SITEID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il sito";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        $sql="DELETE FROM OBJECTS_WEBSTATISTICS WHERE SITEID='$SITEID'";
        if(!maestro_execute($maestro, $sql, false)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"], "STATEMENT" => "Reset Statistics" );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        $TYPOLOGYID=qv_actualid($maestro, "0WEBSTATIST0");
        $sql="DELETE FROM QVOBJECTS WHERE TYPOLOGYID='$TYPOLOGYID' AND SYSID NOT IN (SELECT SYSID FROM OBJECTS_WEBSTATISTICS)";
        if(!maestro_execute($maestro, $sql, false)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"], "STATEMENT" => "Reset Statistics" );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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