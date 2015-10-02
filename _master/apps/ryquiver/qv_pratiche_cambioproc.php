<?php 
/****************************************************************************
* Name:            qv_pratiche_cambioproc.php                               *
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
include_once $path_cambusa."ryquiver/qv_sendmail.php";
include_once $path_cambusa."ryquiver/qv_messages_send.php";
include_once $path_applications."ryquiver/qv_pratiche_auto.php";
include_once $path_applications."ryquiver/stati_ingresso.php";
include_once $path_applications."ryquiver/attivita_notifiche.php";
function qv_pratiche_cambioproc($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO LA PRATICA
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHEJOIN", "PRATICAID", "", $PRATICAID, "*");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $DESCRIPTION=$pratica["DESCRIPTION"];
        $STATOID=$pratica["STATOID"];
        $ATTOREID=$pratica["ATTOREID"];
        $INVIOEMAIL=intval($pratica["INVIOEMAIL"]);
        $REFERENCE=$pratica["REFERENCE"];
        
        // LEGGO IL PROCESSO
        $processo=qv_solverecord($maestro, $data, "QW_PROCESSI", "PROCESSOID", "", $PROCESSOID, "*");
        if($PROCESSOID==""){
            $babelcode="QVERR_PROCESSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $PROTSERIE=$processo["PROTSERIE"];

        // DETERMINO IL PRIMO STATO VALIDO DEL PROCESSO
        $sql=buildfirst($maestro, $PROCESSOID, false);
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $NUOVOSTATOID=$r[0]["STATOID"];
            $NUOVOATTOREID=$r[0]["ATTOREID"];
        }
        else{
            $babelcode="QVERR_NOPROCSTATO";
            $b_params=array();
            $b_pattern="Non trovato uno stato valido per il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // ISTRUZIONE DI CREAZIONE DI UNA ATTIVITA' DI CAMBIO STATO
        $datax=array();
        $datax["DESCRIPTION"]="Cambio di processo";
        $datax["REGISTRY"]="Assegnamento pratica [$DESCRIPTION]";
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0ATTIVITA000");
        $datax["GENREID"]=qv_actualid($maestro, "0TIMEHOURS00");
        $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTTRANS");
        $datax["BOWID"]=$ATTOREID;
        $datax["TARGETID"]=$NUOVOATTOREID;
        $datax["IMPORTANZA"]=1;
        $datax["STATOID"]=$NUOVOSTATOID;
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        // PIPE
        $ARROWID=$jret["SYSID"];
        
        // AGGANCIO DELLA ATTIVITA' DI CAMBIO STATO ALLA PRATICA
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$ARROWID;
        $jret=qv_quivers_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // ISTRUZIONE DI CAMBIO PROCESSO
        $datax=array();
        $datax["SYSID"]=$PRATICAID;
        $datax["PROCESSOID"]=$PROCESSOID;
        $datax["STATOID"]=$NUOVOSTATOID;
        $datax["STATUSTIME"]=date("YmdHis");
        
        // GESTIONE PROTOCOLLAZIONE
        if($REFERENCE==""){
            $PROTPROGR=genera_protocollo($maestro, "QW_PRATICHE", $PROTSERIE);
            $datax["PROTSERIE"]=$PROTSERIE;
            $datax["PROTPROGR"]=$PROTPROGR;
            if($PROTPROGR>0){
                $datax["REFERENCE"]=$PROTSERIE.$PROTPROGR;
            }
        }
        
        $jret=qv_quivers_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }

        // CREAZIONE DELLE ATTIVITA' AUTOMATICHE
        $datax=array();
        $datax["PRATICAID"]=$PRATICAID;
        $datax["STATOID"]=$NUOVOSTATOID;
        $jret=qv_pratiche_auto($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // INVIO EMAIL AL NUOVO PROPRIETARIO
        if($INVIOEMAIL){
            if($ATTOREID!=$NUOVOATTOREID){
                $datax=array();
                $datax["TABLE"]="QVARROWS";
                $datax["SYSID"]=$ARROWID;
                $datax["MAILTABLE"]="QW_ATTORI";
                $datax["SENDERID"]=$ATTOREID;
                $datax["RECIPIENTS"]=$NUOVOATTOREID;
                $jret=qv_sendmail($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    // Non lo considero errore bloccante
                    //return $jret;
                }
            }
        }
        
        // INVIO NOTIFICA
        _qv_attivita_notifica($maestro, $ARROWID, $ATTOREID, $NUOVOATTOREID, false);
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