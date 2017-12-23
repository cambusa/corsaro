<?php 
/****************************************************************************
* Name:            rywinclude.php                                           *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

/*********************
| FORZATURA AMBIENTE |
*********************/

if(isset($_GET["env"])){
    $winz_appenviron=$_GET["env"];
}

/***********************************
| DETERMINO IL PERCORSO DI CAMBUSA |
***********************************/

$path_cambusa=realpath(dirname(__FILE__)."/..");
$path_cambusa=str_replace("\\", "/", $path_cambusa);
$path_cambusa.="/";

/***************************
| ABILITAZIONE GOOGLE MAPS |
***************************/

$google_maps=true;
$google_zoom=16;
$google_lat=45.550084;
$google_lng=9.180665;

/****************************
| CONFIGURAZIONE DI CAMBUSA |
****************************/

include_once $path_cambusa."sysconfig.php";

/*********
| PILOTA |
*********/

class winz_pilota{
    
    public $name;
    public $path;
    public $title;
    public $icon;
    public $maximize;
    public $controls;
    public $statusbar;
    
    function __construct(){
        global $relative_base;

        $this->name="rudder";
        $this->path=$relative_base."cambusa/rywinz/rudder/";
        $this->title="Pilota";
        $this->icon=$relative_base."cambusa/rywinz/rudder/rudder";
        $this->maximize=false;
        $this->controls=true;
        $this->statusbar=true;
    }
}

/**********
| POSTMAN |
**********/

class winz_postman{
    
    public $title;
    public $enabled;
    
    function __construct(){
        $this->title="Postman";
        $this->enabled=true;
    }
}

/**********
| TOOLS |
**********/

class winz_tools{
    
    public $title;
    public $items;
    
    function __construct(){
        global $relative_base;
    
        $this->title="Cambusa";
        $this->items=array();
        $this->items["EGO"]=array( "TITLE" => "Ego", "URL" => $relative_base."cambusa/ryego/ryego.php" );
        $this->items["MAESTRO"]=array( "TITLE" => "Maestro", "URL" => $relative_base."cambusa/rymaestro/rymaestro.php" );
        $this->items["MIRROR"]=array( "TITLE" => "Mirror", "URL" => $relative_base."cambusa/rymirror/rymirror.php" );
        $this->items["PULSE"]=array( "TITLE" => "Pulse", "URL" => $relative_base."cambusa/rypulse/rypulse.php" );
        
    }
}

/********
| ABOUT |
********/

class winz_about{
    
    public $width;
    public $height;
    public $content;
    
    function __construct(){
        $this->width=550;
        $this->height=320;
        $this->content="";
    }
}

/*********
| RYWINZ |
*********/

class winz_engine{
    
    public $pilota;
    public $postman;
    public $about;
    
    public $appname;
    public $apptitle;
    public $appdescr;
    public $appversion;
    public $copyright;
    public $dealer;
    public $company;
    public $desktop;
    public $wallpaper;
    public $customize;
    
    function __construct(){
    
        $this->pilota=new winz_pilota();
        $this->postman=new winz_postman();
        $this->tools=new winz_tools();
        $this->about=new winz_about();
        $this->appname="rudyz";
        $this->apptitle="ryWinz";
        $this->appdescr="Arrows-oriented application based on advanced web technologies";
        $this->appversion="1.00";
        $this->logo="";
        $this->copyright="2015 RudyZ";
        $this->dealer="";
        $this->company="Anonymous";
        $this->desktop=true;
        $this->wallpaper=true;
        $this->customize="default";
    }
}

/*******************************************************
| ISTANZIO L'OGGETTO DI CONFIGURAZIONE DEL GESTORE MDI |
*******************************************************/

$RYWINZ=new winz_engine();

/***********************************
| RISORSE CAMBUSA - NON MODIFICARE |
***********************************/

include_once $path_applications."cambusares.php";

// Non aggiungere accapi o spazi dopo ">"
?>