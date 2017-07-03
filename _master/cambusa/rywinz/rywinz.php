<?php 
/****************************************************************************
* Name:            rywinz.php                                               *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

// ASSEGNO LA FORZATURA AMBIENTE
if(!isset($winz_appenviron)){
    $winz_appenviron="";
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="ryWinz - Form Management" />
<meta name="framework" content="Cambusa <?php print $cambusa_version ?>" />
<meta name="license" content="GNU LGPL v3" />
<meta name="repository" content="https://github.com/cambusa/" />
<title><?php print $RYWINZ->apptitle ?></title>
<link rel='shortcut icon' href='_images/favicon.ico' type='image/x-icon'/>
<?php
CambusaLibrary("ryBox");
CambusaLibrary("ryQue");
CambusaLibrary("rySource");
CambusaLibrary("ryWinz");
CambusaLibrary("ryDraw");

if(is_file("library.php")){
    include_once "library.php";
}

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
elseif(isset($_GET["sessionid"]))
    $sessionid=$_GET["sessionid"];
else
    $sessionid="";

?>
<!--[if lt IE 9]>
<link rel="stylesheet" href="<?php print $url_cambusa ?>jqdesktop/assets/css/ie.css" />
<![endif]-->
<script>
_sessioninfo.sessionid="<?php  print $sessionid ?>";
var _appname="<?php  print $RYWINZ->appname ?>";
var _apptitle="<?php  print $RYWINZ->apptitle ?>";
var _appenviron="<?php  print $winz_appenviron ?>";
var _companyname="<?php  print $RYWINZ->company ?>";
var _wallpaper=<?php  print ($RYWINZ->wallpaper ? "true" : "false") ?>;
var _timerPostman=false;
var _arrowcount=0;
var POSTMAN={
    title:"<?php  print $RYWINZ->postman->title ?>",
    enabled:<?php  print ($RYWINZ->postman->enabled ? 1 : 0) ?>
}
var PILOTA={
    id:"rudder",
    name:"<?php print $RYWINZ->pilota->name ?>",
    path:"<?php print $RYWINZ->pilota->path ?>",
    title:"<?php print $RYWINZ->pilota->title ?>",
    desk:true,
    icon:"<?php print $RYWINZ->pilota->icon ?>",
    maximize:<?php print ($RYWINZ->pilota->maximize ? 1 : 0) ?>,
    controls:<?php print ($RYWINZ->pilota->controls ? 1 : 0) ?>,
    statusbar:<?php print ($RYWINZ->pilota->statusbar ? 1 : 0) ?>
}
$(document).ready(function(){
    RYEGO.go({
        crossdomain:"",
        appname:_appname,
        apptitle:_apptitle,
        appenv:_appenviron,
        config:function(d){
            _sessioninfo=d;
            if(window.console&&_sessioninfo.debugmode){console.log(_sessioninfo)};
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
    $("#sessioninfo").html(_companyname+" / "+_apptitle+" / "+_sessioninfo.envdescr+" / "+_sessioninfo.alias);
    PILOTA.environ=_appname+"_"+_sessioninfo.role;
    PILOTA.root=_sessioninfo.roledescr;
    RYWINZ.shell(PILOTA);
    
	$("#winz-preview span").click(function(evt){
		$("#winz-preview").hide();
		$("#winz-preview>iframe").prop("src", "");
	});

    // Internet Explorer gestisce l'evento in un modo impossibile
    if(!$.browser.msie){
        window.onbeforeunload=winz_confirmExit;
    }
    setTimeout(
        function(){
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"importegouser",
                    "data":{}
                }, 
                function(d){
                    try{
                        RYQUE.clean();
                    }
                    catch(e){}
                }
            );
            
        }, 5000
    )
    _timerPostman=setInterval(
        function(){
            if(window.console&&_sessioninfo.debugmode){console.log("["+(new Date()).toTimeString()+"] Critical activities: "+_systeminfo.activities)}
            var f=(_systeminfo.activities==0);
            if(f){
                for(var id in _globalforms){
                    if(RYWINZ.busy(id)){
                        f=false;
                        break;
                    }
                }
            }
            if(f){
                if(POSTMAN.enabled){
                    // NOTIFICHE
                    TAIL.enqueue(function(){
                        RYQUEAUX.query({
                            sql:"SELECT COUNT(SYSID) AS NOTIFICATIONS FROM QVMESSAGES WHERE RECEIVERID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"' AND ARCHIVED=0) AND STATUS=0 AND [:DATE(SENDINGTIME,1MONTH)]>[:TODAY()]",
                            ready:function(d){
                                try{
                                    var n=0;
                                    if(d.length>0){
                                        n=__(d[0]["NOTIFICATIONS"]).actualInteger();
                                    }
                                    if(n>0){
                                        $("#winz-notifications").html(n).show();
                                        try{ $("head>title").html(_apptitle+" ( "+n+" )") }catch(e){}
                                        if(n!=_arrowcount){
                                            _arrowcount=n;
                                        }
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
                }
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
        if(_timerPostman!==false)
            clearInterval(_timerPostman);
        var castclose=setTimeout(
            function(){
                ego_logout();
            }, 10000
        );
        winzRemoveAll(  // Rimozione di tutti i form
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
        for(var l in RYWINZ.logoutcalls){
            TAIL.enqueue(function(){
                RYWINZ.logoutcalls[l](function(){
                    TAIL.free();
                });
            });
        }
        TAIL.enqueue(function(){
            RYEGO.logout();
            TAIL.free();
            $.pause(200);
        });
        TAIL.wriggle();
    }
    return msg;
}
function winz_rudder(missing){
    if(_globalforms["rudder"]==missing){
		RYWINZ.shell(PILOTA);
    }
    else{
        // Show the taskbar button.
        if($("#icon_dock_rudder").is(':hidden')){
            $("#icon_dock_rudder").remove().appendTo('#dock');
            $("#icon_dock_rudder").show('fast');
        }
        setTimeout(function(){
            JQD.util.window_flat();
            $("#window_rudder").addClass('window_stack').show();
        });
    }
}
function winz_postman(missing){
    if(_globalforms["postman"]==missing){
        _openingparams="({})";
        RYWINZ.newform({
            id:"postman",
            name:"postman",
            path:_systeminfo.relative.cambusa+"rywinz/postman/",
            title:POSTMAN.title,
            desk:true,
            icon:_systeminfo.relative.cambusa+"rywinz/postman/postman"
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
					<li><a id="WINZ_PILOTA" class="winz-menu" href="javascript:" onclick="winz_rudder()"><?php print $RYWINZ->pilota->title ?></a></li>
<?php if($RYWINZ->postman->enabled){ ?>
                    <li><a id="WINZ_POSTMAN" class="winz-menu" href="javascript:" onclick="winz_postman()"><?php print $RYWINZ->postman->title ?></a></li>
<?php } ?>
					<li><a id="WINZ_LOGOUT" class="winz-menu" href="javascript:" onclick="winz_logout(true)">Logout</a></li>
				</ul>
			</li>
			<li>
				<a id="WINZ_TOOLS" class="menu_trigger" href="#"><?php print $RYWINZ->tools->title; ?></a>
				<ul class="menu">
<?php
// MENU CAMBUSA
foreach($RYWINZ->tools->items as $k => $v){
    print "					<li><a id=\"WINZ_".$k."\" class=\"winz-menu\" href=\"" . $v["URL"] . "\" target=\"_blank\">" . $v["TITLE"] . "</a></li>\n";
}
?>
				</ul>
			</li>
			<li>
				<a id="WINZ_INFO" class="menu_trigger" href="#">Info</a>
				<ul class="menu">
					<li><a id="WINZ_ABOUT" class="winz-menu" href="javascript:" onclick="winz_showabout()">About <?php print $RYWINZ->apptitle ?></a></li>
				</ul>
			</li>
		</ul>
        <div id="WINZ_TITLE" class="winz-maintitle"></div>
	</div>
    
    <div class="abs" id="bar_bottom" style="overflow:visible;z-index:1000000;">
<?php
// ABILITAZIONE "MOSTRA DESKTOP"
if($RYWINZ->desktop){
?>
		<a class="float_left" href="#" id="show_desktop" title="Show Desktop">
			<img src="<?php print $url_cambusa ?>jqdesktop/assets/images/icons/icon_22_desktop.png" />
		</a>
<?php
}
?>
		<ul id="dock">
		</ul>
<?php
    if($RYWINZ->logo!=""){
        $copy=$RYWINZ->logo;
    }
    else{
        $copy=$RYWINZ->apptitle." &copy; ".$RYWINZ->copyright;
        if($RYWINZ->dealer!=""){
            $copy.=" - Dealer ".$RYWINZ->dealer;
        }
    }
?>
		<span class="float_right" style="font-size:11px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php  print $copy ?>&nbsp;&nbsp;&nbsp;</span>
        <a id="winz-notifications" class="float_right" style="background:red;color:white;cursor:pointer;display:none;" href="javascript:" onclick="winz_postman()"></a>

    </div>

    <div id="winz-about-dither" class="winz_dither" style="top:0px;background:#1E90FF;height:120%;"></div>
    <div id="winz-about" class="winz_dialog" style="display:none;width:<?php  print $RYWINZ->about->width ?>px;height: <?php  print $RYWINZ->about->height ?>px;" title="About <?php print $RYWINZ->apptitle ?>">
    <div class='winz_close'>X</div><div class="winz_msgbox" style="top:30px;width:<?php  print $RYWINZ->about->width-50 ?>px;height:<?php  print $RYWINZ->about->height-60 ?>px;font-size:12px;line-height:1.8;">
        <!-- INIZIO ABOUT -->
<?php 
    print $RYWINZ->about->content;
?>
        <!-- FINE ABOUT -->
    </div>
    </div>
    
    <div id="winz-dialog"></div>
    <div id="winz-printing"></div>
    <iframe id="winz-iframe"></iframe>
    
</div> <!-- END WRAPPER -->

<div id="winz-preview" style="position:absolute;left:0px;top:0px;right:0px;bottom:0px;display:none;margin-top:20px;">
    <iframe style="position:absolute;left:0px;top:0px;width:100%;height:100%;border:1px solid black;background:white;z-index:1000000;"></iframe>
    <div style="position:absolute;left:0px;top:-20px;right:0px;height:20px;border:1px solid black;background:navy;text-align:right;padding-right:5px;z-index:1000001;"><span style="cursor:pointer;color:white;line-height:18px;">&nbsp;X&nbsp;</span></div>
</div>

</body>
</html>
