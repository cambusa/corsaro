<?php 
/****************************************************************************
* Name:            _config.php                                              *
* Project:         Customize                                                *
* Version:         1.69                                                     *
* Description:     Customize configuration file                             *
* Copyright (C):   2015 Rodolfo Calzetti                                    *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

/***********************************
| DETERMINO IL PERCORSO DI CAMBUSA |
***********************************/

$s=realpath(dirname(__FILE__));
$s=str_replace("\\", "/", $s);
$p=strrpos($s, "/customize");
if($p!==false){
    $s=substr($s, 0, $p);
}
$tocambusa=$s."/cambusa/";

/********************************
| AMBIENTE DIRECTORY TEMPORANEA |
********************************/

$temp_environ="temporary";

/***************
| INTESTAZIONE |
***************/

$cust_header="Mulfa - Azienda Ombra";

// Non aggiungere accapi o spazi dopo ">"
?>