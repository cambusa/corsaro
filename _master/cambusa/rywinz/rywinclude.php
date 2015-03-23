<?php 
/****************************************************************************
* Name:            rywinclude.php                                           *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
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
    
    function winz_pilota(){
        global $url_cambusa;

        $this->name="rudder";
        $this->path=$url_cambusa."rywinz/rudder/";
        $this->title="Pilota";
        $this->icon=$url_cambusa."rywinz/rudder/rudder";
        $this->maximize=false;
        $this->controls=true;
    }
}

/**********
| POSTMAN |
**********/

class winz_postman{
    
    public $title;
    
    function winz_postman(){
        $this->title="Postman";
    }
}

/**********
| TOOLS |
**********/

class winz_tools{
    
    public $title;
    public $items;
    
    function winz_tools(){
        global $url_cambusa;
    
        $this->title="Cambusa";
        $this->items=array();
        $this->items["EGO"]=array( "TITLE" => "Ego", "URL" => $url_cambusa."ryego/ryego.php" );
        $this->items["MAESTRO"]=array( "TITLE" => "Maestro", "URL" => $url_cambusa."rymaestro/rymaestro.php" );
        $this->items["MIRROR"]=array( "TITLE" => "Mirror", "URL" => $url_cambusa."rymirror/rymirror.php" );
        $this->items["PULSE"]=array( "TITLE" => "Pulse", "URL" => $url_cambusa."rypulse/rypulse.php" );
        
    }
}

/********
| ABOUT |
********/

class winz_about{
    
    public $width;
    public $height;
    public $content;
    
    function winz_about(){
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
    public $customize;
    
    function winz_engine(){
    
        $this->pilota=new winz_pilota();
        $this->postman=new winz_postman();
        $this->tools=new winz_tools();
        $this->about=new winz_about();
        $this->appname="rudyz";
        $this->apptitle="ryWinz";
        $this->appdescr="Arrows-oriented application based on advanced web technologies";
        $this->appversion="1.00";
        $this->copyright="2015 RudyZ";
        $this->dealer="";
        $this->company="Anonymous";
        $this->desktop=true;
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