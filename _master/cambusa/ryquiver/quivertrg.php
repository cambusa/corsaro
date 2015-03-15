<?php 
/****************************************************************************
* Name:            quivertrg.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_triggerobject($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize,$path_applications;
    global $babelcode, $babelparams;
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "object");
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "object");
    _qv_cacheremove($maestro, $SYSID);
}
function qv_triggerarrow($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "arrow");
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "arrow");
    _qv_cacheremove($maestro, $SYSID);
}
function qv_triggerquiver($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "quiver");
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "quiver");
    _qv_cacheremove($maestro, $SYSID);
}
function qv_triggergenre($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "genre");
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "genre");
    _qv_cacheremove($maestro, $SYSID);
}

function qv_triggermotive($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "motive");
    _qv_extratrigger($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "motive");
    _qv_cacheremove($maestro, $SYSID);
}
function _qv_extratrigger($maestro, &$data, $SYSID, $TYPOLOGYID, $oper, $position, $entity){
    global $global_lastusername,$global_lastrolename,$path_customize,$path_applications;
    global $babelcode, $babelparams;
    
    switch($position){
    case "app":$dir=$path_applications;break;
    case "cust":$dir=$path_customize;break;
    }
    switch($entity){
    case "object":$prefix="QVOBJECT";break;
    case "genre":$prefix="QVGENRE";break;
    case "motive":$prefix="QVMOTIVE";break;
    case "arrow":$prefix="QVARROW";break;
    case "quiver":$prefix="QVQUIVER";break;
    }
    $inc=$dir."ryquiver/".$position."trigger".$entity.".php";
    if(is_file($inc)){
        include_once $inc;
        $funct=$position."trigger".$entity;
        if(function_exists($funct)){
            $babelcode="";
            $failure="";
            if( !$funct($maestro, $data, $SYSID, $TYPOLOGYID, $oper, $global_lastusername, $global_lastrolename, $babelcode, $failure) ){
                if($babelcode=="")
                    $babelcode="QVERR_USERDEFINED";
                if($failure=="")
                    $failure="Fallito trigger per motivi non specificati";
                $b_params=array();
                $b_pattern=$failure;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
    }
}
?>