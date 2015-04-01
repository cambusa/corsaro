<?php
/****************************************************************************
* Name:            appvalidatearrow.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function appvalidatearrow(
            $maestro, 
            &$data, 
            $prevdata, 
            $SYSID, 
            $TYPOLOGYID, 
            $oper, 
            $user, 
            $role, 
            &$babelcode, 
            &$failure){

    global $global_lastadmin;
    global $global_quiveruserid;

    $ret=1;
    // CONTROLLO GESTIONE MOVIMENTI RELATIVI A PRATICHE
    switch( substr($TYPOLOGYID, 0, 12) ){
    case "0MOVIMENTI00":
        $data["TRIGGERSTATOID"]=qv_actualvalue($data, $prevdata, "STATOID");
        break;
    case "0ACCREDITI00":
        if($oper<=1){
            // DESCRIZIONE, DATA INIZIO
            $TARGETID=qv_actualvalue($data, $prevdata, "TARGETID");
            $CORSOID=qv_actualvalue($data, $prevdata, "CORSOID");
            $DESCRIPTION=qv_actualvalue($data, $prevdata, "DESCRIPTION");
            $BOWTIME="[:DATE(".qv_strtime(qv_actualvalue($data, $prevdata, "BOWTIME")).")]";
            
            $where="TARGETID='$TARGETID' AND ((DESCRIPTION='$DESCRIPTION' AND BOWTIME=$BOWTIME) OR (CORSOID<>'' AND CORSOID='$CORSOID'))";
            if(!qv_uniquity($maestro, "QW_ACCREDITI", $SYSID, $where)){
                $babelcode="QVUSER_NOTUNIQUE";
                $failure="Corso già presente in anagrafica";
                $ret=0;
            }
        }
        break;
    case "0WEBCONTENTS":
        if($oper==1){
            // GESTIONE DI UPDATING
            if($global_lastadmin==0){
                if(intval($prevdata["UPDATING"])==2){   // Modifica privata
                    if($global_quiveruserid!=$prevdata["USERINSERTID"]){
                        $babelcode="QVERR_FORBIDDEN";
                        $b_params=array();
                        $b_pattern="Autorizzazioni insufficienti";
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                    }
                    break;
                }
            }
        }
        if($oper<=1){
            if(isset($data["_AUTOTAGS"])){
                if(intval($data["_AUTOTAGS"])){
                    $REGISTRY=qv_actualvalue($data, $prevdata, "REGISTRY");
                    $REGISTRY=preg_replace("/<[bh]r *\/?>/i", " ", $REGISTRY);
                    $REGISTRY=preg_replace("/<p *\/?>/i", " ", $REGISTRY);
                    $REGISTRY=html_entity_decode(strip_tags($REGISTRY));
                    @preg_match_all("/([A-Z]{4,})/i", $REGISTRY, $m);
                    if(isset($m[1]))
                        $v=$m[1];
                    else
                        $v=Array();
                    $TAGS=implode(" ", $v);
                    $data["TAG"]=substr($TAGS, 0, 1000);
                }
            }
        }
        break;
    }
    return $ret;
}
?>