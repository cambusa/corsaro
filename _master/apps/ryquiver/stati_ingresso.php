<?php 
/****************************************************************************
* Name:            stati_ingresso.php                                       *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function buildfirst($PROCESSOID, $owner){
    global $global_lastuserid, $global_quiveruserid;
    $sql="";
    $sql.="SELECT QW_PROCSTATI.SYSID AS STATOID,QW_PROCSTATI.ATTOREID AS ATTOREID,ATTORISTATO.UFFICIOID AS UFFICIOID ";
    $sql.="FROM QW_PROCSTATI ";
    $sql.="INNER JOIN QW_ATTORI ATTORISTATO ON ATTORISTATO.SYSID=QW_PROCSTATI.ATTOREID ";
    $sql.="WHERE QW_PROCSTATI.PROCESSOID='$PROCESSOID' AND ";
    //if(substr($global_lastuserid,0,12)!="0SERVERID000" && $owner){
    if($global_quiveruserid!="" && $owner){
        $sql.="( ATTORISTATO.UTENTEID='$global_quiveruserid' OR '$global_quiveruserid' IN (SELECT UTENTEID FROM QW_ATTORI WHERE QW_ATTORI.UFFICIOID=ATTORISTATO.UFFICIOID) ) AND ";
    }
    $sql.="QW_PROCSTATI.INIZIALE=1 ";
    $sql.="ORDER BY QW_PROCSTATI.ORDINATORE";
    return $sql;
}
?>