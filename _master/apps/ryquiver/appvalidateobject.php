<?php
/****************************************************************************
* Name:            appvalidateobject.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function appvalidateobject(
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
    $ret=1;
    // CONTROLLO SU CANCELLAZIONE DI UNO STATO DI PROCESSO
    switch( substr($TYPOLOGYID, 0, 12) ){
    case "0PROCSTATI00":
        if($oper>=2){
            $where="STATOID='$SYSID' AND STATUS=0";
            if(qv_recordexists($maestro, "QW_PRATICHE", $where)){
                $babelcode="QVUSER_STATOINUSO";
                $failure="Stato in uso da pratiche aperte";
                $ret=0;
            }
        }
        break;
    case "0CONTI000000":
        if($oper==1){
            // DIVISA OBBLIGATORIA
            $GENREID=qv_actualvalue($data, $prevdata, "REFGENREID");
            if($GENREID==""){
                $babelcode="QVUSER_NODIVISA";
                $failure="Divisa del conto non specificata";
                $ret=0;
            }
        }
        if($oper<=1){
            // DESCRIZIONE, NUMERO CONTO UNIVOCI
            $DESCRIPTION=qv_actualvalue($data, $prevdata, "DESCRIPTION");
            $NUMCONTO=qv_actualvalue($data, $prevdata, "NUMCONTO");
            
            $fields=array();
            if(substr($DESCRIPTION,0,1)!="("){
                $fields[]="DESCRIPTION='$DESCRIPTION'";
            }
            if($NUMCONTO!=""){
                $fields[]="NUMCONTO='$NUMCONTO'";
            }
            if(count($fields)>0){
                $where=implode(" OR ", $fields);
                if(!qv_uniquity($maestro, "QW_CONTI", $SYSID, $where)){
                    $babelcode="QVUSER_NOTUNIQUE";
                    $failure="Descrizione, numero o CO.GE. già presente in anagrafica";
                    $ret=0;
                }
            }
        }
        break;
    case "0PERSONE0000":
        if($oper<=1){
            // ATTRIBUZIONE DEL GENERE PREDEFINITO IN INSERIMENTO
            if($oper==0){
                if(!isset($data["REFGENREID"])){
                    $data["REFGENREID"]=qv_actualid($maestro, "0CREDITS0000");
                }
            }
            // DESCRIZIONE, CODICE FISCALE
            $DESCRIPTION=qv_actualvalue($data, $prevdata, "DESCRIPTION");
            $CODFISC=qv_actualvalue($data, $prevdata, "CODFISC");
            
            $fields=array();
            if(substr($DESCRIPTION,0,1)!="("){
                $fields[]="DESCRIPTION='$DESCRIPTION'";
            }
            if($CODFISC!=""){
                $fields[]="CODFISC='$CODFISC'";
            }
            if(count($fields)>0){
                $where=implode(" OR ", $fields);
                if(!qv_uniquity($maestro, "QW_PERSONE", $SYSID, $where)){
                    $action="RAISE";
                    if(isset($data["CONFLICT"])){
                        $action=$data["CONFLICT"];
                    }
                    if($action=="RAISE"){
                        $babelcode="QVUSER_NOTUNIQUE";
                        $failure="Descrizione o codice fiscale già presente in anagrafica";
                        $ret=0;
                    }
                    elseif($action=="SKIP"){
                        $ret=2;
                    }
                }
            }
        }
        break;
    case "0PROPRIETA00":
        if($oper<=1){
            // DESCRIZIONE, PIVA
            $DESCRIPTION=qv_actualvalue($data, $prevdata, "DESCRIPTION");
            $PIVA=qv_actualvalue($data, $prevdata, "PIVA");
            
            $fields=array();
            if(substr($DESCRIPTION,0,1)!="("){
                $fields[]="DESCRIPTION='$DESCRIPTION'";
            }
            if($PIVA!=""){
                $fields[]="PIVA='$PIVA'";
            }
            if(count($fields)>0){
                $where=implode(" OR ", $fields);
                if(!qv_uniquity($maestro, "QW_PROPRIETA", $SYSID, $where)){
                    $babelcode="QVUSER_NOTUNIQUE";
                    $failure="Descrizione o P.IVA già presente in anagrafica";
                    $ret=0;
                }
            }
        }
        break;
    case "0AZIENDE0000":
        if($oper<=1){
            // DESCRIZIONE, PIVA
            $DESCRIPTION=qv_actualvalue($data, $prevdata, "DESCRIPTION");
            $PIVA=qv_actualvalue($data, $prevdata, "PIVA");
            
            $fields=array();
            if(substr($DESCRIPTION,0,1)!="("){
                $fields[]="DESCRIPTION='$DESCRIPTION'";
            }
            if($PIVA!=""){
                $fields[]="PIVA='$PIVA'";
            }
            if(count($fields)>0){
                $where=implode(" OR ", $fields);
                if(!qv_uniquity($maestro, "QW_AZIENDE", $SYSID, $where)){
                    $babelcode="QVUSER_NOTUNIQUE";
                    $failure="Descrizione o P.IVA già presente in anagrafica";
                    $ret=0;
                }
            }
        }
        break;
    case "0ATTORI00000":
        if($oper<=1){
            // DESCRIZIONE
            $DESCRIPTION=qv_actualvalue($data, $prevdata, "DESCRIPTION");
            
            $fields=array();
            if(substr($DESCRIPTION,0,1)!="("){
                $fields[]="DESCRIPTION='$DESCRIPTION'";
            }
            if(count($fields)>0){
                $where=implode(" OR ", $fields);
                if(!qv_uniquity($maestro, "QW_ATTORI", $SYSID, $where)){
                    $babelcode="QVUSER_NOTUNIQUE";
                    $failure="Attore già presente in anagrafica";
                    $ret=0;
                }
            }
        }
        break;
    case "0CORSIFORMAT":
        if($oper<=1){
            // DESCRIZIONE, INIZIO E LUOGO
            $DESCRIPTION=qv_actualvalue($data, $prevdata, "DESCRIPTION");
            $BEGINTIME="[:DATE(".qv_strtime(qv_actualvalue($data, $prevdata, "BEGINTIME")).")]";
            $LUOGO=qv_actualvalue($data, $prevdata, "LUOGO");
            
            $where="DESCRIPTION='$DESCRIPTION' AND BEGINTIME=$BEGINTIME AND LUOGO='$LUOGO'";
            if(!qv_uniquity($maestro, "QW_CORSI", $SYSID, $where)){
                $action="RAISE";
                if(isset($data["CONFLICT"])){
                    $action=$data["CONFLICT"];
                }
                if($action=="RAISE"){
                    $babelcode="QVUSER_NOTUNIQUE";
                    $failure="Corso già presente in anagrafica";
                    $ret=0;
                }
                elseif($action=="SKIP"){
                    $ret=2;
                }
            }
        }
        break;
    }
    return $ret;
}
?>