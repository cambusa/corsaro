<?php 
/****************************************************************************
* Name:            protocollo_nuovo.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function genera_protocollo($maestro, $table, $PROTSERIE){
    if($PROTSERIE!=""){
        $sql="SELECT MAX(PROTPROGR) AS LASTPROGR FROM $table WHERE PROTSERIE='$PROTSERIE'";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $PROTPROGR=intval($r[0]["LASTPROGR"])+1;
        }
        else{
            $PROTPROGR=0;
        }
    }
    else{
        $PROTPROGR=0;
    }
    return $PROTPROGR;
}
?>