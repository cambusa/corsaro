<?php 
/****************************************************************************
* Name:            qv_pratiche_auto.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."ryquiver/qv_quivers_update.php";
include_once $path_applications."ryquiver/qv_attivita_insert.php";
function qv_pratiche_auto($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "*");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare lo stato: [PRATICAID]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $RICHIEDENTEID=$pratica["RICHIEDENTEID"];
        $RICHIEDENTEDESCR="";
        
        // DETERMINO STATOID
        $stato=qv_solverecord($maestro, $data, "QW_PROCSTATI", "STATOID", "", $STATOID, "*");
        if($STATOID==""){
            $babelcode="QVERR_STATOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare lo stato: [STATOID]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $ATTOREID=$stato["ATTOREID"];
        
        // RISOLVO RICHIEDENTEDESCR
        $sql="SELECT DESCRIPTION FROM QVOBJECTS WHERE SYSID='$RICHIEDENTEID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $RICHIEDENTEDESCR=$r[0]["DESCRIPTION"];
        }
        
        // CERCO I MOTIVI AUTOMATICI ATTIVI DELLO STATO
        $sql="SELECT * FROM QW_MOTIVISTATO WHERE STATOID='$STATOID' AND ENABLED=1 AND AUTOMATICA=1 ORDER BY ORDINATORE";
        maestro_query($maestro, $sql, $r);
        for($i=0; $i<count($r); $i++){
            $DESCRIPTION=$r[$i]["DESCRIPTION"];
            $DESCRIPTION=str_replace("[!RICHIEDENTE]", $RICHIEDENTEDESCR, $DESCRIPTION);
            
            $REGISTRY=$r[$i]["REGISTRY"];
            $MOTIVEID=$r[$i]["SYSID"];
            
            $COUNTERPARTID=$r[$i]["COUNTERPARTID"];
            if($COUNTERPARTID==qv_actualid($maestro, "0ATTJOLLYRIC")){
                $COUNTERPARTID=$RICHIEDENTEID;
            }

            // INTESTAZIONE AUTOMATICA DELLA PRATICA
            $MOTIVE_INTESTAZIONE=intval($r[$i]["INTESTAZIONE"]);
            
            // CALCOLO AUTOMATICO DELLE DATE
            motivo_calcolodata($maestro, date("Ymd"), $r[$i], $GENREID, $AMOUNT, $BOWTIME, $TARGETTIME);
            
            // CREAZIONE DELLA ATTIVITA' AUTOMATICA
            $datax=array();
            $datax["PRATICAID"]=$PRATICAID;
            $datax["DESCRIPTION"]=$DESCRIPTION;
            $datax["REGISTRY"]=$REGISTRY;
            $datax["TYPOLOGYID"]=qv_actualid($maestro, "0ATTIVITA000");
            $datax["GENREID"]=$GENREID;
            $datax["AMOUNT"]=$AMOUNT;
            $datax["MOTIVEID"]=$MOTIVEID;
            $datax["BOWID"]=$ATTOREID;
            $datax["TARGETID"]=$COUNTERPARTID;
            $datax["BOWTIME"]=$BOWTIME;
            $datax["TARGETTIME"]=$TARGETTIME;
            $datax["AUXTIME"]=date("YmdHis");
            $datax["IMPORTANZA"]=1;
            $datax["STATOID"]=$STATOID;
            $jret=qv_attivita_insert($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            // PIPE
            $ARROWID=$jret["SYSID"];
            
            // GESTIONE INTESTAZIONE AUTOMATICA DELLA PRATICA
            if($MOTIVE_INTESTAZIONE){
                $datax=array();
                $datax["SYSID"]=$PRATICAID;
                $datax["DESCRIPTION"]=$DESCRIPTION;
                $datax["REGISTRY"]=$REGISTRY;
                $datax["DATAINIZIO"]=substr($BOWTIME, 0, 8);
                $datax["DATAFINE"]=substr($TARGETTIME, 0, 8);
                $jret=qv_quivers_update($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }
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