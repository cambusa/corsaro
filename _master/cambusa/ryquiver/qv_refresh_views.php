<?php 
/****************************************************************************
* Name:            qv_refresh_views.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivervws.php";
function qv_refresh_views($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $classes=array("QVGENRE", "QVOBJECT", "QVMOTIVE", "QVARROW", "QVQUIVER");
        
        for($i=0; $i<count($classes); $i++){
            $class=$classes[$i];
            $types=$classes[$i]."TYPES";
            maestro_query($maestro, "SELECT SYSID FROM $types", $r);
            for($j=0; $j<count($r); $j++){
                $TYPOLOGYID=$r[$j]["SYSID"];
                // DROPPO LA VECCHIA VIEW
                qv_deleteview($maestro, $class, $TYPOLOGYID);
                // RICREO LA VIEW
                qv_refreshview($maestro, $class, "", $TYPOLOGYID);
            }
        }
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
    }
    // USCITA JSON
    $j=array();
    $j["success"]=$success;
    $j["code"]=$babelcode;
    $j["params"]=$babelparams;
    $j["message"]=$message;
    $j["SYSID"]=$SYSID;
    return $j; //ritorno standard
}
?>