<?php
/****************************************************************************
* Name:            remote_container.php                                     *
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
$pageid=$_GET["pageid"];
$width=$_GET["width"];
$buff="";
$buff=@file_get_contents($host."food4container.php?host=@&env=$env&site=$site&id=$id&pageid=$pageid&width=$width");
print $buff; 
?>