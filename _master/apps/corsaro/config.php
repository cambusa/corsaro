<?php
/****************************************************************************
* Name:            config.php                                               *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

// ***************************************************************
// CORSARO
// ***************************************************************

// NOME DELL'AZIENDA CLIENTE
$companyname="RudyZ";

// NOME DEL DISTRIBUTORE
$copyright_dealer="";

// DATI DI VERSIONE
include_once "version.php";

// ***************************************************************
// FILIBUSTER
// ***************************************************************

// DATI REMOTI
//$filibuster_host="http://www.rudyz.net/apps/ryquiver/";

// DATI LOCALI
$filibuster_host="";

// AMBIENTE PREDEFINITO
$filibuster_environ="flb_rudyz";

// SITO PREDEFINITO
$filibuster_site="rudyz";

// DIMENSIONE MASSIMA DOCUMENTO PER SINTESI VOCALE AD ALTA QUALITA'
$filibuster_sizeHQ=2500;

// ***************************************************************
// MATHJAX
// ***************************************************************

// PERCORSO
$mathjax_path="http://www.rudyz.net/cambusa/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML";

?>