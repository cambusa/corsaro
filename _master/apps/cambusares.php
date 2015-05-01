<?php
/****************************************************************************
* Name:            cambusares.php                                           *
* Project:         Cambusa                                                  *
* Version:         1.69                                                     *
* Description:     Cambusa resources for applications                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_applications."cacheversion.php";

$include_lib=Array();

function CambusaLibrary($id){
    global $url_base, $url_cambusa, $url_applications, $url_customize;
    global $path_root, $path_cambusa, $path_applications, $path_customize;
    global $include_lib, $google_maps, $google_zoom, $google_lat, $google_lng;
    global $cacheversion;

$url_temporary=$url_customize."temporary/";
    
$script_cambusa=<<<CAMBUSA
<script language="javascript">
_systeminfo.web.root="$url_base";
_systeminfo.web.apps="$url_applications";
_systeminfo.web.cambusa="$url_cambusa";
_systeminfo.web.customize="$url_customize";
_systeminfo.web.temporary="$url_temporary";
</script>
CAMBUSA;

$script_gmaps=<<<MAPS
<script language="javascript">
_systeminfo.maps.zoom=$google_zoom;
_systeminfo.maps.lat=$google_lat;
_systeminfo.maps.lng=$google_lng;
</script>
MAPS;

$style_rybox=<<<RYBOX
<style>
input,select,a:focus{outline:none;border:none;}
.contextMenu{position:absolute;display:none;}
.contextMenu>ul>li{font-family:verdana;font-size:12px;text-align:left;}
.contextMenu>ul>li>a{color:black;}
.contextMenu>ul>li>a:focus{outline:1px dotted;color:black;}
.contextDisabled>a{color:silver !important;}
</style>
RYBOX;
    
    switch(strtolower($id)){
        case "jquery":
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            break;
    
        case "jqbutton":
            CambusaLibraryAdd("jquery.ui.theme.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.theme.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.button.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.button.css' rel='stylesheet' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js'></script>");
            CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js'></script>");
            CambusaLibraryAdd("jquery.ui.button.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.button.js'></script>");
            break;
            
        case "jqform":
			CambusaLibraryAdd("jquery.ui.base.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.base.css' rel='stylesheet' />");
			CambusaLibraryAdd("jquery.ui.theme.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.theme.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.core.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.core.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.resizable.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.resizable.css' rel='stylesheet' />");
            //CambusaLibraryAdd("jquery.ui.dialog.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.dialog.css' rel='stylesheet' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js' ></script>");
            CambusaLibraryAdd("jquery.ui.position.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.position.js' ></script>");
            CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js' ></script>");
            CambusaLibraryAdd("jquery.ui.mouse.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mouse.js' ></script>");
            CambusaLibraryAdd("jquery.ui.draggable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.draggable.js' ></script>");
            CambusaLibraryAdd("jquery.ui.resizable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.resizable.js' ></script>");
            //CambusaLibraryAdd("jquery.ui.dialog.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.dialog.js' ></script>");
			break;
            
        case "ryque":
            CambusaLibraryAdd("ryque.css", "<link rel='stylesheet' href='".$url_cambusa."ryque/ryque.css?ver=$cacheversion' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js'></script>");
            CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js'></script>");
            CambusaLibraryAdd("jquery.ui.mouse.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mouse.js'></script>");
            CambusaLibraryAdd("jquery.ui.draggable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.draggable.js'></script>");
            CambusaLibraryAdd("jquery.ui.mousewheel.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mousewheel.js'></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("script-cambusa", $script_cambusa);
            CambusaLibraryAdd("ryque.js", "<script type='text/javascript' src='".$url_cambusa."ryque/ryque.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("ryunbound.js", "<script type='text/javascript' src='".$url_cambusa."ryque/ryunbound.js?ver=$cacheversion' ></script>");
            break;
    
        case "rybox":
			CambusaLibraryAdd("jquery.ui.core.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.core.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.theme.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.theme.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.datepicker.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.datepicker.css' rel='stylesheet' />");
            //CambusaLibraryAdd("jquery.ui.tabs.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.tabs.css' rel='stylesheet' />");
            CambusaLibraryAdd("css-datepicker", "<style>div.ui-datepicker{font-size:11px;}</style>");
            CambusaLibraryAdd("css-contextmenu", $style_rybox);
            CambusaLibraryAdd("rybox.css", "<link rel='stylesheet' href='".$url_cambusa."rybox/rybox.css?ver=$cacheversion' />");
            CambusaLibraryAdd("rytabs.css", "<link rel='stylesheet' href='".$url_cambusa."rybox/rytabs.css?ver=$cacheversion' />");
            //CambusaLibraryAdd("rytools.css", "<link rel='stylesheet' href='".$url_cambusa."rybox/rytools.css' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js'></script>");
            CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js'></script>");
            CambusaLibraryAdd("jquery.ui.datepicker.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.datepicker.js' ></script>");
            CambusaLibraryAdd("jquery.ui.contextmenu.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.contextmenu.js' ></script>");
            //CambusaLibraryAdd("jquery.ui.tabs.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.tabs.js' ></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("script-cambusa", $script_cambusa);
            CambusaLibraryAdd("rybox.js", "<script type='text/javascript' src='".$url_cambusa."rybox/rybox.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("rytabs.js", "<script type='text/javascript' src='".$url_cambusa."rybox/rytabs.js?ver=$cacheversion' ></script>");
            //CambusaLibraryAdd("rytools.js", "<script type='text/javascript' src='".$url_cambusa."rybox/rytools.js' ></script>");
            CambusaLibraryAdd("ryedit.js", "<script type='text/javascript' src='".$url_cambusa."rybox/ryedit.js?ver=$cacheversion' ></script>");
            break;
            
        case "rywinz":
            //CambusaLibraryAdd("jquery.ui.dialog.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.dialog.css' rel='stylesheet' />");
			CambusaLibraryAdd("reset.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/reset.css' />");
			CambusaLibraryAdd("desktop.ry.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/desktop.ry.css?ver=$cacheversion' />");
            CambusaLibraryAdd("rywinz.css", "<link rel='stylesheet' href='".$url_cambusa."rywinz/rywinz.css?ver=$cacheversion' />");

			CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js'></script>");
			CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js' ></script>");
			CambusaLibraryAdd("jquery.ui.position.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.position.js' ></script>");
			CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js' ></script>");
			CambusaLibraryAdd("jquery.ui.mouse.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mouse.js' ></script>");
			CambusaLibraryAdd("jquery.ui.draggable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.draggable.js' ></script>");
			CambusaLibraryAdd("jquery.ui.resizable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.resizable.js' ></script>");
            CambusaLibraryAdd("jquery.cookie.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.cookie.js' ></script>");
            //CambusaLibraryAdd("jquery.ui.dialog.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.dialog.js' ></script>");
			CambusaLibraryAdd("jquery.desktop.ry.js", "<script type='text/javascript' src='".$url_cambusa."jqdesktop/assets/js/jquery.desktop.ry.js?ver=$cacheversion' ></script>");
            
            CambusaLibraryAdd("ryego.js", "<script type='text/javascript' src='".$url_cambusa."ryego/ryego.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("rywshared.js", "<script type='text/javascript' src='".$url_cambusa."rywinz/rywshared.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("rywinz.js", "<script type='text/javascript' src='".$url_cambusa."rywinz/rywinz.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("printthis.js", "<script type='text/javascript' src='".$url_cambusa."printthis/printThis.js' ></script>");
            break;
    
        case "rywembed":
            //CambusaLibraryAdd("jquery.ui.dialog.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.dialog.css' rel='stylesheet' />");
			CambusaLibraryAdd("reset.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/reset.css' />");
			CambusaLibraryAdd("desktop.ry.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/desktop.ry.css?ver=$cacheversion' />");
            CambusaLibraryAdd("rywinz.css", "<link rel='stylesheet' href='".$url_cambusa."rywinz/rywinz.css?ver=$cacheversion' />");

			CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js'></script>");
			CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js' ></script>");
			CambusaLibraryAdd("jquery.ui.position.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.position.js' ></script>");
			CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js' ></script>");
			CambusaLibraryAdd("jquery.ui.mouse.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mouse.js' ></script>");
			CambusaLibraryAdd("jquery.ui.draggable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.draggable.js' ></script>");
			CambusaLibraryAdd("jquery.ui.resizable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.resizable.js' ></script>");
            CambusaLibraryAdd("jquery.cookie.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.cookie.js' ></script>");
            //CambusaLibraryAdd("jquery.ui.dialog.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.dialog.js' ></script>");
			CambusaLibraryAdd("jquery.desktop.ry.js", "<script type='text/javascript' src='".$url_cambusa."jqdesktop/assets/js/jquery.desktop.embed.js?ver=$cacheversion' ></script>");
            
            CambusaLibraryAdd("ryego.js", "<script type='text/javascript' src='".$url_cambusa."ryego/ryego.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("rywshared.js", "<script type='text/javascript' src='".$url_cambusa."rywinz/rywshared.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("rywinz.js", "<script type='text/javascript' src='".$url_cambusa."rywinz/rywembed.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("printthis.js", "<script type='text/javascript' src='".$url_cambusa."printthis/printThis.js' ></script>");
            break;
    
        case "ryupload":
            CambusaLibraryAdd("fileuploader.ry.css", "<link type='text/css' href='".$url_cambusa."ryupload/fileuploader.ry.css?ver=$cacheversion' rel='stylesheet' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("fileuploader.ry.js", "<script type='text/javascript' src='".$url_cambusa."ryupload/fileuploader.ry.js?ver=$cacheversion'></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("script-cambusa", $script_cambusa);
            CambusaLibraryAdd("ryupload.js", "<script type='text/javascript' src='".$url_cambusa."ryupload/ryupload.js?ver=$cacheversion'></script>");
            break;
    
        case "ryfamily":
            CambusaLibraryAdd("jquery.treeview.ry.css", "<link type='text/css' href='".$url_cambusa."jqtreeview/jquery.treeview.ry.css' rel='stylesheet' />");

            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.cookie.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.cookie.js' ></script>");
            CambusaLibraryAdd("jquery.treeview.ry.js", "<script type='text/javascript' src='".$url_cambusa."jqtreeview/jquery.treeview.ry.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("script-cambusa", $script_cambusa);
            CambusaLibraryAdd("ryfamily.js", "<script type='text/javascript' src='".$url_cambusa."ryfamily/ryfamily.js?ver=$cacheversion' ></script>");
            break;
    
        case "rysource":
            CambusaLibrary("ryfamily");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("script-cambusa", $script_cambusa);
            CambusaLibraryAdd("rysource.js", "<script type='text/javascript' src='".$url_cambusa."rysource/rysource.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("css-source", "<style>.anchor_rysource{text-decoration:none;color:#000000;}.anchor_rysource:hover{text-decoration:none;color:red;}</style>");
            break;
    
        case "ckeditor":
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("ckeditor.js", "<script type='text/javascript' src='".$url_cambusa."ckeditor/ckeditor.js' ></script>");
            break;
            
        case "googlemaps":
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            if($google_maps){
                CambusaLibraryAdd("script-google", $script_gmaps);
                CambusaLibraryAdd("googleapis.js", "<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&language=it'></script>");
                CambusaLibraryAdd("googlemaps.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/googlemaps.js' ></script>");
            }
            break;

        case "rydraw":
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("raphael.js", "<script type='text/javascript' src='".$url_cambusa."raphael/raphael.js' ></script>");
            //CambusaLibraryAdd("snapsvg.js", "<script type='text/javascript' src='".$url_cambusa."snapsvg/dist/snap.svg-min.js' ></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("rydraw.js", "<script type='text/javascript' src='".$url_cambusa."rydraw/rydraw.js?ver=$cacheversion' ></script>");
            break;

        case "ryquiverbase":
            CambusaLibraryAdd("ryquiverbase.js", "<script type='text/javascript' src='".$url_cambusa."ryquiver/js/ryquiverbase.js?ver=$cacheversion' ></script>");
            break;

        case "ryquiver":
            CambusaLibraryAdd("ryquiverbase.js", "<script type='text/javascript' src='".$url_cambusa."ryquiver/js/ryquiverbase.js?ver=$cacheversion' ></script>");
            CambusaLibraryAdd("ryquiver.js", "<script type='text/javascript' src='".$url_cambusa."ryquiver/js/ryquiver.js?ver=$cacheversion' ></script>");
            break;

        case "jsonx":
            CambusaLibraryAdd("jsonx.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/jsonx.js?ver=$cacheversion' ></script>");
            break;
        
        case "rycode":
            CambusaLibraryAdd("rycode.css", "<link rel='stylesheet' href='".$url_cambusa."rybox/rycode.css?ver=$cacheversion' />");
            CambusaLibraryAdd("rycode.js", "<script type='text/javascript' src='".$url_cambusa."rybox/rycode.js?ver=$cacheversion' ></script>");

        case "geography":
            CambusaLibraryAdd("geography.js", "<script type='text/javascript' src='".$url_cambusa."rywinz/geography/geography.js?ver=$cacheversion' ></script>");
    }
}

function CambusaLibraryAdd($name, $comp){
    global $include_lib;
    if(!isset($include_lib[$name])){
        print $comp."\n";
        $include_lib[$name]=true;
    }
}
?>