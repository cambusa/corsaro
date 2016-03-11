<?php 
/****************************************************************************
* Name:            rywembed.php                                             *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(isset($_POST["sessionid"])){
    $sessionid=$_POST["sessionid"];
    setcookie("egosessionid", $sessionid, time()+24*60*60);
}
elseif(isset($_GET["sessionid"])){
    $sessionid=$_GET["sessionid"];
    setcookie("egosessionid", $sessionid, time()+24*60*60);
}
else{
    if(isset($_COOKIE["egosessionid"]))
        $sessionid=$_COOKIE["egosessionid"];
    else
        $sessionid="";
}

// ASSEGNO LA FORZATURA AMBIENTE
if(!isset($winz_appenviron)){
    $winz_appenviron="";
}

// ASSEGNO LA BARRA LOGOUT
if(!isset($winz_applogout)){
    $winz_applogout=true;
}
if($winz_applogout)
    $desk_top=13;
else
    $desk_top=1;

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
<title><?php print $winz_apptitle ?></title>
<link rel='shortcut icon' href='_images/favicon.ico' type='image/x-icon'/>
<?php
CambusaLibrary("ryBox");
CambusaLibrary("ryQue");
CambusaLibrary("rySource");
CambusaLibrary("ryWembed");
CambusaLibrary("ryDraw");

if(isset($winz_loadmodules)){
    if(is_file($winz_loadmodules)){
        include_once $winz_loadmodules;
    }
}
if(isset($winz_moremodules)){
    print $winz_moremodules;
}
?>
<!--[if lt IE 9]>
<link rel="stylesheet" href="<?php print $url_cambusa ?>jqdesktop/assets/css/ie.css" />
<![endif]-->
<script>
_sessioninfo.sessionid="<?php  print $sessionid ?>";
var _appname="<?php  print $winz_appname ?>";
var _apptitle="<?php  print $winz_apptitle ?>";
var _appenviron="<?php  print $winz_appenviron ?>";
$(document).ready(function(){
    RYEGO.go({
        crossdomain:"",
        appname:_appname,
        apptitle:_apptitle,
        appenv:_appenviron,
        formlogin:"ryegoembed.php",
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
    _openingparams="({environ:\""+_appname+"_"+_sessioninfo.role+"\",root:\""+_sessioninfo.roledescr+"\"})";
    RYWINZ.newform({
        id:"<?php  print $winz_functionname ?>",
        name:"<?php  print $winz_functionname ?>",
        path:"<?php  print $winz_functionpath ?>",
        title:"<?php  print $winz_functiontitle ?>",
    });
}
function winz_logout(){
    var ok=true;
    var msg=RYBOX.babels("MSG_QUITPAGE");
    for(var n in _globalforms){
        if(RYWINZ.modified(n) || RYWINZ.busy(n)){
            ok=false;
            break;
        }
    }
    if(ok==false){
        msg=RYBOX.babels("MSG_CONFIRMQUIT");
        ok=confirm(msg);
    }
    if(ok){
        TAIL.enqueue(function(){
            winzRemoveAll(  // Rimozione di tuttu i form
                function(){
                    TAIL.free();
                }
            );
        
        });
        TAIL.enqueue(function(){
            RYQUE.dispose(  // Rimozione RYQUE principale
                function(){
                    TAIL.free();
                }
            );
        });
        TAIL.enqueue(function(){
            RYQUEAUX.dispose(   // Rimozione RYQUE ausiliario
                function(){
                    TAIL.free();
                }
            );
        });
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
            $.pause(1000);
        });
        TAIL.wriggle();
    }
    return ok;
}
</script>
</head>
<body spellcheck="false">

<?php
if($winz_applogout){
?>
<div id="winz_draggable" style="position:absolute;top:0px;left:0px;right:0px;height:12px;padding-right:5px;font-size:10px;text-align:right;background:black;">
<a href="javascript:" style="color:#F9F9F9;cursor:pointer;" onclick="winz_logout()">Logout</a>
</div>
<?php
}
?>

<div style="position:absolute;top:<?php print $desk_top; ?>px;left:0px;right:0px;bottom:0px;">
<div id="desktop"></div>
</div>

<div id="winz-dialog"></div>
<div id="winz-printing"></div>
<iframe id="winz-iframe"></iframe>
    
</body>
</html>
