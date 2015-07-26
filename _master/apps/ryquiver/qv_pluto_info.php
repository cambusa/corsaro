<?php 
/****************************************************************************
* Name:            qv_pluto_info.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_applications."ryquiver/pluto_developer.php";
function qv_pluto_info($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL FINANZIAMENTO
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "DESCRIPTION,MOREDATA");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il finanziamento";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $DESCRIPTION=$pratica["DESCRIPTION"];
        $PARAMETRI=json_decode($pratica["MOREDATA"], true);
        
        if(isset($PARAMETRI["_SEGNO"]))
            $SEGNO=intval($PARAMETRI["_SEGNO"]);
        else
            $SEGNO=1;
        
        // DETERMINO SE E' UNO SWAP
        maestro_query($maestro, "SELECT SYSID FROM QW_MOVIMENTI WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID') AND CONSISTENCY=2", $r);
        if(count($r)>0)
            $SWAP=1;
        else
            $SWAP=0;
        
        // VARIABILI DI RITORNO
        $babelparams["DESCRIPTION"]=$DESCRIPTION;
        $babelparams["SWAP"]=$SWAP;
        $babelparams["SEGNO"]=$SEGNO;
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