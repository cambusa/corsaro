<?php 
/****************************************************************************
* Name:            qv_files_info.php                                        *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
function qv_files_info($maestro, $data){
    global $babelcode, $babelparams;
    global $path_applications, $url_applications;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        qv_environs($maestro, $dirtemp, $dirattach, $urltemp, $urlattach);
        
        $babelparams["DIRTEMP"]=$dirtemp;
        $babelparams["DIRATTACH"]=$dirattach;
        $babelparams["DIRAPPS"]=$path_applications;
        
        $babelparams["URLTEMP"]=$urltemp;
        $babelparams["URLATTACH"]=$urlattach;
        $babelparams["URLAPPS"]=$url_applications;
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