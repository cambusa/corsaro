<?php 
/****************************************************************************
* Name:            qv_processi_clone.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_clone.php";
include_once $path_cambusa."ryquiver/qv_motives_clone.php";
include_once $path_cambusa."ryquiver/qv_motives_update.php";
include_once $path_cambusa."ryquiver/qv_selections_copy.php";
include_once $path_cambusa."ryquiver/qv_objects_clone.php";
include_once $path_cambusa."ryquiver/qv_objects_update.php";
include_once $path_cambusa."ryquiver/qv_arrows_clone.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."ryquiver/qv_quivers_update.php";
function qv_processi_clone($maestro, $data){
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

        // ISTRUZIONE DI CLONAZIONE QUIVER PROCESSO
        $datax=array();
        $datax["SYSID"]=$PROCESSOID;
        $datax["DESCRIPTION"]="(nuovo processo)";
        $jret=qv_quivers_clone($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $CLONE_PROCESSOID=$jret["SYSID"];

        // ISTRUZIONE DI CAMBIO SETINTERPROCESSO
        $datax=array();
        $datax["SYSID"]=$CLONE_PROCESSOID;
        $datax["SETINTERPROCESSO"]=qv_createsysid($maestro);
        $jret=qv_quivers_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $CLONE_PROCESSOID=$jret["SYSID"];

        // INIZIALIZZAZIONE DEI VETTORI MOTIVI E STATI
        $motivi=array();
        $stati=array();
        
        // ISTRUZIONI DI CLONAZIONE MOTIVI
        maestro_query($maestro, "SELECT SYSID,SETCONOSCENZA FROM QW_MOTIVIATTIVITA WHERE PROCESSOID='$PROCESSOID'", $r);
        for($i=0; $i<count($r); $i++){
            $MOTIVEID=$r[$i]["SYSID"];
            $SETCONOSCENZA=$r[$i]["SETCONOSCENZA"];
            
            // CLONAZIONE
            $datax=array();
            $datax["SYSID"]=$MOTIVEID;
            $jret=qv_motives_clone($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            $CLONE_MOTIVEID=$jret["SYSID"];
            $motivi[$MOTIVEID]=$CLONE_MOTIVEID;
            
            // MODIFICA DI PROCESSOID E SETCONOSCENZA
            $CLONE_SETCONOSCENZA=qv_createsysid($maestro);

            $datax=array();
            $datax["SYSID"]=$CLONE_MOTIVEID;
            $datax["PROCESSOID"]=$CLONE_PROCESSOID;
            $datax["SETCONOSCENZA"]=$CLONE_SETCONOSCENZA;
            $jret=qv_motives_update($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }

            // CLONAZIONE DELLA SELEZIONE SETCONOSCENZA
            $datax=array();
            $datax["PARENTID"]=$SETCONOSCENZA;
            $datax["TARGETID"]=$CLONE_SETCONOSCENZA;
            $jret=qv_selections_copy($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }                        
        
        // ISTRUZIONE DI CLONAZIONE ATTORI
        $datax=array();
        $datax["PARENTID"]=$PROCESSOID;
        $datax["TARGETID"]=$CLONE_PROCESSOID;
        $jret=qv_selections_copy($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // ISTRUZIONI DI CLONAZIONE STATI
        maestro_query($maestro, "SELECT SYSID FROM QW_PROCSTATI WHERE PROCESSOID='$PROCESSOID'", $r);
        for($i=0; $i<count($r); $i++){
            $STATOID=$r[$i]["SYSID"];
            
            // CLONAZIONE
            $datax=array();
            $datax["SYSID"]=$STATOID;
            $jret=qv_objects_clone($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            $CLONE_STATOID=$jret["SYSID"];
            $stati[$STATOID]=$CLONE_STATOID;

            // MODIFICA DI PROCESSOID
            $datax=array();
            $datax["SYSID"]=$CLONE_STATOID;
            $datax["PROCESSOID"]=$CLONE_PROCESSOID;
            $jret=qv_objects_update($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }

            // CLONAZIONE DEI VINCOLI
            $datax=array();
            $datax["PARENTID"]=$STATOID;
            $datax["TARGETID"]=$CLONE_STATOID;
            $datax["SELECTEDID"]=$motivi;
            $jret=qv_selections_copy($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }                        
        
        // INSTRUZIONI DI CLONAZIONE DELLE TRANSIZIONI
        foreach($stati as $STATOID => $CLONE_STATOID){
            maestro_query($maestro, "SELECT SYSID,BOWID,TARGETID FROM QW_TRANSIZIONI WHERE BOWID='$STATOID' AND TARGETID<>''", $r);
            for($i=0; $i<count($r); $i++){
                $TRANSID=$r[$i]["SYSID"];
                $BOWID=$r[$i]["BOWID"];
                $TARGETID=$r[$i]["TARGETID"];
                
                // CLONAZIONE
                $datax=array();
                $datax["SYSID"]=$TRANSID;
                $jret=qv_arrows_clone($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }
                $CLONE_TRANSID=$jret["SYSID"];
                
                // MODIFICA DI BOWID E TARGETID
                $datax=array();
                $datax["SYSID"]=$CLONE_TRANSID;
                $datax["BOWID"]=$stati[$BOWID];
                $datax["TARGETID"]=$stati[$TARGETID];
                $jret=qv_arrows_update($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }

                // AGGANCIO AL QUIVER
                $datax=array();
                $datax["QUIVERID"]=$CLONE_PROCESSOID;
                $datax["ARROWID"]=$CLONE_TRANSID;
                $jret=qv_quivers_add($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }
            }
        }

        // VARIABILI DI RITORNO
        $babelparams["PROCESSOID"]=$CLONE_PROCESSOID;
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