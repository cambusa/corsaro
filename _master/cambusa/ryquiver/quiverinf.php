<?php 
/****************************************************************************
* Name:            quiverinf.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

function qv_infosession($maestro){
    global $global_lastuserid,
           $global_lastusername,
           $global_lastadmin,
           $global_lastemail,
           $global_lastroleid,
           $global_lastrolename,
           $global_quiveruserid,
           $global_quiverroleid;
    
    if($global_quiveruserid==""){
        maestro_query($maestro, "SELECT * FROM QVUSERS WHERE EGOID='$global_lastuserid' AND ARCHIVED=0", $r);
        if(count($r)==1){
            $global_quiveruserid=$r[0]["SYSID"];
            $qvname=$r[0]["USERNAME"];
            $qvadmin=intval($r[0]["ADMINISTRATOR"]);
            $qvemail=$r[0]["EMAIL"];
            if($qvname!=$global_lastusername || $qvadmin!=$global_lastadmin || $qvemail!=$global_lastemail){
                // ALLINEO LA TABELLA INTERNA CON IL DATO EGO MODIFICATO
                maestro_execute($maestro, "UPDATE QVUSERS SET USERNAME='".ryqEscapize($global_lastusername)."',ADMINISTRATOR=".$global_lastadmin.",EMAIL='".ryqEscapize($global_lastemail)."' WHERE SYSID='$global_quiveruserid'");
            }
        }
        else{
            // ARCHIVIO TUTTI I RECORD CHE HANNO LO STESSO USERNAME PER CONSIDERARLI OBSOLETI
            maestro_execute($maestro, "UPDATE QVUSERS SET ARCHIVED=1 WHERE [:UPPER(USERNAME)]='" . strtoupper( ryqEscapize($global_lastusername) ) . "'");
            // INSERISCO UNA COPIA DEI DATI EGO NELLA TABELLA INTERNA
            $global_quiveruserid=qv_createsysid($maestro);
            maestro_execute($maestro, "INSERT INTO QVUSERS(SYSID,EGOID,USERNAME,ADMINISTRATOR,EMAIL,ARCHIVED) VALUES('$global_quiveruserid','$global_lastuserid','".ryqEscapize($global_lastusername)."',".$global_lastadmin.",'".ryqEscapize($global_lastemail)."',0)");
        }

        maestro_query($maestro, "SELECT * FROM QVROLES WHERE EGOID='$global_lastroleid' AND ARCHIVED=0", $r);
        if(count($r)==1){
            $global_quiverroleid=$r[0]["SYSID"];
            $qvname=$r[0]["ROLENAME"];
            if($qvname!=$global_lastrolename){
                // ALLINEO LA TABELLA INTERNA CON IL DATO EGO MODIFICATO
                maestro_execute($maestro, "UPDATE QVROLES SET ROLENAME='" . ryqEscapize($global_lastrolename) . "' WHERE SYSID='$global_quiverroleid'");
            }
        }
        else{
            // ARCHIVIO TUTTI I RECORD CHE HANNO LO STESSO ROLENAME PER CONSIDERARLI OBSOLETI
            maestro_execute($maestro, "UPDATE QVROLES SET ARCHIVED=1 WHERE [:UPPER(ROLENAME)]='".strtoupper( ryqEscapize($global_lastrolename) )."'");
            // INSERISCO UNA COPIA DEI DATI EGO NELLA TABELLA INTERNA
            $global_quiverroleid=qv_createsysid($maestro);
            maestro_execute($maestro, "INSERT INTO QVROLES(SYSID,EGOID,ROLENAME,ARCHIVED) VALUES('$global_quiverroleid','$global_lastroleid','" . ryqEscapize($global_lastrolename) . "',0)");
        }
    }
}

function qv_solveuser($maestro, $data, $id, $ego, $name, &$SYSID, &$USERNAME, $raise=true){
    global $babelcode, $babelparams;
    $SYSID="";
    $USERNAME="";
    if(isset($data[$id])){
        // CERCO PER SYSID
        $SYSID=ryqEscapize($data[$id]);
        maestro_query($maestro, "SELECT USERNAME FROM QVUSERS WHERE SYSID='$SYSID'", $r);
        if(count($r)==1){
            $USERNAME=$r[0]["USERNAME"];
        }
        elseif($raise){
            $babelcode="QVERR_NOREF";
            $b_params=array("SYSID" => $SYSID, "table" => "QVUSERS");
            $b_pattern="Riferimento non trovato in [{2}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        else{
            writelog("Utente ID [$SYSID] non trovato.\n---> ".serialize($data));
        }
    }
    elseif(isset($data[$ego])){
        // CERCO PER IDENTIFICATORE EGO
        $EGOID=ryqEscapize($data[$ego]);
        maestro_query($maestro, "SELECT SYSID,USERNAME FROM QVUSERS WHERE EGOID='$EGOID' AND ARCHIVED=0", $r);
        if(count($r)==1){
            $SYSID=$r[0]["SYSID"];
            $USERNAME=$r[0]["USERNAME"];
        }
        elseif($raise){
            $babelcode="QVERR_NOEGO";
            $b_params=array("EGOID" => $EGOID, "table" => "QVUSERS");
            $b_pattern="Nome [{1}] non trovato in [{2}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        else{
            writelog("Utente EGO [$EGOID] non trovato.\n---> ".serialize($data));
        }
    }
    elseif(isset($data[$name])){
        $USERNAME=ryqEscapize($data[$name]);
        maestro_query($maestro, "SELECT SYSID FROM QVUSERS WHERE [:UPPER(USERNAME)]='".strtoupper($USERNAME)."' AND ARCHIVED=0", $r);
        if(count($r)==1){
            $SYSID=$r[0]["SYSID"];
        }
        elseif($raise){
            $babelcode="QVERR_NONAME";
            $b_params=array("USERNAME" => $USERNAME, "table" => "QVUSERS");
            $b_pattern="Nome [{1}] non trovato in [{2}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        else{
            writelog("Utente [$USERNAME] non trovato.\n---> ".serialize($data));
        }
    }
}

?>