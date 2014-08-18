<?php 
/****************************************************************************
* Name:            rybabel.php                                              *
* Project:         Cambusa/ryBabel                                          *
* Version:         1.00                                                     *
* Description:     Language localization                                    *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
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