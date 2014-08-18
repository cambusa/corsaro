<?php 
/****************************************************************************
* Name:            qv_system_listqbk.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_system_listqbk($maestro, $data){
    global $babelcode, $babelparams;
    global $path_databases;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $list=array();
        $dir=glob($path_databases."_backup/*.QBK");
        foreach($dir as $file){
            $b=basename($file);
            $n=substr($b, 0, -4);
            $e=substr($n, 0, strlen($n)-14);
            $d=substr($n, -14);
            $d=substr($d, 6, 2)."/".substr($d, 4, 2)."/".substr($d, 0, 4)." ".substr($d, 8, 2).":".substr($d, 10, 2).":".substr($d, 12, 4);
            
            $list[]=array("NAME" => $b, "ENV" => $e, "TIME" => $d);
        }
        
        rsort($list);
        
        // VARIABILI DI RITORNO
        $babelparams["LIST"]=$list;
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

function _readblock($fp, $len){
    $buff="";
    for($i=0; $i<$len; $i++){
        $buff.=fgetc($fp);
    }
    return $buff;
}
?>