<?php 
/****************************************************************************
* Name:            quivervws.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_refreshview($maestro, $prefix, $SYSID, $TYPOLOGYID){
    $table=$prefix."S";
    $tabletypes=$prefix."TYPES";
    $tableviews=$prefix."VIEWS";

    switch($table){
    case "QVGENRES":
        $deleted=" AND QVGENRES.DELETED=0";
        break;
    case "QVOBJECTS":
        $deleted=" AND QVOBJECTS.DELETED=0";
        break;
    case "QVMOTIVES":
        $deleted=" AND QVMOTIVES.DELETED=0";
        break;
    case "QVARROWS":
        $deleted=" AND QVARROWS.DELETED=0";
        break;
    case "QVQUIVERS":
        $deleted=" AND QVQUIVERS.DELETED=0";
        break;
    default:
        $deleted="";
    }
    maestro_query($maestro,"SELECT * FROM $tabletypes WHERE SYSID='$TYPOLOGYID'",$r);
    if(count($r)==1){
        $VIEWNAME=$r[0]["VIEWNAME"];
        $TABLENAME=$r[0]["TABLENAME"];
        unset($r);
        // CREO LA VISTA 
        if($VIEWNAME!=""){
            $select=qv_standardselect($table);
            if($TABLENAME!=""){
                // LA VISTA DOVREBBE ESTENDERE I DATI
                maestro_query($maestro,"SELECT * FROM $tableviews WHERE TYPOLOGYID='$TYPOLOGYID'",$r);
                if(count($r)>0){
                    for($i=0;$i<count($r);$i++){
                        $FIELDNAME=$r[$i]["FIELDNAME"];
                        $FIELDTYPE=$r[$i]["FIELDTYPE"];
                        $FORMULA=$r[$i]["FORMULA"];
                        if($FIELDNAME!="NomeCampo"){
                            if($FORMULA=="")
                                $select.=",$TABLENAME.$FIELDNAME AS $FIELDNAME";
                            else
                                $select.=",$FORMULA AS $FIELDNAME";
                        }
                    }
                    $sql="CREATE VIEW $VIEWNAME AS SELECT $select FROM $table LEFT JOIN $TABLENAME ON $TABLENAME.SYSID=$table.SYSID WHERE $table.TYPOLOGYID='$TYPOLOGYID' $deleted";
                    maestro_execute($maestro, $sql);
                }
                else{
                    $sql="CREATE VIEW $VIEWNAME AS SELECT $select FROM $table WHERE TYPOLOGYID='$TYPOLOGYID' $deleted";
                    maestro_execute($maestro, $sql);
                }
            }
            else{
                $sql="CREATE VIEW $VIEWNAME AS SELECT $select FROM $table WHERE TYPOLOGYID='$TYPOLOGYID' $deleted";
                maestro_execute($maestro, $sql);
            }
        }
    }
}

function qv_deleteview($maestro, $prefix, $TYPOLOGYID){
    $table=$prefix."TYPES";
    maestro_query($maestro,"SELECT * FROM $table WHERE SYSID='$TYPOLOGYID'",$r);
    if(count($r)==1){
        $VIEWNAME=$r[0]["VIEWNAME"];
        if($VIEWNAME!=""){
            if(maestro_istable($maestro, $VIEWNAME)){
                maestro_execute($maestro, "DROP VIEW $VIEWNAME", false);
            }
        }
    }
}

function qv_standardselect($table){
    $ret="";
    switch($table){
    case "QVGENRES":
        $ret.="QVGENRES.SYSID,";
        $ret.="QVGENRES.NAME,";
        $ret.="QVGENRES.DESCRIPTION,";
        $ret.="QVGENRES.BREVITY,";
        $ret.="QVGENRES.REGISTRY,";
        $ret.="QVGENRES.ROUNDING,";
        $ret.="QVGENRES.TYPOLOGYID,";
        $ret.="QVGENRES.TAG,";
        //$ret.="QVGENRES.DELETED,"; Campo nascosto alla vista
        $ret.="QVGENRES.ROLEID,";
        $ret.="QVGENRES.USERINSERTID,";
        $ret.="QVGENRES.USERUPDATEID,";
        $ret.="QVGENRES.USERDELETEID,";
        $ret.="QVGENRES.TIMEINSERT,";
        $ret.="QVGENRES.TIMEUPDATE,";
        $ret.="QVGENRES.TIMEDELETE";
        break;
    case "QVMOTIVES":
        $ret.="QVMOTIVES.SYSID,";
        $ret.="QVMOTIVES.NAME,";
        $ret.="QVMOTIVES.DESCRIPTION,";
        $ret.="QVMOTIVES.REGISTRY,";
        $ret.="QVMOTIVES.TYPOLOGYID,";
        $ret.="QVMOTIVES.DIRECTION,";
        $ret.="QVMOTIVES.REFERENCEID,";
        $ret.="QVMOTIVES.COUNTERPARTID,";
        $ret.="QVMOTIVES.CONSISTENCY,";
        $ret.="QVMOTIVES.SCOPE,";
        $ret.="QVMOTIVES.UPDATING,";
        $ret.="QVMOTIVES.DELETING,";
        $ret.="QVMOTIVES.STATUS,";
        $ret.="QVMOTIVES.DISCHARGE,";
        $ret.="QVMOTIVES.TAG,";
        //$ret.="QVMOTIVES.DELETED,"; Campo nascosto alla vista
        $ret.="QVMOTIVES.ROLEID,";
        $ret.="QVMOTIVES.USERINSERTID,";
        $ret.="QVMOTIVES.USERUPDATEID,";
        $ret.="QVMOTIVES.USERDELETEID,";
        $ret.="QVMOTIVES.TIMEINSERT,";
        $ret.="QVMOTIVES.TIMEUPDATE,";
        $ret.="QVMOTIVES.TIMEDELETE";
        break;
    case "QVOBJECTS":
        $ret.="QVOBJECTS.SYSID,";
        $ret.="QVOBJECTS.NAME,";
        $ret.="QVOBJECTS.DESCRIPTION,";
        $ret.="QVOBJECTS.REGISTRY,";
        $ret.="QVOBJECTS.TYPOLOGYID,";
        $ret.="QVOBJECTS.REFGENREID,";
        $ret.="QVOBJECTS.REFOBJECTID,";
        $ret.="QVOBJECTS.REFQUIVERID,";
        $ret.="QVOBJECTS.BEGINTIME,";
        $ret.="QVOBJECTS.ENDTIME,";
        $ret.="QVOBJECTS.REFERENCE,";
        $ret.="QVOBJECTS.AUXTIME,";
        $ret.="QVOBJECTS.AUXAMOUNT,";
        $ret.="QVOBJECTS.MAXAMOUNT,";
        $ret.="QVOBJECTS.BUFFERID,";
        $ret.="QVOBJECTS.TAG,";
        $ret.="QVOBJECTS.CONSISTENCY,";
        $ret.="QVOBJECTS.SCOPE,";
        $ret.="QVOBJECTS.UPDATING,";
        $ret.="QVOBJECTS.DELETING,";
        //$ret.="QVOBJECTS.DELETED,"; Campo nascosto alla vista
        $ret.="QVOBJECTS.ROLEID,";
        $ret.="QVOBJECTS.USERINSERTID,";
        $ret.="QVOBJECTS.USERUPDATEID,";
        $ret.="QVOBJECTS.USERDELETEID,";
        $ret.="QVOBJECTS.TIMEINSERT,";
        $ret.="QVOBJECTS.TIMEUPDATE,";
        $ret.="QVOBJECTS.TIMEDELETE";
        break;
    case "QVARROWS":
        $ret.="QVARROWS.SYSID,";
        $ret.="QVARROWS.NAME,";
        $ret.="QVARROWS.DESCRIPTION,";
        $ret.="QVARROWS.REGISTRY,";
        $ret.="QVARROWS.BOWID,";
        $ret.="QVARROWS.BOWTIME,";
        $ret.="QVARROWS.TARGETID,";
        $ret.="QVARROWS.TARGETTIME,";
        $ret.="QVARROWS.AUXTIME,";
        $ret.="QVARROWS.STATUSTIME,";
        $ret.="QVARROWS.TYPOLOGYID,";
        $ret.="QVARROWS.MOTIVEID,";
        $ret.="QVARROWS.GENREID,";
        $ret.="QVARROWS.AMOUNT,";
        $ret.="QVARROWS.REFERENCE,";
        $ret.="QVARROWS.REFARROWID,";
        $ret.="QVARROWS.TAG,";
        $ret.="QVARROWS.CONSISTENCY,";
        $ret.="QVARROWS.AVAILABILITY,";
        $ret.="QVARROWS.SCOPE,";
        $ret.="QVARROWS.UPDATING,";
        $ret.="QVARROWS.DELETING,";
        $ret.="QVARROWS.STATUS,";
        $ret.="QVARROWS.STATUSRISK,";
        $ret.="QVARROWS.PHASE,";
        $ret.="QVARROWS.PHASENOTE,";
        $ret.="QVARROWS.PROVIDER,";
        $ret.="QVARROWS.PARCEL,";
        //$ret.="QVARROWS.DELETED,"; Campo nascosto alla vista
        $ret.="QVARROWS.ROLEID,";
        $ret.="QVARROWS.USERINSERTID,";
        $ret.="QVARROWS.USERUPDATEID,";
        $ret.="QVARROWS.USERDELETEID,";
        $ret.="QVARROWS.TIMEINSERT,";
        $ret.="QVARROWS.TIMEUPDATE,";
        $ret.="QVARROWS.TIMEDELETE";
        break;
    case "QVQUIVERS":
        $ret.="QVQUIVERS.SYSID,";
        $ret.="QVQUIVERS.NAME,";
        $ret.="QVQUIVERS.DESCRIPTION,";
        $ret.="QVQUIVERS.REGISTRY,";
        $ret.="QVQUIVERS.AUXTIME,";
        $ret.="QVQUIVERS.STATUSTIME,";
        $ret.="QVQUIVERS.AUXAMOUNT,";
        $ret.="QVQUIVERS.TYPOLOGYID,";
        $ret.="QVQUIVERS.REFGENREID,";
        $ret.="QVQUIVERS.REFOBJECTID,";
        $ret.="QVQUIVERS.REFMOTIVEID,";
        $ret.="QVQUIVERS.REFARROWID,";
        $ret.="QVQUIVERS.REFQUIVERID,";
        $ret.="QVQUIVERS.REFERENCE,";
        $ret.="QVQUIVERS.TAG,";
        $ret.="QVQUIVERS.CONSISTENCY,";
        $ret.="QVQUIVERS.AVAILABILITY,";
        $ret.="QVQUIVERS.SCOPE,";
        $ret.="QVQUIVERS.UPDATING,";
        $ret.="QVQUIVERS.DELETING,";
        $ret.="QVQUIVERS.STATUS,";
        $ret.="QVQUIVERS.PHASE,";
        $ret.="QVQUIVERS.PHASENOTE,";
        $ret.="QVQUIVERS.MOREDATA,";
        //$ret.="QVQUIVERS.DELETED,";  Campo nascosto alla vista
        $ret.="QVQUIVERS.ROLEID,";
        $ret.="QVQUIVERS.USERINSERTID,";
        $ret.="QVQUIVERS.USERUPDATEID,";
        $ret.="QVQUIVERS.USERDELETEID,";
        $ret.="QVQUIVERS.TIMEINSERT,";
        $ret.="QVQUIVERS.TIMEUPDATE,";
        $ret.="QVQUIVERS.TIMEDELETE";
      break;
    }
    return $ret;
}

function qv_checkfieldname($maestro,$table,$SYSID,$TYPOLOGYID,$FIELDNAME){
    global $babelcode, $babelparams;
    // IMPONGO CHE NON SIA VUOTO
    if(trim($FIELDNAME)==""){
        $babelcode="QVERR_EMPTYNAME";
        $b_params=array("FIELDNAME" => $FIELDNAME, "table" => $table);
        $b_pattern="Nome non specificato";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    // CONTROLLO L'UNICITA'
    maestro_query($maestro,"SELECT SYSID FROM $table WHERE SYSID<>'$SYSID' AND TYPOLOGYID='$TYPOLOGYID' AND [:UPPER(FIELDNAME)]='".strtoupper($FIELDNAME)."'",$r);
    if(count($r)>0){
        $babelcode="QVERR_NOTUNIQUE";
        $b_params=array("FIELDNAME" => $FIELDNAME, "table" => $table, "TYPOLOGYID" => $TYPOLOGYID);
        $b_pattern="Nome [{1}] non univoco in [{2}]";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

?>