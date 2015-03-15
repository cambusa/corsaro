<?php 
/****************************************************************************
* Name:            rywinz.php                                               *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

// ASSEGNO LA VARIABILE CHE FORZEREBBE L'AMBIENTE SE NON FOSSE SETTATA
if(!isset($winz_appenviron)){
    $winz_appenviron="";
}

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
if(!isset($aboutinclude))
    $aboutinclude="";
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
var _appenviron="<?php  print $winz_appenviron ?>";
var _timerPostman;
$(document).ready(function(){
    RYEGO.go({
        crossdomain:"",
        appname:_appname,
        apptitle:_apptitle,
        appenv:_appenviron,
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
    _timerPostman=setInterval(
        function(){
            if(window.console&&_sessioninfo.debugmode){console.log("["+(new Date()).toTimeString()+"] Critical activities: "+_criticalactivities)}
            var f=(_criticalactivities==0);
            if(f){
                for(var id in _globalforms){
                    if(RYWINZ.busy(id)){
                        f=false;
                        break;
                    }
                }
            }
            if(f){
                // NOTIFICHE
                TAIL.enqueue(function(){
                    RYQUEAUX.query({
                        sql:"SELECT COUNT(SYSID) AS NOTIFICATIONS FROM QVMESSAGES WHERE RECEIVERID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"' AND ARCHIVED=0) AND STATUS=0 AND [:DATE(SENDINGTIME,1MONTH)]>[:TODAY()]",
                        ready:function(d){
                            try{
                                var n=0;
                                if(d.length>0){
                                    n=_getinteger(d[0]["NOTIFICATIONS"]);
                                }
                                if(n>0){
                                    $("#winz-notifications").html(n).show();
                                    try{ $("head>title").html(_apptitle+" ( "+n+" )") }catch(e){}
                                }
                                else{
                                    $("#winz-notifications").html("").hide();
                                    try{ $("head>title").html(_apptitle) }catch(e){}
                                }
                            }
                            catch(e){
                                if(window.console){console.log(e.message)}
                            }
                            TAIL.free();
                        }
                    });
                });
                // REFRESH
                for(var id in _globalforms){
                    if(_globalforms[id]._timer instanceof Function){
                        TAIL.enqueue(function(id){
                            _globalforms[id]._timer();
                            TAIL.free();
                        }, id);
                    }
                }
                TAIL.wriggle();
            }
        }, 15000
    );
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
    var msg=RYBOX.babels("MSG_QUITPAGE");
    for(var n in _globalforms){
        if(RYWINZ.modified(n) || RYWINZ.busy(n)){
            ok=false;
            break;
        }
    }
    if(ok==false){
        if(promptmess){
            msg=RYBOX.babels("MSG_CONFIRMQUIT");
            ok=confirm(msg);
        }
        else{
            msg=RYBOX.babels("MSG_QUITMODIFIEDPAGE");
        }
    }
    if(ok==true && promptmess==true){
        window.onbeforeunload=null;
        clearInterval(_timerPostman);
        var castclose=setTimeout(
            function(){
                ego_logout();
            }, 10000
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
function winz_postman(){
    if(_ismissing(_globalforms["postman"])){
        _openingparams="({})";
        RYWINZ.newform({
            id:"postman",
            name:"postman",
            path:_cambusaURL+"rywinz/postman/",
            title:"Postman",
            desk:true,
            icon:_cambusaURL+"rywinz/postman/postman"
        });
    }
    else{
        // Show the taskbar button.
        if($("#icon_dock_postman").is(':hidden')){
            $("#icon_dock_postman").remove().appendTo('#dock');
            $("#icon_dock_postman").show('fast');
        }
        setTimeout(function(){
            JQD.util.window_flat();
            $("#window_postman").addClass('window_stack').show();
            JQD.util.clear_active();
        });
        try{
            setTimeout(_globalforms["postman"].refresh, 200);
        }catch(e){}
    }
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
                    <li><a class="rudyz" href="javascript:" onclick="winz_postman()">Postman</a></li>
					<li><a class="rudyz" href="javascript:" onclick="winz_logout(true)">Logout</a></li>
				</ul>
			</li>
			<li>
				<a class="menu_trigger" href="#">Cambusa</a>
				<ul class="menu">
					<li><a class="rudyz" href="<?php print $url_cambusa ?>ryego/ryego.php" target="_blank">Ego</a></li>
					<li><a class="rudyz" href="<?php print $url_cambusa ?>rymaestro/rymaestro.php" target="_blank">Maestro</a></li>
                    <li><a class="rudyz" href="<?php print $url_cambusa ?>rymirror/rymirror.php" target="_blank">Mirror</a></li>
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
        $copy.=" - Dealer ".$copyright_dealer;
    }
?>
		<span class="float_right" style="font-size:11px;"><?php  print $copy ?></span>
        <a id="winz-notifications" class="float_right" style="background:red;color:white;cursor:pointer;display:none;" href="javascript:" onclick="winz_postman()"></a>
    </div>

    <div id="winz-about-dither" class="winz_dither" style="top:0px;background:#1E90FF;height:120%;"></div>
    <div id="winz-about" class="winz_dialog" style="display:none;width:<?php  print $aboutwidth ?>px;height: <?php  print $aboutheight ?>px;" title="About <?php print $winz_apptitle ?>">
    <div class='winz_close'>X</div><div class="winz_msgbox" style="top:30px;width:<?php  print $aboutwidth-50 ?>px;height:<?php  print $aboutheight-60 ?>px;font-size:12px;line-height:14px;">
        <!-- INIZIO ABOUT -->
<?php 
    if($aboutinclude!=""){ 
        include $aboutinclude;
    }
    else{
?>
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
<?php 
    }
?>
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
    $p=strrpos($pageURL, "/apps");
    if($p!==false){
        $pageURL=substr($pageURL, 0, $p+1);
    }
    return $pageURL;
}
?>