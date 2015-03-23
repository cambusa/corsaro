<?php 
/*******************************
| INIZIALIZZAZIONE GESTORE MDI |
*******************************/

include_once "../../cambusa/rywinz/rywinclude.php";

/******************************
| CONFIGURAZIONE APPLICAZIONE |
******************************/

$winz_appname="corsaro";
$winz_apptitle="Corsaro";
$winz_loadmodules="../../apps/corsaro/library.php";
$winz_functionname="qvaccrediti";
$winz_functionpath="../../apps/corsaro/qvcredits/";
$winz_functiontitle="Crediti Formativi";

/**************
| GESTORE MDI |
**************/

include_once $path_cambusa."rywinz/rywembed.php";

// Non aggiungere accapi o spazi dopo ">"
?>