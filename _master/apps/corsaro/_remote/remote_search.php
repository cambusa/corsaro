<?php
/****************************************************************************
* Name:            remote_search.php                                        *
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
$pageid=$_GET["pageid"];
$width=$_GET["width"];
$search=$_GET["search"];
$buff="";
$buff=@file_get_contents($host."food4search.php?host=@&env=$env&site=$site&toolid=$toolid&pageid=$pageid&width=$width&search=$search");
print $buff; 
?>