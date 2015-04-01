<?php 
/****************************************************************************
* Name:            qv_pratiche_chiusura.php                                 *
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
include_once $path_applications."ryquiver/qv_stati_abbandonabile.php";
function qv_pratiche_chiusura($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
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
        $DESCRIPTION=$pratica["DESCRIPTION"];
        $STATOID=$pratica["STATOID"];
        $ATTOREID=$pratica["ATTOREID"];
        $INVIOEMAIL=intval($pratica["INVIOEMAIL"]);
        
        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<0 || $STATUS>3 ){
                $STATUS=1;
            }
        }
        else{
            $babelcode="QVERR_NOSTATUS";
            $b_params=array();
            $b_pattern="Nuovo stato non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // VERIFICO SE LO STATO E' ABBANDONABILE
        if($STATUS>0){
            $datax=array();
            $datax["PRATICAID"]=$PRATICAID;
            $jret=qv_stati_abbandonabile($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        
        // ISTRUZIONE DI CREAZIONE DI UNA ATTIVITA' DI CHIUSURA (RIAPERTURA)
        $datax=array();
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0ATTIVITA000");
        $datax["GENREID"]=qv_actualid($maestro, "0TIMEHOURS00");
        if($STATUS==0){
            $datax["DESCRIPTION"]="Riapertura pratica";
            $datax["REGISTRY"]="Riapertura pratica [$DESCRIPTION].";
            $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTAPERT");
        }
        else{
            if($STATUS==1){
                $datax["DESCRIPTION"]="Chiusura pratica";
                $datax["REGISTRY"]="Chiusura pratica [$DESCRIPTION].";
            }
            else{
                $datax["DESCRIPTION"]="Archiviazione pratica";
                $datax["REGISTRY"]="Archiviazione pratica [$DESCRIPTION].";
            }
            $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTCHIUS");
        }
        $datax["BOWID"]=$ATTOREID;
        $datax["TARGETID"]=$ATTOREID;
        $datax["IMPORTANZA"]=1;
        $datax["STATOID"]=$STATOID;
        $jret=qv_arrows_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        // PIPE
        $ARROWID=$jret["SYSID"];
        
        // AGGANCIO DELLA ATTIVITA' DI CHIUSURA ALLA PRATICA
        $datax=array();
        $datax["QUIVERID"]=$PRATICAID;
        $datax["ARROWID"]=$ARROWID;
        $jret=qv_quivers_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // ISTRUZIONE DI CHIUSURA
        $datax=array();
        $datax["SYSID"]=$PRATICAID;
        $datax["STATUS"]=$STATUS;
        $datax["STATUSTIME"]=date("YmdHis");
        $jret=qv_quivers_update($maestro, $datax);
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