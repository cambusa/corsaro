<?php 
/****************************************************************************
* Name:            quiverobj.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

function qv_insertdatalife($maestro, $data, &$BEGINTIME, &$ENDTIME){
    global $babelcode, $babelparams;
    // DETERMINO BEGINTIME
    if(isset($data["BEGINTIME"]))
        $BEGINTIME=qv_escapizetime($data["BEGINTIME"], LOWEST_TIME);
    else
        $BEGINTIME=LOWEST_TIME;

    // DETERMINO ENDTIME
    if(isset($data["ENDTIME"]))
        $ENDTIME=qv_escapizetime($data["ENDTIME"], HIGHEST_TIME);
    else
        $ENDTIME=HIGHEST_TIME;

    // CONSISTENZA DATE
    //if($BEGINTIME>=$ENDTIME){
    if($BEGINTIME>$ENDTIME){
        $babelcode="QVERR_NOLIFE";
        $b_params=array("BEGINTIME" => $BEGINTIME, "ENDTIME" => $ENDTIME);
        $b_pattern="Date non consistenti";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}
function qv_updatedatalife($maestro, $data, &$sets, &$BEGINTIME, &$ENDTIME, &$changed=false){
    global $babelcode, $babelparams;
    $prevbegin=$BEGINTIME;
    $prevend=$ENDTIME;
    // DETERMINO BEGINTIME
    if(isset($data["BEGINTIME"])){
        $BEGINTIME=qv_escapizetime($data["BEGINTIME"], LOWEST_TIME);
        $changed=($BEGINTIME!=$prevbegin);
    }
        
    // DETERMINO ENDTIME
    if(isset($data["ENDTIME"])){
        $ENDTIME=qv_escapizetime($data["ENDTIME"], HIGHEST_TIME);
        $changed=($ENDTIME!=$prevend);
    }
    // CONSISTENZA DATE
    //if($BEGINTIME>=$ENDTIME){
    if($BEGINTIME>$ENDTIME){
        $babelcode="QVERR_NOLIFE";
        $b_params=array("BEGINTIME" => $BEGINTIME, "ENDTIME" => $ENDTIME);
        $b_pattern="Date non consistenti";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_deletableobject($maestro, $SYSID){
    qv_deletable($maestro, "QVOBJECTS", "REFOBJECTID", $SYSID);
    qv_deletable($maestro, "QVOBJECTS", "BUFFERID", $SYSID);
    qv_deletable($maestro, "QVINCLUSIONS", "OBJECTID", $SYSID);
    qv_deletable($maestro, "QVINCLUSIONS", "PARENTID", $SYSID);
    qv_deletable($maestro, "QVARROWS", "BOWID", $SYSID);
    qv_deletable($maestro, "QVARROWS", "TARGETID", $SYSID);
    qv_deletable($maestro, "QVQUIVERS", "REFOBJECTID", $SYSID);
    qv_deletable($maestro, "QVMOTIVES", "REFERENCEID", $SYSID);
    qv_deletable($maestro, "QVMOTIVES", "COUNTERPARTID", $SYSID);
    _qv_deletablesel($maestro, $SYSID);

    // INDIVIDUO I CAMPI ESTESI PUNTATORI A RECORD DI QVARROWS
    qv_deletablecustom($maestro, "QVOBJECTS", $SYSID);
}

?>