<?php 
/****************************************************************************
* Name:            mirror_load.php                                          *
* Project:         Cambusa/ryMirror                                         *
* Version:         1.69                                                     *
* Description:     Code Editor                                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
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

// INIZIALIZZO LE VARIABILI IN USCITA
$success=1;
$description="";
$content="";

if(ext_validatesession($sessionid, true, "mirror")){
    if($env_name==$global_lastenvname){
        if(is_file($path_databases."_environs/".$env_name.".php")){
            include($path_databases."_environs/".$env_name.".php");
            if(file_exists($env_strconn.$path)){
                $content=file_get_contents($env_strconn.$path);
                if(htmlentities($content, ENT_NOQUOTES, "UTF-8", false)==""){
                    // CI SONO CARATTERI NON UNICODE
                    $content=utf8_encode($content);
                }
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
$j["content"]=$content;
print json_encode($j);
?>