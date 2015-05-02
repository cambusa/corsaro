<?php 
/****************************************************************************
* Name:            qv_managetemp.php                                        *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
function qv_managetemp($maestro, $data){
    global $babelcode, $babelparams;
    global $path_databases, $path_customize, $url_customize, $path_root, $url_base, $safe_extensions;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        $infoenv=qv_environs($maestro);
        $envtemp=$infoenv["envtemp"];
        $dirtemp=$infoenv["dirtemp"];
        $dirattach=$infoenv["dirattach"];
        
        clearstatcache();
        $sec=60*60;
        
        $d=glob($dirtemp."*.*");
        foreach($d as $filename){
            try{
                if(time()-@filemtime($filename)>$sec){
                    @unlink($filename);
                }
            }
            catch(Exception $e){}
        }
        
        if($envtemp!="temporary"){
            // LA TEMPORANEA D'AMBIENTE E' DIVERSA DA QUELLA PREDEFINITA 
            if(is_file($path_databases."_environs/temporary.php")){
                $env_strconn="";
                include($path_databases."_environs/temporary.php");
                $dirtemp=$env_strconn;

                $d=glob($dirtemp."*.*");
                foreach($d as $filename){
                    try{
                        if(time()-@filemtime($filename)>$sec){
                            @unlink($filename);
                        }
                    }
                    catch(Exception $e){}
                }
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