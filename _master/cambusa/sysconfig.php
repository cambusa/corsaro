<?php 
/****************************************************************************
* Name:            sysconfig.php                                            *
* Project:         Cambusa                                                  *
* Version:         1.00                                                     *
* Description:     Cambusa configuration file                               *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
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

/********************
| PERCORSO DATABASE |
********************/

/*
if(strpos($url_base, "localhost")!==false){
    if(strpos($url_base, "_distrib")!==false)
        $safe_database=false;
    else
        $safe_database=true;
}
else{
    $safe_database=false;
}
*/
$safe_database=false;

if($safe_database)
    $path_databases="D:/WebData/databases/";
else
    $path_databases=$path_root."databases/";

/*********************
| PERCORSO CUSTOMIZE |
*********************/

$path_customize=$path_root."customize/";
$url_customize=$url_base."customize/";

/************************
| PERCORSO APPLICATIONS |
************************/

$path_applications=$path_root."apps/";
$url_applications=$url_base."apps/";

/**************
| URL CAMBUSA |
**************/

$path_cambusa=$path_root."cambusa/";
$url_cambusa=$url_base."cambusa/";

/************
| URL MONAD |
************/

//$url_rymonad="http://www.rudyz.net/cambusa/rymonad/";
$url_rymonad="";

/********************
| SESSIONE PUBBLICA |
********************/

$public_sessionid="";
$ryque_sessionid="";
if(is_file($path_databases."_configs/session.php")){
    include_once $path_databases."_configs/session.php";
}

/*****************************
| INDIRIZZO EMAIL DI SISTEMA |
*****************************/

$postmaster_mail="postmaster@rudyz.net";

/************************
| ESTENSIONI CONSENTITE |
************************/

$safe_extensions="pdf|zip|jpg|jpeg|gif|png|svg|htm|html|pht|txt|mp3|mp4|wav|avi|odf|ods|odt|odp|doc|docx|xls|xlsx|p7m|qvr";

/********************************************
| CONTROLLO DELL'IP IN VALIDAZIONE SESSIONI |
********************************************/

$check_sessionip=false;

// Non aggiungere accapi o spazi dopo ">"
?>