<?php 
/****************************************************************************
* Name:            qv_files_info.php                                        *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
function qv_files_info($maestro, $data){
    global $babelcode, $babelparams;
    global $path_applications, $path_cambusa;
    global $url_applications, $url_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        $infoenv=qv_environs($maestro);
		$envattach=$infoenv["envattach"];
        $dirtemp=$infoenv["dirtemp"];
        $dirattach=$infoenv["dirattach"];
        $urltemp=$infoenv["urltemp"];
        $urlattach=$infoenv["urlattach"];
		
		$babelparams["ENVATTACH"]=$envattach;
        
        $babelparams["DIRTEMP"]=$dirtemp;
        $babelparams["DIRATTACH"]=$dirattach;
        $babelparams["DIRAPPS"]=$path_applications;
        $babelparams["DIRCAMBUSA"]=$path_cambusa;
        
        $babelparams["URLTEMP"]=$urltemp;
        $babelparams["URLATTACH"]=$urlattach;
        $babelparams["URLAPPS"]=$url_applications;
        $babelparams["URLCAMBUSA"]=$url_cambusa;
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