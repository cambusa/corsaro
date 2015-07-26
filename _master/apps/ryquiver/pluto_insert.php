<?php 
/****************************************************************************
* Name:            pluto_insert.php                                         *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function pluto_generamov($maestro,
                         $PRATICAID,
                         $GENREID,
                         $AMOUNT,
                         $MOTIVEID,
                         $DESCRIPTION,
                         $BOWID,
                         $TARGETID,
                         $DATAVAL,
                         $STATOID,
                         $CONSISTENCY=0){
    $datax=array();
    $datax["TYPOLOGYID"]=qv_actualid($maestro, "0MOVIMENTI00");
    $datax["GENREID"]=$GENREID;
    $datax["AMOUNT"]=$AMOUNT;
    $datax["MOTIVEID"]=$MOTIVEID;
    $datax["DESCRIPTION"]=$DESCRIPTION;
    $datax["BOWID"]=$BOWID;
    $datax["TARGETID"]=$TARGETID;
    $datax["BOWTIME"]=$DATAVAL;
    $datax["TARGETTIME"]=$DATAVAL;
    $datax["AUXTIME"]=$DATAVAL;
    $datax["CONSISTENCY"]=$CONSISTENCY;
    $datax["STATOID"]=$STATOID;
    $datax["PROVIDER"]="PLUTO";
    $datax["PARCEL"]=$PRATICAID;
    if($CONSISTENCY<=1)
        $datax["QUERYSIGNUM"]=1;
    else
        $datax["QUERYSIGNUM"]=0;    // PER NON SOMMARE IL NOMINALE
    $jret=qv_arrows_insert($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $ARROWID=$jret["SYSID"];
    
    // AGGANCIO DELLA FRECCIA AL QUIVER
    $datax=array();
    $datax["QUIVERID"]=$PRATICAID;
    $datax["ARROWID"]=$ARROWID;
    $jret=qv_quivers_add($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $j=array();
    $j["success"]=1;
    $j["code"]="";
    $j["params"]="";
    $j["message"]="";
    $j["SYSID"]=$ARROWID;
    return $j; //ritorno standard
}
function pluto_generaevento($maestro,
                            $PRATICAID,
                            $GENREID,
                            $AMOUNT,
                            $MOTIVEID,
                            $DESCRIPTION,
                            $BOWID,
                            $TARGETID,
                            $DATAVAL,
                            $STATOID,
                            $DIVIDENDO,
                            $DIVISORE){
    $datax=array();
    $datax["TYPOLOGYID"]=qv_actualid($maestro, "0FINEVENTI00");
    $datax["GENREID"]=$GENREID;
    $datax["AMOUNT"]=$AMOUNT;
    $datax["MOTIVEID"]=$MOTIVEID;
    $datax["DESCRIPTION"]=$DESCRIPTION;
    $datax["BOWID"]=$BOWID;
    $datax["TARGETID"]=$TARGETID;
    $datax["BOWTIME"]=$DATAVAL;
    $datax["TARGETTIME"]=$DATAVAL;
    $datax["AUXTIME"]=$DATAVAL;
    $datax["STATOID"]=$STATOID;
    $datax["DIVIDENDO"]=$DIVIDENDO;
    $datax["DIVISORE"]=$DIVISORE;
    $datax["SCAGLIONE"]=0;
    $datax["PROVIDER"]="PLUTO";
    $datax["PARCEL"]=$PRATICAID;
    $jret=qv_arrows_insert($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $ARROWID=$jret["SYSID"];
    
    // AGGANCIO DELLA FRECCIA AL QUIVER
    $datax=array();
    $datax["QUIVERID"]=$PRATICAID;
    $datax["ARROWID"]=$ARROWID;
    $jret=qv_quivers_add($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $j=array();
    $j["success"]=1;
    $j["code"]="";
    $j["params"]="";
    $j["message"]="";
    $j["SYSID"]=$ARROWID;
    return $j; //ritorno standard
}
?>