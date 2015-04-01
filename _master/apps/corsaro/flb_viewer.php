<?php 
/****************************************************************************
* Name:            flb_viewer.php                                           *
* Project:         Corsaro                                                  *
* Module:          Filibuster                                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$env="";
$site="";
$id="";
$file="";

if(isset($_GET["env"])){
    $env=$_GET["env"];
}
if(isset($_GET["site"])){
    $site=$_GET["site"];
}
if(isset($_GET["id"])){
    $id=$_GET["id"];
}
if(isset($_GET["file"])){
    $file=$_GET["file"];
}
$LINK="filibuster.php?env=$env&site=$site&id=$id";
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
<title>Filibuster Viewer</title>
<style>
body{font-family:sans-serif;font-size:100%;}
a{color:#4E5A65;text-decoration:none;}
a:hover{color:#708090;text-decoration:none;}
</style>
</head>
<body>
<a href="<?php print $LINK ?>">&#x21e6; back to the page</a>
<div style="font-size:8px;">&nbsp;</div>
<?php
$path_parts=pathinfo($file);
if(isset($path_parts["extension"]))
    $ext="." . $path_parts["extension"];
else
    $ext="";
switch(strtolower($ext)){
case ".gif":
case ".jpg":
case ".jpeg":
case ".png":
case ".svg":
    print "<a href=\"$LINK\">\n";
    print "<img src=\"$file\"/ border=\"0\">\n";
    print "</a>\n";
    break;
default:
    print "<iframe src=\"$file\" frameborder=\"0\" width=\"600\" height=\"810\"/></iframe>\n";
}
?>
</body>
</html>
