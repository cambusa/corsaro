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
    global $global_lastuserid, $global_lastroleid;
    $sql="";
    $sql.="SELECT QW_PROCSTATI.SYSID AS STATOID,QW_PROCSTATI.ATTOREID AS ATTOREID ";
    $sql.="FROM QW_PROCSTATI ";
    $sql.="INNER JOIN QW_ATTORI ON QW_ATTORI.SYSID=QW_PROCSTATI.ATTOREID ";
    $sql.="LEFT JOIN QVUSERS ON QVUSERS.SYSID=QW_ATTORI.UTENTEID ";
    $sql.="WHERE QW_PROCSTATI.PROCESSOID='$PROCESSOID' AND ";
    if($owner){
        $sql.="QVUSERS.EGOID='$global_lastuserid' AND ";
    }
    $sql.="QW_PROCSTATI.INIZIALE=1 ";
    $sql.="ORDER BY QW_PROCSTATI.ORDINATORE";
    return $sql;
}
?>