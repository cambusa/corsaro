<?php 
/****************************************************************************
* Name:            qv_pluto_preview.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."rygeneral/datetime.php";
include_once $path_cambusa."rygeneral/financial.php";
include_once $path_applications."ryquiver/pluto_developer.php";
include_once $path_applications."ryquiver/pluto_preview.php";
function qv_pluto_preview($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $PREVIEW="";
        
        // LEGGO IL FINANZIAMENTO
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "CONTOID,MOREDATA");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il finanziamento";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $CONTOID=$pratica["CONTOID"];
        $PARAMETRI=json_decode($pratica["MOREDATA"], true);

        if(isset($PARAMETRI["_SEGNO"]))
            $SEGNO=intval($PARAMETRI["_SEGNO"]);
        else
            $SEGNO=1;
            
        // ISTANZIO UN DEVELOPER
        $DEVELOPER=new ryDeveloper();
        $DEVELOPER->contoid=$CONTOID;
        $DEVELOPER->segno=$SEGNO;
        $DEVELOPER->maestro=&$maestro;
        
        // CARICO IL FINANZIAMENTO
        $DEVELOPER->caricafin($PRATICAID);
        
        // PREDISPONGO L'ANTEPRIMA
        $PREVIEW=pluto_preview($DEVELOPER);
        
        // VARIABILI DI RITORNO
        $babelparams["PREVIEW"]=$PREVIEW;
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