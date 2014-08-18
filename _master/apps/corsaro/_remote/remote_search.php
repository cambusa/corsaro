<?php
/****************************************************************************
* Name:            remote_search.php                                        *
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
$pageid=$_GET["pageid"];
$width=$_GET["width"];
$search=$_GET["search"];
$buff="";
$buff=@file_get_contents($host."food4search.php?host=@&env=$env&site=$site&toolid=$toolid&pageid=$pageid&width=$width&search=$search");
print $buff; 
?>