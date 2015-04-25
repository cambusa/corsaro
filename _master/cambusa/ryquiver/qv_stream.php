<?php 
/****************************************************************************
* Name:            qv_stream.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
function qv_stream($maestro, $data){
    global $babelcode, $babelparams;
    global $url_base, $path_root;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $args=array();
        $sql="";
            
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        $infoenv=qv_environs($maestro);
        $dirtemp=$infoenv["dirtemp"];
        $dirattach=$infoenv["dirattach"];

        $path=date("YmdHis");
        for($i=1; $i<=2; $i++){
            $path.=monadrand();
        }
        while(file_exists($dirtemp.$path.".xml")){
            $path=substr($path, 0, 18).monadrand();
        }
        $path=$dirtemp.$path.".xml";
        
        // URL
        $url=str_replace($path_root, $url_base, $path);
        
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
        
        $writer=new XMLWriter();  
        $writer->openURI($path);
        $writer->startDocument("1.0", "UTF-8");  
        $writer->setIndent(4);
        
        // APRO ROOT
        $writer->startElement("xml");
        
        $res=maestro_unbuffered($maestro, $sql);
        while( $row=maestro_fetch($maestro, $res) ){
            $writer->startElement("array");
            foreach($row as $key => $value){
                if($value!=""){
                    if(!mb_check_encoding($value, "UTF-8")){
                        // CI SONO CARATTERI NON UNICODE
                        $value=utf8_encode($value);
                    }
                }
                $writer->writeAttribute($key, $value);
            }
            $writer->endElement();
        }
        maestro_free($maestro, $res);
        
        // CHIUDO ROOT
        $writer->endElement();

        $writer->endDocument();
        $writer->flush();
        
        $babelparams["URL"]=$url;
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