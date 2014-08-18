<?php
/****************************************************************************
* Name:            appvalidatearrow.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function appvalidatearrow(
            $maestro, 
            &$data, 
            $prevdata, 
            $SYSID, 
            $TYPOLOGYID, 
            $oper, 
            $user, 
            $role, 
            &$babelcode, 
            &$failure){
    $ret=true;
    // CONTROLLO GESTIONE MOVIMENTI RELATIVI A PRATICHE
    switch( substr($TYPOLOGYID, 0, 12) ){
    case "0MOVIMENTI00":
        $data["TRIGGERSTATOID"]=qv_actualvalue($data, $prevdata, "STATOID");
        break;
    }
    return $ret;
}
?>