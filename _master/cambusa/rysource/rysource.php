<?php 
/****************************************************************************
* Name:            rysource.php                                             *
* Project:         Cambusa/rySource                                         *
* Version:         1.69                                                     *
* Description:     Remote file system browser                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."sysconfig.php";
include_once $tocambusa."rygeneral/unicode.php";
//include_once $tocambusa."rygeneral/writelog.php";

if(isset($_POST["env"]))
    $env_name=strtolower($_POST["env"]);
elseif(isset($_GET["env"]))
    $env_name=strtolower($_GET["env"]);
else
    $env_name="";

if(isset($_POST["sub"]))
    $subdir=$_POST["sub"];
elseif(isset($_GET["sub"]))
    $subdir=$_GET["sub"];
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

if(isset($_POST["sort"]))
    $sort=intval($_POST["sort"]);
elseif(isset($_GET["sort"]))
    $sort=intval($_GET["sort"]);
else
    $sort=1;
    
// INIZIO GESTIONE LOCALIZZAZIONE
$localize=false;

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
elseif(isset($_GET["sessionid"]))
    $sessionid=$_GET["sessionid"];
else
    $sessionid="";

if(isset($_POST["dbenv"]))
    $dbenv=strtolower($_POST["dbenv"]);
elseif(isset($_GET["dbenv"]))
    $dbenv=strtolower($_GET["dbenv"]);
else
    $dbenv="";

if($sessionid!="" && $dbenv){
    include_once $tocambusa."ryquiver/quiversex.php";
    
    // APRO IL DATABASE
    $maestro=maestro_opendb($dbenv, false);

    // VERIFICO IL BUON ESITO DELL'APERTURA
    if($maestro->conn!==false){
    
        // VALIDAZIONE CODICE DI SESSIONE
        if(qv_validatesession($maestro, $sessionid)){
            if($global_lastlanguage!="default"){
                try{
                    // APERTURA DATABASE BABEL
                    $maestro_babel=maestro_opendb($global_lastlanguage);
                    if($maestro_babel->conn!==false){
                        $localize=true;
                    }
                }catch(Exception $e){}
            }
        }
    }

    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
// FINE GESTIONE LOCALIZZAZIONE

// INIZIALIZZO LE VARIABILI IN USCITA
$success=1;
$description="";
$url="";
$path="";
$content=array();

if($env_name!=""){
    if(is_file($path_databases."_environs/".$env_name.".php")){
        include($path_databases."_environs/".$env_name.".php");
        if ($dir=opendir($env_strconn.$subdir)) {
            while(($file = readdir($dir)) !== false) { 
                if($file!="." && $file!=".."){
                    $info=array();
                    $info["params"]=array();

                    // Determino l'estensione
                    $path_parts = pathinfo($file);
                    if(isset($path_parts["extension"]))
                        $ext=strtolower($path_parts["extension"]);
                    else
                        $ext="";
                    
                    // Determino nome, titolo, chiave
                    $name=escapize($file);
                    $title=$name;
                    $key="";
                    $babelcode="";
                    if($opn=strpos($name, "]")){
                        if(substr($name, 0, 1)=="["){
                            $key="_".strtolower(substr($name, 1, $opn-1));
                            $title=substr($name, $opn+1);
                            // ESTRAGGO L'EVENTUALE BABELCODE
                            if($p=strpos($key, "-")){
                                $babelcode=strtoupper(trim(substr($key, $p+1)));
                                $key=substr($key, 0, $p);
                            }
                        }
                    }
                    
                    // Determino il tipo
                    if(is_dir($env_strconn.$subdir.$file)){
                        $info["type"]="folder";
                        if($key=="")
                            $key=" :".strtolower($name);
                    }
                    elseif($ext=="json"){
                        $info["type"]="form";
                        $title=substr($title,0,strlen($title)-5);
                        
                        // Leggo e decodifico il documento JSON
                        $json=json_decode(file_get_contents($env_strconn.$subdir.$file), true);
                        
                        if(isset($json["type"]))
                            $info["type"]=$json["type"];
                        if(isset($json["name"]))
                            $name=$json["name"];
                        if(isset($json["title"]))
                            $title=$json["title"];
                        if(isset($json["babelcode"]))
                            $babelcode=strtoupper($json["babelcode"]);
                        if($key==""){
                            if(isset($json["key"]))
                                $key=$json["key"];
                            if($key=="")
                                $key=strtolower($name);
                        }
                        if(isset($json["params"])){
                            $info["params"]=$json["params"];
                        }
                    }
                    else{
                        $info["type"]="file";
                        if($key=="")
                            $key=strtolower($name);
                    }
                    
                    // GESTIONE LOCALIZZAZIONE
                    if($localize){
                        if($babelcode!=""){
                            maestro_query($maestro_babel, "SELECT CAPTION FROM BABELITEMS WHERE [:UPPER(NAME)]='$babelcode'", $r);
                            if(count($r)>0){
                                $title=$r[0]["CAPTION"];
                                $info["params"]["title"]=$title;
                            }
                        }
                    }
                    
                    $info["name"]=$name;
                    $info["title"]=$title;
                        
                    $content[$key]=$info;
                    unset($info);
               }
            }  
            closedir($dir);
            if($sort)
                ksort($content);
        }
        $url=escapize($env_baseurl.$subdir);
        $path=escapize($env_strconn.$subdir);
    }
    else{
        $success=0;
        $description="Environ ".$env_name." is not defined.";
    }
}
else{
    $success=0;
    $description="No folder specified";
}

if($localize){
    // CHIUSURA DATABASE BABEL
    maestro_closedb($maestro_babel);
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
$j["url"]=$url;
$j["path"]=$path;
$j["content"]=$content;
print json_encode($j);

function escapize($t){
    $t=utf8Decode($t);
    $t=htmlentities($t);
    $t=str_replace("'","&acute;",$t);
    /*
    if(preg_match_all("/[\x80-\xFF]/",$t,$m)){
        $v=$m[0];
        $tr=array();
        for($i=0;$i<count($v);$i++){
            $k=$v[$i];
            if(!in_array($k,$tr))
               $tr[$k]="&#x".base_convert(ord($v[$i]),10,16).";";
        }
        $t=strtr($t, $tr);
    }
    */
    return $t;
}
?>