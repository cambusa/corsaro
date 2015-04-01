<?php 
/****************************************************************************
* Name:            qv_pratiche_audit.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_applications."ryquiver/pratiche_saldo.php";
function qv_pratiche_audit($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO LA PRATICA (SOLO LA PRATICA E' OBBLIGATORIA)
        if(isset($data["PRATICAID"])){
            $PRATICAID=$data["PRATICAID"];
        }
        else{
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO IL CONTO
        if(isset($data["CONTOID"]))
            $CONTOID=$data["CONTOID"];
        else
            $CONTOID="";

        // DETERMINO IL GENERE
        if(isset($data["GENREID"]))
            $GENREID=$data["GENREID"];
        else
            $GENREID="";
            
        if($CONTOID=="" || $GENREID==""){
            // DETERMINO IL PROCESSO
            $PROCESSOID="";
            if(isset($data["PROCESSOID"])){
                $PROCESSOID=$data["PROCESSOID"];
            }
            else{
                $sql="SELECT PROCESSOID FROM QUIVERS_PRATICHE WHERE SYSID='$PRATICAID'";
                maestro_query($maestro, $sql, $r);
                if(count($r)==1){
                    $PROCESSOID=$r[0]["PROCESSOID"];
                }
            }
            if($PROCESSOID!=""){
                // LEGGO IL PRIMO STATO DEL PROCESSO
                maestro_query($maestro, "SELECT CONTOID FROM OBJECTS_PROCSTATI WHERE PROCESSOID='$PROCESSOID' AND INIZIALE=1 ORDER BY ORDINATORE", $r);
                if(count($r)>0){
                    $CONTOID=$r[0]["CONTOID"];
                    if($CONTOID!="" && $GENREID==""){
                        // LEGGO IL CONTO PER REPERIRE GENREID
                        $sql="SELECT REFGENREID FROM QVOBJECTS WHERE SYSID='$CONTOID'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1){
                            $GENREID=$r[0]["REFGENREID"];
                        }
                    }
                }
            }
        }
        if($CONTOID!="" && $GENREID!=""){
            // AGGIORNO IL SALDO DEL QUIVER
            pratiche_saldo($maestro, $CONTOID, $GENREID, $PRATICAID);
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