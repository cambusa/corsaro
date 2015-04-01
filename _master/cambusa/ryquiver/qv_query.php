<?php 
/****************************************************************************
* Name:            qv_query.php                                             *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_query($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $args=array();
        $sql="";
            
        foreach($data as $key => $value){
            switch($key){
                case "sql":
                    $sql=ryqNormalize($value);
                    break;
                default:
                    $args[$key]=ryqEscapize($value);
            }
        }
        if($sql==""){
            $babelcode="QVERR_SQL";
            $b_params=array();
            $b_pattern="Query SQL non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        foreach($args as $key => $value){
           $sql=str_replace("[=$key]", $value, $sql);
        }
        maestro_query($maestro, $sql, $r);
        $babelparams["dataset"]=$r;
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