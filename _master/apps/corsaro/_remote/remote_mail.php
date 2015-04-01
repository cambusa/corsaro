<?php
/****************************************************************************
* Name:            remote_mail.php                                          *
* Project:         Corsaro                                                  *
* Module:          Filibuster                                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
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