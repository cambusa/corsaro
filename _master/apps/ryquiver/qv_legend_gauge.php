<?php 
/****************************************************************************
* Name:            qv_legend_gauge.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_legend_gauge($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $dirgauge=$path_cambusa."ryque/requests/";
        $sec=24*60*60;
        // ELIMINO I FILE DELLA LISTA
        if(isset($data["GAUGELIST"])){
            clearstatcache();
            $GAUGELIST=$data["GAUGELIST"];
            $GAUGEARRAY=explode("|", $GAUGELIST);
            foreach($GAUGEARRAY as $GAUGEID){
                $jolly=$dirgauge.$GAUGEID.".*";
                $g=glob($jolly);
                foreach($g as $path){
                    @unlink($path);
                }
            }
        }
        // ELIMINO I FILE SCADUTI
        clearstatcache();
        $g=glob($dirgauge."*.sts");
        foreach($g as $filename){
            if(time()-@filemtime($filename)>$sec){
                $GAUGEID=substr(basename($filename),0,-4);
                $jolly=$dirgauge.$GAUGEID.".*";
                $g=glob($jolly);
                foreach($g as $path){
                    @unlink($path);
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