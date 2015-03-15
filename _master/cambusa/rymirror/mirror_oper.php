<?php 
/****************************************************************************
* Name:            mirror_save.php                                          *
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

if(isset($_POST["action"]))
    $action=$_POST["action"];
else
    $action="";

if(isset($_POST["newname"]))
    $newname=$_POST["newname"];
else
    $newname="";

if(isset($_POST["target"]))
    $target=$_POST["target"];
else
    $target="";

// INIZIALIZZO LE VARIABILI IN USCITA
$success=1;
$description="";

if(ext_validatesession($sessionid, true, "mirror")){
    if($env_name==$global_lastenvname){
        if(is_file($path_databases."_environs/".$env_name.".php")){
            include($path_databases."_environs/".$env_name.".php");
            switch($action){
            case "newfile":
                $n=$env_strconn.$path."_newfile";
                while(is_file($n)) {
                    $n.=rand(10, 99);
                }
                $buff="";
                $fp=fopen($n, "wb");
                fwrite($fp, $buff);
                fclose($fp);
                break;

            case "newfolder":
                $n=$env_strconn.$path."_newdir";
                while(is_dir($n)) {
                    $n.=rand(10, 99);
                }
                mkdir($n);
                break;

            case "rename":
                $old=$env_strconn.$path;
                $new=dirname($old)."/".$newname;
                if(is_dir($old)){
                    if(!is_dir($new)){
                        rename($old, $new);
                    }
                    else{
                        $success=0;
                        $description="'$new' already exists.";
                    }
                }
                elseif(is_file($old)){
                    if(!is_file($new)){
                        rename($old, $new);
                    }
                    else{
                        $success=0;
                        $description="'$new' already exists.";
                    }
                }
                else{
                    $success=0;
                    $description="'$old' doesn't exist.";
                }
                break;
                
            case "saveas":
                $old=$env_strconn.$path;
                $new=dirname($old)."/".$newname;
                if(is_dir($old)){
                    if(!is_dir($new)){
                        rename($old, $new);
                    }
                    else{
                        $success=0;
                        $description="'$new' already exists.";
                    }
                }
                elseif(is_file($old)){
                    if(!is_file($new)){
                        copy($old, $new);
                    }
                    else{
                        $success=0;
                        $description="'$new' already exists.";
                    }
                }
                else{
                    $success=0;
                    $description="'$old' doesn't exist.";
                }
                break;
                
            case "copy":
                if(strpos($target, $path)!==0){
                    if(is_dir($env_strconn.$target)){
                        if(is_dir($env_strconn.$path)){
                            $new=$env_strconn.$target.basename($path);
                            if(!is_dir($new))
                                mkdir($new);
                            mirror_xcopy($env_strconn.$path, $new."/");
                        }
                        elseif(is_file($env_strconn.$path)){
                            $new=$env_strconn.$target.basename($path);
                            copy($env_strconn.$path, $new);
                        }
                        else{
                            $success=0;
                            $description="'$path' doesn't exist.";
                        }
                    }
                    else{
                        $success=0;
                        $description="'$target' doesn't exist.";
                    }
                }
                else{
                    $success=0;
                    $description="'$target' can't go under '$path'.";
                }
                break;
                
            case "delete":
                if(is_dir($env_strconn.$path)){
                    mirror_xdelete($env_strconn.$path);
                }
                elseif(is_file($env_strconn.$path)){
                    unlink($env_strconn.$path);
                }
                else{
                    $success=0;
                    $description="'$path' doesn't exist.";
                }
                break;
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

function mirror_xdelete($trg){
    if($direc=@opendir($trg)){
        while(($file=readdir($direc))!==false){
            if($file!="." && $file!=".."){
                if(is_file($trg.$file))
                    @unlink($trg.$file);
                elseif(is_dir($trg.$file))
                    mirror_xdelete($trg.$file."/");
            }
        }
        rmdir($trg);
    }
    @closedir($direc);
}

function mirror_xcopy($src, $trg){
    if(!is_dir($trg))
        mkdir($trg);
    if($direc=@opendir($src)){
        while(($file=readdir($direc))!==false){
            if($file!="." && $file!=".."){
                if(is_file($src.$file))
                    copy($src.$file, $trg.$file);
                elseif(is_dir($src.$file))
                    mirror_xcopy($src.$file."/", $trg.$file."/");
            }
        }
    }
    @closedir($direc);
}
?>