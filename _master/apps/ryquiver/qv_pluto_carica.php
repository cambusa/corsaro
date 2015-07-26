<?php 
/****************************************************************************
* Name:            qv_pluto_carica.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_applications."ryquiver/pluto_developer.php";
function qv_pluto_carica($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
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
        
        // DETERMINO FLUSSOID
        if(isset($data["FLUSSOID"])){
            $FLUSSOID=$data["FLUSSOID"];
        }
        else{
            $babelcode="QVERR_FLUSSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il flusso";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO LA DATA DEL FLUSSO
        maestro_query($maestro, "SELECT AUXTIME FROM QVARROWS WHERE SYSID='$FLUSSOID'", $r);
        if(count($r)==1){
            $DATAFLUSSO=substr(qv_strtime($r[0]["AUXTIME"]), 0, 8);
        }
        else{
            $babelcode="QVERR_NOFLUSSO";
            $b_params=array();
            $b_pattern="Flusso inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
            
        // ISTANZIO UN DEVELOPER
        $DEVELOPER=new ryDeveloper();
        $DEVELOPER->contoid=$CONTOID;
        $DEVELOPER->segno=$SEGNO;
        $DEVELOPER->maestro=&$maestro;
        
        // CARICO IL FINANZIAMENTO
        $DEVELOPER->caricafin($PRATICAID);
        
        if(isset($DEVELOPER->sviluppo[$DATAFLUSSO])){
            $FLUSSO=$DEVELOPER->sviluppo[$DATAFLUSSO];
        }
        else{
            $babelcode="QVERR_NOFLUSSO";
            $b_params=array();
            $b_pattern="Data flusso [$DATAFLUSSO] inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // VARIABILI DI RITORNO
        $babelparams["DATA"]=$DATAFLUSSO;
        if($DEVELOPER->swap){
            $babelparams["_CAPITALE"]=$FLUSSO["_NOMINALE"];
            $babelparams["CAPITALE"]=$FLUSSO["NOMINALE"];
            
            $babelparams["_INTINC"]=$FLUSSO["_INTINC"];
            $babelparams["#INTINC"]=1;
            $babelparams["INTINC"]=$FLUSSO["INTINC"];
            $babelparams["_COMMINC"]=$FLUSSO["_COMMINC"];
            $babelparams["#COMMINC"]=1;
            $babelparams["COMMINC"]=$FLUSSO["COMMINC"];
            $babelparams["_TASSOINC"]=$FLUSSO["_TASSOINC"];
            $babelparams["#TASSOINC"]=1;
            $babelparams["TASSOINC"]=$FLUSSO["TASSOINC"];
            $babelparams["_SPREADINC"]=$FLUSSO["_SPREADINC"];
            $babelparams["#SPREADINC"]=1;
            $babelparams["SPREADINC"]=$FLUSSO["SPREADINC"];

            $babelparams["_INTPAG"]=$FLUSSO["_INTPAG"];
            $babelparams["#INTPAG"]=1;
            $babelparams["INTPAG"]=$FLUSSO["INTPAG"];
            $babelparams["_COMMPAG"]=$FLUSSO["_COMMPAG"];
            $babelparams["#COMMPAG"]=1;
            $babelparams["COMMPAG"]=$FLUSSO["COMMPAG"];
            $babelparams["_TASSOPAG"]=$FLUSSO["_TASSOPAG"];
            $babelparams["#TASSOPAG"]=1;
            $babelparams["TASSOPAG"]=$FLUSSO["TASSOPAG"];
            $babelparams["_SPREADPAG"]=$FLUSSO["_SPREADPAG"];
            $babelparams["#SPREADPAG"]=1;
            $babelparams["SPREADPAG"]=$FLUSSO["SPREADPAG"];
        }
        else{
            $babelparams["_CAPITALE"]=$FLUSSO["_CAPITALE"];
            $babelparams["CAPITALE"]=$FLUSSO["CAPITALE"];
            if($SEGNO>0){
                $babelparams["_INTINC"]=$FLUSSO["_INTERESSI"];
                $babelparams["#INTINC"]=1;
                $babelparams["INTINC"]=$FLUSSO["INTERESSI"];
                $babelparams["_COMMINC"]=$FLUSSO["_COMMISSIONI"];
                $babelparams["#COMMINC"]=1;
                $babelparams["COMMINC"]=$FLUSSO["COMMISSIONI"];
                $babelparams["_TASSOINC"]=$FLUSSO["_TASSO"];
                $babelparams["#TASSOINC"]=1;
                $babelparams["TASSOINC"]=$FLUSSO["TASSO"];
                $babelparams["_SPREADINC"]=$FLUSSO["_SPREAD"];
                $babelparams["#SPREADINC"]=1;
                $babelparams["SPREADINC"]=$FLUSSO["SPREAD"];
            
                $babelparams["_INTPAG"]=0;
                $babelparams["#INTPAG"]=0;
                $babelparams["INTPAG"]=0;
                $babelparams["_COMMPAG"]=0;
                $babelparams["#COMMPAG"]=0;
                $babelparams["COMMPAG"]=0;
                $babelparams["_TASSOPAG"]=0;
                $babelparams["#TASSOPAG"]=0;
                $babelparams["TASSOPAG"]=0;
                $babelparams["_SPREADPAG"]=0;
                $babelparams["#SPREADPAG"]=0;
                $babelparams["SPREADPAG"]=0;
            }
            else{
                $babelparams["_INTINC"]=0;
                $babelparams["#INTINC"]=0;
                $babelparams["INTINC"]=0;
                $babelparams["_COMMINC"]=0;
                $babelparams["#COMMINC"]=0;
                $babelparams["COMMINC"]=0;
                $babelparams["_TASSOINC"]=0;
                $babelparams["#TASSOINC"]=0;
                $babelparams["TASSOINC"]=0;
                $babelparams["_SPREADINC"]=0;
                $babelparams["#SPREADINC"]=0;
                $babelparams["SPREADINC"]=0;

                $babelparams["_INTPAG"]=$FLUSSO["_INTERESSI"];
                $babelparams["#INTPAG"]=1;
                $babelparams["INTPAG"]=$FLUSSO["INTERESSI"];
                $babelparams["_COMMPAG"]=$FLUSSO["_COMMISSIONI"];
                $babelparams["#COMMPAG"]=1;
                $babelparams["COMMPAG"]=$FLUSSO["COMMISSIONI"];
                $babelparams["_TASSOPAG"]=$FLUSSO["_TASSO"];
                $babelparams["#TASSOPAG"]=1;
                $babelparams["TASSOPAG"]=$FLUSSO["TASSO"];
                $babelparams["_SPREADPAG"]=$FLUSSO["_SPREAD"];
                $babelparams["#SPREADPAG"]=1;
                $babelparams["SPREADPAG"]=$FLUSSO["SPREAD"];
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