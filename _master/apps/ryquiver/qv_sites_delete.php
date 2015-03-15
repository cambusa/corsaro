<?php 
/****************************************************************************
* Name:            qv_sites_delete.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_objects_update.php";
include_once $path_cambusa."ryquiver/qv_objects_delete.php";
include_once $path_cambusa."ryquiver/qv_arrows_delete.php";
function qv_sites_delete($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL SITO
        $site=qv_solverecord($maestro, $data, "QW_WEBSITES", "SITEID", "", $SITEID, "*");
        if($SITEID==""){
            $babelcode="QVERR_SITEID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il sito";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // ISTRUZIONE DI PULIZIA SITO
        $datax=array();
        $datax["SYSID"]=$SITEID;
        $datax["HOMEPAGEID"]="";
        $datax["DEFAULTID"]="";
        $jret=qv_objects_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // SCANSIONE DEI CONTENITORI
        maestro_query($maestro, "SELECT SYSID FROM QW_WEBCONTAINERS WHERE SITEID='$SITEID'", $r);
        for($i=0; $i<count($r); $i++){
            $CONTAINERID=$r[$i]["SYSID"];
        
            // ISTRUZIONE DI PULIZIA CONTENITORE
            $datax=array();
            $datax["SYSID"]=$CONTAINERID;
            $datax["CONTENTID"]="";
            $datax["REFOBJECTID"]="";
            $jret=qv_objects_update($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }

        // SCANSIONE DEI CONTENUTI
        maestro_query($maestro, "SELECT SYSID FROM QW_WEBCONTENTS WHERE SITEID='$SITEID'", $r);
        for($i=0; $i<count($r); $i++){
            $CONTENTID=$r[$i]["SYSID"];
        
            // ISTRUZIONE DI CANCELLAZIONE CONTENUTO
            $datax=array();
            $datax["SYSID"]=$CONTENTID;
            $jret=qv_arrows_delete($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        
        // SCANSIONE DEI CONTENITORI
        maestro_query($maestro, "SELECT SYSID FROM QW_WEBCONTAINERS WHERE SITEID='$SITEID' ORDER BY REFOBJECTID", $r);
        for($i=0; $i<count($r); $i++){
            $CONTAINERID=$r[$i]["SYSID"];
        
            // ISTRUZIONE DI CANCELLAZIONE CONTENITORE
            $datax=array();
            $datax["SYSID"]=$CONTAINERID;
            $jret=qv_objects_delete($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        
        // ISTRUZIONE DI CANCELLAZIONE SITO
        $datax=array();
        $datax["SYSID"]=$SITEID;
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