<?php
/****************************************************************************
* Name:            food4voice.php                                           *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tocambusa="../../cambusa/";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."rygeneral/post_request.php";
include_once "food4_library.php";

set_time_limit(0);

if(isset($_POST["env"]))
    $env=$_POST["env"];
elseif(isset($_GET["env"]))
    $env=$_GET["env"];
else
    $env="";

if(isset($_POST["site"]))
    $site=ryqEscapize($_POST["site"]);
elseif(isset($_GET["site"]))
    $site=ryqEscapize($_GET["site"]);
else
    $site="";

if(isset($_POST["id"]))
    $id=ryqEscapize($_POST["id"]);
elseif(isset($_GET["id"]))
    $id=ryqEscapize($_GET["id"]);
else
    $id="";

$jret=array();
$jret["success"]=0;
$jret["url"]="";
$urlvoice="";
    
if($env!="" && $site!=""){
    // DETERMINO IL CONTENUTO
    $SITEID="";
    if(solvecontents($env, $site, $id, $text, $lang, $gender)){
        // PERCORSO MP3
        $pathname="../../customize/_voice/$env-$id.mp3";
        $urlvoice=food4voice()."$env-$id.mp3";
        // PERCORSO CRC
        $pathcrc="../../customize/_voice/$env-$id.crc";
        if(is_file($pathcrc))
            $crcfile=file_get_contents($pathcrc);
        else
            $crcfile="";
        $crctext="CRC".crc32("$lang\n$gender\n$text");
        $download=false;
        if(is_file($pathname)){
            if($crcfile==$crctext){
                // ESITO POSITIVO
                $jret["success"]=1;
                $jret["url"]=$urlvoice;
            }
            else{
                $download=true;
            }
        }
        else{
            $download=true;
        }
        if($download){
            if(strlen($text)>2000){
                $postdata = array(
                    'text' => $text,
                    'lang' => $lang,
                    'gn' => $gender,
                    'interface' => "full"
                );
                $c=do_post_request("http://vozme.com/text2voice.php", $postdata);
                if(preg_match("/<source src=\"(.+\.mp3)\"/", $c, $m)){
                    // SCRIVO MP3
                    $buff=@file_get_contents("http://vozme.com/".$m[1]);
                    $fp=fopen($pathname, "wb");
                    fwrite($fp, $buff);
                    fclose($fp);
                    // SCRIVO CRC
                    $fp=fopen($pathcrc, "wb");
                    fwrite($fp, $crctext);
                    fclose($fp);
                    // ESITO POSITIVO
                    $jret["success"]=1;
                    $jret["url"]=$urlvoice;
                }
            }
            else{
                $speed="0";
                switch($lang){
                case "it":
                    $lang="Italian";
                    $speed="1";
                    if($gender=="ml")
                        $gender="16";
                    else
                        $gender="6";
                    break;
                case "en":
                    $lang="British English";
                    if($gender=="ml")
                        $gender="11";
                    else
                        $gender="12";
                    break;
                case "es":
                    $lang="Spanish";
                    if($gender=="ml")
                        $gender="10";
                    else
                        $gender="13";
                    break;
                }
                $postdata = array(
                    'input_text' => $text,
                    'action' => "process_text",
                    'language' => $lang,
                    'voice' => $gender,
                    'speed' => $speed
                );
                $c=do_post_request("http://www.fromtexttospeech.com/", $postdata);
                if(preg_match("/<a href='(.+\.mp3)'/", $c, $m)){
                    // SCRIVO MP3
                    $buff=@file_get_contents("http://www.fromtexttospeech.com/".$m[1]);
                    $fp=fopen($pathname, "wb");
                    fwrite($fp, $buff);
                    fclose($fp);
                    // SCRIVO CRC
                    $fp=fopen($pathcrc, "wb");
                    fwrite($fp, $crctext);
                    fclose($fp);
                    // ESITO POSITIVO
                    $jret["success"]=1;
                    $jret["url"]=$urlvoice;
                }
            }
        }
    }
}
array_walk_recursive($jret, "maestro_escapize");
print json_encode($jret);

function solvecontents($env, $site, &$id, &$text, &$lang, &$gender){
    global $SITEID;
    $ret=false;
    // APRO IL DATABASE
    $maestro=maestro_opendb($env, false);
    // INIZIALIZZO L'OUTPUT
    $text="";
    $gender="fm";
    $lang="it";
    // DETERMINO IL SITO
    maestro_query($maestro, "SELECT * FROM QW_WEBSITES WHERE [:UPPER(NAME)]='".strtoupper($site)."'", $r);
    if(count($r)==1){
        $SITEID=$r[0]["SYSID"];
        $DEFAULTID=$r[0]["DEFAULTID"];
        if($id==""){
            $id=$DEFAULTID;
        }
        $URLCORSARO=food4corsaro();
        maestro_query($maestro, "SELECT DESCRIPTION,REGISTRY,ABSTRACT,CONTENTTYPE,REFERENCE FROM QW_WEBCONTENTS WHERE SYSID='$id'", $r);
        if(count($r)==1){
            $TITLECONTENT=$r[0]["DESCRIPTION"];
            $CONTENTTYPE=$r[0]["CONTENTTYPE"];
            $REFERENCE=$r[0]["REFERENCE"];
            if(strlen($REFERENCE)>=4){
                $gender=substr($REFERENCE, 0, 2);
                $lang=substr($REFERENCE, 2, 2);
            }
            $buff=file_get_contents("$URLCORSARO/food4container.php?env=$env&site=$site&id=$id");
            if(substr($buff, 0, 3)=="[F]"){
                $json=json_decode(substr($buff, 3));
                foreach($json as $key => $value){
                    $SYSID=$value->contentid;
                    $buff=file_get_contents("$URLCORSARO/food4container.php?env=$env&site=$site&id=$SYSID");
                    $text.=substr($buff, 3)."\r\n";
                }
            }
            else{
                $text=substr($buff, 3);
            }
            $text=preg_replace("/<[bh]r\/?>/i", "\r\n", $text);
            $text=preg_replace("/<\/li>/i", "\r\n", $text);
            $text=preg_replace("/<p>/i", "\r\n", $text);
            $text=preg_replace("/<div[^<>]+filibuster-date[^<>]+>.+?<\/div>/i", "\r\n", $text);
            $text=preg_replace("/<div[^<>]*>/i", "\r\n", $text);
            $text=preg_replace("/[\r\n]+/i", "\r\n", $text);
            $text=strip_tags($text);
            $text=html_entity_decode($text, ENT_QUOTES, "UTF-8");
            $text=preg_replace("/ +/i", " ", $text);
            $text=preg_replace("/['´~]/u", "", $text);
            $ret=true;
        }
    }
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
    return $ret;
}
function food4voice(){
    $s=food4containerURL();
    $p=strpos($s, "/ryquiver");
    if($p!==false){
        $s=substr($s, 0, $p-5);
    }
    $s.="/customize/_voice/";
    return $s;
}
function food4corsaro(){
    $s=food4containerURL();
    $p=strpos($s, "/ryquiver");
    if($p!==false){
        $s=substr($s, 0, $p+9);
    }
    return $s;
}
?>