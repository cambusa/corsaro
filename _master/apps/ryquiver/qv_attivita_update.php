<?php 
/****************************************************************************
* Name:            qv_attivita_update.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_sendmail.php";
include_once $path_cambusa."ryquiver/qv_messages_send.php";
include_once $path_applications."ryquiver/protocollo_nuovo.php";
include_once $path_applications."ryquiver/attivita_notifiche.php";
function qv_attivita_update($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_databases;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO SYSID
        $arrow=qv_solverecord($maestro, $data, "QVARROWS", "SYSID", "", $ARROWID, "*");
        if($ARROWID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare l'attività";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_DESCRIPTION=$arrow["DESCRIPTION"];
        $REG_BOWID=$arrow["BOWID"];
        $REG_TARGETID=$arrow["TARGETID"];
        $REG_MOTIVEID=$arrow["MOTIVEID"];
        $REG_CONSISTENCY=intval($arrow["CONSISTENCY"]);
        $REG_STATUS=intval($arrow["STATUS"]);
        $REG_BOWTIME=qv_strtime($arrow["BOWTIME"]);
        $REG_TARGETTIME=qv_strtime($arrow["TARGETTIME"]);
        
        // CONGRUENZA DATE
        if(isset($data["BOWTIME"]))
            $BOWTIME=qv_strtime($data["BOWTIME"]);
        else
            $BOWTIME=$REG_BOWTIME;

        if(isset($data["TARGETTIME"]))
            $TARGETTIME=qv_strtime($data["TARGETTIME"]);
        else
            $TARGETTIME=$REG_TARGETTIME;

        if($BOWTIME>$TARGETTIME){
            $babelcode="QVERR_CONGRUENZADATE";
            $b_params=array();
            $b_pattern="Date incongruenti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if(isset($data["BOWID"]))
            $BOWID=$data["BOWID"];
        else
            $BOWID=$REG_BOWID;

        if(isset($data["TARGETID"]))
            $TARGETID=$data["TARGETID"];
        else
            $TARGETID=$REG_TARGETID;

        if(isset($data["MOTIVEID"]))
            $MOTIVEID=$data["MOTIVEID"];
        else
            $MOTIVEID=$REG_MOTIVEID;

        if(isset($data["CONSISTENCY"]))
            $CONSISTENCY=intval($data["CONSISTENCY"]);
        else
            $CONSISTENCY=$REG_CONSISTENCY;

        if(isset($data["STATUS"]))
            $STATUS=intval($data["STATUS"]);
        else
            $STATUS=intval($REG_STATUS);

        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHEJOIN", "PRATICAID", "", $PRATICAID, "*");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $ATTOREID=$pratica["ATTOREID"];
        $INVIOEMAIL=intval($pratica["INVIOEMAIL"]);
        $RICHIEDENTEID=$pratica["RICHIEDENTEID"];

        // PERFEZIONO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $data["DESCRIPTION"]=str_replace("[!PRATICAID]", "[$PRATICAID]", $data["DESCRIPTION"]);
            $DESCRIPTION=$data["DESCRIPTION"];
        }
        else{
            $DESCRIPTION=$REG_DESCRIPTION;
        }
        // DESTINATARIO JOLLY
        if($TARGETID==qv_actualid($maestro, "0ATTJOLLYRIC")){
            $TARGETID=$RICHIEDENTEID;
        }
        // LEGGO MOTIVEID
        $PROTSERIE="";
        $PROTPROGR=0;
        maestro_query($maestro,"SELECT PROCESSOID,INVIOEMAIL,SETCONOSCENZA,PROTSERIE FROM QW_MOTIVIATTIVITA WHERE SYSID='$MOTIVEID'", $r);
        if(count($r)==1){
            $MOT_PROCESSOID=$r[0]["PROCESSOID"];
            $MOT_EMAIL=intval($r[0]["INVIOEMAIL"]);
            $SETCONOSCENZA=$r[0]["SETCONOSCENZA"];
            $PROTSERIE=$r[0]["PROTSERIE"];
            if($MOT_PROCESSOID!=""){
                $INVIOEMAIL=$MOT_EMAIL;
            }
        }
        else{
            $babelcode="QVERR_NOMOTIVEID";
            $b_params=array();
            $b_pattern="Motivo inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // GESTIONE PROTOCOLLAZIONE
        if($REG_CONSISTENCY==2 && $CONSISTENCY==0){
            $PROTPROGR=genera_protocollo($maestro, "QW_ATTIVITA", $PROTSERIE);
            $data["PROTSERIE"]=$PROTSERIE;
            $data["PROTPROGR"]=$PROTPROGR;
            if($PROTPROGR>0)
                $data["REFERENCE"]=$PROTSERIE.$PROTPROGR;
            else
                $data["REFERENCE"]="";
        }

        // ISTRUZIONE DI AGGIORNAMENTO DELL'ATTIVITA'
        $datax=$data;
        $jret=qv_arrows_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // INVIO EMAIL
        if($INVIOEMAIL!=0 && $CONSISTENCY==0){
            $RECIPIENTS="";
            // Per conoscenza
            $sql="SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='$SETCONOSCENZA'";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $RECIPIENTS.="|".$r[$i]["SELECTEDID"];
            }
            if($TARGETID!="" && $TARGETID!=$ATTOREID){
                $RECIPIENTS.="|".$TARGETID;
            }
            if($RECIPIENTS!=""){
                if( $REG_CONSISTENCY==2 || ($REG_STATUS==0 && $STATUS>0) ){
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
                    if($REG_CONSISTENCY==2)
                        $datax["ARGS"][]=array("SUBJECT" => "[$PRATICAID] Inserimento: ");
                    else
                        $datax["ARGS"][]=array("SUBJECT" => "[$PRATICAID] Completamento: ");
                    $jret=qv_sendmail($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
            }
        }

        // INVIO NOTIFICA
        _qv_attivita_notifica($maestro, $ARROWID, $ATTOREID, $TARGETID, true);
        
        // VARIABILI DI RITORNO
        $babelparams["DESCRIPTION"]=$DESCRIPTION;
        $babelparams["PROTSERIE"]=$PROTSERIE;
        $babelparams["PROTPROGR"]=$PROTPROGR;
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