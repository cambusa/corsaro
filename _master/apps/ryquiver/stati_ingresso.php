<?php 
/****************************************************************************
* Name:            stati_ingresso.php                                       *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function buildfirst($maestro, $PROCESSOID, $owner){
    global $global_lastuserid, $global_quiveruserid, $sqlite3_enabled;
    $sql="";
    $sql.="SELECT QW_PROCSTATI.SYSID AS STATOID,QW_PROCSTATI.ATTOREID AS ATTOREID,ATTORISTATO.UFFICIOID AS UFFICIOID ";
    $sql.="FROM QW_PROCSTATI ";
    $sql.="INNER JOIN QW_ATTORI ATTORISTATO ON ATTORISTATO.SYSID=QW_PROCSTATI.ATTOREID ";
    $sql.="WHERE QW_PROCSTATI.PROCESSOID='$PROCESSOID' AND ";
    if($global_quiveruserid!="" && $owner){
        if($maestro->provider!="sqlite" || $sqlite3_enabled)
            $sql.="( ATTORISTATO.UTENTEID='$global_quiveruserid' OR '$global_quiveruserid' IN (SELECT UTENTEID FROM QW_ATTORI WHERE QW_ATTORI.UFFICIOID<>'' AND QW_ATTORI.UFFICIOID=ATTORISTATO.UFFICIOID) ) AND ";
        else
            $sql.="( ATTORISTATO.UTENTEID='$global_quiveruserid' ) AND ";
    }
    $sql.="QW_PROCSTATI.INIZIALE=1 ";
    $sql.="ORDER BY QW_PROCSTATI.ORDINATORE";
    return $sql;
}
?>