<?php 
/****************************************************************************
* Name:            qv_pluto_ricalcola.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."rygeneral/datetime.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_applications."ryquiver/pluto_developer.php";
function qv_pluto_ricalcola($maestro, $data){
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
        
        if(isset($PARAMETRI["_GENREID"]))
            $GENREID=$PARAMETRI["_GENREID"];
        else
            $GENREID=qv_actualid($maestro, "0MONEYEURO00");;

        if(isset($PARAMETRI["_SEGNO"]))
            $SEGNO=intval($PARAMETRI["_SEGNO"]);
        else
            $SEGNO=1;
        
        if(isset($PARAMETRI["_DIVIDENDO"]))
            $DIVIDENDO=intval($PARAMETRI["_DIVIDENDO"]);
        else
            $DIVIDENDO=365;
            
        if(isset($PARAMETRI["_DIVISORE"]))
            $DIVISORE=intval($PARAMETRI["_DIVISORE"]);
        else
            $DIVISORE=365;
            
        if(isset($PARAMETRI["_DVDINC"]))
            $DVDINC=intval($PARAMETRI["_DVDINC"]);
        else
            $DVDINC=$DIVIDENDO;
            
        if(isset($PARAMETRI["_DVSINC"]))
            $DVSINC=intval($PARAMETRI["_DVSINC"]);
        else
            $DVSINC=$DIVISORE;

        if(isset($PARAMETRI["_DVDPAG"]))
            $DVDPAG=intval($PARAMETRI["_DVDPAG"]);
        else
            $DVDPAG=$DIVIDENDO;
            
        if(isset($PARAMETRI["_DVSPAG"]))
            $DVSPAG=intval($PARAMETRI["_DVSPAG"]);
        else
            $DVSPAG=$DIVISORE;

        // DETERMINO FLUSSOID
        if(isset($data["FLUSSOID"]))
            $FLUSSOID=$data["FLUSSOID"];
        else
            $FLUSSOID="";
        if($FLUSSOID==""){
            $babelcode="QVERR_FLUSSO";
            $b_params=array();
            $b_pattern="Flusso non specificato";
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
        $DEVELOPER->genreid=$GENREID;
        $DEVELOPER->segno=$SEGNO;
        $DEVELOPER->dividendo=$DIVIDENDO;
        $DEVELOPER->divisore=$DIVISORE;
        $DEVELOPER->dvdinc=$DVDINC;
        $DEVELOPER->dvsinc=$DVSINC;
        $DEVELOPER->dvdpag=$DVDPAG;
        $DEVELOPER->dvspag=$DVSPAG;
        $DEVELOPER->maestro=&$maestro;
        
        // CARICO IL FINANZIAMENTO
        $DEVELOPER->caricafin($PRATICAID);
        
        foreach($DEVELOPER->sviluppo as $DATA => &$FLUSSO){
            if($DATA>=$DATAFLUSSO){
                if($DEVELOPER->swap){
                    if(isset($FLUSSO["@INTINC"])){
                        $FLUSSO["INTINC"]=0;
                    }
                    if(isset($FLUSSO["@INTPAG"])){
                        $FLUSSO["INTPAG"]=0;
                    }
                }
                else{
                    if(isset($FLUSSO["@INTERESSI"])){
                        $FLUSSO["INTERESSI"]=0;
                    }
                }
            }
        }
        
        $DEVELOPER->calcolainteressi();

        foreach($DEVELOPER->sviluppo as $DATA => &$FLUSSO){
            if($DATA>=$DATAFLUSSO){
                if($DEVELOPER->swap){
                    if($FLUSSO["@INTINC"]!=""){
                        $datax=array();
                        $datax["SYSID"]=$FLUSSO["@INTINC"];
                        $datax["AMOUNT"]=$FLUSSO["INTINC"];
                        $jret=qv_arrows_update($maestro, $datax);
                        unset($datax);
                        if(!$jret["success"]){
                            return $jret;
                        }
                    }
                    if($FLUSSO["@INTPAG"]!=""){
                        $datax=array();
                        $datax["SYSID"]=$FLUSSO["@INTPAG"];
                        $datax["AMOUNT"]=$FLUSSO["INTPAG"];
                        $jret=qv_arrows_update($maestro, $datax);
                        unset($datax);
                        if(!$jret["success"]){
                            return $jret;
                        }
                    }
                }
                else{
                    if($FLUSSO["@INTERESSI"]!=""){
                        $datax=array();
                        $datax["SYSID"]=$FLUSSO["@INTERESSI"];
                        $datax["AMOUNT"]=$FLUSSO["INTERESSI"];
                        $jret=qv_arrows_update($maestro, $datax);
                        unset($datax);
                        if(!$jret["success"]){
                            return $jret;
                        }
                    }
                }
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