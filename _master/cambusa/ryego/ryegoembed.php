<?php 
/****************************************************************************
* Name:            ryego.php                                                *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."ryego/ego_crypt.php";    
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."phpseclib/Math/BigInteger.php";
include_once $tocambusa."phpseclib/Crypt/RSA.php";

// APRO IL DATABASE
$maestro=maestro_opendb("ryego");

// URL
if(isset($_GET["url"])){
    $returnurl=$_GET["url"];
    $egomethod="GET";
}
elseif(isset($_POST["url"])){
    $returnurl=$_POST["url"];
    $egomethod="POST";
}
else{
    $returnurl="";
    $egomethod="POST";
}

// APPLICAZIONE
if(isset($_GET["app"]))
    $appname=$_GET["app"];
elseif(isset($_POST["app"]))
    $appname=$_POST["app"];
else
    $appname="";

// GESTIONE AMBIENTE: POSSO CAMBIARLO SENZA PASSARE ESPLICITAMENTE DAL SETUP EGO
if(isset($_GET["env"]))
    $castenv=$_GET["env"];
elseif(isset($_POST["env"]))
    $castenv=$_POST["env"];
else
    $castenv="";
    
// METODO
if(isset($_GET["method"]))
    $egomethod=$_GET["method"];
elseif(isset($_POST["method"]))
    $egomethod=$_POST["method"];

// AVVIARE SETUP
if(isset($_GET["set"]))
    $setup=intval($_GET["set"]);
elseif(isset($_POST["set"]))
    $setup=intval($_POST["set"]);
else
    $setup=0;

// POSIZIONAMENTO MASCHERA SETUP
if(isset($_POST["msk"]))
    $msk=$_POST["msk"];
else
    $msk="login";

if(isset($_POST["sessionid"])){
    $sessionid=ryqEscapize($_POST["sessionid"]);
    if($maestro->conn!==false){
        if(!ext_validatesession($sessionid)){
            $sessionid="";
            $msk="login";
        }
    }
    else{
        $sessionid="";
        $msk="login";
    }
}
else{
    $sessionid="";
    $msk="login";
}

if(isset($_POST["userid"]))
    $userid=$_POST["userid"];
else
    $userid="";

if(isset($_POST["aliasid"]))
    $aliasid=$_POST["aliasid"];
else
    $aliasid="";

if(isset($_POST["appid"]))
    $appid=$_POST["appid"];
else
    $appid="";

if(isset($_POST["expiry"]))
    $expiry=intval($_POST["expiry"]);
else
    $expiry=0;

if(isset($_GET["active"]))
    $active=intval($_GET["active"]);
elseif(isset($_POST["active"]))
    $active=intval($_POST["active"]);
else
    $active="settings";
    
$setuponly=0;

$egouser="";
if($appname!="" && isset($_COOKIE['_egouser']))
    $egouser=$_COOKIE['_egouser'];

$egolanguage=$config_defaultlang;
if(isset($_COOKIE['_egolanguage']))
    $egolanguage=$_COOKIE['_egolanguage'];

// PREPARAZIONE CRITTOGRAFIA PER PROTEZIONE PASSWORD
prepareEncrypt($maestro, $publickey);
    
?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
<title>Ego - Servizio di autenticazione</title>
<link rel='shortcut icon' href='images/favicon.ico' type='image/x-icon'/>
<link type='text/css' href='ryego.css' rel='stylesheet' />
    
<style type="text/css">
body{font-family:verdana,sans-serif;font-size:13px;background-color:white;}
table{font-family:verdana,sans-serif;font-size:13px;}
</style>

<link type='text/css' href='../rybox/rybox.css' rel='stylesheet' />
<link type='text/css' href='../ryque/ryque.css' rel='stylesheet' />

<link type='text/css' href='../jquery/css/jquery.ui.core.css' rel='stylesheet' />
<link type='text/css' href='../jquery/css/jquery.ui.datepicker.css' rel='stylesheet' />
<link type='text/css' href='../jquery/css/jquery.ui.theme.css' rel='stylesheet' />
<link type='text/css' href='../jquery/css/jquery.ui.button.css' rel='stylesheet' />
<link type='text/css' href='../jquery/css/jquery.ui.tabs.css' rel='stylesheet' />

<style>
div.ui-datepicker{font-size:11px;}
</style>

<style>
input,select,a:focus{outline:none;border:none;}
.contextMenu{position:absolute;display:none;}
.contextMenu>ul>li{font-family:verdana;font-size:12px;text-align:left;}
.contextMenu>ul>li>a{color:black;}
.contextMenu>ul>li>a:focus{outline:1px dotted;color:black;}
.contextDisabled>a{color:silver !important;}
</style>
            
<script type='text/javascript' src='../jquery/jquery.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.datepicker.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.contextmenu.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.widget.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.button.js'></script>
<script type='text/javascript' src='../rygeneral/rygeneral.js' ></script>
<script language='javascript'>_cambusaURL='../';</script>
<script type='text/javascript' src='../rybox/rybox.js' ></script>
<script type='text/javascript' src='../cryptojs/rollups/sha1.js' ></script>
<script type='text/javascript' src='../jsencrypt/bin/jsencrypt.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.draggable.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mousewheel.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.tabs.js'></script>
<script type='text/javascript' src='../ryque/ryque.js' ></script>

<script>
_sessionid="<?php print $sessionid ?>";
var htimer="";
var _publickey="<?php print strtr($publickey, array("\n" => "[n]", "\r" => "[r]")); ?>";
_publickey=_publickey.replace(/\[n\]/g, "\n").replace(/\[r\]/g, "\r");
var _returnURL="<?php print $returnurl ?>";
var _egomethod="<?php  print $egomethod ?>";
var _setuponly=<?php  print $setuponly ?>;
var _appname="<?php  print $appname ?>";
var _castenv="<?php  print $castenv ?>";
var _egocontext="embed";
function encryptString(s){
    var e=new JSEncrypt();
    s=CryptoJS.SHA1(s);
    e.setPublicKey(_publickey);
    var r=e.encrypt( s.toString() );
    return r;
}
function syswaiting(){
    if(htimer!=""){
        clearInterval(htimer);
        htimer="";
    }
	$("#messbar").html("<img src='images/waiting.gif' style='border:1px solid silver;'>").show();
}
function sysmessage(t,s){
    if(htimer!=""){
        clearInterval(htimer);
        htimer="";
    }
	var c="red";
    var m=6000;
	if(s==1){
		c="green";
        m=4000;
    }
	$("#messbar").html(t).css({color:c}).show();
	htimer=setTimeout(sysmessagehide, m);
}
function sysmessagehide(){
    htimer="";
	$("#messbar").html("").hide("slow");
}
</script>

<?php 
include_once "egoform_login.php";
?>

</head>

<body style='overflow:hidden;' spellcheck='false'>

<?php 
$copyappname=$appname;
$appname="";
include_once "egoform_loginbody.php";
?>

<div id="messbar" style="display:none;position:absolute;left:120px;top:210px;"></div>

<div style="position:absolute;left:120px;top:260px;">
<a href="ryego.php?app=<?php print $copyappname ?>&setuponly=1" target="_blank">Setup, password e disattivazione</a><br/>
<br/>
<?php
$trigger_login=$path_customize."ryego/custtriggerlogin.php";
if(is_file($trigger_login)){
    include $trigger_login;
}
?>
<div>

</body>
</html>
<?php 
// CHIUDO IL DATABASE
maestro_closedb($maestro);
?>