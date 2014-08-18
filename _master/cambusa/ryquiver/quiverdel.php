<?php 
/****************************************************************************
* Name:            quiverdel.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

function qv_deletable($maestro, $table, $field, $SYSID, $tablebase=""){
    global $babelcode, $babelparams;
    switch($table){
    case "QVGENRES":
    case "QVOBJECTS":
    case "QVMOTIVES":
    case "QVARROWS":
    case "QVQUIVERS":
    case "QVFILES":
        $deleted="AND DELETED=0";
        $tablebase="";
        break;
    default:
        switch($tablebase){
        case "QVGENRES":
        case "QVOBJECTS":
        case "QVMOTIVES":
        case "QVARROWS":
        case "QVQUIVERS":
            $deleted="AND $tablebase.DELETED=0";
            break;
        default:
            $deleted="";
            $tablebase="";
        }
    }
    // CERCO IL SYSID NELLA TABELLA SPECIFICATA
    if($tablebase=="")
        maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM $table WHERE $field='$SYSID' $deleted {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}",$r);
    else
        maestro_query($maestro,"SELECT {AS:TOP 1} $table.SYSID AS SYSID FROM $table INNER JOIN $tablebase ON $tablebase.SYSID=$table.SYSID WHERE $table.$field='$SYSID' $deleted {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}",$r);
    if(count($r)>0){
        $babelcode="QVERR_USEDID";
        $b_params=array("field" => $field, "table" => $table);
        $b_pattern="Impossibile cancellare un record con riferimenti [{1}] alla tabella [{2}]";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_deletablearrow($maestro, $SYSID){
    global $babelcode, $babelparams;
    qv_deletable($maestro, "QVARROWS", "REFARROWID", $SYSID);
    qv_deletable($maestro, "QVQUIVERS", "REFARROWID", $SYSID);
    _qv_deletablesel($maestro, $SYSID);
    
    // INDIVIDUO I CAMPI ESTESI PUNTATORI A RECORD DI QVARROWS
    qv_deletablecustom($maestro, "QVARROWS", $SYSID);

    // CERCO IL SYSID IN QVQUIVERARROW
    maestro_query($maestro,"SELECT {AS:TOP 1} QVQUIVERARROW.SYSID FROM QVQUIVERARROW INNER JOIN QVQUIVERS ON QVQUIVERS.SYSID=QVQUIVERARROW.QUIVERID WHERE QVQUIVERARROW.ARROWID='$SYSID' AND QVQUIVERS.DELETED=0 {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}",$r);
    if(count($r)>0){
        $babelcode="QVERR_USEDID";
        $b_params=array("field" => "QUIVERID", "table" => "QVQUIVERARROW");
        $b_pattern="Impossibile cancellare una freccia inclusa in un quiver";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_deletablecustom($maestro, $ref, $SYSID){
    global $babelcode, $babelparams, $global_pointers;
    if(!isset($global_pointers[$ref])){
        $global_pointers[$ref]=array();
        $maestro->loadinfo();
        $infobase=$maestro->infobase;
        for(reset($infobase); $table=current($infobase); next($infobase)){
            if(isset($table->type) && isset($table->fields)){
                if($table->type=="database"){
                    $tabname=key($infobase);
                    if(strpos(",QVSETTINGS,QVGENRETYPES,QVGENRES,QVGENREVIEWS,QVOBJECTTYPES,QVOBJECTS,QVOBJECTVIEWS,QVINCLUSIONS,QVMOTIVETYPES,QVMOTIVES,QVMOTIVEVIEWS,QVARROWTYPES,QVARROWS,QVARROWVIEWS,QVHISTORY,QVEQUIVALENCES,QVQUIVERTYPES,QVQUIVERS,QVQUIVERVIEWS,QVQUIVERARROW,QVFILES,QVTABLEFILE,QVALLOCATIONS,QVSELECTIONS,QVALIASES,QVUSERS,QVROLES,QVMESSAGES,QVJSON,QVSYSTEM,", ",".$tabname.",")===false){
                        $fields=$table->fields;
                        for(reset($fields); $field=current($fields); next($fields)){
                            $fieldname=key($fields);
                            if(isset($field->ref)){
                                if($field->ref==$ref){
                                    $global_pointers[$ref][]=array($tabname, $fieldname);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    for($i=0; $i<count($global_pointers[$ref]); $i++){
        list($t, $f)=$global_pointers[$ref][$i];
        $tableref="";
        $p=strpos($t, "_");
        if($p!==false){
            $tableref="QV".substr($t, 0, $p);
        }
        qv_deletable($maestro, $t, $f, $SYSID, $tableref);
    }
}

function _qv_deletablesel($maestro, $SYSID){
    global $babelcode, $babelparams;
    // CERCO IL PARENTID IN QVSELECTIONS
    maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM QVSELECTIONS WHERE PARENTID='$SYSID' AND UPWARD=0 {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}",$r);
    if(count($r)>0){
        $babelcode="QVERR_USEDID";
        $b_params=array("field" => "PARENTID", "table" => "QVSELECTIONS");
        $b_pattern="Impossibile cancellare un record con riferimenti [{1}] alla tabella [{2}]";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    // CERCO IL SELECTEDID IN QVSELECTIONS
    maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM QVSELECTIONS WHERE SELECTEDID='$SYSID' AND UPWARD=1 {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}",$r);
    if(count($r)>0){
        $babelcode="QVERR_USEDID";
        $b_params=array("field" => "SELECTEDID", "table" => "QVSELECTIONS");
        $b_pattern="Impossibile cancellare un record con riferimenti [{1}] alla tabella [{2}]";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function _qv_clearselections($maestro, $SYSID){
    maestro_execute($maestro, "DELETE FROM QVSELECTIONS WHERE SELECTEDID='$SYSID' AND UPWARD=0");
    maestro_execute($maestro, "DELETE FROM QVSELECTIONS WHERE PARENTID='$SYSID' AND UPWARD=1");
}
?>