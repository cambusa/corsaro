<?php
/****************************************************************************
* Name:            cambusares.php                                           *
* Project:         Cambusa                                                  *
* Version:         1.00                                                     *
* Description:     Cambusa resources for applications                       *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$include_lib=Array();
function CambusaLibrary($id){
    global $url_cambusa,$url_applications,$url_customize,$include_lib,$google_maps,$google_zoom,$google_lat,$google_lng;
    
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
            CambusaLibraryAdd("ryque.css", "<link rel='stylesheet' href='".$url_cambusa."ryque/ryque.css' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js'></script>");
            CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js'></script>");
            CambusaLibraryAdd("jquery.ui.mouse.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mouse.js'></script>");
            CambusaLibraryAdd("jquery.ui.draggable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.draggable.js'></script>");
            CambusaLibraryAdd("jquery.ui.mousewheel.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mousewheel.js'></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js' ></script>");
            CambusaLibraryAdd("script-cambusa", "<script language='javascript'>_cambusaURL='".$url_cambusa."';_customizeURL='".$url_customize."';</script>");
            CambusaLibraryAdd("ryque.js", "<script type='text/javascript' src='".$url_cambusa."ryque/ryque.js' ></script>");
            break;
    
        case "rybox":
			CambusaLibraryAdd("jquery.ui.core.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.core.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.theme.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.theme.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.datepicker.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.datepicker.css' rel='stylesheet' />");
            CambusaLibraryAdd("jquery.ui.tabs.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.tabs.css' rel='stylesheet' />");
            CambusaLibraryAdd("css-datepicker", "<style>div.ui-datepicker{font-size:11px;}</style>");
            CambusaLibraryAdd("css-contextmenu", "<style>.ry-contextMenu{font-family:verdana;font-size:12px;}input,select,a:focus{outline:none;border:none;}</style>");
            CambusaLibraryAdd("rybox.css", "<link rel='stylesheet' href='".$url_cambusa."rybox/rybox.css' />");
            //CambusaLibraryAdd("rytools.css", "<link rel='stylesheet' href='".$url_cambusa."rybox/rytools.css' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js'></script>");
            CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js'></script>");
            CambusaLibraryAdd("jquery.ui.datepicker.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.datepicker.js' ></script>");
            CambusaLibraryAdd("jquery.ui.contextmenu.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.contextmenu.js' ></script>");
            CambusaLibraryAdd("jquery.ui.tabs.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.tabs.js' ></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js' ></script>");
            CambusaLibraryAdd("script-cambusa", "<script language='javascript'>_cambusaURL='".$url_cambusa."';_customizeURL='".$url_customize."';</script>");
            CambusaLibraryAdd("rybox.js", "<script type='text/javascript' src='".$url_cambusa."rybox/rybox.js' ></script>");
            CambusaLibraryAdd("rytabs.js", "<script type='text/javascript' src='".$url_cambusa."rybox/rytabs.js' ></script>");
            //CambusaLibraryAdd("rytools.js", "<script type='text/javascript' src='".$url_cambusa."rybox/rytools.js' ></script>");
            CambusaLibraryAdd("ryedit.js", "<script type='text/javascript' src='".$url_cambusa."rybox/ryedit.js' ></script>");
            break;
            
        case "rywinz":
            //CambusaLibraryAdd("jquery.ui.dialog.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.dialog.css' rel='stylesheet' />");
			CambusaLibraryAdd("reset.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/reset.css' />");
			CambusaLibraryAdd("desktop.ry.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/desktop.ry.css' />");
            CambusaLibraryAdd("rywinz.css", "<link rel='stylesheet' href='".$url_cambusa."rywinz/rywinz.css' />");

			CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js'></script>");
			CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js' ></script>");
			CambusaLibraryAdd("jquery.ui.position.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.position.js' ></script>");
			CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js' ></script>");
			CambusaLibraryAdd("jquery.ui.mouse.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mouse.js' ></script>");
			CambusaLibraryAdd("jquery.ui.draggable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.draggable.js' ></script>");
			CambusaLibraryAdd("jquery.ui.resizable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.resizable.js' ></script>");
            CambusaLibraryAdd("jquery.cookie.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.cookie.js' ></script>");
            //CambusaLibraryAdd("jquery.ui.dialog.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.dialog.js' ></script>");
			CambusaLibraryAdd("jquery.desktop.ry.js", "<script type='text/javascript' src='".$url_cambusa."jqdesktop/assets/js/jquery.desktop.ry.js' ></script>");
            
            CambusaLibraryAdd("ryego.js", "<script type='text/javascript' src='".$url_cambusa."ryego/ryego.js' ></script>");
            CambusaLibraryAdd("rywinz.js", "<script type='text/javascript' src='".$url_cambusa."rywinz/rywinz.js' ></script>");
            CambusaLibraryAdd("printthis.js", "<script type='text/javascript' src='".$url_cambusa."printthis/printThis.js' ></script>");
            break;
    
        case "rywembed":
            //CambusaLibraryAdd("jquery.ui.dialog.css", "<link type='text/css' href='".$url_cambusa."jquery/css/jquery.ui.dialog.css' rel='stylesheet' />");
			CambusaLibraryAdd("reset.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/reset.css' />");
			CambusaLibraryAdd("desktop.ry.css", "<link rel='stylesheet' href='".$url_cambusa."jqdesktop/assets/css/desktop.ry.css' />");
            CambusaLibraryAdd("rywinz.css", "<link rel='stylesheet' href='".$url_cambusa."rywinz/rywinz.css' />");

			CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js'></script>");
			CambusaLibraryAdd("jquery.ui.core.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.core.js' ></script>");
			CambusaLibraryAdd("jquery.ui.position.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.position.js' ></script>");
			CambusaLibraryAdd("jquery.ui.widget.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.widget.js' ></script>");
			CambusaLibraryAdd("jquery.ui.mouse.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.mouse.js' ></script>");
			CambusaLibraryAdd("jquery.ui.draggable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.draggable.js' ></script>");
			CambusaLibraryAdd("jquery.ui.resizable.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.resizable.js' ></script>");
            CambusaLibraryAdd("jquery.cookie.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.cookie.js' ></script>");
            //CambusaLibraryAdd("jquery.ui.dialog.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.ui.dialog.js' ></script>");
			CambusaLibraryAdd("jquery.desktop.ry.js", "<script type='text/javascript' src='".$url_cambusa."jqdesktop/assets/js/jquery.desktop.embed.js' ></script>");
            
            CambusaLibraryAdd("ryego.js", "<script type='text/javascript' src='".$url_cambusa."ryego/ryego.js' ></script>");
            CambusaLibraryAdd("rywinz.js", "<script type='text/javascript' src='".$url_cambusa."rywinz/rywembed.js' ></script>");
            CambusaLibraryAdd("printthis.js", "<script type='text/javascript' src='".$url_cambusa."printthis/printThis.js' ></script>");
            break;
    
        case "ryupload":
            CambusaLibraryAdd("fileuploader.ry.css", "<link type='text/css' href='".$url_cambusa."ryupload/fileuploader.ry.css' rel='stylesheet' />");
            
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("fileuploader.ry.js", "<script type='text/javascript' src='".$url_cambusa."ryupload/fileuploader.ry.js'></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js' ></script>");
            CambusaLibraryAdd("script-cambusa", "<script language='javascript'>_cambusaURL='".$url_cambusa."';_customizeURL='".$url_customize."';</script>");
            CambusaLibraryAdd("ryupload.js", "<script type='text/javascript' src='".$url_cambusa."ryupload/ryupload.js'></script>");
            break;
    
        case "ryfamily":
            CambusaLibraryAdd("jquery.treeview.ry.css", "<link type='text/css' href='".$url_cambusa."jqtreeview/jquery.treeview.ry.css' rel='stylesheet' />");

            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("jquery.cookie.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.cookie.js' ></script>");
            CambusaLibraryAdd("jquery.treeview.ry.js", "<script type='text/javascript' src='".$url_cambusa."jqtreeview/jquery.treeview.ry.js' ></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js' ></script>");
            CambusaLibraryAdd("script-cambusa", "<script language='javascript'>_cambusaURL='".$url_cambusa."';_customizeURL='".$url_customize."';</script>");
            CambusaLibraryAdd("ryfamily.js", "<script type='text/javascript' src='".$url_cambusa."ryfamily/ryfamily.js' ></script>");
            break;
    
        case "rysource":
            CambusaLibrary("ryfamily");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js' ></script>");
            CambusaLibraryAdd("script-cambusa", "<script language='javascript'>_cambusaURL='".$url_cambusa."';_customizeURL='".$url_customize."';</script>");
            CambusaLibraryAdd("rysource.js", "<script type='text/javascript' src='".$url_cambusa."rysource/rysource.js' ></script>");
            CambusaLibraryAdd("css-source", "<style>.anchor_rysource{text-decoration:none;color:#000000;}.anchor_rysource:hover{text-decoration:none;color:red;}</style>");
            break;
    
        case "ckeditor":
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("ckeditor.js", "<script type='text/javascript' src='".$url_cambusa."ckeditor/ckeditor.js' ></script>");
            break;
            
        case "googlemaps":
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            if($google_maps){
                CambusaLibraryAdd("script-google", "<script language='javascript'>_googleZoom=".$google_zoom.";_googleLat=".$google_lat.";_googleLng=".$google_lng.";</script>");
                CambusaLibraryAdd("googleapis.js", "<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&language=it'></script>");
                CambusaLibraryAdd("googlemaps.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/googlemaps.js' ></script>");
            }
            break;
        case "rydraw":
            CambusaLibraryAdd("jquery.js", "<script type='text/javascript' src='".$url_cambusa."jquery/jquery.js' ></script>");
            CambusaLibraryAdd("raphael.js", "<script type='text/javascript' src='".$url_cambusa."raphael/raphael.js' ></script>");
            //CambusaLibraryAdd("snapsvg.js", "<script type='text/javascript' src='".$url_cambusa."snapsvg/dist/snap.svg-min.js' ></script>");
            CambusaLibraryAdd("rygeneral.js", "<script type='text/javascript' src='".$url_cambusa."rygeneral/rygeneral.js' ></script>");
            CambusaLibraryAdd("rydraw.js", "<script type='text/javascript' src='".$url_cambusa."rydraw/rydraw.js' ></script>");
            break;
        case "ryquiver":
            CambusaLibraryAdd("ryquiver.js", "<script type='text/javascript' src='".$url_cambusa."ryquiver/ryquiver.js' ></script>");
            break;
        case "corsaro":
            CambusaLibraryAdd("corsaro.js", "<script type='text/javascript' src='".$url_applications."corsaro/_javascript/corsaro.js' ></script>");
            break;
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