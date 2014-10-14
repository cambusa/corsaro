<?php 
/****************************************************************************
* Name:            qv_processi_delete.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_deepdelete.php";
include_once $path_cambusa."ryquiver/qv_motives_delete.php";
include_once $path_cambusa."ryquiver/qv_selections_remove.php";
include_once $path_cambusa."ryquiver/qv_objects_delete.php";
function qv_processi_delete($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL PROCESSO
        $processo=qv_solverecord($maestro, $data, "QW_PROCESSI", "PROCESSOID", "", $PROCESSOID, "*");
        if($PROCESSOID==""){
            $babelcode="QVERR_PROCESSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $INTERPROCESSO=$processo["SETINTERPROCESSO"];
        
        // ISTRUZIONE DI CANCELLAZIONE QUIVER PROCESSO
        $datax=array();
        $datax["SYSID"]=$PROCESSOID;
        $jret=qv_quivers_deepdelete($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // ISTRUZIONI DI CANCELLAZIONE MOTIVI
        maestro_query($maestro, "SELECT SYSID FROM QW_MOTIVIATTIVITA WHERE PROCESSOID='$PROCESSOID'", $r);
        for($i=0; $i<count($r); $i++){
            $datax=array();
            $datax["SYSID"]=$r[$i]["SYSID"];
            $jret=qv_motives_delete($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }                        
        
        // ISTRUZIONE DI CANCELLAZIONE INTERPROCESSO
        $datax=array();
        $datax["PARENTID"]=$INTERPROCESSO;
        $jret=qv_selections_remove($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // ISTRUZIONI DI CANCELLAZIONE STATI
        maestro_query($maestro, "SELECT SYSID FROM QW_PROCSTATI WHERE PROCESSOID='$PROCESSOID'", $r);
        for($i=0; $i<count($r); $i++){
            $datax=array();
            $datax["SYSID"]=$r[$i]["SYSID"];
            $jret=qv_objects_delete($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
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