<?php
/****************************************************************************
* Name:            remote_statistics.php                                    *
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
$id=$_GET["id"];
$user=$_GET["user"];
$ip=$_SERVER["REMOTE_ADDR"];
$browser=$_GET["browser"];
$buff="";
$buff=@file_get_contents($host."food4statistics.php?env=$env&site=$site&id=$id&user=$user&ip=$ip&browser=$browser");
print $buff; 
?>