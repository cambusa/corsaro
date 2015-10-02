<?php 
/****************************************************************************
* Name:            qv_pratiche_trans.php                                    *
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
include_once $path_applications."ryquiver/qv_stati_abbandonabile.php";
include_once $path_applications."ryquiver/qv_pratiche_auto.php";
include_once $path_applications."ryquiver/attivita_notifiche.php";
function qv_pratiche_trans($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO LA PRATICA
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "*");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $DESCRIPTION=$pratica["DESCRIPTION"];
        $STATOID=$pratica["STATOID"];
        $INVIOEMAIL=intval($pratica["INVIOEMAIL"]);
        
        // LEGGO LA TRANSIZIONE
        $trans=qv_solverecord($maestro, $data, "QW_TRANSIZIONIJOIN", "TRANSID", "", $TRANSID, "*");
        if($TRANSID==""){
            $babelcode="QVERR_TRANSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la transizione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $NUOVOSTATOID=$trans["TARGETID"];
        $NUOVOSTATODESCR=$trans["TARGETDESCR"];
        $TRANSATTOREBOW=$trans["ATTOREBOWID"];
        $TRANSATTORETARGET=$trans["ATTORETARGETID"];
        $SVINCOLANTE=intval($trans["SVINCOLANTE"]);
        
        if($TRANSATTORETARGET==""){
            $babelcode="QVERR_TRANSTARGET";
            $b_params=array();
            $b_pattern="Il nuovo stato non ha un proprietario";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if($SVINCOLANTE==0){
            // VERIFICO SE LO STATO E' ABBANDONABILE
            $datax=array();
            $datax["PRATICAID"]=$PRATICAID;
            $jret=qv_stati_abbandonabile($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        
        // ISTRUZIONE DI CREAZIONE DI UNA ATTIVITA' DI CAMBIO STATO
        $datax=array();
        $datax["DESCRIPTION"]="Transizione di stato";
        $datax["REGISTRY"]="Assegnamento pratica [$DESCRIPTION]";
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0ATTIVITA000");
        $datax["GENREID"]=qv_actualid($maestro, "0TIMEHOURS00");
        $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTTRANS");
        $datax["BOWID"]=$TRANSATTOREBOW;
        $datax["TARGETID"]=$TRANSATTORETARGET;
        $datax["IMPORTANZA"]=1;
        $datax["STATOID"]=$NUOVOSTATOID;
        $datax["TRANSID"]=$TRANSID;
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
        
        // ISTRUZIONE DI CAMBIO STATO
        $datax=array();
        $datax["SYSID"]=$PRATICAID;
        $datax["STATOID"]=$NUOVOSTATOID;
        $datax["STATUSTIME"]=date("YmdHis");
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
            if($TRANSATTOREBOW!=$TRANSATTORETARGET){
                $datax=array();
                $datax["TABLE"]="QVARROWS";
                $datax["SYSID"]=$ARROWID;
                $datax["MAILTABLE"]="QW_ATTORI";
                $datax["SENDERID"]=$TRANSATTOREBOW;
                $datax["RECIPIENTS"]=$TRANSATTORETARGET;
                $jret=qv_sendmail($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    // Non lo considero errore bloccante
                    //return $jret;
                }
            }
        }

        // INVIO NOTIFICA
        _qv_attivita_notifica($maestro, $ARROWID, $TRANSATTOREBOW, $TRANSATTORETARGET, false);

        // VARIABILI DI RITORNO
        $babelparams["NUOVOSTATOID"]=$NUOVOSTATOID;
        $babelparams["NUOVOSTATODESCR"]=$NUOVOSTATODESCR;
        $babelparams["ATTOREBOWID"]=$TRANSATTOREBOW;
        $babelparams["ATTORETARGETID"]=$TRANSATTORETARGET;
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