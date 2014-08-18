<?php
/****************************************************************************
* Name:            remote_voice.php                                         *
* Project:         Corsaro                                                  *
* Module:          Filibuster                                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$host=$_GET["host"];
$env=$_GET["env"];
$site=$_GET["site"];
$id=$_GET["id"];
$buff="";
$buff=@file_get_contents($host."food4voice.php?host=@&env=$env&site=$site&id=$id");
print $buff; 
?>