<?php
/****************************************************************************
* Name:            apptriggerarrow.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function apptriggerarrow(
            $maestro, 
            &$data, 
            $SYSID, 
            $TYPOLOGYID, 
            $oper, 
            $user, 
            $role, 
            &$babelcode, 
            &$failure){
    global $path_applications;
    $ret=true;
    // CONTROLLO GESTIONE MOVIMENTI RELATIVI A PRATICHE
    switch( substr($TYPOLOGYID, 0, 12) ){
    case "0MOVIMENTI00":
        if(isset($data["TRIGGERSTATOID"]))
            $STATOID=$data["TRIGGERSTATOID"];
        else
            $STATOID="";
        if($STATOID!=""){
            include_once $path_applications."ryquiver/pratiche_saldo.php";
            $sql="SELECT PROCESSOID FROM OBJECTS_PROCSTATI WHERE SYSID='$STATOID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                $PROCESSOID=$r[0]["PROCESSOID"];
                // LEGGO IL PRIMO STATO DEL PROCESSO
                maestro_query($maestro, "SELECT CONTOID FROM OBJECTS_PROCSTATI WHERE PROCESSOID='$PROCESSOID' AND INIZIALE=1 ORDER BY ORDINATORE", $r);
                if(count($r)>0){
                    $CONTOID=$r[0]["CONTOID"];
                    if($CONTOID!=""){
                        $GENREID="";
                        if(isset($data["PRATICAID"]))   // POTREBBE ESSERE PASSATO IN CASO DI CANCELLAZIONE
                            $PRATICAID=$data["PRATICAID"];
                        else
                            $PRATICAID="";
                        // LEGGO IL CONTO PER REPERIRE GENREID
                        $sql="SELECT REFGENREID FROM QVOBJECTS WHERE SYSID='$CONTOID'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1){
                            $GENREID=$r[0]["REFGENREID"];
                        }
                        // DETERMINO LA PRATICA DI APPARTENENZA
                        $sql="SELECT QUIVERID FROM QVQUIVERARROW WHERE ARROWID='$SYSID'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1){
                            $PRATICAID=$r[0]["QUIVERID"];
                        }
                        if($PRATICAID!=""){
                            // AGGIORNO IL SALDO DEL QUIVER
                            pratiche_saldo($maestro, $CONTOID, $GENREID, $PRATICAID);
                        }
                    }
                }
            }
        }
        break;
    }
    return $ret;
}
?>