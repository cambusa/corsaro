<?php
/****************************************************************************
* Name:            xmlutil.php                                              *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function xml_loadfile($pathfile){
    return simplexml_load_file($pathfile);
}
function xml_xml2array($xml){
    $json=json_encode($xml);
    $arr=json_decode($json, TRUE);
    return $arr;
}
function xml_array2xml($arr, $root="xml"){
    $xml=new SimpleXMLElement("<?xml version=\"1.0\"?><$root></$root>");
    _array_to_xml($arr, $xml);
    return $xml;
}
function xml_xml2string($xml){
    return $xml->asXML();
}
function xml_string2xml($str){
    return simplexml_load_string($str);
}
function _array_to_xml($arr, &$xml) {
    foreach($arr as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode=$xml->addChild("$key");
                _array_to_xml($value, $subnode);
            }
            else{
                $subnode=$xml->addChild(intval($key));
                _array_to_xml($value, $subnode);
            }
        }
        else {
            $xml->addChild("$key", "$value");
        }
    }
}
function xml_xml2stream($xml, $mem=true){
    $writer=new XMLWriter();  

    if($mem)
        $writer->openMemory();
    else
        $writer->openURI('php://output');

    $writer->startDocument("1.0", "UTF-8");  
    $writer->setIndent(4);

    // CHIAMATA RICORSIVA
    _xml_to_stream($writer, $xml);
    
    $writer->endDocument();
    if($mem){
        return $writer->outputMemory(true);
    }
    else{
        $writer->flush();
        return "";
    }
}
function _xml_to_stream($writer, $elem){
    $writer->startElement($elem->getName());
    foreach($elem->attributes() as $attKey => $attValue){
        $writer->writeAttribute($attKey, $attValue);
    }
    if($elem->count()>0){
        foreach($elem as $key => $value){
            if(is_object($value)){
                _xml_to_stream($writer, $value);
            }
        }
    }
    else{
        $writer->text($elem);
    }
    $writer->endElement();
}
?>
