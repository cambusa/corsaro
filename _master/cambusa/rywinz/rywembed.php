<?php 
/****************************************************************************
* Name:            rywembed.php                                             *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
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
_baseURL="<?php  print rywinzHost() ?>";
_sessionid="<?php  print $sessionid ?>";
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
        if(_logoutcallext!==false){    // Logout personalizzato esterno
            TAIL.enqueue(function(){
                _logoutcallext(
                    function(){
                        TAIL.free();
                    }
                );
            });
        }
        if(_logoutcall!==false){    // Logout personalizzato
            TAIL.enqueue(function(){
                _logoutcall(
                    function(){
                        TAIL.free();
                    }
                );
            });
        }
        TAIL.enqueue(function(){
            RYEGO.logout();
            TAIL.free();
            _pause(1000);
        });
        TAIL.wriggle();
    }
}
</script>
</head>
<body spellcheck="false">

<div id="winz_draggable" style="position:absolute;top:0px;left:0px;right:0px;height:12px;padding-right:5px;font-size:10px;text-align:right;background:black;">
<a href="javascript:winz_logout()" style="color:#F9F9F9;cursor:pointer;">Logout</a>
</div>

<div style="position:absolute;top:12px;left:0px;right:0px;bottom:0px;">
<div id="desktop"></div>
</div>

<div id="winz-dialog"></div>
<div id="winz-printing"></div>
<iframe id="winz-iframe"></iframe>
    
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