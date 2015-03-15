<?php 
/****************************************************************************
* Name:            appconfig.php                                            *
* Project:         Cambusa                                                  *
* Version:         1.69                                                     *
* Description:     Cambusa configuration file for applications              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
/***********************
| PERCORSO/URL CAMBUSA |
***********************/

$url_cambusa="../../cambusa/";

/****************************
| PERCORSO/URL APPLICATIONS |
****************************/

$url_applications="../../apps/";

/***************************
| PERCORSO/URL CUSTOMIZING |
***************************/

$url_customize="../../customize/";

/***************************
| ABILITAZIONE GOOGLE MAPS |
***************************/

$google_maps=true;
$google_zoom=16;
$google_lat=45.550084;
$google_lng=9.180665;

/***********************************
| RISORSE CAMBUSA - NON MODIFICARE |
***********************************/

include("cambusares.php");

// Non aggiungere accapi o spazi dopo ">"
?>