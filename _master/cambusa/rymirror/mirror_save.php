<?php 
/****************************************************************************
* Name:            mirror_save.php                                          *
* Project:         Cambusa/ryMirror                                         *
* Version:         1.69                                                     *
* Description:     Code Editor                                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryego/ego_validate.php";

if(isset($_POST["env"]))
    $env_name=strtolower($_POST["env"]);
else
    $env_name="";

if(isset($_POST["path"]))
    $path=$_POST["path"];
else
    $path="";

if(strpos($path,"..")!==false)
    $path="";

$path=utf8Decode($path);
$path=html_entity_decode($path);
$path=str_replace("´", "'", $path);
$tr=Array();
$tr["\'"]="'";
$tr["\\\""]="\"";
$tr["\\\\"]="\\";
$path=strtr($path, $tr);

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
else
    $sessionid="";

if(isset($_POST["content"]))
    $content=$_POST["content"];
else
    $content="";
$content=strtr($content, $tr);

// INIZIALIZZO LE VARIABILI IN USCITA
$success=1;
$description="";

if(ext_validatesession($sessionid, true, "mirror")){
    if($env_name==$global_lastenvname){
        if(is_file($path_databases."_environs/".$env_name.".php")){
            include($path_databases."_environs/".$env_name.".php");
            if(file_exists($env_strconn.$path)){
                $fp=fopen($env_strconn.$path, "wb");
                fwrite($fp, $content);
                fclose($fp);
            }
            else{
                $success=0;
                $description="File '$env_strconn$path' doesn't exist.";
            }
        }
        else{
            $success=0;
            $description="Environ ".$env_name." is not defined.";
        }
    }
    else{
        $success=0;
        $description="Permission denied.";
    }
}
else{
    $success=0;
    $description="Invalid session.";
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
print json_encode($j);
?>