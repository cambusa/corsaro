<?php 
/****************************************************************************
* Name:            quiverxml.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function _qv_loadxml($xml){
    global $babelcode, $babelparams;
    
    $xml=ryqNormalize($xml);
    if(substr($xml,0,2)!="<?"){
        $xml="<?xml version=\"1.0\" encoding=\"UTF-8\"?>".$xml;
    }
    $p=array();
    __loadxml( json_decode(json_encode((array) simplexml_load_string($xml)), 1), $r, $p);
    $_POST=$r;
}
function __loadxml($a, &$r, &$p){
    $r=array();
    $arr=false;
    foreach($a as $n=>$v){
        if(is_array($v)){
            __loadxml($v, $t, $r);
            if($n=="array")
                $arr=true;
            if($arr)
                $p[]=$t;
            else
                $r[$n]=$t;
        }
        else{
            $r[$n]=$v;
        }
    }
}

function _qv_savexml($arr){
    if(gettype($arr)=="string"){
        $arr=json_decode($arr, true);
    }
    array_walk_recursive($arr, "xml_escapize");
    $xml=new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><xml></xml>");
    __savexml($arr, $xml);
    return $xml->asXML();
}
function __savexml($arr, &$xml) {
    foreach($arr as $key => $value) {
        if(is_array($value)){
            if(count($value)>0){
                list($k, $v)=each($value);
                $isarr=is_numeric($k);
            }
            else{
                $isarr=false;
            }
            if($isarr){
                $set=$xml->addChild($key);
                foreach($value as $r){
                    $subnode=$set->addChild("array");
                    foreach($r as $a=>$z){
                        $subnode->addAttribute($a, $z);
                    }
                }
            }
            else{
                $subnode=$xml->addChild($key);
                __savexml($value, $subnode);
            }
        }
        else {
            $xml->addChild($key, $value);
        }
    }
}
function xml_escapize(&$value){
    if($value!=""){
        $value=html_entity_decode($value);
        if(!mb_check_encoding($value, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            $value=utf8_encode($value);
        }
    }
}
?>