<?php 
/****************************************************************************
* Name:            qv_attivita_insert.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_arrows_clone.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."ryquiver/qv_sendmail.php";
include_once $path_applications."ryquiver/pratiche_date.php";
include_once $path_applications."ryquiver/protocollo_nuovo.php";
function qv_attivita_insert($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_databases;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHEJOIN", "PRATICAID", "", $PRATICAID, "*");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $STATOID=$pratica["STATOID"];
        $ATTOREID=$pratica["ATTOREID"];
        $INVIOEMAIL=intval($pratica["INVIOEMAIL"]);
        $STATUS=intval($pratica["STATUS"]);
        $STATUSTIME=qv_strtime($pratica["STATUSTIME"]);
        $RICHIEDENTEID=$pratica["RICHIEDENTEID"];
        $RICHIEDENTEDESCR="";
        
        if($STATUS>0){
            $babelcode="QVERR_PRATICACHIUSA";
            $b_params=array();
            $b_pattern="Pratica chiusa: impossibile inserire attività";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO BOWID ED TARGETID
        if(isset($data["BOWID"])){
            $BOWID=$data["BOWID"];
            $TARGETID=$ATTOREID;
        }
        else{
            $BOWID=$ATTOREID;
            $TARGETID="";
        }

        // PERFEZIONO TARGETID
        if(isset($data["TARGETID"])){
            $TARGETID=$data["TARGETID"];
        }

        // RISOLVO RICHIEDENTEDESCR E RICHUTENTEID
        $sql="SELECT DESCRIPTION,UTENTEID FROM QW_ATTORI WHERE SYSID='$RICHIEDENTEID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $RICHIEDENTEDESCR=$r[0]["DESCRIPTION"];
            $RICHUTENTEID=$r[0]["UTENTEID"];
        }
        
        // DETERMINO OPERATION
        if(isset($data["OPERATION"]))
            $OPERATION=strtoupper($data["OPERATION"]);
        else
            $OPERATION="INSERT";
        
        switch($OPERATION){
        case "INSERT":
            // DETERMINO MOTIVEID
            $motive=qv_solverecord($maestro, $data, "QW_MOTIVIATTIVITA", "MOTIVEID", "", $MOTIVEID, "*");
            if($MOTIVEID==""){
                $babelcode="QVERR_MOTIVEID";
                $b_params=array();
                $b_pattern="Dati insufficienti per individuare il motivo";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $REFARROWID="";

            if($TARGETID==""){
                $TARGETID=$motive["COUNTERPARTID"];
            }
            // DESTINATARIO JOLLY
            if($TARGETID==qv_actualid($maestro, "0ATTJOLLYRIC")){
                $TARGETID=$RICHIEDENTEID;
            }
            // INTESTAZIONE AUTOMATICA DELLA PRATICA (solo per la generazione automatica)
            //$MOTIVE_INTESTAZIONE=intval($motive["INTESTAZIONE"]);
            
            // CALCOLO AUTOMATICO DELLE DATE
            motivo_calcolodata($maestro, date("Ymd"), $motive, $GENREID, $AMOUNT, $BOWTIME, $TARGETTIME);
            
            break;
        case "ANSWER":
        case "CLONE":
            // DETERMINO REFARROWID
            $parent=qv_solverecord($maestro, $data, "QW_ATTIVITA", "REFARROWID", "", $REFARROWID, "*");
            if($REFARROWID==""){
                $babelcode="QVERR_PRATICAID";
                $b_params=array();
                $b_pattern="Dati insufficienti per individuare il riferimento";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $MOTIVEID=$parent["MOTIVEID"];
            $DESCRIPTION=$parent["DESCRIPTION"];
            $REGISTRY="";
            $GENREID=qv_actualid($maestro, "0TIMEDAYS000");
            $AMOUNT=1;
            $BOWTIME=date("Ymd");
            $TARGETTIME=pratiche_sommagiorni($BOWTIME, 1, 2);
            if($OPERATION=="ANSWER"){
                $TARGETID=$parent["BOWID"];
            }
            // LEGGO IL MOTIVO
            $sql="SELECT * FROM QW_MOTIVIATTIVITA WHERE SYSID='$MOTIVEID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==0){
                $babelcode="QVERR_MOTIVEID";
                $b_params=array();
                $b_pattern="Dati insufficienti per individuare il motivo";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $motive=$r[0];
            break;
        default:
            $babelcode="QVERR_OPERATION";
            $b_params=array();
            $b_pattern="Operazione non riconosciuta";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // DATI MOTIVO
        $DESCRIPTION=$motive["DESCRIPTION"];
        $DESCRIPTION=str_replace("[!RICHIEDENTE]", $RICHIEDENTEDESCR, $DESCRIPTION);
        $DESCRIPTION=str_replace("[!PRATICAID]", "[$PRATICAID]", $DESCRIPTION);
        
        $REGISTRY=$motive["REGISTRY"];
        $SETCONOSCENZA=$motive["SETCONOSCENZA"];

        // INVIO EMAIL
        $MOT_PROCESSOID=$motive["PROCESSOID"];
        $MOT_EMAIL=intval($motive["INVIOEMAIL"]);
        if($MOT_PROCESSOID!=""){
            $INVIOEMAIL=$MOT_EMAIL;
        }
            
        if($OPERATION!="CLONE"){
            // ISTRUZIONE DI CREAZIONE DI UNA NUOVA ATTIVITA'
            $datax=array();
            $datax["TYPOLOGYID"]=qv_actualid($maestro, "0ATTIVITA000");
            $datax["GENREID"]=$GENREID;
            $datax["AMOUNT"]=$AMOUNT;
            $datax["MOTIVEID"]=$MOTIVEID;
            $datax["DESCRIPTION"]=$DESCRIPTION;
            $datax["REGISTRY"]=$REGISTRY;
            $datax["BOWID"]=$BOWID;
            $datax["TARGETID"]=$TARGETID;
            $datax["BOWTIME"]=$BOWTIME;
            $datax["TARGETTIME"]=$TARGETTIME;
            $datax["AUXTIME"]=date("YmdHis");
            $datax["IMPORTANZA"]=1;
            $datax["STATOID"]=$STATOID;
            $datax["REFARROWID"]=$REFARROWID;
            if($OPERATION=="ANSWER"){
                $datax["CONSISTENCY"]=2;
            }
            // VALORI PASSATI ESTERNAMENTE
            if(isset($data["DESCRIPTION"])){
                $datax["DESCRIPTION"]=$data["DESCRIPTION"];
            }
            if(isset($data["REGISTRY"])){
                $datax["REGISTRY"]=$data["REGISTRY"];
            }
            if(isset($data["CONSISTENCY"])){
                $datax["CONSISTENCY"]=$data["CONSISTENCY"];
            }
            $jret=qv_arrows_insert($maestro, $datax);
        }
        else{
            $datax=array();
            $datax["SYSID"]=$REFARROWID;
            $datax["CONSISTENCY"]=2;
            $datax["STATUS"]=0;
            $datax["AUXTIME"]=date("YmdHis");
            $jret=qv_arrows_clone($maestro, $datax);
            unset($datax);
        }
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        // PIPE
        $ARROWID=$jret["SYSID"];
        
        // GESTIONE ISTANZE
        $ISTANZE=intval($motive["ISTANZE"]);
        if($ISTANZE>0){
            $sql="SELECT COUNT(*) AS ISTANZE FROM QVARROWS WHERE MOTIVEID='$MOTIVEID' AND AVAILABILITY<=1 AND SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                if(intval($r[0]["ISTANZE"])>=$ISTANZE){
                    $babelcode="QVERR_ISTANZE";
                    $b_params=array();
                    $b_pattern="Raggiunto il limite di istanze per questa attività";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
        }
        
        // GESTIONE PROTOCOLLAZIONE
        $PROTPROGR=0;
        $PROTSERIE=$motive["PROTSERIE"];
        if($PROTSERIE!=""){
            // LEGGO L'ATTIVITA' PER DETERMINARE LA CONSISTENCY
            $sql="SELECT CONSISTENCY FROM QVARROWS WHERE SYSID='$ARROWID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                if(intval($r[0]["CONSISTENCY"])==0){
                    $PROTPROGR=genera_protocollo($maestro, "QW_ATTIVITA", $PROTSERIE);
                }
            }
        }
        $datax=array();
        $datax["SYSID"]=$ARROWID;
        $datax["PROTSERIE"]=$PROTSERIE;
        $datax["PROTPROGR"]=$PROTPROGR;
        if($PROTPROGR>0)
            $datax["REFERENCE"]=$PROTSERIE.$PROTPROGR;
        else
            $datax["REFERENCE"]="";
        $jret=qv_arrows_update($maestro, $datax);
        unset($datax);
        
        // AGGANCIO DELL'ATTIVITA' ALLA PRATICA
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$ARROWID;
        $jret=qv_quivers_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // INVIO EMAIL
        if($INVIOEMAIL!=0){
            $RECIPIENTS="";
            // Per conoscenza
            $sql="SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='$SETCONOSCENZA'";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $RECIPIENTS.="|".$r[$i]["SELECTEDID"];
            }
            // Destinatario
            $sql="SELECT * FROM QVARROWS WHERE SYSID='$ARROWID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                $CONSISTENCY=intval($r[0]["CONSISTENCY"]);
                $BOWID=$r[0]["BOWID"];
                $TARGETID=$r[0]["TARGETID"];
                //if($BOWID!="" && $BOWID!=$ATTOREID){
                //    $RECIPIENTS.="|".$BOWID;
                //}
                if($TARGETID!="" && $TARGETID!=$ATTOREID){
                    $RECIPIENTS.="|".$TARGETID;
                }
                if($RECIPIENTS!="" && $CONSISTENCY==0){
                    // DETERMINO SE L'INVIO E' A UN ESTERNO
                    $esterno=false;
                    // DETERMINO ATTORE ROBOT
                    $ROBOTID="";
                    if(strpos($RECIPIENTS, $RICHIEDENTEID)!==false){
                        if($RICHUTENTEID==""){  // Non ha autenticazione Ego
                            // CERCO DI RISOLVERE L'ATTORE ROBOT
                            
                            // CASELLA DI POSTA
                            $env=qv_setting($maestro, "_EMAILBOX", "mailbox");
                            $env_robotname="";
                            include($path_databases."_environs/$env.php");
                            $emailrobot=$env_robotname;
                            if($emailrobot!=""){
                                // LETTURA ROBOT
                                $sql="SELECT SYSID FROM QW_ATTORI WHERE [:UPPER(NAME)]='".strtoupper($emailrobot)."'";
                                maestro_query($maestro, $sql, $r);
                                if(count($r)>0){
                                    $ROBOTID=$r[0]["SYSID"];
                                }
                            }
                            if($ROBOTID!=""){
                                $esterno=true;
                            }
                        }
                    }
                    $datax=array();
                    $datax["TABLE"]="QVARROWS";
                    $datax["SYSID"]=$ARROWID;
                    $datax["MAILTABLE"]="QW_ATTORI";
                    if($esterno)
                        $datax["SENDERID"]=$ROBOTID;
                    else
                        $datax["SENDERID"]=$ATTOREID;
                    $datax["RECIPIENTS"]=$RECIPIENTS;
                    $datax["ARGS"]=array();
                    $datax["ARGS"][]=array("SUBJECT" => "[$PRATICAID] Inserimento: ");
                    $jret=qv_sendmail($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
            }
        }
        // VARIABILI DI RITORNO
        $babelparams["ARROWID"]=$ARROWID;
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