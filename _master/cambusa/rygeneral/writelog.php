<?php
/****************************************************************************
* Name:            writelog.php                                             *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."sysconfig.php";    
$pointerlog=false;
$writeanylog=false;
$bufferpathlog="";
$freezelog=false;
function writelog($text){
    global $path_databases;
    try{
        if(is_array($text)){
            $text=implode("\r\n",$text);
        }
        $filename=date("Y-m-d-H-i-s");
        $pathname=$path_databases."_syslog/".$filename;
        while(is_file($pathname.".txt")){
            $suffix="-".rand(10,99);
            $filename.=$suffix;
            $pathname.=$suffix;
        }
        $filename.=".txt";
        $pathname.=".txt";
        $fp=fopen($pathname,"w");
        fwrite($fp,$text);
        fclose($fp);
    }
    catch(Exception $e){
        $filename="";
    }
    return $filename;
}
function log_unique($prefix){
    global $path_databases,$pointerlog;
    try{
        $filename=$prefix.date("Y-m-d-H-i-s");
        $pathname=$path_databases."_syslog/".$filename;
        while(is_file($pathname.".txt")){
            $suffix="-".rand(10,99);
            $filename.=$suffix;
            $pathname.=$suffix;
        }
        $filename.=".txt";
        return $filename;
    }
    catch(Exception $e){
        return "";
    }
}
function log_open($filename){
    global $path_databases,$pointerlog,$bufferpathlog;
    try{
        $bufferpathlog=$path_databases."_syslog/".$filename;
        $pointerlog=fopen($bufferpathlog,"wb");
    }
    catch(Exception $e){
        $pointerlog=false;
    }
}
function log_write($text){
    global $pointerlog,$bufferpathlog,$freezelog;
    try{
        if($pointerlog!==false){
            if($freezelog){
                $freezelog=false;
            }
            else{
                $text.="\r\n";
                fwrite($pointerlog,$text);
                $bufferpathlog="";
            }
        }
    }
    catch(Exception $e){
        $pointerlog=false;
    }
}
function log_close(){
    global $pointerlog,$bufferpathlog;
    try{
        fclose($pointerlog);
        $pointerlog=false;
        if($bufferpathlog!="")
            @unlink($bufferpathlog);
        $writeanylog=false;
    }
    catch(Exception $e){
        $pointerlog=false;
    }
}
?>