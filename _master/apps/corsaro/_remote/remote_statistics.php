<?php
/****************************************************************************
* Name:            remote_statistics.php                                    *
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
$user=$_GET["user"];
$ip=$_SERVER["REMOTE_ADDR"];
$browser=$_GET["browser"];
$buff="";
$buff=@file_get_contents($host."food4statistics.php?env=$env&site=$site&id=$id&user=$user&ip=$ip&browser=$browser");
print $buff; 
?>