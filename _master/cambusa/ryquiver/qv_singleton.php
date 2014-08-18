<?php 
/****************************************************************************
* Name:            qv_singleton.php                                         *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_singleton($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $args=array();
        $select="";
        $where="";
            
        foreach($data as $key => $value){
            switch($key){
                case "select":
                    $select=ryqNormalize($value);
                    break;
                case "where":
                    $where=ryqNormalize($value);
                    break;
                default:
                    $args[$key]=ryqEscapize($value);
            }
        }
        if($select==""){
            $babelcode="QVERR_SELECT";
            $b_params=array();
            $b_pattern="Selezione non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        if($where==""){
            $babelcode="QVERR_WHERE";
            $b_params=array();
            $b_pattern="Vincolo non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        foreach($args as $key => $value){
           $select=str_replace("[=$key]", $value, $select);
           $where=str_replace("[=$key]", $value, $where);
        }

        $sql="SELECT $select WHERE $where";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            foreach($r[0] as $key => $value){
                // SE E' UNA DATA LA NORMALIZZO
                if(preg_match("/\d\d\d\d-\d\d-\d\d( |T)?(\d\d:\d\d:\d\d)?/", $value)==1 ){
                    $value=qv_strtime($value);
                    $value=str_replace("000000", "", $value);
                }
                $babelparams[$key]=$value;
                if($key=="SYSID")
                    $SYSID=$value;
            }
        }
        else{
            $babelcode="QVERR_SINGLETON";
            $b_params=array();
            $b_pattern="Record univoco non trovato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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