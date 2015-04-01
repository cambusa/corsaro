<?php
/****************************************************************************
* Name:            rymonad.php                                              *
* Project:         Cambusa/ryMonad                                          *
* Version:         1.69                                                     *
* Description:     Generator of system unique identifier (SYSID)            *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymonad/monad_lib.php";

/***********************************
| Determino la lunghezza del SYSID |
***********************************/

if(isset($_GET["l"]))
    $l=intval($_GET["l"]);
elseif(isset($_POST["l"]))
    $l=intval($_POST["l"]);
else
    $l=8;

if($l<7)
    $l=7;
elseif($l>32)
    $l=32;

/*********************************
| Determino il formato in uscita |
*********************************/

if(isset($_GET["f"]))
    $f=intval($_GET["f"]);
elseif(isset($_POST["f"]))
    $f=intval($_POST["f"]);
else
    $f=0;

if($f<0)
    $f=0;
elseif($f>2)
    $f=2;

/***************************************************
| Determino il numero dei blocchi random in uscita |
***************************************************/

if(isset($_GET["b"]))
    $b=intval($_GET["b"]);
elseif(isset($_POST["b"]))
    $b=intval($_POST["b"]);
else
    $b=0;
    
$p=monadcall($l,$b);

/***********************
| Restituisco il SYSID |
***********************/

switch($f){
    case 1:
        print '{"SYSID":"'.$p.'"}';
        break;
    case 2:
        print '<xml><sysid>'.$p.'</sysid></xml>';
        break;
    default:
        print $p;
        break;
}
?>