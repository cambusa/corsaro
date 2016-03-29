<?php 
/****************************************************************************
* Name:            sysconfig.php                                            *
* Project:         Cambusa                                                  *
* Version:         1.70                                                     *
* Description:     Cambusa configuration file                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);

$curdir=realpath(dirname(__FILE__));
$curdir=str_replace("\\", "/", $curdir);
include_once $curdir."/solveroot.php";

/*******************
| VERSIONE CAMBUSA |
*******************/

$cambusa_version="v1.70";

/**************
| URL DOMINIO |
**************/

$url_base=installationURL();

/***********************
| RADICE INSTALLAZIONE |
***********************/

$path_root=installationPATH();

/*********************************************************
| BASE RELATIVA DA DEDURRE (O ASSEGNARE IN _cambusa.php) |
/********************************************************/

$relative_base="#";

/************************
| PERCORSO APPLICATIONS |
************************/

$path_applications=$path_root."apps/";
$url_applications=$url_base."apps/";

/*******************
| PERCORSO CAMBUSA |
*******************/

$path_cambusa=$path_root."cambusa/";
$url_cambusa=$url_base."cambusa/";

/*********************
| PERCORSO CUSTOMIZE |
*********************/

$path_customize=$path_root."customize/";
$url_customize=$url_base."customize/";

/********************************
| PERCORSO DATABASE PREDEFINITO |
********************************/

$path_databases=$path_root."databases/";

/************
| URL MONAD |
************/

//$url_rymonad="http://www.rudyz.net/cambusa/rymonad/";
$url_rymonad="";

/*****************************
| INDIRIZZO EMAIL DI SISTEMA |
*****************************/

$postmaster_mail="";

/************************
| ESTENSIONI CONSENTITE |
************************/

$safe_extensions="pdf|zip|jpg|jpeg|gif|png|svg|ico|htm|html|pht|txt|mp3|mp4|wav|avi|mid|odf|ods|odt|odp|doc|docx|xls|xlsx|p7m|qvr|xml";

/********************************************
| CONTROLLO DELL'IP IN VALIDAZIONE SESSIONI |
********************************************/

$check_sessionip=true;

/*********************
| LINGUA PREDEFINITA |
*********************/

$config_defaultlang="default";
$config_selflearning="";

/******************
| VERSIONE SQLITE |
******************/

$sqlite3_enabled=true;
if(floatval(phpversion())<5.3){
    $sqlite3_enabled=false;
}

/********************
| PERSONALIZZAZIONI |
********************/

if(is_file($path_customize."_cambusa.php")){
    include $path_customize."_cambusa.php";
}

/****************************************
| EVENTUALE AGGIUSTAMENTO BASE RELATIVA |
****************************************/

if($relative_base=="#"){
    $relative_base=preg_replace("@\w+://[^/]+@i", "", $url_base);
    if($relative_base==""){
        $relative_base="/";
    }
}

/********************
| SESSIONE PUBBLICA |
********************/

$public_sessionid="";
$ryque_sessionid="";
if(is_file($path_databases."_configs/session.php")){
    include $path_databases."_configs/session.php";
}

// Non aggiungere accapi o spazi dopo ">"
?>