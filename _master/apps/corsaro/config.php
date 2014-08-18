<?php
/****************************************************************************
* Name:            config.php                                               *
* Project:         Corsaro                                                  *
* Version:         1.56                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

// NOME DELL'AZIENDA CLIENTE
$companyname="RudyZ";

// NOME DEL DISTRIBUTORE
$copyright_dealer="";

// DATI DI VERSIONE
include_once "version.php";

// PREDEFINITI PER FILIBUSTER
$filibuster_host="";
//$filibuster_host="http://www.rudyz.net/apps/ryquiver/";
$filibuster_environ="flb_rudyz";
//$filibuster_environ="mysql";
$filibuster_site="rudyz";

// Originale
//$mathjax_path="http://www.rudyz.net/cambusa/mathjax/MathJax.js?config=default";
// Nuovo
$mathjax_path="http://www.rudyz.net/cambusa/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML";
// Remoto
//$mathjax_path="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML";

?>