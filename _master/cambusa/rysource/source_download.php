<?php
/****************************************************************************
* Name:            source_download.php                                      *
* Project:         Cambusa/rySource                                         *
* Version:         1.70                                                     *
* Description:     Remote file system browser                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include("../sysconfig.php");
include("../rygeneral/unicode.php");
include("../rygeneral/writelog.php");

if(isset($_GET['file'])){
    $base="";
    /***********************
    | DETERMINO L'AMBIENTE |
    ***********************/
    if(isset($_GET['env']))
        $env_name=$_GET['env'];
    else
        $env_name="";
    if($env_name!=""){
        /*******************
        | LEGGO L'AMBIENTE |
        *******************/
        $env_validation="";

        if(is_file($path_databases."_environs/".$env_name.".php")){

            include($path_databases."_environs/".$env_name.".php");

            if($env_provider=="filesystem"){
                /************************************
                | L'AMBIENTE E' DI TIPO FILE SYSTEM |
                ************************************/
                if($env_validation==""){
                    $base=realpath($env_strconn);
                }
                else{
                    /**************************************************************
                    | L'AMBIENTE NON E' PUBBLICO: SI RICHIEDE UNA SESSIONE VALIDA |
                    **************************************************************/
                    include_once "../ryquiver/quiversex.php";
                    
                    // APRO IL DATABASE
                    $maestro=maestro_opendb($env_validation, false);

                    // VERIFICO IL BUON ESITO DELL'APERTURA
                    if($maestro->conn!==false){
                        /**********************************
                        | SI RICHIEDE UNA SESSIONE VALIDA |
                        **********************************/
                        if(isset($_GET['sessionid'])){
                            $sessionid=$_GET['sessionid'];
                            if(qv_validatesession($maestro, $sessionid)){
                                $base=realpath($env_strconn);
                            }
                        }
                    }
                    // CHIUDO IL DATABASE
                    maestro_closedb($maestro);
                }
            }
            else{
                /**********************************************************************
                | L'AMBIENTE E' DI TIPO DATABASE: REPERSICO IL PARAMETRO _TEMPENVIRON |
                **********************************************************************/
                include_once "../ryquiver/quiversex.php";
                
                // APRO IL DATABASE
                $maestro=maestro_opendb($env_name, false);

                // VERIFICO IL BUON ESITO DELL'APERTURA
                if($maestro->conn!==false){
                    /**********************************
                    | SI RICHIEDE UNA SESSIONE VALIDA |
                    **********************************/
                    if(isset($_GET['sessionid'])){
                        $sessionid=$_GET['sessionid'];
                        if(qv_validatesession($maestro, $sessionid)){
                            maestro_query($maestro, "SELECT DATAVALUE FROM QVSETTINGS WHERE NAME='_TEMPENVIRON'", $r);
                            if(count($r)>0){
                                $base=solve_directory($r[0]["DATAVALUE"]);
                            }
                        }
                    }
                }
                // CHIUDO IL DATABASE
                maestro_closedb($maestro);
            }
        }
    }
    else{
        /******************************************************************************************
        | L'AMBIENTE NON E' IMPOSTATO: LE UNICHE DIRECTORY CONSENTITE SONO QUELLE SOTTO CUSTOMIZE |
        ******************************************************************************************/
        $base=realpath($path_customize);
    }
    if($base!=""){
        // PERFEZIONAMENTO BASE
        if(substr($base,-1)!="/")
            $base.="/";
        // DETERMINAZIONE FILE
        $file=$_GET['file'];
        $file=utf8Decode($file);
        $file=html_entity_decode($file);
        $file=str_replace("´", "'", $file);
        $tr=Array();
        $tr["\'"]="'";
        $tr["\\\""]="\"";
        $tr["\\\\"]="\\";
        $file=strtr($file,$tr);
        if(strpos($file, "..")===false){
            // NORMALIZZO LA BASE
            $base=str_replace("\\", "/", $base);
            // NORMALIZZO IL FILE
            $file=str_replace("\\", "/", $file);
            if(substr($file,0,1)!="/" && substr($file,1,2)!=":/"){
                $file=$base.$file;
            }
            // RISOLVO E RINORMALIZZO
            $file=realpath($file);
            $file=str_replace("\\", "/", $file);
            if(strpos(strtolower($file), strtolower($base))!==false){
                $path_parts=pathinfo($file);
                $base=$path_parts["basename"];
                $ext=strtolower($path_parts["extension"]);
                if(is_file($file)){
                    // Download
                    if(strpos("|".$safe_extensions."|", "|".$ext."|")!==false){
                        header("Pragma: public");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                        header("Cache-Control: private",false);
                        header("Content-Type: application/octet-stream");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename=\"".$base."\";" );
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Length: ".filesize($file));
                        header('Connection: close');

                        readfile($file);
                    }
                    else{
                        manage_error("File extension not allowed!");
                    }
                }
                else{
                    manage_error("[ $file ] not found!");
                }
            }
            else{
                manage_error("[ $file ] access denied!");
            }
        }
        else{
            manage_error("Access denied!");
        }
    }
    else{
        manage_error("Wrong parameters!");
    }
}
else{
    manage_error("Insufficient parameters!");
}
exit(0);

function solve_directory($temp){
    global $path_customize,$path_cambusa,$path_databases,$path_applications;

    $temp=str_replace("@customize/", $path_customize, $temp);
    $temp=str_replace("@cambusa/", $path_cambusa, $temp);
    $temp=str_replace("@databases/", $path_databases, $temp);
    $temp=str_replace("@apps/", $path_applications, $temp);
    return realpath($temp);
}

function manage_error($mess){
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: application/octet-stream");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=\"error.txt\";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".strlen($mess));
    header('Connection: close');
    print $mess;
}
?>