<?php 
/****************************************************************************
* Name:            sysconfig.php                                            *
* Project:         Cambusa                                                  *
* Version:         1.69                                                     *
* Description:     Cambusa configuration file                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

include_once "solveroot.php";

/**************
| URL DOMINIO |
**************/

$url_base=installationURL();

/***********************
| RADICE INSTALLAZIONE |
***********************/

$path_root=installationPATH();

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

$safe_extensions="pdf|zip|jpg|jpeg|gif|png|svg|htm|html|pht|txt|mp3|mp4|wav|avi|odf|ods|odt|odp|doc|docx|xls|xlsx|p7m|qvr";

/********************************************
| CONTROLLO DELL'IP IN VALIDAZIONE SESSIONI |
********************************************/

$check_sessionip=false;

/*********************
| LINGUA PREDEFINITA |
*********************/

$config_defaultlang="default";

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

/********************
| SESSIONE PUBBLICA |
********************/

$public_sessionid="";
$ryque_sessionid="";
if(is_file($path_databases."_configs/session.php")){
    include_once $path_databases."_configs/session.php";
}

// Non aggiungere accapi o spazi dopo ">"
?>