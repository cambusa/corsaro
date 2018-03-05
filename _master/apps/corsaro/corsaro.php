<?php 
/****************************************************************************
* Name:            corsaro.php                                              *
* Project:         Corsaro                                                  *
* Version:         1.70                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

/*******************************
| INIZIALIZZAZIONE GESTORE MDI |
*******************************/

include_once "../../cambusa/rywinz/rywinclude.php";

/******************************
| CONFIGURAZIONE APPLICAZIONE |
******************************/

$RYWINZ->appname="corsaro";
$RYWINZ->apptitle="Corsaro";
$RYWINZ->appdescr="Web-based Enterprise Resource Planning";
$RYWINZ->appversion="v2.0";
$RYWINZ->copyright="2018 Rodolfo Calzetti";
$RYWINZ->dealer="";
$RYWINZ->about->content=<<<ABOUT
<div style="line-height:14px;">
<br/>
<img src="_images/appicon.png" align="left" style="margin:5px;">
<div>&nbsp;</div>
<span style="font-size:16px;">{$RYWINZ->apptitle} {$RYWINZ->appversion} - Copyright &copy; {$RYWINZ->copyright}</span><br/>
<div style="line-height:20px;">&nbsp;</div>
<div style="text-align:center;color:navy;">{$RYWINZ->appdescr}</div>
<div style="line-height:20px;">&nbsp;</div>
<a class="winz-linkabout" href="{$url_cambusa}CREDITS.TXT" target="_blank">Cambusa credits</a><br/>
<br/>
<a class="winz-linkabout" href="{$url_applications}{$RYWINZ->appname}/LICENSE.TXT" target="_blank">{$RYWINZ->apptitle} License</a><br/>
<br/>
<br/>
- Thanks to <b>The jQuery Foundation</b> for <a href="http://jquery.com/" target="_blank" style="cursor:pointer;text-decoration:underline;">jQuery</a> 
and <a href="http://jqueryui.com/" target="_blank" style="cursor:pointer;text-decoration:underline;">jQuery UI</a>.<br/>
<br/>
- Thanks to all who believe in free software licenses.<br/>
<br/>
- Special thanks to <b>Nathan Smith</b> for his terrific 
<a href="http://sonspring.com/journal/jquery-desktop" target="_blank" style="cursor:pointer;text-decoration:underline;">Multiple Document Interface</a>.<br/>
</div>
ABOUT;

/********************
| PERSONALIZZAZIONI |
********************/

if(is_file($path_customize."_apps.php")){
    include $path_customize."_apps.php";
}

/**************
| GESTORE MDI |
**************/

include_once $path_cambusa."rywinz/rywinz.php";

// Non aggiungere accapi o spazi dopo ">"
?>