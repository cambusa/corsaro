<?php 
/****************************************************************************
* Name:            rybabel.php                                              *
* Project:         Cambusa/ryBabel                                          *
* Version:         1.69                                                     *
* Description:     Language localization                                    *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

if(isset($_POST['lang']))
    $lang=$_POST['lang'];
elseif(isset($_GET['lang']))
    $lang=$_GET['lang'];
else
    $lang="italiano";
    
if(isset($_POST['codes']))
    $codes=$_POST['codes'];
elseif(isset($_GET['codes']))
    $codes=$_GET['codes'];
else
    $codes="";

include("../sysconfig.php");    
include("babel_lib.php");
print babeldecode($lang, $codes);
?>