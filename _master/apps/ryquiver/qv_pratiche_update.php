<?php 
/****************************************************************************
* Name:            qv_pratiche_update.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_update.php";
function qv_pratiche_update($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "SYSID", "", $PRATICAID, "MOREDATA");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // GESTIONE DATI AGGIUNTIVI
        if(isset($data["DATIAGGIUNTIVI"])){
            $PARAMETRI=json_decode($pratica["MOREDATA"], true);
            foreach($data["DATIAGGIUNTIVI"] as $key => $value){
                $PARAMETRI[strtoupper($key)]=$value;
            }
            $MOREDATA=json_encode($PARAMETRI);
        }
        else{
            $MOREDATA=false;
        }

        // AGGIORNO LA PRATICA
        $datax=$data;
        if($MOREDATA!==false){
            $datax["MOREDATA"]=$MOREDATA;
        }
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