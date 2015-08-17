<?php 
/****************************************************************************
* Name:            qv_bandi_insert.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_objects_insert.php";
function qv_bandi_insert($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // ISTRUZIONE DI CREAZIONE DI UN NUOVO CONTENUTO
        $datax=array();
        $datax["DESCRIPTION"]="(nuovo bando)";
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0BANDI000000");
        $datax["SETFORMAGIURIDICA"]=qv_createsysid($maestro);
        $datax["SETCRITERIESCLUSIVI"]=qv_createsysid($maestro);
        $datax["SETDIMENSIONE"]=qv_createsysid($maestro);
        $datax["SETOGGETTO"]=qv_createsysid($maestro);
        $datax["SETSPESEAMMISSIBILI"]=qv_createsysid($maestro);
        $datax["SETTIPICONTRIBUTO"]=qv_createsysid($maestro);
        $jret=qv_objects_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $SYSID=$jret["SYSID"];
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