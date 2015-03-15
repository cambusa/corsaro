<?php 
/****************************************************************************
* Name:            mirror_upload.php                                        *
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

// SOTTODIRECTORY
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
    
// NOME FILE
if(isset($_POST["import"]))
    $import=$_POST["import"];
else
    $import="";
if(strpos($import,"..")!==false)
    $import="";
$import=utf8Decode($import);
$import=html_entity_decode($import);
$import=str_replace("´", "'", $import);
$tr=Array();
$tr["\'"]="'";
$tr["\\\""]="\"";
$tr["\\\\"]="\\";
$import=strtr($import, $tr);

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
else
    $sessionid="";

// INIZIALIZZO LE VARIABILI IN USCITA
$success=1;
$description="";

if(ext_validatesession($sessionid, true, "mirror")){
    if($env_name==$global_lastenvname){
        $dirtemp="";
        $env_strconn="";
        if(is_file($path_databases."_environs/temporary.php")){
            include($path_databases."_environs/temporary.php");
            $dirtemp=$env_strconn;
            $env_strconn="";
            if(is_file($path_databases."_environs/".$env_name.".php")){
                include($path_databases."_environs/".$env_name.".php");
                if(is_file($dirtemp.$import)){
                    if($dirtemp.$import!=$env_strconn.$path.$import){
                        copy($dirtemp.$import, $env_strconn.$path.$import);
                        unlink($dirtemp.$import);
                    }
                }
                else{
                    $success=0;
                    $description="File '$dirtemp.$import' doesn't exist.";
                }
            }
            else{
                $success=0;
                $description="Environ '".$env_name."' is not defined.";
            }
        }
        else{
            $success=0;
            $description="Environ 'temporary' is not defined.";
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