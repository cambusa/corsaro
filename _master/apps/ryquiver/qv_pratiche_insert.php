<?php 
/****************************************************************************
* Name:            qv_pratiche_insert.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_quivers_insert.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."ryquiver/qv_sendmail.php";
include_once $path_applications."ryquiver/qv_pratiche_auto.php";
include_once $path_applications."ryquiver/pratiche_date.php";
include_once $path_applications."ryquiver/stati_ingresso.php";
include_once $path_applications."ryquiver/protocollo_nuovo.php";
function qv_pratiche_insert($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // DETERMINO PROCESSO
        $processo=qv_solverecord($maestro, $data, "QW_PROCESSI", "PROCESSOID", "", $PROCESSOID, "DESCRIPTION,GANTT,INVIOEMAIL,PROTSERIE");
        if($PROCESSOID==""){
            $babelcode="QVERR_PROCESSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $PROCESSODESCR=$processo["DESCRIPTION"];
        $GANTT=intval($processo["GANTT"]);
        $INVIOEMAIL=intval($processo["INVIOEMAIL"]);
        $PROTSERIE=$processo["PROTSERIE"];
        $PROTPROGR=genera_protocollo($maestro, "QW_PRATICHE", $PROTSERIE);
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"]))
            $PRATICADESCR=$data["DESCRIPTION"];
        else
            $PRATICADESCR="(nuova pratica)";
        
        // DETERMINO RICHIEDENTEID
        $richiedente=qv_solverecord($maestro, $data, "QW_ATTORI", "RICHIEDENTEID", "", $RICHIEDENTEID, "DESCRIPTION");
        if($RICHIEDENTEID!=""){
            $RICHIEDENTEDESCR=$richiedente["DESCRIPTION"];
            $PRATICADESCR=str_replace("[!RICHIEDENTE]", $RICHIEDENTEDESCR, $PRATICADESCR);
        }
        
        // DETERMINO IL PRIMO STATO VALIDO DEL PROCESSO
        $sql=buildfirst($maestro, $PROCESSOID, true);
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $STATOID=$r[0]["STATOID"];
            $ATTOREID=$r[0]["ATTOREID"];
        }
        else{
            $babelcode="QVERR_NOPROCSTATO";
            $b_params=array();
            $b_pattern="Non trovato uno stato valido per il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // PERFEZIONO RICHIEDENTEID
        if($RICHIEDENTEID==""){
            $RICHIEDENTEID=$ATTOREID;
            $RICHIEDENTEDESCR="(proprietario)";
        }
        
        // DETERMINAZIONE INIZIO E FINE
        $DATAINIZIO=date("Ymd");
        $DATAFINE=pratiche_sommagiorni($DATAINIZIO, 7, 1);
        
        // INSERIMENTO QUIVER
        $datax=array();
        $datax["DESCRIPTION"]=$PRATICADESCR;
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0PRATICHE000");
        $datax["PROCESSOID"]=$PROCESSOID;
        $datax["STATOID"]=$STATOID;
        $datax["DATAINIZIO"]=$DATAINIZIO;
        $datax["DATAFINE"]=$DATAFINE;
        $datax["RICHIEDENTEID"]=$RICHIEDENTEID;
        $datax["GANTT"]=$GANTT;
        $datax["INVIOEMAIL"]=$INVIOEMAIL;
        $datax["PROTSERIE"]=$PROTSERIE;
        $datax["PROTPROGR"]=$PROTPROGR;
        if($PROTPROGR>0){
            $datax["REFERENCE"]=$PROTSERIE.$PROTPROGR;
        }
        $jret=qv_quivers_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $PRATICAID=$jret["SYSID"];

        // ATTIVITA' INGRESSO
        $datax=array();
        $datax["DESCRIPTION"]="Apertura pratica";
        $datax["REGISTRY"]="Inserita una nuova pratica <b>$PROCESSODESCR</b>.<br><br>$RICHIEDENTEDESCR";
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0ATTIVITA000");
        $datax["GENREID"]=qv_actualid($maestro, "0TIMEHOURS00");
        $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTAPERT");
        $datax["BOWID"]=$RICHIEDENTEID;
        $datax["BOWTIME"]=date("Ymd");
        $datax["TARGETID"]=$ATTOREID;
        $datax["TARGETTIME"]=date("Ymd");
        $datax["AUXTIME"]=date("YmdHis");
        $datax["STATOID"]=$STATOID;
        $datax["IMPORTANZA"]=1;
        $datax["CONSISTENCY"]=1;
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $APERTURAID=$jret["SYSID"];
        
        // AGGANCIO DELL'APERTURA AL QUIVER
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$APERTURAID;
        $jret=qv_quivers_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
    
        // CREAZIONE DELLE ATTIVITA' AUTOMATICHE
        $datax=array();
        $datax["PRATICAID"]=$PRATICAID;
        $datax["STATOID"]=$STATOID;
        $jret=qv_pratiche_auto($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        if($INVIOEMAIL){
            if($RICHIEDENTEID!="" && $RICHIEDENTEID!=$ATTOREID){
                // INVIO EMAIL AL PROPRIETARIO
                $datax=array();
                $datax["TABLE"]="QVARROWS";
                $datax["SYSID"]=$APERTURAID;
                $datax["MAILTABLE"]="QW_ATTORI";
                $datax["SENDERID"]=$RICHIEDENTEID;
                $datax["RECIPIENTS"]=$ATTOREID;
                $jret=qv_sendmail($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }
            }
        }

        // VARIABILI DI RITORNO
        $babelparams["PRATICAID"]=$PRATICAID;
        $babelparams["STATOID"]=$STATOID;
        $babelparams["ATTOREID"]=$ATTOREID;
        $babelparams["GANTT"]=$GANTT;
        $babelparams["INVIOEMAIL"]=$INVIOEMAIL;
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