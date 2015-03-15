<?php 
/****************************************************************************
* Name:            quivercln.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

function qv_cloning($maestro, $prefix, $TYPOLOGYID, $SYSID, &$datains){
    global $babelcode, $babelparams;

    // NOME DELLE TABELLE INTERESSATE
    $table=$prefix."S";
    $tabletypes=$prefix."TYPES";
    $tableviews=$prefix."VIEWS";
    
    // CAMPI ESTESI
    maestro_query($maestro,"SELECT TABLENAME FROM $tabletypes WHERE SYSID='$TYPOLOGYID'", $t);
    if(count($t)==1){
        $TABLENAME=$t[0]["TABLENAME"];
        if($TABLENAME!=""){
            // DETERMINO I TIPI DEI DATI ESTESI
            $viewtypes=array();
            maestro_query($maestro,"SELECT * FROM $tableviews WHERE TYPOLOGYID='$TYPOLOGYID'", $v);
            for($i=0;$i<count($v);$i++){
                $FIELDNAME=$v[$i]["FIELDNAME"];
                $FIELDTYPE=$v[$i]["FIELDTYPE"];
                $viewtypes[$FIELDNAME]=$FIELDTYPE;
            }
            // LEGGO I DATI ESTESI
            maestro_query($maestro,"SELECT * FROM $TABLENAME WHERE SYSID='$SYSID'", $r);
            if(count($r)==1){
                foreach($r[0] as $name => $value){
                    if($name!="SYSID"){
                        if(isset($viewtypes[$name])){
                            $type=strtoupper($viewtypes[$name]);
                            if($type=="DATE" || $type=="TIMESTAMP"){
                                $value=qv_strtime($value);
                            }
                        }
                        $datains[$name]=$value;
                    }
                }
            }
        }
    }
}
function qv_cloneattachments($maestro, $SYSID, $CLONEID){
    maestro_query($maestro,"SELECT * FROM QVTABLEFILE WHERE RECORDID='$SYSID'", $r);
    for($i=0; $i<count($r); $i++){
        $SYSID=qv_createsysid($maestro);
        $TABLENAME=$r[$i]["TABLENAME"];
        $RECORDID=$CLONEID;
        $FILEID=$r[$i]["FILEID"];
        $columns="SYSID,TABLENAME,RECORDID,FILEID";
        $values="'$SYSID','$TABLENAME','$RECORDID','$FILEID'";
        $sql="INSERT INTO QVTABLEFILE($columns) VALUES($values)";
        maestro_execute($maestro, $sql);
    }
}
?>