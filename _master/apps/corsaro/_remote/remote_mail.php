<?php
/****************************************************************************
* Name:            remote_mail.php                                          *
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
$toolid=$_GET["toolid"];
$email=$_GET["email"];
$text=$_GET["text"];
$buff="";
$buff=@file_get_contents($host."food4mail.php?env=$env&site=$site&toolid=$toolid&email=$email&text=$text");
print $buff; 
?>