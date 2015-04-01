<?php 
/****************************************************************************
* Name:            mirror_files.php                                         *
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

if(isset($_POST["sub"]))
    $subdir=$_POST["sub"];
else
    $subdir="";

if(strpos($subdir,"..")!==false)
    $subdir="";

$subdir=utf8Decode($subdir);
$subdir=html_entity_decode($subdir);
$subdir=str_replace("´", "'", $subdir);
$tr=Array();
$tr["\'"]="'";
$tr["\\\""]="\"";
$tr["\\\\"]="\\";
$subdir=strtr($subdir, $tr);

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
else
    $sessionid="";

// INIZIALIZZO LE VARIABILI IN USCITA
$success=1;
$description="";
$url="";
$path="";
$content=array();

if(ext_validatesession($sessionid, true, "mirror")){
    if($env_name==$global_lastenvname){
        if(is_file($path_databases."_environs/".$env_name.".php")){
            include($path_databases."_environs/".$env_name.".php");
            if(is_dir($env_strconn.$subdir)){
                if($dir=opendir($env_strconn.$subdir)) {
                    while(($file = readdir($dir)) !== false) { 
                        if($file!="." && $file!=".."){
                            $info=array();
                            $info["params"]=array();

                            // Determino nome, titolo, chiave
                            $name=escapize($file);
                            $key="";
                            
                            // Determino il tipo
                            if(is_dir($env_strconn.$subdir.$file)){
                                $info["type"]="folder";
                                $key=" :".strtolower($name);
                            }
                            else{
                                $info["type"]="file";
                                $key=strtolower($name);
                            }
                            
                            $info["name"]=$name;
                                
                            $content[$key]=$info;
                            unset($info);
                       }
                    }  
                    closedir($dir);
                    ksort($content);
                }
                $path=escapize($subdir);
            }
            else{
                $success=0;
                $description="Directory doesn't exist.";
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
$j["path"]=$path;
$j["content"]=$content;
print json_encode($j);

function escapize($t){
    $t=utf8Decode($t);
    $t=htmlentities($t);
    $t=str_replace("'","&acute;",$t);
    return $t;
}
?>