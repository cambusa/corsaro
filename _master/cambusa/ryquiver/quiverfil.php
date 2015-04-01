<?php 
/****************************************************************************
* Name:            quiverfil.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

function qv_environs($maestro, &$dirtemp, &$dirattach, &$urltemp="", &$urlattach=""){
    global $babelcode, $babelparams;
    global $path_databases, $path_customize, $url_customize, $path_root, $url_base, $safe_extensions;
    // DETERMINO SORGENTE E DESTINAZIONE DEL FILE DA IMPORTARE
    $tempenviron=qv_setting($maestro, "_TEMPENVIRON","");
    if($tempenviron!=""){
        if(is_file($path_databases."_environs/".$tempenviron.".php")){
            $env_strconn="";
            $env_baseurl="";
            include($path_databases."_environs/".$tempenviron.".php");
            $dirtemp=$env_strconn;
            $urltemp=$env_baseurl;
        }
        else{
            $babelcode="QVERR_NOENVIRON";
            $b_params=array("_TEMPENVIRON" => $tempenviron);
            $b_pattern="Directory temporanea inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    else{
        $babelcode="QVERR_ENVIRON";
        $b_params=array("setting" => "_TEMPENVIRON");
        $b_pattern="Directory temporanea non specificata nei parametri globali";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }

    $fileenviron=qv_setting($maestro, "_FILEENVIRON","");
    if($fileenviron!=""){
        if(is_file($path_databases."_environs/".$fileenviron.".php")){
            $env_strconn="";
            $env_baseurl="";
            include($path_databases."_environs/".$fileenviron.".php");
            $dirattach=$env_strconn;
            $urlattach=$env_baseurl;
        }
        else{
            $babelcode="QVERR_NOENVIRON";
            $b_params=array("_FILEENVIRON" => $fileenviron);
            $b_pattern="Directory documenti inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    else{
        $babelcode="QVERR_ENVIRON";
        $b_params=array("setting" => "_FILEENVIRON");
        $b_pattern="Directory documenti non specificata nei parametri globali";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_makepath($base, $subpath){
    // SCOMPONGO IL PERCORSO
    $v=explode("/", $subpath);
    for($i=0; $i<count($v); $i++){
        if($v[$i]!=""){
            $base.=$v[$i];
            if(!is_dir($base))
                mkdir($base);
            $base.="/";
        }
    }
}

function qv_solvesubpath($maestro, $SYSID, &$SUBPATH){
    global $babelcode, $babelparams;
    maestro_query($maestro,"SELECT SUBPATH FROM QVFILES WHERE SYSID='$SYSID'",$r);
    if(count($r)==1){
        $SUBPATH=$r[0]["SUBPATH"];
    }
    else{
        $babelcode="QVERR_NOFILE";
        $b_params=array("SYSID" => $SYSID, "table" => "QVFILES");
        $b_pattern="File non trovato";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_tripletattach($maestro, $data, &$TABLENAME, &$RECORDID, &$FILEID){
    global $babelcode, $babelparams;
    
    $TABLENAME="";
    $RECORDID="";
    $FILEID="";
    
    // DETERMINO TABLENAME
    if(isset($data["TABLENAME"])){
        $TABLENAME=ryqEscapize($data["TABLENAME"]);
    }
    else{
        $babelcode="QVERR_TABLENAME";
        $b_params=array();
        $b_pattern="Nome tabella non specificato";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
        
    // DETERMINO RECORDID
    if(isset($data["RECORDNAME"])){
        $RECORDNAME=ryqEscapize($data["RECORDNAME"]);
        maestro_query($maestro,"SELECT SYSID FROM $TABLENAME WHERE [:UPPER(NAME)]='".strtoupper($RECORDNAME)."'",$r);
        if(count($r)==1){
            $RECORDID=$r[0]["SYSID"];
        }
        else{
            $babelcode="QVERR_NORECNAME";
            $b_params=array("NAME" => $RECORDNAME, "TABLENAME" => $TABLENAME);
            $b_pattern="Nome [{1}] non trovato in [{2}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    elseif(isset($data["RECORDID"])){
        $RECORDID=ryqEscapize($data["RECORDID"]);
        maestro_query($maestro,"SELECT SYSID FROM $TABLENAME WHERE SYSID='$RECORDID'",$r);
        if(count($r)==0){
            $babelcode="QVERR_NORECID";
            $b_params=array("SYSID" => $RECORDID, "TABLENAME" => $TABLENAME);
            $b_pattern="Identificatore [{1}] non trovato in [{2}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }

    // DETERMINO FILEID
    if(isset($data["FILENAME"])){
        $FILENAME=ryqEscapize($data["FILENAME"]);
        maestro_query($maestro,"SELECT SYSID FROM QVFILES WHERE [:UPPER(NAME)]='".strtoupper($FILENAME)."'",$r);
        if(count($r)==1){
            $FILEID=$r[0]["SYSID"];
        }
        else{
            $babelcode="QVERR_NOFILENAME";
            $b_params=array("NAME" => $FILENAME, "TABLENAME" => "QVFILES");
            $b_pattern="Nome [{1}] non trovato in [{2}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    elseif(isset($data["FILEID"])){
        $FILEID=ryqEscapize($data["FILEID"]);
        maestro_query($maestro,"SELECT SYSID FROM QVFILES WHERE SYSID='$FILEID'",$r);
        if(count($r)==0){
            $babelcode="QVERR_NOFILEID";
            $b_params=array("SYSID" => $RECORDID, "TABLENAME" => "QVFILES");
            $b_pattern="Identificatore [{1}] non trovato in [{2}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
}
function qv_fileuniquity($dir, $path){
    $pathinfo=pathinfo($path);
    $filename=$pathinfo['filename'];
    if(isset($pathinfo['extension']))
        $ext=".".$pathinfo['extension'];
    else
        $ext="";
    while(file_exists($dir.$filename.$ext)) {
        $filename.=rand(10, 99);
    }
    return $filename.$ext;
}
?>