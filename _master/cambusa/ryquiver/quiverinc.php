<?php 
/****************************************************************************
* Name:            quiverinc.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

function qv_solveinclusion($maestro, $SYSID, &$OBJECTID, &$PARENTID){
    global $babelcode, $babelparams;
    maestro_query($maestro,"SELECT OBJECTID,PARENTID FROM QVINCLUSIONS WHERE SYSID='$SYSID'", $r);
    if(count($r)==1){
        $OBJECTID=$r[0]["OBJECTID"];
        $PARENTID=$r[0]["PARENTID"];
    }
    else{
        $babelcode="QVERR_NOINCLUSION";
        $b_params=array("SYSID" => $SYSID);
        $b_pattern="Inclusione non trovata";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_checkinclusions($maestro, $SYSID, $OBJECTID, $PARENTID, $BEGINTIME, $ENDTIME){
    global $babelcode, $babelparams;
    maestro_query($maestro,"SELECT SYSID,BEGINTIME,ENDTIME FROM QVINCLUSIONS WHERE SYSID<>'$SYSID' AND OBJECTID='$OBJECTID' AND PARENTID='$PARENTID'", $r);
    for($i=0; $i<count($r); $i++){
        $id=$r[$i]["SYSID"];
        $bi=qv_strtime($r[$i]["BEGINTIME"]);
        $ei=qv_strtime($r[$i]["ENDTIME"]);
        if( $bi<$ENDTIME && $ei>$BEGINTIME ){
            $babelcode="QVERR_INCLCONFLICT";
            $b_params=array("SYSID" => $id, "BEGINTIME" => $bi, "ENDTIME" => $ei);
            $b_pattern="Ciclo di vita non compatibile con quello di altre inclusioni";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
}

?>