<?php 
/****************************************************************************
* Name:            rywinz.php                                               *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.00                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
<title><?php print $winz_apptitle ?></title>
<link rel='shortcut icon' href='_images/favicon.ico' type='image/x-icon'/>
<?php
CambusaLibrary("ryBox");
CambusaLibrary("ryQue");
CambusaLibrary("rySource");
CambusaLibrary("ryWinz");
CambusaLibrary("ryDraw");

if(is_file("library.php"))
    include("library.php");

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
elseif(isset($_GET["sessionid"]))
    $sessionid=$_GET["sessionid"];
else
    $sessionid="";

// VALORI DI CONFIGURAZIONE
if(!isset($companyname))
    $companyname="Anonymous";
if(!isset($aboutwidth))
    $aboutwidth=550;
if(!isset($aboutheight))
    $aboutheight=320;
if(!isset($winz_apptitle))
    $winz_apptitle="ryWinz";
if(!isset($winz_appdescr))
    $winz_appdescr="Arrows-oriented application based on advanced web technologies";
if(!isset($winz_appversion))
    $winz_appversion="1.00";
if(!isset($copyright_name))
    $copyright_name="RudyZ";
if(!isset($copyright_year))
    $copyright_year="2014";
if(!isset($copyright_dealer))
    $copyright_dealer="";
?>
<!--[if lt IE 9]>
<link rel="stylesheet" href="<?php print $url_cambusa ?>jqdesktop/assets/css/ie.css" />
<![endif]-->
<script>
_baseURL="<?php  print rywinzHost() ?>";
_sessionid="<?php  print $sessionid ?>";
var _appname="<?php  print $winz_appname ?>";
var _apptitle="<?php  print $winz_apptitle ?>";
$(document).ready(function(){
    RYEGO.go({
        crossdomain:"",
        appname:_appname,
        config:function(d){
            _sessioninfo=d;
            if(window.console)console.log(_sessioninfo);
            $("body").css("display", "block");
            RYQUE.request({
                environ:_sessioninfo.environ,
                ready:function(){
                    RYQUEAUX.request({
                        environ:_sessioninfo.environ,
                        ready:function(){
                            mdiconfig();
                        }
                    });
                }
            });
        }
    });
});
function mdiconfig(){
    $("#sessioninfo").html("<?php  print $companyname ?> / "+_apptitle+" / "+_sessioninfo.envdescr+" / "+_sessioninfo.alias);
    _openingparams="({environ:\""+_appname+"_"+_sessioninfo.role+"\",root:\""+_sessioninfo.roledescr+"\"})";
    RYWINZ.newform({
        id:"rudder",
        name:"rudder",
        path:_cambusaURL+"rywinz/rudder/",
        title:"Pilota",
        desk:true,
        icon:_cambusaURL+"rywinz/rudder/rudder"
    });
    // Internet Explorer gestisce l'evento in un modo impossibile
    if(!$.browser.msie){
        window.onbeforeunload=winz_confirmExit;
    }
    setTimeout(
        function(){
            RYQUE.clean();
        }, 5000
    )
    $("#winz-about .winz_close").click(
        function(){
            $("#winz-about-dither").hide();
            $("#winz-about").hide();
        }
    );
}
function winz_confirmExit(){
    return winz_logout(false);
}
function winz_showabout(){
    var z=10000;
    $.each( $("#winz-about").parents(), 
        function(key, value){
            var t=$(value).css("z-index");
            if(z<t){z=t+1}
        }
    );
    $("#winz-about-dither").css("z-index",z).show();
    $("#winz-about").css("z-index",z+1).show();
}
function winz_logout(promptmess){
    var ok=true;
    var msg="Richiesta di abbandono della pagina!";
    for(var n in _globalforms){
        if(RYWINZ.modified(n) || RYWINZ.busy(n)){
            ok=false;
            break;
        }
    }
    if(ok==false){
        msg="Alcune attivit"+_utf8("a")+" sono in corso o qualche documento non "+_utf8("e")+" stato salvato.";
        if(promptmess){
            msg+="\n\nUscire comunque?";
            ok=confirm(msg);
        }
    }
    if(ok==true && promptmess==true){
        window.onbeforeunload=null;
        var castclose=setTimeout(
            function(){
                ego_logout();
            }, 5000
        );
        winzRemoveAll(  // Rimozione di tuttu i form
            function(){
                RYQUE.dispose(  // Rimozione RYQUE principale
                    function(){
                        RYQUEAUX.dispose(   // Rimozione RYQUE ausiliario
                            function(){
                                clearTimeout(castclose);
                                ego_logout();
                            }
                        );
                    }
                );
            }
        );
    }
    function ego_logout(){
        if(_logoutcall!==false){    // Logout personalizzato
            _logoutcall(
                function(){
                    RYEGO.logout();
                }
            );
        }
        else{   // Logout standard
            RYEGO.logout();
        }
    }
    return msg;
}
</script>
</head>
<body spellcheck="false" style="display:none;">
<div class="abs" id="wrapper">  <!-- BEGIN WRAPPER -->

    <div class="abs" id="desktop"></div>

	<div class="abs" id="bar_top">
		<span class="float_right" id="sessioninfo"></span>
		<ul>
			<li>
				<a class="menu_trigger" href="#">File</a>
				<ul class="menu">
					<li><a class="rudyz" href="#icon_dock_rudder">Pilota</a></li>
					<li><a class="rudyz" href="javascript:" onclick="winz_logout(true)">Logout</a></li>
				</ul>
			</li>
			<li>
				<a class="menu_trigger" href="#">Cambusa</a>
				<ul class="menu">
					<li><a class="rudyz" href="<?php print $url_cambusa ?>ryego/ryego.php" target="_blank">Ego</a></li>
					<li><a class="rudyz" href="<?php print $url_cambusa ?>rymaestro/rymaestro.php" target="_blank">Maestro</a></li>
                    <li><a class="rudyz" href="<?php print $url_cambusa ?>rypulse/rypulse.php" target="_blank">Pulse</a></li>
				</ul>
			</li>
			<li>
				<a class="menu_trigger" href="#">Info</a>
				<ul class="menu">
					<li><a class="rudyz" href="javascript:" onclick="winz_showabout()">About <?php print $winz_apptitle ?></a></li>
				</ul>
			</li>
		</ul>
	</div>
    
    <div class="abs" id="bar_bottom">
		<a class="float_left" href="#" id="show_desktop" title="Show Desktop">
			<img src="<?php print $url_cambusa ?>jqdesktop/assets/images/icons/icon_22_desktop.png" />
		</a>
		<ul id="dock">
		</ul>
<?php
    $copy="$winz_apptitle &copy; $copyright_year $copyright_name";
    if($copyright_dealer!=""){
        $copy.=" - Distributore ".$copyright_dealer;
    }
?>
		<a class="float_right" style="font-size:11px;" href="license.html" target="_blank"><?php  print $copy ?></a>
    </div>

    <div id="winz-about-dither" class="winz_dither" style="top:0px;background:#1E90FF;"></div>
    <div id="winz-about" class="winz_dialog" style="display:none;width:<?php  print $aboutwidth ?>px;height: <?php  print $aboutheight ?>px;" title="About <?php print $winz_apptitle ?>">
    <div class='winz_close'>X</div><div class="winz_msgbox" style="top:30px;width:<?php  print $aboutwidth-50 ?>px;height:<?php  print $aboutheight-60 ?>px;font-size:12px;line-height:14px;">
        <!-- INIZIO ABOUT -->
        <br/>
        <img src="_images/appicon.png" align="left" style="margin:5px;">
        <div>&nbsp;</div>
        <span style="font-size:16px;"><?php print $winz_apptitle." ".$winz_appversion." - Copyright &copy ".$copyright_year." ".$copyright_name ?></span><br/>
        <div style="line-height:20px;">&nbsp;</div>
        <div style="text-align:center;color:navy;"><?php print $winz_appdescr ?></div>
        <div style="line-height:20px;">&nbsp;</div>
        <a class="winz-linkabout" href="<?php print $url_cambusa ?>license.html" target="_blank">Cambusa License</a><br/>
        <br/>
        <a class="winz-linkabout" href="license.html" target="_blank"><?php print $winz_apptitle ?> License</a><br/>
        <br/>
        <br/>
        - Thanks to <b>The jQuery Foundation</b> for <a href="http://jquery.com/" target="_blank" style="cursor:pointer;text-decoration:underline;">jQuery</a> 
        and <a href="http://jqueryui.com/" target="_blank" style="cursor:pointer;text-decoration:underline;">jQuery UI</a>.<br/>
        <br/>
        - Thanks to all who believe in free software licenses.<br/>
        <br/>
        - Special thanks to <b>Nathan Smith</b> for his terrific 
        <a href="http://sonspring.com/journal/jquery-desktop" target="_blank" style="cursor:pointer;text-decoration:underline;">Multiple Document Interface</a>.<br/>
        <!-- FINE ABOUT -->
    </div>
    </div>
    
    <div id="winz-dialog"></div>
    <div id="winz-printing"></div>
    <iframe id="winz-iframe"></iframe>
    
</div> <!-- END WRAPPER -->
</body>
</html>
<?php
function rywinzHost(){
    $pageURL='http';
    if(isset($_SERVER["HTTPS"])){
        if($_SERVER["HTTPS"]=="on"){
            $pageURL.="s";
        }
    }
    $pageURL.="://";
    if($_SERVER["SERVER_PORT"]!="80"){
        $pageURL.=$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }
    else{
        $pageURL.=$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    $p=strpos($pageURL, "/apps");
    if($p!==false){
        $pageURL=substr($pageURL, 0, $p+1);
    }
    return $pageURL;
}
?>