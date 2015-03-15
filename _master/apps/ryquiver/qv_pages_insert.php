<?php 
/****************************************************************************
* Name:            qv_pages_insert.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
function qv_pages_insert($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        if(isset($data["SITEID"]))
            $SITEID=$data["SITEID"];
        else
            $SITEID="";
        
        // ISTRUZIONE DI CREAZIONE DI UN NUOVO CONTENUTO
        $datax=array();
        $datax["DESCRIPTION"]="(nuovo contenuto)";
        $datax["TYPOLOGYID"]=qv_actualid($maestro, "0WEBCONTENTS");
        $datax["GENREID"]=qv_actualid($maestro, "0TIMEHOURS00");
        $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTANNOT");
        $datax["CONSISTENCY"]="0";
        $datax["STATUS"]="0";
        $datax["SCOPE"]="2";
        $datax["SETFRAMES"]=qv_createsysid($maestro);
        $datax["SETRELATED"]=qv_createsysid($maestro);
        $datax["SITEID"]=$SITEID;
        $datax["CONTENTTYPE"]="wysiwyg";
        $datax["MARQUEETYPE"]="20";
        $datax["ITEMDETAILS"]="1";
        $datax["NAVHOME"]="1";
        $datax["NAVPRIMARY"]="1";
        $datax["NAVPARENTS"]="1";
        $datax["NAVSIBLINGS"]="1";
        $datax["NAVRELATED"]="1";
        $datax["NAVTOOL"]="1";
        $datax["SEARCHITEMS"]="100";
        $datax["REFERENCE"]="fmit";
        $datax["SORTER"]=0;
        $jret=qv_arrows_insert($maestro, $datax);
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