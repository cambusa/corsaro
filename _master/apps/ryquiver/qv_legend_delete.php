<?php 
/****************************************************************************
* Name:            qv_legend_delete.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_objects_delete.php";
function qv_legend_delete($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL LEGEND
        $legend=qv_solverecord($maestro, $data, "QW_LEGEND", "LEGENDID", "", $LEGENDID, "*");
        if($LEGENDID==""){
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // SCANSIONE DELLE QUERY
        maestro_query($maestro, "SELECT SYSID FROM QW_LEGENDQUERY WHERE LEGENDID='$LEGENDID'", $r);
        for($i=0; $i<count($r); $i++){
            $QUERYID=$r[$i]["SYSID"];
        
            // ISTRUZIONE DI CANCELLAZIONE DELLA QUERY
            $datax=array();
            $datax["SYSID"]=$QUERYID;
            $jret=qv_objects_delete($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        
        // ISTRUZIONE DI CANCELLAZIONE LEGEND
        $datax=array();
        $datax["SYSID"]=$LEGENDID;
        $jret=qv_objects_delete($maestro, $datax);
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