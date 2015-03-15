<?php
/****************************************************************************
* Name:            food4_library.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function food4containerURL(){
    $pageURL='http';
    if(isset($_SERVER["HTTPS"])){
        if($_SERVER["HTTPS"]=="on"){
            $pageURL.="s";
        }
    }
    $pageURL.="://";
    if($_SERVER["SERVER_PORT"]!="80"){
        $pageURL.=$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }
    else{
        $pageURL.=$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
function flb_dirattachment($maestro, &$dirattach, &$urlattach){
    global $path_databases, $url_base, $path_root;
    $dirattach="../../databases/attachments/";
    $urlattach=$url_base."databases/attachments/";
    maestro_query($maestro,"SELECT DATAVALUE FROM QVSETTINGS WHERE NAME='_FILEENVIRON'", $r);
    if(count($r)==1){
        $fileenviron=$r[0]["DATAVALUE"];
        if($fileenviron!=""){
            if(is_file($path_databases."_environs/".$fileenviron.".php")){
                $env_strconn="";
                $env_baseurl="";
                include($path_databases."_environs/".$fileenviron.".php");
                $dirattach=$env_strconn;
                $urlattach=$env_baseurl;
            }
        }
    }
}
function flb_urlquiver(){
    $s=food4containerURL();
    $p=strrpos($s, "/ryquiver");
    if($p!==false){
        $s=substr($s, 0, $p);
    }
    $s.="/ryquiver/";
    return $s;
}
?>